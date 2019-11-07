<?php

error_reporting(E_ALL);
$units_side_bar_height = .6;
$do_blink = TRUE;
$ld_ticker = "";
$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");
$gt_status = get_text("Status");
$isGuest = (is_guest()) ? 1 : 0;

require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";
$columns_arr = explode(',', get_msg_variable('columns'));
$not_sit = (array_key_exists('id', ($_GET)))?  $_GET['id'] : NULL;
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
$sess_id = $_SESSION['id'];
$curr_cats = get_category_butts();	//	get current categories.
$fac_curr_cats = get_fac_category_butts();
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
$api_key = get_variable('gmaps_api_key');
$key_str = (strlen($api_key) == 39) ? "key={$api_key}&" : false;
$gmaps_ok = ($key_str) ? 1 : 0;
$showmaps = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet'])) ? 1 : 0;
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}

$mapzooms = array();
$dir = '_osm/tiles';
$mapdir = scandir($dir);
foreach($mapdir as $val) {
	if($val <> "." && $val <> "..") {
		if(is_dir('_osm/tiles/' . $val)) {
			$mapzooms[] = intval($val);
			}
		}
	}
if(count($mapzooms) > 0 && get_variable('local_maps') == "1") {$localZoomMin = min($mapzooms); $localZoomMax = max($mapzooms);} else {$localZoomMin = 0; $localZoomMax = 20;}
$setZoom = (get_variable('local_maps') == "1") ? $localZoomMin : get_variable('def_zoom');

	// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Full Screen</TITLE>
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
	<link rel="stylesheet" href="./js/Control.Geocoder.css" />
	<link rel="stylesheet" href="./js/leaflet-openweathermap.css" />
	<style type="text/css">
	table td + td { padding: 3px; border-left:1px solid red; }
	</style>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/messaging.js"></SCRIPT>
<?php 
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}	
?>
	<script type="application/x-javascript" src="./js/proj4js.js"></script>
	<script type="application/x-javascript" src="./js/proj4-compressed.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/leaflet.js"></script>
	<script type="application/x-javascript" src="./js/proj4leaflet.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/KML.js"></script>
	<script type="application/x-javascript" src="./js/leaflet/gpx.js"></script>  
	<script type="application/x-javascript" src="./js/osopenspace.js"></script>
	<script type="application/x-javascript" src="./js/leaflet-openweathermap.js"></script>
	<script type="application/x-javascript" src="./js/esri-leaflet.js"></script>
	<script type="application/x-javascript" src="./js/Control.Geocoder.js"></script>
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
	<script type="application/x-javascript" src="./js/usng.js"></script>
	<script type="application/x-javascript" src="./js/osgb.js"></script>
	<script type="application/x-javascript" src="./js/geotools2.js"></script>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
<?php
$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));
print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var showTicker = <?php print $use_ticker;?>;
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var controlDiv;
var theLayer;
var quick = false;
var minimap;
var mapWidth;
var mapHeight;
var listHeight;
var sidebar_height;
var colwidth;
var listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var inc_last_display = 0;
var inc_period_changed = 0;
var i_interval = null;
var r_interval = null;
var f_interval = null;
var b_interval = null;
var fs_interval = null;
var latest_ticket = 0;
var latest_responder = 0;
var latest_facility = 0;
var do_update = true;
var do_resp_update = true;
var do_fac_update = true;
var tickets_updated = [];
var responders_updated = [];
var facilities_updated = [];
var inc_period = 0;
var last_disp = 0;
var cell1 = "0px";
var cell2 = "0px";
var cell3 = "0px";
var cell4 = "0px";
var cell5 = "0px";
var cell6 = "0px";
var cell7 = "0px";
var mapCenter;
var mapZoom;
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
var captions = ["Current situation", "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year", "Scheduled"];
var heading = captions[inc_period];
heading += " - ";
heading += "<?php print get_variable('map_caption');?>";
		
/* Initial period selection - current tickets, 
	options available 0 (current tickets), 
	1 - Closed today
	2 - Closed Yesterday+
	3 - Closed this week
	4 - Closed last week
	5 - Closed last week+
	6 - Closed this month
	7 - Closed last month
	8 - Closed this year
	9 - Closed last year
*/
var colors = new Array ('odd', 'even');
var fscolors = new Array ('fs_odd', 'fs_even');

function set_period(period) {
	window.inc_period = period;
	thelength = document.getElementById('period_select').options.length;
	for(var f = 0; f < thelength; f++) {
		if(document.getElementById('period_select').options[f].value == period) {
			document.getElementById('period_select').options[f].selected = true;
			}
		}
	$('theHeading').innerHTML = window.captions[window.inc_period];
	}

function submit_period() {
	$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
	inc_period_changed = 1;
	load_fs_incidentlist();
	}

function destroy_unitmarkers() {
	for (var i = 1; i < rmarkers.length; i++) {
		map.removeLayer(rmarkers[i]);
		}
	rmarkers.length = 0;
	}
	
function destroy_incmarkers() {
	for (var i = 1; i < tmarkers.length; i++) { 
		map.removeLayer(tmarkers[i]);
		}
	tmarkers.length = 0;
	}
	
function destroy_facmarkers() {
	for (var i = 1; i < fmarkers.length; i++) { 
		map.removeLayer(fmarkers[i]);
		}
	fmarkers.length = 0;
	}

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
	set_fontsizes(viewportwidth, "fullscreen");
	mapWidth = viewportwidth -2;
	mapHeight = viewportheight - 40;
	outerwidth = viewportwidth -2;
	outerheight = viewportheight - 50;
	listHeight = viewportheight * .25;
	colwidth = outerwidth * .42;
	colheight = outerheight * .7;
	listHeight = viewportheight * .5;
	sidebar_height = mapHeight - 50;
	listwidth = colwidth * .95
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = sidebar_height + "px";
	$('left_sidebar').style.height = sidebar_height + "px";
	$('listheading').style.width = listwidth + "px";
	$('ticketlist').style.maxHeight = listHeight + "px";
	$('ticketlist').style.width = listwidth + "px";
	$('ticketheader').style.width = listwidth + "px";
	$('assignmentslist').style.maxHeight = listHeight + "px";	
	$('assignmentslist').style.width = listwidth + "px";
	$('assignments_list').style.maxHeight = listHeight + "px";
	$('assignments_list').style.width = listwidth + "px";
	$('assignmentsheader').style.width = listwidth + "px";		
	$("hidelists").style.position = "absolute";	
	$("hidelists").style.top = "0px";
	$("hidelists").style.left = colwidth + "px";
	loadData();
	}
	
function loadData() {
	get_mi_totals();
	load_exclusions();
	load_ringfences();
	load_catchments();
	load_basemarkup();
	load_groupbounds();	
	get_assignments();
	get_unit_categories();
	load_fs_incidentlist();
	load_fs_responders();
	load_fs_facilities();
	full_scr_ass();
	load_regions();
	set_initial_pri_disp();
	load_poly_controls();
	mapCenter = map.getCenter();
	mapZoom = map.getZoom();
	map.invalidateSize();
	setTimeout(function() {$('leftcol').style.display = "none"; $('showlists').style.display='inline-block';},10000);
	load_warnlocations("fullscreen");
	}

var thelevel = '<?php print $the_level;?>';

function get_new_colors() {
	window.location.href = '<?php print basename(__FILE__);?>';
	}
	
function do_tab(tabid, suffix, lat, lng) {
	theTabs = new Array(1,2,3);
	for(var key in theTabs) {
		if(key == (suffix -1)) {
			}
		}
	if(tabid == "tab1") {
		if($('tab1')) {CngClass('tab1', 'tabinuse');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content1')) {$("content1").style.display = "block";}
		} else if(tabid == "tab2") {
		if($('tab2')) {CngClass('tab2', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab3')) {CngClass('tab3', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content3')) {$("content3").style.display = "none";}
		if($('content2')) {$("content2").style.display = "block";}
		} else {
		if($('tab3')) {CngClass('tab3', 'tabinuse');}
		if($('tab1')) {CngClass('tab1', 'tab');}
		if($('tab2')) {CngClass('tab2', 'tab');}
		if($('content1')) {$("content1").style.display = "none";}
		if($('content2')) {$("content2").style.display = "none";}
		if($('content3')) {$("content3").style.display = "block";}
		init_minimap(3, lat,lng, "", 13, <?php print get_variable('locale');?>, 1);
		minimap.setView([lat,lng], 13);
		}
	}

</SCRIPT>

<?php 
	if ($_SESSION['internet']) {	
?>
		<SCRIPT SRC='./js/usng.js' 			TYPE='application/x-javascript'></SCRIPT>
<?php
	}
	if($_SESSION['good_internet']) {
		$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;
		if((module_active("Ticker")==1) && (!($sit_scr))) {
?>
			<SCRIPT SRC='./modules/Ticker/js/mootools-1.2-core.js' type='application/x-javascript'></SCRIPT>
			<SCRIPT SRC='./modules/Ticker/js/ticker_core.js' type='application/x-javascript'></SCRIPT>
			<LINK REL=StyleSheet HREF="./modules/Ticker/css/ticker_css.php?version=<?php print time();?>" TYPE="text/css">
<?php
			$ld_ticker = "ticker_init();";
			}
		}

?>	
</HEAD>
<?php
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	
	if((!(is_guest())) && ($_SESSION['good_internet']) && (!($get_id))) {
		if(file_exists("./incs/modules.inc.php")) {
			get_modules('main');
			}
		}	
	
	$gunload = "clearInterval(i_interval); clearInterval(r_interval); clearInterval(f_interval); clearInterval(b_interval);";
?>
<BODY STYLE='background-color: #CECECE;' onLoad = "loadData(); <?php print $ld_ticker;?> location.href = '#top';" onUnload = "<?php print $gunload;?>";>
<A NAME='top'></A>
<DIV id='screenname' style='display: none;'>fullscreen</DIV>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 ID = "down"/></div>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>

<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV id='outer' style='position: absolute; left: 0px; top: 30px; z-index: 1;'>
	<DIV id='left_sidebar' style='position: fixed; top: 70px; left: 0px; height: 500px; font-size: 1.2em; z-index: 9999; background-color: #FFFFFF;'>
		<SPAN id='showlists' class='plain text' style='position: absolute; top: 250px; left: 0px; width: 35px; display: none; cursor: pointer; padding: 2px; background-color: #FEFEFE; text-align: right;' 
		onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' 
		onClick="$('leftcol').style.display = 'inline-block'; $('showlists').style.display = 'none'; $('hidelists').style.display = 'inline-block';">
		<IMG src='./images/fs_show_lists_button.jpg'></SPAN>
		<DIV id='leftcol' style='position: absolute; top: 5px; left: 5px; width: 250px; padding-left: 20px; border: 1px outset #707070; background-color:rgba(0, 0, 0, 0.2); text-align: center; z-index: 3;'>
			<BR /><BR />
			<DIV id='listheading' class='heading text' style='text-align: center; font-size: 24px;'>Incidents and Assignments</DIV><BR /><BR />
			<DIV id='ticketheader' class='header text' style='text-align: center;'>Incidents</DIV>
			<DIV class="scrollableContainer" id='ticketlist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='the_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
			<BR /><BR />
			<DIV id='assignmentsheader' class='header text' style='text-align: center;'>Unit Assignments</DIV>
			<DIV class="scrollableContainer" id='assignmentslist' style='border: 1px outset #707070;'>
				<DIV class="scrollingArea" id='assignments_list'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>				
			</DIV>
		</DIV>
		<SPAN id='hidelists' class='plain text' style='position: absolute; top: 70px; left: 500px; float: right; z-index: 9999; width: 26px; cursor: pointer; display: none;' 
		onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' 
		onClick="$('leftcol').style.display = 'none'; $('showlists').style.display='inline-block'; $('hidelists').style.display='none';">
		<IMG src='./images/fs_hide_lists_button.jpg'></SPAN>
	</DIV>
	<DIV id='map_canvas' style='border: 1px outset #707070; z-index: 1; position: relative; top: 5px;'></DIV>	
</DIV>
<DIV id='controls_bar' style='position: fixed; top: 0px; left: 0px; width: 100%; border: 1px outset #707070; background-color:rgba(0, 0, 0, 0.25); text-align: center; z-index: 3;'>
	<DIV CLASS='header text' style = "height:32px; width: 100%; float: none; text-align: center;">
		<A id='maj_incs' class='text_bold text_biggest' style='text-decoration: none; background-color: Red; color: #FFFFFF; float: left; width: 300px; display: inline-block; cursor: pointer;' HREF="maj_inc.php"></A>
		<SPAN ID='theHeading' CLASS='header text_biggest' STYLE='background-color: inherit;'></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID='theRegions' CLASS='heading text' STYLE='background-color: #707070;' onmouseout='UnTip();'>Viewing Regions (mouse over to view)</SPAN>
		<SPAN ID='sev_counts' CLASS='sev_counts text_small'></SPAN>
	</DIV>
	<SPAN id='close' class='plain text' style='float: left; width: 100px; display: inline-block' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='./images/close_door_small.png' BORDER=0></SPAN>
	<SPAN id='fs_show_asgn' class='plain text' style='float: left; width: 150px; display: inline-block;'><SPAN STYLE='float: left;'>Show Assigned</SPAN><IMG id='fs_show_asgn_img' SRC='./images/assigned_small.png' style='opacity: 0.3;'></SPAN>
	<CENTER>
	<TABLE style='text-align: center; width: 200px;'>
		<TR>
			<TD>
				<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >
					<SELECT CLASS='text' ID='frm_interval' NAME = 'frm_interval' onChange = 'show_btns_closed(); set_period(this.value);'>
						<OPTION CLASS='text' VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>
						<OPTION CLASS='text' VALUE='0'><?php print get_text("Current situation"); ?></OPTION>
						<OPTION CLASS='text' VALUE='1'><?php print $incidents;?> closed today</OPTION>
						<OPTION CLASS='text' VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>
						<OPTION CLASS='text' VALUE='3'><?php print $incidents;?> closed this week</OPTION>
						<OPTION CLASS='text' VALUE='4'><?php print $incidents;?> closed last week</OPTION>
						<OPTION CLASS='text' VALUE='5'><?php print $incidents;?> closed last week+</OPTION>
						<OPTION CLASS='text' VALUE='6'><?php print $incidents;?> closed this month</OPTION>
						<OPTION CLASS='text' VALUE='7'><?php print $incidents;?> closed last month</OPTION>
						<OPTION CLASS='text' VALUE='8'><?php print $incidents;?> closed this year</OPTION>
						<OPTION CLASS='text' VALUE='9'><?php print $incidents;?> closed last year</OPTION>
					</SELECT>
				</FORM>
			</TD>
			<TD>
				<SPAN ID = 'btn_go' class = 'plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='submit_period(); hide_btns_closed();' STYLE = 'color: green; display: none;'><U>Next</U></SPAN>
			</TD>
			<TD>
				<SPAN ID = 'btn_can' class='plain text' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='hide_btns_closed(); hide_btns_scheduled();' STYLE = 'color: red; display: none'><U>Cancel</U></SPAN>
			</TD>
		</TR>
	</TABLE>
	</CENTER>

</DIV>	
<DIV style='position: fixed; top: 100px; right: 0px; z-index: 9999; width: auto;'>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(TRUE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, 0, 0, 0);
?>
</DIV>
<SCRIPT>
var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
controlsHTML +=	"<TR CLASS='odd'><TD>";
controlsHTML +=	"<TABLE WIDTH='100%'><TR class='heading_2' WIDTH='100%'><TH ALIGN='center'>Incidents</TH></TR><TR><TD>";
controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('normal'); hideGroup(1, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' STYLE = 'vertical-align: middle'BORDER=0>&nbsp;&nbsp;Normal: <input type=checkbox id='normal'  onClick=\"set_pri_chkbox('normal')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('medium'); hideGroup(2, 'Incident');\"><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;Medium: <input type=checkbox id='medium'  onClick=\"set_pri_chkbox('medium')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button' onClick=\"set_pri_chkbox('high'); hideGroup(3, 'Incident');\"><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;High: <input type=checkbox id='high'  onClick=\"set_pri_chkbox('high')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button' ID = 'pri_all' class='pri_button' STYLE = 'display: none; width: 70px;' onClick=\"set_pri_chkbox('all'); hideGroup(4, 'Incident');\"><IMG SRC = './our_icons/sm_blue.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_green.png' BORDER=0 STYLE = 'vertical-align: middle'><IMG SRC = './our_icons/sm_red.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;All <input type=checkbox id='all'  STYLE = 'display:none;' onClick=\"set_pri_chkbox('all')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"<DIV class='pri_button' ID = 'pri_none' class='pri_button' STYLE = 'width: 60px;' onClick=\"set_pri_chkbox('none'); hideGroup(5, 'Incident');\"><IMG SRC = './our_icons/sm_white.png' BORDER=0 STYLE = 'vertical-align: middle'>&nbsp;&nbsp;None <input type=checkbox id='none' STYLE = 'display:none;' onClick=\"set_pri_chkbox('none')\"/>&nbsp;&nbsp;</DIV>";
controlsHTML +=	"</TD></TR></TABLE></TD></TR><TR CLASS='odd'><TD><DIV ID = 'boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'fac_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR>";
controlsHTML += "</TABLE></CENTER></TD></TR></TABLE>";

//	setup map-----------------------------------//
var map;
var minimap;
var thelevel = '<?php print $the_level;?>';
var tmarkers = [];	//	Incident markers array
var rmarkers = [];			//	Responder Markers array
var fmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	conditions markers array
var wlmarkers = [];			//	Locations warning markers array
var rss_markers = [];		//	RSS markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var bounds;
var zoom;
var got_points = false;
var latLng;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
var isTicker = <?php print module_active("Ticker");?>;
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
set_fontsizes(viewportwidth, "fullscreen");
mapWidth = viewportwidth -2;
mapHeight = viewportheight - 40;
outerwidth = viewportwidth -2;
outerheight = viewportheight - 50;
listHeight = viewportheight * .25;
colwidth = outerwidth * .42;
colheight = outerheight * .7;
listHeight = viewportheight * .5;
sidebar_height = mapHeight - 50;
listwidth = colwidth * .95
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = sidebar_height + "px";
$('left_sidebar').style.height = sidebar_height + "px";
$('listheading').style.width = listwidth + "px";
$('ticketlist').style.maxHeight = listHeight + "px";
$('ticketlist').style.width = listwidth + "px";
$('ticketheader').style.width = listwidth + "px";
$('assignmentslist').style.maxHeight = listHeight + "px";	
$('assignmentslist').style.width = listwidth + "px";
$('assignments_list').style.maxHeight = listHeight + "px";
$('assignments_list').style.width = listwidth + "px";
$('assignmentsheader').style.width = listwidth + "px";
$("hidelists").style.position = "absolute";	
$("hidelists").style.top = "0px";
$("hidelists").style.left = colwidth + "px";
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_fsmap(1, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "br");
map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;
$('controls').innerHTML = controlsHTML;
$('theHeading').innerHTML = heading;
<?php
do_kml();
?>
</SCRIPT>
<?php
	$sit_scr = (array_key_exists('id', ($_GET)))? $_GET['id'] :	NULL;		//	10/23/12	
	if(($_SESSION['good_internet']) && (module_active("Ticker")==1) && (!($sit_scr))) {			//	10/23/12
		require_once('./modules/Ticker/incs/ticker.inc.php');
		$the_markers = buildmarkers();
		foreach($the_markers AS $value) {
			if(my_is_float($value[3]) && my_is_float($value[3])) {
?>
<SCRIPT>
				var theLat = <?php print $value[3];?>;
				var theLng = <?php print $value[4];?>;
				var theA = "<?php print $value[6];?>";
				var the_point = new L.LatLng(theLat, theLng);		//	10/23/12
				var the_header = "Traffic Alert";		//	10/23/12
				var the_text = "<?php print $value[1];?>";		//	10/23/12
				var the_id = "<?php print $value[0];?>";		//	10/23/12
				var the_category = "<?php print $value[5];?>";		//	10/23/12
				var the_link = '<A CLASS="link" HREF="' + theA + '" TARGET="_blank" TITLE="' + the_text + '">' + the_text + '</A>';
				var the_descrip = "<DIV style='border: 1px outset #707070; background-color: yellow;'>";
				the_descrip += "<DIV style='font-size: 14px; color: #FFFFFF; background-color: #707070; font-weight: bold;'>" + the_header + "</DIV><BR />";		//	10/23/12
				the_descrip += "<DIV style='font-size: 14px; color: #000000; font-weight: bold;'>" + the_category + "</DIV><BR />";		//	10/23/12
				the_descrip += "<DIV><SPAN>" + the_link + "</SPAN></DIV><BR />";		
				the_descrip += "<DIV style='font-size: 12px; color: blue; font-weight: normal;'>";		//	10/23/12
				the_descrip += "<?php print $value[2];?>";		//	10/23/12
				the_descrip += "</DIV></DIV>";		//	10/23/12
				var rss_marker = create_feedMarker(the_point, the_text, the_descrip, the_id, the_id);		//	10/23/12
				rss_marker.addTo(map);			
</SCRIPT>
<?php
			}
		}
	}
?>
<FORM NAME='to_listtype' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
</FORM>
<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
<INPUT TYPE='hidden' NAME='func' VALUE='1' />
</FORM>
<FORM NAME='to_map' METHOD='get' ACTION = 'config.php'>
<INPUT TYPE='hidden' NAME='func' VALUE='api_key' />
</FORM>

<FORM NAME='view_loc_Form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='view' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<FORM NAME='edit_loc_Form' METHOD='get' ACTION='warn_locations.php'>
<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
<INPUT TYPE='hidden' NAME='id' VALUE=''>
</FORM>

<br /><br />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png" ID = "up" BORDER=0></div>
<A NAME="bottom" />
</BODY>
<?php
if (array_key_exists('print', ($_GET))) {
?>
<script>
$("down").style.display = $("up").style.display = "none";
</script>
<?php
	}
?>
</HTML>
