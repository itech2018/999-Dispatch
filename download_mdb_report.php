<?php
require_once('./incs/functions.inc.php');
include("./incs/html_to_doc.inc.php");
@session_start();
session_write_close();
$randomnumber = rand(0000000 , 9999999);
$currDate = date('m,d,Y');
$title = "Full_MDB_Report";
$httpuser = get_variable('httpuser');
$httppwd = get_variable('httppwd');
$mode = "xls";
$member = (array_key_exists('member', $_GET)) ? $_GET['member'] : 0;
$team = (array_key_exists('team', $_GET)) ? $_GET['team'] : 0;

function curPageURL() {
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$uri;
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$uri;
		}
	return $pageURL;
	}
	
$serverpath = curPageURL();

$url = $serverpath . "/ajax/download_full_mdb_report.php?member=" . $member . "&team=" . $team . "&q=" . $_GET['q'] . "&version=" . $randomnumber;
if (function_exists("curl_init")) {
	$ch = curl_init();
	$timeout = 20;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if(($httpuser!="") && ($httppwd!="")) {
		$security = $httpuser .":" . $httppwd;
		curl_setopt($ch, CURLOPT_USERPWD, $security);
		}
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "sessid=" . $_SESSION['id']);
	$thePage = curl_exec($ch);
	$thePage = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)  ([\"'>]+)#",'$1'.$url.'$2$3', $thePage);
	curl_close($ch);
	} else {				// no CURL
	if ($fp = @fopen($url, "r")) {
		while (!feof($fp) && (strlen($thePage)<9000)) $thePage .= fgets($fp, 128);
		fclose($fp);
		}
	}

//$htmltodoc= new HTML_TO_DOC();

//$htmltodoc->createDoc($thePage,"test");
//$htmltodoc->createDocFromURL($url,"test");
	
//echo $htmltodoc;

$reportname = $title;
$str     = $reportname;
$order   = array(" ", ",");
$replace = '_';

// Processes \r\n's first so they aren't converted twice.
$reportname = str_replace($order, $replace, $str);
$header = "<TABLE style='width: 90%;'>";
$header .= "<TR><TD style='text-align: left;'><IMG src='" . get_variable('report_graphic') . "' /></TD>";
$header .= "<TD style='text-align: right;'>Contact: " . get_variable('report_contact') . "</TD></TR></TABLE>";
$footer = "<TABLE style='width: 100%;'><TR><TD COLSPAN=99 style='text-align: center;'>" . get_variable('report_footer') . "</TD></TR></TABLE>";
if($mode == "doc") {
	header("Content-Type: application/msword");
	header("Content-Disposition: attachment; filename=" . $reportname . ".doc");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $header;
	echo $thePage;
	} else {
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=" . $reportname . ".xls");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $thePage;
	}
?>