<?php
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
if($_GET['q'] != $_SESSION['id']) {
	exit();
	}
$output_arr = array();
$query = "SELECT `s`.`id` AS `stock_item`,
		`s`.`name` AS `stock_name`,
		`s`.`description` AS `stock_description`,
		`s`.`reorder_level` AS `reorder_level`,
		`sx`.`on_order` AS `on_order`,
		`sx`.`stock_level` AS `stock_level`,
		`sx`.`location` AS `location`,
		`sx`.`id` AS `id`
		FROM `$GLOBALS[mysql_prefix]stock_x_facility` `sx` 
		LEFT JOIN `$GLOBALS[mysql_prefix]stock` `s` ON (`sx`.`stock_item` = `s`.`id`)
		WHERE `sx`.`facility_id` = " . $_GET['facility_id'] . " ORDER BY `sx`.`stock_item`, `location`";
$result = mysql_query($query);
$i=0;
if($result && mysql_num_rows($result) > 0) {
	$output_arr[0][0] = mysql_num_rows($result);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
		$output_arr[$i][0] = $row['id'];
		$output_arr[$i][1] = $row['stock_name'];
		$output_arr[$i][2] = $row['stock_level'];
		$output_arr[$i][3] = $row['reorder_level'];
		$output_arr[$i][4] = $row['on_order'];
		$output_arr[$i][5] = $row['location'];
		$i++;
		}
	} else {
	$output_arr[0][0] = 0;		
	}
print json_encode($output_arr);

exit();
?>