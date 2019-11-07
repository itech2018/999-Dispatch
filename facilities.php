<?php

error_reporting(E_ALL);
/*
8/20/09 created facilities.php from units.php
10/6/09 Added links button
10/8/09 Index in list and on marker changed to part of name after /
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs
10/29/09 Removed period after index in sidebar
11/11/09 Fixed sidebar display when not using map location
11/11/09 Made map location mandatory for form input, added 'top' anchor.
11/27/09 Changed edit 'Cancel' action
3/24/10 removed 'top' function calls
7/5/10 Added Location fields and phone number fields as for Incident. Geocoding of address and reverse geocoding of map click implemented.
7/7/10 mysql_fetch_array -> mysql_fetch_assoc
7/7/10 removed refresh, add mail button, list_xxx function name changed
7/22/10 NULL handling revised, miscjs, google reverse geocode parse added
7/27/10 unit-level limitation applied
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/28/10 Added default icon for tickets entered in no-maps operation
8/13/10 map.setUIToDefault();
8/25/10 light top-frame button
11/29/10 locale 2 handling added
12/6/10 internet test relocated
2/17/11 Changed wrong log events from log_unit_status to LOG_FACILITY_ADD or LOG_FACILITY_CHANGE as appropriate
3/15/11 Added reference to stylesheet.php for revisable day night colors.
3/19/11 changed index length to 6 chars
4/27/11 icon logic added, top/bottom nav added
5/4/11 get_new_colors() added
7/1/11 permissions corrected
8/1/11 state length increased to 4 chars
6/10/11 Added Groups and Boundaries
6/18/12 'points' boolean to 'got_points'
9/5/12 GMaps V3 key handling added
1/4/2013 V3 polylines and polygon, setMap conversions made
5/30/13 Implement catch for when there are no allocated regions for current user. 
6/4/2013 beds information added for all operations
8/28/13 Added mailgroup capability - email to mailgroup when facility set as originating or receiving facility. Also about status field.
*/

@session_start();	
session_write_close();
/* if (!($_SESSION['internet'])) {				// 12/6/10
	header("Location: facilities_nm.php");
	} */

require_once($_SESSION['fip']);		//7/28/10
do_login(basename(__FILE__));

$key_field_size = 30;
$st_size = (get_variable("locale") ==0)?  2: 4;	

$FacID = (isset($_GET['id'])) ? $_GET['id'] : 0;	

extract($_GET);
extract($_POST);
if((($istest)) && (!empty($_GET))) {dump ($_GET);}
if((($istest)) && (!empty($_POST))) {dump ($_POST);}

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

function fac_format_date($date){							/* 1/20/2013 */ 
	if (get_variable('locale')==1)	{return date("j/n/y H:i",$date);}					// 08/27/10 - Revised to show UK format for locale = 1	
	else 							{return date(get_variable("date_format"),$date);}	// return date(get_variable("date_format"),strtotime($date));
	}				// end function fac format date
function isempty($arg) {
	return (bool) (strlen($arg) == 0) ;
	}

$usng = get_text('USNG');
$osgb = get_text('OSGB');

$f_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

$icons = $GLOBALS['fac_icons'];
$sm_icons = $GLOBALS['sm_fac_icons'];	//	3/15/11

$f_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$f_types [$row['id']] = array ($row['name'], $row['icon']);
	}
unset($result);

function get_icon_legend (){			// returns legend string
	global $f_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `type`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$temp = $f_types[$row['type']];
		$print .= "\t\t<SPAN class='legend' style='height: 3em; text-align: center; vertical-align: middle; float: none;'> ". $temp[0] . " &raquo; <IMG SRC = './our_icons/" . $sm_icons[$temp[1]] . "' STYLE = 'vertical-align: middle' BORDER=0 PADDING='10'>&nbsp;&nbsp;&nbsp;</SPAN>";
		}
	return $print;
	}			// end function get_icon_legend ()	
	
function get_mailgroup_name($id) {	//	8/28/13
	if($id == 0) {
		return "";
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` WHERE `id` = " . $id;
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_ret = $row['name'];
	return $the_ret;
	}
	
if(file_exists("./incs/modules.inc.php")) {	//	10/28/10
	require_once('./incs/modules.inc.php');
	}	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Facilities Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
	<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
	<![endif]-->
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT><!-- 10/23/12-->
	<script src="./js/leaflet/leaflet.js"></script>
	<script src="./js/proj4js.js"></script>
	<script src="./js/proj4-compressed.js"></script>
	<script src="./js/proj4leaflet.js"></script>
	<script src="./js/leaflet/KML.js"></script>
	<script src="./js/leaflet/gpx.js"></script>  
	<script src="./js/osopenspace.js"></script>
	<script src="./js/leaflet-openweathermap.js"></script>
	<script src="./js/esri-leaflet.js"></script>
	<script src="./js/Control.Geocoder.js"></script>
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
<?php
	if ($_SESSION['internet'] || $_SESSION['good_internet']) {
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
	<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>
	<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
	var map;		// note global
	var layercontrol;
	
	try {
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	parent.upper.show_butts();
	parent.upper.light_butt('facy');		// light the button - 8/25/10

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
	var map;								// map object

	function checkAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = true ;
			}
		}

	function uncheckAll() {	//	9/10/13
		var theField = document.res_add_Form.elements["frm_group[]"];
		for (i = 0; i < theField.length; i++) {
			theField[i].checked = false ;
			}
		}
		
	var type;					// Global variable - identifies browser family
	BrowserSniffer();

	function BrowserSniffer() {													//detects the capabilities of the browser
		if (navigator.userAgent.indexOf("Opera")!=-1 && $) type="OP";	//Opera
		else if (document.all) type="IE";										//Internet Explorer e.g. IE4 upwards
		else if (document.layers) type="NN";									//Netscape Communicator 4
		else if (!document.all && $) type="MO";			//Mozila e.g. Netscape 6 upwards
		else type = "IE";														//????????????
		}

	var starting = false;

	function to_routes(id) {
		document.routes_Form.ticket_id.value=id;
		document.routes_Form.submit();
		}

	function pad(width, string, padding) { 
		return (width <= string.length) ? string : pad(width, string + padding, padding)
		}
		
	function get_stock(id) {
		if(!($('stocklist'))) {return;}
		var randomnumber=Math.floor(Math.random()*99999999);
		var sessID = "<?php print $_SESSION['id'];?>";
		var url = "./ajax/facboard_stocklist.php?facility_id=" + id + "&version=" + randomnumber+"&q="+sessID;
		sendRequest (url,stocklist_cb, "");
		function stocklist_cb(req) {
			var i = 1;
			var stocklist = JSON.decode(req.responseText);
			var theCount;
			var outputtext = "<TABLE id='stocktable' class='fixedheadscrolling scrollable' style='width: 100%; maxHeight: 200px;'>";
			outputtext += "<thead>";
			outputtext += "<TR class='plain_listheader text text_left' style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text text_left'>Stock Item</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Stock Level</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Reorder Level</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>On Order</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Location</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			if(stocklist[0][0] == 0) {
				outputtext += "<TR class='noentries_listRow' style='width: 100%;'><TD class='noentries' COLSPAN=99>Nothing Current</TD></TR>";
				theCount = 0;
				} else {
				theCount = stocklist.length;
				for (var key = 0; key < stocklist.length; key++) {
					outputtext += "<TR class='" + colors[i%2] + "' style='width: 100%;'>";
					outputtext += "<TD id='st_name_" + stocklist[key][0] + "' class='plain_list text text_left'>" + stocklist[key][1] + "</TD>";
					outputtext += "<TD id='st_level_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(20, stocklist[key][2], "\u00a0") + "</TD>";
					outputtext += "<TD id='st_reorder_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(20, stocklist[key][3], "\u00a0") + "</TD>";
					outputtext += "<TD id='st_onorder_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(20, stocklist[key][4], "\u00a0") + "</TD>";
					outputtext += "<TD id='st_location_" + stocklist[key][0] + "' class='plain_list text text_left'>" + stocklist[key][5] + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			$('stocklist').innerHTML = outputtext;
			var stocktbl = document.getElementById('stocktable');
			if(stocktbl) {
				var headerRow = stocktbl.rows[0];
				var viewableRow = 1;
				var headerRow = stocktbl.rows[0];
				for (i = 1; i < stocktbl.rows.length; i++) {
					if(!isViewable(stocktbl.rows[i])) {
						} else {
						viewableRow = i;
						break;
						}
					}
				var tableRow = stocktbl.rows[viewableRow];
				if(tableRow && i != stocktbl.rows.length) {
					for (var i = 0; i < tableRow.cells.length; i++) {
						if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth -1 + "px";}
						}
					} else {
					var cellwidthBase = window.listWidth / 5;
					for (var i = 0; i < tableRow.cells.length; i++) {		
						headerRow.cells[0].style.width = cellwidthBase + "px";
						}
					}
				}		
			}				// end function stocklist_cb()
		}				// end function get_stock()


	function do_is_warehouse(val) {
		if(val == 1) {
			if($('beds_information')) {$('beds_information').style.display = "none";}
			if($('beds_information2')) {$('beds_information2').style.display = "none";}
			if($('warehouse_information')) {$('warehouse_information').style.display = "";}
			} else {
			if($('beds_information')) {$('beds_information').style.display = "";}
			if($('beds_information2')) {$('beds_information2').style.display = "";}
			if($('warehouse_information')) {$('warehouse_information').style.display = "none";}
			}
		}
</SCRIPT>
<?php
	function finished ($caption) {
		print "</HEAD><BODY><!--" . __LINE__ . " -->";
		require_once('./incs/links.inc.php');	// 10/6/09
		print "\n<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>\n";
		print "<FORM NAME='fin_form' METHOD='get' ACTION='" . basename(__FILE__) . "'>";
		print "<INPUT TYPE='hidden' NAME='caption' VALUE='" . $caption . "'>";
		print "<INPUT TYPE='hidden' NAME='func' VALUE='responder'>";
		print "</FORM>\n<A NAME='bottom' />\n</BODY></HTML>";
		}

	function do_calls($id = 0) {				// generates js callsigns array
		$print = "\n<SCRIPT >\n";
		$print .="\t\tvar calls = new Array();\n";
		$query	= "SELECT `id`, `callsign` FROM `$GLOBALS[mysql_prefix]facilities` where `id` != $id";
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
			if (!empty($row['callsign'])) {
				$print .="\t\tcalls.push('" .$row['callsign'] . "');\n";
				}
			}				// end while();
		$print .= "</SCRIPT>\n";
		return $print;
		}		// end function do calls()

	$_postfrm_remove = 	(array_key_exists ('frm_remove',$_POST ))? $_POST['frm_remove']: "";
	$_getgoedit = 		(array_key_exists ('goedit',$_GET )) ? $_GET['goedit']: "";
	$_getgoadd = 		(array_key_exists ('goadd',$_GET ))? $_GET['goadd']: "";
	$_getedit = 		(array_key_exists ('edit',$_GET))? $_GET['edit']:  "";
	$_getadd = 			(array_key_exists ('add',$_GET))? $_GET['add']:  "";
	$_getview = 		(array_key_exists ('view',$_GET ))? $_GET['view']: "";
	$_dodisp = 			(array_key_exists ('disp',$_GET ))? $_GET['disp']: "";

	$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
	$caption = "";
	if ($_postfrm_remove == 'yes') {					//delete Facility - checkbox
		$query = "DELETE FROM $GLOBALS[mysql_prefix]facilities WHERE `id`=" . $_POST['frm_id'];
		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$caption = "<B>Facility <I>" . stripslashes_deep($_POST['frm_name']) . "</I> has been deleted from database.</B><BR /><BR />";
		} else {
		if ($_getgoedit == 'true') {
			$station = TRUE;			//
			$the_lat = empty($_POST['frm_lat'])? "NULL" : quote_smart(trim($_POST['frm_lat'])) ;
			$the_lng = empty($_POST['frm_lng'])? "NULL" : quote_smart(trim($_POST['frm_lng'])) ;
			$frm_opening_hours = base64_encode(serialize($_POST['frm_opening_hours']));
			$curr_groups = $_POST['frm_exist_groups']; 	//	4/14/11
			$groups = isset($_POST['frm_group']) ? ", " . implode(',', $_POST['frm_group']) . "," : $_POST['frm_exist_groups'];	//	3/28/12 - fixes error when accessed from view ticket screen..	
			$fac_id = $_POST['frm_id'];
			$fac_stat = $_POST['frm_status_id'];
			$by = $_SESSION['user_id'];					// 6/4/2013
			$query = "UPDATE `$GLOBALS[mysql_prefix]facilities` SET
				`is_warehouse`= " . quote_smart(trim($_POST['frm_is_warehouse'])) . ",
				`name`= " . 		quote_smart(trim($_POST['frm_name'])) . ",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) . ",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) . ",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`handle`= " . 		quote_smart(trim($_POST['frm_handle'])) . ",
				`icon_str`= " . 	quote_smart(trim($_POST['frm_icon_str'])) . ",
				`boundary`= " . 	quote_smart(trim($_POST['frm_boundary'])) . ",				
				`description`= " . 	quote_smart(trim($_POST['frm_descr'])) . ",
				`beds_a`= " . 		quote_smart(trim($_POST['frm_beds_a'])) . ",
				`beds_o`= " . 		quote_smart(trim($_POST['frm_beds_o'])) . ",
				`beds_info`= " . 	quote_smart(trim($_POST['frm_beds_info'])) . ",
				`capab`= " . 		quote_smart(trim($_POST['frm_capab'])) . ",
				`status_id`= " .	quote_smart(trim($_POST['frm_status_id'])) . ",
				`status_about`= " . quote_smart(trim($_POST['frm_status_about'])) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`contact_name`= " . quote_smart(trim($_POST['frm_contact_name'])) . ",
				`contact_email`= " . 	quote_smart(trim($_POST['frm_contact_email'])) . ",
				`contact_phone`= " . 	quote_smart(trim($_POST['frm_contact_phone'])) . ",
				`security_contact`= " . quote_smart(trim($_POST['frm_security_contact'])) . ",
				`security_email`= " . 	quote_smart(trim($_POST['frm_security_email'])) . ",
				`security_phone`= " . 	quote_smart(trim($_POST['frm_security_phone'])) . ",
				`opening_hours`= " . 	quote_smart($frm_opening_hours) . ",
				`access_rules`= " . 	quote_smart(trim($_POST['frm_access_rules'])) . ",
				`security_reqs`= " . 	quote_smart(trim($_POST['frm_security_reqs'])) . ",
				`pager_p`= " . 		quote_smart(trim($_POST['frm_pager_p'])) . ",
				`pager_s`= " . 		quote_smart(trim($_POST['frm_pager_s'])) . ",
				`type`= " . 		quote_smart(trim($_POST['frm_type'])) . ",
				`user_id`= " . 		quote_smart(trim($_SESSION['user_id'])) . ",
				`notify_mailgroup` = " . quote_smart(trim($_POST['frm_notify_mailgroup'])) . ",
				`notify_email` = " . quote_smart(trim($_POST['frm_notify_email'])) . ",
				`notify_when` = " . quote_smart(trim($_POST['frm_notify_when'])) . ",
				`updated`= " . 		quote_smart(trim($now)) . "
				WHERE `id`= " . 	quote_smart(trim($_POST['frm_id'])) . ";";	//	8/28/13

			$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

			if (!empty($_POST['frm_log_it'])) { do_log($GLOBALS['LOG_FACILITY_CHANGE'], 0, $_POST['frm_id'], $_POST['frm_status_id']);}	//2/17/11
			$list = $_POST['frm_exist_groups']; 	//	4/14/11
			$ex_grps = explode(',', $list); 	//	4/14/11 
			
			if($curr_groups != $groups) { 	//	4/14/11
				foreach($_POST['frm_group'] as $posted_grp) { 	//	4/14/11
					if(!in_array($posted_grp, $ex_grps)) {
						$query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
								($posted_grp, 3, '$now', $fac_stat, $fac_id, 'Allocated to Group' , $by)";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				foreach($ex_grps as $existing_grps) { 	//	4/14/11
					if(!in_array($existing_grps, $_POST['frm_group'])) {
						$query  = "DELETE FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type` = 3 AND `group` = $existing_grps AND `resource_id` = {$fac_id}";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
						}
					}
				}				
			
			
			$caption = "<i>" . stripslashes_deep($_POST['frm_name']) . "</i><B>' data has been updated.</B><BR /><BR />";
			}
		}				// end else {}

	if ($_getgoadd == 'true') {
		$frm_opening_hours = base64_encode(serialize($_POST['frm_opening_hours']));
		$by = $_SESSION['user_id'];		//	4/14/11
		$frm_lat = (empty($_POST['frm_lat']))? 'NULL': quote_smart(trim($_POST['frm_lat']));		// 7/22/10
		$frm_lng = (empty($_POST['frm_lng']))? 'NULL': quote_smart(trim($_POST['frm_lng']));		// 7/15/10
		$now = mysql_format_date(time() - (get_variable('delta_mins')*60));							// 6/4/2013
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]facilities` (
			`is_warehouse`, `name`, `street`, `city`, `state`, `handle`, `icon_str`, `boundary`, `description`, `beds_a`, `beds_o`, `beds_info`, `capab`, `status_id`, `status_about`, `contact_name`, `contact_email`, `contact_phone`, `security_contact`, `security_email`, `security_phone`, `opening_hours`, `access_rules`, `security_reqs`, `pager_p`, `pager_s`, `lat`, `lng`, `type`, `user_id`, `notify_mailgroup`, `notify_email`, `notify_when`, `updated` )
			VALUES (" .
				quote_smart(trim($_POST['frm_is_warehouse'])) . "," .
				quote_smart(trim($_POST['frm_name'])) . "," .
				quote_smart(trim($_POST['frm_street'])) . "," .
				quote_smart(trim($_POST['frm_city'])) . "," .
				quote_smart(trim($_POST['frm_state'])) . "," .
				quote_smart(trim($_POST['frm_handle'])) . "," .
				quote_smart(trim($_POST['frm_icon_str'])) . "," .
				quote_smart(trim($_POST['frm_boundary'])) . "," .				
				quote_smart(trim($_POST['frm_descr'])) . "," .
				quote_smart(trim($_POST['frm_beds_a'])) . "," .
				quote_smart(trim($_POST['frm_beds_o'])) . "," .
				quote_smart(trim($_POST['frm_beds_info'])) . "," .
				quote_smart(trim($_POST['frm_capab'])) . "," .
				quote_smart(trim($_POST['frm_status_id'])) . "," .
				quote_smart(trim($_POST['frm_status_about'])) . "," .
				quote_smart(trim($_POST['frm_contact_name'])) . "," .
				quote_smart(trim($_POST['frm_contact_email'])) . "," .
				quote_smart(trim($_POST['frm_contact_phone'])) . "," .
				quote_smart(trim($_POST['frm_security_contact'])) . "," .
				quote_smart(trim($_POST['frm_security_email'])) . "," .
				quote_smart(trim($_POST['frm_security_phone'])) . "," .
				quote_smart($frm_opening_hours) . "," .
				quote_smart(trim($_POST['frm_access_rules'])) . "," .
				quote_smart(trim($_POST['frm_security_reqs'])) . "," .
				quote_smart(trim($_POST['frm_pager_p'])) . "," .
				quote_smart(trim($_POST['frm_pager_s'])) . "," .
				$frm_lat . "," .
				$frm_lng . "," .
				quote_smart(trim($_POST['frm_type'])) . "," .
				quote_smart(trim($_SESSION['user_id'])) . "," .
				quote_smart(trim($_POST['frm_notify_mailgroup'])) . "," .
				quote_smart(trim($_POST['frm_notify_email'])) . "," .
				quote_smart(trim($_POST['frm_notify_when'])) . "," .
				quote_smart(trim($now)) . ");";	//	8/28/13

		$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
		$new_id=mysql_insert_id();

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
			
// End of file upload

		$status_id = $_POST['frm_status_id'];	//4/14/11
		foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
		if(test_allocates($new_id, $grp_val, 3))	{		
			$query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES 
					($grp_val, 3, '$now', $status_id, $new_id, 'Allocated to Group' , $by)";
			$result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
			}
		}
		
		do_log($GLOBALS['LOG_FACILITY_ADD'], 0, mysql_insert_id(), $_POST['frm_status_id']);	//	2/17/11

		$caption = "<B>Facility  <i>" . stripslashes_deep($_POST['frm_name']) . "</i> data has been updated.</B><BR /><BR />";

		finished ($caption);		// wrap it up
		}							// end if ($_getgoadd == 'true')

// add ===========================================================================================================================
// add ===========================================================================================================================
// add ===========================================================================================================================

	if ($_getadd == 'true') {
		require_once('./incs/links.inc.php');
		if(((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) || get_variable('local_maps') == "1") {
			require_once('./forms/facilities_add_screen.php');
			} else {
			require_once('./forms/facilities_add_screen_NM.php');
			}
		exit();
		}		// end if ($_GET['add'])

// edit =================================================================================================================
// edit =================================================================================================================
// edit =================================================================================================================

	if ($_getedit == 'true') {
		require_once('./incs/links.inc.php');
		if(((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) || get_variable('local_maps') == "1") {
			require_once('./forms/facilities_edit_screen.php');
			} else {
			require_once('./forms/facilities_edit_screen_NM.php');
			}
		}		// end if ($_GET['edit'])
		
// view =================================================================================================================
// view =================================================================================================================
// view =================================================================================================================

	if ($_getview == 'true') {
		require_once('./incs/links.inc.php');
		if(((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) || get_variable('local_maps') == "1") {
			require_once('./forms/facilities_view_screen.php');
			} else {
			require_once('./forms/facilities_view_screen_NM.php');
			}
		}
	
// ============================================= initial display =======================
		if (!isset($mapmode)) {$mapmode="a";}
		require_once('./incs/links.inc.php');
		if(((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) || get_variable('local_maps') == "1") {
			require_once('./forms/facilities_screen.php');
			} else {
			require_once('./forms/facilities_screen_NM.php');
			}
		exit();
?>