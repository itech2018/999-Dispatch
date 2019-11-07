<?php
error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11
require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
$the_level = (isset($_SESSION['level'])) ? $_SESSION['level'] : 0 ;
require_once($the_inc);
?>

<SCRIPT>
window.onresize=function(){set_size()};
</SCRIPT>
<?php
require_once('./incs/all_forms_js_variables.inc.php');
?>
<SCRIPT>
var theBounds = <?php echo json_encode(get_tile_bounds("./_osm/tiles")); ?>;
var mapWidth;
var mapHeight;
var listHeight;
var leftcolwidth;
var rightcolwidth;
var leftcolheight;
var rightcolheight;
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
var r_interval = null;
var latest_responder = 0;
var do_resp_update = true;
var responders_updated = new Array();
var rmarkers = [];
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
	set_fontsizes(viewportwidth, 'fullscreen');
	if(use_mdb && use_mdb_contact) {show_member_contact_info();}
	mapWidth = viewportwidth * .45;
	mapHeight = mapWidth * .9;
	outerwidth = viewportwidth * .99;
	outerheight = viewportheight * .95;
	leftcolwidth = viewportwidth * 0.45;
	rightcolwidth = viewportwidth * 0.45;
	leftcolheight = outerheight * .95;
	rightcolheight = outerheight * .95;
	colwidth = outerwidth * .42;
	colheight = outerheight * .95;
	listHeight = viewportheight * .7;
	listwidth = colwidth * .95;
	inner_listwidth = listwidth *.9;
	celwidth = listwidth * .20;
	res_celwidth = listwidth * .15;
	fac_celwidth = listwidth * .15;
	$('outer').style.width = outerwidth + "px";
	$('outer').style.height = outerheight + "px";
	$('leftcol').style.width = leftcolwidth + "px";
	$('leftcol').style.height = leftcolheight + "px";	
	$('rightcol').style.width = rightcolwidth + "px";
	$('rightcol').style.height = rightcolheight + "px";	
	$('view_unit').style.width = leftcolwidth * 0.97 + "px";
	$('map_canvas').style.width = mapWidth + "px";
	$('map_caption').style.width = mapWidth + "px";
	$('map_canvas').style.height = mapHeight + "px";
	map.invalidateSize();
	}
	
function loadData() {
	load_exclusions();
	load_ringfences();
	load_basemarkup();
	load_groupbounds();
	load_poly_controls();
	}

function do_disp(){												// show incidents for dispatch - added 6/7/08
	$('incidents').style.display='block';
	$('view_unit').style.display='none';
	}

function do_dispfac(){												// show incidents for dispatch - added 6/7/08
	$('facilities').style.display='block';
	$('view_unit').style.display='none';
	}
	
function to_routes(id) {
	document.routes_Form.ticket_id.value=id;			// 10/16/08, 10/25/08
	document.routes_Form.submit();
	}

function to_fac_routes(id) {
	document.fac_routes_Form.id.value=id;			// 10/6/09
	document.fac_routes_Form.submit();
	}
</SCRIPT>
<?php
$query_un = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 2 AND `resource_id` = '$_GET[id]' ORDER BY `id` ASC;";
$result_un = mysql_query($query_un);	// 6/10/11
$un_groups = array();
$un_names = "";	
while ($row_un = stripslashes_deep(mysql_fetch_assoc($result_un))) 	{	// 6/10/11
	$un_groups[] = $row_un['group'];
	$query_un2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row_un[group]';";	// 6/10/11
	$result_un2 = mysql_query($query_un2);	// 6/10/11
	while ($row_un2 = stripslashes_deep(mysql_fetch_assoc($result_un2))) 	{	// 6/10/11		
		$un_names .= $row_un2['group_name'] . " ";
		}
	}
	

	
$id = mysql_real_escape_string($_GET['id']);
$query	= "SELECT *, r.updated AS `r_updated` FROM `$GLOBALS[mysql_prefix]responder` `r` WHERE `r`.`id` = {$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$track_type = get_remote_type ($row) ;
$is_mobile = (($row['mobile']==1) && ($row['callsign'] != ''));
$lat = $row['lat'];
$lng = $row['lng'];
$ringfence = $row['ring_fence'];
$exclzone = $row['excl_zone'];

$rf_name = "";
$query_rf	= "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l` WHERE `l`.`id`={$ringfence}";	//	6/10/11
$result_rf	= mysql_query($query_rf) or do_error($query_rf, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
while($row_rf	= stripslashes_deep(mysql_fetch_assoc($result_rf))) {
	$rf_name = $row_rf['line_name'];
	}
	
$ex_name = "";
$query_ex	= "SELECT * FROM `$GLOBALS[mysql_prefix]mmarkup` `l` WHERE `l`.`id`={$exclzone}";	//	6/10/11
$result_ex	= mysql_query($query_ex) or do_error($query_ex, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
while($row_ex	= stripslashes_deep(mysql_fetch_assoc($result_ex))) {
	$ex_name = $row_ex['line_name'];
	}
	
if (isset($row['un_status_id'])) {
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id`=" . $row['un_status_id'];	// status value
	$result_st	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$row_st	= mysql_fetch_assoc($result_st);
	unset($result_st);
	}
$un_st_val = (isset($row['un_status_id']))? $row_st['status_val'] : "?";
$un_st_bg = (isset($row['bg_color']))? $row_st['bg_color'] : "white";		// 3/14/10
$un_st_txt = (isset($row['text_color']))? $row_st['text_color'] : "black";
$type_checks = array ("", "", "", "", "", "");
$type_checks[$row['type']] = " checked";
$checked = (!empty($row['mobile']))? " checked" : "" ;

$coords =  $row['lat'] . "," . $row['lng'];		// for UTM

$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
	WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows()>0) {						// got track stuff?
	$rowtr = stripslashes_deep(mysql_fetch_array($result_tr));
	$lat = $rowtr['latitude'];
	$lng = $rowtr['longitude'];
	}

$mob_checked = (!empty($row['mobile']))? " checked" : "" ;				// 1/24/09
$multi_checked = (!empty($row['multi']))? " checked" : "" ;				// 1/24/09
$direcs_checked = (!empty($row['direcs']))? " checked" : "" ;			// 3/19/09
$get_messages = ((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) ? "get_main_messagelist('', {$id}, sortby, sort, '', 'units');" : "";
?>
</HEAD>
<?php
		if ($_dodisp == 'true') {				// dispatch
			print "\t<BODY onLoad = 'loadData(); ck_frames(); do_disp();'> <!-- 3281 do_disp -->\n";
			require_once('./incs/links.inc.php');
			}
		else {
			print "\t<BODY onLoad = 'loadData(); ck_frames()'><!-- 3289  view --> \n";
			require_once('./incs/links.inc.php');
			}
	
?>
<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
<A NAME='top'>		<!-- 11/11/09 -->
<DIV ID='to_bottom' style="position:fixed; top:2px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0></div>
<?php
$temp = $u_types[$row['type']];
$the_type = $temp[0];			// name of type

?>

<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
	<DIV id = "leftcol" style='position: relative; left: 10px; float: left; overflow-y: auto; overflow-x: hidden;'>
		<DIV id = 'fence_flag'></DIV>
		<FORM METHOD="POST" NAME= "res_view_Form" ACTION="<?php print basename(__FILE__);?>?func=responder">
		<TABLE BORDER=0 ID='view_unit'>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>&nbsp;</TD>
			</TR>
			<TR CLASS='even'>
				<TD CLASS='odd' ALIGN='center' COLSPAN='4'>
					<SPAN CLASS='text_green text_biggest'>View Unit&nbsp;<?php print $row['name'] ;?></FONT> (#<?php print $row['id'];?>)</SPAN>
					<BR />
					<SPAN CLASS='text_white'>(mouseover caption for help information)</SPAN>
					<BR />
				</TD>
			</TR>
			<TR class='spacer'>
				<TD class='spacer' COLSPAN=99>&nbsp;</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">Roster User: </TD>		
				<TD CLASS='td_data text'><?php print get_user_details($row['roster_user']);?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Name: </TD>		
				<TD CLASS='td_data text'><?php print $row['name'];?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">Handle: </TD>	
				<TD CLASS='td_data text'><?php print $row['handle'];?>
				<SPAN STYLE = 'margin-left:30px'  CLASS="td_label text"> Icon: </SPAN>&nbsp;<?php print $row['icon_str'];?></TD>
			</TR>
			<TR CLASS = 'even'>
				<TD CLASS="td_label text">Location: </TD>
				<TD CLASS='td_data text'><?php print $row['street'] ;?></TD>
			</TR>
			<TR CLASS = 'odd'>
				<TD CLASS="td_label text">City: &nbsp;&nbsp;&nbsp;&nbsp;</TD>
				<TD CLASS='td_data text'><?php print $row['city'] ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $row['state'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Regions: </TD>			
				<TD CLASS='td_data text'><?php print $un_names;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">Type: </TD>
				<TD CLASS='td_data text'><?php print $the_type;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<SPAN CLASS="td_label text">
						Mobile  &raquo;<INPUT TYPE="checkbox" NAME="frm_mob_disp" <?php print $mob_checked; ?> DISABLED />&nbsp;&nbsp;
						Multiple  &raquo;<INPUT TYPE="checkbox" NAME="frm_multi_disp" <?php print $multi_checked; ?> DISABLED />&nbsp;&nbsp;
						Directions &raquo;<INPUT TYPE="checkbox" NAME="frm_direcs_disp"<?php print $direcs_checked; ?> DISABLED />
					</SPAN>
				</TD>
			</TR>
			<TR CLASS = "even" VALIGN='top'>
				<TD CLASS="td_label text" >Tracking:</TD>
				<TD CLASS='td_data text'><?php print $GLOBALS['TRACK_NAMES'][$track_type];?></TD>
			</TR>
			<TR CLASS = "odd" VALIGN='top'>
				<TD CLASS="td_label text">Callsign/License/Key: </TD>	
				<TD CLASS='td_data text'><?php print $row['callsign'];?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Ringfence: </TD>			
				<TD CLASS='td_data text'><?php print $rf_name;?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">Exclusion Zone: </TD>			
				<TD CLASS='td_data text'><?php print $ex_name;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Status:</TD>		
				<TD CLASS='td_data text'>
					<SPAN STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'><?php print $un_st_val;?></SPAN>
<?php
					$dispatch_arr = array("Yes", "No, not enforced", "No, enforced");
?>
					<SPAN CLASS="td_label text" STYLE='margin-left: 32px'>Dispatch:&nbsp;</SPAN><?php print $dispatch_arr[$row_st['dispatch']];?>
				</TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">About Status</TD> 
				<TD CLASS='td_data text'><?php print $row['status_about'] ;?></TD>
			</TR>	<!-- 9/6/13 -->
			<TR CLASS = "even">
				<TD CLASS="td_label text">Description: </TD>	
				<TD CLASS='td_data_wrap' style='word-wrap: break-word;'><?php print $row['description'];?></TD>
			</TR>
			<TR CLASS = "odd">
				<TD CLASS="td_label text">Capability: </TD>	
				<TD CLASS='td_data text'><?php print $row['capab'];?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Located at Facility: </TD>	
				<TD CLASS='td_data text'><?php print get_facilityname($row['at_facility']);?></TD>
			</TR>
			<TR ID = 'members_info_row' CLASS = "odd" style='display: none;'>
				<TD CLASS="td_label text top">
					<A CLASS="td_label text" HREF="#" TITLE="Member Data">Member Information</A>:&nbsp;					
				</TD>
				<TD CLASS='td_data_wrap text'>
					<DIV class='text top' id='member_info_div' style='vertical-align: text-top; max-height: 200px; width: 100%;'>
<?php
						$theName = (is_array(get_mdb_names($id))) ? implode(" , ", get_mdb_names($id)) : get_mdb_names($id);
						$contactVia = (is_array(get_contact_via($id))) ? implode(" | ", get_contact_via($id)) : get_contact_via($id);
						$thePhone = (is_array(get_mdb_phone($id))) ? implode(",", get_mdb_phone($id)) : get_mdb_phone($id);
						$cellphone = (is_array(get_mdb_cell($id))) ? implode(" , ", get_mdb_cell($id)) : get_mdb_cell($id);
						$smsgid = (is_array(get_smsgid($id))) ? implode(" | ", get_smsgid($id)) : get_smsgid($id);
?>
						<SPAN CLASS='td_label text top' style='width: 25%; display: inline-block;' TITLE="Member Names assigned to this unit.">Contact Names</SPAN><SPAN class='td_data_wrap text top' style='width: 70%;'><?php print $theName;?></SPAN><BR />
						<SPAN CLASS='td_label text top' style='width: 25%; display: inline-block;' TITLE="Contact emails for units assigned to this unit.">Contact Via</SPAN><SPAN class='td_data_wrap text top' style='width: 70%; display: inline-block; word-wrap: break-word;'><?php print $contactVia;?></SPAN><BR />
						<SPAN CLASS='td_label text top' style='width: 25%; display: inline-block;' TITLE="Phone numbers of members assigned to this unit.">Phone</SPAN><SPAN class='td_data_wrap text top' style='width: 70%; display: inline-block; word-wrap: break-word;'><?php print $thePhone;?></SPAN><BR />
						<SPAN CLASS='td_label text top' style='width: 25%; display: inline-block;' TITLE="Cellphone numbers of members assigned to this unit.">Cellphone</SPAN><SPAN class='td_data_wrap text top' style='width: 70%; display: inline-block;'><?php print $cellphone;?></SPAN><BR />
						<SPAN CLASS='td_label text top' style='width: 25%; display: inline-block;' TITLE="SMS Gateway IDs for Members assigned to this unit - this is not the cellphone number but the short ID for the Gateway Provider - If provider uses Cellphones as IDs use the Handle here.">SMS Gateway ID</SPAN><SPAN class='td_data_wrap text top' style='width: 70%; display: inline-block;'><?php print $smsgid;?></SPAN><BR />								
					</DIV>
				</TD>
			</TR>
			<TR ID = 'contact_name_row' CLASS = "even">
				<TD CLASS="td_label text">Contact name:</TD>	
				<TD CLASS='td_data text'><?php print $row['contact_name'] ;?></TD>
			</TR>
			<TR ID = 'contact_via_row' CLASS = "odd">
				<TD CLASS="td_label text">Contact via:</TD>	
				<TD CLASS='td_data text'><?php print $row['contact_via'] ;?></TD>
			</TR>
			<TR CLASS = "even">
				<TD CLASS="td_label text">Phone: &nbsp;</TD>
				<TD CLASS='td_data text' COLSPAN=3><?php print $row['phone'] ;?></TD>
			</TR>
			<TR ID = 'cellphone_row' CLASS = "odd">
				<TD CLASS="td_label text">Cellphone:</TD>	
				<TD CLASS='td_data text'><?php print $row['cellphone'] ;?></TD>
			</TR>
			<TR ID = 'smsg_provider_row' CLASS = "even">
				<TD CLASS="td_label text"><?php get_provider_name(get_msg_variable('smsg_provider'));?> ID:</TD>	
				<TD CLASS='td_data text'><?php print $row['smsg_id'] ;?></TD>
			</TR>
			<TR CLASS = 'odd'>
				<TD CLASS="td_label text">As of:</TD>	
				<TD CLASS='td_data text'><?php print format_date($row['updated']); ?></TD>
			</TR>
<?php
			if (my_is_float($lat)) {				// 7/10/09
?>		
				<TR CLASS = "even">
					<TD CLASS="td_label text"  onClick = 'javascript: do_coords(<?php print "$lat,$lng";?>)'><U>Lat/Lng</U>:</TD>
					<TD CLASS='td_data text'>
						<INPUT TYPE="text" NAME="show_lat" VALUE="<?php print get_lat($lat);?>" SIZE=11 disabled />&nbsp;
						<INPUT TYPE="text" NAME="show_lng" VALUE="<?php print get_lng($lng);?>" SIZE=11 disabled />&nbsp;

<?php
					$locale = get_variable('locale');	// 08/03/09
					switch($locale) { 
						case "0":
?>
							&nbsp;USNG:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUSNG($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
<?php 		
							break;

						case "1":
?>
							&nbsp;OSGB:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoOSGB($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
<?php
							break;

						case "2":
?>
							&nbsp;UTM:<INPUT TYPE="text" NAME="frm_ngs" VALUE="<?php print LLtoUTM($row['lat'], $row['lng']) ;?>" SIZE=19 disabled />
<?php
							break;			
						default:
							print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";				

						}

				}		// end if (my_is_float($lat))
?>
				</TD>
			</TR>
<?php
if (isset($rowtr)) {																	// got tracks?
	print "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>TRACKING</B></TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Course: </TD><TD CLASS='td_data text'>" . $rowtr['course'] . ", Speed:  " . $rowtr['speed'] . ", Alt: " . $rowtr['altitude'] . "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>Closest city: </TD><TD CLASS='td_data text'>" . $rowtr['closest_city'] . "</TD></TR>";
	print "<TR CLASS='odd'><TD CLASS='td_label'>Status: </TD><TD CLASS='td_data text'>" . $rowtr['status'] . "</TD></TR>";
	print "<TR CLASS='even'><TD CLASS='td_label'>As of: </TD><TD CLASS='td_data text'>" . format_date($rowtr['packet_date']) . " (UTC)</TD></TR>";
	$lat = $rowtr['latitude'];
	$lng = $rowtr['longitude'];
	}

?>
			<TR>
				<TD>&nbsp;</TD>
			</TR>
			<TR class='even'>
				<TD COLSPAN=99>

					<TABLE WIDTH='100%'>
						<TR>
							<TD WIDTH='100%'>
								<?php print show_assigns(1,$row['id']);?>
								<?php print show_unit_log($row['id']);?>
							</TD>
						</TR>
						<TR class='spacer'>
							<TD class='spacer'></TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
		</TABLE>

		<INPUT TYPE="hidden" NAME="frm_lat" VALUE="<?php print $lat;?>" />
		<INPUT TYPE="hidden" NAME="frm_lng" VALUE="<?php print $lng;?>" />
		<INPUT TYPE="hidden" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" />
		</FORM>
		<TABLE BORDER=0 ID = 'incidents' STYLE = 'display:none' >
			<TR CLASS='even'>
				<TH COLSPAN=99 CLASS='header'> Click incident to dispatch '<?php print $row['handle'] ;?>'</TH>
			</TR>
			<TR>
				<TD></TD>
			</TR>

<?php
										// 11/15/09 - identify candidate incidents - i. e., open and not already assigned to this unit
			$query_t = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `responder_id` = {$row['id']}  AND ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00'))";
			$result_temp = mysql_query($query_t) or do_error($query_t, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$ctr = 0;		// count hits
			if (mysql_affected_rows()>0) {
			$work = $sep = "";
			$ctr = 0;		// count hits
			while ($row_temp = stripslashes_deep(mysql_fetch_array($result_temp))) {
				if (!(is_date($row_temp['clear']))) {
					$ctr++;										// if open
					$work .= $sep . $row_temp['ticket_id'];
					$sep = ", ";								// set comma separator for next
					}					// end if (is_date())
				}					// end while ($row_temp)
			}					// end if (mysql_affected_rows()>0)

			$instr = ($ctr == 0)? "" : " AND `$GLOBALS[mysql_prefix]ticket`.`id` NOT IN ({$work})";

			$al_groups = $_SESSION['user_groups'];

			if(!isset($curr_viewed)) {		//	7/2/13	revised WHERE to AND - Where clause was repeated
			if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
				} else {			
				$x=0;	//	6/10/11
				$where2 = "AND (";	//	6/10/11
				foreach($al_groups as $grp) {	//	6/10/11
					$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
				$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 AND (`$GLOBALS[mysql_prefix]allocates`.`al_status` = 1 OR `$GLOBALS[mysql_prefix]allocates`.`al_status` = 2)";	//	6/10/11
				}
			} else {
			if(count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
				$where2 = "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
				} else {					
				$x=0;	//	6/10/11
				$where2 = "AND (";	//	6/10/11
				foreach($curr_viewed as $grp) {	//	6/10/11
					$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
					$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
					$where2 .= $where3;
					$x++;
					}
				$where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1 AND (`$GLOBALS[mysql_prefix]allocates`.`al_status` = 1 OR `$GLOBALS[mysql_prefix]allocates`.`al_status` = 2)";	//	6/10/11
				}
			}

			$query_t = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` 
					LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON `$GLOBALS[mysql_prefix]ticket`.id=`$GLOBALS[mysql_prefix]allocates`.`resource_id`	
			WHERE `status` IN ({$GLOBALS['STATUS_OPEN']}, {$GLOBALS['STATUS_SCHEDULED']}) {$instr} {$where2}
			GROUP BY `$GLOBALS[mysql_prefix]ticket`.`id`";	//	6/10/11
			$result_t = mysql_query($query_t) or do_error($query_t, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$i=0;			
			while ($row_t = stripslashes_deep(mysql_fetch_array($result_t))) 	{
//			dump($row_t);
				switch($row_t['severity'])		{								//color tickets by severity
					case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
					case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
					default: 							$severityclass='severity_normal'; break;
					}

				print "\t<TR CLASS ='" .  $evenodd[($i+1)%2] . "' onClick = 'to_routes(\"" . $row_t[0] . "\")'>\n";		//	6/13/13 Revised to remove id conflict.
				print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['scope']}'>" . 						shorten($row_t['scope'], 24) . "</TD>\n";
				print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['description']}'>" . 				shorten($row_t['description'], 24) . "</TD>\n";
				print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['street']} {$row_t['city']}'>" . 	shorten($row_t['street'], 24) . "</TD>\n";
				print "\t\t<TD CLASS='{$severityclass}' TITLE ='{$row_t['city']}'>" . 						shorten($row_t['city'], 8). "</TD>";
				print "\t\t</TR>\n";
				$i++;
				}				// end while ($row_t ... )

				print ($i>0)? "" : "<TR><TD COLSPAN=99 ALIGN='center'><BR />No incidents available</TD></TR>\n";
?>
			<TR>
				<TD ALIGN="center" COLSPAN=99><BR /><BR />
					<INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>" onClick = "$('incidents').style.display='none'; $('view_unit').style.display='block';">
				</TD>
			</TR>
		</TABLE>
	</DIV>
	<DIV ID="middle_col" style='position: relative; left: 20px; width: 110px; float: left;'>&nbsp;
		<DIV style='position: relative; top: 50px; z-index: 1;'>
<?php
			if(is_administrator() || is_super()) {
?>
				<SPAN id='edit_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='to_edit_Form.submit();'><?php print get_text("Edit");?><BR /><IMG id='edit_img' SRC='./images/edit.png' /></SPAN>
<?php
				$disp_allowed = ($row_st['dispatch']==3)?  false : true;
				if(!is_guest() && $disp_allowed) {
?>
					<SPAN id='disp_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="$('incidents').style.display='block'; $('view_unit').style.display='none';"><?php print get_text("To Dispatch");?><BR /><IMG id='disp_img' SRC='./images/dispatch.png' /></SPAN>
					<SPAN id='dispfac_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick="to_fac_routes(<?php print $id;?>);"><?php print get_text("To Facility");?><BR /><IMG id='dispfac_img' SRC='./images/dispatch.png' /></SPAN>
<?php
					}
				if(!is_guest()) {
?>					
					<SPAN id='log_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='unit_log(<?php print $id;?>);'><?php print get_text("Log");?><BR /><IMG id='log_img' SRC='./images/edit.png' /></SPAN>
<?php
					}
				}
?>
			<SPAN id='can_but' CLASS='plain_centerbuttons text' style='float: none; width: 80px; display: block;' onMouseover='do_hover_centerbuttons(this.id);' onMouseout='do_plain_centerbuttons(this.id);' onClick='document.can_Form.submit();'><?php print get_text("Cancel");?><BR /><IMG id='can_img' SRC='./images/cancel.png' /></SPAN>
		</DIV>	
	</DIV>
	<DIV id='rightcol' style='position: relative; left: 20px; float: left;'>
		<DIV id='map_canvas' style='border: 1px outset #707070;'></DIV>
<SCRIPT>
		var controlsHTML = "<TABLE id='controlstable' ALIGN='center'>";
		controlsHTML += "<SPAN class='heading' style='width: 100%; text-align: center; display: inline-block;'>Map Controls</SPAN></BR>";
		controlsHTML +=	"<TR class='even'><TD><CENTER><TABLE ID='buttons_sh' style='display: inline-block;'>";
		controlsHTML +=	"<TR CLASS='odd'><TD><DIV ID = 'poly_boxes' ALIGN='center' VALIGN='middle' style='text-align: center; vertical-align: middle;'></DIV></TD></TR></TABLE></CENTER></TD></TR></TABLE>";
</SCRIPT>
		<SPAN id='map_caption' CLASS='text_blue text text_bold' style='width: 100%; text-align: center; display: block;'><?php print get_variable('map_caption');?></SPAN><BR />
	</DIV>
	<DIV id='memberview' class='even' style='position: fixed; top: 100px; left: 30%; width: 50%; height: 60%; display: none; overflow-y: scroll; border: 4px outset #707070; z-index: 99999; box-shadow: 0px 0px 0px 15px rgba(0, 0, 0, 0.3), 0px 20px 15px 0px rgba(0, 0, 0, 0.6);'>
		<SPAN id='memberview_close' class='plain text' style='position: absolute; right: 0px; top: 0px; display: inline;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onclick="$('memberview').style.display = 'none';">Close&nbsp;&nbsp;<IMG id='memclose_img' style='vertical-align: middle;' SRC='./images/close.png' /></SPAN>
		<DIV id='memberdetails' style='position: absolute; top: 30px;'></DIV>
	</DIV>
</DIV>
<?php
$allow_filedelete = ($the_level == $GLOBALS['LEVEL_SUPER']) ? TRUE : FALSE;
print add_sidebar(FALSE, TRUE, TRUE, TRUE, TRUE, $allow_filedelete, 0, $id, 0, 0)
?>
<FORM NAME='can_Form' METHOD="post" ACTION = "units.php"></FORM>
<FORM NAME="to_edit_Form" METHOD="post" ACTION = "units.php?func=responder&edit=true&id=<?php print $id; ?>"></FORM>
<FORM NAME="routes_Form" METHOD="get" ACTION = "<?php print $_SESSION['routesfile'];?>"> <!-- 8/31/10 -->
<INPUT TYPE="hidden" NAME="ticket_id" 	VALUE="">						<!-- 10/16/08 -->
<INPUT TYPE="hidden" NAME="unit_id" 	VALUE="<?php print $id; ?>">
</FORM>
<FORM NAME="fac_routes_Form" METHOD="get" ACTION = "fac_routes.php">
<INPUT TYPE="hidden" NAME="fac_id" 	VALUE="">
<INPUT TYPE="hidden" NAME="stage" VALUE=1>
<INPUT TYPE="hidden" NAME="id" 	VALUE="">
</FORM>
<A NAME="bottom" />
<DIV ID='to_top' style="position:fixed; bottom:50px; left:50px; height: 12px; width: 10px;" onclick = "location.href = '#top';"><IMG SRC="markers/up.png"  BORDER=0></div>
<SCRIPT>
var latLng;
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
set_fontsizes(viewportwidth, 'fullscreen');
if(use_mdb && use_mdb_contact) {show_member_contact_info();}
mapWidth = viewportwidth * .45;
mapHeight = mapWidth * .9;
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
leftcolwidth = viewportwidth * 0.45;
rightcolwidth = viewportwidth * 0.45;
leftcolheight = outerheight * .95;
rightcolheight = outerheight * .95;
colwidth = outerwidth * .42;
colheight = outerheight * .95;
listHeight = viewportheight * .7;
listwidth = colwidth * .95;
inner_listwidth = listwidth *.9;
celwidth = listwidth * .20;
res_celwidth = listwidth * .15;
fac_celwidth = listwidth * .15;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('leftcol').style.width = leftcolwidth + "px";
$('leftcol').style.height = leftcolheight + "px";
$('rightcol').style.width = rightcolwidth + "px";
$('rightcol').style.height = rightcolheight + "px";
$('view_unit').style.width = leftcolwidth * 0.97 + "px";
$('map_canvas').style.width = mapWidth + "px";
$('map_caption').style.width = mapWidth + "px";
$('map_canvas').style.height = mapHeight + "px";
var boundary = [];			//	exclusion zones array
var bound_names = [];
var theLocale = <?php print get_variable('locale');?>;
var useOSMAP = <?php print get_variable('use_osmap');?>;
var initZoom = <?php print get_variable('def_zoom');?>;
init_map(3, <?php print $lat;?>, <?php print $lng;?>, "", parseInt(initZoom), theLocale, useOSMAP, "tr");
var bounds = map.getBounds();	
var zoom = map.getZoom();
load_exclusions();
load_ringfences();
load_basemarkup();
load_groupbounds();
load_poly_controls();
$('controls').innerHTML = controlsHTML;
<?php
do_kml();
?>
</SCRIPT>
</BODY>
</HTML>
<?php
if((is_super()) || (is_administrator()) || (is_user())) {	//	10/28/10 Added for add on modules
	if(file_exists("./incs/modules.inc.php")) {
		$handle=$row['handle'];
		get_modules('view_form');
		}
	}	
exit();
