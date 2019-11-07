<?php

/*
1/3/10 complete re-write to support button light-up for multi-user operation
1/11/10 added do_set_sess_exp()
4/1/10 JSON replaces eval
4/5/10 do_time, cb width calc, cb script rename, syncAjax() {
4/7/10 $cycle added, 'mu_init' to 'get_latest.php', unit position change now tracked
4/10/10 replaced JSON return with tab-sep'd string
4/11/10 removed poll value references
4/15/10 fullscreen=no
5/12/10 show/hide Board button
6/12/10 browser id, audible alarms added for new ticket, chat invite
7/3/10 changed Card to SOP's
7/21/10 hide cb frame on logout
7/27/10 Unit login handling added
7/28/10 window focus added, logout moved to top row
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/8/10 implment version no. as hyphen-separated string
8/16/10 convert synch ajax to asynch
8/20/10 'term' => 'mobile'
8/21/10 light up active module/button
8/24/10 emd card handling cleanup
8/25/10 server variables handling cleaned up
8/27/10 chat error detection
10/28/10 additions to support modules
1/7/11  JSON re-introduced with length validation and parseInt()
3/15/11 added reference to stylesheet.php for revisable day night colors.
5/4/11 day/night color changes added
5/10/11 log window width increased
6/28/11 try/catch added to accommodate main's new auto-refresh
2/10/12	added logout() call to error detection 3 places
2/25/12 action and patient data to button light-up
2/27/12 div's added for latest ticket, assigns, action and patient
10/23/12 Added code for messaging
5/13/2013 added ics-213 button conditional on setting value
5/24/2013 - websockets code added
5/29/2013 - revised message handling/notification, do_logout() calls commented out in try/catch error handling
5/30/2013 - set 5-second poll cycle.
6/3/2013 - made HAS button appearance conditional on setting
7/2/2013 include setting internet in HAS include
7/16/13 Revisions to strings for top bars which fail on intial load after install and stop buttons from showing.
10/25/13 Revised get_filelist and associated timer.
1/3/14 Added Road Condition Alert markers and live moving unit markers
1/30/14 Revised new message handling and added unread messages flag
3/23/2015 - corrected script-name to 'os_watch' 2 places
3/30/2015 - added OSW initialization
4/2/2015 - added data existence check
9/16/2015 - revise OSW operation
*/

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
require_once('./incs/browser.inc.php');
@session_start();
session_write_close();
if(file_exists("./incs/modules.inc.php")) {
	require_once('./incs/modules.inc.php');
	}
$poll_cycle_time = 5000;	// 5 seconds to ms - 4/13/18
$resps_arr = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
$result_resps = mysql_query($query);
while ($row_resps = stripslashes_deep(mysql_fetch_assoc($result_resps))) 	{
	$resps_arr[$row_resps['id']] = $row_resps['icon_str'];
	}

$sounds = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]sound_settings`";
$result = mysql_query($query);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{
	$sounds[$row['name']] = $row['ison'];
	}

$browser = trim(checkBrowser(FALSE));
$temp1  = get_variable('socketserver_url');
$host = (array_key_exists("SERVER_NAME", $_SERVER)) ? "{$_SERVER['SERVER_NAME']}" : $temp1;
$currscript = $_SERVER['REQUEST_URI'];
$loc_arr = explode("/", $currscript);
$count = count($loc_arr);
$uri = get_variable('socketserver_url');
for ($i = 0; $i < $count-1; $i++) {
	$uri .= $loc_arr[$i];
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE>Tickets - Main Module</TITLE>
<META NAME="Author" CONTENT="Tickets CAD" />
<META NAME="Keywords" CONTENT="CAD, Dispatch, EMS" />
<META NAME="Description" CONTENT="Open Source Computer Aided Dispatch" />
<META NAME="viewport" content="width=device-width, initial-scale=1.0"> 
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<STYLE type="text/css">
	.message { FONT-WEIGHT: bold; FONT-SIZE: 2em; COLOR: #0000FF; FONT-STYLE: normal;}
</STYLE>
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>
<SCRIPT SRC='./js/md5.js'></SCRIPT>
<SCRIPT>
	window.onresize=function(){set_size();}

	var sounds = <?php print json_encode($sounds);?>;
	var buttons = false;
	var viewportwidth;
	var viewportheight;
	var all_hands = <?php print intval(get_variable('all_hands'));?>;
	var emergency_messages = 0;
	var resps = <?php echo json_encode($resps_arr);?>;
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
		
	function get_mem_usage() {
		var randomnumber=Math.floor(Math.random()*99999999);
		var url = './ajax/check_memory.php?version=' + randomnumber;
		sendRequest (url,mem_cb, "");
		function mem_cb(req) {
			var mem_arr = JSON.decode(req.responseText);
			var theOutput = "";
			if(!mem_arr) { return;}
			theOutput += mem_arr[0] + " - " + mem_arr[1] + " - " + mem_arr[2] + "<BR />";
			theOutput += mem_arr[4] + " - " + mem_arr[5] + " - " + mem_arr[3] + "<BR />";
			if($('memory')) {$('memory').innerHTML = theOutput;}
			}				// end function mem_cb()
		}				// end function get_mem_usage()

	function set_size()	{
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
		set_fontsizes(viewportwidth,'fullscreen');
		}

	var current_butt_id = "main";
	var internet = false;
	var is_messaging = 0;

<?php if(file_exists("./incs/modules.inc.php")) { ?>
	var ticker_active = <?php print module_active("Ticker");?>;
<?php } else { ?>
	var ticker_active = 0;
<?php } ?>

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php

	function do_time() {
		var today=new Date();
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		$('time_of_day').innerHTML=h+":"+m;
		}
		
	function do_all_hands() {
		var url = "all_hands.php";
		sendRequest (url, allhands_handleResult, "");
		}
		
	function allhands_handleResult(req) {
		var response = JSON.decode(req.responseText);
		if(response[2] == 1) {
			if($('alag_but')) {$('alag_but').innerHTML = "All Hands Off";}
			} else {
			if($('alag_but')) {$('alag_but').innerHTML = "All Hands On";}
			}
		}
		
	var the_time = setInterval("do_time()", 15000);
	var is_initialized = false;
	var nmis_initialized = false;
	var mu_interval = null;
	var nm_interval = null;
	var msgs_interval = null;
	var emsgs_interval = null;
	var pos_interval = null;
	var m_interval = null;
	var lit=new Array();
	var lit_r = new Array();
	var lit_o = new Array();
	var unread_messages = 0;
	var hasUsercount = 0;
	var chat_id = 0;
	var ticket_id = 0;
	var unit_id;
	var updated;
	var dispatch;
	var new_msg = 0;
	var the_unit = 0;
	var the_status = 0;
	var the_time = 0;

	var d = new Date();
	var chk_osw_at = d.getTime();
	var ws_server_started = false;
	var mu = false;
	
	function do_emergency_alerts() {
		if(emerwindow && !emerwindow.closed) {emerwindow.close();}
		var spec ="titlebar, resizable=1, scrollbars, height=300, width=500, status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
		var title = "Responder Assitance Request";
		var url = "emergency_help.php";
		var emerwindow=window.open(url, title, spec);
		if (isNull(emerwindow)) {
			alert ("Responder alert screen requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		do_audible('man_down');
		emerwindow.focus();
		}
		
	function start_server() {
		var randomnumber=Math.floor(Math.random()*99999999);
		var https = <?php print $https;?>;
		var protocol = (https == 1) ? "https" : "http";
		var hostURL = "<?php print $uri;?>";
		var host = protocol + "://" + hostURL;
		var url = host + "/socketserver/server.php?version=" + randomnumber;
		var obj; 
		obj = new XMLHttpRequest();
		obj.onreadystatechange = function() {
			if(typeof parent.frames["main"].Socket_startup == 'function') {
				setTimeout(function(){parent.frames["main"].Socket_startup(); }, 5000);
				}
			if(typeof Socket_startup == 'function') {
				setTimeout(function(){Socket_startup(); }, 5000);
				}
			}
		obj.open("POST", url, true);
		obj.send(null);
		}
	
	function do_msgs_loop() {		//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		if (window.XMLHttpRequest) {
			xmlHttp = new XMLHttpRequest();
			xmlHttp.open("GET", "./ajax/get_messages.php?version=" + randomnumber, true);
			xmlHttp.onreadystatechange = handleRequestStateChange2;
			xmlHttp.send(null);
			}
		}			// end function do_msgs_loop()

	function handleRequestStateChange2() {
		var the_resp;
		var the_val;
		if (xmlHttp.readyState == 4) {
			if (xmlHttp.status == 200) {
				var response = JSON.decode(xmlHttp.responseText);
				if(response) {
					if(response[0]) {
						for(var key in response[0]) {
							the_resp = key;
							the_val = response[0][key];
							un_stat_chg(the_resp, the_val);
							}
						}
					if(response[1]) {
						var the_mess = response[1][0];
						var the_stored = response[1][1];
						if(the_stored != 0) {
							show_msg("There are " + the_stored + " new messages");
							msg_signal_r();
							} else {
							msg_signal_r_off();
							}
						}
					}
				}
			}
		}

	function do_loop() {								// monitor for changes - 4/10/10, 6/10/11
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('get_latest_id.php?version=' + randomnumber,get_latest_id_cb, "");
		}			// end function do_loop()

	function do_latest_msgs_loop() {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,get_latest_messages_cb, "");
		}
		
	function un_stat_chg(unit_id, the_stat_id) {	//	10/23/12
		var the_stat_control = "frm_status_id_u_" + unit_id;
		if(typeof parent.frames["main"].change_status_sel == 'function') {
			var theIcon = resps[unit_id];
			parent.frames["main"].change_status_sel(the_stat_control, the_stat_id, theIcon, unit_id);
			}
		}

	var arr_lgth_good = 19;								// size of a valid returned array - 3/23/2015
	var gd = new Date();								// 3/23/2015
	var g_time_now = gd.getTime();						// set global vals
	g_priority_run_at = g_normal_run_at = g_routine_run_at = g_time_now ;					// next routine  when-to-run ( 60 mins nominal default)

	function get_latest_id_cb(req) {					// get_latest_id callback() - 8/16/10
		try {
			var the_id_arr=JSON.decode(req.responseText);	// 1/7/11
			}
		catch (e) {
			return;
			}

		try {
			var the_arr_lgth = the_id_arr.length;		// sanity check
			}
		catch (e) {
			return;
			}
		if (the_arr_lgth != arr_lgth_good)  {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			}
		var temp = parseInt(the_id_arr[0]);				// new chat invite?
		if (temp != chat_id) {
			chat_id = temp;
			chat_signal();								// light the chat button
			}
		$("div_ticket_id").innerHTML = the_id_arr[1].trim();	// 2/19/12
		var temp =  parseInt(the_id_arr[1]);			// ticket?
		if (temp != ticket_id) {
			ticket_id = temp;
			tick_signal();								// light the ticket button
			if(typeof parent.frames["main"].load_incidentlist == 'function') {
				parent.frames["main"].load_incidentlist(parent.frames["main"].inc_field, parent.frames["main"].inc_direct);
				}
			}
		var temp =  parseInt(the_id_arr[2]);			// unit?
		var temp1 =  the_id_arr[3].trim();				// unit timestamp?
		if ((temp != unit_id) || (temp1 != updated)) {	//	10/23/12
			unit_id = temp;
			updated =  temp1;							// timestamp this unit
			$('unit_id').innerHTML = unit_id;			// unit id
			unit_signal();								// light the unit button
			}

		$("div_assign_id").innerHTML = the_id_arr[4].trim();			// 2/19/12
		if (the_id_arr[4].trim() != dispatch)  {		// 1/21/11
			dispatch = the_id_arr[4].trim();
			unit_signal();								// sit scr to blue
			}
		if (the_id_arr[5].trim() != $("div_action_id").innerHTML)  {		// 2/25/12
			misc_signal();													// situation button blue if ...
			$("div_action_id").innerHTML = the_id_arr[5].trim();
			}

		if (the_id_arr[6].trim() != $("div_patient_id").innerHTML)  {		// 2/25/12
			misc_signal();													// situation button blue if ...
			$("div_patient_id").innerHTML = the_id_arr[6].trim();
			}
		if (the_id_arr[7] != $("div_requests_id").innerHTML) {	//		9/10/13
			if(the_id_arr[7] != "0") {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "inline-block";
				$("reqs").innerHTML = "Open Requests = " + the_id_arr[7];
				} else if (the_id_arr[8] != "0") {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "inline-block";
				$("reqs").innerHTML = "Requests";
				} else {
				$("div_requests_id").innerHTML = the_id_arr[7];
				$("reqs").style.display = "none";
				$("reqs").innerHTML = "";
				}
			}
		if (the_id_arr[9] != $("div_imm_danger_req").innerHTML && the_id_arr[9] != 0) {
			alert("There are " + the_id_arr[9] + " urgent requests for people in imminent danger");
			$("div_imm_danger_req").innerHTML = the_id_arr[9];
			do_audible('man_down');
			}
		var temp2 =  parseInt(the_id_arr[10]);			// unit?	9/10/13
		var temp3 =  parseInt(the_id_arr[11]);			// status?	9/10/13
		var temp4 =  the_id_arr[11].trim();				// unit timestamp?	9/10/13
		if ((temp2 != the_unit) || (temp3 != the_status) || (temp4 != the_time)) {
			if(the_unit != 0) {
				if(typeof parent.frames["main"].get_unit_assigns == 'function') {
					parent.frames["main"].get_unit_assigns(the_unit);
					}
				}
			the_unit = temp2;	//		9/10/13
			the_status = temp3;	//		9/10/13
			the_time =  temp4;	// timestamp this unit, 	9/10/13
			un_stat_chg(the_unit, the_status);	//		9/10/13
			}
		if(the_id_arr[15] == 1) {
			$('all_hands').style.display = "inline-block";
			} else {
			$('all_hands').style.display = "none";
			}
		if(the_id_arr[15] != all_hands) {
			do_all_hands(); 
			all_hands = the_id_arr[15];
			}	//	For All hands function
		if(the_id_arr[16] != 0) {
			if(the_id_arr[16] != emergency_messages) {
				emergency_messages = the_id_arr[16];
				do_emergency_alerts();
				} else {
				if(emergency_messages != 0) {
					if(emerwindow && !emerwindow.closed) {emerwindow.focus();}
					}
				}
			}
		var d = new Date();
		var rightNow = d.getTime(); 									// millisecs since 1970/01/01
		if ( rightNow > chk_osw_at ) {
			chk_osw_at = rightNow + ( 60000 );							// set next check at one minute from rightNow
			if ( the_id_arr[13] == 1 ) {								// run on-scene watch?
				var rand = Math.floor(Math.random() * 10000);			// cache buster
				var window_addr = "os_watch.php?rand=" + rand;
				newwindow_co=window.open( window_addr, "On-Scene Watch",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
				setTimeout(function() { newwindow_co.focus(); }, 1);
				}
			}			// end ( rightNow > chk_osw_at )
		}			// end function get_latest_id_cb()

	function get_latest_messages_cb(req) {					// get_latest_messages callback(), 10/23/12, 1/30/14
		var the_msg_arr=JSON.decode(req.responseText);
		var the_number = parseInt(the_msg_arr[0][0]);
		unread_messages = the_number;
		if(the_number != 0) {
			if(the_number != unread_messages) {
				$("msg").innerHTML = "Msgs (" + unread_messages + ")";
				unread_messages = the_number;
				msg_signal_o();
				}
			} else {
			$("msg").innerHTML = "Msgs";
			msg_signal_o_off();
			}
		new_msgs_get();
		}			// end function get_latest_messages_cb()

	function toHex(x) {
		hex="0123456789ABCDEF";almostAscii=' !"#$%&'+"'"+'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ['+'\\'+']^_`abcdefghijklmnopqrstuvwxyz{|}';r="";
		for(i=0;i<x.length;i++){
			let=x.charAt(i);pos=almostAscii.indexOf(let)+32;
			h16=Math.floor(pos/16);h1=pos%16;r+=hex.charAt(h16)+hex.charAt(h1);
			};
		return r;
		};

	function mu_get() {								// set cycle
		if (mu_interval!=null) {return;}			// ????
		mu_interval = window.setInterval('do_loop()', <?php print $poll_cycle_time;?>);		// 4/7/10
		}			// end function mu get()

	function new_msgs_get() {								// set cycle, 10/23/12
		if (nm_interval!=null) {return;}			// ????
		nm_interval = window.setInterval('do_latest_msgs_loop()', 60000);
		}			// end function mu get()

	function messages_get() {								// set cycle, 10/23/12
		if (msgs_interval!=null) {return;}			// ????
		msgs_interval = window.setInterval('do_msgs_loop()', 60000);
		}			// end function mu get()
		
	function mu_init() {								// get initial values from server -  4/7/10
		if(!buttons) {show_butts();}
		if(mu) {return;}
		mu = true;
//		if(!m_interval) {
//			m_interval = window.setInterval('get_mem_usage()', 3000);
//			}
		var theBroadcast =  <?php print get_variable('broadcast');?>;
		if(parseInt(theBroadcast) == 1) {
			start_server();
			}
		var randomnumber=Math.floor(Math.random()*99999999);
		if (is_initialized) { return; }
		is_initialized = true;
		sendRequest ('get_latest_id.php?version=' + randomnumber,init_cb, "");
		function init_cb(req) {
			var the_id_arr=JSON.decode(req.responseText);				// 1/7/11
			if (!the_id_arr || (the_id_arr.length != arr_lgth_good))  {
//				alert(arr_lgth_good + ", " + the_id_arr.length);
				alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
				} else {
				chat_id =  parseInt(the_id_arr[0]);
				ticket_id = parseInt(the_id_arr[1]);
				unit_id =  parseInt(the_id_arr[2]);
				updated =  the_id_arr[3].trim();					// timestamp this unit
				dispatch = the_id_arr[4].trim();					// 1/21/11
				if($("div_ticket_id")) {$("div_ticket_id").innerHTML = the_id_arr[1].trim();}	// 2/19/12
				if($("div_assign_id")) {$("div_assign_id").innerHTML = the_id_arr[4].trim();}	// 2/19/12
				if($("div_action_id")) {$("div_action_id").innerHTML = the_id_arr[5].trim();}	// 2/25/12
				if($("div_patient_id")) {$("div_patient_id").innerHTML = the_id_arr[6].trim();}	// 2/25/12
				if(the_id_arr[7] != "0") {	//		9/10/13
					if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
					if($("reqs")) {$("reqs").style.display = "inline-block";}
					if($("reqs")) {$("reqs").innerHTML = "Open Requests = " + the_id_arr[7];}
					} else if (the_id_arr[8] != "0") {
					if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
					if($("reqs")) {$("reqs").style.display = "inline-block";}
					if($("reqs")) {$("reqs").innerHTML = "Requests";}
					} else {
					if($("div_requests_id")) {$("div_requests_id").innerHTML = the_id_arr[7];}
					if($("reqs")) {$("reqs").style.display = "none";}
					if($("reqs")) {$("reqs").innerHTML = "";}
					}
				if (the_id_arr[9] != $("div_imm_danger_req").innerHTML && the_id_arr[9] != 0) {
					alert("There are " + the_id_arr[9] + " urgent requests for people in imminent danger");
					$("div_imm_danger_req").innerHTML = the_id_arr[9];
					do_audible('man_down');
					}

				var temp2 =  parseInt(the_id_arr[10]);			// unit?	9/10/13
				var temp3 =  parseInt(the_id_arr[11]);			// status?	9/10/13
				var temp4 =  the_id_arr[11].trim();				// unit timestamp?	9/10/13
				if ((temp2 != the_unit) || (temp3 != the_status) || (temp4 != the_time)) {
					if(the_unit != 0) {
						if(typeof parent.frames["main"].get_unit_assigns == 'function') {
							parent.frames["main"].get_unit_assigns(the_unit);
							}
						}
					the_unit = temp2;	//		9/10/13
					the_status = temp3;	//		9/10/13
					the_time =  temp4;	// timestamp this unit, 	9/10/13
					un_stat_chg(the_unit, the_status);	//		9/10/13
					}
				if(the_id_arr[15] == 1) {
					$('all_hands').style.display = "inline-block";
					} else {
					$('all_hands').style.display = "none";
					}
				if(the_id_arr[15] != all_hands) {
					do_all_hands(); 
					all_hands = the_id_arr[15];
					}	//	For All hands function
				if(the_id_arr[16] != 0) {
					if(the_id_arr[16] != emergency_messages) {
						emergency_messages = the_id_arr[16];
						do_emergency_alerts();
						} else {
						if(emergency_messages != 0) {
							if(emerwindow && !emerwindow.closed) {emerwindow.focus();}
							}
						}
					}
				get_current_user_id();
				mu_get();				// start loop
				var is_messaging = parseInt("<?php print get_variable('use_messaging');?>");
				if((is_messaging == 1) || (is_messaging == 2) || (is_messaging == 3)) {
					get_msgs();
					nm_init();
					}
				}
			}				// end function init_cb()
		}				// end function mu_init()

	function nm_init() {								// get initial values from server -  10/23/12, 1/30/14
		var randomnumber=Math.floor(Math.random()*99999999);
		if (nmis_initialized) { return; }
		nmis_initialized = true;
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,msg_cb, "");
			function msg_cb(req) {
				var the_msg_arr=JSON.decode(req.responseText);
				var the_number = parseInt(the_msg_arr[0][0]);
				unread_messages = the_number;
				if(unread_messages != 0) {
					if($("msg")) {$("msg").innerHTML = "Msgs (" + unread_messages + ")";}
					msg_signal_o();
					} else {
					if($("msg")) {$("msg").innerHTML = "Msgs";}
					msg_signal_o_off();
					}
				new_msgs_get();
				}			// end function msg_cb()
		}				// end function nm_init()
// for messages
	function get_msgs() {	//	10/23/12
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			var url = "./ajax/get_messages.php?version=" + randomnumber;
			xmlHttp = new XMLHttpRequest();
			xmlHttp.open("GET", url, true);
			xmlHttp.onreadystatechange = handleRequestStateChange;
			xmlHttp.send(null);
			}
		}

	function handleRequestStateChange() {	//	10/23/12, 1/30/14
		var the_resp;
		var the_val;
		if (xmlHttp.readyState == 4) {
			if (xmlHttp.status == 200) {
				var response = JSON.decode(xmlHttp.responseText);
				if(!response) {return;}
				for(var key in response[0]) {
					the_resp = key;
					the_val = response[0][key];
					un_stat_chg(the_resp, the_val);
					}
				if(response[1]) {
					var the_mess = response[1][0];
					var the_stored = response[1][1];
					if(the_stored != 0) {
						show_msg("There are " + the_stored + " new messages");
						msg_signal_r();								// light the msg button
						} else {
						msg_signal_r_off();								// unlight the msg button
						}
					}
				}
			}
		messages_get();
		}
		
	function get_current_user_id() {
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('./get_current_user_id.php?version=' + randomnumber,userid_cb, "");
		function userid_cb(req) {
			var the_resp_arr = JSON.decode(req.responseText);
			var theCurrentUser = the_resp_arr[0];
			$('user_id').innerHTML = theCurrentUser;
			}
		}

	function do_set_sess_exp() {			// set session expiration  - 1/11/10
		var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('set_cook_exp.php?version=' + randomnumber,set_cook_exp_handleResult, "");
		}

	function set_cook_exp_handleResult() {
		}

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}

	function syncAjax(strURL) {							// synchronous ajax function - 4/5/10
		if (window.XMLHttpRequest) {
			AJAX=new XMLHttpRequest();
			}
		else {
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);
			AJAX.send(null);							// form name
			return AJAX.responseText;
			}
		else {
			alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
			return false;
			}
		}		// end function sync Ajax()

	function do_audible(element) {	// 6/12/10
		element = typeof element !== 'undefined' ? element : 'incident';
		if(sounds[element] == 0) {return;}
		var theID = element + "_alert";
		try 		{
			document.getElementById(theID).currentTime=0;
			document.getElementById(theID).play();
			}
		catch (e) 	{
			console.log(e);
			}		// ignore
		}				// end function do_audible()

	var linect = 0;
		
	function get_line_count() {
		var randomnumber=Math.floor(Math.random()*99999999);
		var url = "do_get_line_ct.php?version=" + randomnumber;
		sendRequest (url,lc_cb, "");
		function lc_cb(req) {
			var payload = req.responseText;
			linect = payload;		
			}
		}		// end function get line_count()

	function chat_signal() {									// light the button
		CngClass("chat", "signal_r");
		lit["chat"] = true;
		do_audible('Chat');				// 6/12/10
		}
	function unit_signal() {										// light the units button and - if not already lit red - the situation button
		do_audible('Responder');
		if (lit["main"]) {return; }									// already lit - possibly red
		CngClass("main", "signal_b");
		lit["main"] = true;
		}
	function msg_signal() {										// light the msg button, 10/23/12, 1/30/14
		if (lit["msg"]) {return; }									// already lit - possibly red
		CngClass("msg", "signal_b");
		lit["msg"] = true;
		}
	function msg_signal_r() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_r["msg"]) {return; }									// already lit - possibly red
		CngClass("msg", "signal_r");
		lit_r["msg"] = true;
		do_audible('Message');				// 1/20/14
		}

	function msg_signal_r_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_r["msg"]) {return; }									// not lit ignore
		if(unread_messages != 0) {
			CngClass("msg", "signal_o");
			lit_o["msg"] = true;
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_r["msg"] = false;
		lit_o["msg"] = true;
		}
	function msg_signal_o() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_o["msg"]) {return; }									// already lit - possibly red
		if (lit_r["msg"]) {return; }
		CngClass("msg", "signal_o");
		lit_o["msg"] = true;
		}

	function new_signal_off() {
		if(current_butt_id = "add") {
			lit["add"] = false;
			CngClass("add", "plain");
			CngClass("main", "signal_w");
			}
		}				// end function go there ()

	function msg_signal_o_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_o["msg"]) {return; }									// not lit ignore
		if (lit_r["msg"]) {
			CngClass("msg", "signal_r");
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_o["msg"] = false;
		}
	function tick_signal() {										// red light the button
		CngClass("main", "signal_r");
		lit["main"] = true;
		do_audible('Incident');				// 6/12/10
		}
																	// 2/25/12
	function misc_signal() {										// blue light to situation button if not already lit
		if (lit["main"]) {return; }									// already lit - possibly red
		CngClass("main", "signal_b");
		lit["main"] = true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function do_logout_hover (the_id) {
		if (the_id == current_butt_id) {return true;}				// 8/21/10
		CngClass(the_id, 'hover_logout text_red text_biggest');
		return true;
		}
	function do_logout_plain (the_id) {				// 8/21/10
		if (the_id == current_butt_id) {return true;}
		CngClass(the_id, 'plain_logout text_red text_biggest');
		return true;
		}		
	function do_hover (the_id) {
		if (the_id == current_butt_id) {return true;}				// 8/21/10
		if (lit[the_id] || lit_o[the_id]) {return true;}
		CngClass(the_id, 'hover');
		return true;
		}
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
	function do_plain (the_id) {				// 8/21/10
		if (the_id == current_butt_id) {return true;}
		if (lit[the_id] || lit_o[the_id]) {return true;}
		CngClass(the_id, 'plain');
		return true;
		}
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}
	function do_signal (the_id) {		// lights the light
		lit[the_id] = true;
		CngClass(the_id, 'signal');
		return true;
		}
	function do_off_signal (the_id) {
		CngClass(the_id, 'plain')
		return true;
		}

	function light_butt(btn_id) {				// 8/24/10 -
		CngClass(btn_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == btn_id)) {
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = btn_id;				//
		}				// end function light_butt()

	function go_there (where, the_id) {		//
		CngClass(the_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == the_id)) {
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = the_id;				// 8/21/10
		lit[the_id] = false;
		document.go.action = where;
		document.go.submit();
		}				// end function go there ()

	function go_there_win(where, the_id) {
		CngClass(the_id, 'signal_w')                    // highlight this button
                if(!(current_butt_id == the_id)) {
                        do_off_signal (current_butt_id);        // clear any prior one if different
                        }
                current_butt_id = the_id;                               // 8/21/10
                lit[the_id] = false;
		newwindow_c=window.open(where, "New Ticket",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		}

	function show_msg (msg) {
		$('sys_msg_span').innerHTML = msg;
		setTimeout("$('sys_msg_span').innerHTML =''", 3000);	// show for 3 seconds
		}

	function logged_in() {								// returns boolean
		var temp = $("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function do_logout() {						// 10/27/08
		$("user_id").innerHTML  = 0;
		$('time_of_day').innerHTML="";
		clearInterval(mu_interval);
		mu_interval = null;
		clearInterval(nm_interval);	//	10/23/12
		nm_interval = null;	//	10/23/12
		clearInterval(msgs_interval);	//	10/23/12
		msgs_interval = null;	//	10/23/12
		clearInterval(emsgs_interval);	//	10/23/12
		emsgs_interval = null;	//	10/23/12
		$('whom').innerHTML=NOT_STR;
		is_initialized = false;
		nmis_initialized = false;	//	10/23/12
		if(ticker_active == 1) {
			clearInterval(ticker_interval);
			var ticker_interval = null;
			ticker_is_initialized = false;
		}

		try {						// close() any open windows
			newwindow_c.close();
			}
		catch(e) {
			}
		try {
			newwindow_sl.close();
			}
		catch(e) {
			}
		try {
			newwindow_cb.close();
			}
		catch(e) {
			}
		try {
			newwindow_fs.close();
			}
		catch(e) {
			}
		try {
			newwindow_em.close();
			}
		catch(e) {
			}

		newwindow_sl = newwindow_cb = newwindow_c = newwindow_fs = newwindow_em = null;

		hide_butts();		// hide buttons

<?php if (get_variable('call_board') == 2) { ?>

		parent.document.getElementById('the_frames').setAttribute('rows', '<?php print (get_variable('framesize') + 25);?>, 0, *'); // 7/21/10

<?php } ?>

		$('gout').style.display = 'none';		// hide the logout button
		document.gout_form.submit();			// send logout
		}
	function hide_butts() {						// 10/27/08, 3/15/11
		setTimeout(" $('buttons').style.display = 'none';" , 500);
		$("daynight").style.display = "none";				// 5/2/11
		$("main_body").style.backgroundColor  = "<?php print get_css('page_background', 'Day');?>";
		$("main_body").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("tagline").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("user_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("unit_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("script").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("time_of_day").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("whom").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("level").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("perms_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("modules_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		$("time_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
		try {
			$('manual').style.display = 'none';		// hide the manual link	- possibly absent
			}
		catch(e) {
			}
		}

	function show_butts() {
		$("buttons").style.display = "inline";
		$("daynight").style.display = "inline";
		$("has_form_row").style.display = "none";
		$("has_message_row").style.display = "none";
		buttons = true;
		}
//	============== module window openers ===========================================

	function open_FWindow(theFilename) {
		var url = theFilename;
		var ofWindow = window.open(url, 'ViewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		setTimeout(function() { ofWindow.focus(); }, 1);
		}

	var newwindow_sl = null;
	var starting;

	function do_sta_log() {				// 1/19/09
		light_butt('log') ;
		if ((newwindow_sl) && (!(newwindow_sl.closed))) {newwindow_sl.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			newwindow_sl=window.open("log.php", "sta_log",  "titlebar, location=0, resizable=1, height=400,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=100,screenX=100,screenY=100");
			if (isNull(newwindow_sl)) {
				alert ("Station log operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_sl.focus();
			starting = false;
			}
		}		// end function do sta_log()

	var newwindow_msg = null;
	function do_mess() {				// 10/23/12
		light_butt('msg') ;
		if ((newwindow_msg) && (!(newwindow_msg.closed))) {newwindow_msg.focus(); return;}		// 10/23/12
		if (logged_in()) {
			if(starting) {return;}
			starting=true;
			do_set_sess_exp();		// session expiration update
			newwindow_msg=window.open("messages.php", "messages",  "titlebar, location=0, resizable=1, scrollbars=no, height=600,width=950,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300");
			if (isNull(newwindow_msg)) {
				alert ("Viewing messages requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_msg.focus();
			starting = false;
			}
		}		// end function do sta_log()

	var newwindow_cb = null;
	function do_callBoard() {
		light_butt('call');
		get_line_count();
		if ((newwindow_cb) && (!(newwindow_cb.closed))) {newwindow_cb.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			var the_height = 60 + (16 * linect);
			var the_width = (2.0 * Math.floor((Math.floor(.90 * screen.width) / 2.0)));
			newwindow_cb=window.open("board.php", "callBoard",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+", width="+the_width+", status=0,toolbar=0,menubar=0,location=0, left=20,top=300,screenX=20,screenY=300");
			if (isNull(newwindow_cb)) {
				alert ("Call Board operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_cb.focus();
			starting = false;
			}
		}		// end function do callBoard()
	var newwindow_c = null;

	function chat_win_close() {				// called from chat.pgp
		newwindow_c = null;
		}
	function do_chat() {
		light_butt('chat') ;
		if ((newwindow_c) && (!(newwindow_c.closed))) {newwindow_c.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}					// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			try {
				newwindow_c.focus();
				}
			catch(e) {
				}

			newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (isNull(newwindow_c)) {
				alert ("Chat operation requires popups to be enabled. Please adjust your browser options - or else turn off the Chat option setting.");
				return;
				}
			newwindow_c.focus();
			starting = false;
			CngClass("chat", "plain");

			}
		}
		
	var newwindow_fs = null;
	function do_full_scr() {                            //9/7/09
		light_butt('full');
		if ((newwindow_fs) && (!(newwindow_fs.closed))) {newwindow_fs.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}                        // 4/15/10 fullscreen=no
			do_set_sess_exp();		// session expiration update
			if(window.focus() && newwindow_fs) {newwindow_fs.focus()}    // if already exists
			starting=true;
			params  = 'width='+screen.width;
			params += ', height='+screen.height;
			params += ', top=0, left=0, scrollbars = 1';
			params += ', resizable=1';
			newwindow_fs=window.open("full_scr.php", "full_scr", params);
			if (isNull(newwindow_fs)) {
				alert ("This operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_fs.focus();
			starting = false;
			}
		}        // end function do full_scr()
		
	var newwindow_wsm = null;
	function do_wsm_scr() {                            //9/7/09
		if ((newwindow_wsm) && (!(newwindow_wsm.closed))) {newwindow_wsm.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}                        // 4/15/10 fullscreen=no
			do_set_sess_exp();		// session expiration update
			if(window.focus() && newwindow_wsm) {newwindow_wsm.focus()}    // if already exists
			starting=true;
			params  = 'width=500';
			params += ', height=400';
			params += ', top=100, left=100, scrollbars = 0';
			params += ', resizable=1';
			newwindow_wsm=window.open("ws_monitor.php", "Websocket_Monitor", params);
			if (isNull(newwindow_wsm)) {
				alert ("This operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_wsm.focus();
			starting = false;
			}
		}        // end function do full_scr()

	function do_emd_card(filename) {
		light_butt('card') ;
		try {
			newwindow_em=window.open(filename, "emdCard",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
			}
		catch (e) {
			}
		try {
			newwindow_em.focus();;
			}
		catch (e) {
			}
		if (isNull(newwindow_em)) {
			alert ("SOP Doc's operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		starting = false;
		}

<?php
$start_up_str = 	(array_key_exists('user', $_SESSION))? "": " mu_init();";
$the_userid = 		(array_key_exists('user_id', $_SESSION))? $_SESSION['user_id'] : "na";
$the_whom = 		(array_key_exists('user', $_SESSION))? $_SESSION['user']: NOT_STR;
$the_level = 		(array_key_exists('level', $_SESSION))? get_level_text($_SESSION['level']):"na";

$day_night = (array_key_exists('day_night', $_SESSION)) ? $_SESSION['day_night'] : 'Day';
print "\n\t var the_whom = '{$the_whom}'\n";
print "\t var the_level ='{$the_level}'\n";

function get_daynight() {
	$day_night = ((array_key_exists('day_night', $_SESSION)) && ($_SESSION['day_night'])) ? $_SESSION['day_night'] : 'Day';
	return $day_night;
	}
?>
	function guest_hide_buttons(level) {
		if((level == "Guest") || (level == 1)) {
			window.guest = 1;
			if($("msg")) {$("msg").style.display  = "none";}
			if($("reps")) {$("reps").style.display  = "none";}
			if($("conf")) {$("conf").style.display  = "none";}
			if($("card")) {$("card").style.display  = "none";}
			if($("chat")) {$("chat").style.display  = "none";}
			if($("log")) {$("log").style.display  = "none";}
			if($("rc")) {$("rc").style.display  = "none";}
			if($("links")) {$("links").style.display  = "none";}
			if($("call")) {$("call").style.display  = "none";}
			if($("term")) {$("term").style.display  = "none";}
			if($("reqs")) {$("reqs").style.display  = "none";}
			if($("ics")) {$("ics").style.display  = "none";}
			if($("has_button")) {$("has_button").style.display  = "none";}
			}
		}
		
	function unit_hide_buttons() {
//		alert("Hide Buttons for Restricted Units");
		if(the_level == "Unit") {
			if($("buttons")) {$("buttons").style.display  = "none";}
			}
		}

	function top_init() {					// initialize display
		CngClass('main', 'signal_w');		// light up 'sit' button - 8/21/10
		$("whom").innerHTML  =	the_whom;
		$("level").innerHTML =	the_level;
		do_time();
<?php												// 5/4/11
		if (empty($_SESSION)) {						// pending login
			$day_checked = $night_checked = "";
			$day_disabled = $night_disabled= "DISABLED";
			} else {				// logged-in
			if($start_up_str == 'Day') {	//	7/16/13	Revised to fix error on initial startup
				$day_checked = "CHECKED";			// allow only 'night'
				$day_disabled = "DISABLED";
				$night_checked = "";
				$night_disabled = "";
				} else {
				$day_checked = "";					//  allow only 'day'
				$day_disabled = "";
				$night_checked = "CHECKED";
				$night_disabled = "DISABLED";
				}
?>
			var current_user_id = "<?php print $the_userid;?>";
			guest_hide_buttons();
			show_butts();	// navigation buttons
			$("gout").style.display  = "inline-block";								// logout button
			$("whom").innerHTML  = "<?php print $the_whom;?>";			// user name, 7/1613
			$("level").innerHTML = "<?php print $the_level;?>";		//	7/16/13
			mu_init();			// start polling
<?php
			}				// end if/else (empty($_SESSION))
?>
		}		// end function top_init()

	function do_log(instr) {
		$('log_div').innerHTML += instr + "<br />";
		}

	function get_new_colors() {										// 5/4/11 - a simple refresh
		window.location.href = '<?php print basename(__FILE__);?>';
		}
		
	var daynight = [];
	daynight['day_but'] = true;
	daynight['night_but'] = false;		
	
	function do_is_set(the_id) {
		CngClass(the_id, 'isselected text')
		return true;
		}
		
	function do_not_set(the_id) {
		CngClass(the_id, 'plain text')
		return true;			
		}

	var selecteddaynight = "<?php print $day_night;?>";
		
	function set_daynight_but(val) {
		if(val == "Day") {
			do_is_set("day_but"); 
			do_not_set("night_but"); 
			daynight['day_but'] = true;
			lit['day_but'] = true;
			daynight['night_but'] = false;
			lit['night_but'] = false;
			selecteddaynight="Day";
			} else {
			do_is_set("night_but"); 
			do_not_set("day_but"); 
			daynight['day_but'] = false;
			lit['day_but'] = false;
			daynight['night_but'] = true;
			lit['night_but'] = true;
			selecteddaynight="Night";		
			}
		}

	function set_day_night(which) {			// 5/2/11
		sendRequest ('./ajax/do_day_night_swap.php', day_night_callback, "");
		function day_night_callback(req) {
			var the_ret_val = req.responseText;
			try {
				parent.frames["main"].get_new_colors();			// reloads main frame
				}
			catch (e) {
				}
			window.clearInterval(mu_interval);
			window.clearInterval(nm_interval);	//	10/23/12
			window.clearInterval(msgs_interval);	//	10/23/12
			window.clearInterval(emsgs_interval);	//	10/23/12
			get_new_colors();								// reloads top
			set_daynight_but(which);
			}									// end function day_night_callback()
		}

	function do_manual(filename){							// launches Tickets manual page -  5/27/11
		try {
			newwindow_em=window.open(filename, "Manual",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=20,top=20,screenX=20,screenY=20");
			}
		catch (e) {
			}
		try {
			newwindow_em.focus();;
			}
		catch (e) {
			}
		}		// end do_manual()

		function can_has () {							// cancel HAS function - return to normal display
			$("has_form_row").style.display = "none";
			show_butts();								// show buttons
			}
		function end_message_show() {
			setTimeout(function(){
				$("has_message_row").style.display = $("has_form_row").style.display = "none";
				$("has_form_row").style.display = "none";
				show_butts();								// show buttons
				}, 1000);			// end setTimeout()
			}					// end function

<?php				// 7/2/2013
		if ((intval( get_variable ('broadcast')==1)) &&  (intval(get_variable ('internet')==1))) { 		//
?>
			function do_broadcast() {
				if(hasUsercount > 1) {
					hide_butts();
					$("has_form_row").style.display = "inline-block";
					$('has_send').style.display = "inline";
					$('has_cancel').style.display = "inline";		
					$("has_message_row").style.display = "none";
					document.has_form.has_text.focus()
					} else {
					hide_butts();
					$("has_form_row").style.display = "none";
					$("has_message_text").innerHTML = "There are no other users connected, messages will not be sent";
					CngClass("has_message_text", "heading");
					$("has_message_row").style.display = "block";	// include button		
					}
				}
			function has_check(inStr) {
				if (inStr.trim().length == 0) { alert("Value required - try again."); return;}
				else {
					var msg =  $("whom").innerHTML + " sends: " + inStr.trim(); // identify sender
					broadcast(msg, 1); 				// send it
					setTimeout(function(){
						CngClass("has_text", "heading");
						$('has_send').style.display = "none";
						$('has_cancel').style.display = "none";						
						document.has_form.has_text.value = "              Sent!";		// note spaces
						setTimeout(function(){
							document.has_form.has_text.value = "";
							$("has_form_row").style.display = "none";		// hide the form row
							CngClass("has_text", "");
							show_butts();								// back to normal
							}, 3000);
						}, 1000);
					}		// end else{}
				}		// end function has_check()

			function hide_has_message_row() {
				$("msg_span").style.display = "none";
				show_butts();								// show buttons
				}

			function show_has_message(in_message) {
				hide_butts();											// make room
				$("has_message_text").innerHTML = in_message;			// the message text
				CngClass("has_message_text", "heading");
				$("has_message_row").style.display = "inline-block";	// include button
				$("has_ok").style.display = "inline-block";	// include button
				}
<?php
			}			// end if (broadcast && internet )
?>
	</SCRIPT>
</HEAD>
<BODY ID="main_body" onLoad = "top_init(); set_fontsizes(viewportwidth,'fullscreen');">
<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV>
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV ID = "div_requests_id" STYLE="display:none;"></DIV>
<DIV ID = "div_imm_danger_req" STYLE="display:none;"></DIV>
	<TABLE ALIGN='left' style='width: 100%;'>
		<TR VALIGN='top' style='height: 30px; vertical-align: middle;'>
			<TD ROWSPAN=4 style='padding-left: 20px; width: 50px;'><IMG SRC="<?php print get_variable('logo');?>" BORDER=0/></TD>
			<TD style='text-align: left;'>
<?php

			$temp = get_variable('_version');				// 8/8/10
			$version_ary = explode ( "-", $temp, 2);
			if(get_variable('title_string')=="") {
				$title_string = "<FONT SIZE=3 aria-hidden='true'>ickets " . trim($version_ary[0]) . " on <B>" . get_variable('host') . "</B></FONT>";
				} else {
				$title_string = "<FONT SIZE=3 aria-hidden='true'><B>" . get_variable('title_string') . "</B></FONT>";
				}
?>
				<SPAN ID="tagline" CLASS="titlebar_text text_large" aria-hidden='true' style='vertical-align: middle;'><?php print $title_string; ?></SPAN>
				<SPAN ID="logged_in_txt" STYLE='margin-left: 8px;' CLASS="titlebar_text text" aria-hidden='true' style='vertical-align: middle;'><?php print get_text("Logged in"); ?>:</SPAN>
				<SPAN ID="whom" CLASS="titlebar_text text" aria-hidden='true' style='vertical-align: middle;'><?php print NOT_STR;?></SPAN>
				<SPAN ID="perms_txt" CLASS="titlebar_text text" aria-hidden='true' style='vertical-align: middle;'>:</SPAN>
				<SPAN ID="level" CLASS="titlebar_text text" style='vertical-align: middle;'>na</SPAN>&nbsp;&nbsp;&nbsp;
<?php
			$temp = get_variable('auto_poll');

			$dir = "./emd_cards";
			if (file_exists ($dir)) {
				$dh  = opendir($dir);
				while (false !== ($filename = readdir($dh))) {
					if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
						$card_file = $filename;						// at least one pdf, use first encountered
						break;
						}
					}
				$card_addr=(!empty($card_file))? $dir . "/" . $filename  : "";
				}
?>
				<SPAN ID='user_id' STYLE="display: none; vertical-align: middle;" aria-hidden='true' CLASS="titlebar_text text">0</SPAN>
				<SPAN ID='unit_id' STYLE="display: none; vertical-align: middle;" aria-hidden='true' CLASS="titlebar_text text"></SPAN>
				<SPAN ID='modules_txt' CLASS="titlebar_text text" aria-hidden='true' style='vertical-align: middle;'><?php print get_text("Module"); ?>: </SPAN>
				<SPAN ID="script" CLASS="titlebar_text text" aria-hidden='true' style='vertical-align: middle;'>login</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
				<SPAN ID='time_txt' CLASS="titlebar_text text_large text_bold" aria-hidden='true' style='vertical-align: middle;'><?php print get_text("Time"); ?>: </SPAN>
				<SPAN ID="time_of_day" CLASS="titlebar_text text_large text_bold" style='vertical-align: middle;'></SPAN>
				<SPAN ID='daynight' CLASS="titlebar_text text" aria-hidden='true' STYLE='display: none; vertical-align: middle;'>
<?php
					$butText = (($day_checked == "CHECKED") || ($night_checked == "" && $day_checked == "")) ? "Night" : "Day";
?>
					<SPAN ID='day_but' CLASS='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='set_day_night("Day");'>Day</SPAN>
					<SPAN ID='night_but' CLASS='plain text' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='set_day_night("Night");'>Night</SPAN>
				</SPAN>
				<DIV id='broadcastWrapper' class='plain text' TITLE='Click to Open Websocket Server Monitor' style='display: none; float: none; vertical-align: middle;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='do_wsm_scr();' />
					<SPAN ID = 'timeText' CLASS="text" style='float: left; display: inline;' /></SPAN>&nbsp;&nbsp;
					<SPAN ID = 'usercount' CLASS="text" style='float: right; display: inline;' /></SPAN>
				</DIV>
				&nbsp;&nbsp;<SPAN ID="all_hands" CLASS="plain text" style='display: none; vertical-align: middle; background-color: yellow; color: red; float: right;'>All Hands Set</SPAN>
<?php				// 5/26/11
				$dir = "./manual";
				if (file_exists ($dir)) {
					$dh  = opendir($dir);
					while (false !== ($filename = readdir($dh))) {
						if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
							$manual_file = $filename;						// at least one pdf, use first encountered
							break;
							}
						}
					$manual_addr=(!empty($manual_file))? $dir . "/" . $filename  : "";
					}
				if (!(empty($manual_addr))) {
?>

					<SPAN ID='manual' aria-hidden='true' CLASS="titlebar_text text" onClick = "do_manual('<?php echo $manual_addr;?>');" STYLE="display: none;"><U>Manual</U></SPAN>
<?php
					}
?>
				
<?php
				if ($_SERVER['HTTP_HOST'] == "127.0.0.1") { print "&nbsp;&nbsp;&nbsp;&nbsp;DB:&nbsp;{$mysql_db}&nbsp;&nbsp;&nbsp;&nbsp;";}
?>

				<SPAN ID='sys_msg_span' CLASS = 'text_bold text_large' style='vertical-align: middle; color: blue; background-color: yellow;'></SPAN>
			</TD>
			<TD ROWSPAN='2'>
				<SPAN ID='gout' CLASS='plain_logout text_biggest text_red' onMouseOver="do_logout_hover(this.id);" onMouseOut="do_logout_plain(this.id);" onClick="do_logout();" STYLE="display: none; float: right; vertical-align: middle;"><SPAN STYLE='float: left; vertical-align: middle;'><?php print get_text("Logout");?></SPAN>&nbsp;&nbsp;&nbsp;<IMG STYLE='float: right;' SRC='./images/close_door.png' BORDER=0></SPAN>
<!--			<SPAN id='memory' CLASS='text' style='padding-left: 10px; padding-right: 10px; display: inline-block; float: right; width: auto; height: auto; background-color: #000000; color: #FFFFFF; vertical-align: middle;'></SPAN>	-->
			</TD>
		</TR>
		<TR ID = 'buttons' STYLE = "display: none;">
			<TD COLSPAN=99>
			<SPAN ID = 'main' roll='button' tabindex=1 aria-label='Situation Screen' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick ="go_there('main.php', this.id);"><?php print get_text("Situation"); ?></SPAN>
<!--		<SPAN ID = 'mi'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick ="go_there('maj_inc.php', this.id);">Maj Incs</SPAN> -->
			<SPAN ID = 'add' roll='button' tabindex=2 aria-label='New Incident'  CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('add.php', this.id);"><?php print get_text("New"); ?></SPAN>
			<SPAN ID = 'resp' roll='button' tabindex=3 aria-label='Responders Screen'  CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('units.php', this.id);"><?php print get_text("Units"); ?></SPAN>
			<SPAN ID = 'facy' roll='button' tabindex=4 aria-label='Facilities Screen' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('facilities.php', this.id);"><?php print get_text("Fac's"); ?></SPAN>
<?php
		if((!is_guest()) && ((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3))) {		//	10/23/12
?>
			<SPAN ID = 'msg' roll='button' tabindex=5 aria-label='Messages Window' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_mess();"><?php print get_text("Msgs"); ?></SPAN>
<?php
			}
?>
			<SPAN ID = 'srch' roll='button' tabindex=6 aria-label='Search' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('search.php', this.id);"><?php print get_text("Search"); ?></SPAN>
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'reps' roll='button' tabindex=7 aria-label='Reports' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('reports.php', this.id);"><?php print get_text("Reports"); ?></SPAN>
			<SPAN ID = 'conf' roll='button' tabindex=8 aria-label='Configuration' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('config.php', this.id);"><?php print get_text("Config"); ?></SPAN>
<?php
			}
		if (!(is_guest()) && !(empty($card_addr))) {
?>
			<SPAN ID = 'card' roll='button' tabindex=9 aria-label='Special Operations Procedures'  CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting = false; do_emd_card('<?php print $card_addr; ?>')"><?php print get_text("SOP's"); ?></SPAN>	<!-- 7/3/10 -->
<?php
			}
		if((!(is_guest())) && (!(intval(get_variable('chat_time')==0)))) {
?>
			<SPAN ID = 'chat' roll='button' tabindex=10 aria-label='Chat' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_chat();"><?php print get_text("Chat"); ?></SPAN>
<?php
			}
?>
			<SPAN ID = 'help' roll='button' tabindex=11 aria-label='Help' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('help.php', this.id);"><?php print get_text("Help"); ?></SPAN>
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'log' roll='button' tabindex=12 aria-label='og' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "do_sta_log()"><?php print get_text("Log"); ?></SPAN>
<?php
			}
?>
		<SPAN ID = 'full' roll='button' tabindex=13 aria-label='Full Screen Map' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_full_scr()"><?php print get_text("Full scr"); ?></SPAN>
<?php
		if(get_variable('use_mdb') == "1") {
?>
			<SPAN ID = 'personnel' roll='button' tabindex=14 aria-label='Personnel Screen' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('member.php', this.id);"><?php print get_text("Personnel"); ?></SPAN>
<?php
			}
		if (!(is_guest())) {
?>
			<SPAN ID = 'links' roll='button' tabindex=15 aria-label='Links' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "light_butt('links'); parent.main.$('links').style.display='inline';"><?php print get_text("Links"); ?></SPAN>
<?php
			}
		if (!(is_guest())) {
			$call_disp_attr = (get_variable('call_board')==1)?  "inline" : "none";
?>
			<SPAN ID = 'call' roll='button' tabindex=16 aria-label='Call Board' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false;do_callBoard()" STYLE = 'display:<?php print $call_disp_attr; ?>'><?php print get_text("Board"); ?></SPAN> <!-- 5/12/10 -->
<?php
			}
?>
<!-- ================== -->
<?php
		if (!(is_guest())) {
?>
			<SPAN ID = 'term' roll='button' tabindex=17 aria-label='Mobile Screen' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('mobile.php', this.id);"><?php print get_text("Mobile"); ?></SPAN>	<!-- 7/27/10 -->
<!-- ================== -->
			<SPAN ID = 'reqs' roll='button' tabindex=18 aria-label='Service Requests' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('./portal/requests.php', this.id);">Requests</SPAN>	<!-- 10/23/12 -->
<?php
			}
		if ((!(is_guest())) && (intval(get_variable('ics_top')==1))) { 		// 5/21/2013
?>

<!-- ================== -->			<!-- 5/13/2013 -->
			<SPAN ID = 'ics' roll='button' tabindex=19 aria-label='ICS Forms' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false;window.open('ics.php', 'ics213')"><?php print get_text("ICS-FORMS"); ?></SPAN>
<?php
			}		// end if (ics_top)

		if ((!(is_guest())) && (intval ( get_variable ('broadcast')==1 )) &&  (intval ( get_variable ('internet')==1 )) ) {
?>
			<SPAN ID = 'has_button' roll='button' tabindex=20 aria-label='Hello All Stations' CLASS = 'plain text' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "do_broadcast();"><?php echo get_text("HAS"); ?></SPAN>
<?php
	}			// end if (broadcast && internet )
?>
			</TD>
		</TR>
		<TR ID = 'has_form_row' STYLE = "display:none;">
			<TD ALIGN='CENTER'>
				<SPAN ID = "has_span" >
				<FORM NAME = 'has_form' METHOD = post ACTION = "javascript: void(0)">
				<INPUT TYPE = 'text' NAME = 'has_text' ID = 'has_text' CLASS = '' size=90 value = "" STYLE = "margin-left:6px; float: left;" placeholder="enter your broadcast message" aria-label='enter your broadcast message' />
				<SPAN class='plain' id='has_send' roll='button' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onclick = "has_check(document.has_form.has_text.value.trim())" STYLE = "margin-left:16px; display: none;" aria-label='Send message'>Send</SPAN>
				<SPAN class='plain' id='has_cancel' roll='button' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onclick = "can_has();" STYLE = "margin-left:16px; display: none;" aria-label='Cancel HAS bessage'>Cancel</SPAN>
				</FORM>
				</SPAN>
			</TD>
		</TR>
		<TR ID = 'has_message_row' STYLE = "display: none;">
			<TD ALIGN='CENTER'>
				<SPAN ID = "msg_span" STYLE = "margin-left: 50px; display: inline-block;" >
					<SPAN ID = "has_message_text" STYLE = "margin-left: 50px; display: inline-block; float: left;"></SPAN>
					<SPAN class='plain' id='has_ok' roll='button' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onclick = "end_message_show();" STYLE = "margin-left:16px; display: none;" aria-label='OK'>OK</SPAN>
				</SPAN>
			</TD>
		</TR>

<!-- ================== -->
	</TABLE>
	<FORM NAME="go" action="#" TARGET = "main"></FORM>
	<FORM NAME="gout_form" action="main.php" TARGET = "main">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>

	<P>
		<DIV ID = "log_div"></DIV>
<!-- <button onclick = 'alert(getElementById("user_id"))'>Test</button> -->
<?php
	$the_wav_file = get_variable('sound_wav');		// browser-specific cabilities as of 6/12/10
	$the_mp3_file = get_variable('sound_mp3');
	$sounds_arr = array();
	$mp3sounds_arr = array();
	$query = "SELECT `name`, `filename`, `mp3_filename`, `ison` FROM `$GLOBALS[mysql_prefix]sound_settings` ORDER BY `id`";
	$result = mysql_query($query);
	while($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if($row['ison'] == 1){
			$sounds_arr[$row['name']] = $row['filename'];
			$mp3sounds_arr[$row['name']] = $row['mp3_filename'];
			}
		}


	$temp = explode (" ", $browser);
	switch (trim($temp[0])) {
	    case "firefox" :
			foreach($sounds_arr AS $key=>$val) {
				print "\t\t<audio id=\"" . $key . "_alert\" src=\"./sounds/" . $val . "\" preload></audio>\n";
				}
			break;
	    case "chrome" :
	    case "safari" :
		case "mozilla" :
		case "gecko" :
			foreach($mp3sounds_arr AS $key=>$val) {
				print "\t\t<audio id=\"" . $key . "_alert\" src=\"./sounds/" . $val . "\" preload></audio>\n";
				}
			break;
	    default:
		}	// end switch
?>
<!--  example frame manipulation
<button onClick = "alert(parent.document.getElementById('the_frames').getAttribute('rows'));">Get</button>
<button onClick = "parent.document.getElementById('the_frames').setAttribute('rows', '600, 100, *');">Set</button>
-->
<DIV ID='test' style="position: fixed; top: 20px; left: 20px; height: 20px; width: 100px;" onclick = "location.href = '#bottom';">
	<h3></h3></DIV>
<!-- <button onclick = "show_has_message('asasasasas ERERERERER ')">Test</button> -->
<?php							// 7/2/2013
	if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
		require_once('./incs/socket2me.inc.php');		// 5/24/2013
		}
?>
<SCRIPT>
set_daynight_but(selecteddaynight);
</SCRIPT>
</BODY>
</HTML>
