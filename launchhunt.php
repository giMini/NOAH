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
			<table class="table no-margin">
                  <thead>
                  <tr>
					<th>Action</th>                   					
                  </tr>
                  </thead>			
			<tbody id="launchph">
				  <form name="formlaunch" id="formlaunch" method="post" />
				  <input type="text" class="form-control" id="launchinput" name="HuntDescription" placeholder="  Hunt Description">
				  <br>
					<div class="form-group">
					<div class="col-xs-3">
						<p class="lead">Options</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal" id="huntchoices1" name="huntchoices[]" value="All"> All</label>	
							<br><label><input type="checkbox" class="minimal" id="huntchoices2 name="huntchoices[]" value="EnableHash"> Enable MD5</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices3 name="huntchoices[]" value="VT"> Virus Total</label>						
							<br><label><input type="checkbox" class="minimal" id="huntchoices4 name="huntchoices[]" value="EZ"> EZ</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices5 name="huntchoices[]" value="MassStorage"> Mass Storage</label>
						</div>				
					</div>
					<div class="col-xs-3">
						<p class="lead">General Information</p>
						<div class="table-responsive">						
							<br><label><input type="checkbox" class="minimal" id="huntchoices6 name="huntchoices[]" value="Processor"> Processor</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices7 name="huntchoices[]" value="Memory"> Memory</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices8 name="huntchoices[]" value="Disk"> Disk</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices9 name="huntchoices[]" value="Network"> Network</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices10 name="huntchoices[]" value="InstalledPrograms"> Installed Programs</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices11 name="huntchoices[]" value="Shares"> Shares</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices12 name="huntchoices[]" value="Services"> Services</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices13 name="huntchoices[]" value="ScheduledTasks"> Scheduled Tasks</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices14 name="huntchoices[]" value="Printers"> Printers</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices15 name="huntchoices[]" value="Process"> Process</label>
						</div>				
					</div>
					<div class="col-xs-3">
						<p class="lead">Persistence</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal" id="huntchoices16 name="huntchoices[]" value="Autoruns"> Autoruns</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices17 name="huntchoices[]" value="ProcessTree"> ProcessTree</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices18 name="huntchoices[]" value="LocalUsers"> Local Users</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices19 name="huntchoices[]" value="ODBCConfigured"> ODBC Configured</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices20 name="huntchoices[]" value="ODBCInstalled"> ODBC Installed</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices21 name="huntchoices[]" value="OperatingSystemPrivileges"> Operating System Privileges</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices22 name="huntchoices[]" value="Netstat"> Netstat</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices23 name="huntchoices[]" value="DNSCache"> DNS Cache</label>														
						</div>
					</div>
					<div class="col-xs-3">
						<p class="lead">Forensic</p>
						<div class="table-responsive">
							<br><label><input type="checkbox" class="minimal" id="huntchoices24 name="huntchoices[]" value="LINKFile"> LINK File</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices25 name="huntchoices[]" value="ExplorerBar"> Explorer Bar</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices26 name="huntchoices[]" value="RunMRU"> Run MRU</label> # Most Recently Used
							<br><label><input type="checkbox" class="minimal" id="huntchoices27 name="huntchoices[]" value="USBHistory"> USB History</label>							
							<br><label><input type="checkbox" class="minimal" id="huntchoices28 name="huntchoices[]" value="AMcache"> AMcache</label> # > Windows 8
							<br><label><input type="checkbox" class="minimal" id="huntchoices29 name="huntchoices[]" value="Shimcache"> Shimcache</label> # > Windows 7
							<br><label><input type="checkbox" class="minimal" id="huntchoices30 name="huntchoices[]" value="RecentFileCache"> RecentFileCache</label> # < Windows 8
							<br><label><input type="checkbox" class="minimal" id="huntchoices31 name="huntchoices[]" value="RecentDocs"> Recent Docs</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices32 name="huntchoices[]" value="Prefetch"> Prefetch</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices33 name="huntchoices[]" value="BrowserHistory"> Browser History</label>
							<br><label><input type="checkbox" class="minimal" id="huntchoices34 name="huntchoices[]" value="UserProfiles"> User Profiles</label>	
							<br><label><input type="checkbox" class="minimal" id="huntchoices35 name="huntchoices[]" value="Memorydump"> Memory dump</label>								
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
			</tbody>
			 </table>
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