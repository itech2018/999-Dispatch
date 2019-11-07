		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />

		<TABLE BORDER="0" ALIGN="center">
			<TR CLASS="even" VALIGN="top">
				<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'stock_x_facility' - Add New Entry</FONT></TD>
			</TR>
			<TR>
				<TD COLSPAN="2">&nbsp;</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Facility:</TD>
<?php
				$facility_control = "<SELECT NAME='frm_facility_id'>\n";
				$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`";
				$result_f = mysql_query($query_f);
				while($row_f = stripslashes_deep(mysql_fetch_assoc($result_f))) {
					$facility_control .= "<OPTION VALUE=" . $row_f['id'] . ">" . $row_f['name'] . "</OPTION>\n";
					}
				$facility_control .= "</SELECT>\n";
?>
				<TD>
					<?php print $facility_control;?>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Stock Item:</TD>
<?php
				$stock_control = "<SELECT NAME='frm_stock_item'>\n";
				$query_s = "SELECT * FROM `$GLOBALS[mysql_prefix]stock`";
				$result_s = mysql_query($query_s);
				while($row_s = stripslashes_deep(mysql_fetch_assoc($result_s))) {
					$stock_control .= "<OPTION VALUE=" . $row_s['id'] . ">" . $row_s['name'] . "</OPTION>\n";
					}
				$stock_control .= "</SELECT>\n";
?>
				<TD>
					<?php print $stock_control;?>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Number on order:</TD>
				<TD>
					<INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="8" SIZE="8" type="text" NAME="frm_on_order" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Stock Level:</TD>
				<TD>
					<INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="8" SIZE="8" type="text" NAME="frm_stock_level" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Location:</TD>
				<TD>
					<INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="48" SIZE="16" type="text" NAME="frm_location" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR>
				<TD COLSPAN="99" ALIGN="center">
					<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
					<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: $('ID3').style.visibility='hidden'; document.c.frm_icon.value = ''; document.c.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
					<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="JSfnCheckInput(document.c, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
				</TD>
			</TR>
		</TABLE>
		</FORM>


<?php
