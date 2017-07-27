<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	
include_once("switchPage.php");
$tsqlInformation = "select count(*) Total FROM ".$table." tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
	$groupByReq = "";
	$groupByStat = "GROUP BY huntingGUID, serverName".$stat;
	
	$tsql = "SELECT TOP (50)						
		hu.huntingGUID".$stat.", COUNT(*) as tot
		FROM ".$table." tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter
		$groupByStat
		ORDER BY tot";
		//OFFSET ".$page_position." ROWS
		//FETCH NEXT ".$item_per_page." ROWS ONLY
		//";					
}	

$getInformation = sqlsrv_query($conn, $tsql); 
if ( $getInformation === false)  
die( print_r( sqlsrv_errors(), true));	
if(sqlsrv_has_rows($getInformation)) { 							
	while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  			
		echo '<tr><td>'.$row['huntingGUID'].'</td>';
		switch ($id) {	
			case 'ProcessTreeAuditedID':
				echo '<td>'.$row['hash'].'</td><td>'.$row['name'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'shimCacheAuditedID':
				echo '<td>'.$row['ProgramName'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'BrowserHistoryAuditedID':
				echo '<td>'.$row['URL'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'DNSCacheAuditedID':
				echo '<td>'.$row['RecordName'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'ExplorerBarAuditedID':
				echo '<td>'.$row['URL'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'LinkFilesAuditedID':
				echo '<td>'.$row['Caption'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'amcacheAuditedID':
				echo '<td>'.$row['SHA1'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'RunMRUsAuditedID':
				echo '<td>'.$row['MRU'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'NetstatID':
				echo '<td>'.$row['RemoteAddress'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'autorunAuditedID':
				echo '<td>'.$row['SHA-256'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'recentDocsAuditedID':
				echo '<td>'.$row['Unicode_Link_Name'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'scheduledTaskAuditedID':
				echo '<td>'.$row['hash'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'serviceAuditedID':
				echo '<td>'.$row['displayName'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'installedProgramID':
				echo '<td>'.$row['displayName'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
			case 'USBHistoryAuditedID':
				echo '<td>'.$row['DeviceName'].'</td><td>'.$row['tot'].'</td></tr>';
				break;
		}
	}  
}
		
	
echo '<tr><td>';
//echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';