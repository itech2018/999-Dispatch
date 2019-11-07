<?php
/*
*/
error_reporting(E_ALL);

@session_start();
session_write_close();
require_once('../incs/functions.inc.php');

if($_GET['q'] != $_POST['sessid']) {
	exit();
	}

$fieldtypes = array();
$col_headings = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` LIMIT 1";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	foreach ($row as $col_name => $cell) {
		$fieldindex = get_field_index('member', $col_name);
		$type = get_field_type('member', $fieldindex);
		$fieldtypes[$col_name] = $type;
		if($col_name != "_by" && $col_name != "_on" && $col_name != "_from" && $col_name != "id" && $col_name != 'field5') {
			$col_name = substr($col_name, 5);
			$col_name = get_fieldlabel($col_name);
			if($col_name != "Not Used") {
				$col_headings[] = $col_name . "\t";
				}
			}
		}
	}

function get_fieldid($theval) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]defined_fields` WHERE `label` = '$theval' LIMIT 1";
	$result = mysql_query($query);
	$row = stripslashes_deep(mysql_fetch_assoc($result)); 
	$ret_val = "field" . $row['field_id'];
	return $ret_val;
	}
	
function get_training($id) {
	$query = "SELECT `package_name`, `description`, `available`, `cost` FROM `$GLOBALS[mysql_prefix]training_packages` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_event($id) {
	$query = "SELECT `event_name`, `description` FROM `$GLOBALS[mysql_prefix]events` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_capabilities($id) {
	$query = "SELECT `name`, `description` FROM `$GLOBALS[mysql_prefix]capability_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_equipment($id) {
	$query = "SELECT `equipment_name`, `spec`, `serial`, `condition` FROM `$GLOBALS[mysql_prefix]equipment_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}

function get_clothing($id) {
	$query = "SELECT `clothing_item`, `description`, `size` FROM `$GLOBALS[mysql_prefix]clothing_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
	
function get_memberType($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_types` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	return $row['name'];		
	}

function get_memberStatus($id) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member_status` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	return $row['status_val'];		
	}
	
function get_memberName($id) {
	$surname = "";
	$firstname = "";
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $id;
	$result = mysql_query($query);
	$row= mysql_fetch_assoc($result);
	foreach ($row as $col_name => $cell) {
		if($col_name == "_by" && $col_name != "_on" && $col_name != "_from" && $col_name != "id") {
			$col_name = substr($col_name, 5);
			$col_name = get_fieldlabel($col_name);
			if($col_name == "Surname") {$surname = $cell;}
			if($col_name == "First Name") {$firstname = $cell;}
			}
		}
	return $firstname . " " . $surname;		
	}
	
function get_vehicle($id) {
	$query = "SELECT
		`ve`.`regno` AS `vehicle_identifier`,
		`m`.`field4` AS `vehicle_owner`,		
		`ve`.`make` AS `vehicle_make`, 	
		`ve`.`model` AS `vehicle_model`, 
		`ve`.`seats` AS `vehicle_seats`,
		`ve`.`fueltype` AS `vehicle_fuel`	
		FROM `$GLOBALS[mysql_prefix]vehicles` `ve` 
		LEFT JOIN `$GLOBALS[mysql_prefix]member` `m` ON ( `ve`.`owner` = `m`.`id` ) 			
		WHERE `ve`.`id` = '$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
	}
$print = "";
foreach($col_headings as $val) {
	$print .= $val;
	}

$training_packages = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]training_packages`";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$training_packages[$row['id']] = $row['package_name'];
	$print .= $row['package_name'] . "\t";
	}
	
$capabilities = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]capability_types`";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$capabilities[$row['id']] = $row['name'];
	$print .= $row['name'] . "\t";
	}

$equipment = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]equipment_types`";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$equipment[$row['id']] = $row['equipment_name'];
	$print .= $row['equipment_name'] . "\t";
	}
	
$clothing = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]clothing_types`";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$clothing[$row['id']] = $row['clothing_item'] . " - " . $row['size'];
	$print .= $row['clothing_item'] . " - " . $row['size'] . "\t";
	}

$events = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]events`";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$events[$row['id']] = $row['event_name'];
	$print .= $row['event_name'] . "\t";
	}	

$print .= "\r\n";

$member = (array_key_exists('member', $_GET)) ? $_GET['member'] : 0;
$team = (array_key_exists('team', $_GET)) ? $_GET['team'] : 0;
$output = array();
if($team != 0) {
	$scope = 3;
	} elseif($team == 0 && $member != 0) {
	$scope = 1;
	} elseif($team == 0 && $member == 0) {
	$scope = 2;
	} else {
	$scope = 3;
	}

switch($scope) {
	case 1:		//	scope equals 1 - individual member
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `id` = " . $member;
	break;
	
	case 2:		//	scope equals 1 - all members
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` ORDER by `field4`";
	break;
	
	case 3:		//	scope equals 1 - all members in specific team
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]member` WHERE `field3` = " . $team . " ORDER by `id`";
	break;
	}

$result = mysql_query($query);
$i = 1;
while ($row = mysql_fetch_assoc($result)) {
	$member = $row['id'];
	$teamid = $row['field4'];
	foreach ($row as $col_name => $cell) {
		$coltype = $fieldtypes[$col_name];
		if($col_name != "_by" && $col_name != "_on" && $col_name != "_from" && $col_name != "id" && $col_name != 'field5') {
			if($col_name != 'id') {
				$col_name = substr($col_name, 5);
				$col_name = get_fieldlabel($col_name);
				}
			if($col_name != "Not Used") {
				$cell = str_replace('.', ' ', $cell); // remove dots
				$cell = str_replace(' ', ' ', $cell); // remove spaces
				$cell = str_replace("\t", ' ', $cell); // remove tabs
				$cell = str_replace("\n", ' ', $cell); // remove new lines
				$cell = str_replace("\r", ' ', $cell); // remove carriage returns
				if($col_name == "Member Type") {
					$print .= get_memberType($cell) . "\t";
					} elseif($col_name == "Member Status") {
					$print .= get_memberStatus($cell) . "\t";
					} elseif($col_name == "Team") {
					$print .= get_Teamname($cell) . "\t";
					} elseif($coltype == "DATETIME") {
					$print .= format_dateonly($cell) . "\t";
					} else {
					$print .= $cell . "\t";
					}
				}
			}
		}
		
// Training completed

	$tp = array();
	$completed = array();
	$refresh = array();
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 1 ORDER BY `skill_id` ASC";
	$result1 = mysql_query($query1);
	if(!$result1) {
		foreach($training_packages as $key => $val) {
			$training_pkg = $training_packages[$key];
			$print .= "- \t";
			}
		} else {
		while ($row1 = mysql_fetch_assoc($result1)) {	
			$tp[] = $row1['skill_id'];
			$completed[$row1['skill_id']] = format_dateonly($row1['completed']);
			$refresh[$row1['skill_id']] = format_dateonly($row1['refresh_due']);
			}
		if(count($tp) > 0) {
			foreach($training_packages as $key => $val) {
				if(in_array($key, $tp)) {
					$training_pkg = $training_packages[$key];
					$temp1 = $completed[$key];
					$temp2 = $refresh[$key];
					$print .= "Completed: " . $temp1 . " - Refresh:" . $temp2 . "\t";
					} else {
					$training_pkg = $training_packages[$key];
					$print .= "- \t";
					}
				}
			} else {
			foreach($training_packages as $key => $val) {
				$training_pkg = $training_packages[$key];
				$print .= "- \t";
				}
			}
		}

// Capabilities

	$cp = array();
	$registered = array();
	$query1 = "SELECT *	FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 2 ORDER BY `skill_id` ASC";
	$result1 = mysql_query($query1);
	if(!$result1) {
		foreach($capabilities as $key => $val) {
			$capability_name = $capabilities[$key];
			$print .= "- \t";
			}
		} else {
		while ($row1 = mysql_fetch_assoc($result1)) {	
			$cp[] = $row1['skill_id'];
			$registered[$row1['skill_id']] = format_dateonly($row1['_on']);
			}			
		if(count($cp) > 0) {
			foreach($capabilities as $key => $val) {
				if(in_array($key, $cp)) {
					$capability_name = $capabilities[$key];
					$temp1 = $registered[$key];
					$print .= $temp1 . "\t";
					} else {
					$capability_name = $capabilities[$key];
					$print .= "- \t";
					}
				}
			} else {
			foreach($capabilities as $key => $val) {
				$capability_name = $capabilities[$key];
				$print .= "- \t";
				}
			}
		}
		
// Equipment

	$eq = array();
	$allocated = array();
	$query1 = "SELECT * FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 3 ORDER BY `skill_id` ASC";
	$result1 = mysql_query($query1);
	if(!$result1) {
		foreach($equipment as $key => $val) {
			$equipment_name = $equipment[$key];
			$print .= "- \t";
			}
		} else {
		while ($row1 = mysql_fetch_assoc($result1)) {	
			$eq[] = $row1['skill_id'];
			$allocated[$row1['skill_id']] = format_dateonly($row1['_on']);
			}			
		if(count($eq) > 0) {
			foreach($equipment as $key => $val) {
				if(in_array($key, $eq)) {			
					$equipment_name = $equipment[$key];
					$temp1 = $allocated[$key];
					$print .= $temp1 . "\t";
					} else {
					$equipment_name = $equipment[$key];
					$print .= "- \t";
					}
				}
			} else {
			foreach($equipment as $key => $val) {
				$equipment_name = $equipment[$key];
				$print .= "- \t";
				}
			}
		}
		
// Clothing
		
	$cl = array();
	$allocated = array();
	$query1 = "SELECT *	FROM `$GLOBALS[mysql_prefix]allocations` WHERE `member_id` = " . $member . " AND `skill_type` = 5 ORDER BY `skill_id` ASC";
	$result1 = mysql_query($query1);
	if(!$result1) {
		foreach($clothing as $key => $val) {
			$clothing_item = $clothing[$key];
			$print .= "- \t";
			}
		} else {
		while ($row1 = mysql_fetch_assoc($result1)) {	
			$eq[] = $row1['skill_id'];
			$allocated[$row1['skill_id']] = format_dateonly($row1['_on']);
			}	
		if(count($eq) > 0) {
			foreach($clothing as $key => $val) {
				if(in_array($key, $cl)) {
					$clothing_item = $clothing[$key];
					$temp1 = $allocated[$key];
					$print .= $temp1 . "\t";
					} else {
					$clothing_item = $clothing[$key];
					$print .= "- \t";
					}
				}
			} else {
			foreach($clothing as $key => $val) {
				$clothing_item = $clothing[$key];
				$print .= "- \t";
				}				
			}
		}
		
// Events
		
	$ev = array();
	$start = array();
	$end = array();
	$thestring = "";
	$temp = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
	$datetoday = strtotime($temp);
	$query1 = "SELECT *, `start` AS `start`, `end` AS `end`, `$GLOBALS[mysql_prefix]allocations`.`id` AS `all_id`,`$GLOBALS[mysql_prefix]member`.`id` AS `member_id` FROM `$GLOBALS[mysql_prefix]allocations`
				LEFT JOIN `$GLOBALS[mysql_prefix]member` ON `$GLOBALS[mysql_prefix]allocations`.`member_id`=`$GLOBALS[mysql_prefix]member`.`id`
				LEFT JOIN `$GLOBALS[mysql_prefix]events` ON `$GLOBALS[mysql_prefix]allocations`.`skill_id`=`$GLOBALS[mysql_prefix]events`.`id`					
				WHERE `member_id` = " . $member . " AND `skill_type` = '6' ORDER BY `skill_id`";
	$result1 = mysql_query($query1);
	if(!$result1) {
		foreach($events as $key => $val) {
			$event_name = $events[$key];
			$print .= "- \t";
			}
		} else {
		while ($row1 = mysql_fetch_assoc($result1)) {	
			$ev[] = $row1['skill_id'];
			$start[$row1['skill_id']] = do_datestring(strtotime($row1['start']));
			$end[$row1['skill_id']] = do_datestring(strtotime($row1['end']));
			}	
		if(count($eq) > 0) {
			foreach($events as $key => $val) {
				if(in_array($key, $ev)) {
					$event_name = $events[$key];
					$temp1 = $start[$key];
					$temp2 = $end[$key];
					$print .= $temp1 . " to " . $temp2 . "\t";
					} else {
					$event_name = $events[$key];
					$print .= "- \t";
					}
				}
			} else {
			foreach($events as $key => $val) {
				$event_name = $events[$key];
				$print .= "- \t";
				}
			}
		}
	$print .= "\r\n";
	$i++;
	}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename='full_report.xls'");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
echo $print;
	
?>
