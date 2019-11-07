<?php
/*
9/10/13 - Major re-write to previous versions
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
session_write_close();
$logged_in = $logged_out = false;
if (empty($_SESSION)) {
	$logged_out = true;
	header("Location: index.php");
	} else {
	$logged_in = true;
	}
require_once './incs/functions.inc.php';
do_login(basename(__FILE__));
$sess_id = $_SESSION['id'];
$theFacility = get_user_facility($_SESSION['user_id']);
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $theFacility . " LIMIT 1";
$result = mysql_query($query);	
$row = stripslashes_deep(mysql_fetch_assoc($result));
$isWarehouse = $row['is_warehouse'];
$facName = $row['name'];
$beds_a = ($row['beds_a'] != "") ? $row['beds_a'] : 0;
$beds_o = ($row['beds_o'] != "") ? $row['beds_o'] : 0;
$beds_information = $row['beds_info'];

$stock_items = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]stock`";
$result = mysql_query($query);
if(mysql_num_rows($result) == 0) {
	$num_stock = 0;
	} else {
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$stock_items[$row['id']] = $row['name'];
		}
	}

function get_user_name($the_id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $row['user'];
		}
	return $the_ret;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - <?php print get_text('Facility');?> <?php print get_text('Portal');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
	 <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<STYLE>
	.text-labels {font-size: 2em; font-weight: 700;}
	.plain_listheader 	{color:#000000; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.listRow 	{color:#000000; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; text-decoration: none; background-color: #DEE3E7; font-weight: bolder; cursor: pointer; height: 100px;}
	.listEntry 	{text-align: left; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; color: inherit; border: 1px solid #606060; text-decoration: none; background-color: inherit; font-weight: bolder; cursor: pointer; font-size: 1.2em;}
	.noentries_listRow 	{color:#FFFFFF; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; border: 1px solid #606060; text-decoration: none; background-color: green; font-weight: bolder; height: 50px; cursor: default;}
	.noentries 	{text-align: center; word-wrap: break-word; word-break: break-all; white-space: -moz-pre-wrap; color:#FFFFFF; border: 1px solid #606060; text-decoration: none; background-color: green; font-weight: bolder; font-size: 1.2em; cursor: default;}
	.btn_chkd 		{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF; border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center;} 
	.btn_not_chkd 	{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7; border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center;} 
	.btn_hover	 	{ height: 50px; color:#050; font: bold 16px 'trebuchet ms',helvetica,sans-serif; background-color:#DEDEDE; border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center;} 
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<script src="./js/proj4js.js"></script>
<script src="./js/proj4-compressed.js"></script>
<script src="./js/leaflet/leaflet.js"></script>
<script src="./js/leaflet/leaflet-routing-machine.js"></script>
<script src="./js/proj4leaflet.js"></script>
<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
<script type="application/x-javascript" src="./js/osopenspace.js"></script>
<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
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
<script type="application/x-javascript" src="./js/L.Graticule.js"></script>
<script type="application/x-javascript" src="./js/leaflet-providers.js"></script>
<script type="application/x-javascript" src="./js/usng.js"></script>
<script type="application/x-javascript" src="./js/osgb.js"></script>
<script type="application/x-javascript" src="./js/geotools2.js"></script>
<script type="application/x-javascript" src="./js/osm_map_functions.js"></script>

<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var doDebug = true;
var changed_showhide = true;
var changed_mkrshowhide = true;
var randomnumber;
var viewportwidth;
var viewportheight;
var the_string;
var theClass = "background-color: #CECECE";
var reqFin = false;
var outerWidth = 0;
var outerHeight = 0;
var listWidth = 0;
var listHeight = 0;
var cellwidth = 0;
var colors = new Array ('odd', 'even');
var requeststimer = null;
var stocktimer = null;
var textDirections;
var showall = "no";
var isWarehouse = <?php print $isWarehouse;?>;

window.onresize=function(){set_size();}

function pad(width, string, padding) { 
	return (width <= string.length) ? string : pad(width, string + padding, padding)
	}

function getHeaderHeight(element) {
	return element.clientHeight;
	}
	
function trimstring (str) {
	return str.replace(/^[\s(&nbsp;)]+/g,'').replace(/[\s(&nbsp;)]+$/g,'');
	}
	
function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	outerWidth = viewportwidth * .98;
	outerHeight = viewportheight * .99;
	listWidth = outerWidth * .99;	
	listHeight = outerHeight * .99;
	cellwidth = listWidth / 9;
	if($('outer')) {$('outer').style.width = outerWidth + "px"; $('outer').style.height = outerHeight + "px";}
	if($('requests_list')) {$('requests_list').style.maxHeight = listHeight + "px"; $('requests_list').style.width = listWidth + "px";}
	if($('beds')) {$('beds').style.width = listWidth + "px";}
	if($('stock')) {$('stock').style.width = listWidth + "px";}
	loadIt();
	}

function loadIt() {								// set cycle
	if(isWarehouse == 0) {
		$('requests_list').style.display = "block";
		$('facility_stocklist').style.display = "none";
		$('stockitemslist').style.display = "none";
		get_requests();
		} else {
		$('requests_list').style.display = "none";
		$('facility_stocklist').style.display = "block";
		$('stockitemslist').style.display = "none";
		get_stock();
		}
	}
	
function requestsLoop() {
	if (requeststimer != null) {return;}
	requeststimer = window.setInterval('get_requests()', 30000);	
	}
	
function stockLoop() {
	if (stocktimer != null) {return;}
	stocktimer = window.setInterval('get_stock()', 30000);	
	}

function out_frames() {		//  onLoad = "out_frames()"
	if (top.location != location) {
		top.location.href = document.location.href;
		location.href = '#top'; 
		} else {
		location.href = '#top';
		}
	}		// end function out_frames()
	
function go_there (where, the_id) {		//
	document.go.action = where;
	document.go.submit();
	}				// end function go there ()	

function setDirections(fromLat, fromLng, toLat, toLng, theDiv) {
	if(fromLat == "" || fromLng == "" || toLat == "" || toLng == "") {return;}
	window.theDirections = L.Routing.control({
		waypoints: [
			L.latLng(fromLat,fromLng),
			L.latLng(toLat,toLng)
		]});
	window.theDirections.on('routingerror', function(o) { console.log(o); });
	setTimeout(function() {
		theETA = Math.round(window.totTime / 60) + " Minutes<BR /><BR /><SPAN style='color: red; width: 80%; display: inline-block;'>Approximate based on tracking data</SPAN>";
		$(theDiv).innerHTML = theETA;
		},1000);
	}
	
function logged_in() {								// returns boolean
	var temp = <?php print $logged_in;?>;
	return temp;
	}	
	
function isNull(val) {								// checks var stuff = null;
	return val === null;
	}

dbfns = new Array ();					//  field names per assigns_t.php expectations
dbfns['c'] = 'frm_clear';
dbfns['a'] = 'frm_u2farr';

function set_assign(which, theAssign, theTicket, theUnit, btn) {
	if (!(parseInt(theAssign)) > 0) {return;}
	var currTxt = $(btn).innerHTML;
	var params = "frm_id=" + theAssign;
	params += "&frm_tick=" + theTicket;
	params += "&frm_unit=" + theUnit;
	params += "&frm_vals=" + dbfns[which];
	sendRequest ('assigns_t.php',handleResult, params);			// does the work
	var curr_time = do_time();
	replaceButtonText(btn, currTxt + " @ " + curr_time)
	CngClass(btn, 'btn_chkd');
	}		// end function set_assign()
	
function set_button(btn,theTime) {
	var currTxt = $(btn).innerHTML;
	if(the_time != "") {
		replaceButtonText(btn, currTxt + " @ " + theTime)
		CngClass(btn, 'btn_chkd');
		}
	}

function handleResult(req) {			// the called-back function
	}			// end function handle Result()

function replaceButtonText(buttonId, text) {
	if (document.getElementById) {
		var button=document.getElementById(buttonId);
		if (button) {
			if (button.childNodes[0]) {
				button.childNodes[0].nodeValue=text;
				}
			else if (button.value) {
				button.value=text;
				}
			else {					//if (button.innerHTML) 
				button.innerHTML=text;
				}
			}
		}
	}		// end function replaceButtonText()
	
var newwindow = null;
var starting;
function do_window(id) {				// 1/19/09
	if ((newwindow) && (!(newwindow.closed))) {newwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		var url = "./add_facnote.php?ticket_id=" + id;
		newwindow=window.open(url, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(newwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow.focus();
		starting = false;
		}
	}		// end function do_window()
	
var catwindow = null;
function do_catwindow() {				// 1/19/09
	if ((catwindow) && (!(catwindow.closed))) {catwindow.focus(); return;}		// 7/28/10	
	if (logged_in()) {
		if(starting) {return;}						// 6/6/08
		starting=true;	
		var url = "./faccategories.php";
		catwindow=window.open(url, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
		if (isNull(catwindow)) {
			alert ("Portal operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		catwindow.focus();
		starting = false;
		}
	}		// end function do_window()
	
function do_showall() {
	if(window.showall == "no") {
		window.showall = "yes";
		$('showall_but').innerHTML = "Hide Cleared";
		} else {
		window.showall = "no";
		$('showall_but').innerHTML = "Show Cleared";		
		}
	loadIt();
	}
	
function get_stock_x_fac_form(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/stock_x_fac_form.php?id=" + id + "&version=" + randomnumber+"&q="+sessID+"&showall="+window.showall;
	sendRequest (url,sxf_form_cb, "");
	function sxf_form_cb(req) {
		var theForm = JSON.decode(req.responseText);
		$('edit_facility_stock').innerHTML = theForm[0];
		}
	}

function get_requests() {
	$('list_header').innerHTML = "Current Jobs";
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/facboard_incidents.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber+"&q="+sessID+"&showall="+window.showall;
	sendRequest (url,requestlist_cb, "");
	function requestlist_cb(req) {
		var i = 1;
 		var the_requests = JSON.decode(req.responseText);
		var theCount;
		var outputtext = "<TABLE id='requeststable' class='fixedheadscrolling scrollable' style='width: " + window.listWidth + "px; maxHeight: " + window.listHeight + "px;'>";
		outputtext += "<thead>";
		outputtext += "<TR class='plain_listheader' style='width: " + window.listWidth + "px;'>";
		outputtext += "<TH class='plain_listheader'>Incident Type</TH>";
		outputtext += "<TH class='plain_listheader'>Origin</TH>";
		outputtext += "<TH class='plain_listheader'>Destination</TH>";
		outputtext += "<TH class='plain_listheader'>Type</TH>";
		outputtext += "<TH class='plain_listheader'>Num Patients</TH>";
		outputtext += "<TH class='plain_listheader'>Notes</TH>";
		outputtext += "<TH class='plain_listheader'>Patient Name</TH>";
		outputtext += "<TH class='plain_listheader'>ETA</TH>";
		outputtext += "<TH class='plain_listheader'>Status</TH>";
		outputtext += "</TR>";
		outputtext += "</thead>";
		outputtext += "<tbody>";
		if(the_requests[0][0] == 0) {
			outputtext += "<TR class='noentries_listRow' style='width: " + window.listwidth + "px;'><TD class='noentries' COLSPAN=99>Nothing Current</TD></TR>";
			theCount = 0;
			} else {
			theCount = the_requests.length;
			for (var key = 0; key < the_requests.length; key++) {
				if(the_requests[key][13] == 0) {
				outputtext += "<TR class='listRow' style='background-color: yellow; width: " + window.listWidth + "px;'>";
				} else {
				outputtext += "<TR class='listRow " + colors[i%2] + "' style='width: " + window.listWidth + "px;'>";
				}
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][2] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][5] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][6] + "</TD>";
				outputtext += "<TD class='listEntry' style='color: " + the_requests[key][11] + "; background-color: " + the_requests[key][12] + ";' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][7] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][3] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][10] + "</TD>";
				outputtext += "<TD class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][8] + "</TD>";
				outputtext += "<TD ID='eta_" + the_requests[key][4] + "' class='listEntry' onClick='do_window(" + the_requests[key][4] + ");'>" + the_requests[key][9] + "</TD>";
				var btnID1 = "arrbtn" + the_requests[key][13];
				var btnID2 = "clrbtn" + the_requests[key][13];
				if(the_requests[key][15] != "") {
					var txt1 = "Arrived @ " + the_requests[key][15];
					var class1 = "btn_chkd";
					} else {
					var txt1 = "Arrive";
					var class1 = "btn_not_chkd";
					var mouseon1 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff1 = "onMouseout='do_btn_plain(this.id);'";
					}
				if(the_requests[key][16] != "") {
					var txt2 = "Clear @ " + the_requests[key][15];
					var class2 = "btn_chkd"
					} else {
					var txt2 = "Clear";
					var class2 = "btn_not_chkd";
					var mouseon2 = "onMouseover='do_btn_hover(this.id);'";
					var mouseoff2 = "onMouseout='do_btn_plain(this.id);'";
					}
				outputtext += "<TD class='listEntry'><SPAN id='" + btnID1 + "' class='" + class1 + "' style='width: 100%; display: inline-block;' " + mouseon1 + " " + mouseoff1 + " onClick=\"set_assign('a'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID1 + "');\">" + txt1 + "</SPAN><SPAN id='" + btnID2 + "' class='" + class2 + "' style='width: 100%; display: inline-block;' " + mouseon2 + " " + mouseoff2 + " onClick=\"set_assign('c'," + the_requests[key][13] + "," + the_requests[key][4] + "," + the_requests[key][14] + ",'" + btnID2 + "');\">" + txt2 + "</SPAN></TD>";
				outputtext += "</TR>";
				setDirections(the_requests[key][17], the_requests[key][18], the_requests[key][19], the_requests[key][20], "eta_" +  the_requests[key][4]);
				i++;
				}
			}
		outputtext += "</tbody>";
		outputtext += "</TABLE>";
		$('all_requests').innerHTML = outputtext;
		var theWidth = cellwidth + "px";
		var reqtbl = document.getElementById('requeststable');
		if(theCount == 0) {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				}
			} else {
			if(reqtbl) {
				var headerRow = reqtbl.rows[0];
				var tableRow = reqtbl.rows[1];
				for (var j = 0; j < headerRow.cells.length; j++) {
					headerRow.cells[j].style.width = theWidth;
					}
				for (var k = 0; k < tableRow.cells.length; k++) {
					tableRow.cells[k].style.width = theWidth;
					}
				if(getHeaderHeight(headerRow) >= listheader_height) {
					var theRow = inctbl.insertRow(1);
					theRow.style.height = "20px";
					for (var i = 0; i < tableRow.cells.length; i++) {
						var theCell = theRow.insertCell(i);
						theCell.innerHTML = " ";
						}
					}
				}
			}			//	end if theCount == 0
		requestsLoop();
		}				// end function requestlist_cb()
	}				// end function get_requests()
	
function get_stock() {
	$('facstocklist_header').innerHTML = "Current Stock Levels and Locations";
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/facboard_stocklist.php?facility_id=<?php print $theFacility;?>&version=" + randomnumber+"&q="+sessID;
	sendRequest (url,stocklist_cb, "");
	function stocklist_cb(req) {
		var i = 1;
		var stocklist = JSON.decode(req.responseText);
		var theCount;
		if(stocklist[0][0] == 0) {
			outputtext = "<SPAN class='text_large text_bold' style='width: " + window.listwidth + "px;'>No Current Stock</SPAN>";
			theCount = 0;
			} else {
			theCount = stocklist.length;
			var i = 1;
			var stocklist = JSON.decode(req.responseText);
			var theCount;
			var outputtext = "<TABLE id='stock_table' class='fixedheadscrolling scrollable' style='width: " + window.listWidth + "px; maxHeight: " + window.listHeight + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR class='plain_listheader text text_left' style='width: " + window.listWidth + "px;'>";
			outputtext += "<TH class='plain_listheader text text_left'>Stock Item</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Stock Level</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Reorder Level</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>On Order</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Location</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for (var key = 0; key < stocklist.length; key++) {
				outputtext += "<TR class='" + colors[i%2] + "' style='width: " + window.listWidth + "px;' onClick='edit_stock(" + stocklist[key][0] + ");'>";
				outputtext += "<TD id='st_name_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(30, stocklist[key][1], "\u00a0") + "</TD>";
				outputtext += "<TD id='st_level_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(30, stocklist[key][2], "\u00a0") + "</TD>";
				outputtext += "<TD id='st_reorder_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(30, stocklist[key][3], "\u00a0") + "</TD>";
				outputtext += "<TD id='st_onorder_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(30, stocklist[key][4], "\u00a0") + "</TD>";
				outputtext += "<TD id='st_location_" + stocklist[key][0] + "' class='plain_list text text_left'>" + pad(30, stocklist[key][5], "\u00a0") + "</TD>";
				outputtext += "</TR>";
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			}
		$('fac_stocklist').innerHTML = outputtext;
		var fstocktbl = document.getElementById('stock_table');
		if(fstocktbl) {
			var headerRow = fstocktbl.rows[0];
			var viewableRow = 1;
			var headerRow = fstocktbl.rows[0];
			for (i = 1; i < fstocktbl.rows.length; i++) {
				if(!isViewable(fstocktbl.rows[i])) {
					} else {
					viewableRow = i;
					break;
					}
				}
			var tableRow = fstocktbl.rows[viewableRow];
			if(tableRow && i != fstocktbl.rows.length) {
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
		stockLoop();
		}				// end function stocklist_cb()
	}				// end function get_stock()
	
function get_stock_items() {
	$('stocklist_header').innerHTML = "Master Stock Items";
	$('stocklist_header').innerHTML += "<SPAN ID='new_s_but' CLASS='plain text' style='float: right; vertical-align: middle;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='do_new();'><?php print get_text('New Stock Item');?></SPAN>";
	var randomnumber=Math.floor(Math.random()*99999999);
	var sessID = "<?php print $_SESSION['id'];?>";
	var url = "./ajax/facboard_stockitems.php?version=" + randomnumber+"&q="+sessID;
	sendRequest (url,stockitems_cb, "");
	function stockitems_cb(req) {
		var i = 1;
 		var stockitems = JSON.decode(req.responseText);
		var theCount;
		if(stockitems[0][0] == 0) {
			outputtext = "<SPAN class='text_large text_bold' style='text-align: center; width: " + window.listwidth + "px; display: block;'>No Current Stock Items</SPAN>";
			theCount = 0;
			} else {
			theCount = stockitems.length;
			var outputtext = "<TABLE id='stockitemstable' class='fixedheadscrolling scrollable' style='width: 100%; maxHeight: " + window.listHeight + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR class='plain_listheader text text_left' style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text text_left'>Name</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Description</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Order Quantity</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Pack Size</TH>";
			outputtext += "<TH class='plain_listheader text text_left'>Re-order Level</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for (var key = 0; key < stockitems.length; key++) {
				outputtext += "<TR class='" + colors[i%2] + "' style='width: " + window.listWidth + "px;' onClick='edit_stock_item(" + stockitems[key][1] + ");'>";
				outputtext += "<TD id='name_" + stockitems[key][1] + "' class='plain_list text text_left'>" + pad(30, stockitems[key][2], "\u00a0") + "</TD>";
				outputtext += "<TD id='description_" + stockitems[key][1] + "' class='plain_list text text_left'>" + pad(30, stockitems[key][3], "\u00a0") + "</TD>";
				outputtext += "<TD id='orderq_" + stockitems[key][1] + "' class='plain_list text text_left'>" + pad(30, stockitems[key][4], "\u00a0") + "</TD>";
				outputtext += "<TD id='packs_" + stockitems[key][1] + "' class='plain_list text text_left'>" + pad(30, stockitems[key][5], "\u00a0") + "</TD>";
				outputtext += "<TD id='reorder_" + stockitems[key][1] + "' class='plain_list text text_left'>" + pad(30, stockitems[key][6], "\u00a0") + "</TD>";
				outputtext += "</TR>";
				i++;
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			}
		$('stocklist').innerHTML = outputtext;
		var stocktbl = document.getElementById('stockitemstable');
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
		}				// end function stockitems_cb()
	}				// end function get_stock_items()
	
function sendajax(myForm, task) {
	var action = myForm.getAttribute("action"), //Getting Form Action URL
	method = myForm.getAttribute("method"); //Getting Form Submit Method (Post/Get)
	var data = new FormData(myForm);
	var http = new XMLHttpRequest();
	http.open(method,action,true);
	http.onload = function() {
		if (http.status == 200) {
			if(task == 0) {
				var bedsresp = JSON.decode(this.responseText);
				document.frm_beds_a.value = bedsresp[0];
				document.frm_beds_o.value = bedsresp[1];
				document.frm_beds_info.value = bedsresp[2];
				$('notices').style.display = "block";
				$('notices').innerHTML = "Updated Beds information";
				setTimeout(function() {
					$('notices').style.display = "none";
					}, 2000)
				} else {
				$('notices').style.display = "block";
				$('notices').innerHTML = this.responseText;
				get_stock();
				if($('stockitemslist').style.display == "block") {
					get_stock_items();
					}
				setTimeout(function() {
					$('notices').style.display = "none";
					}, 3000)
				}
			}
		};
	http.send(data);
	if($('stock_edit')) {$('stock_edit').style.display = "none";}
	if($('edit_stock')) {$('edit_stock').style.display = "none";}
	if($('new_stock')) {$('new_stock').style.display = "none";}
	}
	
function do_beds() {
	sendajax(document.frm_beds, 0);
	}
	
function do_stock() {
	sendajax(document.frm_stock, 1);
	}
	
function do_edit_stock() {
	sendajax(document.stock_edit_Form, 1);
	}
	
function do_new_stock() {
	sendajax(document.new_stock_Form, 1);
	}
	
function do_stockitem_edit() {
	sendajax(document.edit_stock_Form, 1);
	}
	
function edit_stock(stockid) {
	var nameElem = "st_name_" + stockid;
	var levelElem = "st_level_" + stockid;
	var locationElem = "st_location_" + stockid;
	var onorderElem = "st_onorder_" + stockid;
	var theName = trimstring($(nameElem).innerHTML);
	var theLevel = trimstring($(levelElem).innerHTML);
	var theLocation = trimstring($(locationElem).innerHTML);
	var theOnorder = trimstring($(onorderElem).innerHTML);
	$('stock_edit').style.display = "block";
	if($('edit_stock').style.display == "block") {$('edit_stock').style.display = "none";}
	if($('new_stock').style.display == "block") {$('new_stock').style.display = "none";}
	document.stock_edit_Form.frm_stock_item.value = theName;
	document.stock_edit_Form.frm_stock_level.value = theLevel;
	document.stock_edit_Form.frm_stock_location.value = theLocation;
	document.stock_edit_Form.frm_on_order.value = theOnorder;
	document.stock_edit_Form.frm_stock_id.value = stockid;
	}

function show_stockitems() {
	if($('stockitemslist').style.display == "block") {
		$('stockitemslist').style.display = "none";
		$('show_stockitems_but').innerHTML = "<?php print get_text('Show Stock Items');?>";
		} else {
		$('stockitemslist').style.display = "block";
		$('show_stockitems_but').innerHTML = "<?php print get_text('Hide Stock Items');?>";
		get_stock_items();
		}
	}

function edit_stock_item(stockid) {
	var nameElem = "name_" + stockid;
	var descElem = "description_" + stockid;
	var orderqElem = "orderq_" + stockid;
	var packsElem = "packs_" + stockid;
	var reorderElem = "reorder_" + stockid;
	var theName = trimstring($(nameElem).innerHTML);
	var theDescription = trimstring($(descElem).innerHTML);
	var theOrderq = trimstring($(orderqElem).innerHTML);
	var thePacks = trimstring($(packsElem).innerHTML);
	var theReorder = trimstring($(reorderElem).innerHTML);
	$('edit_stock').style.display = "block";
	if($('stock_edit').style.display == "block") {$('stock_edit').style.display = "none";}
	if($('new_stock').style.display == "block") {$('new_stock').style.display = "none";}
	document.edit_stock_Form.frm_name.value = theName;
	document.edit_stock_Form.frm_description.value = theDescription;
	document.edit_stock_Form.frm_stock_order_size.value = theOrderq;
	document.edit_stock_Form.frm_pack_size.value = thePacks;
	document.edit_stock_Form.frm_reorder_level.value = theReorder;
	document.edit_stock_Form.frm_id.value = stockid;
	}
	
function do_new() {
	document.new_stock_Form.frm_name.value = "";
	document.new_stock_Form.frm_description.value = "";
	document.new_stock_Form.frm_stock_order_size.value = "";
	document.new_stock_Form.frm_pack_size.value = "";
	document.new_stock_Form.frm_reorder_level.value = "";
	$('new_stock').style.display = "block";
	if($('stock_edit').style.display == "block") {$('stock_edit').style.display = "none";}
	if($('edit_stock').style.display == "block") {$('edit_stock').style.display = "none";}
	}

function do_logout() {
	document.gout_form.submit();
	}	
		
function do_unload() {
	}
</SCRIPT>
</HEAD>
<?php


if((!isset($_SESSION)) && (empty($_POST))) {
	print "Not Logged in";
} elseif((isset($_SESSION)) && (empty($_POST))) {
	$now = time() - (intval(get_variable('delta_mins')*60));
?>

	<BODY style='overflow: hidden;' onLoad="out_frames();" onUnload='do_unload();'>
		<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<DIV id='outer' style='position: absolute; top: 0px; left: 1%; text-align: center; height: 100%; display: block;'>
			<DIV CLASS='header' style = "height: auto; width: 100%; float: none; text-align: center; display: block;"><BR />
				<SPAN class='header text_massive' style='vertical-align: middle; cursor: default; width: 96%;'>Tickets <?php print get_text('Facility');?> <?php print get_text('Portal');?> for <?php print $facName;?>
					<SPAN class='text' style='padding-right: 2%; float: right;'>
						<SPAN ID='gout' CLASS='plain text' style='float: right; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_logout()"><?php print get_text('Logout');?></SPAN>
<?php
						if($isWarehouse == 0) {
?>
							<SPAN ID='cats_but' CLASS='plain text' style='float: right; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_catwindow()"><?php print get_text('Categories');?></SPAN>
							<SPAN ID='showall_but' CLASS='plain text' style='float: right; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_showall()">Show <?php print get_text('Cleared');?></SPAN>
<?php
							} else {
?>
							<SPAN ID='show_stockitems_but' CLASS='plain text' style='float: right; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="show_stockitems()"><?php print get_text('Show Stock Items');?></SPAN>
<?php
							}
?>
					</SPAN>
				</SPAN><BR /><BR />
			</DIV>
			<BR />
			<DIV id='requests_list' style='display: block; cursor: default;'>
				<DIV id='list_header' class='header text_large' style='vertical-align: middle; height: 22px; background-color: #707070;'></DIV>
				<DIV class="scrollableContainer">
					<DIV class="scrollingArea" id='all_requests'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
				</DIV>
			</DIV>
			<DIV id='facility_stocklist' style='display: block; cursor: default;'>
				<DIV id='facstocklist_header' class='header text_large' style='vertical-align: middle; height: 22px; background-color: #707070;'></DIV>
				<DIV class="scrollableContainer">
					<DIV class="scrollingArea" id='fac_stocklist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
				</DIV>
			</DIV><BR />
<?php
			if($isWarehouse == 0) {
?>
				<DIV id='beds' style='position: absolute; bottom: 1%; display: block; cursor: default; width: 98%;'>
					<FORM NAME='frm_beds' METHOD='post' ACTION='./ajax/facboard_form_submit.php?table=facilities&func=beds&q=". $sess_id . "'>
					<FIELDSET style='width: 98%;'>
						<LEGEND class='text_large text_bold'>Beds</LEGEND>
						<LABEL for="beds_a" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Beds Available');"><?php print get_text("Beds Available");?>:</LABEL>
						<INPUT id='beds_a' NAME="frm_beds_a" tabindex=1 SIZE="4" TYPE="text" VALUE="<?php print $beds_a;?>" MAXLENGTH="4" />
						<LABEL for="beds_o" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Beds Occupied');"><?php print get_text("Beds Occupied");?>:</LABEL>
						<INPUT id='beds_o' NAME="frm_beds_o" tabindex=1 SIZE="4" TYPE="text" VALUE="<?php print $beds_o;?>" MAXLENGTH="4" />
						<LABEL for="beds_info" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Beds Information');"><?php print get_text("Beds Information");?>:</LABEL>
						<INPUT id='beds_info' NAME="frm_beds_info" tabindex=1 SIZE="48" TYPE="text" VALUE="<?php print $beds_information;?>" MAXLENGTH="2048" />
						<SPAN id='sub_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_beds();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
						<INPUT TYPE='hidden' NAME = 'frm_facility_id' VALUE = '<?php print $theFacility;?>' />
					</FIELDSET>
					</FORM>
				</DIV>
<?php
				} else {
				$query_stock = "SELECT * FROM `$GLOBALS[mysql_prefix]stock` ORDER BY `name` ASC";
				$result_stock = mysql_query($query_stock);
				if(mysql_num_rows($result_stock) > 0) {
					$stock = "<SELECT ID='stock_item' NAME='frm_stock_item'>\n";
					$stock .= "<OPTION VALUE = 0 SELECTED>Select Item</OPTION>\n";
					while ($row_stock = mysql_fetch_array($result_stock, MYSQL_ASSOC)) {
						$stock .= "<OPTION VALUE=" . $row_stock['id'] . ">" . $row_stock['name'] . "</OPTION>\n";
						}
					$stock .= "</SELECT>\n";
					} else {
					$stock = "No items configured!";
					}
?>
				<DIV id='stock' style='position: absolute; bottom: 1%; display: block; cursor: default; width: 98%;'>
					<FORM NAME='frm_stock' METHOD='post' ACTION='./ajax/facboard_form_submit.php?table=facilities&func=new_stock&q=". $sess_id . "'>
					<FIELDSET style='width: 98%;'>
						<LEGEND class='text_large text_bold'>New Stock</LEGEND>
						<LABEL for="stock_item" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Warehouse stock items');"><?php print get_text("Item");?>:</LABEL>
						<?php print $stock;?>
						<LABEL for="stock_level" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Stock Level");?>:</LABEL>
						<INPUT id='stock_level' NAME="frm_stock_level" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="4" />
						<LABEL for="on_order" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('No of items of this type on order from manufacturer / supplier');"><?php print get_text("On Order");?>:</LABEL>
						<INPUT id='on_order' NAME="frm_on_order" tabindex=3 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="10" />
						<LABEL for="stock_location" style='width: auto; display: inline; vertical-align: middle;' onmouseout="UnTip()" onmouseover="Tip('Where located in warehouse');"><?php print get_text("Location");?>:</LABEL>
						<INPUT id='stock_location' NAME="frm_stock_location" tabindex=5 SIZE="48" TYPE="text" VALUE="" MAXLENGTH="2048" />
						<SPAN id='sub_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_stock();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
						<INPUT TYPE='hidden' NAME = 'frm_facility_id' VALUE = '<?php print $theFacility;?>' />
					</FIELDSET>
					</FORM>
				</DIV>
<?php
				}
?>
		</DIV>
		<DIV id='stock_edit' style='position: absolute; top: 30%; left: 30%; display: none; cursor: default; height: 40%; width: 40%;'>
			<FORM NAME='stock_edit_Form' METHOD='post' ACTION='./ajax/facboard_form_submit.php?table=facilities&func=stock_update&q=". $sess_id . "'>
			<FIELDSET style='width: 98%;'>
				<LEGEND class='text_large text_bold'>Edit Stock</LEGEND>
				<LABEL for="stock_item" onmouseout="UnTip()" onmouseover="Tip('Warehouse stock items');"><?php print get_text("Item");?>:</LABEL>
				<INPUT id='stock_item' NAME="frm_stock_item" tabindex=1 SIZE="24" TYPE="text" VALUE="" MAXLENGTH="48" READONLY='readonly'/>
				<BR />
				<LABEL for="stock_level" onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Stock Level");?>:</LABEL>
				<INPUT id='stock_level' NAME="frm_stock_level" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="4" />
				<BR />
				<LABEL for="stock_location" onmouseout="UnTip()" onmouseover="Tip('Where located in warehouse');"><?php print get_text("Location");?>:</LABEL>
				<INPUT id='stock_location' NAME="frm_stock_location" tabindex=1 SIZE="48" TYPE="text" VALUE="" MAXLENGTH="2048" />
				<BR />
				<LABEL for="on_order" onmouseout="UnTip()" onmouseover="Tip('No of items of this type on order from manufacturer / supplier');"><?php print get_text("On Order");?>:</LABEL>
				<INPUT id='on_order' NAME="frm_on_order" tabindex=4 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="10" />
				<BR />
				<BR />
				<CENTER>
				<SPAN id='sub1_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_edit_stock();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='can1_but' roll='button' aria-label='Cancel' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('stock_edit').style.display = 'none';"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</CENTER>
				<INPUT TYPE='hidden' NAME = 'frm_facility_id' VALUE = '<?php print $theFacility;?>' />
				<INPUT TYPE='hidden' NAME = 'frm_stock_id' VALUE = '' />
			</FIELDSET>
			</FORM>		
		</DIV>
		<DIV id='new_stock' style='position: absolute; top: 30%; left: 30%; display: none; cursor: default; height: 40%; width: 40%; z-index: 99999;'>
			<FORM NAME='new_stock_Form' METHOD='post' ACTION='./ajax/facboard_form_submit.php?table=facilities&func=new_stock_item&q=<?php print $sess_id;?>'>
			<FIELDSET style='width: 98%;'>
				<LEGEND class='text_large text_bold'>New Stock Item</LEGEND>
				<LABEL for="stock_name" onmouseout="UnTip()" onmouseover="Tip('Warehouse stock items');"><?php print get_text("Item");?>:</LABEL>
				<INPUT id='stock_name' NAME="frm_name" tabindex=1 SIZE="24" TYPE="text" VALUE="" MAXLENGTH="48" />
				<BR />
				<LABEL for="stock_description" onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Description");?>:</LABEL>
				<INPUT id='stock_description' NAME="frm_description" tabindex=1 SIZE="48" TYPE="text" VALUE="" MAXLENGTH="512" />
				<BR />
				<LABEL for="stock_order_size" onmouseout="UnTip()" onmouseover="Tip('What multiples of items need to be ordered');"><?php print get_text("Order Size");?>:</LABEL>
				<INPUT id='stock_order_size' NAME="frm_stock_order_size" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<LABEL for="pack_size" onmouseout="UnTip()" onmouseover="Tip('Pack size item is delivered in');"><?php print get_text("Pack Size");?>:</LABEL>
				<INPUT id='pack_size' NAME="frm_pack_size" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<LABEL for="reorder_level" onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Re-order Level");?>:</LABEL>
				<INPUT id='reorder_level' NAME="frm_reorder_level" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<BR />
				<CENTER>
				<SPAN id='sub2_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_new_stock();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='can2_but' roll='button' aria-label='Cancel' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('new_stock').style.display = 'none';"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</CENTER>
			</FIELDSET>
			</FORM>		
		</DIV>
		<DIV id='edit_stock' style='position: absolute; top: 30%; left: 30%; display: none; cursor: default; height: 40%; width: 40%; z-index: 99999;'>
			<FORM NAME='edit_stock_Form' METHOD='post' ACTION='./ajax/facboard_form_submit.php?table=facilities&func=edit_stock_item&q=<?php print $sess_id;?>'>
			<FIELDSET style='width: 98%;'>
				<LEGEND class='text_large text_bold'>Edit Stock Item</LEGEND>
				<LABEL for="stock_name" onmouseout="UnTip()" onmouseover="Tip('Warehouse stock items');"><?php print get_text("Item");?>:</LABEL>
				<INPUT id='stock_name' NAME="frm_name" tabindex=1 SIZE="24" TYPE="text" VALUE="" MAXLENGTH="48" />
				<BR />
				<LABEL for="stock_description" onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Stock Level");?>:</LABEL>
				<INPUT id='stock_description' NAME="frm_description" tabindex=1 SIZE="48" TYPE="text" VALUE="" MAXLENGTH="512" />
				<BR />
				<LABEL for="stock_order_size" onmouseout="UnTip()" onmouseover="Tip('What multiples of items need to be ordered');"><?php print get_text("Order Size");?>:</LABEL>
				<INPUT id='stock_order_size' NAME="frm_stock_order_size" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<LABEL for="pack_size" onmouseout="UnTip()" onmouseover="Tip('Pack size item is delivered in');"><?php print get_text("Pack Size");?>:</LABEL>
				<INPUT id='pack_size' NAME="frm_pack_size" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<LABEL for="reorder_level" onmouseout="UnTip()" onmouseover="Tip('Stock Level');"><?php print get_text("Re-order Level");?>:</LABEL>
				<INPUT id='reorder_level' NAME="frm_reorder_level" tabindex=1 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="8" />
				<BR />
				<BR />
				<CENTER>
				<SPAN id='sub3_but' roll='button' aria-label='Submit' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="do_stockitem_edit();"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='can3_but' roll='button' aria-label='Cancel' CLASS='plain text' style='float: none; width: 80px; display: inline-block; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="$('edit_stock').style.display = 'none';"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</CENTER>
				<INPUT TYPE='hidden' NAME = 'frm_id' VALUE = '' />
			</FIELDSET>
			</FORM>		
		</DIV>
		<DIV id='stockitemslist' style='position: absolute; top: 30%; left: 20%; display: none; cursor: default; width: 60%; border: 20px solid #CECECE;'>
			<DIV id='stocklist_header' class='header text_large' style='width: 100%; vertical-align: middle; height: 30px; background-color: #707070; display: block; text-align: center;'></DIV>
			<DIV class="scrollableContainer" style='width: 100%; display: block;'>
				<DIV class="scrollingArea" id='stocklist'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
		</DIV>
		<DIV id='directions' style='display: none;'></DIV>
		<DIV id='map_canvas' style='display: none;'></DIV>
		<DIV id='notices' class='text_massive' style='display: none; position: absolute; bottom: 20%; width: 100%; text-align: center; background-color: orange; color: blue;'></DIV>
	<SCRIPT>
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	set_fontsizes(viewportwidth, "fullscreen");
	outerWidth = viewportwidth * .98;
	outerHeight = viewportheight * .99;
	listWidth = outerWidth;	
	listHeight = outerHeight * .80;
	cellwidth = listWidth / 9;
	if($('outer')) {$('outer').style.width = outerWidth + "px"; $('outer').style.height = outerHeight + "px";}
	if($('requests_list')) {$('requests_list').style.maxHeight = listHeight + "px"; $('requests_list').style.width = listWidth + "px";}
	if($('beds')) {$('beds').style.width = listWidth + "px";}
	if($('stock')) {$('stock').style.width = listWidth + "px";}
	loadIt();
	var map;				// make globally visible
	var minimap;
	var latLng;
	var in_local_bool = "0";
	var theLocale = <?php print get_variable('locale');?>;
	var useOSMAP = <?php print get_variable('use_osmap');?>;
	init_map(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "br");
	map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 13);
	</SCRIPT>
	<FORM METHOD='POST' NAME="gout_form" action="index.php">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	<FORM NAME="go" action="#" TARGET = "main"></FORM>	
	</BODY>
<?php
	}
?> 

</HTML>
