<?php
require_once './incs/functions.inc.php';
require_once './lib/zello_auth/tokenmanager.class.php';
session_start();
session_write_close();
$channels = array();
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]zello_channels` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$i = 0;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$channels[$i][0] = $row['name'];
	$channels[$i][1] = $row['channel'];
	$i++;
	}
unset($result);
$user = $_SESSION['user_id'];
$zello_details = get_zello($user);
if($zello_details) {
	$username = $zello_details['user'];
	$password = $zello_details['password'];
	} else {
	print "No configured Zello user data, please contact your system admin<BR />";
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
<html>
<head>
    <script src="https://zello.io/sdks/js/latest/zcc.sdk.js"></script>
    <script>

	  var username = "<?php print $username;?>";
	  var password = "<?php print $password;?>";
	  var token = "<?php print $token;?>";
      var incomingDataBuffers = [];
      var outgoingMessage = null;
      var incomingMessage = null;
      var incomingMessageFrameRate = null;

      var CustomRecorder = function() {
        this.cp = 0;
        var self = this;
        setTimeout(function() {
          self.onready();
          setTimeout(function() {
            self.sendBuffer();
          }, 100);
        }, 200);
      };

      CustomRecorder.prototype.sendBuffer = function() {
        var self = this;
        var buffer = incomingDataBuffers.shift();
        if (buffer) {
          self.ondata([buffer]);
          setTimeout(function() {
            self.sendBuffer();
          }, 100);
        } else {
          outgoingMessage.stop();
        }
      };

      function connect() {
        ZCC.Sdk.init({
          player: true,
          recorder: false,
          widget: false
        }).then(function() {
          var session = new ZCC.Session({
            serverUrl: 'wss://zello.io/ws/',
            channel: 'Old-Time Radio',
            authToken: token,
            username: username,
            password: password
          });
          session.connect();

          session.on(ZCC.Constants.EVENT_INCOMING_VOICE_WILL_START, function(message) {

            incomingMessage = message;
            incomingMessageFrameRate = incomingMessage.codecDetails.rate;
            incomingDataBuffers = [];

            incomingMessage.on(ZCC.Constants.EVENT_INCOMING_VOICE_DATA_DECODED, function(pcmData) {
              incomingDataBuffers.push(pcmData);
            });

            incomingMessage.on(ZCC.Constants.EVENT_INCOMING_VOICE_DID_STOP, function() {
              setTimeout(function() {
                outgoingMessage = session.startVoiceMessage({
                  recorder: CustomRecorder,
                  originalSampleRate: incomingMessageFrameRate
                });
                outgoingMessage.on(ZCC.Constants.EVENT_DATA, function(data) {
                  console.warn('data to be encoded:', data);
                });
              }, 1000);
            });

          });

        })
      }
    </script>
</head>
<body onload="connect()">
Echo incoming messages back to channel
</body>
</html>