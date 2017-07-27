<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[BrowserHistoryAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
		hu.huntingGUID, sa.serverName,[BrowserHistoryAuditedID],[BrowserType],[UserName],[URL]
	  FROM [NOAH].[dbo].[BrowserHistoryAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE 
		tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter
		GROUP BY hu.huntingGUID, sa.serverName,BrowserHistoryAuditedID,[BrowserType],[UserName],[URL]
		ORDER BY [BrowserHistoryAuditedID]
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
		$browserType='';
		switch ($row['BrowserType']) {
			case 1:
				$browserType="IE";
				break;
			case 2:
				$browserType="Chrome";
				break;
			case 3:
				$browserType="Firefox";
				break;
		}
		echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$browserType.'</td><td>'.$row['UserName'].'</td><td>'.$row['URL'].'</td></tr>';  							
	}  
}

echo '<tr><td><br>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  