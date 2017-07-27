<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[AutorunAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID $sqlFilter";								
		$getInformation = sqlsrv_query($conn, $tsqlInformation); 
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) { 							
	if( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  
		$total = $row['Total'];
	}
	//break records into pages
	$total_pages = ceil($total/$item_per_page);

	//position of records
	$page_position = (($page_number-1) * $item_per_page);	
	$tsqlInformation = "SELECT 						
		hu.huntingGUID, sa.serverName, sa.serverID, autorunAuditedID, Suspicious, count(MD5) as countMD5, MD5, LaunchString, EntryLocation, [Entry], Signer, Category
	  FROM [NOAH].[dbo].[AutorunAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter		
		GROUP BY hu.huntingGUID, sa.serverName, sa.serverID, autorunAuditedID, Suspicious, MD5, LaunchString, EntryLocation, [Entry], Signer, Category
		ORDER BY Suspicious DESC, countMD5
		OFFSET ".$page_position." ROWS
		FETCH NEXT ".$item_per_page." ROWS ONLY
		";		
	}	
$getInformation = sqlsrv_query($conn, $tsqlInformation); 
if ( $getInformation === false)  
die( print_r( sqlsrv_errors(), true));	
$data = array();
$data['data'] = '';
if(sqlsrv_has_rows($getInformation)) { 							
	while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  
		$launchStringTruncated = truncateForceLength($row['LaunchString'],80);
		if($row['Suspicious']) {
			echo '<tr><td>'.$row['huntingGUID'].'</td><td><span class="label label-warning">'.$row['serverName'].'</span></td><td>'.$row['countMD5'].'</td><td>'.$row['MD5'].'</td><td>'.$row['Category'].'</td><td>'.$launchStringTruncated.'</td><td>'.$row['Entry'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}
		else {
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['countMD5'].'</td><td>'.$row['MD5'].'</td><td>'.$row['Category'].'</td><td>'.$launchStringTruncated.'</td><td>'.$row['Entry'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}		
	}  
}

echo '<tr><td><br>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  