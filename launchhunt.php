<?php

include ("header.php");
echo '
	<h1>
        Hunts
        <small>NOAH 0.1</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Hunts</li>
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
              <h3 class="box-title">Hunts</h3>
	
              <div class="box-tools pull-right">			  
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">                                              				  
				  <form name="formlaunch" id="formlaunch" method="post" />
				  <input type="text" class="form-control" id="launchinput" placeholder="  Hunt Description">
				  <br>
					<div class="form-group">
					<div class="col-xs-3">
						<p class="lead">Options</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal"> All</label>	
							<br><label><input type="checkbox" class="minimal"> Enable MD5</label>
							<br><label><input type="checkbox" class="minimal"> Virus Total</label>						
							<br><label><input type="checkbox" class="minimal"> EZ</label>
							<br><label><input type="checkbox" class="minimal"> Mass Storage</label>
						</div>				
					</div>
					<div class="col-xs-3">
						<p class="lead">General Information</p>
						<div class="table-responsive">						
							<br><label><input type="checkbox" class="minimal"> Processor</label>
							<br><label><input type="checkbox" class="minimal"> Memory</label>
							<br><label><input type="checkbox" class="minimal"> Disk</label>
							<br><label><input type="checkbox" class="minimal"> Network</label>
							<br><label><input type="checkbox" class="minimal"> Installed Programs</label>
							<br><label><input type="checkbox" class="minimal"> Shares</label>
							<br><label><input type="checkbox" class="minimal"> Services</label>
							<br><label><input type="checkbox" class="minimal"> Scheduled Tasks</label>
							<br><label><input type="checkbox" class="minimal"> Printers</label>
							<br><label><input type="checkbox" class="minimal"> Process</label>
						</div>				
					</div>
					<div class="col-xs-3">
						<p class="lead">Persistence</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal"> Autoruns</label>
							<br><label><input type="checkbox" class="minimal"> ProcessTree</label>
							<br><label><input type="checkbox" class="minimal"> Local Users</label>
							<br><label><input type="checkbox" class="minimal"> ODBC Configured</label>
							<br><label><input type="checkbox" class="minimal"> ODBC Installed</label>
							<br><label><input type="checkbox" class="minimal"> Operating System Privileges</label>
							<br><label><input type="checkbox" class="minimal"> Netstat</label>
							<br><label><input type="checkbox" class="minimal"> DNS Cache</label>														
						</div>
					</div>
					<div class="col-xs-3">
						<p class="lead">Forensic</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal"> LINK File</label>
							<br><label><input type="checkbox" class="minimal"> Explorer Bar</label>
							<br><label><input type="checkbox" class="minimal"> Run MRU</label> # Most Recently Used
							<br><label><input type="checkbox" class="minimal"> USB History</label>							
							<br><label><input type="checkbox" class="minimal"> AMcache</label> # > Windows 8
							<br><label><input type="checkbox" class="minimal"> Shimcache</label> # > Windows 7
							<br><label><input type="checkbox" class="minimal"> RecentFileCache</label> # < Windows 8
							<br><label><input type="checkbox" class="minimal"> Recent Docs</label>
							<br><label><input type="checkbox" class="minimal"> Prefetch</label>
							<br><label><input type="checkbox" class="minimal"> Browser History</label>
							<br><label><input type="checkbox" class="minimal"> User Profiles</label>	
							<br><label><input type="checkbox" class="minimal"> Memory dump</label>								
						</div>
					</div>
					</div>
				</div>	
				<div class="box-footer clearfix">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-sm btn-info btn-flat pull-left" id="launch">Hunt!</button>
                    </span>
					</div>
				</form>			
				</div>					  				  				              
              </div>
              <!-- /.table-responsive -->			  
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
  <!-- /.content-wrapper -->';


include ("footer.php");
sqlsrv_close($conn);