<?php
error_reporting(E_ALL);
session_start();
session_write_close();	
require_once('../incs/functions.inc.php');
function adj_time($time_stamp) {
	$temp = mysql2timestamp($time_stamp);					// MySQL to integer form
	return date ("H:i", $temp);
	}
$ret_arr = array();	
define("UNIT", 0);
define("MINE", 1);
define("ALL", 2);
$interval = 48;				// booked date limit - hide if date is > n hours ahead of 'now'
$blink_duration = 5;		// blink for n (5, here) minutes after ticket was written
$button_height = 50;		// height in pixels
$button_width = 160;		// width in pixels
$button_spacing = 4;		// spacing in pixels
$map_size = .75;			// map size multiplier - as a percent of full size
$butts_width = 0;
$ret_arr = array();
$id_array = array();
	
$margin = 20;				// 11/10/10

$time_now = mysql_format_date(now());			// collect ticket id's into $id_array 
$selected = (array_key_exists('selected', $_GET)) ? $_GET['selected'] : 0;

if (array_key_exists('frm_mode', $_GET)) {$mode =  $_GET['frm_mode'];
	} else {
	if (is_unit())  {
		$mode = UNIT;
		} else {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
		$mode = (intval ($user_row['responder_id'])>0)? MINE: ALL;		// $mode => 'all' if no unit associated this user - 10/3/10
		}
	}		// end if/else initialize $mode

if (($mode == UNIT) || ($mode == MINE)){
	$my_unit = (empty($user_row['name_f']) && empty($user_row['name_l']))? "(NA)": $user_row['name_f'] . " " . $user_row['name_l'];
	$unit_str = "{$my_unit}";
	} else {
	$unit_str = "";
	}	
	
if ((($mode==0) || ($mode==1))) {									// pull $the_unit, $the_unit_name, this user
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` 
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `u`.`responder_id` = `r`.`id` )
		WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$user_row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_unit = $user_row['responder_id'];
	$the_unit_name = (empty($user_row['name']))? "NA": $user_row['name'];	// 'NA' if no responder this user
	} else {
	$the_unit_name = "NA";
	}
$restrict = (($mode==UNIT) || ($mode==MINE)) ? " (`responder_id` = {$the_unit}) AND ": "";		// 8/20/10, 9/3/10 

//	User Groups

$al_groups = $_SESSION['user_groups'];

if(array_key_exists('viewed_groups', $_SESSION)) {	//	6/10/11
	$curr_viewed= explode(",",$_SESSION['viewed_groups']);
	}
	
//	Set regions applicable for user

if(count($al_groups) == 0) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13		
	$where2 = " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";
	} else {		
	if(!isset($curr_viewed)) {			//	6/10/11
		$x=0;	
		$where2 = "AND (";
		foreach($al_groups as $grp) {
			$where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		} else {
		$x=0;	
		$where2 = "AND (";	
		foreach($curr_viewed as $grp) {
			$where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";	
			$where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
			$where2 .= $where3;
			$x++;
			}
		}
	$where2 .= " AND `$GLOBALS[mysql_prefix]allocates`.`type` = 1";	
	}

$mob_show_cleared = intval(get_variable('mob_show_cleared'));

$restrict = (($mode==UNIT) || ($mode==MINE)) ? " (`responder_id` = {$the_unit}) AND ": "";		// 8/20/10, 9/3/10 

$showWhich = ($mob_show_cleared == 1) ? 
			"((`t`.`status` = {$GLOBALS['STATUS_OPEN']}) OR (`t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} AND `t`.`booked_date` < (NOW() + INTERVAL {$interval} HOUR)))" : 
			"(((`t`.`status` = {$GLOBALS['STATUS_OPEN']}) OR ((`t`.`status` = {$GLOBALS['STATUS_SCHEDULED']} AND `t`.`booked_date` < (NOW() + INTERVAL {$interval} HOUR))))	AND (`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'))";

$query = "SELECT *,  `t`.`id` AS `tick_id`,
			`t`.`street` AS `tick_street`,
			`t`.`city` AS `tick_city`,
			`t`.`status` AS `tick_status`,
			`t`.`updated` AS `tick_updated`,
			`r`.`name` AS `unit_name`,
			`r`.`handle` AS `unit_handle`,				
			`a`.`id` AS `assign_id`,
			`i`.`type` AS `inc_type`
		FROM  `$GLOBALS[mysql_prefix]ticket` `t`
		LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON (`a`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON (`t`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`a`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `u` ON (`u`.`id` = `r`.`type`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `i` ON (`i`.`id` = `t`.`in_types_id` )	
		WHERE {$restrict} {$showWhich} {$where2} GROUP BY `assign_id` ORDER BY `t`.`status` DESC, `t`.`severity` DESC, `t`.`problemstart` ASC";

// dump($query);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
if (mysql_affected_rows()==0) {
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$unit_str = $the_unit_name . ": no current calls  as of " . substr($now, 11,5);
	$num_tickets = 0;
	$caption = ($mode==MINE)? "All calls": $the_unit_name;
	$frm_mode = ($mode==MINE)? ALL: MINE;
	} else {
	$num_tickets = 0;
	$i = $selected_indx = 0;
	$assigns_stack = array();
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		array_push($assigns_stack, $in_row);									// stack it up	
		if (empty($_GET['assign_id']) && empty($_GET['ticket_id'])) {
			if (empty($_GET) && ($i==0)) {
				$selected_indx = $i;
				} elseif($selected != 0) {
				$selected_indx = $selected;
				}
			} else {
			if((empty($assigns_stack[$i]['assign_id'])) && ($assigns_stack[$i]['tick_id'] == $_GET['ticket_id'])) {
				$selected_indx = $i;
				} elseif((!empty($_GET['assign_id'])) && ($assigns_stack[$i]['assign_id'] == $_GET['assign_id'])) {
				$selected_indx = $i;
				} elseif($selected != 0) {
				$selected_indx = $selected;				
				}
			}
		$i++;
		$num_tickets++;
		}		// end while(...)
 
	$assign_id = 	$assigns_stack[$selected_indx]['assign_id'];				// if any
	$ticket_id =  	$assigns_stack[$selected_indx]['tick_id'];					// 2/20/12
	$unit_id =  	$assigns_stack[$selected_indx]['responder_id'];				// if any
	$id_array = array();
	
	$time_now = mysql_format_date(now());			// collect ticket id's into $id_array 
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		array_push($id_array, $in_row['ticket_id']);
		}
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `updated` > ('{$time_now}' - INTERVAL 5 MINUTE);";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 
		array_push($id_array, $in_row['ticket_id']);
		}			

	$colors = array("even", "odd");
	$the_ticket_id = (array_key_exists('ticket_id', $_GET))? $_GET['ticket_id'] : 0 ;				// possibly empty on initial etry
//	dump($assigns_stack);
	for ($i = 0; $i<count($assigns_stack); $i++) {
		$print = "<TR VALIGN='top' WIDTH='100%' CLASS= '{$colors[($i + 1) % 2]}'>\n";
		if (($i==0) && ($the_ticket_id==0)) {$the_ticket_id = $assigns_stack[0]['ticket_id'];}		// first entry into mobile

		if (((now() -  mysql2timestamp($assigns_stack[$i]['tick_updated'])) < $blink_duration*60) ||
			(in_array( $assigns_stack[$i]['tick_id'], $id_array))) {

			$blinkst = "<blink>";
			$blinkend ="</blink>";
			}
		else {$blinkst = $blinkend = "";
			}		
	
		if ($i == $selected_indx) {
			$checked = "CHECKED";
			$the_ticket_id = $assigns_stack[$i]['tick_id'];
			}
		else {$checked = "";}
		$theAssign_id = ($assigns_stack[$i]['assign_id'] == "") ? 0 : $assigns_stack[$i]['assign_id'];
		$print .= "\t<TD class='td_data text text_left'><INPUT TYPE = 'radio' NAME = 'others' VALUE='{$i}' {$checked} onClick = 'load_ticket(" . $assigns_stack[$i]['tick_id'] . ", " . $theAssign_id . ", " . $i . ")' /></TD>\n";
		switch($assigns_stack[$i]['severity'])		{					//set cell color by severity
			case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; 	break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; 	break;
			default: 							$severityclass='severity_normal'; 	break;
			}
		$the_icon = intval($assigns_stack[$i]['icon']);					// 6/19/11
		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$the_icon];		// 8/29/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$the_icon];
		$unit_handle = addslashes($assigns_stack[$i]['handle']);
		$the_disp_stat = get_disp_status ($assigns_stack[$i]);			// 8/29/10
		$print .= "\t<TD class='td_data text text_left'><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>{$unit_handle}</SPAN></TD>\n";		// column 2 - handle 
		$print .= "\t<TD class='td_data text text_left'>&nbsp;{$the_disp_stat}</TD>\n";		// column 3-  disp status
		$the_ticket = shorten("{$assigns_stack[$i]['scope']}", 24); 					
		$print .= "\t<TD CLASS='td_data text text_left {$severityclass}' style='text-align: left;'>&nbsp;{$blinkst}{$the_ticket}{$blinkend}</TD>\n";						// column 5 - ticket
		$the_addr = shorten("{$assigns_stack[$i]['tick_street']}, {$assigns_stack[$i]['tick_city']}", 24); 					
		$print .= "\t<TD CLASS='td_data text text_left {$severityclass}' style='text-align: left;'>&nbsp;{$the_addr}</TD>\n";							// column 6 - address
		if($assigns_stack[$i]['tick_status'] == $GLOBALS['STATUS_SCHEDULED']) {
			$the_date = $assigns_stack[$i]['booked_date'];					
			$booked_symb = "<IMG SRC = 'markers/clock.png'/> &nbsp;";
			}
		else {
			$the_date =$assigns_stack[$i]['problemstart'];					
			$booked_symb = "";
			}
		$incType = $assigns_stack[$i]['inc_type'];
		$print .= "<TD CLASS='td_data text text_left {$severityclass}' style='text-align: left;'>" .  format_date_time($the_date) . "</TD>\n";			// column 4 - date
		$print .= "\t<TD class='td_data text text_left'>&nbsp;{$booked_symb}</TD>\n";						// column 7 - booked symb
		$print .= "\t<TD CLASS='td_data text text_left {$severityclass}' style='text-align: left;'>&nbsp;{$incType}</TD>\n";						// column 7 - booked symb
		$print .= "</TR>\n";
		$ret_arr[$i][0] = $assigns_stack[$i]['tick_id'];
		$ret_arr[$i][1] = $assigns_stack[$i]['assign_id'];
		$ret_arr[$i][2] = strtotime($assigns_stack[$i]['tick_updated']);	
		$ret_arr[$i][3] = $print;
		}			// end for ($i ...)
	}

$ret_arr[0][4] = $unit_str;
$ret_arr[0][5] = $num_tickets;
//dump($ret_arr);
print json_encode($ret_arr);
exit();
?>