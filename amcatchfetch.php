<?php
if(isset($_POST["page"])){						
						$page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH); //filter number
						if(!is_numeric($page_number)){die('Invalid page number!');} //incase of invalid page number
					}else{
						$page_number = 1; //if there's no page number, set it to 1
					}
					$total = 0;$item_per_page = 10;								
										
						if($huntGUID != '' and $serverID != '') {
							$tsqlAMCacheCount = "select count(*) Total FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
								WHERE hu.huntingGUID = '$huntGUID'
								AND sa.serverID = $serverID
								AND aa.serverID = sa.serverID
								AND hu.huntingID = sa.huntingID";
								$getAmcache = sqlsrv_query($conn, $tsqlAMCacheCount); 
							if ( $getAmcache === false)  
							die( print_r( sqlsrv_errors(), true));	
							$data = array();
							$data['data'] = '';
							if(sqlsrv_has_rows($getAmcache)) { 							
							if( $row = sqlsrv_fetch_array( $getAmcache, SQLSRV_FETCH_ASSOC)) {  
								$total = $row['Total'];
							}
							//break records into pages
							$total_pages = ceil($total/$item_per_page);

							//position of records
							$page_position = (($page_number-1) * $item_per_page);	
							$tsqlAMCache = "SELECT 						
								hu.huntingGUID, sa.serverName,[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
							  FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
								WHERE hu.huntingGUID = '$huntGUID'
								AND sa.serverID = $serverID
								AND aa.serverID = sa.serverID
								AND hu.huntingID = sa.huntingID
								GROUP BY huntingGUID, serverName,[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
								ORDER BY [amcacheAuditedID]
								OFFSET ".$page_position." ROWS
								FETCH NEXT ".$item_per_page." ROWS ONLY
								";
							}	
						}
						else {
							$tsqlAMCacheCount = "select count(*) Total FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
								WHERE aa.serverID = sa.serverID
								AND sa.huntingID = hu.huntingID";
								$getAmcache = sqlsrv_query($conn, $tsqlAMCacheCount); 
							if ( $getAmcache === false)  
							die( print_r( sqlsrv_errors(), true));	
							$data = array();
							$data['data'] = '';
							if(sqlsrv_has_rows($getAmcache)) { 							
							if( $row = sqlsrv_fetch_array( $getAmcache, SQLSRV_FETCH_ASSOC)) {  
								$total = $row['Total'];
							}
							//break records into pages
							$total_pages = ceil($total/$item_per_page);

							//position of records
							$page_position = (($page_number-1) * $item_per_page);	
							$tsqlAMCache = "SELECT
								hu.huntingGUID, sa.serverName,[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
							  FROM [NOAH].[dbo].[AmcacheAudited] aa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
								WHERE aa.serverID = sa.serverID
								AND sa.huntingID = hu.huntingID
								GROUP BY huntingGUID, serverName,[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]
								OFFSET ".$page_position." ROWS
								FETCH NEXT ".$item_per_page." ROWS ONLY
								";
							}
						}
						$getAmcache = sqlsrv_query($conn, $tsqlAMCache); 
						if ( $getAmcache === false)  
						die( print_r( sqlsrv_errors(), true));	
						$data = array();
						$data['data'] = '';
						if(sqlsrv_has_rows($getAmcache)) { 							
							while( $row = sqlsrv_fetch_array( $getAmcache, SQLSRV_FETCH_ASSOC)) {  																	
								echo '<tr><td>'.$row['huntingGUID'].'</td><td>'.$row['serverName'].'</td><td>'.$row['Associated'].'</td><td>'.$row['ProgramName'].'</td><td>'.$row['ProgramID'].'</td><td>'.$row['VolumeID'].'</td><td>'.$row['SHA1'].'</td></tr>';  															
							}  
						}