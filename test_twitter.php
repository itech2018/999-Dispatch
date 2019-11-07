<?php
define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
require_once('./incs/functions.inc.php');
require_once './lib/twitter/twitter.class.php';
error_reporting(E_ALL);				// 9/13/08
set_time_limit(0);
@session_start();
session_write_close();
$the_result = "";
if (empty($_SESSION)) {
	header("Location: index.php");
	}
do_login(basename(__FILE__));

$consumerKey = get_variable('twitter_consumerkey');
$consumerSecret = get_variable('twitter_consumersecret');
$accessToken = get_variable('twitter_accesstoken');
$accessTokenSecret = get_variable('twitter_accesstokensecret');

function test_tweets() {
	global $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret;
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$statuses = $twitter->load(Twitter::ME_AND_FRIENDS);
	$print = "";
	$print .= "<ul>";
	foreach($statuses as $status) {
		$print .= "<li><a href='http://twitter.com/" . $status->user->screen_name . "' target='_blank'><img src='" . htmlspecialchars($status->user->profile_image_url) . "'>";
		$print .= htmlspecialchars($status->user->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($status);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($status->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}
	
function test_rec_direc($count = 20) {
	global $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret;
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$messages = $twitter->rec_direct($count);
	$print = "";
	$print .= "<ul>";
	foreach($messages as $message) {
		$print .= "<li><a href='http://twitter.com/" . $message->recipient->screen_name . "' target='_blank'>";
		$print .= htmlspecialchars($message->recipient->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($message);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($message->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}
	
function test_sent_direc($count = 20) {
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	$messages = $twitter->sent_direct($count);
	$print = "";
	$print .= "<ul>";
	foreach($messages as $message) {
		$print .= "<li><a href='http://twitter.com/" . $message->user->screen_name . "' target='_blank'>";
		$print .= htmlspecialchars($message->user->name) . "</a>&nbsp;&nbsp;";
		$print .= Twitter::clickable($message);
		$print .= "<small> at " . date('j.n.Y H:i', strtotime($message->created_at)) . "</small>";
		$print .= "</li>";
		}
	$print .= "</ul>";
	return $print;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Twitter Timeline test script</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT TYPE="application/x-javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->	
<SCRIPT TYPE="application/x-javascript" SRC="./js/domready.js"></script>
</HEAD>
<BODY>
	<DIV id='outer' class='even' style='height: 90%; padding: 10px;'>
		<DIV id='header' class='but_container'>
			<SPAN class='heading' style='float: none; display: inline-block; text-align: center; vertical-align: middle; font-size: 1.3em;'>Twitter Home</SPAN>
			<SPAN id='close_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'>Close</SPAN>
			<SPAN id='print_but' class='plain' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.print();'>Print</SPAN>
		</DIV>
		<DIV id='main' style='position: relative; top: 40px;'>
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Received Direct Messages</SPAN>
			<DIV style="height: 110px; overflow-y: scroll;"><?php echo test_rec_direc(20);?></DIV><BR />
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Sent Direct Messages</SPAN>
			<DIV style="height: 110px; overflow-y: scroll;"><?php echo test_sent_direc(20);?></DIV><BR />
			<SPAN class='header' style='display: block; font-size: 1.2em;'>Timeline</SPAN>
			<DIV style="height: 220px; overflow-y: scroll;"><?php echo test_tweets();?></DIV><BR />
		</DIV>
	</DIV>
</BODY>
