<?php

include ("header.php");
if (isset($huntGUID) and isset($serverID)) {	
if($huntGUID != '') {
	$sqlFilter = " AND hu.huntingGUID = '$huntGUID'";
}
if($serverID != '') {
	$sqlFilter = " AND sa.serverID = $serverID";
}
echo '
	<h1>
        Host Details
        <small>NOAH 0.1</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="hunt.php"><i class="fa fa-cube"></i> Hunts</a></li>
        <li><a href="huntdetails.php?hunt='.$huntGUID.'"><i class="fa fa-cubes"></i> Hunt Details</a></li>
        <li class="active">Host Details</li>
      </ol>
    </section>';
		
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
	  , hu.huntingID, [huntingGUID]
      ,[huntingDate]
      ,[huntingState]
      ,[huntingComputerNumber]
      ,[huntingDescription]
  FROM [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
  WHERE hu.huntingGUID = '$huntGUID'
  AND sa.serverID = '$serverID'
  AND sa.huntingID = hu.huntingID";  
	$getHunt = sqlsrv_query($conn, $tsql); 
	if ( $getHunt === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getHunt)) {  
		$huntingState = '';$huntingGUID='';$huntDate='';$huntingComputerNumber='';$huntingDescription='';
		if( $row = sqlsrv_fetch_array( $getHunt, SQLSRV_FETCH_ASSOC)) {  
			$huntDate = date_format($row['huntingDate'], 'Y-m-d H:i:s');
			if($row['huntingState'] == 1){
				$huntingState = '<span class="label label-success">Completed</span>';
			}
			else {
				$huntingState = '<span class="label label-warning">Pending</span>';
			}
			$huntingID = $row['huntingID'];
			$huntingGUID = $row['huntingGUID'];
			$huntingComputerNumber = $row['huntingComputerNumber'];
			$huntingDescription = $row['huntingDescription'];
		}  

	echo '
	 <!-- Main content -->
    <section class="content">';
	
echo '
	  <!-- Main row -->
      <div class="row">
	  <div class="box">
	<div class="box-header">
	  <h3 class="box-title">Hunting Actions</h3>
	</div>
	<div class="box-body">
	  <p>Use the <code>buttons</code> below to <code>hunt</code>:</p>';
	 
	if((CountArtifactByHuntAndHost($conn, "AmcacheAudited", $sqlFilter)) > 0){
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=amcache" class="btn btn-app">		
		<i class="fa fa-archive"></i> AM Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "BrowserHistoryAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=browserhistory" class="btn btn-app">		
		<i class="fa fa-internet-explorer"></i> Browser History
		</a>';
	}  
	if((CountArtifactByHuntAndHost($conn, "DNSCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=dnscache" class="btn btn-app">		
		<i class="fa fa-cloud"></i> DNS Cache
		</a>';
	}	
	if((CountArtifactByHuntAndHost($conn, "ExplorerBarAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=explorerbar" class="btn btn-app">		
		<i class="fa fa-folder"></i> Explorer Bar
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "LinkFilesAudited", $sqlFilter)) > 0){	
		echo '	
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=linkfile" class="btn btn-app">		
		<i class="fa fa-file-code-o"></i> LNK Files
		</a>';
	}	
	if((CountArtifactByHuntAndHost($conn, "NetStatAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=netstat" class="btn btn-app">		
		<i class="fa fa-exchange"></i> Network Flows
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ODBCInstalledAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=obdcinstalled" class="btn btn-app">		
		<i class="fa fa-database"></i> ODBC Installed
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ODBCConfiguredAudited", $sqlFilter)) > 0){	
		echo '	
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=obdcconfigured" class="btn btn-app">		
		<i class="fa fa-database"></i> ODBC Configured
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "AutorunAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=persistence" class="btn btn-app">		
		<i class="fa fa-history"></i> Persistence
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "PrefetchAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=prefetch" class="btn btn-app">		
		<i class="fa fa-th"></i> Prefetch Files
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ProcessTreeAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=processtreebyserver" class="btn btn-app">		
		<i class="fa fa-cogs"></i> Process Tree
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RecentDocsAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=recentdocs" class="btn btn-app">		
		<i class="fa fa-file-word-o"></i> Recent Docs
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RecentFileCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=recentfilecache" class="btn btn-app">		
		<i class="fa fa-archive"></i> Recent File Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RunMRUsAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=runmru" class="btn btn-app">		
		<i class="fa fa-hdd-o"></i> Run MRU
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ScheduledTaskAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=scheduledtask" class="btn btn-app">		
		<i class="fa fa-tasks"></i> Scheduled Tasks
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ServiceAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=service" class="btn btn-app">		
		<i class="fa fa-gear"></i> Services
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ShimCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=shimcache" class="btn btn-app">		
		<i class="fa fa-archive"></i> Shim Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "InstalledProgramAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=software" class="btn btn-app">		
		<i class="fa fa-laptop"></i> Software Installed
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "USBHistoryAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&serverID='.$serverID.'&pageSwitch=usbhistory" class="btn btn-app">		
		<i class="fa fa-bolt"></i> USB Forensic
		</a>';
	}
	//if((CountArtifactByHuntAndHost($conn, "ExplorerBarAudited", $sqlFilter)) > 0){	
		echo '
		<a href="useraccess.php?hunt='.$huntingGUID.'&serverID='.$serverID.'" class="btn btn-app">		
		<i class="fa fa-users"></i> Users Access
		</a>';
	//}
	//if((CountArtifactByHuntAndHost($conn, "ExplorerBarAudited", $sqlFilter)) > 0){	
		echo '
		<a class="btn btn-app">		
		<i class="fa fa-users"></i> Users Profiles
		</a>';
	//}
echo '		
	</div>
	<!-- /.box-body -->
  </div>
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Host Details</h3><br />			 	              
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              		              	 	
        
          <address>
            <strong>'.$row['serverName'].'</strong> ('.$huntDate.')<br>			       
          </address>
        
	  <div class="col-xs-4">
          <p class="lead">General Information</p>
          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">Domain</th>
                <td>'.$row['domain'].'</td>
              </tr>              
              <tr>
                <th>Role</th>
                <td>'.$row['role'].'</td>
              </tr>
			  <tr>
                <th>Operating System</th>
                <td>'.$row['operatingSystem'].'</td>
              </tr>
              <tr>
                <th>HW Make</th>
                <td>'.$row['HW_Make'].'</td>
              </tr>
			  <tr>
                <th>HW Model</th>
                <td>'.$row['HW_Model'].'</td>
              </tr>
			  <tr>
                <th>HW Type</th>
                <td>'.$row['HW_Type'].'</td>
              </tr>
            </table>
          </div>
        </div>
        <!-- /.col -->
		<div class="col-xs-4">
          <p class="lead">Resources</p>

          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">#CPU:</th>
                <td>'.$row['cpuCount'].'</td>
              </tr>              
              <tr>
                <th>RAM</th>
                <td>'.$row['memoryGB'].'</td>
              </tr>
			  <tr>
                <th>Virtual Memory Current Usage</th>
                <td>'.$row['virtualMemoryCurrentUsage'].'</td>
              </tr>
              <tr>
                <th>Virtual Memory Peak Usage</th>
                <td>'.$row['virtualMermoryPeakUsage'].'</td>
              </tr>
			  <tr>
                <th>Virtual Memory AllocatedBase Size</th>
                <td>'.$row['virtualMemoryAllocatedBaseSize'].'</td>
              </tr>
            </table>
          </div>
        </div>
        <!-- /.col -->
	  <div class="col-xs-4">
          <p class="lead">Misc</p>

          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">Bios Name</th>
                <td>'.$row['biosName'].'</td>
              </tr>              
              <tr>
                <th>Bios Version</th>
                <td>'.$row['biosVersion'].'</td>
              </tr>
			  <tr>
                <th>WMI Version</th>
                <td>'.$row['wmiVersion'].'</td>
              </tr>
              <tr>
                <th>Virtual Memory Name</th>
                <td>'.$row['virtualMemoryName'].'</td>
              </tr>
			  <tr>
                <th>Hardware Serial</th>
                <td>'.$row['hardwareSerial'].'</td>
              </tr>
			  <tr>
                <th>Time Zone</th>
                <td>'.$row['timeZone'].'</td>
              </tr>
            </table>
          </div>
        </div>
		<!-- Table row -->
      <div class="row">
       
        
        <!-- /.col -->
      </div>
      <!-- /.row -->
		<!-- details -->		
		<div class="col-xs-4">
          <p class="lead">Devices and Drives</p>
          <div class="table-responsive">
            <table class="table">';
			$tsqlDrive = "SELECT [diskType]
				  ,[driveLetter]
				  ,[capacity]
				  ,[freeSpace]
			  FROM [NOAH].[dbo].[DriveAudited] da, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE da.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getDrive = sqlsrv_query($conn, $tsqlDrive); 
			if ( $getDrive === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getDrive)) {  				
				while( $row = sqlsrv_fetch_array( $getDrive, SQLSRV_FETCH_ASSOC)) {  
					echo '<tr>
							<th style="width:50%">Disk Type</th>
							<td>'.$row['diskType'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Drive Letter</th>
							<td>'.$row['driveLetter'].'</td>
						  </tr>
						  <tr>
							<th style="width:50%">Capacity</th>
							<td>'.$row['capacity'].' GB</td>
						  </tr>
						  <tr>
							<th style="width:50%">Free Space</th>
							<td>'.$row['freeSpace'].' GB</td>
						  </tr>';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
            echo '             
            </table>
          </div>
        </div>
        <!-- /.col -->
		<!-- details -->		
		<div class="col-xs-4">
          <p class="lead">Network Connections</p>
          <div class="table-responsive">
            <table class="table">';
			$tsqlDrive = "SELECT [networkCard]
				  ,[dhcpEnabled]
				  ,[ipAddress]
				  ,[subnetMask]
				  ,[defaultGateway]
				  ,[dnsServers]
				  ,[dnsReg]
				  ,[primaryWins]
				  ,[secondaryWins]
				  ,[winsLookup]
			  FROM [NOAH].[dbo].[NetworkAudited] na, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE na.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getDrive = sqlsrv_query($conn, $tsqlDrive); 
			if ( $getDrive === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getDrive)) {  				
				while( $row = sqlsrv_fetch_array( $getDrive, SQLSRV_FETCH_ASSOC)) {  
					echo '<tr>
							<th style="width:50%">Network Card</th>
							<td>'.$row['networkCard'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">IP address</th>
							<td>'.$row['ipAddress'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Subnet Mask</th>
							<td>'.$row['subnetMask'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Default Gateway</th>
							<td>'.$row['defaultGateway'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">DNS Servers</th>
							<td>'.$row['dnsServers'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Primary Wins</th>
							<td>'.$row['primaryWins'].'</td>
						  </tr>
						  <tr>
							<th style="width:50%">Secondary Wins</th>
							<td>'.$row['secondaryWins'].'</td>
						  </tr>		
						  <tr>
							<th style="width:50%">DHCP Enabled</th>
							<td>'.$row['dhcpEnabled'].'</td>
						  </tr>
						  <tr>
							<th style="width:50%">DNS Reg</th>
							<td>'.$row['dnsReg'].'</td>
						  </tr>	
						  <tr>
							<th style="width:50%">Wins Lookup</th>
							<td>'.$row['winsLookup'].'</td>
						  </tr>						  
						  ';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
            echo '             
            </table>
          </div>
        </div>
        <!-- /.col -->
		<!-- details -->		
		<div class="col-xs-4">
          <p class="lead">CPU</p>
          <div class="table-responsive">
            <table class="table">';
			$tsqlDrive = "SELECT [Name]
				  ,[TypeP]
				  ,[Family]
				  ,[Speed]
				  ,[CacheSize]
				  ,[Interface]
				  ,[SocketNumber]
			  FROM [NOAH].[dbo].[ProcessorAudited] pa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE pa.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getDrive = sqlsrv_query($conn, $tsqlDrive); 
			if ( $getDrive === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getDrive)) {  				
				while( $row = sqlsrv_fetch_array( $getDrive, SQLSRV_FETCH_ASSOC)) {  
					echo '<tr>
							<th style="width:50%">Processor Type</th>
							<td>'.$row['TypeP'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Family</th>
							<td>'.$row['Family'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Speed</th>
							<td>'.$row['Speed'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Cache Size</th>
							<td>'.$row['CacheSize'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Interface</th>
							<td>'.$row['Interface'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Socket Number</th>
							<td>'.$row['SocketNumber'].'</td>
						  </tr>						  
						  ';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
            echo '             
            </table>
          </div>
        </div>
        <!-- /.col -->
      <!-- Table row -->
      <div class="row">
       
        
        <!-- /.col -->
		
      </div>
      <!-- /.row -->
<!-- details -->		
		<div class="col-xs-4">
          <p class="lead">Memory</p>
          <div class="table-responsive">
            <table class="table">';
			$tsqlDrive = "SELECT [Label]
				  ,[Capacity]
				  ,[Form]
				  ,[TypeM]
			  FROM [NOAH].[dbo].[MemoryAudited] ma, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE ma.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getDrive = sqlsrv_query($conn, $tsqlDrive); 
			if ( $getDrive === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getDrive)) {  				
				while( $row = sqlsrv_fetch_array( $getDrive, SQLSRV_FETCH_ASSOC)) {  
					echo '<tr>
							<th style="width:50%">Label</th>
							<td>'.$row['Label'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Capacity</th>
							<td>'.$row['Capacity'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Form</th>
							<td>'.$row['Form'].'</td>
						  </tr> 
						  <tr>
							<th style="width:50%">Type</th>
							<td>'.$row['TypeM'].'</td>
						  </tr> 						  					 
						  ';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
            echo '             
            </table>
          </div>
        </div>
        <!-- /.col -->
		<div class="col-xs-4">
          <p class="lead">Printers</p>

          <div class="table-responsive">
            <table class="table">';
			$tsqlPrinter = "SELECT [name]
				  ,[location]
				  ,[printerState]
				  ,[printerStatus]
				  ,[shareName]
				  ,[systemName]
			  FROM [NOAH].[dbo].[PrinterAudited] pa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE pa.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getPrinter = sqlsrv_query($conn, $tsqlPrinter); 
			if ( $getPrinter === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getPrinter)) {  				
				while( $row = sqlsrv_fetch_array( $getPrinter, SQLSRV_FETCH_ASSOC)) {  
				echo '
              <tr>
                <th style="width:50%">Name</th>
                <td>'.$row['name'].'</td>
              </tr>              
              <tr>
                <th>Location</th>
                <td>'.$row['location'].'</td>
              </tr>
			  <tr>
                <th>State</th>
                <td>'.$row['printerState'].'</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>'.$row['printerStatus'].'</td>
              </tr>
			  <tr>
                <th>Share Name</th>
                <td>'.$row['shareName'].'</td>
              </tr>
			  ';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
			echo '
            </table>
          </div>
        </div>
        <!-- /.col -->
<div class="col-xs-4">
          <p class="lead">Shares</p>

          <div class="table-responsive">
            <table class="table">';
			$tsqlPrinter = "SELECT shareName
			  FROM [NOAH].[dbo].[ShareAudited] saa, [NOAH].[dbo].[ServerAudited] sa, [NOAH].[dbo].[Hunt] hu
			  WHERE saa.serverID = sa.serverID
			  AND hu.huntingGUID = '$huntGUID'
			  AND sa.serverID = '$serverID'
			  AND sa.huntingID = hu.huntingID";			  
			$getPrinter = sqlsrv_query($conn, $tsqlPrinter); 
			if ( $getPrinter === false)  
			die( print_r( sqlsrv_errors(), true));	
			$data = array();
			$data['data'] = '';
			if(sqlsrv_has_rows($getPrinter)) {  				
				while( $row = sqlsrv_fetch_array( $getPrinter, SQLSRV_FETCH_ASSOC)) {  
				echo '
              <tr>
                <th style="width:50%">Name</th>
                <td>'.$row['shareName'].'</td>
              </tr>              
			  ';									
				} 
			}
			else {
				echo '
				<tr>
					<th style="width:50%">N/A</th>
					<td>N/A</td>
				  </tr>';
				
			}
			echo '
            </table>
          </div>
        </div>
        <!-- /.col -->
     
     
	</section>
   </div>
 ';
 	}
} 
 include ("footer.php");
sqlsrv_close($conn);