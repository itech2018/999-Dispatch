<?php

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
if (empty($_SESSION)) {
	header("Location: index.php");
	}

require_once('incs/functions.inc.php');		//7/28/10
do_login(basename(__FILE__));

$in_win = (array_key_exists("mode", $_GET)) ? 1 : 0;
$from_mi = (array_key_exists("mi", $_GET)) ? 1 : 0;
$gmaps = $_SESSION['internet'];

if($istest) {print "_GET"; dump($_GET);}
if($istest) {print "_POST"; dump($_POST);}
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$states[$row['name']] = $row['code'];
	}
/*
10/28/07 added onLoad = "document.add.frm_lat.disabled..
11/38/07 added frame jump prevention
11/98/07 added map under image
5/29/08  added do_kml() call
8/11/08	 added problem-start lock/unlock
8/23/08	 added usng handling
8/23/08  corrected problem-end hskpng
9/9/08	 added lat/lng-to-CG format functions
10/4/08	 added function do_inc_name()
10/7/08	 set WRAP="virtual"
10/8/08 synopsis made non-mandatory
10/15/08 changed 'Comments' to 'Disposition'
10/16/08 changed ticket_id to frm_ticket_id
10/17/08 removed 10/16/08 change
10/19/08 added insert_id to description
12/6/08 allow user input of NGS values; common icon marker function
1/11/09 TBD as default, auto_route setting option
1/17/09 replaced ajax functions - for consistency
1/18/09 added script-specific CONSTANTS
1/19/09 added geocode function
1/21/09 show/hide butts
1/22/09 - serial no. to ticket description
1/25/09 serial no. pre-set
1/27/09 area code vaiable added
2/4/09  added function get_res_row()
2/10/09 added function sv_win()
2/11/09 added dollar function, streetview functions
3/3/09 cleaned trash as page bottom
3/10/09 intrusive space in ticket_id
4/30/09 $ replaces document.getElementById, USNG text underline
7/7/09	added protocol handling
7/16/09	zero to in_types_id
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/UTM/UTM dependant on variable in tabs and sidebar.
8/13/09	'date' = now added to UPDATE
9/22/09 Added set Incident at a Facility functionality
9/29/09	'frequent fliers' added
10/1/09 added special ticket type - for pre-booked tickets
10/2/09	added locale check for WP lookup
10/6/09 Added Mouseover help text to all field labels.
10/6/09 Added Receiving Facility, added links button
10/12/09 Incident at facility menu is hidden by default - click radio button to show.
10/13/09 Added reverse geocoding - map click now returns address and location to form.
11/01/09 Added use of reverse_geo setting to switch off reverse geocoding if not required - default is off.
11/06/09 Changed "Special" incident type to "Scheduled".
11/06/09 Moved both Facility dropdown menus to the same area
12/16/09 added call-history operation
1/3/10 added '_by' field for multi-user call-taker id
3/13/10 present constituents 'miscellaneous'
3/18/10 corrections to facilities options list
3/24/10 made facilities input conditioned on existence, logging revised
4/21/10 provided for changed NOC/name values - per AF  email
4/27/10 try geo-code on failed phone lookup
5/6/10 accommodate embedded quotes
6/20/10 handle negative delta's, NULL forced, 'NULL' un-quoted
6/25/10 guest/member notification changed
6/26/10 911 field handling added
7/5/10 Revised reverse geocoding function - per AH
7/11/10 'NULL'  to 0
7/22/10 miscjs, google reverse geocode parse added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/7/10 protocol reset house-keeping
8/13/10 map.setUIToDefault(), get_text settings
9/30/10 use '_by' as the match identifier, booking button name disambiguated
10/21/10 onload focus(), tabindex added
11/5/10 revised to prepare for callerid handling
11/13/10 incident numbering added
11/23/10 'state' size made locale-dependent
11/29/10 locale 2 handling added
12/1/10 get_text changes
12/18/10 set signals added
1/1/11 Titles array added, scheduled incidents revised
1/19/10 revised to accommodate both maps and no-maps option
1/21/11 corrections to booked-date handling
1/24/11 corrections to locale/grid handling
1/29/11	changed coordinates test to string-length
2/1/11 added table 'hints' as hints source
2/11/11 condition signals on non-empty table
2/12/11 facility names shortened
2/19/11 draggable button bar replaces tr
2/27/11 corrected 'append incident nature to incident name'
3/2/11 added  base64_decode to serialize/unserialize
3/15/11 changed default.css to stylesheet.php to cater for day / night capability
3/17/11 Revised form to not use default city if places exist in the places table
4/23/11 revisions for USNG handling
5/22/11 corrected reverse geo-location lookup
6/9/11 action and patient buttons, cancel function added, caller id
6/10/11 added changes required to support regional capability (Ticket region assignment).
6/23/11 revised target for action and patient buttons
11/22/2012 'nearby' capability added
12/1/2012 show 'nearby' only if internet/maps
5/22/2013 added broadcast call
6/2/2013 - reverse geocode added
7/3/2013 - socket2me conditioned on internet and broadcast settings, enforce reverse-geo values size limits
9/10/13 - Added "address about" and "To address" fields. Also Location warnings capability
10/11/2013 - corrected auto incident numbering - relocated else {} closure
3/29/2014 - added buildings operations
4/7/2014 - revised per nm operation
4/27/2014 - correction re bldg per YG email, also do_reset() kml re-arrangement
5/23/2015 - revised to handle new 'addr_source' functions
10/3/2015 - revised to accommodate V3 map functions (AS)
*/
/* $temp = get_variable('_inc_num');										// 3/2/11
$inc_num_ary = (strpos($temp, "{")>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));
dump($inc_num_ary); */
if (empty($_GET)) {
	if (mysql_table_exists("$GLOBALS[mysql_prefix]caller_id")) {				// 6/9/11
		$cid_calls = $cid_name = $cid_phone = $cid_street = $cid_city = $cid_state = $cid_lat = $cid_lng = "";
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]caller_id` WHERE `status` = 0 LIMIT 1;";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
			$row = stripslashes_deep(mysql_fetch_array($result));
			$query = "UPDATE `$GLOBALS[mysql_prefix]caller_id` SET `status` = 1 WHERE `id` = " . quote_smart($row['id']);
			$result = mysql_query($query);
			$lookup_vals = explode (";", $row['lookup_vals']);
			$cid_calls = $lookup_vals[0];
			$cid_name = $lookup_vals[1];
			$cid_phone = $lookup_vals[2];
			$cid_street = $lookup_vals[3];
			$cid_city = $lookup_vals[4];
			$cid_state = $lookup_vals[5];
			$cid_lat = $lookup_vals[7];
			$cid_lng = $lookup_vals[8];
			$cid_id = $row['id'];			// id of caller id record
			}
		}				// end if(empty())
	}

$current_facilities = array();												// 9/22/09
$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `id`";		// types in use
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_f = stripslashes_deep(mysql_fetch_assoc($result_f))) {
	$current_facilities [$row_f['id']] = array ($row_f['name'], $row_f['lat'], $row_f['lng']);
	}
$facilities = mysql_affected_rows();		// 3/24/10

$protocols = array();
$query_in = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
$result_in = mysql_query($query_in);
while ($row_in = stripslashes_deep(mysql_fetch_assoc($result_in))) {
	if($row_in['protocol'] != "") {$protocols[$row_in['id']] = addslashes($row_in['protocol']);}
	}

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00'";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$num_mi = mysql_num_rows($result);

function get_res_row() {				// writes empty ticket if none exists - returns a row - 11/5/10
	$by = $_SESSION['user_id'];			// 5/27/10

	$query  = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`
		WHERE `status`= '{$GLOBALS['STATUS_RESERVED']}'
		AND  `_by` = '{$by}' LIMIT 1";

	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_num_rows($result) == 1) {							// any ?
		$row = stripslashes_deep(mysql_fetch_array($result));	// yes, return it
		}
	else {				// insert empty STATUS_RESERVED row
		$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`id` , `in_types_id` , `contact` , `street` , `address_about` , `city` , `state` , `phone` , `to_address` , `lat` , `lng` , `date` ,
				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` ,
				`severity` , `updated`, `booked_date`, `_by`
			) VALUES (
				NULL , 0, 0, NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL ,
				NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL, NULL, $by
			)";	//	9/10/13

		$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		}

	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));			// get the reserved row
	return $row;													// and return it - 11/5/10

	}						// end function get_res_row()

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;
	if ($get_add == 'true')	{
		function updt_ticket($id) {							/* 1/25/09 */
			global $addrs, $_POST, $NOTIFY_TICKET;	//	8/28/13

			$ticket_id = $id;
			$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
			$post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ; //10/1/09
			$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

			$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
			$post_frm_affected 	 		= strip_html($post_frm_affected);
			$_POST['frm_scope']			= strip_html($_POST['frm_scope']);

			if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
					}
				}

			if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_booked_date == 'pm'){
					$post_frm_meridiem_booked_date	= ($post_frm_meridiem_booked_date + 12) % 24;
					}
				}

			if(empty($post_frm_owner)) {$post_frm_owner = $_SESSION['user_id'];}
			$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

			if (intval($_POST['frm_status']) == 3) {		// 1/21/11
				$frm_booked_date = "$_POST[frm_year_booked_date]-$_POST[frm_month_booked_date]-$_POST[frm_day_booked_date] $_POST[frm_hour_booked_date]:$_POST[frm_minute_booked_date]:00$post_frm_meridiem_booked_date";
				} else {
//				$frm_booked_date = "NULL";
				$frm_booked_date = "";		// 6/20/10
				}


			if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
					}
				if (isset($_POST['frm_meridiem_problemend'])) {
					if ($_POST['frm_meridiem_problemend'] == 'pm'){
						$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
						}
					}
				if (isset($_POST['frm_meridiem_booked_date'])) {	//10/1/09
					if ($_POST['frm_meridiem_booked_date'] == 'pm'){
						$_POST['frm_hour_booked_date'] = ($_POST['frm_hour_booked_date'] + 12) % 24;
						}
					}
				}
			$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";

			$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
			if(empty($post_frm_owner)) {$post_frm_owner=0;}
			
			//$inc_num_ary = unserialize (get_variable('_inc_num'));											// 11/13/10
			$temp = get_variable('_inc_num');										// 3/2/11
			$inc_num_ary = (strpos($temp, "{")>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));
			
			// Show serial and format
			switch ((int) $inc_num_ary[0]) {
				case 0:			// none
					$inc_serial="";													// empty
					break;
				case 1: 		// number only
					$inc_serial = (string) $inc_num_ary[3]. $inc_num_ary[2] . " " ;					// number and trailing separator if any
					break;

				case 2:			// labeled
					$inc_serial = $inc_num_ary[1]. $inc_num_ary[2] . (string) $inc_num_ary[3] . " "   ;		// label, separator, number
					break;

				case 3:			// year
					$inc_serial = $inc_num_ary[5]  . $inc_num_ary[2] . (string) $inc_num_ary[3] . " " ;		// year, separator, number
					break;

				default:
					alert("ERROR @ " + "<?php print __LINE__;?>");
				}
			
			$inc_nature = get_type($_POST['frm_in_types_id']);
			
			$prepend_nature = (bool)($inc_num_ary[4]==1)? $inc_nature: "" ;

			switch (get_variable('serial_no_ap')) {								// incident name revise -1/22/09
				case 0:								/*  no serial no. */
					$name_rev = $_POST['frm_scope'];
					break;
				case 1:								/*  prepend  */
					$name_rev =  $ticket_id . "/" . $_POST['frm_scope'];
					break;
				case 2:								/*  append  */
					$name_rev = $_POST['frm_scope'] . " / " .  $ticket_id;
					break;
				default:							/* error????  */
					$name_rev = " error  error  error ";
				}				// end switch
															// 8/23/08, 9/20/08, 8/13/09
			$spacer = ($inc_num_ary[2] != "") ? $inc_num_ary[2] : "";
					
			$scope = $inc_serial . $name_rev . $spacer . $prepend_nature;	//	set final scope to be inserted to ticket_id
			
			// increment the serial if used
			
			$the_year = date("y");
			if ((((int) $inc_num_ary[0]) == 3) && (!($inc_num_ary[5] == $the_year))) {				// year style and change?
				$inc_num_ary[3] = 1;																// roll over and start at 1
				$inc_num_ary[5] = $the_year;
				} else {
				if (((int) $inc_num_ary[0])>0) {		// step to next no. if scheme in use
					$inc_num_ary[3]++;				// do the deed for next use
					}
				}			// end if/else - 10/11/2013

			$out_str = base64_encode(serialize ($inc_num_ary));						// 3/2/11

			$query = "UPDATE`$GLOBALS[mysql_prefix]settings` SET `value` = '$out_str' WHERE `name` = '_inc_num'";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			
			// end of serial increment

			$facility_id = 		empty($_POST['frm_facility_id'])?		0 : trim($_POST['frm_facility_id']);				// 9/28/09
			$rec_facility_id = 	empty($_POST['frm_rec_facility_id'])?	0 : trim($_POST['frm_rec_facility_id']);				// 9/28/09
			$portal_user = 		empty($_POST['frm_portal_user'])?		0:  trim($_POST['frm_portal_user']);				// 9/10/13
			$groups = "," . implode(',', $_POST['frm_group']) . ",";	//	6/10/11
			if ($facility_id > 0) {			// 9/22/09

				$query_g = "SELECT * FROM $GLOBALS[mysql_prefix]facilities WHERE `id`= $facility_id LIMIT 1";
				$result_g = mysql_query($query_g) or do_error($query_g, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$row_g = stripslashes_deep(mysql_fetch_array($result_g));
				$the_lat = $row_g['lat'];								// use facility location
				$the_lng = $row_g['lng'];
			} else {
				$the_lat = quote_smart(trim($_POST['frm_lat']));		// use incident location
				$the_lng = quote_smart(trim($_POST['frm_lng']));
			}

			if ((strlen($the_lat) < 3 ) && (strlen($the_lng) < 3)) {	// 1/29/11
				$the_lat = $the_lng = 0.999999;
				}

			// perform db update	//9/22/09 added facility capability, 10/1/09 added receiving facility
			$by = $_SESSION['user_id'];
			$booked_date = (intval(trim($_POST['frm_do_scheduled'])==1))?  quote_smart($frm_booked_date): "NULL" ;	// 1/2/11, 1/19/10
			// 6/26/10
			$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET
				`portal_user`= " . 	quote_smart(trim($portal_user)) . ",
				`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
				`address_about`= " . 		quote_smart(trim($_POST['frm_address_about'])) .",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
				`to_address`= " . 		quote_smart(trim($_POST['frm_to_address'])) . ",
				`facility`= " . 		quote_smart($facility_id ) . ",
				`rec_facility`= " . 	quote_smart($rec_facility_id) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`scope`= " . 		quote_smart(trim($scope)) . ",
				`owner`= " . 		quote_smart(trim($post_frm_owner)) . ",
				`severity`= " . 	quote_smart(trim($_POST['frm_severity'])) . ",
				`in_types_id`= " . 	quote_smart(trim($_POST['frm_in_types_id'])) . ",
				`status`=" . 		quote_smart(trim($_POST['frm_status'])) . ",
				`problemstart`=".	quote_smart(trim($frm_problemstart)) . ",
				`problemend`=".		$frm_problemend . ",
				`description`= " .	quote_smart(trim($_POST['frm_description'] . "\n")) .",
				`comments`= " . 	quote_smart(trim($_POST['frm_comments'])) .",
				`nine_one_one`= " . quote_smart(trim($_POST['frm_nine_one_one'])) .",
				`booked_date`= " . 	$booked_date .",
				`date`='$now',
				`updated`='$now',
				`_by` = $by
				WHERE ID=" . $id;	//	9/10/13
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

			$tick_stat = $_POST['frm_status'];	// 6/10/11
			$prob_start = quote_smart(trim($frm_problemstart));	// 6/10/11

//	If portal user is set, insert an associated request if one does not already exist for this Ticket	9/10/13

			$where = $_SERVER['REMOTE_ADDR'];		//	9/10/13
			if(($portal_user != NULL) && ($portal_user != 0)) {		//	9/10/13
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `ticket_id` = " . $id;		//	9/10/13
				$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);			//	9/10/13
				if(mysql_affected_rows() == 0) {		//	9/10/13
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
					`org`,
					`contact`,
					`street`,
					`city`,
					`state`,
					`the_name`,
					`phone`,
					`to_address`,
					`orig_facility`,
					`rec_facility`,
					`scope`,
					`description`,
					`comments`,
					`lat`,
					`lng`,
					`request_date`,
					`status`,
					`accepted_date`,
					`declined_date`,
					`resourced_date`,
					`completed_date`,
					`closed`,
					`requester`,
					`ticket_id`,
					`_by`,
					`_on`,
					`_from`
					) VALUES (
					" . 0 . ",
					'" . get_owner($_POST['frm_portal_user']) . "',
					" . quote_smart(trim($_POST['frm_street'])) . ",
					" . quote_smart(trim($_POST['frm_city'])) . ",
					" . quote_smart(trim($_POST['frm_state'])) . ",
					" . quote_smart(trim($_POST['frm_contact'])) . ",
					" . quote_smart(trim($_POST['frm_phone'])) . ",
					" . quote_smart(trim($_POST['frm_to_address'])) . ",
					" . quote_smart($facility_id ) . ",
					" . quote_smart($rec_facility_id ) . ",
					" . quote_smart(trim($name_rev)) . ",
					" . quote_smart(trim($_POST['frm_description'] + '\n')) . ",
					" . quote_smart(trim($_POST['frm_comments'])) . ",
					" . $the_lat . ",
					" . $the_lng . ",
					" . quote_smart(trim($frm_problemstart)) . ",
					'Accepted',
					'" . $now . "',
					NULL,
					NULL,
					NULL,
					NULL,
					" . $portal_user . ",
					" . $id . ",
					" . $_SESSION['user_id'] . ",
					'" . $now . "',
					'" . $where . "')";
					$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);		//	9/10/13
				}
			}

//	end of insert request associated with Ticket

//	9/10/13 File Upload support
			$print = "";
			if ((isset($_FILES['frm_file'])) && ($_FILES['frm_file']['name'] != "")){
				$nogoodFile = false;
				$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py");
				foreach ($blacklist as $file) {
					if(preg_match("/$file\$/i", $_FILES['frm_file']['name'])) {
						$nogoodFile = true;
						}
					}
				if(!$nogoodFile) {
					$exists = false;
					$existing_file = "";
					$upload_directory = "./files/";
					if (!(file_exists($upload_directory))) {
						mkdir ($upload_directory, 0770);
						}
					chmod($upload_directory, 0770);
					$filename = rand(1,999999);
					$realfilename = $_FILES["frm_file"]["name"];
					$file = $upload_directory . $filename;

//	Does the file already exist in the files table

					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `orig_filename` = '" . $realfilename . "'";
					$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
					if(mysql_affected_rows() == 0) {	//	file doesn't exist already
						if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
							if (strlen(filesize($file)) < 20000000) {
								$print .= "";
								} else {
								$print .= "Attached file is too large!";
								}
							} else {
							$print .= "Error uploading file";
							}
						} else {
						$row = stripslashes_deep(mysql_fetch_assoc($result));
						$exists = true;
						$existing_file = $row['filename'];	//	get existing file name
						}

					$from = $_SERVER['REMOTE_ADDR'];
					$filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
					$query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files` (
							`title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
						) VALUES (
							'" . $_POST['frm_file_title'] . "', '" . $filename . "', '" . $realfilename . "', " . $id . ", 0,
							0, 0, '" . $_FILES['frm_file']['type'] . "', $by, '" . $now . "', '" . $from . "'
						)";
					$result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
					if($result_insert) {	//	is the database insert successful
						$dbUpdated = true;
						} else {	//	problem with the database insert
						$dbUpdated = false;
						}
					}
				} else {	// Problem with the file upload
				$fileUploaded = false;
				}

//	End of file upload
			foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
				if(test_allocates($id, $grp_val, 1))	{
					$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
							($grp_val, 1, '$now', 1, $id, 'Allocated to Group' , $by)";
					$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					}
				}
				
// If Major Incident Set
			if(array_key_exists('frm_maj_inc', $_POST) && $_POST['frm_maj_inc'] != 0) {
				// Delete existing entries to avoid dupes
				$query = "DELETE FROM `$GLOBALS[mysql_prefix]mi_x` WHERE `ticket_id` = '$id'"; 
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
				// Insert new entry
				$maj_inc = intval($_POST['frm_maj_inc']);
				$query  = "INSERT INTO `$GLOBALS[mysql_prefix]mi_x` (
					`mi_id`, 
					`ticket_id`)
					VALUES (
					" . $maj_inc . ",
					" . $id . ")";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
				}

			do_log($GLOBALS['LOG_INCIDENT_OPEN'], $id);

			if (intval($facility_id) > 0) {	//9/22/09, 10/1/09, 3/24/10
				do_log($GLOBALS['LOG_FACILITY_INCIDENT_OPEN'], $id, '' ,0 ,$facility_id);	// - 7/11/10
				}
			if (intval($rec_facility_id) >  0) {
				do_log($GLOBALS['LOG_CALL_REC_FAC_SET'], $id, 0 ,0 ,0 ,$rec_facility_id);	// 6/20/10 - 7/11/10
				}
			$ret_arr = array();
			$ret_arr[0] = $name_rev;
			$ret_arr[1] = $scope;
			return $ret_arr;
			}				// end function updt ticket()

		$ticket_name_ary = updt_ticket(trim($_POST['ticket_id']));				// 1/25/09
		$ticket_name = $ticket_name_ary[0];
		$ticket_scope = $ticket_name_ary[1];
		$addrs = notify_user($_POST['ticket_id'],$GLOBALS['NOTIFY_TICKET_OPEN']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?	8/28/13
			$theTo = implode("|", array_unique($addrs));
			$theText = "New " . get_text("Incident");
			mail_it ($theTo, "", $theText, $_POST['ticket_id'], 1 );
			}				// end if/else ($addrs)

?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<HEAD><TITLE>Tickets - Add Module</TITLE>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
		<META HTTP-EQUIV="Expires" CONTENT="0" />
		<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
		<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
		<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
		<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
		<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
		<!--[if lte IE 8]>
			 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
		<![endif]-->
		<link rel="stylesheet" href="./js/Control.Geocoder.css" />
		<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
		<SCRIPT SRC="./js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 9/14/12 -->
		<SCRIPT SRC="./js/jscolor/jscolor.js"></SCRIPT>	<!-- 9/14/12 -->
<?php


		if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) {
			require_once('./incs/socket2me.inc.php');		// 5/22/2013
			}
?>
		</HEAD>
<?php
		if($in_win) {
			if((intval(get_variable('auto_route'))==1) && ($in_win == 0)) {
				$onloadStr = "setTimeout(function(){ document.to_routes.submit();}, 8000);";
				} else if((intval(get_variable('auto_route'))==0) && ($in_win == 0)) {
//				$onloadStr = "setTimeout(function(){ document.to_main.submit();}, 8000);";
				$onloadStr = "";
				}
			} else {
			$onloadStr = "";
			}
?>
		<BODY onLoad = "<?php print $onloadStr;?>">
<?php

		$now = time() - (intval(get_variable('delta_mins')*60));		// 6/20/10
		print "<BR /><BR /><BR /><CENTER><FONT CLASS='header'>Ticket: '{$ticket_scope} ' ({$ticket_name}) Added by '{$_SESSION['user_id']}' at " . date(get_variable("date_format"),$now) . "</FONT></CENTER><BR /><BR />";
		if($in_win == 1 || get_variable("calltaker_mode") == 1) {
?>
			<CENTER><SPAN id='cont_but' class='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>FINISH</SPAN></CENTER>
<?php
			} else {
?>
			<FORM NAME='to_main' METHOD='post' ACTION='main.php'></FORM>
			<CENTER>
			<SPAN id='sub_but' class='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.to_main.submit();'>Main</SPAN><BR />			
			<BR />
			<FORM NAME='to_routes' METHOD='get' ACTION='<?php print $_SESSION['routesfile'];?>'>
			<INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print $_POST['ticket_id'];?>' />
			</FORM>
			<SPAN id='sub_but' class='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.to_routes.submit();'>Routes</SPAN>
			</CENTER>
			
<?php
			}
		exit();
		}				// end if ($_GET['add'] ...
//					==============================================
	else {
		if (is_guest() && !get_variable('guest_add_ticket')) {		// 6/25/10
			print '<FONT CLASS="warn">Guest/member users may not add tickets on this system.  Contact administrator for further information.</FONT>';
			exit();
			}

	$res_row = get_res_row();				// 11/5/10

	$ticket_id = $res_row['id'];
//	$hints = get_hints("a");

	$nature = get_text("Nature");				// 12/1/10	{$nature}
	$disposition = get_text("Disposition");		// 	{$disposition}
	$patient = get_text("Patient");				// 	{$patient}
	$incident = get_text("Incident");			// 	{$incident}
	$incidents = get_text("Incidents");			// 	{$incidents}


	$titles = array();				// 2/1/11

	$query = "SELECT `tag`, `hint` FROM `$GLOBALS[mysql_prefix]hints`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row =stripslashes_deep(mysql_fetch_assoc($result))) {
		$titles[trim($row['tag'])] = trim($row['hint']);
		}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Add Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type" CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="./js/Control.Geocoder.css" />
<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT SRC="./js/suggest.js" TYPE="application/x-javascript"></SCRIPT>			<!-- 2/20/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script src="./js/leaflet/KML.js"></script>
<script src="./js/leaflet-openweathermap.js"></script>
<script src="./js/esri-leaflet.js"></script>
<script src="./js/osopenspace.js"></script>
<script src="./js/Control.Geocoder.js"></script>
<?php
	if ($_SESSION['internet']) {
		$api_key = get_variable('gmaps_api_key');
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : false;
		if($key_str) {
			if($https) {
?>
				<script src="https://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php
				} else {
?>
				<script src="http://maps.google.com/maps/api/js?<?php print $key_str;?>"></script>
				<script src="./js/Google.js"></script>
<?php				
				}
			}
		}
?>
<SCRIPT SRC="./js/usng.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT SRC='./js/jscoord.js' TYPE="application/x-javascript"></SCRIPT>			<!-- coordinate conversion 12/10/10 -->
<SCRIPT SRC="./js/lat_lng.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/geotools2.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/osgb.js" TYPE="application/x-javascript"></SCRIPT>	<!-- 11/8/11 -->
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<SCRIPT>
	window.onresize=function(){set_size();}
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
	var protocols = <?php echo json_encode($protocols); ?>;
	var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
	var states_arr = <?php echo json_encode($states); ?>;
	var colors = new Array ('odd', 'even');
	var viewportwidth;
	var viewportheight;
	var fieldwidth;
	var smallfieldwidth;
	var mapwidth;
	var mapheight;
	var outerwidth;
	var outerheight;
	var colwidth;
	var colheight;
	var leftcolwidth;
	var winleftcolwidth;
	var rightcolwidth
	var isinwin = <?php print $in_win;?>;
	
	function do_timedate() {
		var today=new Date();
		var day= today.getDate();
		var month = today.getMonth() + 1;
		var year = today.getFullYear();
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		var yearselect = $('year_problemstart');
		var wizyearselect = $('wiz_frm_year_problemstart');
		var monthselect = $('month_problemstart');
		var wizmonthselect = $('wiz_frm_month_problemstart');
		var dayselect = $('day_problemstart');
		var wizdayselect = $('wiz_frm_day_problemstart');
		var hourinput = $('hour_problemstart');
		var wizhourinput = $('wiz_frm_hour_problemstart');
		var mininput = $('minute_problemstart');
		var wizmininput = $('wiz_frm_minute_problemstart');
		//	for add form
		if(yearselect) {
			for(var f = 0; f < yearselect.options.length; f++) {
				if(yearselect.options[f].value == year) {
					yearselect.options[f].selected = true;
					}
				}
			}
		if(monthselect) {
			for(var f = 0; f < monthselect.options.length; f++) {
				if(monthselect.options[f].value == month) {
					monthselect.options[f].selected = true;
					}
				}
			}
		if(dayselect) {
			for(var f = 0; f < dayselect.options.length; f++) {
				if(dayselect.options[f].value == day) {
					dayselect.options[f].selected = true;
					}
				}
			}
		hourinput.value = hours;
		mininput.value = mins;
		//	For Wizard
		if(wizyearselect) {
			for(var f = 0; f < wizyearselect.options.length; f++) {
				if(wizyearselect.options[f].value == year) {
					wizyearselect.options[f].selected = true;
					}
				}
			}
		if(wizmonthselect) {
			for(var f = 0; f < wizmonthselect.options.length; f++) {
				if(wizmonthselect.options[f].value == month) {
					wizmonthselect.options[f].selected = true;
					}
				}
			}
		if(wizdayselect) {
			for(var f = 0; f < wizdayselect.options.length; f++) {
				if(wizdayselect.options[f].value == day) {
					wizdayselect.options[f].selected = true;
					}
				}
			}
		wizhourinput.value = hours;
		wizmininput.value = mins;		
		}
		
	var fields = ["about",
				"street",
				"scope",
				"toaddress",
				"loc_warnings",
				"description",
				"comments",
				"contact",
				"filename",
				"sel_bldg",
				"911",
				"frm_phone",
				"proto_cell"];
	var medfields = ["facy",
					"recfacy",
					"my_txt",
					"sel_in_types_id",
					"severity",
					"signals",
					"signals2",
					"portal_user",
					"statusSelect",
					"sel_status",
					"sel_maj_inc"];
	var smallfields = ["show_lat", "show_lng", "griddisp"];
	
	function set_size() {
		if (typeof window.innerWidth != 'undefined') {
			viewportwidth = window.innerWidth,
			viewportheight = window.innerHeight
			} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			viewportwidth = document.documentElement.clientWidth,
			viewportheight = document.documentElement.clientHeight
			} else {
			viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
			viewportheight = document.getElementsByTagName('body')[0].clientHeight
			}
		if(window.isinwin) {set_fontsizes(viewportwidth, "popup");} else {set_fontsizes(viewportwidth, "fullscreen");}
		if(window.isinwin) {mapWidth = viewportwidth * .95;} else {mapWidth = viewportwidth * .40;}
		mapHeight = viewportheight * .55;
		if(window.isinwin) {outerwidth = viewportwidth * .95;} else {outerwidth = viewportwidth * .99;}
		outerheight = viewportheight * .95;
		if(window.isinwin) {colwidth = outerwidth;} else {colwidth = outerwidth * .40;}
		colheight = outerheight * .95;
		leftcolwidth = viewportwidth * .45;
		winleftcolwidth = viewportwidth * .90;
		rightcolwidth = viewportwidth * .35;
		fieldwidth = leftcolwidth * .45;
		medfieldwidth = colwidth * .20;	
		smallfieldwidth = colwidth * .10;
		if($('outer')) {$('outer').style.width = outerwidth + "px";}
		if($('outer')) {$('outer').style.height = outerheight + "px";}
		if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
		if($('leftcol_inwin')) {$('leftcol_inwin').style.width = winleftcolwidth + "px";}
		if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
		if($('rightcol')) {$('rightcol').style.width = colwidth + "px";}
		if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
		if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
		if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
		if($('map_caption')) {$('map_caption').style.width = mapWidth + "px";}
		for (var i = 0; i < fields.length; i++) {
			if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
			} 
		for (var i = 0; i < medfields.length; i++) {
			if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
			}
		for (var i = 0; i < smallfields.length; i++) {
			if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
			}
		map.invalidateSize();
		}
	
	function get_new_colors() {				// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}
<?php
	if(!$in_win) {
?>
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
<?php
		}
?>
	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08
	var starting = false;

	starting=false;						// 12/16/09
	function do_hist_win() {
		if(starting) {return;}
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
<?php
	if (get_variable("locale") ==0) {				// USA only
?>
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
<?php
		}		// end locale check
?>
		starting=true;
		var url = "call_hist.php?frm_phone=" + goodno;
		newwindow_c_h=window.open(url, "Call_hist",  "titlebar, resizable=1, scrollbars, height=640,width=760,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
		if (isNullOrEmpty(newwindow_c_h)) {
			starting = false;
			alert ("Call history operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_c_h.focus();
		starting = false;
		}		// function do hist_win()

	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)

	function getRes() {
		return window.screen.width + ' x ' + window.screen.height;
		}

	var request;
	var tab1contents				// info window contents - first/only tab
	var thePoint;
	var baseIcon;
	var cross;

	function do_usng(theForm) {								// 8/23/08, 12/5/10
		theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);	// US NG
		}

	function do_utm (theForm) {
		var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
		var utm_out = ll_in.toUTMRef().toString();
		temp_ary = utm_out.split(" ");
		theForm.frm_ngs.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
		}

	function do_osgb (theForm) {
		theForm.frm_ngs.value = LLtoOSGB(theForm.frm_lat.value, theForm.frm_lng.value);
		if(document.wiz_add) {document.wiz_add.wiz_frm_ngs.value = LLtoOSGB(theForm.frm_lat.value, theForm.frm_lng.value);}
		}

	function do_cancel(the_form) {			// 6/9/11
		try {
			window.opener.frames["upper"].new_signal_off();
			}
			catch(e) {
			}
		try {
			parent.frames["upper"].new_signal_off();
			}
			catch(e) {
			}
		var params = "ticket_id=" + the_form.ticket_id.value;
		sendRequest (the_form, 'cancel_add.php',handleResult_can, params);	// (my_form, url,callback,postData))   10/15/08
		}			// end function do cancel()

	function handleResult_can(req) {				// the called-back function
<?php
		if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
<?php
	if ($in_win) {
?>
	window.close();
<?php
		}
	else {
?>
		parent.frames['upper'].light_butt('main');			// top frame button
		history.back();
<?php
		} 		// end else
?>
		}			// end function handleResult_can(req)

// *********************************************************************

// "Juan Wzzzzz;(123) 456-9876;1689 Abcd St;Abcdefghi;MD;16701;99.013297;-88.544775;"
//  1           2              3            4         5  6     7         8

	 function isOKCoord (theVal) {									// 10/3/2015
	 	return (
	 		( ! ( isNaN ( parseFloat ( theVal ) ) ) ) &&
	 		( theVal != "<?php echo $GLOBALS['NM_LAT_VAL'];?>" )
	 		);
	 	}		// end function

	function handleResult(req) {									// the called-back phone lookup function
//		alert(754);
		var result=req.responseText.split(";");						// parse semic-separated return string
		$('repeats').innerHTML = "(" + result[0].trim() + ")";		// prior calls this phone no. - 9/29/09
		if (!(result.length>2)) {
<?php
			if (get_variable("locale") ==0) {				// USA only		// 10/2/09
?>
				alert("lookup failed");
<?php
				}
?>
 			} else {				// 10/2/2015
			document.add.frm_contact.value=result[1].trim();	// name
			document.add.frm_phone.value=result[2].trim();		// phone
			document.add.frm_street.value=result[3].trim();		// street
			document.add.frm_city.value=result[4].trim();		// city
			document.add.frm_state.value=result[5].trim();		// state
//			document.add.frm_zip.value=result[6].trim();		// frm_zip - unused
			if (result[9].length > 0) {								// misc constituents information - 3/13/10
				$('td_misc').innerHTML = '&nbsp;' + result[9].trim();
				$('tr_misc').style.display='';
				pt_to_map (document.add, result[7].trim(), result[8].trim());				// 1/19/09
				}
//			else if ((result[3].length>0) && (result[4].length>0) && (result[5].length>0)) {		// 4/27/10
			else if ( isOKCoord ( result[7].trim() ) && ( isOKCoord ( result[8].trim() ) ) ) {			// 10/3/2015
				pt_to_map (document.add, result[7].trim(), result[8].trim());	// (my_form, lat, lng) - 10/2/2015

				}
			}		// end else ...
		}		// end function handleResult()

	function phone_lkup(){
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
//		alert(goodno);
<?php
	if (get_variable("locale") ==0) {				// USA only
?>
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
<?php
		}		// end locale check
?>
		var params = "phone=" + URLEncode(goodno);
//		alert(800);
//		sendRequest (document.add, 'wp_lkup.php',handleResult, params);		//1/17/09 - (url,callback,postData)
		sendRequest ( 'wp_lkup.php', handleResult, params);					// 10/1/2015 - (url,callback,postData)
		}

// *********************************************************************
	function contains(array, item) {
		for (var i = 0, I = array.length; i < I; ++i) {
			if (array[i] == item) return true;
			}
		return false;
		}

	function do_lat (lat) {							// 9/14/08
		document.forms[0].frm_lat.value=lat.toFixed(6);			// 9/9/08
		document.forms[0].show_lat.disabled=false;
		document.forms[0].show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
		document.forms[0].show_lat.disabled=true;
		if(document.wiz_add) {
			document.wiz_add.wiz_show_lat.disabled=false;
			document.wiz_add.wiz_show_lat.value=do_lat_fmt(document.forms[0].frm_lat.value);
			document.wiz_add.wiz_show_lat.disabled=true;
			}
		}
	function do_lng (lng) {
		document.forms[0].frm_lng.value=lng.toFixed(6);			// 9/9/08
		document.forms[0].show_lng.disabled=false;
		document.forms[0].show_lng.value=do_lng_fmt(document.forms[0].frm_lng.value);
		document.forms[0].show_lng.disabled=true;
		if(document.wiz_add) {
			document.wiz_add.wiz_show_lng.disabled=false;
			document.wiz_add.wiz_show_lng.value=do_lng_fmt(document.forms[0].frm_lng.value);
			document.wiz_add.wiz_show_lng.disabled=true;
			}
		}

	function do_ngs() {											// LL to USNG
		var loc = <?php print get_variable('locale');?>;
		document.forms[0].frm_ngs.disabled=false;
		if(loc == 0) {
			document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);
			if(document.wiz_add) {document.wiz_add.wiz_frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);}
			}
		if(loc == 1) {
			if(document.wiz_add) {document.wiz_add.wiz_frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);}
			}
		if(loc == 2) {
			if(document.wiz_add) {document.wiz_add.wiz_frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);}
			}
		document.forms[0].frm_ngs.disabled=true;
		if(document.wiz_add) {document.wiz_add.wiz_frm_ngs.disabled=true;}
		}

	function find_warnings(tick_lat, tick_lng) {	//	9/10/13
		randomnumber=Math.floor(Math.random()*99999999);
		var theurl ="./ajax/loc_warn_list.php?version=" + randomnumber + "&lat=" + tick_lat + "&lng=" + tick_lng;
		theRequest(theurl, loc_w, "");
		function loc_w(req) {
			var the_warnings=JSON.decode(req.responseText);
			var the_count = the_warnings[0];
			if(the_count != 0) {
				alert("There is at least one location nearby with a warning registered for it\r\nPlease view the Ticket to see the warnings");
				$('loc_warnings').innerHTML = the_warnings[1];
				$('loc_warnings').style.display = 'block';
				}
			}
		}

	var start_wl = false;
	function wl_win(the_Id) {				// 9/10/13
		if(start_wl) {return;}				// dbl-click proof
		start_wl = true;
		var url = "warnloc_popup.php?id=" + the_Id;
		newwindow_wl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=600,width=750,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_wl)) {
			alert ("Locations warning operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_wl.focus();
		start_wl = false;
		}		// end function sv win()

	function theRequest(url,callback,postData) {
		var req = docreateXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}

	var doXMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];

	function docreateXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try { xmlhttp = doXMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}

	var tbd_str = "TBD";									// 1/11/09
	var user_inc_name = false;							// 4/21/10

	function do_inc_protocol(indx) {								// 10/4/08, 7/7/09
		if (window.protocols && window.protocols[indx]) {
			if($('proto_cell')) {$('proto_cell').innerHTML = protocols[indx];}
			if($('wiz_proto_cell')) {$('wiz_proto_cell').innerHTML = protocols[indx];}
			} else {
			if($('proto_cell')) {$('proto_cell').innerHTML = "";}
			if($('wiz_proto_cell')) {$('wiz_proto_cell').innerHTML = "";}
			}
		}			// end function do_inc_protocol()

	function datechk_s(theForm) {		// pblm start vs now
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
		var now = new Date();
		return (start.valueOf() <= now.valueOf());
		}
	function datechk_e(theForm) {		// pblm end vs now
		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value, theForm.frm_minute_problemend.value, 0,0);
		var now = new Date();
		return (end.valueOf() <= now.valueOf());
		}
	function datechk_r(theForm) {		// pblm start vs end
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);

		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value,theForm.frm_minute_problemend.value, 0,0);
		return (start.valueOf() <= end.valueOf());
		}

	function validate(theForm) {
		do_unlock_ps(theForm);								// 8/11/08
		var errmsg="";
		if ((theForm.frm_street.value == "") && (theForm.frm_city.value == "") && (theForm.frm_state.value == ""))	{errmsg+= "\tAddress is required\n";}
		if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_CLOSED'];?>) && (!theForm.re_but.checked)) {errmsg+= "\tRun end-date is required for Status=Closed\n";}
		if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_OPEN'];?>) && (theForm.re_but.checked))	{errmsg+= "\tRun end-date not allowed for Status=Open\n";}	// 9/30/10
<?php
		if (!(intval(get_variable('quick')==1))) {
?>
			if (theForm.frm_in_types_id.value == 0)		{errmsg+= "\tNature of Incident is required\n";}			// 1/11/09
<?php
			}
?>
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")			{errmsg+= "\tIncident name is required\n";}
		if (theForm.frm_description.value == "")	{errmsg+= "\t<?php print get_text('synopsis');?> is required\n";}
		if(allow_nogeo == "0") {
<?php
			if($gmaps || $good_internet) {
?>
				if ((theForm.frm_lat.value == 0) || (theForm.frm_lng.value == 0))		{errmsg+= "\tMap position is required\n";}
<?php
				}
?>
			} else {
			if ((theForm.frm_lat.value == 0) || (theForm.frm_lng.value == 0))		{
				theForm.frm_lat.value = def_lat;
				theForm.frm_lng.value = def_lng;
				}
			}
		if (theForm.frm_status.value==<?php print $GLOBALS['STATUS_SCHEDULED'];?>) {		//10/1/09
			if (theForm.frm_year_booked_date.value == "NULL") 		{errmsg+= "\tScheduled date time error - Hours\n";}
			if (theForm.frm_minute_booked_date.value == "NULL") 	{errmsg+= "\tScheduled date time error - Minutes\n";}
			}
		if (!chkval(theForm.frm_hour_problemstart.value, 0,23)) 		{errmsg+= "\tRun start time error - Hours\n";}
		if (!chkval(theForm.frm_minute_problemstart.value, 0,59)) 		{errmsg+= "\tRun start time error - Minutes\n";}
		if (!datechk_s(theForm))										{errmsg+= "\tRun start time error - future date\n" ;}

		if (theForm.re_but.checked) {				// run end?
			do_unlock_pe(theForm);								// problemend values
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_r(theForm)){errmsg+= "\tRun start time error - future\n" ;}

			if (!chkval(theForm.frm_hour_problemend.value, 0,23)) 		{errmsg+= "\tRun end time error - Hours\n";}
			if (!chkval(theForm.frm_minute_problemend.value, 0,59)) 	{errmsg+= "\tRun end time error - Minutes\n";}
			}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			} else {
			do_unlock_ps(theForm);								// 8/11/08
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
			var theAddr = theForm.frm_street.value + " " + theForm.frm_city.value + " " + theForm.frm_state.value;
<?php
			if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) {
?>																						/*	5/22/2013 */
				var theMessage = "New  <?php print get_text('Incident');?> (" + theForm.frm_scope.value + ") " + theAddr  + " by <?php echo $_SESSION['user'];?>";
				broadcast(theMessage, 1);
<?php
				}			// end if (broadcast)
?>
			find_warnings(theForm.frm_lat.value, theForm.frm_lng.value);	//	9/10/13
			document.add.submit();			
			return true;	//	11/18/13
			}
		}				// end function validate(theForm)

	function do_fac_to_loc(text, index){			// 9/22/09
		var curr_lat = fac_lat[index];
		var curr_lng = fac_lng[index];
		do_lat(curr_lat);
		do_lng(curr_lng);
		document.add.frm_lat.disabled=true;
		document.add.frm_lng.disabled=true;
		}					// end function do_fac_to_loc

	function capWords(str){
		var words = str.split(" ");
		for (var i=0 ; i < words.length ; i++){
			var testwd = words[i];
			var firLet = testwd.substr(0,1);
			var rest = testwd.substr(1, testwd.length -1)
			words[i] = firLet.toUpperCase() + rest
	  	 	}
		return( words.join(" "));
		}

	function do_end(theForm) {			// enable run-end date/time inputs
		elem = $("runend1");
		elem.style.visibility = "visible";
<?php
		$show_ampm = (!get_variable('military_time')==1);
		if ($show_ampm){	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
			print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
			}
?>
		do_unlock_pe(theForm);								// problemend values
		}

	function do_reset(theForm) {				// disable run-end date/time inputs - 4/27/2014
//		clearmap();
		do_lock_ps(theForm);				// hskp problem start date
		do_lock_pe(theForm);				// hskp problem end date
		$("runend1").visibility = "hidden";
		$("lock_p").style.visibility = "visible";
		$("runend1").style.visibility = "hidden";
		theForm.frm_lat.value=theForm.frm_lng.value="";
		theForm.frm_do_scheduled.value=0;				// 1/1/11

		try {document.add.frm_ngs.disabled=true; }		// 4/30/09
			catch (err) {}
		try {$("USNG").style.textDecoration = '"none';}
			catch (err) {}
		$('booking1').style.visibility = 'hidden';
		user_inc_name = false;							// no incident name input 4/21/10
		$('proto_cell').innerHTML = "";					// 8/7/10
		$('bldg_info').innerHTML = "";					// 4/27/2014
		theForm.reset();
		}		// end function reset()

	function do_problemstart(theForm, theBool) {							// 8/10/08
		theForm.frm_year_problemstart.disabled = theBool;
		theForm.frm_month_problemstart.disabled = theBool;
		theForm.frm_day_problemstart.disabled = theBool;
		theForm.frm_hour_problemstart.disabled = theBool;
		theForm.frm_minute_problemstart.disabled = theBool;
		if (theForm.frm_meridiem_problemstart) {theForm.frm_meridiem_problemstart.disabled = theBool;}
		}

	function do_problemend(theForm, theBool) {								// 8/10/08
		theForm.frm_year_problemend.disabled = theBool;
		theForm.frm_month_problemend.disabled = theBool;
		theForm.frm_day_problemend.disabled = theBool;
		theForm.frm_hour_problemend.disabled = theBool;
		theForm.frm_minute_problemend.disabled = theBool;
		if (theForm.frm_meridiem_problemend) {theForm.frm_meridiem_problemend.disabled = theBool;}
		}

	function do_booking(theForm) {			// 10/1/09 enable booked date entry
		if(!document.wiz_add) {
			theForm.frm_do_scheduled.value=1;	// 1/1/11
			for (i=0;i<theForm.frm_status.options.length; i++){
				if (theForm.frm_status.options[i].value == <?php print $GLOBALS['STATUS_SCHEDULED'];?>) {
					theForm.frm_status.options[i].selected = true;
					break;
					}
				}
			elem = $("booking1");
			elem.style.visibility = "visible";
<?php
			$show_ampm = (!get_variable('military_time')==1);
			if ($show_ampm){	//put am/pm optionlist if not military time
				print "\tdocument.add.frm_meridiem_booked_date.disabled = false;\n";
				}
?>
			do_booked_date(theForm, false);
			} else {
			document.add.frm_do_scheduled.value=1;	// 1/1/11
			theForm.wiz_frm_do_scheduled.value=1;	// 1/1/11
			if(document.wiz_frm_status) {
				for (i=0;i<theForm.wiz_frm_status.options.length; i++){
					if (theForm.wiz_frm_status.options[i].value == <?php print $GLOBALS['STATUS_SCHEDULED'];?>) {
						theForm.wiz_frm_status.options[i].selected = true;
						break;
						}
					}
				}
			var otherform = document.add;
			for (i=0;i<otherform.frm_status.options.length; i++){
				if (otherform.frm_status.options[i].value == <?php print $GLOBALS['STATUS_SCHEDULED'];?>) {
					otherform.frm_status.options[i].selected = true;
					break;
					}
				}
			elem = $("booking1");
			elem.style.visibility = "visible";
			elem2 = $("wiz_booking1");
			elem2.style.visibility = "visible";
<?php
			$show_ampm = (!get_variable('military_time')==1);
			if ($show_ampm){	//put am/pm optionlist if not military time
				print "\tdocument.add.frm_meridiem_booked_date.disabled = false;\n";
				print "\tdocument.wiz_add.frm_meridiem_booked_date.disabled = false;\n";
				}
?>
			do_booked_date(theForm, false);
			}
		}

	function do_booked_date(theForm, theBool) {							// 10/1/09 Booked Date processing
		if(!document.wiz_add) {
			theForm.frm_year_booked_date.disabled = theBool;
			theForm.frm_month_booked_date.disabled = theBool;
			theForm.frm_day_booked_date.disabled = theBool;
			theForm.frm_hour_booked_date.disabled = theBool;
			theForm.frm_minute_booked_date.disabled = theBool;
			if (theForm.frm_meridiem_booked_date) {theForm.frm_meridiem_booked_date.disabled = theBool;}
			} else {
			document.add.frm_year_booked_date.disabled = theBool;
			document.add.frm_month_booked_date.disabled = theBool;
			document.add.frm_day_booked_date.disabled = theBool;
			document.add.frm_hour_booked_date.disabled = theBool;
			document.add.frm_minute_booked_date.disabled = theBool;
			if (document.add.frm_meridiem_booked_date) {document.add.frm_meridiem_booked_date.disabled = theBool;}
			document.wiz_add.wiz_frm_year_booked_date.disabled = theBool;
			document.wiz_add.wiz_frm_month_booked_date.disabled = theBool;
			document.wiz_add.wiz_frm_day_booked_date.disabled = theBool;
			document.wiz_add.wiz_frm_hour_booked_date.disabled = theBool;
			document.wiz_add.wiz_frm_minute_booked_date.disabled = theBool;
			if (document.wiz_add.wiz_frm_meridiem_booked_date) {document.wiz_add.wiz_frm_meridiem_booked_date.disabled = theBool;}			
			}
		}

	function do_unlock_ps(theForm) {
		do_problemstart(theForm, false)
		if($('lock_s')) {$("lock_s").style.visibility = "hidden";}
		}

	function do_unlock_bd(theForm) {
		do_booked_date(theForm, false)
		if($('lock_b')) {$("lock_b").style.visibility = "hidden";}
		}

	function do_lock_ps(theForm) {
		do_problemstart(theForm, true)
		if($('lock_s')) {$("lock_s").style.visibility = "visible";}
		}

	function do_unlock_pe(theForm) {
		do_problemend(theForm, false)
		if($('lock_e')) {$("lock_e").style.visibility = "hidden";}
		}

	function do_lock_pe(theForm) {
		do_problemend(theForm, true)
		if($('lock_e')) {$("lock_e").style.visibility = "visible";}
		}

	function do_unlock_pos(theForm) {
		document.add.frm_ngs.disabled=false;
		if($('lock_p')) {$("lock_p").style.visibility = "hidden";}
		try {$("grid_link").style.textDecoration = "underline";	}						// 4/30/09
		catch (e) { }
		}

	function checkAll() {	//	9/10/13
		var theField = document.add.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = true ;
			}
		}

	function uncheckAll() {	//	9/10/13
		var theField = document.add.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = false ;
			}
		}

	var fac_lat = [];
	var fac_lng = [];

	function ReadOnlyCheckBox() {
		alert("Sorry; Not permitted");
		return false;
		}

	function getLocation() {
		if(map) {
			map.locate({setView: true, watch: false, maxZoom: 16, enableHighAccuracy:true})	//	get location, don't auto watch, set max zoom, zoom and center on location, enable GPS
				.on('locationfound', function(e){
					if(marker) {map.removeLayer(marker);}
					if(myMarker) {map.removeLayer(myMarker);}
					var iconurl = "./our_icons/yellow.png";
					icon = new baseIcon({iconUrl: iconurl});
					myMarker = new L.marker(e.latlng, {icon:icon, draggable:'true'});
					myMarker.addTo(map);
					var control = new L.Control.Geocoder();
					getTheAddress(e.latlng);
				})
			   .on('locationerror', function(e){
					alert("Location access denied.");
				});
			}
		}
 <?php
	$al_groups = $_SESSION['user_groups'];

	if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
		$curr_viewed= explode(",",$_SESSION['viewed_groups']);
		}

	if(!isset($curr_viewed)) {
		if(count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($al_groups as $grp) {	//	6/10/11
				$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
			}
		} else {
		if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
			$where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
			} else {
			$x=0;	//	6/10/11
			$where2 = "WHERE (";	//	6/10/11
			foreach($curr_viewed as $grp) {	//	6/10/11
				$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
				$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
				$where2 .= $where3;
				$x++;
				}
			$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
			}
		}

		// Pulldown menu for use of Incident set at Facility 9/22/09, 3/18/10 - 2/12/11
	$query_fc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
		$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
	$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$pulldown = '<option value=0 selected>Incident at Facility</option>\n';	// 3/18/10
		while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
			$pulldown .= "<option value=\"{$row_fc['fac_id']}\">" . shorten($row_fc['name'], 30) . "</option>\n";
			if($row_fc['lat'] == "" || $row_fc['lng'] == "") {
				print "\tfac_lat[" . $row_fc['fac_id'] . "] = " . $GLOBALS['NM_LAT_VAL'] . " ;\n";
				print "\tfac_lng[" . $row_fc['fac_id'] . "] = " . $GLOBALS['NM_LAT_VAL'] . " ;\n";
				} else {
				print "\tfac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;\n";
				print "\tfac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;\n";
				}
			}

		// Pulldown menu for use of receiving Facility 10/6/09, 3/18/10
	$query_rfc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
		$where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
	$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$pulldown2 = '<option value = 0 selected>Receiving facility</option>\n'; 	// 3/18/10
		while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
			$pulldown2 .= "<option value=\"{$row_rfc['fac_id']}\">" . shorten($row_rfc['name'], 30) . "</option>\n";
			if($row_rfc['lat'] == "" || $row_rfc['lng'] == "") {
				print "\tfac_lat[" . $row_rfc['fac_id'] . "] = " . $GLOBALS['NM_LAT_VAL'] . " ;\n";
				print "\tfac_lng[" . $row_rfc['fac_id'] . "] = " . $GLOBALS['NM_LAT_VAL'] . " ;\n";
				} else {
				print "\tfac_lat[" . $row_rfc['fac_id'] . "] = " . $row_rfc['lat'] . " ;\n";
				print "\tfac_lng[" . $row_rfc['fac_id'] . "] = " . $row_rfc['lng'] . " ;\n";
				}
			}

		// Pulldown menu for portal user association 9/10/13
	$query_pu = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = " . $GLOBALS['LEVEL_SERVICE_USER'] . " ORDER BY `name_l` ASC";
	$result_pu = mysql_query($query_pu) or do_error($query_pu, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	if(mysql_affected_rows() > 0) {
		$has_portal = 1;
		$portal_user_control = "<SELECT ID='portal_user' NAME='frm_portal_user'>\n";
		$portal_user_control .= "<OPTION VALUE = 0 SELECTED>Select User</OPTION>\n";
			while ($row_pu = mysql_fetch_array($result_pu, MYSQL_ASSOC)) {
				$theName = $row_pu['name_f'] . " " . $row_pu['name_l'] . " (" . $row_pu['user'] . ")";
				$portal_user_control .= "<OPTION VALUE=" . $row_pu['id'] . ">" . $theName . "</OPTION>\n";
				}
		$portal_user_control .= "</SELECT>\n";
		} else {
		$has_portal = 0;
		}

	print "\n\tvar severities = new Array();\n";				// 6/25/10 - builds JS array of severities indexed to incident types
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	print "\t severities.push(0);\n";		// the inserted "TBD" dummy
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		print "\t severities.push({$row['set_severity']});\n";
		}
?>

	function set_controlvalue(wizval, inval) {
		if(document.wiz_add) {document.forms['add'][inval].value = document.forms['wiz_add'][wizval].value;}
		}

	function set_datselectvalue(thecontrol, inval) {
		var formfield = document.forms['add'][thecontrol];
		if(document.wiz_add) {formfield.selectedIndex = inval;}
		}		
		
	function do_set_priority(inval) {
		if(document.wiz_add) {document.add.frm_severity.selectedIndex = inval;}
		}

	function do_set_severity (inval) {
		if(document.wiz_add) {document.add.sel_in_types_id.selectedIndex = inval;}
		if(severities[inval]>0) {
			if(document.add) {document.add.frm_severity.selectedIndex = severities[inval];}
			if(document.wiz_add) {document.wiz_add.wiz_frm_severity.selectedIndex = severities[inval];}
			}
		}

	function do_act_window(the_url) {				// 5/6/11
		newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (newwindow == null) {
			alert ("Station log operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}

	function do_pat_window(the_url) {				// 5/6/11
		newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (newwindow == null) {
			alert ("Station log operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		}

	function ck_frames() {
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			}
		}
		
	function load_markup() {
		load_exclusions();
		load_ringfences();
		load_catchments();
		load_basemarkup();
		load_groupbounds();	
		load_regions();
		}

</SCRIPT>
<SCRIPT SRC="./js/misc_function.js" type="application/x-javascript"></SCRIPT>
<?php
	if ((intval(get_variable('broadcast') == 1)) &&  ($_SESSION['good_internet'])) {
		require_once('./incs/socket2me.inc.php');		// 5/22/2013
		}
?>
</HEAD>
<?php

if (!(mysql_table_exists("$GLOBALS[mysql_prefix]places"))) {
	$city_name_array_str="";
	} else {		// 2/21/11 - build array of city names for JS usage
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` =  'city' ORDER BY `id`";		// get all city names
	$place_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$num_places = mysql_num_rows($result);	//	3/17/11
	$city_name_array_str = $sep = "";
	while ($place_row = stripslashes_deep(mysql_fetch_assoc($place_result))) {
		$city_name_array_str .= "{$sep}'{$place_row['name']}'";
		$sep =",";
		}
	}
						// apply cid data if available
$street = isset($cid_street) ?  $cid_street : $res_row['street'] ;

if (isset($cid_city)) {
	$city = $cid_city;
	} else {
	$city = (!(empty($res_row['city'])))?	$res_row['city']  : get_variable('def_city');		// 11/5/10, 3/17/11
	}
if (isset($cid_state)) {
	$st = $cid_state;
	} else {
	$st = (!(empty($res_row['state'])))?	$res_row['state'] : get_variable('def_st') ;
	}
$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10
$phone = (isset($cid_phone))? format_phone($cid_phone): get_variable('def_area_code');
$reported_by = (isset($cid_name))?	$cid_name: "TBD";

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	} else {
	$curr_viewed = $al_groups;
	}

$curr_names="";	//	6/10/11
$z=0;	//	6/10/11
foreach($curr_viewed as $grp_id) {	//	6/10/11
	$counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
	$curr_names .= get_groupname($grp_id);
	$curr_names .= $counter;
	$z++;
	}

$heading = "Add Ticket - " . get_variable('map_caption');


$from_left = 500;
$from_top = 150;				// 11/22/2012
$cid_lat = isset($cid_lat) ? $cid_lat : ""; // 8/8/11
$cid_lng = isset($cid_llng) ? $cid_lng : ""; // 8/8/11
$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
$onload_str .= (is_float($cid_lat))? " pt_to_map( add, {$cid_lat} ,{$cid_lng});": "";
$doloc = intval(get_variable('add_uselocation'));
$loc_startup = ($doloc == 1) ? "getLocation();" : "";
$do_inwin = ($in_win) ? $loc_startup : "ck_frames();";


//dump(__LINE__);
//dump(is_float($cid_lat));
//dump($onload_str);
?>
<SCRIPT>
var obj_sugg;
function createAutoComplete() {
	if($('wiz_my_txt')) {
		obj_sugg = new autoComplete(aNames,document.getElementById('wiz_my_txt'),document.getElementById('suggest'),50);
		$('my_txt').value = $('wiz_my_txt').value;
		} else {
		obj_sugg = new autoComplete(aNames,document.getElementById('my_txt'),document.getElementById('suggest'),50);	
		}
	}
var aNames =[<?php print $city_name_array_str;?>];

function do_nearby(the_form){		// 11/22/2012
	if (the_form.frm_lat.value.length == 0) {
		alert("Map <?php echo get_text("Location");?> is required for nearby <?php echo get_text("Incident");?> lookup.");
		return;
		}
	var the_url = "nearby.php?tick_lat="+the_form.frm_lat.value+"&tick_lng="+the_form.frm_lng.value;
	newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
	if (newwindow == null) {
		alert ("Nearby operation requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	newwindow.focus();
	}		// end do_nearby()

function do_bldg(in_val) {									// called with zero-based array index - 3/29/2014
	if(myMarker) {map.removeLayer(marker); }
	var obj_bldg = bldg_arr[in_val];						// nth object
	document.add.frm_street.value = obj_bldg.bldg_street;
	document.add.frm_city.value = obj_bldg.bldg_city;
	document.add.frm_state.value = obj_bldg.bldg_state;
	var theLat = parseFloat(obj_bldg.bldg_lat).toFixed(6);
	var theLng = parseFloat(obj_bldg.bldg_lon).toFixed(6);
	if (document.add.frm_lat) {
		document.add.frm_lat.value = theLat;
		document.add.show_lat.value = theLat.toString();
		document.add.frm_lng.value = theLng;
		document.add.show_lng.value = theLng.toString();
		}
	if (obj_bldg.bldg_info.length > 0 ) {
		var close_str = "<span onclick = \"$('bldg_info').style.display = 'none';\"><b><center><u>X</u></center></b></span>";
		$('bldg_info').innerHTML = obj_bldg.bldg_info + close_str;		//
		$('bldg_info').style.display = "inline";
		}
	pt_to_map(document.add, theLat, theLng) ;			// to map
	}		// end function do_bldg()

<?php								// 3/29/2014

$query_bldg = "SELECT * FROM `$GLOBALS[mysql_prefix]places` WHERE `apply_to` = 'bldg' ORDER BY `name` ASC";		// types in use
$result_bldg = mysql_query($query_bldg) or do_error($query_bldg, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_num_rows($result_bldg) > 0) {
	$i = 0;
	$sel_str = "<select tabindex=1 id='sel_bldg' name='bldg' onChange = 'do_bldg(this.options[this.selectedIndex].value); '>\n";
	$sel_str .= "\t<option value = '' selected>Select building</option>\n";
	echo "\n\t var bldg_arr = new Array();\n";
	while ($row_bldg = stripslashes_deep(mysql_fetch_assoc($result_bldg))) {
//			4/27/2014
		$sel_str .= "\t<option value = {$i} >{$row_bldg['name']}</option>\n";
		echo "\t var bldg={ bldg_name:\"{$row_bldg['name']}\", bldg_street:\"{$row_bldg['street']}\", bldg_city:\"{$row_bldg['city']}\",
			bldg_state:\"{$row_bldg['state']}\", bldg_lat:\"{$row_bldg['lat']}\", bldg_lon:\"{$row_bldg['lon']}\", bldg_info:\"{$row_bldg['information']}\"};\n";
		echo "\t bldg_arr.push(bldg);\n";		// object onto array
		$i++;
		}		// end while ()

	$sel_str .= "\t</SELECT>\n";
	}		// end if (mysql... )

//	5/23/2015

$addr_sugg_str = (intval (get_variable('addr_source')) == 0 )? "" : " onkeyup=\"get_addr_list(this.value);\"  autocomplete=\"off\"";

if (intval (get_variable('addr_source')) > 0 ) {
?>
	var char_lim = 2;									// minimum lgth for ajax call - 8/10/2014

	function do_selected_addr (payload) {	     		// handles selected item for form  fill-in
		document.getElementById("addr_list").innerHTML = "";
		var payload_arr = payload.split("/",8);
		document.add.frm_lat.value = 			payload_arr[1];			// lat
		document.add.frm_lng.value = 			payload_arr[2];			// lng
		do_coords(document.add.frm_lat.value, document.add.frm_lng.value );
		document.add.frm_street.value = 		payload_arr[3];			// street
		document.add.frm_address_about.value = 	payload_arr[4];			// about/community
		document.add.frm_city.value = 			payload_arr[5];			// city
		loc_lkup(document.add);
		do_lat (parseFloat(payload_arr[1]));
		do_lng (parseFloat(payload_arr[2]));
		pt_to_map (document.add, parseFloat(payload_arr[1]), parseFloat(payload_arr[2]));		// lat, lng as floats
		document.add.frm_address_about.focus();
		return;
		}		// end function


	function get_addr_list(inStr) {	     				// 8/10/2014
			function handle_lookup_list(req) {			// the private callback function
				document.getElementById("addr_list").innerHTML = req.responseText;
				}
     	if (inStr.length < char_lim) { return;}			// revisit length check
		else {

			var params = "q=" + escape(inStr);			// post keyboarded string
			sendRequest ('./ajax/get_addrs.php', handle_lookup_list, params);	// url, return handler, data sent
			}
	 	}
<?php	
	}
?>
</SCRIPT>
<?php
$useWizard = intval(get_variable('use_wizard'));
$do_wizard = ($useWizard == 1) ? "modalStart();" : "";
?>
<BODY onLoad="<?php print $do_inwin;?> load_markup(); do_lock_pe(document.add); document.add.frm_street.focus(); <?php print $do_wizard;?>; do_timedate();">
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<div id = "bldg_info" class = "even" style = "display: none; position:fixed; left:500px; top:30px; z-index: 998; width:300px; height:auto;"></div>
<?php
if($in_win) {
	$leftcol = "<DIV id = 'leftcol_inwin' style='position: relative; left: 10px; top: 60px; float: left;'>";
	} else {
	require_once('./incs/links.inc.php');
	$leftcol = "<DIV id = 'leftcol' style='position: relative; left: 30px; float: left;'>";
	}
?>
	<DIV id = "outer" style='position: absolute; left: 0px; top: 0px; width: 90%;'>
	<?php print $leftcol;?>
		<DIV CLASS='header' style = "height:40px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header' STYLE='background-color: inherit;'>New <?php print get_text('Call');?></SPAN>
			<BR />
			<SPAN CLASS='text_blue'>(mouseover caption for help information)</SPAN><BR />
		</DIV>
<?php
	If(!$in_win) {
?>
		<FORM NAME="add" METHOD="post" ENCTYPE="multipart/form-data" ACTION="<?php print basename(__FILE__);?>?add=true" onSubmit="return validate(document.add)">
<?php
		} else {
?>
		<FORM NAME="add" METHOD="post" ENCTYPE="multipart/form-data" ACTION="<?php print basename(__FILE__);?>?add=true&mode=1" onSubmit="return validate(document.add)">
<?php
		}
?>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Start Here</LEGEND>
			<DIV style='position: relative;'>
				<LABEL for="sel_in_types_id" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_nature"];?>');"><?php print $nature;?>: <font color='red' size='-1'>*</font></LABEL>
				<SELECT id='sel_in_types_id' NAME="frm_in_types_id"  tabindex=60 onChange="do_set_severity (this.selectedIndex); do_inc_protocol(this.options[selectedIndex].value.trim());">
					<OPTION VALUE=0 SELECTED>TBD</OPTION>				<!-- 1/11/09 -->
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
					$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					$the_grp = strval(rand());			//  force initial optgroup value
					$i = 0;
					while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
						if ($the_grp != $temp_row['group']) {
							print ($i == 0)? "": "</OPTGROUP>\n";
							$the_grp = $temp_row['group'];
							print "<OPTGROUP LABEL='{$temp_row['group']}'>\n";
							}
						$color = $temp_row['color'];
						$bgcolor = "white";
						print  "\t<OPTION VALUE=' {$temp_row['id']}' CLASS='{$temp_row['group']}' style='color: {$color}; background-color: {$bgcolor};' title='" . addslashes($temp_row['description']) . "'> " . addslashes($temp_row['type']) . " </OPTION>\n";
						$i++;
						}		// end while()
					print "\n</OPTGROUP>\n";
?>
				</SELECT>
				<BR />
				<LABEL for="severity" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_prio"];?>');"><?php print get_text("Priority");?>: <font color='red' size='-1'>*</font></LABEL>
				<SELECT id='severity' NAME="frm_severity" tabindex=70>
					<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
					<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
					<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
				</SELECT>
				<BR />
				<LABEL onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_proto"];?>');"><?php print get_text("Protocol");?>:</LABEL>
				<SPAN class='td_data_wrap' ID='proto_cell' style='display: inline-block; color: #000000; padding: 5px; max-height: 50px; overflow-y: auto;'></SPAN>
				<BR />
<?php
				if (mysql_num_rows($result_bldg) > 0) {
?>
					<LABEL for="sel_bldg"><?php print get_text("Building"); ?>:</LABEL>
					<?php echo $sel_str;?>
					<BR />
<?php
					}
?>
				<LABEL for="street" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_loca"];?>');"><?php print get_text("Location"); ?>:</LABEL>
				<INPUT id='street' NAME="frm_street" tabindex=20 SIZE="64" TYPE="text" VALUE="<?php print $street;?>" MAXLENGTH="96" <?php echo $addr_sugg_str ;?> />
				<DIV ID="addr_list" style = "display:inline;"></DIV>
				<BR />
				<LABEL for="about" onmouseout="UnTip()" onmouseover="Tip('About Address - for instance, round the back, building number etc.');"><?php print get_text("Address About"); ?>:</LABEL>
				<INPUT id='about' NAME="frm_address_about" tabindex=30 SIZE="64" TYPE="text" VALUE="" MAXLENGTH="512" />
				<BR />
				<LABEL for="my_txt" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_city"];?>')"><?php print get_text("City"); ?>:</LABEL>
<?php
				if($gmaps || $good_internet) {
?>
					<BUTTON type="button" onClick="Javascript:loc_lkup(document.add);return false;"><img src="./markers/glasses.png" alt="Lookup location." /></BUTTON>&nbsp;&nbsp;
<?php
					}
?>
				<INPUT ID="my_txt" onFocus = "createAutoComplete();$('city_reset').visibility='visible';" NAME="frm_city" autocomplete="off" tabindex=30 SIZE="32" TYPE="text" VALUE="<?php print $city; ?>" MAXLENGTH="32" onChange = " $('city_reset').visibility='visible'; this.value=capWords(this.value)">
				<span id="suggest" onmousedown="$('suggest').style.display='none'; $('city_reset').style.visibility='visible';" style="visibility:hidden;border:#000000 1px solid;width:150px;right:400px;" /></span>
				<IMG ID = 'city_reset' SRC="./markers/reset.png" STYLE = "margin-left:20px; visibility:hidden;" onClick = "this.style.visibility='hidden'; document.add.frm_city.value=''; document.add.frm_city.focus(); obj_sugg = null;" />
<?php
				if ($gmaps) {		// 12/1/2012
?>
					<BUTTON type="button" onClick="Javascript:do_nearby(this.form);return false;">Nearby?</BUTTON> <!-- 11/22/2012 -->
<?php
					}
?>
				<BR />
				<LABEL for="state" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles['_state'];?>');"><?php print get_text("St"); ?> <font color='red' size='-1'>*</font></LABEL>
<?php
				$locale = get_variable('locale');
				if($locale == "0") {
					print "<SELECT id='state' NAME='frm_state' tabindex=40>";
					foreach($us_states as $val) {
						print "<OPTION VALUE='" . $val['code'] . "'>" . $val['name'] . " (" . $val['code'] . ")</OPTION>";
						}
					print "</SELECT>";
					} else {
?>
					<INPUT ID='state' NAME="frm_state" tabindex=40 SIZE="<?php print $st_size;?>" TYPE="text" VALUE="<?php print $st;?>" MAXLENGTH="<?php print $st_size;?>" />
<?php
					}
?>
				<BR />
				<LABEL for="toaddress" onmouseout="UnTip()" onmouseover="Tip('To address - Not plotted on map, for information only');"><?php print get_text("To Address"); ?>:</LABEL>
				<INPUT id='toaddress' NAME="frm_to_address" tabindex=50 SIZE="72" TYPE="text" VALUE=""  MAXLENGTH="1024">
				<BR />
				<DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 1px outset #707070;'></DIV>
				<BR />
<?php
				if ($gmaps || $doloc|| $good_internet) {
?>
					<LABEL for="lock_p" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_coords"];?>');"><?php print $incident;?> Lat/Lng: <font color='red' size='-1'>*</font>
						<img id='lock_p' border=0 src='./markers/unlock2.png' STYLE='float: right; vertical-align: middle; margin-left: 20px;' onClick = 'do_unlock_pos(document.add);'>
					</LABEL>				
					<INPUT ID='show_lat' SIZE="11" TYPE="text" NAME="show_lat" VALUE="" />
					<INPUT ID='show_lng' SIZE="11" TYPE="text" NAME="show_lng" VALUE="" />
<?php
					$locale = get_variable('locale');
					$grid_types = array("USNG", "OSGB", "UTM");
?>
					&nbsp;&nbsp;<SPAN class='td_label text' display: inline;' onmouseout="UnTip()" onmouseover="Tip('Grid Reference');" onClick = 'do_grid_to_ll();'><?php print $grid_types[$locale];?>:</SPAN>
					<INPUT ID='griddisp' SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='' DISABLED />
					<BR />
<?php
					} else {		// end if ($gmaps)
?>
					<INPUT TYPE="hidden" NAME="show_lat" VALUE="" />
					<INPUT TYPE="hidden" NAME="show_lng" VALUE="" />
<?php
					}		// end else
?>
				<BR />
				<CENTER>
				<SPAN style='text-align: center;'><IMG SRC="glasses.png" BORDER="0"/>: Lookup </SPAN><BR />
				</CENTER>
			</DIV>
		</FIELDSET>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>General</LEGEND>
			<DIV style='position: relative;'>
<?php
			if (empty($inc_name)) {

				switch (get_variable('serial_no_ap')) {									// 1/22/09

					case 0:								/*  no serial no. */
						$prepend = $append = "";
						break;
					case 1:								/*  prepend  */
						$prepend = $ticket_id . "/";
						$append = "";
						break;
					case 2:								/*  append  */
						$prepend = "";
						$append = "/" . $ticket_id;
						break;
					default:							/* error????  */
						$prepend = $append = " error ";
					}				// end switch()
				}				// end if (empty($inc_name))
?>
				
				<LABEL for="scope" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_name"];?>');"><?php print get_text("Incident name");?>: <font color='red' size='-1'>*</font></LABEL>
<?php
				if (!(empty($inc_name))) {				// 11/13/10
?>
					<INPUT id='scope' NAME="frm_scope" tabindex=120 SIZE="56" TYPE="text" VALUE="<?php print $inc_name;?>" MAXLENGTH="61" />
<?php
					} else {
?>
					<INPUT id='scope' NAME="frm_scope" tabindex=130 SIZE="56" TYPE="text" VALUE="TBD" MAXLENGTH="61" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}" onkeypress='user_inc_name = true;'/><?php print $append;?>
<?php
					}										// end else {} 11/13/10
?>	
				<BR />
				<LABEL for="description" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_synop"];?>');"><?php print get_text("Synopsis");?>: <font color='red' size='-1'>*</font></LABEL>
				<TEXTAREA id='description' NAME="frm_description" tabindex=80 COLS="48" ROWS="2" WRAP="virtual"></TEXTAREA>
				<BR />
<?php
				$query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);
				if (mysql_num_rows($result_sigs)>0) {
?>

<SCRIPT>
					function set_signal(inval) {				// 12/18/10
						var lh_sep = (document.add.frm_description.value.trim().length>0)? " " : "";
						var temp_ary = inval.split("|", 2);		// inserted separator
						document.add.frm_description.value+= lh_sep + temp_ary[1] + ' ';
						document.add.frm_description.focus();
						}		// end function set_signal()

					function set_signal2(inval) {				// 12/18/10
						var lh_sep = (document.add.frm_comments.value.trim().length>0)? " " : "";
						var temp_ary = inval.split("|", 2);		// inserted separator
						document.add.frm_comments.value+= lh_sep  + temp_ary[1] + ' ';
						document.add.frm_comments.focus();
						}		// end function set_signal()
</SCRIPT>				
					<LABEL for="signals" onmouseout="UnTip()" onmouseover="Tip('Signal');">Signal &raquo;</LABEL>
					<SELECT ID='signals' NAME='signals' tabindex=90 onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
							print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
					<BR />
<?php
				}		// end if (mysql_num_rows($result_sigs)>0)
				if(get_num_groups()) {
					if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
						<BR />
						<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
						<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center; float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
						<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
						</LABEL>
						<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
							<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
							<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
<?php
						$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
						print get_user_group_butts(($_SESSION['user_id']));
?>
						</DIV>
						<BR />
<?php
						} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>
						<BR />
						<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
						<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
						<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
						</LABEL>
						<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
							<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
							<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>

<?php
							$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
							print get_user_group_butts(($_SESSION['user_id']));
?>
						</DIV>
						<BR />
<?php
						} else {
?>
						<BR />
						<LABEL onmouseout='UnTip()' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?>:
						<SPAN id='expand_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';"><B>+</B></SPAN>
						<SPAN id='collapse_gps' CLASS='plain' onMouseover='do_hover(this.id)' onMouseout='do_plain(this.id)' style='width: 20px; text-align: center;  float: right; margin-right: 30px; padding: 5px; border: 1px outset #707070; display: none; font-size: 16px; text-decoration: none;' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';"><B>-</B></SPAN>
						</LABEL>
						<DIV id='checkButts' style='display: none; position: relative; right: 10px; width: 40%;'>
							<SPAN id='checkbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
							<SPAN id='uncheckbut' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
<?php
							$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
							print get_user_group_butts_readonly($_SESSION['user_id']);
?>
						</DIV>
						<BR />
<?php
						}
					} else {
?>
				<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">
<?php
				}//	end if num of groups is greater than 1 (i.e. non multi-region system
?>
				<LABEL for="sel_maj_inc"><?php print get_text("Major Incident"); ?>:</LABEL>
				<SELECT ID='sel_maj_inc' NAME='frm_maj_inc'>
					<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
					$query = "SELECT * FROM `$GLOBALS[mysql_prefix]major_incidents` WHERE `mi_status` = 'Open' OR `inc_endtime` IS NULL OR DATE_FORMAT(`inc_endtime`,'%y') = '00' ORDER BY `id` ASC";
					$result_mi = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
					while ($row_mi = stripslashes_deep(mysql_fetch_assoc($result_mi))) {
						print "\t<OPTION VALUE='{$row_mi['id']}'>{$row_mi['name']}</OPTION>\n";
						}
?>
				</SELECT>
				<BR />

<?php
				if ($facilities > 0) {				// any? - 3/24/10
?>
					<LABEL for="facy" onmouseout="UnTip()" onmouseover="Tip('Incident located at Facility');"><?php print get_text("Incident at Facility");?>?:</LABEL>
					<SELECT id='facy' NAME="frm_facility_id" tabindex=140 onChange="do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim())"><?php print $pulldown; ?></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
					<BR />
					<LABEL for="recfacy" onmouseout="UnTip()" onmouseover="Tip('Receiving Facility');"><?php print get_text("Receiving Facility");?>?:</LABEL>
					<SELECT id='recfacy' NAME="frm_rec_facility_id" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"><?php print $pulldown2; ?></SELECT>
					<BR />
<?php
					} else {		// end if ($facilities > 0)
?>
					<INPUT TYPE = 'hidden' NAME = 'frm_facility_id' VALUE=''>
					<INPUT TYPE = 'hidden' NAME = 'frm_rec_facility_id' VALUE=''>
					<BR />				
<?php
					}
?>				
			</DIV>
		</FIELDSET>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Contacts</LEGEND>
			<DIV style='position: relative;'>				
				<LABEL for="frm_phone" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_phone"];?>');"><?php print get_text("Phone"); ?>:
<?php
				if(get_variable('locale') == 0) {
?>				
					<BUTTON type="button" style='float: right;' onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="Lookup phone no." ></button>&nbsp;&nbsp;
<?php
					}
?>				
				</LABEL>
				<INPUT ID='frm_phone' NAME="frm_phone" tabindex=50 SIZE="16" TYPE="text" VALUE="<?php print $phone;?>"  MAXLENGTH="16" />
				<SPAN ID='repeats'></SPAN>
				<BR />
				<LABEL for="contact" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_caller"];?>');"><?php print get_text("Reported by");?>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></LABEL>
				<INPUT id='contact' NAME="frm_contact"  tabindex=110 SIZE="56" TYPE="text" VALUE="<?php print $reported_by; ?>" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}">
				<BR />
<?php
				if($has_portal == 1) {
?>
					<LABEL for="contact" onmouseout="UnTip()" onmouseover="Tip('Associate this ticket with a specific portal user so they can see it.');"><?php print get_text("Portal User");?>:</LABEL>
					<?php print $portal_user_control;?>					
					<BR />
<?php
					} else {
?>
					<INPUT TYPE='hidden' NAME="frm_portal_user" VALUE=0>
<?php
					}
?>
			</DIV>
		</FIELDSET>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Call History</LEGEND>
			<DIV style='position: relative;'>
				<LABEL for="911" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_911"];?>');"><?php print get_text("911 Contacted"); ?>:
				<SPAN id='now_but' CLASS='plain text' style='width: auto; display: inline; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="javascript:var now = new Date(); document.add.frm_nine_one_one.value=now.getDate()+'/' + (now.getMonth()+1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();">Now</SPAN>
				</LABEL>
				<INPUT id='911' NAME="frm_nine_one_one" tabindex=100 SIZE="56" TYPE="text" VALUE="" MAXLENGTH="96" />&nbsp;
				<BR />

				<LABEL for="comments" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_disp"];?>');"><?php print $disposition;?>:</LABEL>
				<TEXTAREA id='comments' NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA>
				<BR />		
<?php											// 2/8/11
				if (mysql_num_rows($result_sigs)>0)	{
?>
					<LABEL for="signals2" onmouseout="UnTip()" onmouseover="Tip('Signal');"><?php print get_text("Signal");?>:&raquo;</LABEL>
					<SELECT id='signals2' NAME='signals' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
						<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
						$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
						while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result_sigs))) {
							print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
							}
?>
					</SELECT>
					<BR />
<?php
					}				// end if (mysql_num_rows($result_sigs)>0)
?>
			</DIV>
		</FIELDSET>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Time and Date</LEGEND>
			<DIV style='position: relative;'>
				<LABEL for="year_problemstart" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_start"];?>');"><?php print get_text("Run Start");?>:</LABEL>
				<?php print generate_date_dropdown('problemstart',0,TRUE);?>
				<BR />
				<LABEL for="statusSelect" onmouseout="UnTip()" onmouseover="Tip('Incident Status - Open, Closed or Scheduled');"><?php print get_text("Status"); ?>:</LABEL>
				<SELECT ID='statusSelect' NAME='frm_status'>
					<OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected>Open</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'>Closed</OPTION>
					<OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED']; ?>'>Scheduled</OPTION>
				</SELECT>
				<BR />
				<LABEL for="allowend" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_end"];?>');"><?php print get_text("Run End");?>:
				<input id='allowend' type="radio" style='float: right;' name="re_but" onClick ="do_end(this.form);" /></LABEL>
				<SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
				<BR />
				<LABEL for="bookingselect" onmouseout="UnTip()" onmouseover="Tip('<?php print $titles["_booked"];?>');"><?php print get_text("Scheduled Date");?>:
				<input id='bookingselect' type="radio" style='float: right;' name="book_but" onClick ="do_booking(this.form);" /></LABEL>
				<SPAN style = "visibility:hidden" ID = "booking1"><?php print generate_date_dropdown('booked_date',0, TRUE);?></SPAN>
				<BR />
			</DIV>
		</FIELDSET>
		<FIELDSET>
			<LEGEND class='text_large text_bold'>Files</LEGEND>
			<DIV style='position: relative;'>
				<LABEL for="theFile" onmouseout="UnTip()" onmouseover="Tip('Chose a file to upload');"><?php print get_text("Choose a file to upload"); ?>:</LABEL>
				<INPUT NAME="theFile" TYPE="file" />
				<BR />
				<LABEL for="filename" onmouseout="UnTip()" onmouseover="Tip('Type a descriptive file name');"><?php print get_text("Filename"); ?>:</LABEL>
				<INPUT id='filename' NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE="">
				<BR />
			</DIV>
		</FIELDSET>
<?php
			if($in_win) {
				if($gmaps) {
?>
					<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV><BR />
					<SPAN id='map_caption' class='text bold text_center' style='display: inline-block;'><?php print get_variable('map_caption');?></SPAN><BR />
					<DIV id='button_bar' class='but_container'>
						<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>Add <?php print get_text('Incident');?></SPAN>
						<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_cancel(document.add); window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						<SPAN id='reset_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_reset(document.add);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN id='sub_but_but' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.add);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					</DIV>
					</DIV>
<?php
					} else {
?>
					<DIV id='map_canvas' style='border: 1px outset #707070; display: none;'></DIV>
					<DIV id='button_bar' class='but_container'>
						<SPAN id='can_but' roll='button' aria-label='Cancel' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_cancel(document.add); window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
						<SPAN id='reset_but' roll='button' aria-label='Reset' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_reset(document.add);"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
						<SPAN id='sub_but_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: right; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate(document.add);"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
					</DIV>
					</DIV>
<?php							
					}
				} else {
?>
				</DIV>
				<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
					<DIV style='position: fixed; top: 120px; z-index: 4500;'>
						<SPAN id='hist_but' roll='button' tabindex=1 aria-label='History' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_hist_win();"><?php print get_text("History"); ?><BR /><IMG id='can_img' SRC='./images/list.png' /></SPAN>
						<SPAN id='can_but' roll='button' tabindex=2  aria-label='Cancel' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_cancel(document.add); document.can_Form.submit();"><?php print get_text("Cancel"); ?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
						<SPAN id='reset_but' roll='button' tabindex=3  aria-label='Reset' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_reset(document.add);"><?php print get_text("Reset"); ?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
						<SPAN id='sub_but_but' roll='button' tabindex=4  aria-label='Submit' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="validate(document.add);"><?php print get_text("Submit"); ?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
<?php
						if (!($in_win )) {
?>
							<SPAN id='act_but' roll='button' tabindex=5  aria-label='Actions' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_act_window('action_w.php?ticket_id=<?php echo $ticket_id;?>');"><?php print get_text("Action"); ?><BR /><IMG id='can_img' SRC='./images/action.png' /></SPAN>
							<SPAN id='pat_but' roll='button' tabindex=6  aria-label='Patients' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="do_pat_window('patient_w.php?ticket_id=<?php echo $ticket_id;?>');"><?php print get_text("Patient"); ?><BR /><IMG id='can_img' SRC='./images/patient.png' /></SPAN>

<?php
							}
?>
					</DIV>
				</DIV>
<?php
				if ($gmaps || $good_internet) {
?>
					<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
<?php
					if($gmaps) {
?>
						<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV><BR />
						<SPAN id='map_caption' class='text bold text_center' style='display: inline-block;'><?php print get_variable('map_caption');?></SPAN><BR />
<?php
						} else {
?>
						<DIV id='map_canvas' style='border: 1px outset #707070; display: none;'></DIV>
						<SPAN id='map_caption' class='text bold text_center' style='display: none;'><?php print get_variable('map_caption');?></SPAN><BR />
<?php
						}
?>
					</DIV>
					</DIV>
<?php
					}
				}
?>
		<INPUT TYPE="hidden" NAME="frm_lat" VALUE="">				<!-- // 9/9/08 -->
		<INPUT TYPE="hidden" NAME="frm_lng" VALUE="">
		<INPUT TYPE="hidden" NAME="ticket_id" VALUE="<?php print $ticket_id;?>">	<!-- 1/25/09, 3/10/09 -->
		<INPUT TYPE='hidden' NAME="frm_do_scheduled" VALUE=0>
		</FORM>
<?php
	if($in_win == 0) {
		$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
		print add_sidebar(FALSE, TRUE, TRUE, FALSE, TRUE, $allow_filedelete, 0, 0, 0, 0);
		}
	}
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>
<?php
if ($gmaps || $good_internet) {
?>
	<SCRIPT>
	var map;				// make globally visible
	var thelevel = '<?php print $the_level;?>';
	var the_icon;
	var currentPopup;
	var marker;
	var myMarker;
	var markers;
	var boundary = [];			//	exclusion zones array
	var bound_names = [];
	var cmarkers;
	var latLng;
	
	// set widths
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	if(window.isinwin) {set_fontsizes(viewportwidth, "popup");} else {set_fontsizes(viewportwidth, "fullscreen");}
	if(window.isinwin) {mapWidth = viewportwidth * .95;} else {mapWidth = viewportwidth * .40;}
	mapHeight = viewportheight * .55;
	if(window.isinwin) {outerwidth = viewportwidth * .95;} else {outerwidth = viewportwidth * .99;}
	outerheight = viewportheight * .95;
	if(window.isinwin) {colwidth = outerwidth;} else {colwidth = outerwidth * .40;}
	colheight = outerheight * .95;
	leftcolwidth = viewportwidth * .45;
	winleftcolwidth = viewportwidth * .90;
	rightcolwidth = viewportwidth * .35;
	fieldwidth = leftcolwidth * .45;
	medfieldwidth = leftcolwidth * .20;	
	smallfieldwidth = leftcolwidth * .10;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('leftcol_inwin')) {$('leftcol_inwin').style.width = winleftcolwidth + "px";}
	if($('leftcol')) {$('leftcol').style.height = colheight + "px";}
	if($('rightcol')) {$('rightcol').style.width = colwidth + "px";}
	if($('rightcol')) {$('rightcol').style.height = colheight + "px";}
	if($('map_canvas')) {$('map_canvas').style.width = mapWidth + "px";}
	if($('map_canvas')) {$('map_canvas').style.height = mapHeight + "px";}
	if($('map_caption')) {$('map_caption').style.width = mapWidth + "px";}
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}	
	var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
		iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
		}
		});
	var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
		}
		});
	var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
		}
		});
	var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
		}
		});
<?php
	if($good_internet) {
?>
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
		var bounds = map.getBounds();
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
		function onMapClick(e) {
			if(doReverse == 0) {return;}
			if(marker) {map.removeLayer(marker);}
			if(myMarker) {map.removeLayer(myMarker);}
			var iconurl = "./our_icons/yellow.png";
			icon = new baseIcon({iconUrl: iconurl});
			myMarker = new L.marker(e.latlng, {icon:icon, draggable:'true'});
			myMarker.addTo(map);
			newGetAddress(e.latlng, "ni");
			};

		map.on('click', onMapClick);
<?php
		do_kml();
		}
?>
	</SCRIPT>
<?php
	}
if($useWizard) {
	include("./forms/inc_wizard.php");
	}
?>
</BODY>
</HTML>