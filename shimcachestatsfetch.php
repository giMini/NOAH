<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
	$tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[ShimCacheAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
	$item_per_page = 100;
	//break records into pages
	$total_pages = ceil($total/$item_per_page);

	//position of records
	$page_position = (($page_number-1) * $item_per_page);	
	$tsqlAMCache = "SELECT 						
		[ProgramName], COUNT(*) as tot
	  FROM [NOAH].[dbo].[ShimCacheAudited] tableInf
		
		GROUP BY [ProgramName]
		ORDER BY tot
		OFFSET ".$page_position." ROWS
		FETCH NEXT ".$item_per_page." ROWS ONLY
		";
	}	

$getInformation = sqlsrv_query($conn, $tsqlAMCache); 
if ( $getInformation === false)  
die( print_r( sqlsrv_errors(), true));	
if(sqlsrv_has_rows($getInformation)) { 							
	while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  			
		echo '<tr><td>'.$row['ProgramName'].'</td><td>'.$row['tot'].'</td></tr>';
	}  
}
				  