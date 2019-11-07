<?php
/*
Sets All Hands setting on or off
*/
error_reporting(E_ALL);

require_once('./incs/functions.inc.php');
$ret_arr = array();

function get_setting($which) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '" . $which . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['value'];
	}

function update_setting ($which, $what) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '" . $which . "' AND `value` <> '" . $what . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$ret_val = intval(get_setting('all_hands'));
		} else {
		$ret_val = 0;
		}
	return $ret_val;
	}				// end function update_setting ()
	
$original_setting = intval(get_setting('all_hands'));

if(intval(get_setting('all_hands')) == 0) {
	$the_return = update_setting("all_hands", "1");
	} else {
	$the_return = update_setting("all_hands", "0");
	}

$ret_arr[0] = $the_return;
print json_encode($ret_arr);
?>