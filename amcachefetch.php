<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlAMCacheCount = "select count(*) Total FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		aa.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID $sqlFilter";								
		$getAmcache = sqlsrv_query($conn, $tsqlAMCacheCount); 
	if ( $getAmcache === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getAmcache)) { 							
	if( $row = sqlsrv_fetch_array( $getAmcache, SQLSRV_FETCH_ASSOC)) {  
		$total = $row['Total'];
	}
	//break records into pages
	$total_pages = ceil($total/$item_per_page);

	//position of records
	$page_position = (($page_number-1) * $item_per_page);	
	$tsqlAMCache = "SELECT 						
		hu.huntingGUID, sa.serverName,[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
	  FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		aa.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter
		GROUP BY huntingGUID, serverName,[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
		ORDER BY [amcacheAuditedID]
		OFFSET ".$page_position." ROWS
		FETCH NEXT ".$item_per_page." ROWS ONLY
		";
	}	

$getAmcache = sqlsrv_query($conn, $tsqlAMCache); 
if ( $getAmcache === false)  
die( print_r( sqlsrv_errors(), true));	
if(sqlsrv_has_rows($getAmcache)) { 							
	while( $row = sqlsrv_fetch_array( $getAmcache, SQLSRV_FETCH_ASSOC)) {  
		$VolumeIDLastWriteTimestamp = date_format($row['VolumeIDLastWriteTimestamp'], 'Y-m-d H:i:s');
		$FileIDLastWriteTimestamp = date_format($row['FileIDLastWriteTimestamp'], 'Y-m-d H:i:s');
		$Created = date_format($row['Created'], 'Y-m-d H:i:s');
		$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');
		$LastModified2 = date_format($row['LastModified2'], 'Y-m-d H:i:s');
		$CompileTime = date_format($row['CompileTime'], 'Y-m-d H:i:s');							
		echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['Associated'].'</td><td>'.$row['ProgramName'].'</td><td>'.$row['ProgramID'].'</td><td>'.$row['VolumeID'].'</td><td>'.$VolumeIDLastWriteTimestamp.'</td><td>'.$row['FileID'].'</td><td>'.$FileIDLastWriteTimestamp.'</td><td>'.$row['SHA1'].'</td><td>'.$row['FullPath'].'</td><td>'.$row['FileExtension'].'</td><td>'.$row['MFTEntryNumber'].'</td><td>'.$row['MFTSequenceNumber'].'</td><td>'.$row['FileSize'].'</td><td>'.$row['FileVersionString'].'</td><td>'.$row['FileVersionNumber'].'</td><td>'.$row['FileDescription'].'</td><td>'.$row['PEHeaderSize'].'</td><td>'.$row['PEHeaderHash'].'</td><td>'.$row['PEHeaderChecksum'].'</td><td>'.$Created.'</td><td>'.$LastModified.'</td><td>'.$LastModified2.'</td><td>'.$CompileTime.'</td><td>'.$row['LanguageID'].'</td><td>'.$row['CompanyName'].'</td></tr>';
	}  
}

echo '<tr><td>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  