<?php
/*
9/10/13 New file - writes ne chat message - for mobile page
*/
@session_start();
require_once('../../incs/functions.inc.php');
extract ($_GET);	

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]chat_messages` (`when`, `message`, `chat_room_id`,  `user_id`,  `from`)
				VALUES (%s,%s,%s,%s,%s)",
					quote_smart($now),
					quote_smart($frm_message),
					quote_smart($frm_room),
					quote_smart($frm_user),
					quote_smart($frm_from));
$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
print mysql_insert_id();
exit();
?>