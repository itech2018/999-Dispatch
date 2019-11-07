<?php
/*
*/
error_reporting(E_ALL);

@session_start();
require_once('./incs/functions.inc.php');
extract($_GET);
$now = time() - (get_variable('delta_mins')*60);

$query = "UPDATE `$GLOBALS[mysql_prefix]member` SET `field21`= ";
$query .= quote_smart($frm_status_id) ;
$query .= ", `_on` = " . quote_smart(mysql_format_date($now));
$query .= ", `_by` = " . $_SESSION['user_id'];
$query .= " WHERE `id` = ";
$query .= quote_smart($frm_member_id);
$query .=" LIMIT 1";

$result = mysql_query($query) or do_error($query, "", mysql_error(), basename( __FILE__), __LINE__);

do_log($GLOBALS['LOG_MEMBER_STATUS'], $frm_member_id, $frm_status_id);
	
set_sess_exp();				// update session time
print date("H:i", $now) ;
?>
