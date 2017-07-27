<?php

include ("header.php");

?>
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
              <div class="table-responsive">
                <table class="table no-margin">
                  <thead>
                  <tr>
					<th>State</th>
					<th>GUID</th>
					<th>Date</th>                    
                    <th># Computer</th>
					<th>Description</th>					
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
					</div>	
				  <?php
															
					$getHunt = RetrieveHunt($conn);
					foreach($getHunt as $result) {
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