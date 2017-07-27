<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[ProcessTreeAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
		hu.huntingGUID, sa.serverName, sa.serverID, ProcessTreeAuditedID,[name],[processID],[parentProcessId],[sessionID],[handles],[creationDate]
			,[location],[CommandLine],[Decoded],[Suspicious],[Description],[hash],[username],tableInf.[domain],[VT],[permalink]
	  FROM [NOAH].[dbo].[ProcessTreeAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter		
		ORDER BY [ProcessTreeAuditedID]
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
		$parentProcessSql = "SELECT name FROM [NOAH].[dbo].[ProcessTreeAudited] WHERE processID LIKE ".$row['parentProcessId']." AND serverID LIKE ".$row['serverID'];							
		$getParentProcess = sqlsrv_query($conn, $parentProcessSql);  
		if ( $getParentProcess === false)  
		die( print_r( sqlsrv_errors(), true));
		if( $rowParentName = sqlsrv_fetch_array( $getParentProcess, SQLSRV_FETCH_ASSOC)) { 
			$parentName = $rowParentName['name'];
		}
		else {				
			$parentName = 'Non-existent Process';
		}
		echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><a target=_blank href="graph.php?parentName='.$parentName.'&parentProcessId='.$row['parentProcessId'].'&serverID='.$row['serverID'].'&processID='.$row['processID'].'">'.$row['name'].'</a></td><td>'.$row['sessionID'].'</td><td>'.$row['handles'].'</td><td>'.$row['creationDate'].'</td><td>'.$row['location'].'</td><td>'.$row['CommandLine'].'</td><td>'.$row['Decoded'].'</td><td>'.$row['Suspicious'].'</td><td>'.$row['Description'].'</td><td>'.$row['hash'].'</td><td>'.$row['username'].'</td><td>'.$row['domain'].'</td><td>'.$row['VT'].'</td><td><a target="_blank" href="'.$row['permalink'].'">'.$row['permalink'].'</a></td></tr>'; 
//'<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><a target=_blank href="graph.php?parentName='.$parentName.'&parentProcessId='.$row['parentProcessId'].'&serverID='.$row['serverID'].'&processID='.$row['processID'].'">'.$row['name'].'</a></td></tr>';									
	}  
}

echo '<tr><td><br>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  