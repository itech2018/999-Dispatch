<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$output_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stock`";
$result = mysql_query($query);
$i=0;
if(mysql_num_rows($result) > 0) {
	$output_arr[0][0] = mysql_num_rows($result);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$output_arr[$i][1] = $row['id'];
		$output_arr[$i][2] = $row['name'];
		$output_arr[$i][3] = $row['description'];
		$output_arr[$i][4] = $row['order_quantity'];
		$output_arr[$i][5] = $row['pack_size'];
		$output_arr[$i][6] = $row['reorder_level'];
		$i++;
		}
	} else {
	$output_arr[0][0] = 0;		
	}
	
print json_encode($output_arr);

exit();
?>