
<?php

include ("header.php");

if (isset($_GET['processID'])) {	
	$GETParentName = htmlentities(trim(htmlspecialchars(addslashes($_GET['parentName']))));		
	$GETParentProcessID = htmlentities(trim(htmlspecialchars(addslashes($_GET['parentProcessId']))));		
	$GETProcessID = htmlentities(trim(htmlspecialchars(addslashes($_GET['processID']))));		
	$GETServerID = htmlentities(trim(htmlspecialchars(addslashes($_GET['serverID']))));		

echo '<div id="tree-container"></div>';
	echo '<script>';
	
	echo 'var data = [';
	$query = "DECLARE @ID INT
	DECLARE @SERVERID INT
	DECLARE @PARENTNAME VARCHAR(100)

	SELECT @ID = $GETProcessID
	SELECT @SERVERID = $GETServerID
	SELECT @PARENTNAME = '$GETParentName'

	;WITH ret AS(
			SELECT  tt.processID, tt.parentProcessId, tt.name childName, ISNULL(f.Name, @PARENTNAME) parentName
			FROM    [NOAH].[dbo].[ProcessTreeAudited] tt
			LEFT JOIN [NOAH].[dbo].[ProcessTreeAudited] f ON f.processID = tt.parentProcessId and f.serverID = tt.serverID
			WHERE   tt.processID = @ID 
			AND tt.serverID = @SERVERID

			UNION ALL
			SELECT  t.processID, t.parentProcessId, t.name childName, ISNULL(f.Name, @PARENTNAME) parentName
			FROM    [NOAH].[dbo].[ProcessTreeAudited] t 
			INNER JOIN [NOAH].[dbo].[ProcessTreeAudited] f ON f.processID = t.parentProcessId and f.serverID = t.serverID
			INNER JOIN ret r ON t.parentProcessId = r.processID and t.serverID = @SERVERID
	)

	SELECT  *
	FROM    ret";	
		$result = sqlsrv_query($conn, $query); 
		$jsonArray = array();
		//echo '{ "name" : "Root ('.$row['processID'].')", "parent":"'.$row['parentName'].' ('.$row['parentProcessId'].')", "icon": '.$img.' },';
		echo '{ "name" : "'.$GETParentName.' ('.$GETParentProcessID.')", "parent":"End", "icon": "graph/explorer.png" },';
		while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) { 
			$img = imgGraph($row['childName']);
			$detailsProcessSQL = "SELECT CommandLine FROM [NOAH].[dbo].[ProcessTreeAudited] WHERE serverID = $GETServerID AND processID = ".$row['processID'];							
			$getdetailsProcess = sqlsrv_query($conn, $detailsProcessSQL);  
			if ( $getdetailsProcess === false)  
			die( print_r( sqlsrv_errors(), true));
			$commandLine = "";
			if( $rowDetails = sqlsrv_fetch_array( $getdetailsProcess, SQLSRV_FETCH_ASSOC)) { 
				$commandLine = $string = str_replace('"', '', $rowDetails['CommandLine']);
				$commandLine = truncateForceLength($commandLine, 80, "...");
			}
			echo '{ "name" : "'.$row['childName'].' ('.$row['processID'].')", "parent":"'.$row['parentName'].' ('.$row['parentProcessId'].')", "icon": '.$img.', "cmdline": "'.$commandLine.'" },';
		}		
	echo '];';	
}
?>


// *********** Convert flat data into a nice tree ***************
// create a name: node map
var dataMap = data.reduce(function(map, node) {
	map[node.name] = node;
	return map;
}, {});

// ************** Generate the tree diagram	 *****************
// create the tree array
var treeData = [];
data.forEach(function(node) {
	// add to parent
	var parent = dataMap[node.parent];
	if (parent) {
		// create child array if it doesn't exist
		(parent.children || (parent.children = []))
			// add node to child array
			.push(node);
	} else {
		// parent is null or missing
		treeData.push(node);
	}
});


var margin = {top: 20, right: 120, bottom: 20, left: 180},
    width = 1440 - margin.right - margin.left,
    height = 500 - margin.top - margin.bottom;

var i = 0,
    duration = 300,
    root;

var tree = d3.layout.tree()
    .size([height, width]);

var diagonal = d3.svg.diagonal()
    .projection(function(d) { return [d.y, d.x]; });


		
var svg = d3.select("#tree-container").append("svg")
    .attr("width", width + margin.right + margin.left)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

//d3.json("flare.json", function(error, flare) {
//  if (error) throw error;

  root = treeData[0];
  root.x0 = height / 2;
  root.y0 = 0;

  function collapse(d) {
    if (d.children) {
      d._children = d.children;
      d._children.forEach(collapse);
      d.children = null;
    }
  }

  root.children.forEach(collapse);
  update(root);
//});

d3.select(self.frameElement).style("height", "800px");

function update(source) {

  // Compute the new tree layout.
  var nodes = tree.nodes(root).reverse(),
      links = tree.links(nodes);

  // Normalize for fixed-depth.
  nodes.forEach(function(d) { d.y = d.depth * 180; });

  // Update the nodes…
  var node = svg.selectAll("g.node")
      .data(nodes, function(d) { return d.id || (d.id = ++i); });

  // Enter any new nodes at the parent's previous position.
  var nodeEnter = node.enter().append("g")
      .attr("class", "node")
      .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
      .on("click", click)
	  .on("mouseover", mouseover)
      .on("mouseout", mouseout);

  nodeEnter.append("circle")
      .attr("r", 1e-6)
      .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });
	  
  nodeEnter.append("image")
      .attr("xlink:href", function(d) { return d.icon; })
      .attr("x", "12px")
      .attr("y", "-6px");
  
  nodeEnter.append("text")
      .attr("x", function(d) { return d.children || d._children ? -10 : 35; })
      .attr("dy", ".35em")
      .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
      .text(function(d) { return d.name; })
      .style("fill-opacity", 1e-6);

  // Transition nodes to their new position.
  var nodeUpdate = node.transition()
      .duration(duration)
      .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

  nodeUpdate.select("circle")
      .attr("r", 4.5)
      .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

  nodeUpdate.select("text")
      .style("fill-opacity", 1);

  // Transition exiting nodes to the parent's new position.
  var nodeExit = node.exit().transition()
      .duration(duration)
      .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
      .remove();

  nodeExit.select("circle")
      .attr("r", 1e-6);

  nodeExit.select("text")
      .style("fill-opacity", 1e-6);

  // Update the links…
  var link = svg.selectAll("path.link")
      .data(links, function(d) { return d.target.id; });

  // Enter any new links at the parent's previous position.
  link.enter().insert("path", "g")
      .attr("class", "link")
      .attr("d", function(d) {
        var o = {x: source.x0, y: source.y0};
        return diagonal({source: o, target: o});
      });

  // Transition links to their new position.
  link.transition()
      .duration(duration)
      .attr("d", diagonal);

  // Transition exiting nodes to the parent's new position.
  link.exit().transition()
      .duration(duration)
      .attr("d", function(d) {
        var o = {x: source.x, y: source.y};
        return diagonal({source: o, target: o});
      })
      .remove();

  // Stash the old positions for transition.
  nodes.forEach(function(d) {
    d.x0 = d.x;
    d.y0 = d.y;
  });
}
function mouseover(d) {
    d3.select(this).append("text")
        .attr("class", "hover")
        .attr('transform', function(d){ 
            return 'translate(5, -10)';
        })
        .text(d.cmdline);
}

// Toggle children on click.
function mouseout(d) {
    d3.select(this).select("text.hover").remove();
}

// Toggle children on click.
function click(d) {
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
  }
  update(d);
}

</script>
<?php
echo '
</body>
</html>';