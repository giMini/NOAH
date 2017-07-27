<?php

include ("header.php");
if (isset($huntGUID)) {	
if($huntGUID != '') {
	$sqlFilter = " AND hu.huntingGUID = '$huntGUID'";
}
echo '
	<h1>
        Hunt Details
        <small>NOAH 0.1</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="hunt.php"><i class="fa fa-cube"></i> Hunts</a></li>
        <li class="active">Hunt Details</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">';

if (isset($_GET['hunt'])) {	
if($huntGUID != '') {
	$sqlFilter = " AND hu.huntingGUID = '$huntGUID'";
}
	$huntGUID = htmlentities(trim(htmlspecialchars(addslashes($_GET['hunt']))));
	$tsql = "SELECT huntingID, [huntingGUID]
      ,[huntingDate]
      ,[huntingState]
      ,[huntingComputerNumber]
      ,[huntingDescription]
	FROM [NOAH].[dbo].[Hunt] hu
	WHERE huntingGUID LIKE '$huntGUID'
	ORDER BY huntingDate DESC";
	$getHunt = sqlsrv_query($conn, $tsql); 
	if ( $getHunt === false)  
	die( print_r( sqlsrv_errors(), true));	
	$data = array();
	$data['data'] = '';
	if(sqlsrv_has_rows($getHunt)) {  
		$huntingState = '';$huntGUID='';$huntDate='';$huntingComputerNumber='';$huntingDescription='';
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
	}		
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
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=amcache" class="btn btn-app">		
		<i class="fa fa-archive"></i> AM Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "BrowserHistoryAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=browserhistory" class="btn btn-app">		
		<i class="fa fa-internet-explorer"></i> Browser History
		</a>';
	}  
	if((CountArtifactByHuntAndHost($conn, "DNSCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=dnscache" class="btn btn-app">		
		<i class="fa fa-cloud"></i> DNS Cache
		</a>';
	}	
	if((CountArtifactByHuntAndHost($conn, "ExplorerBarAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=explorerbar" class="btn btn-app">		
		<i class="fa fa-folder"></i> Explorer Bar
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "LinkFilesAudited", $sqlFilter)) > 0){	
		echo '	
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=linkfile" class="btn btn-app">		
		<i class="fa fa-file-code-o"></i> LNK Files
		</a>';
	}	
	if((CountArtifactByHuntAndHost($conn, "NetStatAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=netstat" class="btn btn-app">		
		<i class="fa fa-exchange"></i> Network Flows
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ODBCInstalledAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=obdcinstalled" class="btn btn-app">		
		<i class="fa fa-database"></i> ODBC Installed
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ODBCConfiguredAudited", $sqlFilter)) > 0){	
		echo '	
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=obdcconfigured" class="btn btn-app">		
		<i class="fa fa-database"></i> ODBC Configured
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "AutorunAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=persistence" class="btn btn-app">		
		<i class="fa fa-history"></i> Persistence
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "PrefetchAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=prefetch" class="btn btn-app">		
		<i class="fa fa-th"></i> Prefetch Files
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ProcessTreeAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=processtreebyserver" class="btn btn-app">		
		<i class="fa fa-cogs"></i> Process Tree
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RecentDocsAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=recentdocs" class="btn btn-app">		
		<i class="fa fa-file-word-o"></i> Recent Docs
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RecentFileCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=recentfilecache" class="btn btn-app">		
		<i class="fa fa-archive"></i> Recent File Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "RunMRUsAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=runmru" class="btn btn-app">		
		<i class="fa fa-hdd-o"></i> Run MRU
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ScheduledTaskAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=scheduledtask" class="btn btn-app">		
		<i class="fa fa-tasks"></i> Scheduled Tasks
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ServiceAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=service" class="btn btn-app">		
		<i class="fa fa-gear"></i> Services
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "ShimCacheAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=shimcache" class="btn btn-app">		
		<i class="fa fa-archive"></i> Shim Cache
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "InstalledProgramAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=software" class="btn btn-app">		
		<i class="fa fa-laptop"></i> Software Installed
		</a>';
	}
	if((CountArtifactByHuntAndHost($conn, "USBHistoryAudited", $sqlFilter)) > 0){	
		echo '
		<a href="artifact1.php?hunt='.$huntingGUID.'&pageSwitch=usbhistory" class="btn btn-app">		
		<i class="fa fa-bolt"></i> USB Forensic
		</a>';
	}
	//if((CountArtifactByHuntAndHost($conn, "ExplorerBarAudited", $sqlFilter)) > 0){	
		echo '
		<a href="useraccess.php?hunt='.$huntingGUID.'" class="btn btn-app">		
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
              <h3 class="box-title">Hunt Details</h3><br />
			  <p class="help-block"><?php echo "($huntingGUID - $huntingDescription - $huntDate)";?></p>
	
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="table-responsive">
                <table class="table no-margin">
                  <thead>
                  <tr>
					<th>Sever Name</th>
					<th>Domain</th>
					<th>Role</th>                    
                    <th>HW_Make</th>
					<th>HW_Model</th>					
                  </tr>
                  </thead>
                  <tbody id="suspiciousmruph">
				  <div class="input-group input-group-sm">
					<form action="processtree.php">
					<input type="text" class="form-control" id="mruinput">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="mru">Go!</button>
                    </span>
					</form>
					</div>';	
				  															
					$getHunt = RetrieveHuntDetails($conn, $huntingID);
					foreach($getHunt as $result) {
						echo $result;
					}					
				echo '  
				</tbody>
                </table>
              </div>
              <!-- /.table-responsive -->			  
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <a href="launchhunt.php" class="btn btn-sm btn-info btn-flat pull-left">Make New Hunt</a>
              <a href="hunt.php" class="btn btn-sm btn-default btn-flat pull-right">View All Hunts</a>
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->

       
      </div>
      <!-- /.row -->';

}
echo '
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->';
} 
 include ("footer.php");
sqlsrv_close($conn);