<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[NetStatAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
		hu.huntingGUID, sa.serverName, sa.serverID, NetstatID, [Protocol],[LocalAddress],[LocalPort],[RemoteAddress],[RemotePort],[State],[ProcessName],[PID]
	  FROM [NOAH].[dbo].[NetStatAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter		
		GROUP BY hu.huntingGUID, sa.serverName, sa.serverID, NetstatID, [Protocol],[LocalAddress],[LocalPort],[RemoteAddress],[RemotePort],[State],[ProcessName],[PID]
		ORDER BY NetstatID
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
		echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['Protocol'].'</td><td>'.$row['LocalAddress'].'</td><td>'.$row['LocalPort'].'</td><td>'.$row['RemoteAddress'].'</td><td>'.$row['RemotePort'].'</td><td>'.$row['PID'].'</td><td>'.$row['ProcessName'].'</td></tr>';  										
	}  
}

echo '<tr><td><br>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  