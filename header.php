<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
      
<style>

.node {
  cursor: pointer;
}

.node circle {
  fill: #fff;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.node text {
  font: 10px sans-serif;
}

.link {
  fill: none;
  stroke: #ccc;
  stroke-width: 1.5px;
}

em{ background-color: yellow }

.contents{margin: 20px;padding: 20px;list-style: none;background: #F9F9F9;border: 1px solid #ddd;border-radius: 5px;}
.contents li{margin-bottom: 10px;}
.loading-div{position: absolute;top: 0;left: 0;width: 100%;height: 100%;background: rgba(0, 0, 0, 0.56);z-index: 999;display:none;}
.loading-div img {margin-top: 20%;margin-left: 50%;}
/* Pagination style */
.pagination2{  margin: 0px;padding-left:0px;padding-bottom: 10px;padding-top: 10px;}
.pagination2 li{display: inline;padding: 10px 6px 10px 6px;border: 1px solid #ddd;margin-right: -1px;font: 15px/20px;background: #fafafa;}
.pagination2 li.first {border-radius: 4px 0px 0px 4px;}
.pagination2 li.last {border-radius: 0px 4px 4px 0px;}
.pagination2 li:hover{background: #eee;cursor: default;}
.pagination2 li.active{color: #fff;
  cursor: default;
  background-color: #337ab7;border: 1px solid; border-color: #337ab7;padding: 10px 16px 10px 16px;
  }
.pagination2 li a{color: #666;padding: 12px 16px 12px 16px;}

</style>
  <title>NOAH | Dashboard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/jquery-ui.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link href="plugins/iCheck/all.css" rel="stylesheet">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  
  <!-- jQuery 2.2.3 -->
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- jQuery UI  -->
<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparkline/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll 1.3.0 -->
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="plugins/iCheck/icheck.min.js"></script>
<!-- ChartJS 1.0.1 -->
<script src="plugins/chartjs/Chart.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard2.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
  <script src="plugins/d3js/d3.v3.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini"> <!--   sidebar-collapse -->
<div class="wrapper">

  <header class="main-header">

    <!-- Logo -->
    <a href="index.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>N</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>NOAH</b></span>
    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>


    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <!-- <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image"> -->
        </div>
        <div class="pull-left info">
           <!-- <p>Alexander Pierce</p> -->
          <!-- <a href="#"><i class="fa fa-circle text-success"></i> Online</a> -->
        </div>
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
	  <?php
		if (isset($_GET['active'])) {	
			$active = htmlentities(trim(htmlspecialchars(addslashes($_GET['active']))));
		}
		else {			
			$active = '';
		}
	  ?>
      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>
		<?php 
		if($active == 'main' || $active == '') {
			echo '<li class="active treeview">';
		}
		else {
			echo '<li class="treeview">';
		}
		?>
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Main Menu</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">            
            <li class="active"><a href="index.php?active=main"><i class="fa fa-circle-o"></i> Dashboard</a></li>	
			<li class="active"><a href="launchhunt.php?active=main"><i class="fa fa-circle-o"></i> Launch Hunt</a></li>		
			<li class="active"><a href="hunt.php?active=main"><i class="fa fa-circle-o"></i> Previous Hunts</a></li>			
          </ul>
        </li>
		<?php 
		if($active == 'device') {
			echo '<li class="active treeview">';
		}
		else {
			echo '<li class="treeview">';
		}
		?>
          <a href="#">
            <i class="fa fa-laptop"></i>
            <span>Device</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
		    <li class="active"><a href="magichunt.php?active=device"><i class="fa fa-circle-o"></i> Magic Hunt</a></li>            
			<li class="active"><a href="amcache.php?active=device"><i class="fa fa-circle-o"></i> AM Cache</a></li>		
			<li class="active"><a href="browserhistory.php?active=device"><i class="fa fa-circle-o"></i> Browser History</a></li>			
			<li class="active"><a href="dnscache.php?active=device"><i class="fa fa-circle-o"></i> DNS Cache</a></li>
			<li class="active"><a href="autoruns.php?active=device"><i class="fa fa-circle-o"></i> Persistence</a></li>
			<li class="active"><a href="prefetch.php?active=device"><i class="fa fa-circle-o"></i> Prefetch</a></li>			
			<li class="active"><a href="processtree.php?active=device"><i class="fa fa-circle-o"></i> Process Tree Count</a></li>
			<li class="active"><a href="processtreeserver.php?active=device"><i class="fa fa-circle-o"></i> Process Tree / Server</a></li>
			<li class="active"><a href="runmru.php?active=device"><i class="fa fa-circle-o"></i> Run Most Recent USed</a></li>
			<li class="active"><a href="access.php?active=device"><i class="fa fa-circle-o"></i> User Access</a></li>			
          </ul>
        </li>
             
               
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
    <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      
<?php
$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
}
include_once("function.php");
$huntGUID='';
$serverID='';
if (isset($_GET['hunt'])) {
	$huntGUID = htmlentities(trim(htmlspecialchars(addslashes($_GET['hunt']))));
}
if (isset($_GET['serverID'])) {	
	$serverID = htmlentities(trim(htmlspecialchars(addslashes($_GET['serverID']))));	
}
if (isset($_GET['pageSwitch'])) {
	$pageSwitch = htmlentities(trim(htmlspecialchars(addslashes($_GET['pageSwitch']))));
}
?>