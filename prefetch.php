<?php

include ("header.php");

?>
      <h1>
        Prefetch
        <small>NOAH 1.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Prefetch</li>
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
              <h3 class="box-title">Prefetch</h3>
	
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
					<th>Server ID</th>
					<th>Server Name</th>
                    <th>Program Name</th>
                    <th>File Associated</th>					                 
                  </tr>
                  </thead>
                  <tbody id="suspiciousprefetchph">
				  <div class="input-group input-group-sm">
					<form id="formprefetch" method="post" autocomplete="off">
						<input type="text" class="form-control" id="prefetchinput">
						<input type="text" class="form-control" id="extensioninput">										
						<span class="input-group-btn">
						  <button type="button" class="btn btn-info btn-flat" id="prefetch">Go!</button>
						</span>
					</form>
					</div>	
				  <?php
					function PrefetchCommand ($conn){						
						//$tsql = "DECLARE @serverID int = ".$server."; SELECT pta.serverID, sa.serverName as serverName,
						$tsql = "
						SELECT TOP 1000 [serverID]
							  ,FileAssociated
							  ,[ProgramName]
							  ,[Hash]
							  ,[NumberOfExecutions]
							  ,[PrefetchSize]
							  ,[LastExecutionTime_1]
							  ,[LastExecutionTime_2]
							  ,[LastExecutionTime_3]
							  ,[LastExecutionTime_4]
							  ,[LastExecutionTime_5]
							  ,[LastExecutionTime_6]
							  ,[LastExecutionTime_7]
							  ,[LastExecutionTime_8]	  
						  FROM [NOAH].[dbo].[PrefetchAudited] PA, [NOAH].[dbo].[PrefetchFilesAssociatedAudited] PFAA
						  WHERE PA.[prefetchAuditedID] = PFAA.[prefetchAuditedID]
						
						  ORDER BY NumberOfExecutions DESC
						";
						$getPrefetch = sqlsrv_query($conn, $tsql); 
						if ( $getPrefetch === false)  
						die( print_r( sqlsrv_errors(), true));	
						$data = array();
						$data['data'] = '';
						if(sqlsrv_has_rows($getPrefetch)) {  
							while( $row = sqlsrv_fetch_array( $getPrefetch, SQLSRV_FETCH_ASSOC)) {  		
								//if(strpos($row['ProcessName'], 'powershell') !== FALSE){							
									$data['data'] .= '<tr><td>'.$row['ProgramName'].'</td><td>'.$row['FileAssociated'].'</td></tr>';  							
								//}
							}  
						}	
						return $data;
					}
										
					$getPrefetch = PrefetchCommand($conn);
					foreach($getPrefetch as $result) {
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