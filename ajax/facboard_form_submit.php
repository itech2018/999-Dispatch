<?php

error_reporting(E_ALL);
require_once('../incs/functions.inc.php');
@session_start();
session_write_close();
$table = $_GET['table'];
$func = $_GET['func'];
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
switch($func) {
	case "stock_update":
	$query = "UPDATE `$GLOBALS[mysql_prefix]stock_x_facility` 
			SET `stock_level`= " . quote_smart($_POST['frm_stock_level']) . ",
			`on_order`= " . quote_smart($_POST['frm_stock_level']) . ",
			`location`= " . quote_smart($_POST['frm_stock_location']) . " WHERE `id` = " . quote_smart($_POST['frm_stock_id'])  ." LIMIT 1";
	print $query . "<BR />";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if($result) {print "Updated Stock item<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;

	case "new_stock":
	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]stock_x_facility` (
			`facility_id`, 
			`stock_item`, 
			`stock_level`, 
			`location`) 
			VALUES (
			" . quote_smart($_POST['frm_facility_id']) . ", 
			" . quote_smart($_POST['frm_stock_item']) . ", 
			" . quote_smart($_POST['frm_stock_level']) . ", 
			" . quote_smart($_POST['frm_stock_location']) . ")";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if($result) {print "Added New Stock Item<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;
	
	case "beds":
	$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET `beds_a`= " . quote_smart($_POST['frm_beds_a']) . ", `beds_o` = " . quote_smart($_POST['frm_beds_o']) . ", `beds_info` = " . quote_smart($_POST['frm_beds_info']) . " WHERE `id` = " . quote_smart($_POST['frm_facility_id'])  ." LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	$ret_arr = array();
	if($result) {
		$query = "SELECT `beds_o`, `beds_a`, `beds_info` FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . quote_smart($_POST['frm_facility_id'])  . " LIMIT 1";
		$result = mysql_query($query);
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$ret_arr[0] = $row['beds_a'];
		$ret_arr[1] = $row['beds_o'];
		$ret_arr[2] = $row['beds_info'];
		print json_encode($ret_arr);
		}
	break;
	
	case "new_stock_item";
	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]stock` (
			`name`, 
			`description`,
			`order_quantity`,
			`pack_size`,
			`reorder_level`
			) 
			VALUES (
			" . quote_smart($_POST['frm_name']) . ",
			" . quote_smart($_POST['frm_description']) . ", 
			" . quote_smart($_POST['frm_stock_order_size']) . ", 
			" . quote_smart($_POST['frm_pack_size']) . ", 
			" . quote_smart($_POST['frm_reorder_level']) . ")";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if($result) {print "Added New Stock Item<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;

	case "edit_stock_item";
	$query = "UPDATE `$GLOBALS[mysql_prefix]stock` 
		SET `name`= " . quote_smart($_POST['frm_name']) . ", 
		`description` = " . quote_smart($_POST['frm_description']) . ",
		`order_quantity` = " . quote_smart($_POST['frm_stock_order_size']) . ",
		`pack_size` = " . quote_smart($_POST['frm_pack_size']) . ",
		`reorder_level` = " . quote_smart($_POST['frm_reorder_level']) . "
		WHERE `id` = " . quote_smart($_POST['frm_id'])  ." LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	if($result) {print "Edited Stock Item<BR />";} else {print "Something went wrong, please try again<BR />";}
	break;
	
	default: 	
	return 'error';
	}
	
exit();