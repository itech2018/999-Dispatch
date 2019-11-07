<?php
require_once './incs/functions.inc.php';
require_once './lib/zello_auth/tokenmanager.class.php';
session_start();
session_write_close();
$channels = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]zello_channels` ORDER BY `id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if(mysql_num_rows($result) == 0) {
	print "No Zello Channels Configured, Configure These From the Main Config Page<BR />";
	}
$i = 0;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$channels[$i][0] = $row['name'];
	$channels[$i][1] = $row['channel'];
	$i++;
	}
unset($result);
$user = $_SESSION['user_id'];
$zello_details = get_zello($user);
if($zello_details && $zello_details['user'] != "" && $zello_details['password'] != "") {
	$username = $zello_details['user'];
	$password = $zello_details['password'];
	} else {
	print "No Configured Zello User Data, Please Contact Your System Admin<BR />";
	exit();
	}

$private_key = "-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDZFgaPxbDyal5z
ZPWjXZKBuHDuQlFKmxQFNyZnCwhFipXAyv6xV4hYz1x4H+1yNiiq2Jn/etWLdKv0
gB2uQuE0IrnQ20f+hBt1Xl5PzPRH4LIRBXG/4e3ZFaK1OqV/gXklOBAQYDAE0d2l
eJhQkjAXInAFQiLV5aTFLPfDTAda/549fJ+rSTlW+dFxpIWCyLaOJRyhZBCIG1LS
JH9ChPpV6HLAJ9CX0kv4Fo3OiU3qD+BLARDL4WSBF5cZMaC7PIUi+hVO2ADByfxv
wSc8W7OXk3+MD0/54gZbmr/YRUPwOqhwyYiG4g9reObKKGh+F7TEypsWkuaYzoMN
HFN2Tj1vAgMBAAECggEAcOkS2p7BbSTEIZLmbGUT+aKcImCd9Yb5f8jykW/ciocN
YuxyUn0rrr2T4+r/ToM63bmxR391KIazlYU5atTgW4SgTzBunsPJoF9IAIuiluwY
0d+aDWqOknW9XjO1tr756tDhEhNlhmw9s34pAuc2WiIQT7vZcJV0ARZle8/El6Ad
S+BpQ766iXmmhX6eymK3uNdvXE0f8qgw3jfT7yj15TJkYpLaqw1XhPbh0I/0D8gx
bASozv+mDAYvNvHa8hLkrEqTolxAfKfm58DHS2JoWtb/PMm3//sZYs5tHi0+ntT6
FJBPHqwYhHsbciJqKi982GE6upUQsF4CAZijHiddCQKBgQDr4C5hzVnMtOiRkRuV
CGdlvQK2NUAvuXIxcS3TyTs5laKrY5U6EjCbv5ffXRZyMIPy4YMffo8ZnSArGr9d
vOJcsAQOyEG4msMzjQNAm8VT3QQ0/Jf8SArtfYng0TTY2rhVV7ombljn89kLohlp
uD8u3Bzx5pj8JWRGHDwumI7biwKBgQDrm3RmYDEudk/Dr/r+msw+umo0GyldwYoQ
dImTWeOGFIrUo9ABKPK92XFcKHs6PC1P+1beS9qBLTgc5UWKECUhY1pcvFiKq8I+
EeK7A63cOxvtM+rMOmHgEYy47m2Prj1KoymBion00lugYywgAD17fkX7WbB8ks1o
SQST/wGyLQKBgG0eQpbAFuDaeBSPWoExaBPqwoxkShNJ6QfyYc7t8tYK4TwET46T
x6Tll26fc7jTtNbxeGVjePPSeoU2VH0a2mUikF3+SlkKT29TtsN2zGylfEK+79in
w1ZmkxhL7/S6CjiA4v7QYZS8fBYjoToFIEWfUkyd7vwGmELO4RB1RvFNAoGBAK6z
R10+CFnOSpjsnW06tSXyLhvS5BpsDwbikrybE3VxN/wyN2MUzOFvIXpXXgAxbNv4
n1IX5r6QHCJ48tZL4Gxgcjl/QxwX/eDufDN1p+48OhnpvDmRNM/j03ew+7ZlWXdF
gtpWMrNBY8WKo8ZaxzwRxqx4tb+5Tuv78JQYq1ZFAoGBAIA3LJUBt5BBanqlzhry
U/RrD+OhLbifsvay9ppUjqaGE1HfWr6JjHZMV9aSTa6Zwna6GiC+WssoACY4zWm5
4Rl6DLUhJH90kNWeFvaKnjl8fawkj+8eGOBniopzxzIyLXIPH0sCnsR3ZCko2btG
KziV8iGK2J2BdmI3WB+Uik1L
-----END PRIVATE KEY-----";
$key = openssl_pkey_get_private($private_key);
$issuer = "WkM6aGFydmV5YWoxMToy.4UUwOWzao7NYK8qScmjUMs60PvcHXPEMKB/WFI5K4VA=";
$token = TokenManager::createJwt($issuer, $key);

?>
<!DOCTYPE HTML>
<html>
<HEAD><TITLE>Tickets - Zello</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<script src="https://zello.io/sdks/js/latest/zcc.sdk.js"></script>
<style>
.status {font-size: 1em; color: silver;}
.status.online {color: black;}
</style>
<script>
window.onresize=function(){set_size();}

var username = "<?php print $username;?>";
var password = "<?php print $password;?>";
var token = "<?php print $token;?>";
var channels = '<?php echo json_encode($channels); ?>';
var	theChannels = JSON.parse(channels);
var currChannel = "";
var theLength = theChannels.length;
var c_interval = null;
var session = null;
var sessions = [];
var marker = 0;
var previous = 0;
var outerwidth;
var outerheight;
var colwidth;
var colheight;

function set_size() {
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
	outerheight = viewportheight;
	outerwidth = viewportwidth;
	colwidth = viewportwidth * .80;
	colheight = viewportheight * .98;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('column')) {$('column').style.width = colwidth + "px";}
	if($('column')) {$('column').style.height = colheight + "px";}
	}

var CustomPlayer = function() {};
	CustomPlayer.prototype.feed = function(pcmData) {
//	console.warn('Have incoming decoded message data in player: ', pcmData.length);
	};

function CngClass(obj, the_class){
	$(obj).className=the_class;
	return true;
	}

function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')		element = document.getElementById(element);
		if (arguments.length == 1)			return element;
		elements.push(element);
		}
	return elements;
	}
	
function do_hover(the_id) {
	if($(the_id).classList.contains("text_large")) {
		CngClass(the_id, 'hover text_large');
		} else if($(the_id).classList.contains("text_biggest")) {
		CngClass(the_id, 'hover text_biggest');
		} else if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'hover text_small');
		} else {
		CngClass(the_id, 'hover text');
		}
	return true;
	}

function do_plain(the_id) {
	if($(the_id).classList.contains("text_large")) {
		CngClass(the_id, 'plain text_large');
		} else if($(the_id).classList.contains("text_biggest")) {
		CngClass(the_id, 'plain text_biggest');		
		} else if($(the_id).classList.contains("text_small")) {
		CngClass(the_id, 'plain text_small');
		} else {
		CngClass(the_id, 'plain text');
		}
	return true;
	}

function updateStatus(status, usersOnline, marker) {
	var el = document.getElementById('status_' + marker);
	el.className = 'status ' + status;
	if (status === 'offline') {
		el.innerHTML = 'offline';
		$('traffic_' + marker).style.backgroundColor = "";
		$('traffic_' + marker).innerHTML = "";		
		} else {
		el.innerHTML = usersOnline + " online";;
		document.getElementById('button_' + marker).removeAttribute('disabled');
		}
	}

function selectIt(channel, name, theChannel) {
	c_interval = null;
	for(var i = 0; i < theLength; i++) {
		if('wrapper_' + i ) {$('wrapper_' + i).style.backgroundColor = "";}
		updateStatus('offline', 0, i);
		if(sessions[i]) {sessions[i].disconnect();}
		}
	$('selected').innerHTML = channel;
	marker = theChannel;
	connect(channel, name);
	}

var outgoingMessage = null;
var sessions = [];

function connect(channel, name) {
	$('wrapper_' + previous).style.backgroundColor = "";
	$('wrapper_' + marker).style.backgroundColor = "green";
	updateStatus('offline', 0, previous);
	ZCC.Sdk.init({
		player: true,
		recorder: true,
		encoder: false,
		widget: false
		}).then(function() {
		session = new ZCC.Session({
			serverUrl: 'wss://zello.io/ws/',
			channel: channel,
			authToken: token,
			username: username,
			password: password
			});
		sessions[marker] = session;	
		sessions[marker].connect(function() {
			var theButton = document.getElementById('button_' + marker);
			theButton.onmousedown = function() {
				outgoingMessage = sessions[marker].startVoiceMessage();
				};

			theButton.onmouseup = function() {
				outgoingMessage.stop();
				};
			}).catch(function(err) {
				alert(err);
				});

		sessions[marker].on('status', function(status) {
			sessions[marker].status = status.status;
			updateStatus(status.status, status.users_online, marker);
			});

 		sessions[marker].on(ZCC.Constants.EVENT_INCOMING_VOICE_DATA, function(incomingMessageData) {
//			console.warn('EVENT_INCOMING_VOICE_DATA', 'from session', incomingMessageData);
			});

		sessions[marker].on(ZCC.Constants.EVENT_INCOMING_VOICE_DATA_DECODED, function(pcmData, incomingMessage) {
//			console.warn('EVENT_INCOMING_VOICE_DATA_DECODED', 'from session', pcmData.length, incomingMessage);
			});

		sessions[marker].on(ZCC.Constants.EVENT_INCOMING_VOICE_WILL_START, function(incomingMessage) {
//			console.warn('EVENT_INCOMING_VOICE_WILL_START', incomingMessage);
			incomingMessage.on(ZCC.Constants.EVENT_INCOMING_VOICE_DATA, function(incomingMessageData) {
			$('traffic_' + marker).style.backgroundColor = "red";
			$('traffic_' + marker).innerHTML = "Traffic";
			console.warn('EVENT_INCOMING_VOICE_DATA', 'from message', incomingMessageData);
				});

			incomingMessage.on(ZCC.Constants.EVENT_INCOMING_VOICE_DATA_DECODED, function(pcmData) {
//				console.warn('EVENT_INCOMING_VOICE_DATA_DECODED', 'from message', pcmData.length, incomingMessage);
				});

			incomingMessage.on(ZCC.Constants.EVENT_INCOMING_VOICE_DID_STOP, function() {
				$('traffic_' + marker).style.backgroundColor = "grey";
				$('traffic_' + marker).innerHTML = "";
//				console.warn('Done with message');
				});
			});

	}).catch(function(err) {
		alert(err);
		})
	}

function loop_get() {
	if (c_interval!=null) {return;}
	c_interval = window.setInterval('theLoop()', 3000);
	}

function theLoop() {
	if (c_interval==null) {return;}
	if(marker > 0 && marker < theLength-1) {
		previous = marker;
		marker++;
		} else if(marker == 0) {
		previous = 0;
		marker++;
		} else if(marker == theLength-1) {
		previous = marker;
		marker = 0;
		}
	var channel = theChannels[marker][1];
	var theName = theChannels[marker][0];
	connect(channel, theName);
	}


function startup() {
	marker = 0;
	var channel = theChannels[marker][1];
	var theName = theChannels[marker][0];
	connect(channel, theName);
//	loop_get();
	}
	
function resumeScan() {
	marker = 0;
	c_interval = null;
	for(var i = 0; i < theLength; i++) {
		if('wrapper_' + i ) {$('wrapper_' + i).style.backgroundColor = "";}
		updateStatus('offline', 0, i);
		if(sessions[i]) {sessions[i].disconnect();}
		}
	startup();
	}
	
</script>
</head>
<!-- <BODY onload="startup();"> -->
<BODY>
<DIV id='outer' style='width: 100%; height: auto; text-align: center; display: block;'>
	<SPAN id='header' class='header text_large' style='width: 100%; text-align: center; display: block; height: 40px;'>Tickets - Zello Channel Console
		<SPAN id='closebut' class='plain' style='width: 100px; display: inline-block; float: right; margin-right: 20px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this,id);' onClick='window.close();'>Close</SPAN>
<!--		<SPAN id='scanner' class='plain' style='width: 100px; display: inline-block; float: right; margin-right: 20px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this,id);' onClick='resumeScan();'>Scan</SPAN> -->
	</SPAN>
	<SPAN id='selected' class='header text_large' style='width: 100%; text-align: center; display: block; height: 40px;'></SPAN>
	<BR />
<?php
	foreach($channels as $key => $val) {
?>
		<DIV id='wrapper_<?php print $key;?>' class='plain' style='width: 150px; height: 150px; display: inline-block; padding: 5px; float: left; margin: 20px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this,id);' onClick="selectIt('<?php print $val[1];?>','<?php print $val[0];?>',<?php print $key;?>);">
			<SPAN id="channel_<?php print $key;?>" style='float: none; width: 150px; height: 150px;'><?php print $val[0];?></SPAN><BR />
			<SPAN id="status_<?php print $key;?>" class="status offline">Offline</SPAN><BR />
			<SPAN id="traffic_<?php print $key;?>" style='width: 100%; height: 20px; display: block; float: none; color: #000000;'></SPAN><BR />
			<BUTTON id="button_<?php print $key;?>" class='plain' style='float: none;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this,id);' disabled><IMG STYLE='float: none;' SRC='./images/ptt.png' BORDER=0></BUTTON>
		</DIV>
<?php
		}
?>
</DIV>
<SCRIPT>
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
outerheight = viewportheight * .99;
outerwidth = viewportwidth * .90;
colwidth = viewportwidth * .80;
colheight = viewportheight * .98;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
</SCRIPT>
</BODY>
</html>