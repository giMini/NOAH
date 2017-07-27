<?php
include_once("connection.php");
include_once("function.php");
include_once("configfetch.php");	

$pattern = "%";
	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[strategy]
			  ,[securityParameter]
		  FROM [NOAH].[dbo].[OSPrivilegeAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND securityParameter LIKE '%".$pattern."%'
		";		
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>OS Privileges</b> '.$row['strategy'].'</td><td>'.$row['securityParameter'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}	
	
	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[account]
			  ,sha.[shareName]
		  FROM [NOAH].[dbo].[ShareRightsAudited] tableInf, [NOAH].[dbo].[ShareAudited] sha, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE sha.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND tableInf.shareAuditedID = sha.shareAuditedID 
		  AND account LIKE '%".$pattern."%'
		";		
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Share</b> '.$row['shareName'].'</td><td>'.$row['account'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}

	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[runAs]
			  ,Name
			  ,ScheduledAction
			  ,arguments
		  FROM [NOAH].[dbo].[ScheduledTaskAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND runAs LIKE '%".$pattern."%'
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Scheduled Task</b> '.$row['Name'].' ('.$row['ScheduledAction'].' - '.$row['arguments'].')</td><td>'.$row['runAs'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}	
	
	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[username]
			  ,tableInf.domain
			  ,Name
			  ,CommandLine
		  FROM [NOAH].[dbo].[ProcessTreeAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND username LIKE '%".$pattern."%'
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$launchStringTruncated = truncateForceLength($row['CommandLine'],80);
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Process</b> '.$row['Name'].' ('.$launchStringTruncated.')</td><td>'.$row['domain'].'\\'.$row['username'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}
	
	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[UserName]
			  ,[MRU]			  
		  FROM [NOAH].[dbo].[RunMRUsAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND UserName LIKE '%".$pattern."%'
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>MRU</b> '.$row['MRU'].'</td><td>'.$row['UserName'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}
	
	$tsql = "SELECT hu.huntingGUID, 
			  sa.serverName serverName
			  ,[UserName]
			  ,[Unicode_Link_Name]			 
		  FROM [NOAH].[dbo].[RecentDocsAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND username LIKE '%".$pattern."%'
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Recent Docs</b> '.$row['Unicode_Link_Name'].'</td><td>'.$row['UserName'].'</td></tr>';  							
			//echo '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}	

echo '<tr><td><br>';
//echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';
				  