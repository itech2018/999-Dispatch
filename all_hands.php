<?php
/*
3/15/11 new release
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$ret_arr = array();

function update_setting ($which, $what) {		//	3/15/11
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '" . $which . "' AND `value` <> '" . $what . "' LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result)!=0) {
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '" . $which . "'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function update_setting ()


if(get_variable('all_hands') == "0") {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]region` ORDER BY `id` ASC;";
	$result = mysql_query($query);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$al_groups[] = $row['id'];
		}
		
	$f_n = "user_groups";
	$v_n = $al_groups; 
	$_SESSION[$f_n] = $v_n;

	$f_n = "viewed_groups";
	$v_n = implode(",",$al_groups); 
	$_SESSION[$f_n] = $v_n;
	} else {
	$query_gp = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = " . $_SESSION['user_id'] . " ORDER BY `id` ASC;";
	$result_gp = mysql_query($query_gp);
	while ($row_gp = stripslashes_deep(mysql_fetch_assoc($result_gp))) 	{	//	6/10/11
		$al_groups[] = $row_gp['group'];
		}

	$f_n = "user_groups";
	$v_n = $al_groups; 
	$_SESSION[$f_n] = $v_n;
	unset($_SESSION['viewed_groups']);
	}
$ret_arr[0] = implode($al_groups);
$ret_arr[1] = (isset($_SESSION['viewed_groups'])) ? $_SESSION['viewed_groups'] : "Not Set";
$ret_arr[2] = intval(get_variable("all_hands"));
session_write_close();
print json_encode($ret_arr);
?>