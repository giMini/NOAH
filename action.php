<?php
$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
}
include_once("function.php");
$huntGUID='';
$serverID='';
if (isset($_POST['huntGUID'])) {
	$huntGUID = htmlentities(trim(htmlspecialchars(addslashes($_POST['huntGUID']))));
}
if (isset($_POST['serverID'])) {					
	$serverID = htmlentities(trim(htmlspecialchars(addslashes($_POST['serverID']))));	
}
$sqlFilter = "";
if ($huntGUID != '') {	
	$sqlFilter .= " AND hu.huntingGUID LIKE '$huntGUID' ";		
}
if ($serverID != '') {
	$sqlFilter .= " AND sa.serverID = $serverID ";
}

function SuspiciousUSBHistoryCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName, aa.[serverID], USBHistoryAuditedID,[DeviceName],[FriendlyName],[InstanceID],[ClassGUID],[SymbolicName],[SerialNumber],
		[LastTimeDeviceConnected],[InstallSetupDevTimeDeviceConnected],[DriverDesc],[DriverVersion],[ProviderName],
		[DriverDate],[InfPath],[InfSection],[ParentIdPrefix],[Service]
	  FROM [NOAH].[dbo].[USBHistoryAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (DeviceName LIKE '%".$pattern."%' OR FriendlyName LIKE '%".$pattern."%' OR InstanceID LIKE '%".$pattern."%' 
		OR ClassGUID LIKE '%".$pattern."%' OR SymbolicName LIKE '%".$pattern."%' OR SerialNumber LIKE '%".$pattern."%'
		OR DriverDesc LIKE '%".$pattern."%' OR DriverVersion LIKE '%".$pattern."%' OR ProviderName LIKE '%".$pattern."%'
		 OR DriverDate LIKE '%".$pattern."%' OR InfPath LIKE '%".$pattern."%' OR InfSection LIKE '%".$pattern."%'
		  OR ParentIdPrefix LIKE '%".$pattern."%' OR Service LIKE '%".$pattern."%')
		".$sqlFilter."
		AND aa.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,aa.serverID, serverName, USBHistoryAuditedID,[DeviceName],[FriendlyName],[InstanceID],[ClassGUID],[SymbolicName],[SerialNumber],
		[LastTimeDeviceConnected],[InstallSetupDevTimeDeviceConnected],[DriverDesc],[DriverVersion],[ProviderName],
		[DriverDate],[InfPath],[InfSection],[ParentIdPrefix],[Service]
		";				
	$getInformation = sqlsrv_query($conn, $tsql);
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) {  
		while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  			
			$LastTimeDeviceConnected = date_format($row['LastTimeDeviceConnected'], 'Y-m-d H:i:s');			
			$InstallSetupDevTimeDeviceConnected = date_format($row['InstallSetupDevTimeDeviceConnected'], 'Y-m-d H:i:s');			
			$data['data'] .= '<tr><td>'.$row['DeviceName'].'</td><td>'.$row['FriendlyName'].'</td><td>'.$row['InstanceID'].'</td><td>'.$row['ClassGUID'].'</td><td>'.$row['SymbolicName'].'</td><td>'.$row['SerialNumber'].'</td><td>'.$LastTimeDeviceConnected.'</td><td>'.$InstallSetupDevTimeDeviceConnected.'</td><td>'.$row['DriverDesc'].'</td><td>'.$row['DriverVersion'].'</td><td>'.$row['ProviderName'].'</td><td>'.$row['DriverDate'].'</td><td>'.$row['InfPath'].'</td><td>'.$row['InfSection'].'</td><td>'.$row['ParentIdPrefix'].'</td><td>'.$row['Service'].'</td></tr>';
		}  
	}	
	return $data;
}

function SuspiciousServicesCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName, tableInf.[serverID], [serviceAuditedID],[displayName],[name],[startName],[startMode],[servicePathName],[serviceDescription]
	  FROM [NOAH].[dbo].[ServiceAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (displayName LIKE '%".$pattern."%' OR name LIKE '%".$pattern."%' OR startName LIKE '%".$pattern."%' OR servicePathName LIKE '%".$pattern."%')
		".$sqlFilter."
		AND tableInf.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,tableInf.serverID, serverName, [serviceAuditedID],[displayName],[name],[startName],[startMode],[servicePathName],[serviceDescription]
		";		
	$getInformation = sqlsrv_query($conn, $tsql);
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) {  
		while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  			
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['displayName'].'</td><td>'.$row['name'].'</td><td>'.$row['startName'].'</td><td>'.$row['startMode'].'</td><td>'.$row['servicePathName'].'</td><td>'.$row['serviceDescription'].'</td>';
		}  
	}	
	return $data;
}

function SuspiciousRecentDocsCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName,aa.[serverID], [recentDocsAuditedID],[UserName],[Unicode_Link_Name]
	  FROM [NOAH].[dbo].[RecentDocsAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (UserName LIKE '%".$pattern."%' OR Unicode_Link_Name LIKE '%".$pattern."%')
		".$sqlFilter."
		AND aa.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,aa.serverID, serverName, [recentDocsAuditedID],[UserName],[Unicode_Link_Name]
		";		
	$getInformation = sqlsrv_query($conn, $tsql);
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) {  
		while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  			
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['UserName'].'</td><td>'.$row['Unicode_Link_Name'].'</td></tr>';				
		}  
	}	
	return $data;
}

function SuspiciousLinkFileCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName,aa.[serverID],LinkFilesAuditedID,[FileName],[Caption],[CreationDate],[LastAccessed],[LastModified],[Target],[Hidden]
	  FROM [NOAH].[dbo].[LinkFilesAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (FileName LIKE '%".$pattern."%' OR Caption LIKE '%".$pattern."%' OR Target LIKE '%".$pattern."%')
		".$sqlFilter."
		AND aa.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,aa.serverID, serverName,[LinkFilesAuditedID],[FileName],[Caption],[CreationDate],[LastAccessed],[LastModified],[Target],[Hidden]
		";		
	$getInformation = sqlsrv_query($conn, $tsql);
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) {  
		while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  
			$CreationDate = date_format($row['CreationDate'], 'Y-m-d H:i:s');	
			$LastAccessed = date_format($row['LastAccessed'], 'Y-m-d H:i:s');	
			$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');	
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['FileName'].'</td><td>'.$row['Caption'].'</td><td>'.$CreationDate.'</td><td>'.$LastAccessed.'</td><td>'.$LastModified.'</td><td>'.$row['Target'].'</td><td>'.$row['Hidden'].'</td></tr>';				
		}  
	}	
	return $data;
}

function SuspiciousShimcacheCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName,[shimCacheAuditedID],[ProgramName],[LastModified]
	  FROM [NOAH].[dbo].[shimCacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (ProgramName LIKE '%".$pattern."%')
		".$sqlFilter."
		AND aa.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,aa.serverID, serverName,[shimCacheAuditedID],[ProgramName],[LastModified]		
		";		
	$getInformation = sqlsrv_query($conn, $tsql); 
	if ( $getInformation === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getInformation)) {  
		while( $row = sqlsrv_fetch_array( $getInformation, SQLSRV_FETCH_ASSOC)) {  
			$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');				
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['ProgramName'].'</td><td>'.$LastModified.'</td></tr>';
		}  
	}	
	return $data;
}

function SuspiciousAMCacheCommand ($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT 						
		hu.huntingGUID, sa.serverName,[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
	  FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE (ProgramName LIKE '%".$pattern."%' OR FullPath LIKE '%".$pattern."%' OR SHA1 LIKE '%".$pattern."%')
		".$sqlFilter."
		AND aa.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID	
		GROUP BY hu.huntingGUID,aa.serverID, serverName,amcacheAuditedID,[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]		
		";		
	$getRunMRU = sqlsrv_query($conn, $tsql); 
	if ( $getRunMRU === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getRunMRU)) {  
		while( $row = sqlsrv_fetch_array( $getRunMRU, SQLSRV_FETCH_ASSOC)) {  
			$VolumeIDLastWriteTimestamp = date_format($row['VolumeIDLastWriteTimestamp'], 'Y-m-d H:i:s');
			$FileIDLastWriteTimestamp = date_format($row['FileIDLastWriteTimestamp'], 'Y-m-d H:i:s');
			$Created = date_format($row['Created'], 'Y-m-d H:i:s');
			$LastModified = date_format($row['LastModified'], 'Y-m-d H:i:s');
			$LastModified2 = date_format($row['LastModified2'], 'Y-m-d H:i:s');
			$CompileTime = date_format($row['CompileTime'], 'Y-m-d H:i:s');
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['Associated'].'</td><td>'.$row['ProgramName'].'</td><td>'.$row['ProgramID'].'</td><td>'.$row['VolumeID'].'</td><td>'.$VolumeIDLastWriteTimestamp.'</td><td>'.$row['FileID'].'</td><td>'.$FileIDLastWriteTimestamp.'</td><td>'.$row['SHA1'].'</td><td>'.$row['FullPath'].'</td><td>'.$row['FileExtension'].'</td><td>'.$row['MFTEntryNumber'].'</td><td>'.$row['MFTSequenceNumber'].'</td><td>'.$row['FileSize'].'</td><td>'.$row['FileVersionString'].'</td><td>'.$row['FileVersionNumber'].'</td><td>'.$row['FileDescription'].'</td><td>'.$row['PEHeaderSize'].'</td><td>'.$row['PEHeaderHash'].'</td><td>'.$row['PEHeaderChecksum'].'</td><td>'.$Created.'</td><td>'.$LastModified.'</td><td>'.$LastModified2.'</td><td>'.$CompileTime.'</td><td>'.$row['LanguageID'].'</td><td>'.$row['CompanyName'].'</td></tr>';
		}  
	}	
	return $data;
}

function SuspiciousBrowserHistoryCommand ($conn, $pattern, $sqlFilter){						
	$tsql = "SELECT huntingGUID
		  ,sa.[serverName]
		  ,[BrowserType]
		  ,[UserName]
		  ,[URL]
	FROM [NOAH].[dbo].[BrowserHistoryAudited] bha, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
	WHERE URL LIKE '%".$pattern."%'
	".$sqlFilter."
	AND bha.serverID = sa.serverID
	AND sa.huntingID = hu.huntingID";
	$getProcessTree = sqlsrv_query($conn, $tsql); 
	if ( $getProcessTree === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcessTree)) {  
		while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  		
			//if(strpos($row['ProcessName'], 'powershell') !== FALSE){		
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
				$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$browserType.'</td><td>'.$row['UserName'].'</td><td>'.$row['URL'].'</td></tr>';  							
			//}
		}  
	}	
	return $data;
}

function SuspiciousNetstatCommand ($conn, $pattern, $sqlFilter){
	$tsql = "SELECT hu.huntingGUID, sa.serverName, sa.serverID, NetstatID, [Protocol],[LocalAddress],[LocalPort],[RemoteAddress],[RemotePort],[State],[ProcessName],[PID]
		FROM [NOAH].[dbo].[NetStatAudited] tableInf, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
		WHERE (LocalAddress LIKE '%".$pattern."%' OR RemoteAddress LIKE '%".$pattern."%' OR LocalPort LIKE '%".$pattern."%' OR RemotePort LIKE '%".$pattern."%' OR ProcessName LIKE '%".$pattern."%' OR PID LIKE '%".$pattern."%')
		".$sqlFilter."
		AND tableInf.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID		
		";
	$getNetstat = sqlsrv_query($conn, $tsql); 
	if ( $getNetstat === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getNetstat)) {  
		while( $row = sqlsrv_fetch_array( $getNetstat, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['Protocol'].'</td><td>'.$row['LocalAddress'].'</td><td>'.$row['LocalPort'].'</td><td>'.$row['RemoteAddress'].'</td><td>'.$row['RemotePort'].'</td><td>'.$row['State'].'</td><td>'.$row['ProcessName'].'</td><td>'.$row['PID'].'</td></tr>';  										
		}  
	}	
	return $data;
}

function SuspiciousDNSCacheCommand ($conn, $pattern, $sqlFilter){
	$tsql = "SELECT hu.huntingGUID, sa.serverName, sa.serverID, DNSCacheAuditedID, [RecordName]
		FROM [NOAH].[dbo].[DNSCacheAudited] tableInf, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
		WHERE (RecordName LIKE '%".$pattern."%')
		".$sqlFilter."
		AND tableInf.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID		
		";
	$getDNSCache = sqlsrv_query($conn, $tsql); 
	if ( $getDNSCache === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getDNSCache)) {  
		while( $row = sqlsrv_fetch_array( $getDNSCache, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['RecordName'].'</td></tr>';  										
		}  
	}	
	return $data;
}

function SuspiciousSoftwareInstalledCommand ($conn, $pattern, $sqlFilter){
	$tsql = "SELECT hu.huntingGUID, sa.serverName, sa.serverID, [installedProgramID], [displayName],[displayVersion],[installLocation],[publisher],[displayicon]
		FROM [NOAH].[dbo].[InstalledProgramAudited] tableInf, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
		WHERE (displayName LIKE '%".$pattern."%' OR installLocation LIKE '%".$pattern."%' OR publisher LIKE '%".$pattern."%' OR displayicon LIKE '%".$pattern."%')
		".$sqlFilter."
		AND tableInf.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID		
		";
	$getDNSCache = sqlsrv_query($conn, $tsql); 
	if ( $getDNSCache === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getDNSCache)) {  
		while( $row = sqlsrv_fetch_array( $getDNSCache, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['displayName'].'</td><td>'.$row['displayVersion'].'</td><td>'.$row['installLocation'].'</td><td>'.$row['publisher'].'</td><td>'.$row['displayicon'].'</td></tr>';
		}  
	}	
	return $data;
}

function SuspiciousRunMRUCommand ($conn, $pattern, $sqlFilter){
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT huntingGUID
			  ,sa.[serverName]
			  ,[UserName]
			  ,[MRU]
		FROM [NOAH].[dbo].[RunMRUsAudited] rma, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
		WHERE MRU LIKE '%".$pattern."%'
		".$sqlFilter."
		AND rma.serverID = sa.serverID
		AND sa.huntingID = hu.huntingID		
		";
	$getRunMRU = sqlsrv_query($conn, $tsql); 
	if ( $getRunMRU === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getRunMRU)) {  
		while( $row = sqlsrv_fetch_array( $getRunMRU, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['UserName'].'</td><td>'.$row['MRU'].'</td></tr>';  									
		}  
	}	
	return $data;
}

function SuspiciousPrefetchCommand ($conn, $programname, $extension){						
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT sa.[serverID], sa.serverName
		  ,FileAssociated
		  ,[ProgramName]
		  ,[Hash]
		  ,[NumberOfExecutions]
		  ,[PrefetchSize]
		  ,[LastExecutionTime_1]
		  ,[LastExecutionTime_2]
		  ,[LastExecutionTime_3]
		  ,[LastExecutionTime_4]
		  ,[LastExecutionTime_5]
		  ,[LastExecutionTime_6]
		  ,[LastExecutionTime_7]
		  ,[LastExecutionTime_8]	  
		FROM [NOAH].[dbo].[PrefetchAudited] PA, [NOAH].[dbo].[PrefetchFilesAssociatedAudited] PFAA, [NOAH].[dbo].[ServerAudited] sa
		WHERE FileAssociated LIKE '%".$extension."%'
		AND programname LIKE '%".$programname."%'
		AND PA.[prefetchAuditedID] = PFAA.[prefetchAuditedID]
		AND PA.serverID = sa.serverID
		ORDER BY NumberOfExecutions DESC
		";
	$getPrefetch = sqlsrv_query($conn, $tsql); 
	if ( $getPrefetch === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getPrefetch)) {  
		while( $row = sqlsrv_fetch_array( $getPrefetch, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['serverID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['ProgramName'].'</td><td>'.$row['FileAssociated'].'</td><td>'.$row['NumberOfExecutions'].'</td><td></td></tr>';
			//$data['data'] .= '<tr><td>'.$row['serverName'].'</td><td>'.$row['MD5'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Signer'].'</td></tr>';  							
		}  
	}	
	return $data;
}

function SuspiciousAutorunMD5Command ($conn, $pattern, $sqlFilter){							
	$tsql = "SELECT hu.huntingGUID, serverName, count(MD5) as countMD5, MD5, LaunchString, EntryLocation, [Entry], Signer, Category
		FROM [NOAH].[dbo].[AutorunAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
		WHERE md5 LIKE '%".$pattern."%' OR LaunchString LIKE '%".$pattern."%'
		".$sqlFilter."
		AND aa.serverID = sa.serverID		
		AND sa.huntingID = hu.huntingID
		GROUP BY huntingGUID, serverName, MD5, LaunchString, EntryLocation, [Entry], Signer, Category
		";
	$getAutoruns = sqlsrv_query($conn, $tsql); 
	if ( $getAutoruns === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getAutoruns)) {  
		while( $row = sqlsrv_fetch_array( $getAutoruns, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['countMD5'].'</td><td>'.$row['MD5'].'</td><td>'.$row['Category'].'</td><td>'.$row['LaunchString'].'</td><td>'.$row['Entry'].'</td><td>'.$row['Signer'].'</td></tr>';  												
		}  
	}	
	return $data;
}

function SuspiciousProcessTreeByServerCommand ($conn, $pattern, $sqlFilter) {
	$tsql = "SELECT huntingGUID,
			fpbss.serverID, sa.serverName,[name],[processID],parentProcessId,[sessionID],[handles],[creationDate]
			,[location],[CommandLine],[Decoded],[Suspicious],[Description],[hash],[username],fpbss.[domain],[VT],[permalink]
			FROM [NOAH].[dbo].[ProcessTreeAudited] fpbss, [NOAH].[dbo].[ServerAudited] sa,
			[NOAH].[dbo].[Hunt] hu
			WHERE (hash LIKE '%".$pattern."%' OR name LIKE '%".$pattern."%' OR CommandLine LIKE '%".$pattern."%' OR location LIKE '%".$pattern."%' OR username LIKE '%".$pattern."%' OR processID LIKE '%".$pattern."%' OR parentProcessId LIKE '%".$pattern."%')
			".$sqlFilter."
			AND fpbss.serverID = sa.serverID
			AND hu.huntingID = sa.huntingID";			
	$getProcessTree = sqlsrv_query($conn, $tsql); 
	if ( $getProcessTree === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcessTree)) {  
		while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  		
			//if(stripos($row['name'], $pattern) !== FALSE || stripos($row['processID'], $pattern) !== FALSE ){		
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
				$commandlineTruncated = truncateForceLength($row['CommandLine'],150);
				$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><a target=_blank href="graph.php?parentName='.$parentName.'&parentProcessId='.$row['parentProcessId'].'&serverID='.$row['serverID'].'&processID='.$row['processID'].'">'.$row['name'].'</a></td><td>'.$row['sessionID'].'</td><td>'.$row['handles'].'</td><td>'.$row['creationDate'].'</td><td>'.$row['location'].'</td><td>'.$commandlineTruncated.'</td><td>'.$row['Decoded'].'</td><td>'.$row['Suspicious'].'</td><td>'.$row['Description'].'</td><td>'.$row['hash'].'</td><td>'.$row['username'].'</td><td>'.$row['domain'].'</td><td>'.$row['VT'].'</td><td><a target="_blank" href="'.$row['permalink'].'">'.$row['permalink'].'</a></td></tr>';
			//}
		}  
	}	
	return $data;
}
	

function SuspiciousProcessTreeCommand ($conn, $pattern){							
	$tsql = "SELECT 
			[Count]
			,[ProcessName]
			FROM [NOAH].[dbo].[FlatProcessStat]
			GROUP BY [Count], [ProcessName]
			ORDER BY [Count] ASC";
	$getProcessTree = sqlsrv_query($conn, $tsql); 
	if ( $getProcessTree === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcessTree)) {  
		while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  		
			if(stripos($row['ProcessName'], $pattern) !== FALSE){							
				$data['data'] .= '<tr><td>'.$row['Count'].'</td><td>'.$row['ProcessName'].'</td></tr>';  							
			}
		}  
	}	
	return $data;
}
					
function SuspiciousParentChild($conn, $server, $parent, $pattern){						
	$tsql = "DECLARE @hunt varchar(100) = '".$server."'; SELECT pta.serverID, sa.serverName as serverName,
	hu.huntingGUID as hGUID,[level],[processID],[parentProcessId],[name],[sessionID],
	[handles],[creationDate],[location],[CommandLine],[Decoded],[Suspicious],
	[Description],[hash] 
	FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu						
	WHERE parentProcessId IN (SELECT processID FROM [NOAH].[dbo].[ProcessTreeAudited] pta2 WHERE name LIKE '%".$parent."%' AND pta.serverID = pta2.serverID)
	AND hu.huntingGUID LIKE '%' + @hunt + '%'
	AND pta.serverID = sa.serverID
	AND sa.huntingID = hu.huntingID 
	AND name LIKE '%".$pattern."%'";

	$getProcess = sqlsrv_query($conn, $tsql); 
	if ( $getProcess === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcess)) {  
		while( $row = sqlsrv_fetch_array( $getProcess, SQLSRV_FETCH_ASSOC)) {  
			$parentProcessSql = "SELECT name FROM [NOAH].[dbo].[ProcessTreeAudited] WHERE processID LIKE ".$row['parentProcessId']." AND serverID LIKE ".$row['serverID'];							
			$getParentProcess = sqlsrv_query($conn, $parentProcessSql);  
			if ( $getParentProcess === false)  
			die( print_r( sqlsrv_errors(), true));
			if( $row2 = sqlsrv_fetch_array( $getParentProcess, SQLSRV_FETCH_ASSOC)) {  						
				$data['data'] .= '<tr><td><a href="pages/examples/invoice.html">'.$row['hGUID'].'</a></td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$row2['name'].'</td><td>'.$row['processID'].'</td><td><span class="label label-warning">'.$row['name'].'</span></td><td>'.$row['CommandLine'].'</td><td>'.$row['location'].'</td><td>'.$row['sessionID'].'</td></tr>';  
			}
		}  
	}	
	return $data;
}
function UserAccessCommand($conn, $pattern, $sqlFilter){	
	$tsql = "SELECT hu.huntingGUID,
			  sa.serverName serverName
			  ,[strategy]
			  ,[securityParameter]
		  FROM [NOAH].[dbo].[OSPrivilegeAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu		  
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  $sqlFilter
		  AND (securityParameter LIKE '%".$pattern."%' OR strategy LIKE '%".$pattern."%')
		";		
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>OS Privileges</b> '.$row['strategy'].'</td><td>'.$row['securityParameter'].'</td></tr>';  										
		}  
	}	
	
	$tsql = "SELECT hu.huntingGUID,
			  sa.serverName serverName
			  ,[account]
			  ,tableInf.[shareName], rights
		  FROM [NOAH].[dbo].[ShareRightsAudited] sra, [NOAH].[dbo].[ShareAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu	
		  WHERE tableInf.serverID = sa.serverID 
		  AND hu.huntingID = sa.huntingID
		  AND tableInf.shareAuditedID = sra.shareAuditedID 
		  $sqlFilter		  		  
		  AND (account LIKE '%".$pattern."%' OR rights LIKE '%".$pattern."%')
		";				
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Share</b> '.$row['shareName'].' '.$row['rights'].'</td><td>'.$row['account'].'</td></tr>';  										
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
		  AND (runAs LIKE '%".$pattern."%' OR Name LIKE '%".$pattern."%' OR arguments LIKE '%".$pattern."%')
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Scheduled Task</b> '.$row['Name'].' ('.$row['ScheduledAction'].' - '.$row['arguments'].')</td><td>'.$row['runAs'].'</td></tr>';  										
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
		  AND (username LIKE '%".$pattern."%' OR Name LIKE '%".$pattern."%' OR CommandLine LIKE '%".$pattern."%')
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$launchStringTruncated = truncateForceLength($row['CommandLine'],80);
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Process</b> '.$row['Name'].' ('.$launchStringTruncated.')</td><td>'.$row['domain'].'\\'.$row['username'].'</td></tr>';  										
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
		  AND (UserName LIKE '%".$pattern."%' OR MRU LIKE '%".$pattern."%')
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>MRU</b> '.$row['MRU'].'</td><td>'.$row['UserName'].'</td></tr>';  										
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
		  AND (username LIKE '%".$pattern."%' OR Unicode_Link_Name LIKE '%".$pattern."%')
		";			
	$getUserAccess = sqlsrv_query($conn, $tsql); 
	if ( $getUserAccess === false)  
	die( print_r( sqlsrv_errors(), true));			
	if(sqlsrv_has_rows($getUserAccess)) {  
		while( $row = sqlsrv_fetch_array( $getUserAccess, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td><b>Recent Docs</b> '.$row['Unicode_Link_Name'].'</td><td>'.$row['UserName'].'</td></tr>';  										
		}  
	}
	
	return $data;
}

if (isset($_POST['server'])) {	
	$searchParent = htmlentities(trim(htmlspecialchars(addslashes($_POST['server']))));
	$server = $searchParent;
	$objectReturn = '';																				
	$getProcess = SuspiciousParentChild($conn, $server,"cmd","powershell");
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"wmiprvse","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"explorer","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"windowsupdatebox","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"wscript","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"taskeng","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"winword","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}$getProcess = SuspiciousParentChild($conn, $server,"cab","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"java","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"excel","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"splunkd","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChild($conn, $server,"msaccess","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}  
	echo $objectReturn;
}

if (isset($_POST['tree'])) {	
	$searchTree = htmlentities(trim(htmlspecialchars(addslashes($_POST['tree']))));	
	$pattern = $searchTree;
	$objectReturn = '';																				
	$getProcess = SuspiciousProcessTreeCommand($conn, $pattern);
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['processtreebyserver'])) {	
	$processtreebyserver = htmlentities(trim(htmlspecialchars(addslashes($_POST['processtreebyserver']))));		
	$objectReturn = '';																				
	$getProcess = SuspiciousProcessTreeByServerCommand($conn, $processtreebyserver, $sqlFilter);	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['persistence'])) {	
	$searchMD5 = htmlentities(trim(htmlspecialchars(addslashes($_POST['persistence']))));	
	$pattern = $searchMD5;
	$objectReturn = '';																				
	$getProcess = SuspiciousAutorunMD5Command($conn, $pattern, $sqlFilter);
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['prefetch'])) {	
	$programname = htmlentities(trim(htmlspecialchars(addslashes($_POST['prefetch']))));		
	$extension = htmlentities(trim(htmlspecialchars(addslashes($_POST['extension']))));		
	$objectReturn = '';																				
	$getProcess = SuspiciousPrefetchCommand($conn, $programname, $extension);
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['mru'])) {	
	$mru = htmlentities(trim(htmlspecialchars(addslashes($_POST['mru']))));	
	$pattern = $mru;	
	$objectReturn = '';																				
	$getProcess = SuspiciousRunMRUCommand($conn, $pattern);
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['bha'])) {	
	$bha = htmlentities(trim(htmlspecialchars(addslashes($_POST['bha']))));		

	$objectReturn = '';				
	$getProcess = SuspiciousBrowserHistoryCommand($conn, $bha, $sqlFilter);
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if (isset($_POST['useraccess'])) {	
	$access = htmlentities(trim(htmlspecialchars(addslashes($_POST['useraccess']))));		
	$objectReturn = '';																				
	$getUserAccess = UserAccessCommand($conn, $access, $sqlFilter);
	
	foreach($getUserAccess as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}

if(isset($_POST["launch"])) {	
  // Path to the PowerShell script. Remember double backslashes:
	$psScriptPath = "C:\\wamp\\www\NOAH\\Backend\\NOAH.ps1";
 
	// Execute the PowerShell script, passing the parameters:
	$query = shell_exec("powershell -command $psScriptPath -All -HuntDescription 'Hello BlackHat!' < NUL");
	echo $query; 
 }	

if (isset($_POST['amcache'])) {	
	$amcache = htmlentities(trim(htmlspecialchars(addslashes($_POST['amcache']))));
	
	$objectReturn = '';				
	$getAmcache = SuspiciousAMCacheCommand($conn, $amcache, $sqlFilter);
	
	foreach($getAmcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['netstat'])) {	
	$netstat = htmlentities(trim(htmlspecialchars(addslashes($_POST['netstat']))));
	
	$objectReturn = '';				
	$getNetstat = SuspiciousNetstatCommand($conn, $netstat, $sqlFilter);
	
	foreach($getNetstat as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['dnscache'])) {	
	$dnscache = htmlentities(trim(htmlspecialchars(addslashes($_POST['dnscache']))));
	
	$objectReturn = '';				
	$getNetstat = SuspiciousDNSCacheCommand($conn, $dnscache, $sqlFilter);
	
	foreach($getNetstat as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['softwareinstalled'])) {	
	$softwareinstalled = htmlentities(trim(htmlspecialchars(addslashes($_POST['softwareinstalled']))));
	
	$objectReturn = '';				
	$getNetstat = SuspiciousSoftwareInstalledCommand($conn, $softwareinstalled, $sqlFilter);
	
	foreach($getNetstat as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['shimcache'])) {	
	$shimcache = htmlentities(trim(htmlspecialchars(addslashes($_POST['shimcache']))));
	
	$objectReturn = '';				
	$getShimcache = SuspiciousShimcacheCommand($conn, $shimcache, $sqlFilter);
	
	foreach($getShimcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['linkfile'])) {	
	$linkfile = htmlentities(trim(htmlspecialchars(addslashes($_POST['linkfile']))));
	
	$objectReturn = '';				
	$getShimcache = SuspiciousLinkFileCommand($conn, $linkfile, $sqlFilter);
	
	foreach($getShimcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['recentdocs'])) {	
	$recentdocs = htmlentities(trim(htmlspecialchars(addslashes($_POST['recentdocs']))));
	
	$objectReturn = '';				
	$getShimcache = SuspiciousRecentDocsCommand($conn, $recentdocs, $sqlFilter);
	
	foreach($getShimcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['usbhistory'])) {	
	$usbhistory = htmlentities(trim(htmlspecialchars(addslashes($_POST['usbhistory']))));
	
	$objectReturn = '';				
	$getShimcache = SuspiciousUSBHistoryCommand($conn, $usbhistory, $sqlFilter);
	
	foreach($getShimcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['runmru'])) {	
	$runmru = htmlentities(trim(htmlspecialchars(addslashes($_POST['runmru']))));
	
	$objectReturn = '';				
	$getShimcache = SuspiciousRunMRUCommand($conn, $runmru, $sqlFilter);
	
	foreach($getShimcache as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
if (isset($_POST['service'])) {	
	$service = htmlentities(trim(htmlspecialchars(addslashes($_POST['service']))));
	
	$objectReturn = '';				
	$getService = SuspiciousServicesCommand($conn, $service, $sqlFilter);
	
	foreach($getService as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;	
}
?>