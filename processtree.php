<?php

include ("header.php");

?>
<h1>
        Process Tree
        <small>NOAH 1.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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
                    <th>Count</th>
                    <th>ProcessName</th>					                 
                  </tr>
                  </thead>
                  <tbody id="suspiciousprocesstreeph">
				  <div class="input-group input-group-sm">
					<form id="formprocesstree" method="post" autocomplete="off">
					<input type="text" class="form-control" id="searchtreeinput">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info btn-flat" id="searchtree">Go!</button>
                    </span>
					</form>
					</div>	
				  <?php
					function ProcessTreeCommand2 ($conn){						
						//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
						$tsql = "SELECT 
								[Count]
								,[ProcessName]
								FROM [NOAH].[dbo].[FlatProcessStat]
								GROUP BY [Count], [ProcessName]
								ORDER BY [Count] ASC";
						$getProcessTree = sqlsrv_query($conn, $tsql); 
						if ( $getProcessTree === false)  
						die( print_r( sqlsrv_errors(), true));	
						$data = array();
						$data['data'] = '';
						if(sqlsrv_has_rows($getProcessTree)) {  
							while( $row = sqlsrv_fetch_array( $getProcessTree, SQLSRV_FETCH_ASSOC)) {  		
								//if(strpos($row['ProcessName'], 'powershell') !== FALSE){							
									$data['data'] .= '<tr><td>'.$row['Count'].'</td><td>'.$row['ProcessName'].'</td></tr>';  							
								//}
							}  
						}	
						return $data;
					}
										
					$getProcessTree = ProcessTreeCommand2($conn);
					foreach($getProcessTree as $result) {
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