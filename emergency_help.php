<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
require_once('./incs/functions.inc.php');

$respondernames = array();
$responderhandles = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id`";
$result = mysql_query($query);	
while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$respondernames[$row['id']] = $row['name'];
	$responderhandles[$row['id']] = $row['handle'];	
	}

$evenodd = array ("even", "odd");

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]emer_messages` WHERE `ack_by` = 0 ORDER BY `id` ASC"; 
$result = mysql_query($query);	
$emergencies = array();
$i=1;
$num_rows = mysql_num_rows($result);

while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$emergencies[$i]['id'] = $row['id'];	
	$emergencies[$i]['responder'] = $row['_by'];
	$emergencies[$i]['when'] = $row['_on'];
	$emergencies[$i]['name'] = (array_key_exists($row['_by'],$respondernames)) ? $respondernames[$row['_by']] : "N/A";
	$emergencies[$i]['handle'] = (array_key_exists($row['_by'],$responderhandles)) ? $responderhandles[$row['_by']] : "N/A";
	$i++;
	}				// end while

?>
<!DOCTYPE html>
<html>
<head>
<title>Responder Assistance Request Popup</title>
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT>
function do_unit_popup(id) {
	var spec ="titlebar, resizable=1, scrollbars, height=1000, width=1000, status=no,toolbar=no,menubar=no,location=0, left=50,top=10,screenX=50,screenY=10";
	var title = "Unit Popup";
	var url = "unit_popup.php?id="+id;;
	var unitwindow=window.open(url, title, spec);
	if (isNull(unitwindow)) {
		alert ("Responder alert screen requires popups to be enabled. Please adjust your browser options.");
		return;
		}
	unitwindow.focus();
	}
</SCRIPT>
</HEAD>
<BODY>
<?php
if(empty($_POST)) {
?>
	<FORM METHOD="POST" NAME= "unit_emergency_Form" ACTION="emergency_help.php?func=ack">
	<TABLE style='width: 100%;'>
<?php
	if(count($emergencies) > 0) {
		print "<TR class='header text'><TD class='header text'>UNIT</TD><TD class='header text'>NAME</TD><TD class='header text'>HANDLE</TD><TD class='header text'>WHEN</TD><TD class='header text'>ACK</TD></TR>";
		$i=0;
		foreach($emergencies as $val) {
			print "<TR CLASS='" . $evenodd[($i+1)%2] . "' style='cursor: hand; width: 100%;'>";
			print "<TD class='td_data text' onClick='do_unit_popup(" . $val['responder'] . ");'>" . $val['responder'] . "</TD>";
			print "<TD class='td_data text' onClick='do_unit_popup(" . $val['responder'] . ");'>" . $val['name'] . "</TD>";
			print "<TD class='td_data text' onClick='do_unit_popup(" . $val['responder'] . ");'>" . $val['handle'] . "</TD>";
			print "<TD class='td_data text' onClick='do_unit_popup(" . $val['responder'] . ");'>" . format_date_2(strtotime($val['when'])) . "</TD>";
			print "<TD class='td_data text'><input type='checkbox' name='ack[]' value=1>Acknowledge</TD>";
			print "<INPUT type='hidden' name='resp[]' VALUE = " . $val['responder'] . ">";
			print "<INPUT type='hidden' name='id[]' VALUE = " . $val['id'] . ">";
			print "<INPUT type='hidden' name='respondername[]' VALUE = " . $val['name'] . ">";
			print "<INPUT type='hidden' name='handle[]' VALUE = " . $val['name'] . ">";
			print "<TR>";
			$i++;
			}
		} else {
		print "<TR class='header text'><TD class='header text' COLSPAN=99>No unacknowledged emergency requests</TD></TR>";
		}

?>
	</TABLE>
	<BR /><BR /><BR />
	<CENTER>
<?php
	if(count($emergencies) > 0) {
?>
		<SPAN id='sub_but' class = 'plain text' style='float: none;' onMouseover = 'do_hover(this.id);' onMouseout = 'do_plain(this.id);' onClick = 'document.unit_emergency_Form.submit();'>Submit</SPAN>
<?php
		} else {
?>
		<SPAN id='can_but' class = 'plain text' style='float: none;' onMouseover = 'do_hover(this.id);' onMouseout = 'do_plain(this.id);' onClick = 'window.close();'>Close</SPAN>
	</CENTER>
	</FORM>
<?php
		}
	} else {
	@session_start();
	@session_write_close();
	$by = $_SESSION['user_id'];
	$delta = (get_variable('delta_mins') != "") ? get_variable('delta_mins') : 0;
	$from = quote_smart($_SERVER['REMOTE_ADDR']);
	$now = mysql_format_date(time() - ($delta*60));
	$submitted = $_POST;
	unset($_POST);
	foreach($submitted as $key=>$val) {
		foreach($val as $key2=>$val2) {
			if(array_key_exists($key2, $submitted['ack'])) {
				$ack = $submitted['ack'][$key2];
				$id = $submitted['id'][$key2];
				$responder = $submitted['resp'][$key2];
				$respondername = $submitted['respondername'][$key2];
				$responderhandle = $submitted['handle'][$key2];
				if($key == "ack") {
					$query = "UPDATE `$GLOBALS[mysql_prefix]emer_messages` SET `ack_by` = " . $by . ", `ack_when` = " . quote_smart($now) . " WHERE `id` = " . $id;
					$result = mysql_query($query);
					if($result) {
						print "<P class = 'warn text text_center'>Responder Help request for responder " . $responderhandle . "(" . $respondername . ") has been acknowledged<BR />";
						}						
					}
				}
			}
		}
?>
	<BR /><BR /><BR />
	<FORM NAME='fin_Form' ACTION="emergency_help.php">
	<CENTER>
	<SPAN id='fin_but' class = 'plain text' style='float: none;' onMouseover = 'do_hover(this.id);' onMouseout = 'do_plain(this.id);' onClick = 'document.fin_Form.submit();'>Finish</SPAN>
	</CENTER>
	</FORM>
<?php
	}
?>
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
set_fontsizes(viewportwidth, "popup");	
</SCRIPT>
</BODY>
</HTML>
<?php


exit();	
?>