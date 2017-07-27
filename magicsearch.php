<?php
$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
}



if (isset($_POST['magic'])) {	
	$magicRequest = htmlentities(trim(htmlspecialchars(addslashes($_POST['magic']))));	
	$pattern = $magicRequest;
		
	$pattern = preg_replace('/\s+/', ' ', $pattern);
	$pattern = explode(" ", $pattern);
	//echo count($pattern);
		
	for($i = 0; $i < count($pattern); ++$i) {
		echo $pattern[$i];		
		if (strcasecmp($pattern[$i], 'show') == 0) {
			$i++;
			switch (strtolower($pattern[$i])) {
				case 'process':
					$table="ProcessTreeAudited";
					break;
				case 2:
					$browserType="Chrome";
					break;
				case 3:
					$browserType="Firefox";
					break;
			}
			$tsql = "Select * FROM ".$table;
		}
	}
	
	$objectReturn = '';																				
						
	$getRunMRU = sqlsrv_query($conn, $tsql); 
	if ( $getRunMRU === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getRunMRU)) {  
		while( $row = sqlsrv_fetch_array( $getRunMRU, SQLSRV_FETCH_ASSOC)) { 
			$data['data'] .= '<tr><td>';
			foreach($row as $key => $var) {
				$data['data'] .= $var;
			}
			$data['data'] .= '</td></tr>';  									
		}  
	}		
	
	foreach($data as $result) {						
		$objectReturn .= $result;
	}
	
	echo $objectReturn;
}
?>