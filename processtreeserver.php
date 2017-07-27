<?php

include ("header.php");

echo '
	<h1>
        Process Tree
        <small>NOAH 1.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="hunt.php"><i class="fa fa-cube"></i> Hunts</a></li>';
if($huntGUID != ''){	
	echo '<li><a href="huntdetails.php?hunt='.$huntGUID.'"><i class="fa fa-cubes"></i> Hunt Details</a></li>';
}
if($serverID != ''){	
	echo '<li><a href="hostdetails.php?hunt='.$huntGUID.'&serverID='.$serverID.'"><i class="fa fa-desktop"></i> Host Details</a></li>';  
}
echo '    
		<li class="active">Process Tree</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

	  <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
       
         
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Process Tree</h3>
	
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
					<th>Hunting GUID</th>
                    <th>Server Name</th>
					<th>Parent Process ID</th>
					<th>Parent Process Name</th>
					<th>Process ID</th>
                    <th>Process Name</th>
					<th>sessionID</th><th>handles</th><th>creationDate</th><th>location</th><th>CommandLine</th><th>Decoded</th><th>Suspicious</th><th>Description</th><th>Hash</th><th>username</th><th>domain</th><th>VT</th><th>permalink</th>					
                  </tr>
                  </thead>
                  <tbody id="suspiciousprocesstreebyserverph">
				  <div class="input-group input-group-sm">
					<form id="formprocesstreebyserver" method="post" autocomplete="off">
						<input type="text" class="form-control" id="processtreebyserverinput">
						<span class="input-group-btn">
						  <button type="button" class="btn btn-info btn-flat" id="searchprocesstreebyserver">Go!</button>					  
						</span>
					</form>
					</div>';
					include_once("processtreeserverfetch.php");
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
      <!-- /.row -->
	  
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<script type="text/javascript">
	$("#suspiciousprocesstreebyserverph").on( "click", ".pagination2 a", function (e){
        e.preventDefault();
        $(".loading-div").show(); //show loading element
        var page = $(this).attr("data-page");';
	
	$varHunt = '';
	$varServer = '';
	if ($huntGUID != '') {
		echo 'var huntGUID = "'.$huntGUID.'";';
		$varHunt = ', "huntGUID":huntGUID';
	}
	if ($serverID!= '') {
		echo 'var serverID = '.$serverID.';';
		$varServer = ',"serverID":serverID';
	}
	echo '$("#suspiciousprocesstreebyserverph").load("processtreeserverfetch.php",{"page":page'.$varHunt.''.$varServer.'}, function(){';
	
		echo '
            $(".loading-div").hide(); //once done, hide loading element
        });
        
    });
</script>';

 include ("footer.php");
sqlsrv_close($conn);