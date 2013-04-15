<?php

// Launcher authentication
// REQUEST[POST]: https://login.minecraft.net/?user=<username>&password=<password>&version=<launcher version>
// RESPONSE: 1343825972000:deprecated:SirCmpwn:7ae9007b9909de05ea58e94199a33b30c310c69c:dba0c48e1c584963b9e93a038a66bb98
// $client_login_response = "{GAME_VERSION}:deprecated:{USER_NAME}:{SESSION_ID}:{USER_ID}";

// Client keep-alive
// REQUEST[GET]: https://login.minecraft.net/session?name=<username>&session=<session id>
// RESPONSE: any; discarded by client

// Join server
// REQUEST[GET]: http://session.minecraft.net/game/joinserver.jsp?user=<username>&sessionId=<session id>&serverId=<server hash>
// RESPONSE: OK

// Server username authentication
// REQUEST[GET]: http://session.minecraft.net/game/checkserver.jsp?user=<username>&serverId=<server hash>
// RESPONSE: YES

define("BASE_URL", "http://localhost");

include("Minecraft.class.php");

$database = new PDO("mysql:host=localhost;dbname=minecraft;charset=utf8", "root", "");
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$minecraft = new Minecraft($database);

function varex($variable_name) {
	if(isset($_POST[$variable_name])) {
		return $_POST[$variable_name];
	} elseif(isset($_GET[$variable_name])) {
		return $_GET[$variable_name];
	} else {
		return null;	
	}
}

if(isset($_GET['action'])) {
	$action = $_GET['action'];
	switch($action) {
		case "login":
			$username = varex('user');
			$password = varex('password');
			if($minecraft->authenticateUserPassword($username, $password)) {
				echo $minecraft->getGameVersion() . ":deprecated:{$username}:" . $minecraft->generateSessionId($username) . 
					":" . $minecraft->generateUserId($username);
			} else {
				echo "Bad login";	
			}
		break;
		case "keepalive":
			echo "KK BRO";
		break;
		case "joinserver":
			$username = varex('user');
			$session_id = varex('sessionId');
			$server_id = varex('serverId');
			if($minecraft->generateServerJoinRequest($username, $session_id, $server_id)) {
				echo "OK";
			} else {
				echo "Not cool bro";	
			}
		break;
		case "checkserver":
			$username = varex('user');
			$server_id = varex('serverId');
			if($minecraft->verifyServerJoinRequest($username, $server_id)) {
				echo "YES";
			} else {
				echo "Not cool bro";	
			}
		break;
		case "getskin":
			$username = strtolower(varex('user'));
			$skin = BASE_URL . "/skins/{$username}.png";
			if(file_exists($skin)) {
				header("Location: $skin");
			} else {
				header("Location: " . BASE_URL . "/skins/default_player_skin.png");
			}
		break;
		case "getcape":
			$username = strtolower(varex('user'));
			$cape = BASE_URL . "/capes/{$username}.png";
			if(file_exists($cape)) {
				header("Location: $cape");
			} else {
				header("Location: " . BASE_URL . "/capes/default_player_cape.png");
			}
		break;
	}	
}

?>