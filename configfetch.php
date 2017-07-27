<?php
if (isset($_POST['huntGUID'])) {	
	$huntGUID = htmlentities(trim(htmlspecialchars(addslashes($_POST['huntGUID']))));
}
else {
	if (!isset($huntGUID)) {
		$huntGUID='';
	}
}
if (isset($_POST['serverID'])) {
	$serverID = htmlentities(trim(htmlspecialchars(addslashes($_POST['serverID']))));	
}
else {
	if (!isset($serverID)) {
		$serverID='';
	}
}
if (isset($_POST['pageSwitch'])) {	
	$pageSwitch = htmlentities(trim(htmlspecialchars(addslashes($_POST['pageSwitch']))));
}
else {
	if (!isset($pageSwitch)) {
		$pageSwitch='';
	}
}

if(isset($_POST["page"])){						
	$page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH); //filter number
	if(!is_numeric($page_number)){die('Invalid page number!');} //incase of invalid page number
}else{
	$page_number = 1; //if there's no page number, set it to 1
}
$total = 0;$item_per_page = 10;								
$sqlFilter = '';
if($huntGUID != '') {
	$sqlFilter = " AND hu.huntingGUID = '$huntGUID'";
}
if($serverID != '') {
	$sqlFilter = " AND sa.serverID = $serverID";
}