<?php
include ("header.php");
if($pageSwitch != ''){	
$stop = 0;
include_once("switchPage.php");
if($stop == 0){
echo '
	<h1>
        '.$title.'
        <small>NOAH 0.1</small>
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
		<li class="active">'.$title.'</li>
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
              <h3 class="box-title">'.$title.'</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="table-responsive">
			  <!--<input type="text" id="search" placeholder="  Live Search"></input>-->
                <table class="table no-margin">
                  <thead>
                  <tr>
					<th>Hunt GUID</th>                   
					<th>Server Name</th>'.$th.'
                  </tr>
                  </thead>
                  <tbody id="suspicious'.$var.'ph">';				  				 
					echo '
				  <div class="input-group input-group-sm">
					<form id="form'.$var.'" method="post" autocomplete="off">
					<input type="text" class="form-control" id="'.$var.'input">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="'.$var.'">Go!</button>
                    </span>
					
					</div>	';							 
					include_once("artifact2.php");
				  					
				  echo '
				</tbody>
                </table>
              </div>
              <!-- /.table-responsive -->			  
            </div>
            <!-- /.box-body -->';
			
			echo '
			</form>
           
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->		     
      </div>
      <!-- /.row -->';
	  if($serverID == ''){
	  echo '
	  <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
          <!-- MAP & BOX PANE -->                
          <!-- TABLE: LATEST ORDERS -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">'.$title.'	 Stats</h3>

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
					<th>Hunt GUID</th>                   
					'.$thStat.'					
                  </tr>
                  </thead>
                  <tbody id="suspicious'.$var.'ph">';				
					include_once("artifact3.php");
				  echo '
				</tbody>
                </table>
              </div>
              <!-- /.table-responsive -->			  
            </div>
            <!-- /.box-body -->';
			
			echo '
			</form>
           
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->		    
      </div>
      <!-- /.row -->
	  ';
	  }
	echo ' 
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
	<script type="text/javascript">
	$("#suspicious'.$var.'ph").on( "click", ".pagination2 a", function (e){
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
	if ($pageSwitch!= '') {
		echo 'var pageSwitch = "'.$pageSwitch.'";';
		$varPageSwitch = ',"pageSwitch":pageSwitch';
	}
	echo '$("#suspicious'.$var.'ph").load("artifact2.php",{"page":page'.$varHunt.''.$varServer.''.$varPageSwitch.'}, function(){';
	
		echo '
            $(".loading-div").hide(); //once done, hide loading element
        });
        
    });
</script>';

 include ("footer.php");

sqlsrv_close($conn);
}
}