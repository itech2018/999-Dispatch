<?php
function get_types() {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]conditions` ORDER BY `id`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_ret = "<SELECT NAME='frm_conditions'>";
	$the_ret .= "<OPTION VALUE='0' SELECTED>Select Condition Type</OPTION>";	
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$the_ret .= "<OPTION VALUE=" .  $row['id'] . ">" . $row['title'] . "</OPTION>";
		}
	$the_ret .= "</SELECT>";
	return $the_ret;
	}
?>
<SCRIPT>
function checkInput(myform, mybutton) {
	var theControl = myform.frm_conditions;
	if(theControl.options[theControl.selectedIndex].value==0) {
		alert("Condition Type must be set");
		return;
		} else {
		JSfnCheckInput(myform, mybutton);
		}
	}

function lckup_place(my_form) {		   						// 7/5/10
	if(!$('map_canvas')) {return; }
	var theLat = my_form.frm_lat.value;
	var theLng = my_form.frm_lng.value;	
	var myAddress = my_form.frm_address.value;
	control.options.geocoder.geocode(myAddress, function(results) {
		if(!results[0]) {
			return;
			}
		var r = results[0]['center'];
		theLat = r.lat;
		theLng = r.lng;
		pt_on_map (my_form, theLat, theLng);
		});
	}				// end function loc_lkup()
	
function pt_on_map (my_form, lat, lng) {
	if(!$('map_canvas')) {return; }
	if(marker) {map.removeLayer(marker);}
	if(myMarker) {map.removeLayer(myMarker);}
	var theLat = parseFloat(lat).toFixed(6);
	var theLng = parseFloat(lng).toFixed(6);
	my_form.frm_lat.value=theLat;	
	my_form.frm_lng.value=theLng;		
	var iconurl = "./our_icons/yellow.png";
	icon = new baseIcon({iconUrl: iconurl});	
	marker = L.marker([theLat, theLng], {icon: icon});
	marker.addTo(map);
	map.setView([theLat, theLng], 16);
	}				// end function pt_to_map ()
</SCRIPT>
		<FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
		<INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
		<INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
		<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
		<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
		<INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
		<INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
		<INPUT TYPE="hidden" NAME="frm_username" VALUE="<?php print $_SESSION['user']; ?>" />	
		
		<TABLE BORDER="0" ALIGN="center">
		<TR CLASS="even" VALIGN="top">
			<TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1">Road Condition Alerts - Add New Entry</FONT></TD>
		</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Title:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="128" SIZE="48" type="text" NAME="frm_title" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Description:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="128" SIZE="48" type="text" NAME="frm_description" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Address:&nbsp;&nbsp;&nbsp;<button type="button" onClick="lckup_place(document.c);"><img src="./markers/glasses.png" alt="Lookup location." /></button></TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="128" SIZE="48" type="text" NAME="frm_address" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Type:</TD>
			<TD><?php print get_types();?></TD></TR>
		<TR VALIGN="baseline" CLASS="odd">
			<TD CLASS="td_label" ALIGN="right">Latitude:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_lat" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<TR VALIGN="baseline" CLASS="even">
			<TD CLASS="td_label" ALIGN="right">Longitude:</TD>
			<TD><INPUT ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_lng" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> <SPAN class='warn' >text</SPAN></TD></TR>
		<tr><td colspan=99 align='center'>
		</td></tr>
		<TR>
			<TD COLSPAN="99">
				<DIV id = "map_canvas" style = "width: 500px; height: 500px; text-align: center;"></DIV>
			</TD>
		</TR>
		<TR>
			<TD COLSPAN="99" ALIGN="center">
				<SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: document.retform.func.value='r';document.retform.submit();"><SPAN STYLE='float: left;'><?php print get_text("Cancel");?></SPAN><IMG STYLE='float: right;' SRC='./images/cancel_small.png' BORDER=0></SPAN>
				<SPAN id='reset_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="Javascript: $('ID3').style.visibility='hidden'; document.c.frm_icon.value = ''; document.c.reset();"><SPAN STYLE='float: left;'><?php print get_text("Reset");?></SPAN><IMG STYLE='float: right;' SRC='./images/restore_small.png' BORDER=0></SPAN>
				<SPAN id='sub_but' CLASS='plain text' style='float: none; width: 80px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="checkInput(document.c, this );"><SPAN STYLE='float: left;'><?php print get_text("Submit");?></SPAN><IMG STYLE='float: right;' SRC='./images/submit_small.png' BORDER=0></SPAN>
			</TD>
		</TR>
		</TD></TR></TABLE>
		</FORM>
		<SCRIPT>
		var map;
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
		var latLng;
		var in_local_bool = "<?php print get_variable('local_maps');?>";
		var theLocale = <?php print get_variable('locale');?>;
		var useOSMAP = <?php print get_variable('use_osmap');?>;
		var initZoom = <?php print get_variable('def_zoom');?>;
		init_map(2, <?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>, "", parseInt(initZoom), theLocale, useOSMAP, "");
		map.setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], parseInt(initZoom));
		var bounds = map.getBounds();	
		var zoom = map.getZoom();
		var got_points = false;	// map is empty of points
		function onMapClick(e) {
		if(marker) {map.removeLayer(marker); }
			var iconurl = "./our_icons/yellow.png";
			icon = new baseIcon({iconUrl: iconurl});	
			marker = new L.marker(e.latlng, {id:1, icon:icon, draggable:'true'});
			marker.addTo(map);
			newGetAddress(e.latlng, "c");
			};

		map.on('click', onMapClick);
<?php
		do_kml();
?>
		</SCRIPT>
<?php
