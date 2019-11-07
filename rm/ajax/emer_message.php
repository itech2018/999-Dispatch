<?php
/*
9/10/13 - New file, updates ticket notes for assignment from mobile screen
*/
error_reporting(E_ALL);

@session_start();
@session_write_close();
require_once('../../incs/functions.inc.php');

$ret_arr = array();
$responderid = $_GET['responderid'];
$delta = (get_variable('delta_mins') != "") ? get_variable('delta_mins') : 0;
$from = quote_smart($_SERVER['REMOTE_ADDR']);
$now = mysql_format_date(time() - ($delta*60));
if($responderid != "") {
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]emer_messages` SET 
		`ack_by` = 0,
		`ack_when` = " . quote_smart($now) .",
		`_by` = " . $responderid . ",
		`_on` = " . quote_smart($now) .",
		`_from` = ". quote_smart($_SERVER['REMOTE_ADDR']) .";";	
	$result = mysql_query($query);
	if($result) {
		$ret_arr[0] = 100;
		} else {
		$ret_arr[0] = 999;
		}	
	} else {
	$ret_arr[0] = 999;	
	}

print json_encode($ret_arr);
exit();
?>