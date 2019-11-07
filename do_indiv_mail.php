<?php
/*
8/17/09	initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
3/15/11 changed stylesheet.php to stylesheet.php
*/
error_reporting(E_ALL);		//

@session_start();
session_write_close();
require_once($_SESSION['fip']);		//7/28/10

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<STYLE>
#.plain 	{ background-color: #FFFFFF;}
</STYLE>
<?php

//dump($_POST);

if (empty($_POST)) {
	$id = quote_smart(trim($_GET['the_id']));
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_assoc($result);
?>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT>
 	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function $() {
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
	

	function validate() {
		var errmsg="";
		if (document.mail_form.frm_addr.value.trim()=="") {errmsg+="Message address is required";}
		if (document.mail_form.frm_subj.value.trim()=="") {errmsg+="Message subject is required";}
		if (document.mail_form.frm_text.value.trim()=="") {errmsg+="Message text is required";}
		if (!(errmsg=="")){
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			document.mail_form.submit();	
			}
		}				// end function validate()

	</SCRIPT>
	</HEAD>

	<BODY><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Unit</H3>
	<P>
		<FORM NAME='mail_form' METHOD='post' ACTION='<?php print basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
		<TABLE BORDER = 0>
			<TR CLASS= 'even'>
				<TD ALIGN='right'>To:</TD>
				<TD>
					<INPUT NAME='frm_name' SIZE=32 VALUE = '<?php print $row['contact_name'];?>'>
				</TD>
			</TR>
			<TR CLASS= 'odd'>
				<TD ALIGN='right'>Addr:</TD>
				<TD>
<?php
					$em_arr = array();
					$temp_arr = array();
					$temp_addrs = get_contact_via($row['unit_id']);
					foreach($temp_addrs as $val) {
						if (is_email($val)) {
							array_push($temp_arr, $val);
							}
						}
					$em_arr = array_unique($temp_arr);
					$em_addr = implode("|", $em_arr);
?>
					<INPUT NAME='frm_addr' SIZE=32 VALUE = '<?php print $em_addr;?>'>
				</TD>
			</TR>
			<TR CLASS='even'>
				<TD ALIGN='right'>Subject: </TD>
				<TD COLSPAN=2>
					<INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60>
				</TD>
			</TR>
			<TR CLASS='odd'>
				<TD ALIGN='right'>Message:</TD>
				<TD COLSPAN=2>
					<TEXTAREA NAME='frm_text' COLS=60 ROWS=4></TEXTAREA>
				</TD>
			</TR>
			<TR CLASS='even'>
				<TD ALIGN='center' COLSPAN=3>
					<SPAN id='send_but' CLASS='plain text' style='width: 100px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="validate();"><SPAN STYLE='float: left;'><?php print get_text("Send");?></SPAN><IMG STYLE='float: right;' SRC='./images/send_small.png' BORDER=0></SPAN>
					<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="document.mail_form.reset();;"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				</TD>
			</TR>
		</TABLE>
		<INPUT TYPE='hidden' NAME="frm_id" VALUE='<?php print $id;?>'>
		</FORM>
<?php
		} else {		// end if (empty($_POST)) {
		do_send ($_POST['frm_addr'], "", $_POST['frm_subj'], $_POST['frm_text'], 0, $_POST['frm_id']);	// ($to_str, $subject_str, $text_str )
?>
	<BODY><CENTER>		
	<CENTER><BR /><BR /><BR /><H3>Mail sent</H3><BR /><BR />
		<SPAN id='cancel_but' CLASS='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
<?php

	}		// end else
?> </BODY>
</HTML>
