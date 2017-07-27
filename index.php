<?php

include ("header.php");

?>

      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Info boxes -->
      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Healthy Hosts</span>
              <span class="info-box-number">157<small></small></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-warning"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Hosts to investigate</span>
              <span class="info-box-number">2</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <!-- fix for small devices only -->
        <div class="clearfix visible-sm-block"></div>

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Hosts in incident</span>
              <span class="info-box-number">1</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
		<!--
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">New Members</span>
              <span class="info-box-number">2,000</span>
            </div>
            <!-- /.info-box-content -->
        <!--  </div> -->
          <!-- /.info-box -->
        <!-- </div> -->
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Monthly Recap Report</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-wrench"></i></button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-8">
                  <p class="text-center">
                    <strong>1 Feb, 2017 - 28 Feb, 2017</strong>
                  </p>
			<div class="box box-primary">
            <div class="box-header with-border">
              <i class="fa fa-bar-chart-o"></i>

              <h3 class="box-title">Threat Events</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div id="line-chart" style="height: 200px;"></div>
            </div>
            <!-- /.box-body-->
          </div>
          <!-- /.box -->

                  <div class="chart">
                    <!-- Sales Chart Canvas -->
                    <!-- <canvas id="huntChart" style="height: 180px;"></canvas> -->
                  </div>
                  <!-- /.chart-responsive -->
                </div>
                <!-- /.col -->
                <div class="col-md-4">
                  <p class="text-center">
                    <strong>Goal Completion</strong>
                  </p>

                  <div class="progress-group">
                    <span class="progress-text">Host to hunt</span>
                    <span class="progress-number"><b>160</b>/200</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-green" style="width: 80%"></div>
                    </div>
                  </div>
                                  </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- ./box-body -->
            <div class="box-footer">
              <div class="row">
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-red"><i class="fa fa-caret-up"></i> 1</span>
                    <h5 class="description-header">Unsafe Host</h5>
                    <span class="description-text"></span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-yellow"><i class="fa fa-caret-up"></i> 2</span>
                    <h5 class="description-header">Host to investigate</h5>
                    <span class="description-text"></span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                    <h5 class="description-header">Host hunted</h5>                    
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col 
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block">
                    <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                    <h5 class="description-header">160</h5>
                    <span class="description-text">GOAL COMPLETIONS</span>
                  </div>
                  <!-- /.description-block -->
                 <!--</div>-->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
       
         
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Suspicious child process to investigate</h3>

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
                    <th>Hunt ID</th>
                    <th>Host</th>
					<th>Parent ID</th>
					<th>Parent Name</th>
                    <th>Child ID</th>
					<th>Child Name</th>
					<th>Command Line</th>
					<th>Location</th>
					<th>Session ID</th>
                  </tr>
                  </thead>
                  <tbody id="suspiciousparentchildph">
					<div class="input-group input-group-sm">
					<form action="index.php">
					<input type="text" class="form-control" id="searchparentinput">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="searchparent">Go!</button>
                    </span>
					</form>
					</div>	
					<?php 
$server = 1;
	$objectReturn = '';																				
	$getProcess = SuspiciousParentChildD($conn,"cmd","powershell");
	
	foreach($getProcess as $result) {						
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"wmiprvse","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"explorer","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"windowsupdatebox","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"wscript","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"taskeng","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"winword","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}$getProcess = SuspiciousParentChildD($conn,"cab","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"java","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"excel","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"splunkd","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}
	$getProcess = SuspiciousParentChildD($conn,"msaccess","powershell");
	foreach($getProcess as $result) {
		$objectReturn .= $result;
	}  
	echo $objectReturn;


					?>
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
	  
	  <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
       
         
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Suspicious Process to investigate</h3>

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
					<th>Category</th>
                    <th>Hunt ID</th>					
                    <th>Host</th>
					<th>VT Report</th>					
					<th>Name</th>					
					<th>Location</th>										
                  </tr>
                  </thead>
                  <tbody>
				  
				  <?php
				  $server = 2;					
										
					$getProcess = MaliciousProcess($conn);
					foreach($getProcess as $result) {
						echo $result;
					}
					?>
				  
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
	  
	  <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
       
         
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Orphaned PowerShell process to investigate</h3>

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
                    <th>Hunt ID</th>
                    <th>Host</th>
					<th>Parent ID</th>
					<th>Parent Name</th>
                    <th>Child ID</th>
					<th>Child Name</th>
					<th>Command Line</th>
					<th>Location</th>
					<th>Session ID</th>
                  </tr>
                  </thead>
                  <tbody>
				  
				  <?php
				  $server = 2;					
										
					$getProcess = SuspiciousOrphanedParentChild($conn, "powershell.exe");
					foreach($getProcess as $result) {
						echo $result;
					}
					?>
				  
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
	  
	  <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->
       
         
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Suspicious process (encoded command/parameters)</h3>

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
                    <th>Hunt ID</th>
                    <th>Host</th>
					<th>Parent ID</th>
					<th>Parent Name</th>
                    <th>Child ID</th>
					<th>Child Name</th>
					<th>Command Line</th>
					<th>Decoded Command Line</th>
					<th>Location</th>
					<th>Session ID</th>
                  </tr>
                  </thead>
                  <tbody>
				  
				  <?php														
					$getProcess = SuspiciousEncodedCommand($conn);
					foreach($getProcess as $result) {
						echo $result;
					}
					?>
				  
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


	  
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <!-- FLOT CHARTS -->
<script src="plugins/flot/jquery.flot.min.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script src="plugins/flot/jquery.flot.resize.min.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script src="plugins/flot/jquery.flot.pie.min.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script src="plugins/flot/jquery.flot.categories.min.js"></script>
<script type="text/javascript">
/*
     * LINE CHART
     * ----------
     */
    //LINE randomly generated data

    var sin = [], cos = [];
	var sin = [[1, 1], [2, 0], [3, 15], [4, 89], [5, 0], [6, 12]];
	var cos =  [[1, 5], [2, 0], [3, 12], [4, 0], [5, 40], [6, 0]];
 
    var line_data1 = {
      data: sin,
      color: "#FF0000",
	  label: "Unsafe"
    };
    var line_data2 = {
      data: cos,
      color: "#FFA500",
	  label: "Abnormal"
    };
    $.plot("#line-chart", [line_data1, line_data2], {
      grid: {
        hoverable: true,
        borderColor: "#f3f3f3",
        borderWidth: 1,
        tickColor: "#f3f3f3"
      },
      series: {
        shadowSize: 0,
        lines: {
          show: true
        },
        points: {
          show: true
        }
      },
      lines: {
        fill: false,
        color: ["#3c8dbc", "#f56954"]
      },
      yaxis: {
        show: true,
      },
      xaxis: {
        show: true
      }
    });
    //Initialize tooltip on hover
    $('<div class="tooltip-inner" id="line-chart-tooltip"></div>').css({
      position: "absolute",
      display: "none",
      opacity: 0.8
    }).appendTo("body");
    $("#line-chart").bind("plothover", function (event, pos, item) {

      if (item) {
        var x = item.datapoint[0].toFixed(2),
            y = item.datapoint[1].toFixed(2);

        $("#line-chart-tooltip").html(item.series.label + " of " + x + " = " + y)
            .css({top: item.pageY + 5, left: item.pageX + 5})
            .fadeIn(200);
      } else {
        $("#line-chart-tooltip").hide();
      }

    });
    /* END LINE CHART */
</script>
 <?php
 include ("footer.php");
sqlsrv_close($conn);