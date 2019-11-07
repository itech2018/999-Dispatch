<?php
/*
msg_status.php - used by message.php to change read status of a message
10/23/12 - new file
*/
require_once('../incs/functions.inc.php');
extract($_GET);
@session_start();
session_write_close();
$result_code = 0;

/*	Fix code to remove dupes in readers

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = 11773";
$result = mysql_query($query);
if(mysql_num_rows($result) > 0) {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_id = $row['id'];
		$temp = explode(",", $row['readby']);
		$readby = implode(",", array_unique($temp));
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `readby` = '" . $readby . "' WHERE `id` = " . $the_id;
		$result2 = mysql_query($query2);
		if($result2) {
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = " . $the_id;
			$result3 = mysql_query($query3);
			$row3 = stripslashes_deep(mysql_fetch_assoc($result3));
			print $row3['readby'] . "<BR />";
			}
		}
	}


$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
$result = mysql_query($query);
if(mysql_num_rows($result) > 0) {
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_id = $row['id'];
		$temp = explode(",", $row['readby']);
		$readby = implode(",", array_unique($temp));
		$query2 = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `readby` = '" . $readby . "' WHERE `id` = " . $the_id;
		$result2 = mysql_query($query2);
		if($result2) {
			$query3 = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = " . $the_id;
			$result3 = mysql_query($query3);
			$row3 = stripslashes_deep(mysql_fetch_assoc($result3));
			}
		}
	}

*/

$the_users = array();	
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$the_users[] = $row['id'];
	}	

$id = clean_string($id);

$count_users = count($the_users);
$the_status = ($status == "read") ? 1 : 0;
$where = ($id == 0) ? "" : " WHERE `id` = '" . $id . "'";
$selected = array_key_exists('selected', $_GET) ? strip_tags($_GET['selected']) : "";
$selected_arr = array();
if($selected != "") {
	$selected_arr = explode("|", $selected);
	if(count($selected_arr) == 1) {
		$id = $selected_arr[0];
		}
	} else {
	$id = 0;
	}

$the_messages = array();
$the_readers = array();
$the_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$the_folder = $_GET['folder'];
if($the_folder == 'inbox') {
	$msgDirection = " AND (`msg_type` = 2 OR `msg_type` = 4 OR `msg_type` = 5)";
	} elseif($the_folder == 'sent') {
	$msgDirection = " AND (`msg_type` = 1 OR `msg_type` = 3)";
	} else {
	$msgDirection = "";
	}


if($id == 0) {	// It's a read or unread all
	if(count($selected_arr) < 2) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages`";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if(mysql_num_rows($result) > 0) {
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$the_id = $row['id'];
				$temp = explode(",", $row['readby']);
				$readby = implode(",", array_unique($temp));
				$the_messages[$the_id][0] = $row['id'];
				$the_messages[$the_id][1] = $readby;
				}
			}
		} else {
		foreach($selected_arr as $theID) {
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = " . intval($theID);
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_assoc($result));
			if($row['id'] != "") {
				$the_id = intval($row['id']);
				$temp = explode(",", $row['readby']);
				$readby = implode(",", array_unique($temp));
				$the_messages[$the_id][0] = $row['id'];
				$the_messages[$the_id][1] = $readby;
				}
			}			
		}
		
	if($status == "read") {
		foreach($the_messages AS $key => $val) {
			$the_message = $val[0];
			$the_readers = ($val[1] != "") ? explode(",", $val[1]): NULL;
			$the_new_status = (count($the_readers) == count($the_users)) ? 2 : 1;	
			if($the_readers != NULL) {
				if(!in_array($the_user, $the_readers, true)) {
					$temp0 = ($the_readers != NULL) ? implode(",", array_unique($the_readers)) . "," . $the_user: $the_user;
					$temp1 = explode(",", $temp0);
					$the_new_readers = implode(",", array_unique($temp1));
					$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $the_new_readers . "' WHERE `id` = " . $val[0];
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);	
					$result_code++;
					} else {
					$temp = implode(",", array_unique($the_readers));
					$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $temp . "' WHERE `id` = " . $val[0];
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);		
					$result_code++;						
					}
				} else {
				$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby` = '" . $the_user . "' WHERE `id` = " . $val[0];
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);		
				$result_code++;				
			}
		}
			
	} elseif($status == "unread") {
		foreach($the_messages AS $val) {		
			$the_message = $val[0];
			$the_readers = explode(",", $val[1]);
			$the_new_readers = array();
			$the_new_status = (count($the_readers) == count($the_users)) ? 2 : 1;	
			foreach($the_readers as $val2) {
				if($val2 != $the_user) {
					$the_new_readers[] = $val2;
					}
				}
			$the_new_readers = $the_new_readers;
			$the_new_readers2 = implode(",", array_unique($the_new_readers));
			$the_new_status = (count($the_new_readers) >= 1) ? 1 : 0;
			$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $the_new_readers2 . "' WHERE `id` = " . $val[0];
			$result = mysql_query($query);
			$result_code++;
			}
	} else {
	//	Do nothing.
	}
	
} else { //	it's an individual message read status change
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]messages` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$readby = $row['readby'];
	
	if($status == "read") {	
		$the_message = $id;
		$the_readers = array_unique(explode(",", $readby));
		$the_new_status = (count($the_readers) == count($the_users)) ? 2 : 1;		
		if(!array_search($the_user, $the_readers, true)) {
			$the_new_readers = implode(",", $the_readers) . "," . $the_user;
			$the_new_readers = array_unique($the_new_readers);
			$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $the_new_readers . "' WHERE `id` = " . $the_message;
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
			$result_code++;			
			} else {
			$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $the_readers . "' WHERE `id` = " . $the_message;
			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);	
			$result_code++;			
			}
			
	} elseif($status == "unread") {
		$the_message = $id;
		$the_readers = array_unique(explode(",", $readby));
		$the_new_readers = array();
		$the_new_status = (count($the_readers) == count($the_users)) ? 2 : 1;	
		foreach($the_readers as $val) {
			if($val != $the_user) {
				$the_new_readers[] = $val;
				}
			}
		$the_new_readers2 = implode(",", $the_new_readers);
		$the_new_readers2 = array_unique($the_new_readers2);
		$the_new_status = (count($the_new_readers) >= 1) ? 1 : 0;
		$query = "UPDATE `$GLOBALS[mysql_prefix]messages` SET `read_status` = " . $the_new_status . ", `readby`= '" . $the_new_readers2 . "' WHERE `id` = " . $the_message;
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		$result_code++;		
		}
	}

if($result_code >= 1) {
	$response[] = 100;
	} else {
	$response[] = 200;
	}
	
print json_encode($response);
exit();
?>