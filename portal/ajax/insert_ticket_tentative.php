<?php
require_once('../../incs/functions.inc.php');
@session_start();
@session_write_close();
$by = (empty($_SESSION) || !array_key_exists('user_id', $_SESSION)) ? 99999 : $_SESSION['user_id'];
$delta = (get_variable('delta_mins') != "") ? intval(get_variable('delta_mins')) : 0;
$now = time() - ($delta*60);
$regions = array();
$nowTimestamp = time() - (intval(get_variable('delta_mins')*60));

$rescue_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]rescue_type`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$rescue_types[$row['id']] = $row['name'];
	}

function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if($row['email'] == "") {
			if($row['email_s'] == "") {
				$the_ret[0] = "";
				} else {
				$the_ret[0] = $row['email_s'];
				}
			} else {
				$the_ret[0] = $row['email'];
			}
		} else {
		$the_ret[0] = "";
		}
		$the_ret[1] = $row['user'];
	return $the_ret;
	}

function get_facname($id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[0] = ($row['name'] != "") ? $row['name'] : "NA";
		$street = ($row['street'] != "") ? $row['street'] : "";
		$the_ret[1] = ($street != "") ? $street . ", " . $row['city'] . ", " . $row['state']: "";
		$the_ret[2] = "Phone: " . $row['contact_phone'];
		} else {
		$the_ret[0] = "";
		$the_ret[1] = "";
		$the_ret[2] = "";
		}
	return $the_ret;
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . strip_tags($_GET['id']) . " LIMIT 1";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$thePickup = ($row['pickup'] != "") ? $row['pickup'] : "";
$theArrival = ($row['arrival'] != "") ? $row['arrival'] : "";

if(($theArrival != "") && ($thePickup == "")) {
	$theSchedTimepart = $theArrival;
	} elseif(($theArrival != "") && ($thePickup != "")) {
	$theSchedTimepart = $thePickup;	
	} elseif(($theArrival == "") && ($thePickup != "")) {
	$theSchedTimepart = $thePickup;	
	} else {
	$theSchedTimepart = NULL;
	}
$theLat = ($row['lat'] == NULL) ? 0.999999 : $row['lat'];
$theLng = ($row['lng'] == NULL) ? 0.999999 : $row['lng'];
$requestDate = strtotime($row['request_date']);
$nowplustwo = strtotime("+2 days",$nowTimestamp);
$nowdate = mysql_format_date($now);
$theStatus = ($requestDate > $nowplustwo) ? 3 : 2;
if($theStatus == 3) {
	$tempDate = explode(" ", $row['request_date']);
	$outDate = ($theSchedTimepart) ? $tempDate[0] . " " . $theSchedTimepart . ":00": "";
	$insertDate = $outDate;
	} else {
	$insertDate = $row['request_date'];
	}

if($row['requester'] == 99999) {
	$public = true;
	$the_email = $row['email'];
	$the_requester = $row['contact'];
	} else {
	$public = false;
	$theDetails = get_requester_details($by);
	$the_email = $theDetails[0];
	$the_requester = strip_tags($theDetails[1]);
	}

if($public) {
	$description = $row['description'] . "\n" . $row['comments'] . "\n";
	$description .= "Imminent Danger: " . $row['danger'] . "\n";
	$description .= "Power: " . $row['power'] . "\n";
	$description .= "Water: " . $row['water'] . "\n";
	$description .= "Food: " . $row['food'] . "\n";
	$description .= "Adults: " . $row['adults'] . "\n";
	$description .= "Children: " . $row['children'] . "\n";
	$description .= "Elderly: " . $row['elderly'] . "\n";
	$description .= "Livestock: " . $row['livestock'] . "\n";
	$description .= "About Livestock: " . $row['livestock_about'] . "\n";
	$description .= "Rescue Type: " . $rescue_types[$row['rescue_type']] . "\n";
	} else {
	$description = (($row['description'] == "") && ($row['comments'] == "")) ? "New Ticket from Portal - Tentatively Accepted " . $now : $row['description'] . $row['comments'];		
	}
	
if($public) {
	$theUsers = get_localuser($row['state']);
	foreach($theUsers as $val) {
		$query_users = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$val'";
		$result_users	= mysql_query($query_users) or do_error($query_users,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
			$regions[] = $row_users['group'];
			}
		}
	} else {
	$query_users = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]'";
	$result_users	= mysql_query($query_users) or do_error($query_users,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_users = stripslashes_deep(mysql_fetch_assoc($result_users))) 	{
		$regions[] = $row_users['group'];
		}
	}

$ret_arr = array();
$query = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`in_types_id`,
				`org`,
				`contact`,
				`street`, 
				`city`, 
				`state`, 
				`phone`, 
				`to_address`,
				`facility`,
				`rec_facility`,
				`lat`,
				`lng`,
				`booked_date`,
				`date`,
				`problemstart`, 
				`scope`, 
				`description`, 
				`status`, 
				`owner`, 
				`severity`, 
				`updated`, 
				`_by` 
			) VALUES (
				0, 
				0,
				'" . $row['contact'] . "', 
				'" . $row['street'] . "', 
				'" . $row['city'] . "', 
				'" . $row['state'] . "', 
				'" . $row['phone'] . "', 
				'" . $row['to_address'] . "', 
				" . $row['orig_facility'] . ", 				
				" . $row['rec_facility'] . ", 
				" . $theLat . ", 
				" . $theLng . ", 
				'" . $insertDate . "', 
 				" . quote_smart($nowdate) . ",
 				" . quote_smart($nowdate) . ", 
				'" . $row['scope'] . "', 
				'" . $description . "', 
				" . $theStatus . ", 
				" . $by . ",  
				0, 
 				" . quote_smart($nowdate) . ",  
				" . $by . ")";

$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
if($result) {
	$last_id = mysql_insert_id();
	
	$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Tentative', `tentative_date` = '" .$now . "', `ticket_id` = " . $last_id . " WHERE `id` = " . strip_tags($_GET['id']);
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	

	$temp = get_variable('_inc_num');										// 3/2/11
	$inc_num_ary = (strpos($temp, "{")>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));
	$theScope = $row['scope'];

	if ($inc_num_ary[0] == 0 ) {
		switch (get_variable('serial_no_ap')) {
			case 0:								/*  no serial no. */
				$theScope = $row['scope'];
				break;
			case 1:								/*  prepend  */
				$theScope =  $last_id . "/" . $row['scope'];
				break;
			case 2:								/*  append  */
				$theScope = $row['scope'] . "/" .  $last_id;
				break;
			default:							/* error????  */
				$theScope = " error  error  error ";
			}				// end switch
		}		// end if()
	
	$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `scope` = '" . $theScope . "' WHERE `id` = " .$last_id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
		
	foreach ($regions as $grp_val) {
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
				($grp_val, 1, '$now', 2, $last_id, 'Allocated to Group' , $by)";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
		}
	
	do_log($GLOBALS['LOG_INCIDENT_OPEN'], $last_id);	
	} else {
	$last_id = 0;
	}

if($last_id != 0) {
	$ret_arr[0] = $last_id;
	$to_str1 = "";
	$smsg_to_str1 = "";
	$subject_str1 = "";
	$text_str1 = "";	
	$to_str2 = "";
	$smsg_to_str2 = "";
	$subject_str2 = "";
	$text_str2 = "";		
	$to_str3 = "";
	$smsg_to_str3 = "";
	$subject_str3 = "";
	$text_str3 = "";	
	$the_summary = "Request from " . $the_requester . "\r\n";
	$the_summary .= get_text('Scope') . ": " . $theScope . "\r\n\r\n";	
	$the_summary .= get_text('Patient') . " name: " . $row['contact'] . "\r\n";
	$the_summary .= get_text('Street') . ": " . $row['street'] . ", ";	
	$the_summary .= get_text('City') . ": " . $row['city'] . ", ";	
	$the_summary .= get_text('State') . ": " . $row['state'] . "\r\n";	
	$the_summary .= get_text('Contact Phone') . ": " . $row['phone'] . "\r\n";
	$orig_Fac = ($row['orig_facility'] != "0") ? get_facname($row['orig_facility']) : "";
	$rec_Fac =  ($row['rec_facility'] != "0") ? get_facname($row['rec_facility']) : "";
	$the_summary .= ((is_array($orig_Fac)) && ($orig_Fac[0] != "")) ? "Originating Facility " . $orig_Fac[0] . "\nAddress: " . $orig_Fac[1] . "\nPhone " . $orig_Fac[2] . "\r\n" : "";
	$the_summary .= ((is_array($rec_Fac)) && ($rec_Fac[0] != "")) ? "Receiving Facility " . $rec_Fac[0] . "\nAddress: " . $rec_Fac[1] . "\nPhone " . $rec_Fac[2] . "\r\n" : "";
	$the_summary .= get_text('Description') . "\r\n" . $description . "\r\n";	
	$the_summary .= get_text('Comments') . "\r\n" . $row['comments'] . "\r\n";	
	$the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($row['request_date'])) . "\r\n";			

	if ($the_email != "") {				// requester address?
		$to_str1 = $the_email;
		$smsg_to_str1 = "";
		if($row['requester'] == 99999) {
			$subject_str1 = "Your request has been tentatively accepted";
			$text_str1 = "Your Request has been tentatively accepted\n\n";
			$text_str1 .= "When a someone has been found to fulfil the job, the request will be fully accepted and you will recveive another email\r\n";
			$text_str1 .= "Request Summary\n\n" . $the_summary;
			} else {
			$subject_str1 = "Your request " . $row['scope'] . " has been tentatively accepted";
			$text_str1 = "Your Request " . $row['scope'] . " tentatively accepted\r\n"; 
			$text_str1 .= "When a someone has been found to fulfil the job, the request will be fully accepted and you will recveive another email\r\n";
			$text_str1 .= "Request Summary\n\n" . $the_summary;
			}
		}				// end if/else ($the_email)			
	
	$ret_arr[1] = $to_str1;
	$ret_arr[2] = $smsg_to_str1;
	$ret_arr[3] = $subject_str1;
	$ret_arr[4] = $text_str1;	
	$ret_arr[5] = $to_str2;
	$ret_arr[6] = $smsg_to_str2;
	$ret_arr[7] = $subject_str2;
	$ret_arr[8] = $text_str2;
	$ret_arr[9] = $to_str3;
	$ret_arr[10] = $smsg_to_str3;
	$ret_arr[11] = $subject_str3;
	$ret_arr[12] = $text_str3;	
	} else {
	$ret_arr[0] = 0;
	}

print json_encode($ret_arr);
exit();
?>