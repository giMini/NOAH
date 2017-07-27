<?php
include ("header.php");

echo '
	<h1>
        AM Cache
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
		<li class="active">AM Cache</li>
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
              <h3 class="box-title">AM Cache</h3>

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
					<th>Server Name</th>                   
                    <th>Associated</th><th>ProgramName</th><th>ProgramID</th><th>Volume ID</th><th>VolumeIDLastWriteTimestamp</th><th>FileID</th><th>FileIDLastWriteTimestamp</th><th>SHA1</th><th>FullPath</th><th>FileExtension</th><th>MFTEntryNumber</th><th>MFTSequenceNumber</th><th>FileSize</th><th>FileVersionString</th><th>FileVersionNumber</th><th>FileDescription</th><th>PEHeaderSize</th><th>PEHeaderHash</th><th>PEHeaderChecksum</th><th>Created</th><th>LastModified</th><th>LastModified2</th><th>CompileTime</th><th>LanguageID</th><th>CompanyName</th>                    
                  </tr>
                  </thead>
                  <tbody id="suspiciousamcacheph">';				  				 
					echo '
				  <div class="input-group input-group-sm">
					<form id="formamcache" method="post" autocomplete="off">
					<input type="text" class="form-control" id="amcacheinput">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="amcache">Go!</button>
                    </span>
					
					</div>	';							 
					include_once("amcachefetch.php");						
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
	  
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
	<script type="text/javascript">
	$("#suspiciousamcacheph").on( "click", ".pagination2 a", function (e){
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
	echo '$("#suspiciousamcacheph").load("amcachefetch.php",{"page":page'.$varHunt.''.$varServer.'}, function(){';
	
		echo '
            $(".loading-div").hide(); //once done, hide loading element
        });
        
    });
</script>';

 include ("footer.php");

sqlsrv_close($conn);