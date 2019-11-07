		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<TABLE BORDER="0" ALIGN="center">
			<TR CLASS="even" VALIGN="top">
				<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Table 'stock' - Add New Entry</FONT></TD>
			</TR>
			<TR>
				<TD COLSPAN="2">&nbsp;</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Item Name:</TD>
				<TD>
					<INPUT ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Description:</TD>
				<TD>
					<INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Order Quantity:</TD>
				<TD>
					<INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_order_quantity" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="even">
				<TD CLASS="td_label" ALIGN="right">Pack Size:</TD>
				<TD>
					<INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_pack_size" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
				</TD>
			</TR>
			<TR VALIGN="baseline" CLASS="odd">
				<TD CLASS="td_label" ALIGN="right">Re-order Level:</TD>
				<TD>
					<INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_reorder_level" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN>
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
