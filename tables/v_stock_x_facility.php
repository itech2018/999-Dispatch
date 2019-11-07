		<FORM NAME="v" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc" />
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top">
			<TD COLSPAN="2" ALIGN="CENTER">
				<FONT SIZE="+1">Table 'stock_x_facility' - View Entry</FONT>
			</TD>
		</TR>
		<TR>
			<TD COLSPAN="2">&nbsp;</TD>
		</TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Facility:</TD>
<?php
			$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $row['facility_id'];
			$result_f = mysql_query($query_f);
			$row_f = stripslashes_deep(mysql_fetch_assoc($result_f));
?>
			<TD><?php print $row_f['name'];?></TD>
		</TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Stock Item:</TD>
<?php
			$query_s = "SELECT * FROM `$GLOBALS[mysql_prefix]stock` WHERE `id` = " . $row['stock_item'];
			$result_s = mysql_query($query_s);
			$row_s = stripslashes_deep(mysql_fetch_assoc($result_s));
?>		
			<TD><?php print $row_s['name'];?></TD>
		</TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Number on order:</TD>
			<TD><?php print $row['on_order'];?></TD>
		</TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Stock Level:</TD>
			<TD><?php print $row['stock_level'];?></TD>
		</TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Location:</TD>
			<TD><?php print $row['location'];?></TD>
		</TR>

<?php
