<?php
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
        $children[$i]['children'] = getChildren($row['processID'],$conn);
    $i++;
    }
return $children;
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
				echo 'childrenDropLevel: 2,
						HTMLclass: "nodeExample2",
						text: {
							name: "'.$a.'",                      
						},';   
						switch ($a) {
							case 'explorer.exe':
								echo 'image: "explorer.png"';
								break;
							case 'cmd.exe':
								echo 'image: "cmd.png"';
								break;
							case 'notepad.exe':
								echo 'image: "notepad.png"';
								break;
							case 'calc.exe':
								echo 'image: "calculator.png"';
								break;	
							case 'conhost.exe':
								echo 'image: "cmd.png"';
								break;	
							case 'powershell.exe':
								echo 'image: "powershell.png"';
								break;
							case 'powershell_ise.exe':
								echo 'image: "powershell.png"';
								break;	
							case 'winword.exe':
								echo 'image: "winword.png"';
								break;
						}							
				echo '},';
			}
		}
		return;
	}	
	foreach($a as $k=>$v) {
		printAll($k,$v);		
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

$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
}

if (isset($_GET['processID'])) {	
	$GETProcessID = htmlentities(trim(htmlspecialchars(addslashes($_GET['processID']))));	
	$GETProcessID = $GETProcessID;	
	$GETServerID = htmlentities(trim(htmlspecialchars(addslashes($_GET['serverID']))));	
	$GETServerID = $GETServerID;	
}

echo '

<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <title> NOAH - Process Tree </title>
    <link rel="stylesheet" href="Treant.css">
    <link rel="stylesheet" href="custom-colored.css">
    
</head>
<body>
    <div class="chart" id="custom-colored"> --@-- </div>    
    
    <script>
	var config = {
        container: "#custom-colored",
        
		rootOrientation: "WEST",

		nodeAlign: "BOTTOM",
		
        connectors: {
            type: "curve",
			style: {
				"stroke-width": 1
			}
        },
        node: {
            HTMLclass: "nodeExample1"
        }
    },';

$query = "SELECT	
				  pta.serverID				  
				  ,[processID]
				  ,[parentProcessId]
				  ,[name]		
				  ,[level]
			  FROM [NOAH].[dbo].[ProcessTreeAudited] pta, [NOAH].[dbo].[ServerAudited] sa
			  WHERE pta.serverID = $GETServerID
			  AND processID = $GETProcessID
			  AND pta.serverID = sa.serverID
			  ";
$result = sqlsrv_query($conn, $query);     
if( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {  					
	echo 'p'.$GETProcessID;	
	echo '
	 = {
		childrenDropLevel: 2,
		HTMLclass: "nodeExample2",
        text: {
            name: "'.$row['name'].'",                      
        },';
	switch ($row['name']) {
		case 'explorer.exe':
			echo 'image: "explorer.png"';
			break;
		case 'cmd.exe':
			echo 'image: "cmd.png"';
			break;
		case 'powershell.exe':
			echo 'image: "powershell.png"';
			break;
		case 'winword.exe':
			echo 'image: "winword.png"';
			break;
	}			
    echo '    
    },';

}
$array = getChildren($GETProcessID,$conn);

printAll(0,$array);
echo '
chart_config = [
        config,
	
		p'.$GETProcessID.',';

echo configAll(0,$array);
echo '
    ];
	</script>

    <script>
        new Treant( chart_config );
    </script>
	    </script>
</body>
</html>';