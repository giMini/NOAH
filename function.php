<?php

function MaliciousProcess ($conn){		
	$data = array();
	$data['data'] = '';
	$tsql = "SELECT [SuspiciousElementID],[resource],[VT],[permalink] FROM [NOAH].[dbo].[SuspiciousElement] WHERE VT LIKE 1";
	$getSuspiciousElement = sqlsrv_query($conn, $tsql); 
	if ( $getSuspiciousElement === false)  
	die( print_r( sqlsrv_errors(), true));
	if(sqlsrv_has_rows($getSuspiciousElement)) {  
		while( $rowSuspiciousElement = sqlsrv_fetch_array( $getSuspiciousElement, SQLSRV_FETCH_ASSOC)) { 
			// Running process
			$tsqlProcess = "SELECT pta.serverID, sa.serverName as serverName, hu.huntingGUID,[processID],[parentProcessId],[name],[sessionID],[location],[CommandLine],[Decoded],[Description]
				FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
				WHERE (hash LIKE '".$rowSuspiciousElement['resource']."'
				AND NOT EXISTS ( SELECT 1 FROM [NOAH].[dbo].[whiteListedProcess] wlp WHERE wlp.resource LIKE '".$rowSuspiciousElement['resource']."'))
				AND pta.serverID = sa.serverID
				AND sa.huntingID = hu.huntingID";					
			$getProcess = sqlsrv_query($conn, $tsqlProcess); 
			while( $row = sqlsrv_fetch_array( $getProcess, SQLSRV_FETCH_ASSOC)) {				
				$parentProcessSql = "SELECT name FROM [NOAH].[dbo].[ProcessTreeAudited] WHERE processID LIKE ".$row['parentProcessId']." AND serverID LIKE ".$row['serverID'];							
				$getParentProcess = sqlsrv_query($conn, $parentProcessSql);  
				if ( $getParentProcess === false)  
				die( print_r( sqlsrv_errors(), true));
				if( $rowParentName = sqlsrv_fetch_array( $getParentProcess, SQLSRV_FETCH_ASSOC)) { 
					$parentName = $rowParentName['name'];
				}
				else {				
					$parentName = 'Unknown';
				}			
				$commandlineTruncated = truncateForceLength($row['CommandLine'],150);	
				$data['data'] .= '<tr><td>Running Process</td><td><a href="huntdetails.php?hunt='.$row['huntingGUID'].'">'.$row['huntingGUID'].'</a></td><td>'.$row['serverName'].'</td><td><a href="'.$rowSuspiciousElement['permalink'].'" target=_blank>'.$rowSuspiciousElement['resource'].'</a></td><td><span class="label label-danger">'.$row['name'].'</span></td><td>'.$row['location'].'</td></tr>';  					
			} 
			// Autoruns
			$tsqlProcess = "SELECT hu.huntingGUID, sa.serverName, sa.serverID, autorunAuditedID, Suspicious, MD5, LaunchString, EntryLocation, [Entry], Signer, Category
				FROM [NOAH].[dbo].[AutorunAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
				WHERE (MD5 LIKE '".$rowSuspiciousElement['resource']."'
				AND NOT EXISTS ( SELECT 1 FROM [NOAH].[dbo].[whiteListedProcess] wlp WHERE wlp.resource LIKE '".$rowSuspiciousElement['resource']."'))
				AND tableInf.serverID = sa.serverID
				AND sa.huntingID = hu.huntingID";	
			$getProcess = sqlsrv_query($conn, $tsqlProcess); 
			while( $row = sqlsrv_fetch_array( $getProcess, SQLSRV_FETCH_ASSOC)) {
				$launchStringTruncated = truncateForceLength($row['LaunchString'],80);								
				$data['data'] .= '<tr><td>'.$row['Category'].'</td><td><a href="huntdetails.php?hunt='.$row['huntingGUID'].'">'.$row['huntingGUID'].'</a></td><td>'.$row['serverName'].'</td><td><a href="'.$rowSuspiciousElement['permalink'].'" target=_blank>'.$rowSuspiciousElement['resource'].'</a></td><td>'.$row['Entry'].'</td><td>'.$launchStringTruncated.'</td><td>'.$row['Signer'].'</td></tr>';  							
			} 
			
			// ScheduledTask
			
			
			// AMCache
			$tsqlInformation = "SELECT hu.huntingGUID, sa.serverName,[ProgramName], [FullPath]
				FROM [NOAH].[dbo].[AmcacheAudited] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
				WHERE (SHA1 LIKE '".$rowSuspiciousElement['resource']."'
				AND NOT EXISTS ( SELECT 1 FROM [NOAH].[dbo].[whiteListedProcess] wlp WHERE wlp.resource LIKE '".$rowSuspiciousElement['resource']."'))
				AND tableInf.serverID = sa.serverID
				AND sa.huntingID = hu.huntingID";				
			$gettsqlInformation = sqlsrv_query($conn, $tsqlInformation); 
			if(sqlsrv_has_rows($gettsqlInformation)) {  
				while( $row = sqlsrv_fetch_array( $gettsqlInformation, SQLSRV_FETCH_ASSOC)) {										
					echo '<tr><td>AMCache</td><td><a href="huntdetails.php?hunt='.$row['huntingGUID'].'">'.$row['huntingGUID'].'</a></td><td>'.$row['serverName'].'</td><td><a href="'.$rowSuspiciousElement['permalink'].'" target=_blank>'.$rowSuspiciousElement['resource'].'</a></td><td>'.$row['ProgramName'].'</td><td>'.$row['FullPath'].'</td></tr>';
				} 
			}
		}
	}		

	return $data;
}

function SuspiciousEncodedCommand ($conn){						
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT pta.serverID, sa.serverName as serverName,
	hu.huntingGUID as hGUID,[level],[processID],[parentProcessId],[name],[sessionID],
	[handles],[creationDate],[location],[CommandLine],[Decoded],[Suspicious],
	[Description],[hash] 
	FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
	WHERE 
	 pta.serverID = sa.serverID
	AND sa.huntingID = hu.huntingID
	AND Suspicious = 1";
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
			if( $rowParentName = sqlsrv_fetch_array( $getParentProcess, SQLSRV_FETCH_ASSOC)) { 
				$parentName = $rowParentName['name'];
			}
			else {				
				$parentName = 'Unknown';
			}
			$tsql2 = "SELECT [whiteListedProcessID]
			FROM [NOAH].[dbo].[WhiteListedProcess] wlp
			WHERE 
			location LIKE '".$row['location']."'
			OR name LIKE '".$row['name']."'
			OR resource LIKE '".$row['hash']."'";					
			$getWhiteListed = sqlsrv_query($conn, $tsql2); 
			if ( $getWhiteListed === false)  
			die( print_r( sqlsrv_errors(), true));	
			if( ! $row2 = sqlsrv_fetch_array( $getWhiteListed, SQLSRV_FETCH_ASSOC)) { 
				$commandlineTruncated = truncateForceLength($row['CommandLine'],150);	
				$data['data'] .= '<tr><td><a href="huntdetails.php?hunt='.$row['hGUID'].'">'.$row['hGUID'].'</a></td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><span class="label label-warning">'.$row['name'].'</span></td><td>'.$commandlineTruncated.'</td><td>'.$row['Decoded'].'</td><td>'.$row['location'].'</td><td>'.$row['sessionID'].'</td></tr>';  
			}
		}  
	}	
	return $data;
}

function SuspiciousParentChildD($conn, $parent, $pattern){						
	$tsql = "SELECT pta.serverID, sa.serverName as serverName,
	hu.huntingGUID as hGUID,[level],[processID],[parentProcessId],[name],[sessionID],
	[handles],[creationDate],[location],[CommandLine],[Decoded],[Suspicious],
	[Description],[hash] 
	FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu						
	WHERE parentProcessId IN (SELECT processID FROM [NOAH].[dbo].[ProcessTreeAudited] pta2 WHERE name LIKE '%".$parent."%' AND pta.serverID = pta2.serverID)	
	AND pta.serverID = sa.serverID
	AND sa.huntingID = hu.huntingID 
	AND name LIKE '%".$pattern.".exe%'";

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
				$data['data'] .= '<tr><td><a href="huntdetails.php?hunt='.$row['hGUID'].'">'.$row['hGUID'].'</a></td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$row2['name'].'</td><td>'.$row['processID'].'</td><td><span class="label label-warning">'.$row['name'].'</span></td><td>'.$row['CommandLine'].'</td><td>'.$row['location'].'</td><td>'.$row['sessionID'].'</td></tr>';  
			}
		}  
	}	
	return $data;
}

function SuspiciousOrphanedParentChild($conn, $pattern){						
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT pta.serverID, sa.serverName as serverName,
	hu.huntingGUID as hGUID,[level],[processID],[parentProcessId],[name],[sessionID],
	[handles],[creationDate],[location],[CommandLine],[Decoded],[Suspicious],
	[Description],[hash] 
	FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
	WHERE pta.serverID = sa.serverID
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
			if(!( $rowParentName = sqlsrv_fetch_array( $getParentProcess, SQLSRV_FETCH_ASSOC))) { 
							
				$parentName = 'Unknown';
			
				$commandlineTruncated = truncateForceLength($row['CommandLine'],80);	
				$data['data'] .= '<tr><td><a href="huntdetails.php?hunt='.$row['hGUID'].'">'.$row['hGUID'].'</a></td><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><span class="label label-warning">'.$row['name'].'</span></td><td>'.$commandlineTruncated.'</td><td>'.$row['location'].'</td><td>'.$row['sessionID'].'</td></tr>';  											
			}
		}  
	}	
	return $data;
}

function BrowserHistoryCommand ($conn){						
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT huntingGUID
		  ,sa.[serverName]
		  ,[BrowserType]
		  ,[UserName]
		  ,[URL]
	FROM [NOAH].[dbo].[BrowserHistoryAudited] bha, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
	WHERE bha.serverID = sa.serverID
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

function RunMRUCommand ($conn){						
	//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
	$tsql = "SELECT huntingGUID
		  ,sa.[serverName]
		  ,[UserName]
		  ,[MRU]
	FROM [NOAH].[dbo].[RunMRUsAudited] rma, [NOAH].[dbo].ServerAudited sa, [NOAH].[dbo].Hunt hu
	WHERE rma.serverID = sa.serverID
	AND sa.huntingID = hu.huntingID";
	$getProcessTree = sqlsrv_query($conn, $tsql); 
	if ( $getProcessTree === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcessTree)) {  
		while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  		
			//if(strpos($row['ProcessName'], 'powershell') !== FALSE){							
				$data['data'] .= '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['UserName'].'</td><td>'.$row['MRU'].'</td></tr>';  							
			//}
		}  
	}	
	return $data;
}

function RetrieveHunt ($conn){							
	$tsql = "SELECT [huntingGUID]
      ,[huntingDate]
      ,[huntingState]
      ,[huntingComputerNumber]
      ,[huntingDescription]
	FROM [NOAH].[dbo].[Hunt] hu
	ORDER BY huntingDate DESC";
	$getHunt = sqlsrv_query($conn, $tsql); 
	if ( $getHunt === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getHunt)) {  
		while( $row = sqlsrv_fetch_array( $getHunt, SQLSRV_FETCH_ASSOC)) {  
			$huntDate = date_format($row['huntingDate'], 'Y-m-d H:i:s');
			if($row['huntingState'] == 1){
				$huntingState = '<span class="label label-success">Completed</span>';
			}
			else {
				$huntingState = '<span class="label label-warning">Pending</span>';
			}
			$data['data'] .= '<tr><td>'.$huntingState.'</td><td><a href="huntdetails.php?hunt='.$row['huntingGUID'].'">'.$row['huntingGUID'].'</a></td><td>'.$huntDate.'</td><td>'.$row['huntingComputerNumber'].'</td><td>'.$row['huntingDescription'].'</td></tr>';  										
		}  
	}	
	return $data;
}

function RetrieveHuntDetails ($conn, $huntingID){							
	$tsql = "SELECT [serverID]
      ,[serverName]
      ,[domain]
      ,[role]
      ,[HW_Make]
      ,[HW_Model]
      ,[HW_Type]
      ,[cpuCount]
      ,[memoryGB]
      ,[operatingSystem]
      ,[servicePackLevel]
      ,[biosName]
      ,[biosVersion]
      ,[hardwareSerial]
      ,[timeZone]
      ,[wmiVersion]
      ,[virtualMemoryName]
      ,[virtualMemoryCurrentUsage]
      ,[virtualMermoryPeakUsage]
      ,[virtualMemoryAllocatedBaseSize]
	  ,hu.huntingGUID
  FROM [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
  WHERE hu.huntingID = $huntingID
  AND sa.huntingID = hu.huntingID";
	$getHunt = sqlsrv_query($conn, $tsql); 
	if ( $getHunt === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getHunt)) {  
		while( $row = sqlsrv_fetch_array( $getHunt, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td><a href="hostdetails.php?hunt='.$row['huntingGUID'].'&serverID='.$row['serverID'].'">'.$row['serverName'].'</a></td><td>'.$row['domain'].'</td><td>'.$row['role'].'</td><td>'.$row['HW_Make'].'</td><td>'.$row['HW_Model'].'</td></tr>';  										
		}  
	}	
	return $data;
}

function RetrieveHostDetails ($conn, $huntingID){							
	$tsql = "SELECT [serverID]
      ,[serverName]
      ,[domain]
      ,[role]
      ,[HW_Make]
      ,[HW_Model]
      ,[HW_Type]
      ,[cpuCount]
      ,[memoryGB]
      ,[operatingSystem]
      ,[servicePackLevel]
      ,[biosName]
      ,[biosVersion]
      ,[hardwareSerial]
      ,[timeZone]
      ,[wmiVersion]
      ,[virtualMemoryName]
      ,[virtualMemoryCurrentUsage]
      ,[virtualMermoryPeakUsage]
      ,[virtualMemoryAllocatedBaseSize]
	  ,hu.huntingGUID
  FROM [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
  WHERE hu.huntingID = $huntingID
  AND sa.huntingID = hu.huntingID";
	$getHunt = sqlsrv_query($conn, $tsql); 
	if ( $getHunt === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getHunt)) {  
		while( $row = sqlsrv_fetch_array( $getHunt, SQLSRV_FETCH_ASSOC)) {  
			$data['data'] .= '<tr><td><a href="hostdetails.php?hunt='.$row['huntingGUID'].'&serverID='.$row['serverID'].'">'.$row['serverName'].'</a></td><td>'.$row['domain'].'</td><td>'.$row['role'].'</td><td>'.$row['HW_Make'].'</td><td>'.$row['HW_Model'].'</td></tr>';  										
		}  
	}	
	return $data;
}

function getChildren($parent, $conn) {
    $query = "SELECT	
				  pta.serverID				  
				  ,[processID]
				  ,[parentProcessId]
				  ,[name]		
				  ,[level]
			  FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa
			  WHERE pta.serverID = 1
			  AND parentProcessId = $parent
			  AND pta.serverID = sa.serverID
			  ORDER by level asc";
    $result = sqlsrv_query($conn, $query); 
    $children = array();
    $i = 0;
    while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {  			
        $children[$i] = array();        		
		$children[$i]['processID'] = $row['processID'];
		$children[$i]['parentProcessId'] = $row['parentProcessId'];
		//$children[$i]['level'] = $row['level'];				
		$children[$i]['name'] = $row['name'];		
		$children[$i]['link1'] = $row['serverID'];
		$children[$i]['link2'] = $row['processID'];		
		$children[$i]['name2'] = $row['name'];	
        $children[$i]['children'] = getChildren($row['processID'],$conn);
    $i++;
    }
return $children;
}

function printTree($tree) {
    if(!is_null($tree) && count($tree) > 0) {        
        foreach($tree as $node) {
            echo '"children": [{"name": "'.$node['name'].'"';
            printTree($node['children']).",";
            echo ']},';
        }        
    }
}

function getChildrenP($parent, $conn) {
    $query = "SELECT	
				  pta.serverID				  
				  ,[processID]
				  ,[parentProcessId]
				  ,[name]		
				  ,[level]
			  FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa
			  WHERE pta.serverID = 1
			  AND parentProcessId = $parent
			  AND pta.serverID = sa.serverID
			  ORDER by level asc";
    $result = sqlsrv_query($conn, $query); 
    $children = array();
    $i = 0;
    while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {
		$query2 = "SELECT					  
				  [name]						  
			  FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa
			  WHERE pta.serverID = 1
			  AND processId = ".$row['parentProcessId']."
			  AND pta.serverID = sa.serverID
			  ORDER by level asc";
		$result2 = sqlsrv_query($conn, $query2); 
		$children[$i] = array();        						      
		//$children[$i]['processID'] = $row['processID'];
		//$children[$i]['parentProcessId'] = $row['parentProcessId'];		
		if( $row2 = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) {		
			$children[$i]['parentname'] = $row2['name']." (".$row['parentProcessId'].")";				
		}
		else {
			$children[$i]['parentname'] = $row['parentProcessId'];				
		} 		
		$children[$i]['name'] = $row['name']." (".$row['processID'].")";				
        $children[$i]['children'] = getChildrenP($row['processID'],$conn);
    $i++;
    }
return $children;
}

function imgGraph($program) {
	$img='';
	$graph = 'graph/';
	switch ($program) {
		case 'explorer.exe':
			$img = '"'.$graph.'explorer.png"';
			break;
		case 'cmd.exe':
			$img = '"'.$graph.'cmd.png"';
			break;
		case 'notepad.exe':
			$img = '"'.$graph.'notepad.png"';
			break;
		case 'calc.exe':
			$img = '"'.$graph.'calculator.png"';
			break;	
		case 'conhost.exe':
			$img = '"'.$graph.'cmd.png"';
			break;	
		case 'powershell.exe':
			$img = '"'.$graph.'powershell.png"';
			break;
		case 'powershell_ise.exe':
			$img = '"'.$graph.'powershell.png"';
			break;	
		case 'winword.exe':
			$img = '"'.$graph.'winword.png"';
			break;
		default:
			$img = '"'.$graph.'app.png"';
			break;		
	}		
	return $img;
}

function printAll($k,$a) {
	if (!is_array($a)) {	
		if($k == 'processID') {			
			echo 'p'.$a.' = {';	
		}
		else {
			if($k == 'parentProcessId') {
				echo 'parent: p'.$a.',';
			}
			else {								
				if($k == 'name') {
					echo 'childrenDropLevel: 2,
								HTMLclass: "nodeExample2",							
								text: {
									name: "'.$a.'",
									';   					
				}	
				else {
					if($k == 'link1') {
						echo '					
						contact: { 
								val: ">>>",
								href: "graph.php?serverID='.$a;						
					}
					else {
						if($k == 'link2') {
							echo '&processID='.$a.'", 
								target: "_self",								
						}},';
						}
						else {
							if ($imgr = imgGraph($a)) {
								echo $imgr;
							}
							echo '},';
						}
					}
				}				
			}								
		}
		return;
	}	
	foreach($a as $k=>$v) {
		printAll($k,$v);		
	}	
}

function printJson($k,$a) {
$temp = '';
if (!is_array($a)) {	
		if($k == 'processID') {					
			echo 'p'.$a.' = {';	
		}
		else {
			if($k == 'parentProcessId') {
				$temp = $a;
				echo 'parent: p'.$a.',';
			}
			else {								
				if($k == 'name') {
					echo 'childrenDropLevel: 2,
								HTMLclass: "nodeExample2",							
								text: {
									name: "'.$a.'",
									';   					
				}							
			}								
		}
		return;
	}	
	foreach($a as $k=>$v) {
		printJson($k,$v);		
	}
}

function configAll($k,$a) {
	if (!is_array($a)) {	
		if($k == 'processID') {			
			echo 'p'.$a.',';	
		}		
		return;
	}	
	foreach($a as $k=>$v) {
		configAll($k,$v);		
	}	
}

// processtreeserver.php
function ProcessTreeCommand ($conn, $huntGUID, $serverID){					
	if($huntGUID != '' and $serverID != '') {
		$tsqlProcessTree = "SELECT 
			fpbss.serverID, sa.serverName,[ProcessName],[processID],[parentProcessId]
			FROM [NOAH].[dbo].[FlatProcessByServerStat] fpbss, [NOAH].[dbo].[ServerAudited] sa,
			[NOAH].[dbo].[Hunt] hu
			WHERE hu.huntingGUID = '$huntGUID'
			AND sa.serverID = $serverID
			AND fpbss.serverID = sa.serverID
			AND hu.huntingID = sa.huntingID
			ORDER by level";
	}
	else {
		$tsqlProcessTree = "SELECT 
			fpbss.serverID, sa.serverName,[ProcessName],[processID],[parentProcessId]
			FROM [NOAH].[dbo].[FlatProcessByServerStat] fpbss, [NOAH].[dbo].[ServerAudited] sa
			WHERE fpbss.serverID = sa.serverID
			ORDER by level";
	}
	$getProcessTree = sqlsrv_query($conn, $tsqlProcessTree); 
	if ( $getProcessTree === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getProcessTree)) {  
		while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  	
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
			$data['data'] .= '<tr><td>'.$row['serverName'].'</td><td>'.$row['parentProcessId'].'</td><td>'.$parentName.'</td><td>'.$row['processID'].'</td><td><a target=_blank href="graph.php?parentName='.$parentName.'&parentProcessId='.$row['parentProcessId'].'&serverID='.$row['serverID'].'&processID='.$row['processID'].'">'.$row['ProcessName'].'</a></td></tr>';								
		}  
	}	
	return $data;
}	

function paginate_function($item_per_page, $current_page, $total_records, $total_pages)
{
    $pagination = '';
    if($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages){ //verify total pages and current page number
        $pagination .= '<ul class="pagination2">';
        
        $right_links    = $current_page + 3; 
        $previous       = $current_page - 3; //previous link 
        $next           = $current_page + 1; //next link
        $first_link     = true; //boolean var to decide our first link
        
        if($current_page > 1){
            $previous_link = ($previous==0)?1:$previous;
            $pagination .= '<li class="first"><a href="#" data-page="1" title="First">First</a></li>'; //first link
            $pagination .= '<li><a href="#" data-page="'.$previous_link.'" title="Previous">Previous</a></li>'; //previous link
                for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
                    if($i > 0){
                        $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page'.$i.'">'.$i.'</a></li>';
                    }
                }   
            $first_link = false; //set first link to false
        }
        
        if($first_link){ //if current active page is first link
            $pagination .= '<li class="first active">'.$current_page.'</li>';
        }elseif($current_page == $total_pages){ //if it's the last active link
            $pagination .= '<li class="last active">'.$current_page.'</li>';
        }else{ //regular current link
            $pagination .= '<li class="active">'.$current_page.'</li>';
        }
                
        for($i = $current_page+1; $i < $right_links ; $i++){ //create right-hand side links
            if($i<=$total_pages){
                $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page '.$i.'">'.$i.'</a></li>';
            }
        }
        if($current_page < $total_pages){ 
                $next_link = ($i > $total_pages)? $total_pages : $i;
                $pagination .= '<li><a href="#" data-page="'.$next_link.'" title="Next">Next</a></li>'; //next link
                $pagination .= '<li class="last"><a href="#" data-page="'.$total_pages.'" title="Last">Last</a></li>'; //last link
        }
        
        $pagination .= '</ul>'; 
    }
    return $pagination; //return pagination links
}				
function truncateForceLength($string, $length, $dots = "...") {
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}

function truncate($string,$length=100,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}		

function CountArtifactByHuntAndHost($conn, $table, $sqlFilter) {
  $tsqlInformation = "select count(*) Total FROM [NOAH].[dbo].[".$table."] tableInf, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
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
		return $total;
	}
	}
	return 0;
}