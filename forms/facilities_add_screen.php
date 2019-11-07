<?php
/*

*/
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$ld_ticker = "";
$show_controls = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none" ;	//	3/15/11
$col_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "s")) ? "" : "none";	//	3/15/11
$exp_butt = ((isset($_SESSION['hide_controls'])) && ($_SESSION['hide_controls'] == "h")) ? "" : "none";		//	3/15/11
$show_resp = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none" ;	//	3/15/11
$resp_col_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "s")) ? "" : "none";	//	3/15/11
$resp_exp_butt = ((isset($_SESSION['resp_list'])) && ($_SESSION['resp_list'] == "h")) ? "" : "none";	//	3/15/11	
$show_facs = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none" ;	//	3/15/11
$facs_col_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "s")) ? "" : "none";	//	3/15/11
$facs_exp_butt = ((isset($_SESSION['facs_list'])) && ($_SESSION['facs_list'] == "h")) ? "" : "none";	//	3/15/11
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
$curr_cats = get_category_butts();	//	get current categories.
$cat_sess_stat = get_session_status($curr_cats);	//	get session current status categories.
$hidden = find_hidden($curr_cats);
$shown = find_showing($curr_cats);
$un_stat_cats = get_all_categories();
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
print do_calls();		// call signs to JS array for validation
?>
<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
window.onload = function(){set_size();};
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var listHeight;
var colwidth;
var listwidth;
var inner_listwidth;
var celwidth;
var res_celwidth;
var fac_celwidth;
var viewportwidth;
var viewportheight;
var colheight;
var outerwidth;
var outerheight;
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

var colors = new Array ('odd', 'even');
var fields = ["name",
			"about",
			"location",
			"description",
			"beds_info",
			"capability",
			"contact_name",
			"contact_email",
			"contact_phone",
			"sec_contact",
			"sec_email",
			"sec_phone",
			"access_rules",
			"sec_reqs",
			"pager_prim",
			"pager_sec",
			"notify_email",
			"filename"];
var medfields = ["city",
				"handle",
				"grid",
				"file",
				"is_warehouse"];
var smallfields = ["beds_o",
				"beds_a",
				"show_lat",
				"show_lng"];

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
	mapWidth = viewportwidth * .40;
	mapHeight = mapWidth * .9;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	fieldwidth = colwidth * .6;
	medfieldwidth = colwidth * .3;		
	smallfieldwidth = colwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = colwidth + "px";
	$('leftcol').style.height = colheight + "px";	
	$('rightcol').style.width = colwidth + "px";
	$('rightcol').style.height = colheight + "px";	
	$('map_canvas').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	$('map_legend').style.width = mapWidth + "px";
	$('icon_legend').style.width = mapWidth + "px";
	for (var i = 0; i < fields.length; i++) {
		 $(fields[i]).style.width = fieldwidth + "px";
		} 
	for (var i = 0; i < medfields.length; i++) {
		 $(medfields[i]).style.width = medfieldwidth + "px";
		}
	for (var i = 0; i < smallfields.length; i++) {
		 $(smallfields[i]).style.width = smallfieldwidth + "px";
		}
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	map.invalidateSize();
	set_fontsizes(viewportwidth, "fullscreen");
	}
	
var fac_icons=[];
fac_icons[0] = 1;
fac_icons[1] = 2;
fac_icons[2] = 3;
fac_icons[3] = 4;	
fac_icons[4] = 5;
fac_icons[5] = 6;
fac_icons[6] = 7;
fac_icons[7] = 8;

var max_zoom = <?php print get_variable('def_zoom');?>;

var map;				// make globally visible
var myMarker;
var sortby = '`date`';	//	10/23/12
var sort = "DESC";	//	10/23/12
var columns = "<?php print get_msg_variable('columns');?>";	//	10/23/12
var the_columns = new Array(<?php print get_msg_variable('columns');?>);	//	10/23/12
var thescreen = 'ticket';	//	10/23/12
var thelevel = '<?php print $the_level;?>';
var rmarkers = [];			//	Responder Markers array
var cmarkers = [];			//	conditions markers array
var boundary = [];			//	exclusion zones array
var bound_names = [];
var the_icon;
var currentPopup;
var marker;
var markers;
var zoom = <?php print get_variable('def_zoom');?>;
var locale = <?php print get_variable('locale');?>;
var my_Local = <?php print get_variable('local_maps');?>;
var lon = <?php print get_variable('def_lng');?>;
var lat = <?php print get_variable('def_lat');?>;
	
function validate(theForm) {						// Facility form contents validation
	if (theForm.frm_remove) {
		if (theForm.frm_remove.checked) {
			var str = "Please confirm removing '" + theForm.frm_name.value + "'";
			if(confirm(str)) 	{
				theForm.submit();
				return true;}
			else 				{return false;}
			}
		}

	var errmsg="";
	if (theForm.frm_name.value.trim()=="")											{errmsg+="Facility NAME is required.\n";}
	if (theForm.frm_handle.value.trim()=="")										{errmsg+="Facility HANDLE is required.\n";}
	if (theForm.frm_icon_str.value.trim()=="")										{errmsg+="Facility ICON is required.\n";}
	if (theForm.frm_type.options[theForm.frm_type.selectedIndex].value==0)			{errmsg+="Facility TYPE is required.\n";}
	if (theForm.frm_status_id.options[theForm.frm_status_id.selectedIndex].value==0)	{errmsg+="Facility STATUS is required.\n";}
	if (theForm.frm_descr.value.trim()=="")											{errmsg+="Facility DESCRIPTION is required.\n";}
	if(allow_nogeo == "0") {
		if (theForm.frm_lat.value.trim().length == 0) 	{
			errmsg+="Facility LOCATION must be set - click map location to set.\n";
			}
		} else {
		if(theForm.frm_lat.value.trim().length == 0) {
			theForm.frm_lat.value = def_lat;
			theForm.frm_lng.value = def_lng;
			}
		}	

	if (errmsg!="") {
		alert ("Please correct the following and re-submit:\n\n" + errmsg);
		return false;
		}
	else {														// good to go!
//			top.upper.calls_start();
		theForm.submit();
//			return true;
		}
	}				// end function validate(theForm)

function contains(array, item) {
	for (var i = 0, I = array.length; i < I; ++i) {
		if (array[i] == item) return true;
		}
	return false;
	}
	
function check_days(id) {
	if((id == "monday") && ($('monday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[0][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "monday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[0][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[0][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[0][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "tuesday") && ($('tuesday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[1][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "tuesday") && (!($(id).tuesday))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[1][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[1][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[1][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "wednesday") && ($('wednesday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[2][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "wednesday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[2][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[2][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[2][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "thursday") && ($('thursday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[3][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "thursday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[3][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[3][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[3][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "friday") && ($('friday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[4][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "friday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[4][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[4][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[4][2]'].style.backgroundColor = "#CECECE";
		} else if((id == "saturday") && ($('saturday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[5][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "saturday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[5][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[5][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[5][2]'].style.backgroundColor = "#CECECE";		
		} else if((id == "sunday") && ($('sunday').checked)) {
		document.forms['res_add_Form'].elements['frm_opening_hours[6][0]'].checked = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].readOnly  = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#FFFFFF";
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#FFFFFF";
		} else if((id == "sunday") && (!($(id).checked))) {
		document.forms['res_add_Form'].elements['frm_opening_hours[6][0]'].checked = false;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].readOnly  = true;
		document.forms['res_add_Form'].elements['frm_opening_hours[6][1]'].style.backgroundColor = "#CECECE";
		document.forms['res_add_Form'].elements['frm_opening_hours[6][2]'].style.backgroundColor = "#CECECE";		
		} else {
		}
	}
</SCRIPT>
</HEAD>
<BODY onLoad='set_size();'>
	<DIV ID='to_bottom' style='position:fixed; top:2px; left:50px; height: 12px; width: 10px;' onclick = 'to_bottom()'><IMG SRC='markers/down.png'  BORDER=0 /></DIV>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV id = "leftcol" style='position: relative; left: 10px; float: left;'>
			<A NAME='top'>
			<FORM NAME= "res_add_Form" METHOD="POST" ACTION="facilities.php?func=responder&goadd=true">
			<TABLE BORDER="0" ID='addform' WIDTH='98%'>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
				</TR>
				<TR CLASS='even'>
					<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
						<SPAN CLASS='text_green text_biggest'>Add <?php print get_text("Facility"); ?></SPAN>
						<BR />
						<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
						<BR />
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label text"><?php print get_text("Is this a Warehouse?"); ?>: </TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text'>
						<SELECT id='is_warehouse' NAME="frm_is_warehouse" onChange = "do_is_warehouse(this.value);">
							<OPTION VALUE=0>No</OPTION>
							<OPTION VALUE=1>Yes</OPTION>
						</SELECT>					
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99></TD>
				</TR>				
				<TR CLASS = "even">
					<TD CLASS="td_label_text"><A CLASS="td_label_text" HREF="#" TITLE="Facility Name - fill in with Name/index where index is the label in the list and on the marker"><?php print get_text("Name"); ?></A>:&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 >
						<INPUT id='name' MAXLENGTH="48" SIZE="48" TYPE="text" NAME="frm_name" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text"><A CLASS="td_label_text" HREF="#" TITLE="Handle - local rules, local abbreviated name for the facility"><?php print get_text("Handle"); ?></A>:&nbsp;</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 >
						<INPUT id='handle' MAXLENGTH="48" SIZE="24" TYPE="text" NAME="frm_handle" VALUE="" />
						<SPAN STYLE = "margin-left:40px;" CLASS="td_label_text" TITLE="A 3-letter value to be used in the map icon">Icon:</SPAN>&nbsp;<FONT COLOR='red' SIZE='-1'>*</FONT>&nbsp;
						<INPUT id='icon' TYPE="text" SIZE = 3 MAXLENGTH=3 NAME="frm_icon_str" VALUE="" />			
					</TD>
				</TR>
<?php
				if(get_num_groups()) {
					if((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>		
						<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
							<TD CLASS="td_label_text">
								<A CLASS="td_label_text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
								<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
								<DIV id='checkButts' style='display: none;'>
									<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>	
								</DIV>
<?php
								$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
								print get_user_group_butts(($_SESSION['user_id']));
?>
							</TD>
						</TR>
<?php
						} elseif((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {
?>		
						<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
							<TD CLASS="td_label_text">
								<A CLASS="td_label_text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
								<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
								<DIV id='checkButts' style='display: none;'>
									<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>				
								</DIV>
	<?php

								$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));
								print get_user_group_butts(($_SESSION['user_id']));
?>	
							</TD>
						</TR>
<?php
						} elseif(COUNT(get_allocates(4, $_SESSION['user_id'])) > 1) {	//	6/10/11
?>
						<TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
							<TD CLASS="td_label_text">
								<A CLASS="td_label_text" HREF="#" TITLE="Sets Regions that Facility is allocated to - click + to expand, - to collapse"><?php print get_text("Region");?></A>: 
								<SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
								<SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN>
							</TD>
							<TD>
								<DIV id='checkButts' style='display: none;'>
									<SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
									<SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>		
								</DIV>
<?php
								$alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
								print get_user_group_butts_readonly($_SESSION['user_id'])		
?>	
							</TD>
						</TR>
<?php
						} else {
?>
						<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
						}
					} else {
?>
					<INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1">	 <!-- 6/10/11 -->
<?php
					}
				if(is_administrator()) {	//	6/10/11
?>
					<TR CLASS='odd' VALIGN="top">	<!--  6/10/11 -->
						<TD CLASS="td_label_text">
							<A CLASS="td_label_text" HREF="#" TITLE="Sets Facility Boundary"><?php print get_text("Boundary");?></A>:
						</TD>
						<TD>&nbsp;</TD>
						<TD COLSPAN=2 CLASS='td_data text'>
							<SELECT NAME="frm_boundary" onChange = "this.value=JSfnTrim(this.value)">
								<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
								$query_bound = "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` WHERE `use_with_f` = 1 ORDER BY `line_name` ASC";
								$result_bound = mysql_query($query_bound) or do_error($query_bound, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
								while ($row_bound = stripslashes_deep(mysql_fetch_assoc($result_bound))) {
									print "\t<OPTION VALUE='{$row_bound['id']}'>{$row_bound['line_name']}</OPTION>\n";		// pipe separator
									}
?>
							</SELECT>
						</TD>
					</TR>
<?php
					}		
?>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>			
				<TR CLASS = "even" VALIGN='middle'>
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility Type - Select from pulldown menu"><?php print get_text("Type"); ?></A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<SELECT NAME='frm_type'>
							<OPTION VALUE=0>Select one</OPTION>
<?php
							foreach ($f_types as $key => $value) {
								$temp = $value; 												// 2-element array
								print "\t\t\t\t<OPTION VALUE='" . $key . "'>" .$temp[0] . "</OPTION>\n";
								}
?>
						</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<A CLASS="td_label_text" HREF="#" TITLE="Calculate directions on dispatch? - required if you wish to use email directions to unit facility">Directions</A> &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp" checked />
					</TD>
				</TR>

				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility Status - Select from pulldown menu"><?php print get_text("Status"); ?></A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<SELECT NAME="frm_status_id" onChange = "document.res_add_Form.frm_log_it.value='1'">
							<OPTION VALUE=0 SELECTED>Select one</OPTION>
<?php
							$query = "SELECT * FROM `$GLOBALS[mysql_prefix]fac_status` ORDER BY `group` ASC, `sort` ASC, `status_val` ASC";
							$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
							$the_grp = strval(rand());			//  force initial optgroup value
							$i = 0;
							while ($row_st = stripslashes_deep(mysql_fetch_assoc($result_st))) {
								if ($the_grp != $row_st['group']) {
									print ($i == 0)? "": "\t</OPTGROUP>\n";
									$the_grp = $row_st['group'];
									print "\t<OPTGROUP LABEL='$the_grp'>\n";
									}
								print "\t<OPTION VALUE=' {$row_st['id']}'  CLASS='{$row_st['group']}' title='{$row_st['description']}'> {$row_st['status_val']} </OPTION>\n";
								$i++;
								}		// end while()
							print "\n</OPTGROUP>\n";
							unset($result_st);
?>
						</SELECT>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="About Facility status - information about particular status values for this facility">About Status</A>
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='about' SIZE="48" TYPE="text" NAME="frm_status_about" VALUE="" MAXLENGTH="512">
					</TD>
				</TR>
				<TR class='spacer'>
					<TD class='spacer' COLSPAN=99>&nbsp;</TD>
				</TR>	
				<TR CLASS='even'>
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Location - type in location in fields or click location on map "><?php print get_text("Location"); ?></A>:
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='location' SIZE="48" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61">
					</TD>
				</TR> <!-- 7/5/10 -->
				<TR CLASS='odd'>
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City"); ?></A>:
					</TD>
					<TD>
						<button type="button" onClick="Javascript:loc_lkup(document.res_add_Form);"><img src="./markers/glasses.png" alt="Lookup location." /></button>
					</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='city' SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A CLASS="td_label_text" HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
						<INPUT SIZE="<?php print $st_size;?>" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="<?php print $st_size;?>">
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility Description - additional details about unit">Description</A>:&nbsp;<font color='red' size='-1'>*</font>
					</TD>	
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TEXTAREA id='description' NAME="frm_descr" COLS=60 ROWS=2></TEXTAREA>
					</TD>
				</TR>
				<TR id='beds_information' CLASS = "odd">
					<TD CLASS="td_label_text"><A CLASS="td_label_text" HREF="#" TITLE="Facility beds "><?php print get_text("Beds"); ?></A>&nbsp;</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<SPAN CLASS = "td_label_text">Available: </SPAN><INPUT id='beds_a' SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_a" VALUE="" />			
						<SPAN CLASS = "td_label_text">Occupied: </SPAN><INPUT id='beds_o' SIZE="16" MAXLENGTH="16" TYPE="text" NAME="frm_beds_o" VALUE="" />			
					</TD>
				</TR>
				<TR id='beds_information2' CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Beds information"><?php print get_text("Beds"); ?> information</A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TEXTAREA id='beds_info' NAME="frm_beds_info" COLS=60 ROWS=2></TEXTAREA>			
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility Capability - e.g ER, Cells, Medical distribution"><?php print get_text("Capability"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TEXTAREA id='capability' NAME="frm_capab" COLS=60 ROWS=2></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility main contact name"><?php print get_text("Contact name"); ?></A>:&nbsp;
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='contact_name' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_name" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility contact email - main contact email address"><?php print get_text("Contact email"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='contact_email' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_email" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility contact phone number - main contact phone number"><?php print get_text("Contact phone"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='contact_phone' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_contact_phone" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility security contact"><?php print get_text("Security contact"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='sec_contact' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_contact" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility security contact email"><?php print get_text("Security email"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='sec_email' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_email" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility security contact phone number"><?php print get_text("Security phone"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='sec_phone' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_security_phone" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility opening hours - e.g. 24x7x365, 8 - 5 mon to sat etc."><?php print get_text("Opening hours"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TABLE style='width: 100%;'>
							<TR>
								<TH style='text-align: left;'><A CLASS="td_label_text" HREF="#" TITLE="Day of the Week"><?php print get_text("Day"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label_text" HREF="#" TITLE="Opening Time"><?php print get_text("Opening"); ?></A></TH>
								<TH style='text-align: left;'><A CLASS="td_label_text" HREF="#" TITLE="Opening Time"><?php print get_text("Closing"); ?></A></TH>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='monday' TYPE="CHECKBOX" NAME="frm_opening_hours[0][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Monday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='monday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='monday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[0][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='tuesday' TYPE="CHECKBOX" NAME="frm_opening_hours[1][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Tuesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='tuesday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='tuesday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[1][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='wednesday' TYPE="CHECKBOX" NAME="frm_opening_hours[2][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Wednesday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='wednesday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='wednesday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[2][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='thursday' TYPE="CHECKBOX" NAME="frm_opening_hours[3][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Thursday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='friday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='friday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[3][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='friday' TYPE="CHECKBOX" NAME="frm_opening_hours[4][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Friday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='friday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='friday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[4][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='saturday' TYPE="CHECKBOX" NAME="frm_opening_hours[5][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Saturday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='saturday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='saturday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[5][2]" VALUE="23:59" /></TD>
							</TR>
							<TR>
								<TD style='text-align: left;'><INPUT ID='sunday' TYPE="CHECKBOX" NAME="frm_opening_hours[6][0]" CHECKED onClick = 'check_days(this.id);'><SPAN CLASS='td_label text'>Sunday</SPAN></TD>
								<TD style='text-align: left;'><INPUT ID='sunday_start' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][1]" VALUE="00:00" /></TD>
								<TD style='text-align: left;'><INPUT ID='sunday_end' SIZE="5" MAXLENGTH="5" TYPE="text" NAME="frm_opening_hours[6][2]" VALUE="23:59" /></TD>
							</TR>
						</TABLE>
					</TD>			
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility access rules - e.g enter by main entrance, enter by ER entrance, call first etc"><?php print get_text("Access rules"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TEXTAREA id='access_rules' NAME="frm_access_rules" COLS=60 ROWS=5></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility securtiy requirements - e.g. phone security first, visitors must be security cleared etc."><?php print get_text("Security reqs"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<TEXTAREA id='sec_reqs' NAME="frm_security_reqs" COLS=60 ROWS=5></TEXTAREA>
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility contact primary pager number"><?php print get_text("Primary pager"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='pager_prim' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_p" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Facility contact secondary pager number"><?php print get_text("Secondary pager"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='pager_sec' SIZE="48" MAXLENGTH="48" TYPE="text" NAME="frm_pager_s" VALUE="" />
					</TD>
				</TR>
<?php
				if($good_internet) {
?>
					<TR CLASS = "odd">
						<TD CLASS="td_label_text">
							<A CLASS="td_label_text" HREF="#" TITLE="Latitude and Longitude - set from map click">
							<SPAN onClick = 'javascript: do_coords(document.res_add_Form.frm_lat.value ,document.res_add_Form.frm_lng.value)'>
								<?php print get_text("Lat/Lng"); ?></A></SPAN>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<IMG ID='lock_p' BORDER=0 SRC='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.res_add_Form);' />
						</TD>
						<TD>&nbsp;</TD>
						<TD COLSPAN=2 CLASS='td_data text text_left'>
							<INPUT id='show_lat' TYPE="text" NAME="show_lat" SIZE=11 VALUE="" disabled />
							<INPUT id='show_lng' TYPE="text" NAME="show_lng" SIZE=11 VALUE="" disabled />&nbsp;&nbsp;
						</TD>
					</TR>
<?php
					$locale = get_variable('locale');
					switch($locale) { 
						case "0":
							$label = "<SPAN ID = 'usng_link' onClick = 'do_usng_conv(res_add_Form)' style='font-weight: bold;'>USNG:</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='' disabled />";
							break;

						case "1":
							$label = "<SPAN ID = 'osgb_link' style='font-weight: bold;'>OSGB:</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_ngs' VALUE='' disabled />";
							break;

						default:
							$label = "<SPAN ID = 'utm_link' style='font-weight: bold;'>UTM:</SPAN>";
							$input = "<INPUT id='grid' TYPE='text' SIZE=19 NAME='frm_utm' VALUE='' disabled />";
						}
?>
					<TR CLASS = "odd">
						<TD CLASS="td_label_text">
							<?php print $label;?>
						</TD>
						<TD>&nbsp;</TD>
						<TD COLSPAN=2 CLASS='td_data text text_left'>
							<?php print $input;?>
						</TD>
					</TR>
<?php
					}
				$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";	//	8/28/13
				$mg_select .= "<OPTION VALUE=0>Select Mail List</OPTION>";	//	8/28/13
				$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup`";	//	8/28/13
				$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	//	8/28/13
				while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {	//	8/28/13
					$mg_select .= "<OPTION VALUE=" . $row_mg['id'] . ">" . $row_mg['name'] . "</OPTION>";
					}
				$mg_select .= "</SELECT>";
?>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Notify Facility with selected mail list"><?php print get_text("Notify Mail List"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<?php print $mg_select;?>
					</TD>
				</TR>
				<TR CLASS = "even">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Notify Facility with this email address"><?php print get_text("Notify Email Address"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='notify_email' SIZE="48" MAXLENGTH="128" TYPE="text" NAME="frm_notify_email" VALUE="" />
					</TD>
				</TR>
				<TR CLASS = "odd">
					<TD CLASS="td_label_text">
						<A CLASS="td_label_text" HREF="#" TITLE="Notify when?"><?php print get_text("Notify when"); ?></A>:&nbsp;
					</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<SELECT NAME="frm_notify_when">
							<OPTION VALUE=1 SELECTED>All</OPTION>
							<OPTION VALUE=2 SELECTED>Incident Open</OPTION>
							<OPTION VALUE=3 SELECTED>Incident Close</OPTION>
						</SELECT>
					</TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
				</TR>
				<TR class='heading text'>
					<TD COLSPAN='4' class='heading text' style='text-align: center;'>File Upload</TD>
				</TR>
				<TR class='even'>
					<TD class='td_label text' style='text-align: left;'>Choose a file to upload:</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
						<INPUT id='file' NAME="frm_file" TYPE="file" />
					</TD>
				</TR>
				<TR class='odd'>
					<TD class='td_label text' style='text-align: left;'>File Name</TD>
					<TD>&nbsp;</TD>
					<TD COLSPAN=2 CLASS='td_data text text_left'>
					<INPUT id='filename' NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE=""></TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
				</TR>
				<TR class='spacer'>
					<TD COLSPAN='4' class='spacer'>&nbsp;</TD>
				</TR>
				<TR>
					<TD COLSPAN=4 ALIGN='center'><font color='red' size='-1'>*</FONT> Required</TD>
				</TR>
			<TR>
				<TD>&nbsp;</TD>
			</TR>
				<INPUT TYPE='hidden' NAME = 'frm_lat' VALUE=''/>
				<INPUT TYPE='hidden' NAME = 'frm_lng' VALUE=''/>
				<INPUT TYPE='hidden' NAME = 'frm_log_it' VALUE=''/>
				<INPUT TYPE='hidden' NAME = 'frm_direcs' VALUE=1 />
			</TABLE>
			</FORM>
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 9999;'>
				<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
				<SPAN id='reset_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='do_add_reset(this.form);'><?php print get_text("Reset");?><BR /><IMG id='can_img' SRC='./images/restore.png' /></SPAN>
				<SPAN id='sub_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='validate(document.res_add_Form);'><?php print get_text("Submit");?><BR /><IMG id='can_img' SRC='./images/submit.png' /></SPAN>
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
			<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
			<DIV id= 'map_legend' style = "text-align: center;">
				<B>Map click to set location, drag / click to change location</B>
			</DIV>
			<BR />
			<DIV ID='icon_legend' style='width: <?php print get_variable('map_width');?>px; text-align: center;'>
				<SPAN><?php print get_text("Facilities Legend");?></SPAN>
				<BR /><BR />
				<SPAN style='text-align: center;'><?php print get_icon_legend();?></SPAN>
			</DIV>
		</DIV>
	</DIV>
	<FORM NAME='can_Form' METHOD="post" ACTION = "facilities.php"></FORM>
	<!-- <?php echo __LINE__;?> -->
	<A NAME="bottom" /> 
	<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
		
<SCRIPT>
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
mapWidth = viewportwidth * .40;
mapHeight = mapWidth * .9;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = colwidth * .95
inner_listwidth = listwidth *.9;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
fieldwidth = colwidth * .6;
medfieldwidth = colwidth * .3;		
smallfieldwidth = colwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = colwidth + "px";
$('leftcol').style.height = colheight + "px";	
$('rightcol').style.width = colwidth + "px";
$('rightcol').style.height = colheight + "px";	
$('map_canvas').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
$('map_legend').style.width = mapWidth + "px";
$('icon_legend').style.width = mapWidth + "px";
for (var i = 0; i < fields.length; i++) {
	 $(fields[i]).style.width = fieldwidth + "px";
	} 
for (var i = 0; i < medfields.length; i++) {
	 $(medfields[i]).style.width = medfieldwidth + "px";
	}
for (var i = 0; i < smallfields.length; i++) {
	 $(smallfields[i]).style.width = smallfieldwidth + "px";
	}
load_exclusions();
load_ringfences();
load_basemarkup();
load_groupbounds();
set_fontsizes(viewportwidth, "fullscreen");
var latLng;
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", 13, theLocale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
var got_points = false;	// map is empty of points
var doReverse = <?php print intval(get_variable('reverse_geo'));?>;
function onMapClick(e) {
	if(doReverse == 0) {return;}
	if(marker) {map.removeLayer(marker); }
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
	marker.addTo(map);
	newGetAddress(e.latlng, "a");
	};

map.on('click', onMapClick);
<?php
do_kml();
?>
</SCRIPT>
</BODY>
</HTML>
<?php
exit();
