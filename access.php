<?php

include ("header.php");

?>
<h1>
        User access
        <small>NOAH 1.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">User access</li>
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
              <h3 class="box-title">User access</h3>
	
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
                    <th>Server Name</th>
                    <th>Object</th>					                 
					<th>User</th>					                 
                  </tr>
                  </thead>
                  <tbody id="accessph">
				  <div class="input-group input-group-sm">
					<form id="formaccess" method="post" autocomplete="off">
					<input type="text" class="form-control" id="accessinput">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="access">Go!</button>
                    </span>
					</form>
					</div>	
				  <?php
					function OSPrivilegeCommand ($conn){												
						$tsql = "SELECT
								  sa.serverName serverName
								  ,[strategy]
								  ,[securityParameter]
							  FROM [NOAH].[dbo].[OSPrivilegeAudited] osp, [NOAH].[dbo].[ServerAudited] sa
							  WHERE osp.serverID = sa.serverID";
						$getOSPrivilege = sqlsrv_query($conn, $tsql); 
						if ( $getOSPrivilege === false)  
						die( print_r( sqlsrv_errors(), true));	
						$data = array();
						$data['data'] = '';
						if(sqlsrv_has_rows($getOSPrivilege)) {  
							while( $row = sqlsrv_fetch_array( $getOSPrivilege, SQLSRV_FETCH_ASSOC)) {  		
								//if(strpos($row['ProcessName'], 'powershell') !== FALSE){							
									$data['data'] .= '<tr><td>'.$row['serverName'].'</td><td>'.$row['strategy'].'</td><td>'.$row['securityParameter'].'</td></tr>';  							
								//}
							}  
						}	
						return $data;
					}
										
					$getOSPrivilege = OSPrivilegeCommand($conn);
					foreach($getOSPrivilege as $result) {
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
	  
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

 <?php
 include ("footer.php");
sqlsrv_close($conn);