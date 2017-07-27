<?php 
$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
}