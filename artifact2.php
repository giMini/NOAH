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
	if ($id != 'ProcessTreeAuditedID') {	
		$groupByReq = "GROUP BY huntingGUID, serverName".$req;
	}
	$tsqlAMCache = "SELECT 						
		hu.huntingGUID,sa.serverName".$req."
		FROM ".$table." tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE tableInf.serverID = sa.serverID
		AND hu.huntingID = sa.huntingID
		$sqlFilter
		$groupByReq
		ORDER BY ".$orderBy."
		OFFSET ".$page_position." ROWS
		FETCH NEXT ".$item_per_page." ROWS ONLY
		";					
	}	

$getInformation = sqlsrv_query($conn, $tsqlAMCache); 
if ( $getInformation === false)  
die( print_r( sqlsrv_errors(), true));	
if(sqlsrv_has_rows($getInformation)) { 							
	while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  								
		echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td>';
		switch ($id) {	
			case 'BrowserHistoryAuditedID':
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
				echo '<td>'.$browserType.'</td><td>'.$row['UserName'].'</td><td>'.$row['URL'].'</td>';
				break;
			case 'ExplorerBarAuditedID':				
				echo '<td>'.$row['UserName'].'</td><td>'.$row['URL'].'</td>';
				break;
			case 'LinkFilesAuditedID':
				$CreationDate = date_format($row['CreationDate'], 'Y-m-d H:i:s');	
				$LastAccessed = date_format($row['LastAccessed'], 'Y-m-d H:i:s');	
				$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');	
				echo '<td>'.$row['FileName'].'</td><td>'.$row['Caption'].'</td><td>'.$CreationDate.'</td><td>'.$LastAccessed.'</td><td>'.$LastModified.'</td><td>'.$row['Target'].'</td><td>'.$row['Hidden'].'</td>';
				break;
			case 'shimCacheAuditedID':
				$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');	
				echo '<td>'.$row['ProgramName'].'</td><td>'.$LastModified.'</td>';
				break;
			case 'NetstatID':				
				echo '<td>'.$row['Protocol'].'</td><td>'.$row['LocalAddress'].'</td><td>'.$row['LocalPort'].'</td><td>'.$row['RemoteAddress'].'</td><td>'.$row['RemotePort'].'</td><td>'.$row['State'].'</td><td>'.$row['PID'].'</td><td>'.$row['ProcessName'].'</td></tr>';  										
				break;	
			case 'amcacheAuditedID':
				$VolumeIDLastWriteTimestamp = date_format($row['VolumeIDLastWriteTimestamp'], 'Y-m-d H:i:s');
				$FileIDLastWriteTimestamp = date_format($row['FileIDLastWriteTimestamp'], 'Y-m-d H:i:s');
				$Created = date_format($row['Created'], 'Y-m-d H:i:s');
				$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');
				$LastModified2 = date_format($row['LastModified2'], 'Y-m-d H:i:s');
				$CompileTime = date_format($row['CompileTime'], 'Y-m-d H:i:s');	
				echo '<td>'.$row['Associated'].'</td><td>'.$row['ProgramName'].'</td><td>'.$row['ProgramID'].'</td><td>'.$row['VolumeID'].'</td><td>'.$VolumeIDLastWriteTimestamp.'</td><td>'.$row['FileID'].'</td><td>'.$FileIDLastWriteTimestamp.'</td><td>'.$row['SHA1'].'</td><td>'.$row['FullPath'].'</td><td>'.$row['FileExtension'].'</td><td>'.$row['MFTEntryNumber'].'</td><td>'.$row['MFTSequenceNumber'].'</td><td>'.$row['FileSize'].'</td><td>'.$row['FileVersionString'].'</td><td>'.$row['FileVersionNumber'].'</td><td>'.$row['FileDescription'].'</td><td>'.$row['PEHeaderSize'].'</td><td>'.$row['PEHeaderHash'].'</td><td>'.$row['PEHeaderChecksum'].'</td><td>'.$Created.'</td><td>'.$LastModified.'</td><td>'.$LastModified2.'</td><td>'.$CompileTime.'</td><td>'.$row['LanguageID'].'</td><td>'.$row['CompanyName'].'</td></tr>';
				break;
			case 'autorunAuditedID':									
				$launchStringTruncated = truncateForceLength($row['LaunchString'],80);
				if($row['Suspicious'] == 1) {
					echo '<td><span class="label label-warning"> '.$row['SHA-256'].' </span></td><td>'.$row['Category'].'</td><td>'.$launchStringTruncated.'</td><td>'.$row['Entry'].'</td><td>'.$row['Signer'].'</td>';  							
				}
				else {
					echo '<td>'.$row['SHA-256'].'</td><td>'.$row['Category'].'</td><td>'.$launchStringTruncated.'</td><td>'.$row['Entry'].'</td><td>'.$row['Signer'].'</td>';  							
				}	
				break;
			case 'ProcessTreeAuditedID':									
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
				echo '<td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><a target=_blank href="graph.php?parentName='.$parentName.'&parentProcessId='.$row['parentProcessId'].'&serverID='.$row['serverID'].'&processID='.$row['processID'].'">'.$row['name'].'</a></td><td>'.$row['sessionID'].'</td><td>'.$row['handles'].'</td><td>'.$row['creationDate'].'</td><td>'.$row['location'].'</td><td>'.$row['CommandLine'].'</td><td>'.$row['Decoded'].'</td><td>'.$row['Suspicious'].'</td><td>'.$row['Description'].'</td><td>'.$row['hash'].'</td><td>'.$row['username'].'</td><td>'.$row['domain'].'</td><td>'.$row['VT'].'</td><td><a target="_blank" href="'.$row['permalink'].'">'.$row['permalink'].'</a></td>'; 	
				break;
			case 'recentDocsAuditedID':				
				echo '<td>'.$row['UserName'].'</td><td>'.$row['Unicode_Link_Name'].'</td>';
				break;	
			case 'installedProgramID':				
				echo '<td>'.$row['displayName'].'</td><td>'.$row['displayVersion'].'</td><td>'.$row['installLocation'].'</td><td>'.$row['publisher'].'</td><td>'.$row['displayicon'].'</td>';
				break;
			case 'DNSCacheAuditedID':				
				echo '<td>'.$row['RecordName'].'</td>';
				break;	
			case 'USBHistoryAuditedID':
				$LastTimeDeviceConnected = date_format($row['LastTimeDeviceConnected'], 'Y-m-d H:i:s');			
				$InstallSetupDevTimeDeviceConnected = date_format($row['InstallSetupDevTimeDeviceConnected'], 'Y-m-d H:i:s');			
				echo '<td>'.$row['DeviceName'].'</td><td>'.$row['FriendlyName'].'</td><td>'.$row['InstanceID'].'</td><td>'.$row['ClassGUID'].'</td><td>'.$row['SymbolicName'].'</td><td>'.$row['SerialNumber'].'</td><td>'.$LastTimeDeviceConnected.'</td><td>'.$InstallSetupDevTimeDeviceConnected.'</td><td>'.$row['DriverDesc'].'</td><td>'.$row['DriverVersion'].'</td><td>'.$row['ProviderName'].'</td><td>'.$row['DriverDate'].'</td><td>'.$row['InfPath'].'</td><td>'.$row['InfSection'].'</td><td>'.$row['ParentIdPrefix'].'</td><td>'.$row['Service'].'<td>';
				break;	
			case 'RunMRUsAuditedID':				
				echo '<td>'.$row['UserName'].'</td><td>'.$row['MRU'].'</td>';
				break;				
			case 'scheduledTaskAuditedID':	
				$LastRunTime = date_format($row['lastRunTime'], 'Y-m-d H:i:s');
				$NextRunTime = date_format($row['nextRunTime'], 'Y-m-d H:i:s');
				if($row['Suspicious'] == 1) {
					echo '<td><span class="label label-warning">'.$row['name'].'</td></span><td>'.$row['runAs'].'</td><td>'.$row['arguments'].'</td><td>'.$row['scheduledAction'].'</td><td>'.$row['pathName'].'</td><td>'.$LastRunTime.'</td><td>'.$NextRunTime.'</td><td>'.$row['hash'].'</td>';
				}
				else {
					echo '<td>'.$row['name'].'</td><td>'.$row['runAs'].'</td><td>'.$row['arguments'].'</td><td>'.$row['scheduledAction'].'</td><td>'.$row['pathName'].'</td><td>'.$LastRunTime.'</td><td>'.$NextRunTime.'</td><td>'.$row['hash'].'</td>';
				}
				break;
			case 'serviceAuditedID':				
				echo '<td>'.$row['displayName'].'</td><td>'.$row['name'].'</td><td>'.$row['startName'].'</td><td>'.$row['startMode'].'</td><td>'.$row['servicePathName'].'</td><td>'.$row['serviceDescription'].'</td>';
				break;
		}
		echo '</tr>';
	}  
}

echo '<tr><td>';
echo paginate_function($item_per_page, $page_number, $total, $total_pages);
echo '</td></tr>';