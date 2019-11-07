<?php
define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
require_once('./incs/functions.inc.php');
require_once('./incs/messaging.inc.php');
require_once './lib/xpm/POP3.php';
require_once './lib/xpm/MIME.php';
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);
@session_start();
session_write_close();
$the_result = "";
if (empty($_SESSION)) {
	header("Location: index.php");
	}
do_login(basename(__FILE__));

define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors

$url = get_msg_variable('email_server');
$port = intval(get_msg_variable('email_port'));
$protocol = get_msg_variable('email_protocol');
$addon = get_msg_variable('email_addon');
$folder = get_msg_variable('email_folder');
$user = get_msg_variable('email_userid'); 
$password = get_msg_variable('email_password');
$ssl = 'ssl';
$timeout = 60;
$simple = get_msg_variable('email_svr_simple');

function get_the_emails() {
	global $simple, $url, $user, $password, $port, $ssl, $timeout;
	if($simple == 1) {
		$c = POP3::connect($url, $user, $password) or die(dump($_RESULT));
		} else {
		$c = POP3::connect($url, $user, $password, $port, $ssl, $timeout) or die(dump($_RESULT));
		}
	// STAT
	if(!$c) {
		print "Cannot connect to IC Email server.<BR />";
		exit();	
		}
	$s = POP3::pStat($c);
	// $i - total number of messages, $b - total bytes
	print "Total Messages = " . $i . "<BR />";
	list($i, $b) = each($s);
	$x = intval($i);
	if ($x >= 1) { // if we have messages
		$the_message = array();
		for($z = $x; $z = $x -5; $z--) {
			$the_message[$z]['id'] = $z;
			// RETR
			$r = POP3::pRetr($c, $z); // <- get the last mail (newest)
			$m = MIME::split_message($r);
			$split = MIME::split_mail($r, $headers, $body);	
			if($headers && $body) {
				$y = 0;
				foreach($headers AS $val) {
					if($val['name'] == "From") { $the_message[$z]['from'] = GetBetween($val['value'],'<','>'); $thename = explode("<", $val['value']); $fromname = $thename[0]; } 
					if($val['name'] == "To") { $the_message[$z]['to'] = $val['value']; } 
					if($val['name'] == "Subject") { $the_message[$z]['subject'] = $val['value']; } 
					if($val['name'] == "Date") { $the_message[$z]['date'] = $val['value']; } 
					$y++;
					}
				$the_message[$z]['text'] = addslashes(htmlentities($body[0]['content']));
				$from = $the_message[$z]['from'];
				$subject = $the_message[$z]['subject'];
				$date = date_parse($the_message[$z]['date']);				
				$datepart = $date['year'] . "-" . $date['month'] . "-" . $date['day'];
				$timepart = $date['hour'] . ":" . $date['minute'] . ":" . $date['second'];
				$datestring = $datepart . " " . $timepart;	
				}
			print $from . " - " . $subject . " - " . $date . "<BR />";
			}
		// optional, you can delete this message from server
		//	POP3::pDele($c, $i);
		} else {
		print "There are no messages to show at this time<BR />";
		}
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Incoming email messages test script</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
<SCRIPT SRC="./js/messaging.js" TYPE="application/x-javascript"></SCRIPT><!-- 10/23/12-->
</HEAD>
<?php
print $url . "," . $user . "," . $password . "," . $port . "," .  $ssl . "," . $timeout . "<BR />"; 
get_the_emails();
?>	
