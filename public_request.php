<?php

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
$day_night = "Day";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]states_translator`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$states[$row['name']] = $row['code'];
	}
	
$rescue_types = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]rescue_type`";
$result	= mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
	$rescue_types[$row['id']] = $row['name'];
	}	

?>
<!DOCTYPE HTML>															  
<HTML>
<HEAD>
	<TITLE>Tickets - Requests Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<STYLE>
	/* Core Elements */
	BODY 			{ background-color: <?php print get_css("page_background", $day_night);?>; margin:0; font-weight: normal; font-style: normal; font-size: 1.4em; 
					color: <?php print get_css("normal_text", $day_night);?>; font-family: Arial, Verdana, Geneva, "Trebuchet MS", Tahoma, Helvetica, sans-serif;}
	INPUT 			{background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal; color: <?php print get_css("form_input_text", $day_night);?>;
					-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; font-size = 1.2em;}
	INPUT:focus 	{background-color: yellow;}
	INPUT:disabled 	{background-color: #CECECE; color: #000000;}
	TEXTAREA 		{background-color: <?php print get_css("form_input_background", $day_night);?>; font-weight: normal; color: <?php print get_css("form_input_text", $day_night);?>; font-size = 1.2em;}
	TEXTAREA:focus 	{background-color: yellow;}
	FIELDSET 		{margin: 0 0 20px; padding: 1em; border: 3px inset #FFFFFF; border-radius: 20px 20px; background-color: <?php print get_css("row_light", $day_night);?>;}
	LABEL 			{margin-left: 5px; width: 90%; display: inline-block; vertical-align: top; font-weight: bold; padding: 2px; text-align: left; font-size: 1.4em}
	LEGEND 			{margin-left: 5px; width: 90%; font-weight: bold; padding: 5px; background: #0000FF; border: 3px inset #FFFFFF; color: #FFFFFF; border-radius: 20px 20px; font-size: 1.6em;}
	SELECT 			{background-color: <?php print get_css("select_menu_background", $day_night);?>; font-weight: normal;; 
					color: <?php print get_css("select_menu_text", $day_night);?>; text-decoration: underline; font-size: 1.4em;}
	OPTION 			{font-weight: normal; font-size: 1.4em;}
	A 				{font-weight: bold; color: <?php print get_css("links", $day_night);?>;}
	.radioopt		{margin-left: 2px; display: inline-block; font-weight: bold; padding: 2px; text-align: left; font-size: 1.4em;}
	</STYLE>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/leaflet/leaflet.js"></SCRIPT>
	<SCRIPT TYPE="application/x-javascript" SRC="./js/Control.Geocoder.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size();}

var viewportwidth, viewportheight, outerwidth, outerheight, leftcolwidth, fields, medfields, smallfields, fieldwidth, medfieldwidth, smallfieldwidth;

var fields = ["firstname", "lastname", "email", "phone", "backupphone", "street", "city", "description"];
var medfields = ["rescue_type", "postcode", "pets_about"];
var smallfields = ["adults", "children", "elderly", "pets", "state"];

var geo_provider = <?php print get_variable('geocoding_provider');?>;
var locale = <?php print get_variable('locale');?>;
var states_arr = <?php echo json_encode($states); ?>;

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
	outerwidth = viewportwidth * .85;
	leftcolwidth = outerwidth * .90;
	fieldwidth = leftcolwidth * .8;
	medfieldwidth = leftcolwidth * .5;		
	smallfieldwidth = leftcolwidth * .3;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	for (var i = 0; i < fields.length; i++) {
		if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
		} 
	for (var i = 0; i < medfields.length; i++) {
		if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
		}
	for (var i = 0; i < smallfields.length; i++) {
		if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
		}
	}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
		}
	}
	
function showPosition(position) {
	var the_lat = position.coords.latitude;
	var the_lng = position.coords.longitude;
	if(document.request_form.frm_lat) {document.request_form.frm_lat.value = the_lat;}
	if(document.request_form.frm_lng) {document.request_form.frm_lng.value = the_lng;}
	var latLng = new L.LatLng(the_lat ,the_lng);
	do_geolocate(latLng, the_lat, the_lng);
	}

function do_geolocate(latLng, the_lat, the_lng) {
	var control = new L.Control.Geocoder();
	var theCity = "";
	control.options.geocoder.reverse(latLng, 20, function(results) {
		if(!results) {alert("Try again"); return;}
		if(window.geo_provider == 0){
			var r1 = results[0]; 
			var r = r1['properties']['address'];
			if(r.neighbourhood && r.neighbourhood != "") {
				theCity = r.neighbourhood;
				} else if(r.suburb && r.suburb != "") {
				theCity = r.suburb;
				} else if(r.town && r.town != "") {
				theCity = r.town;
				}
			theCity = r.city;
			} else if(window.geo_provider == 1) {
			var r = results[0].properties.address;
			if(!r.city) {
				if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
					theCity = r.locality;
					} else {
					theCity = "";
					}
				}
			} else if(window.geo_provider == 2) {
			var r = results[0]; 
			if(!r.city) {
				if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
					theCity = r.locality;
					} else {
					theCity = "";
					}
				}
			}
		if(!r.state) {
			if(r.county) {
				var state = r.county;
				} else {
				var state = "";
				}
			} else {
			var state = r.state;
			}
		if(!theCity) {
			var theCity = "";
			}
		var ausStates = ['New South Wales','Queensland','NSW','QLD','Northern Territory','Western Australia','South Australia','Victoria','Tasmania'];	//	Australian State full names in array
		var ausStatesAbb = ['NSW','QLD','NSW','QLD','NT','WA','SA','Vic','Tas'];	//	Australian State abbreviations in array
		var auskey = ausStates.indexOf(state);	//	Checks if current reported state is an Australian one.
		if(auskey != -1) {state = ausStatesAbb[auskey];}	//	if State is Australian, converts full name to abbreviation.
		if (r) {
			var street = (r.road) ? r.road : "";
			var number = (r.house_number) ? r.house_number : "";
			var theStreet1 = (number != "") ? number + " " : "";
			var theStreet2 = (street != "") ? street : "";
			if(locale == 0) {
				state = (state != "" && state.length > 2) ? states_arr[state] : state;
				}
			if(locale == 1) {state = "UK";}
			address = number + " " + street + " " + theCity + " " + state;
			if(document.request_form.frm_street) {document.request_form.frm_street.value = number + " " + street;}
			if(document.request_form.frm_city) {document.request_form.frm_city.value = theCity;}
			if(document.request_form.frm_state) {document.request_form.frm_state.value = state;}
			}
		});
	}
	
	function validate(theForm) {
		var errmsg="";
		if (theForm.frm_firstname.value == "")	{errmsg+= "First Name is required\n";}
		if (theForm.frm_lastname.value=="") {errmsg+= "Last Name is required\n";}
		if (theForm.frm_phone.value=="") {errmsg+= "Phone number is required\n";}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			} else {
			document.request_form.submit();			
			return true;
			}
		}				// end function validate(theForm)
</SCRIPT>
</HEAD>
<?php

if(!empty($_POST)) {
	extract($_POST);
	$delta = (!empty(get_variable('delta_mins'))) ? get_variable('delta_mins') : 0;
	$now = mysql_format_date(time() - ($delta*60));
	$where = $_SERVER['REMOTE_ADDR'];
	$theName = $frm_firstname . " " . $frm_lastname;
	$lat = ($_POST['frm_lat'] == "") ? "0.999999" : $_POST['frm_lat'];
	$lng = ($_POST['frm_lng'] == "") ? "0.999999" : $_POST['frm_lng'];
	$description = ($frm_backupphone != "") ? $frm_description . "\r\nBackup Phone: " . $frm_backupphone : $frm_description;
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
				`org`,
				`contact`, 
				`email`,
				`street`, 
				`city`, 
				`postcode`,
				`state`, 
				`the_name`, 
				`phone`, 
				`to_address`,
				`pickup`,
				`arrival`,
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
				`water`,
				`power`,
				`danger`,
				`food`,
				`health`,
				`children`,
				`adults`,
				`elderly`,
				`livestock`,
				`livestock_about`,
				`rescue_type`,
				`_by`, 
				`_on`, 
				`_from` 
				) VALUES (
				0,
				'" . addslashes($theName) . "',
				'" . addslashes($frm_email) . "',
				'" . addslashes($frm_street) . "',	
				'" . addslashes($frm_city) . "',	
				'" . addslashes($frm_postcode) . "',	
				'" . addslashes($frm_state) . "',	
				'" . addslashes($theName) . "',
				'" . addslashes($frm_phone) . "',
				'',
				'',
				'',				
				0,					
				0,	
				'New Public Request',
				'" . addslashes($description) . "',					
				'',		
				'" . $lat . "',	
				'" . $lng . "',				
				'" . $frm__on . "',
				'Open',
				NULL,
				NULL,
				NULL,
				NULL,
				NULL,
				99999,
				'" . $frm_water . "',
				'" . $frm_power . "',
				'" . $frm_danger . "',
				'" . $frm_food . "',				
				'" . $frm_health . "',
				'" . $frm_children . "',
				'" . $frm_adults . "',
				'" . $frm_elderly . "',			
				'" . $frm_livestock . "',	
				'" . $frm_livestock_about . "',
				'" . $frm_rescue_type . "',
				99999,
				'" . $now . "',
				'" . $where . "')";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if($result) {
		do_log($GLOBALS['LOG_NEW_REQUEST'], 99999);
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
		$the_summary = "New Request from " . $theName . "\r\n";
		$the_summary .= get_text('Scope') . ": New public help request\r\n\r\n";
		$the_summary .= get_text('Rescue Type') . ": " . $rescue_types[$frm_rescue_type] . "\r\n";
		$the_summary .= get_text('Patient') . ": " . $theName . "\r\n";
		$the_summary .= get_text('Imminent Danger') . ": " . $frm_danger . "\r\n";
		$the_summary .= get_text('Power') . " : " . $frm_power . "\r\n";
		$the_summary .= get_text('Water') . ": " . $frm_water . "\r\n";
		$the_summary .= get_text('Food') . ": " . $frm_food . "\r\n";		
		$the_summary .= get_text('Health conditions') . ": " . $frm_health . "\r\n";
		$the_summary .= get_text('Other Persons') . " Adults: " . $frm_adults . " Children: " . $frm_children . " Elderly: " . $frm_elderly . "\r\n";
		$the_summary .= get_text('Livestock') . ": " . $frm_livestock . "\r\n";
		$the_summary .= get_text('Livestock Details') . ": " . $frm_livestock_about . "\r\n";				
		$the_summary .= get_text('Street') . ": " . $frm_street . ", ";	
		$the_summary .= get_text('City') . ": " . $frm_city . ", ";	
		$the_summary .= get_text('Postcode') . ": " . $frm_postcode . ", ";	
		$the_summary .= get_text('State') . ": " . $frm_state . "\r\n";	
		$the_summary .= get_text('Contact Phone') . ": " . $frm_phone . "\r\n";
		$the_summary .= get_text('Other Contact Phone') . ": " . $frm_backupphone . "\r\n";
		$the_summary .= get_text('Description') . "\r\n" . $frm_description . "\r\n";	
		$the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($now)) . "\r\n";

		
		$job = "<TABLE STYLE='width: 100%;'><TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Patient') . " name: </TD><TD CLASS='td_data text' STYLE='background-color: white; color: black;'>" . $theName . "</TD></TR>";
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Rescue Type') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $rescue_types[$frm_rescue_type] . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Street') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_street . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('City') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_city . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Postcode') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_postcode . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('State') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_state . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Contact Phone') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_phone . "</TD></TR>";
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Description') . "</TD><TD CLASS='td_data_wrap text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_description . "</TD></TR>";	
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Request Date') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . format_date_RFC850(strtotime($now)) . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Imminent Danger') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_danger . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Power') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_power . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Water') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_water . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Food') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_food . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Health Conditions') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_health . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Other Persons') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>Adults: " . $frm_adults . "<BR />Children: " . $frm_children . "<BR />Elderly: " . $frm_elderly . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Livestock') . ": </TD><TD CLASS='td_data text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_livestock . "</TD></TR>";		
		$job .= "<TR style='border-bottom: 1px solid #707070;'><TD CLASS='td_label text text_valign_top'>" . get_text('Livestock Details') . ": </TD><TD CLASS='td_data_wrap text text_valign_top' STYLE='background-color: white; color: black;'>" . $frm_livestock_about . "</TD></TR>";		
		$job .= "</TABLE>";		

		$addrs = notify_newreq(99999, $frm_state);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
			$to_str1 = implode("|", $addrs);
			$smsg_to_str1 = "";
			$subject_str1 = "New Public Request";
			$text_str1 = "A new request has been loaded by " . $theName . " Dated " . $now . ". \r\nPlease log on to Tickets and check\n\n"; 
			$text_str1 .= "Request Summary\r\n" . $the_summary;
			do_send ($to_str1, $smsg_to_str1, $subject_str1, $text_str1, 0, 0);
			}				// end if/else ($addrs)	
		if ($frm_email != "") {				// any addresses?
			$to_str2 = $frm_email;
			$smsg_to_str2 = "";
			$subject_str2 = "Your request has been registered";
			$text_str2 = "Your Help Request has been registered\r\n"; 
			$text_str2 .= "Request Summary\n\n" . $the_summary;
			do_send ($to_str2, $smsg_to_str2, $subject_str2, $text_str2, 0, 0);	
			}				// end if/else ($the_email)	
		$ret_arr[0] = 100;
		$ret_arr[1] = $to_str1;
		$ret_arr[2] = $smsg_to_str1;
		$ret_arr[3] = $subject_str1;
		$ret_arr[4] = $text_str1;	
		$ret_arr[5] = $to_str2;
		$ret_arr[6] = $smsg_to_str2;
		$ret_arr[7] = $subject_str2;
		$ret_arr[8] = $text_str2;
		$ret_arr[9] = $job;
		} else {
		$ret_arr[0] = 999;
		}
	if($ret_arr[0] != 999) {
?>
	<BODY>
		<DIV id = "outer" style='position: absolute; left: 0px; display: block; width: 100%; height: 100%; text-align: center;'>
			<SPAN class = 'td_data_wrap text_large text_bold' style = "position: relative; top: 20%; width: 100%; display: block;">Your request has been submitted. You will receive email updates to the email address you provided (<?php print $frm_email;?>)</SPAN><BR /><BR />
			<SPAN class = 'td_data_wrap text text_left' style = "position: relative; top: 20%; left: 40%; width: 20%; display: block; border: 1px outset #707070; padding: 10px;"><?php print $ret_arr[9];?></SPAN>
			<SPAN id='fin_but' class='plain text' roll='button' aria-label='Finish' style='position: relative; top: 60%; float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finish");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
		</DIV>
<?php
		}

	} else {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]rescue_type` ORDER BY `name` ASC";
	$result = mysql_query($query);
	$type_select = "<SELECT ID='rescue_type' TABINDEX=15 NAME='frm_rescue_type'>";
	$type_select .= "<OPTION VALUE=0 SELECTED>Select One</OPTION>";
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$type_select .= "<OPTION VALUE=" . $row['id'] . ">" . $row['name'] . "</OPTION>";
		}
	$type_select .= "</SELECT>";		

	$delta = (!empty(get_variable('delta_mins'))) ? get_variable('delta_mins') : 0;
	$now = mysql_format_date(time() - ($delta*60));
?>
	<BODY onLoad='getLocation();'>
	<SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
		<A NAME='top'></A>
		<DIV ID = "to_bottom" roll='button' aria-label='To Bottom' style='position:fixed; top: 40px; right: 0px; height: 12px; width: 10px; z-index: 50; cursor: pointer;' onclick = "location.href = '#bottom';"><IMG SRC='./markers/down.png'  BORDER=0 /></DIV>
		<DIV id = "outer" style='position: absolute; left: 0px; display: block;'>
			<IMG id='head_img' style='position: fixed; left: 0px; top: 0px; display: block; width: 8%; z-index: 100;' src="<?php print get_variable('report_graphic');?>" />
			<DIV ID='pageheader' CLASS='but_container' style = "position: fixed; top: 0px; left: 0px; height: auto; width: 100%;text-align: center; display: block; z-index: 99;">
				<SPAN class='text_massive'>Service Request Form</SPAN>
			</DIV>
			<DIV id = "leftcol" style='position: relative; left: 10%; top: 80px; float: left;'>			
				<FORM METHOD="POST" NAME= "request_form" ACTION="public_request.php" onSubmit="return validate(document.request_form)>
				<FIELDSET>
					<LEGEND style='text-align: center;' class='text_large text_bold'>Personal Information</LEGEND>
					<LABEL for="firstname" onmouseout="UnTip()" onmouseover="Tip('First Name');"><?php print get_text("First Name");?>: <font color='red' size='-1'>*</font></LABEL>
					<INPUT id='firstname' NAME="frm_firstname" tabindex=1 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="lastname" onmouseout="UnTip()" onmouseover="Tip('Last Name');"><?php print get_text("Last Name");?>: <font color='red' size='-1'>*</font></LABEL>
					<INPUT id='lastname' NAME="frm_lastname" tabindex=2 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="email" onmouseout="UnTip()" onmouseover="Tip('Your email address or email address where updates should be sent to.');"><?php print get_text("Email Address"); ?>: </LABEL>
					<INPUT id='email' NAME="frm_email" tabindex=3 TYPE="email" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="phone" onmouseout="UnTip()" onmouseover="Tip('Your contact phone number.');"><?php print get_text("Contact Phone"); ?>: <font color='red' size='-1'>*</font></LABEL>
					<INPUT id='phone' NAME="frm_phone" tabindex=4 TYPE='text' VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="backupphone" onmouseout="UnTip()" onmouseover="Tip('Backup phone number so that we can let someone else know when the rescue has been completed.');"><?php print get_text("Backup Phone"); ?>: </LABEL>
					<INPUT id='backupphone' NAME="frm_backupphone" tabindex=5 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
				</FIELDSET>
				<FIELDSET style='text-align: center;'>
					<LEGEND style='text-align: center;' class='text_large text_bold'>Requirements</LEGEND>
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Are you in imminent danger of death?.');"><?php print get_text("Imminent danger of death");?> ?:</LABEL><BR />
					<INPUT TYPE="radio" NAME="frm_danger" VALUE="No" CHECKED><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_danger" VALUE="Yes"><SPAN class='radioopt'>Yes</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Do you have power?.');"><?php print get_text("Do you have power");?> ?:</LABEL><BR />
					<INPUT TYPE="radio" NAME="frm_power" VALUE="No"><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_power" VALUE="Yes" CHECKED><SPAN class='radioopt'>Yes</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Do you have enough water for the next 8 hours?.');"><?php print get_text("Do you have water");?> ?:</LABEL><BR />
					<INPUT TYPE="radio" NAME="frm_water" VALUE="No"><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_water" VALUE="Yes" CHECKED><SPAN class='radioopt'>Yes</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Do you have enough food for the next 8 hours?.');"><?php print get_text("Do you have food");?> ?:</LABEL><BR />
					<INPUT TYPE="radio" NAME="frm_food" VALUE="No"><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_food" VALUE="Yes" CHECKED><SPAN class='radioopt'>Yes</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Do you have significant health conditions?.');"><?php print get_text("Do you have significant health conditions");?> ?:</LABEL><BR />
					<INPUT TYPE="radio" NAME="frm_health" VALUE="No" CHECKED><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_health" VALUE="Yes"><SPAN class='radioopt'>Yes</SPAN>
					<INPUT TYPE="radio" NAME="frm_health" VALUE="Unknown"><SPAN class='radioopt'>Unknown</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' for="adults" onmouseout="UnTip()" onmouseover="Tip('Number of Adults needing assistance.');"><?php print get_text("Adults"); ?>:</LABEL>
					<INPUT id='adults' NAME="frm_adults" tabindex=10 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="4" />
					<BR />
					<BR />
					<LABEL style='text-align: center;' for="children" onmouseout="UnTip()" onmouseover="Tip('Number of Children needing assistance.');"><?php print get_text("Children"); ?>:</LABEL>
					<INPUT id='children' NAME="frm_children" tabindex=11 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="4" />
					<BR />
					<BR />
					<LABEL style='text-align: center;' for="elderly" onmouseout="UnTip()" onmouseover="Tip('Number of Elderly needing assistance.');"><?php print get_text("Elderly"); ?>:</LABEL>
					<INPUT id='elderly' NAME="frm_elderly" tabindex=12 SIZE="4" TYPE="text" VALUE="" MAXLENGTH="4" />
					<BR />
					<BR />
					<LABEL style='text-align: center;' for='livestock' style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('Do you have Pets or Livestock also needing assistance?.');"><?php print get_text("Pets / Livestock");?> ?:</LABEL><BR />
					<INPUT id='livestock' TYPE="radio" NAME="frm_livestock" tabindex=13 VALUE="No" CHECKED><SPAN class='radioopt'>No</SPAN>
					<INPUT TYPE="radio" NAME="frm_livestock" VALUE="Yes"><SPAN class='radioopt'>Yes</SPAN>
					<BR />
					<BR />
					<LABEL style='text-align: center;' for="livestock_about" onmouseout="UnTip()" onmouseover="Tip('If Livestock, what types and number and is vetinary assistance required.');"><?php print get_text("Livestock Info"); ?>: </LABEL><BR />
					<TEXTAREA id='livestock_about' NAME="frm_livestock_about" tabindex=14 ROWS="6" WRAP="virtual"></TEXTAREA>
					<BR />
					<LABEL style='text-align: center;' onmouseout="UnTip()" onmouseover="Tip('What type of rescue is required ?.');"><?php print get_text("Type of Rescue");?> ?:</LABEL>
					<?php print $type_select;?>
					<BR />
				</FIELDSET>
				<FIELDSET>
					<LEGEND style='text-align: center;' class='text_large text_bold'>Location</LEGEND>
					<LABEL for="street" onmouseout="UnTip()" onmouseover="Tip('Street address for the incident or job.');"><?php print get_text("Street Address"); ?>:</LABEL>
					<INPUT id='street' NAME="frm_street" tabindex=16 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="city" onmouseout="UnTip()" onmouseover="Tip('Town or City.');"><?php print get_text("City"); ?>:</LABEL>
					<INPUT id='city' NAME="frm_city" tabindex=17 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="state" onmouseout="UnTip()" onmouseover="Tip('State abbreviation - For UK just put UK.');"><?php print get_text("State"); ?>:</LABEL>
					<INPUT id='state' NAME="frm_state" tabindex=18 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="postcode" onmouseout="UnTip()" onmouseover="Tip('Postal or Zip code.');"><?php print get_text("Postcode"); ?>:</LABEL>
					<INPUT id='postcode' NAME="frm_postcode" tabindex=19 TYPE="text" VALUE="" MAXLENGTH="512" />
					<BR />
					<LABEL for="description" onmouseout="UnTip()" onmouseover="Tip('Other helpful information.');"><?php print get_text("Other Info"); ?>:</LABEL>
					<TEXTAREA id='description' NAME="frm_description" tabindex=20 ROWS="6" WRAP="virtual"></TEXTAREA>
					<BR />			
				</FIELDSET>
				<INPUT TYPE="hidden" NAME="frm_lat" VALUE="" />
				<INPUT TYPE="hidden" NAME="frm_lng" VALUE="" />
				<INPUT TYPE="hidden" NAME="frm_status" VALUE="Open" />
				<INPUT TYPE="hidden" NAME="frm_requester" VALUE=0 />
				<INPUT TYPE="hidden" NAME="frm__by" VALUE=0 />
				<INPUT TYPE="hidden" NAME="frm__on" VALUE="<?php print $now;?>" />
				<INPUT TYPE="hidden" NAME="frm__from" VALUE="<?php print $_SERVER['REMOTE_ADDR'];?>" />			
				</FORM>
				<CENTER>
				<SPAN id='sub_but' class='plain text' roll='button' aria-label='Submit' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.request_form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				<SPAN id='can_but' class='plain text' roll='button' aria-label='Cancel' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='document.can_Form.submit();'><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</CENTER>
				<DIV id='spacer' style='width: 100%; display: block; height: 60px;'>&nbsp;</DIV>
			</DIV>
		</DIV>
		<BR /><BR /><BR /><BR /><BR /><BR /><BR /><BR />
		<FORM NAME='can_Form' ACTION="public_request.php"></FORM>
		<DIV ID = "to_top" roll='button' aria-label='To Top' style='position:fixed; bottom: 40px; right: 0px; height: 12px; width: 10px; z-index: 99; cursor: pointer;' onclick = "location.href = '#top';"><IMG SRC='./markers/up.png'  BORDER=0 /></DIV>

<?php
	}
?>
<SCRIPT>
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
set_fontsizes(viewportwidth, "fullscreen");
outerwidth = viewportwidth;
leftcolwidth = outerwidth * .80;
fieldwidth = leftcolwidth * .8;
medfieldwidth = leftcolwidth * .5;		
smallfieldwidth = leftcolwidth * .3;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
for (var i = 0; i < fields.length; i++) {
	if($(fields[i])) {$(fields[i]).style.width = fieldwidth + "px";}
	} 
for (var i = 0; i < medfields.length; i++) {
	if($(medfields[i])) {$(medfields[i]).style.width = medfieldwidth + "px";}
	}
for (var i = 0; i < smallfields.length; i++) {
	if($(smallfields[i])) {$(smallfields[i]).style.width = smallfieldwidth + "px";}
	}
</SCRIPT>
</BODY>
</HTML>
