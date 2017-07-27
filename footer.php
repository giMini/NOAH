 <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 0.1
    </div>
    <strong>giMini</a>.</strong>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-user bg-yellow"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                <p>New phone +1(800)555-1234</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                <p>nora@example.com</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-file-code-o bg-green"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                <p>Execution time 5 seconds</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="label label-danger pull-right">70%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Update Resume
                <span class="label label-success pull-right">95%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Laravel Integration
                <span class="label label-warning pull-right">50%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Back End Framework
                <span class="label label-primary pull-right">68%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->

      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Allow mail redirect
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Other sets of options are available
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Expose author name in posts
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Allow the user to show his name in blog posts
            </p>
          </div>
          <!-- /.form-group -->

          <h3 class="control-sidebar-heading">Chat Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Show me as online
              <input type="checkbox" class="pull-right" checked>
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Turn off notifications
              <input type="checkbox" class="pull-right">
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Delete chat history
              <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
            </label>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>

</div>
<!-- ./wrapper -->
<script>
<?php
	$varHunt = '';
	$varServer = '';
	$declareHunt = '';
	$declareServer = '';
	$jsControls = array("amcache","bha","processtreebyserver","persistence","netstat","dnscache","softwareinstalled","shimcache","linkfile","recentdocs","usbhistory","useraccess","runmru","service");
	$elements = count($jsControls);
	if ($huntGUID != '') { 
		$declareHunt = 'var huntGUID = "'.$huntGUID.'";';
		$varHunt = ', "huntGUID":huntGUID';
	}
	if ($serverID!= '') {
		$declareServer = 'var serverID = '.$serverID.';';
		$varServer = ',"serverID":serverID';
	}	
	for ($row = 0; $row < $elements; $row++) {
		echo '	
		$("#form'.$jsControls[$row].'").submit(function(e){	
			e.preventDefault(); // Prevent Default Submission
			
			var '.$jsControls[$row].' = $("#'.$jsControls[$row].'input").val();';	
		echo $declareHunt;
		echo $declareServer;
		$postAC = '{ '.$jsControls[$row].': '.$jsControls[$row].''.$varHunt.''.$varServer.' },';
		echo '
		   $.post( 
			  "action.php",'.$postAC.'		  
			  function(data) {					
				 $("#suspicious'.$jsControls[$row].'ph").html(data);
			  }
		   );
				
		});';
	}
echo '
	$("#amcache").click(function(event){
	var amcache = $("#amcacheinput").val();		
	   $.post( 
		  "action.php",
		  { amcache: amcache },
		  function(data) {					
			 $("#suspiciousamcacheph").html(data);
		  }
	   );
			
	});';

?>
	$("#launch").click(function(event){
	var launch = $("#launchinput").val();		
	   $.post( 
		  "action.php",
		  { launch: launch },
		  function(data) {					
			 $('#launchph').html(data);
		  }
	   );
			
	});
	
	$("#formlaunch").submit(function(e){
		e.preventDefault(); // Prevent Default Submission
	var launch = $("#launchinput").val();		
	   $.post( 
		  "action.php",
		  { launch: launch },
		  function(data) {					
			 $('#launchph').html(data);
		  }
	   );
			
	});

	$("#access").click(function(event){
	var access = $("#accessinput").val();		
	   $.post( 
		  "action.php",
		  { access: access },
		  function(data) {					
			 $('#accessph').html(data);
		  }
	   );
			
	});
	
	$("#formaccess").submit(function(e){
		e.preventDefault(); // Prevent Default Submission
	var access = $("#accessinput").val();		
	   $.post( 
		  "action.php",
		  { access: access },
		  function(data) {					
			 $('#accessph').html(data);
		  }
	   );
			
	});
	
	$("#magic").click(function(event){
	var magic = $("#magicinput").val();		
	   $.post( 
		  "magicsearch.php",
		  { magic: magic },
		  function(data) {					
			 $('#suspiciousmagicph').html(data);
		  }
	   );
			
	});
	
	$("#formmagic").submit(function(e){	
		e.preventDefault(); // Prevent Default Submission
		var magic = $("#magicinput").val();				
	   $.post( 
		  "magicsearch.php",
		  { magic: magic },
		  function(data) {					
			 $('#suspiciousmagicph').html(data);
		  }
	   );
			
	});
	
	$("#bha").click(function(event){
	var bha = $("#bhainput").val();		
	   $.post( 
		  "action.php",
		  { bha: bha },
		  function(data) {					
			 $('#suspiciousbhaph').html(data);
		  }
	   );
			
	});

	$("#runmru").click(function(event){
	var mru = $("#runmruinput").val();		
	   $.post( 
		  "action.php",
		  { mru: mru },
		  function(data) {					
			 $('#suspiciousrunmruph').html(data);
		  }
	   );
			
	});

	$("#prefetch").click(function(event){
	var prefetch = $("#prefetchinput").val();	
	var extension = $("#extensioninput").val();	
	   $.post( 
		  "action.php",
		  { prefetch: prefetch, extension: extension },
		  function(data) {					
			 $('#suspiciousprefetchph').html(data);
		  }
	   );
			
	});
	
	$("#formprefetch").submit(function(e){	
		e.preventDefault(); // Prevent Default Submission
		var extension = $("#extensioninput").val();	
		var prefetch = $("#prefetchinput").val();			
	   $.post( 
		  "action.php",
		  { prefetch: prefetch, extension: extension },
		  function(data) {					
			 $('#suspiciousprefetchph').html(data);
		  }
	   );
			
	});
	
	$("#searchparent").click(function(event){
	var serverid = $("#searchparentinput").val();	
	   $.post( 
		  "action.php",
		  { server: serverid },
		  function(data) {					
			 $('#suspiciousparentchildph').html(data);
		  }
	   );
			
	});

   $("#searchtree").click(function(event){
	var treetosearch = $("#searchtreeinput").val();		
	   $.post( 
		  "action.php",
		  { tree: treetosearch },
		  function(data) {					
			 $('#suspiciousprocesstreeph').html(data);
		  }
	   );
			
	});
	
	$("#formprocesstree").submit(function(e){
		e.preventDefault(); // Prevent Default Submission
	var treetosearch = $("#searchtreeinput").val();		
	   $.post( 
		  "action.php",
		  { tree: treetosearch },
		  function(data) {					
			 $('#suspiciousprocesstreeph').html(data);
		  }
	   );
			
	});
	
	$("#searchtreebyserver").click(function(event){
	var treetosearch = $("#ssearchtreebyserverinput").val();		
	   $.post( 
		  "action.php",
		  { treeserver: treetosearch },
		  function(data) {					
			 $('#suspiciousprocesstreebyserverph').html(data);
		  }
	   );
			
	});
		

	$("#searchpersistence").click(function(event){
	var persistence = $("#searchpersistenceinput").val();		
	   $.post( 
		  "action.php",
		  { persistence: persistence },
		  function(data) {					
			 $('#suspiciouspersistenceph').html(data);
		  }
	   );
			
	});

	function removeHighlighting(highlightedElements){
		highlightedElements.each(function(){
			var element = $(this);
			element.replaceWith(element.html());
		})
	}

	function addHighlighting(element, textToHighlight){
		var text = element.text();
		var highlightedText = '<em>' + textToHighlight + '</em>';
		var newText = text.replace(textToHighlight, highlightedText);
		
		element.html(newText);
	}

	$("#search").on("keyup", function() {
		var value = this.value.toLowerCase().trim();
		removeHighlighting($("table tr em"));
    $("table tr").each(function (index) {
        if (!index) return;
        $(this).find("td").each(function () {
			addHighlighting($(this), value);
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);			
            return not_found;
        });
    });
	});
	
	//iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
      checkboxClass: 'icheckbox_minimal-blue',
      radioClass: 'iradio_minimal-blue'
    });

	//$("#example1").DataTable();
	
    //$("#example1").DataTable({
    //  "paging": true,
    //  "lengthChange": false,
    //  "searching": false,
    //  "ordering": true,
    //  "info": true,
    //  "autoWidth": false
    //});

</script>
</body>
</html>
