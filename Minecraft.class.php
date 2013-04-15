<?php

class Minecraft {
	private $db;
	
	public function __construct(&$database) {
		$this->db = $database;
	}
	
	public function authenticateUserPassword($username, $password) {
		$query = $this->db->prepare("SELECT `password` FROM `users` WHERE `username`=:username LIMIT 1");
		$query->bindValue(":username", $username, PDO::PARAM_STR);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result === FALSE) {
			return FALSE; // Username does not exist
		}
		if(sha1($password) == $result['password']) {
			return TRUE;
		} else {
			return FALSE; // Passwords don't match
		}
	}
	
	public function authenticateUserSession($username, $sessionId) {
		$query = $this->db->prepare("SELECT `id` FROM `sessions` WHERE `username`=:username ORDER BY `time` DESC LIMIT 1");
		$query->bindValue(":username", $username, PDO::PARAM_STR);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result === FALSE) {
			return FALSE; // User hasn't logged in
		}
		if($sessionId == $result['id']) {
			return TRUE;
		} else {
			return FALSE; // Someone's trying to session hijack; or my script screwed up
		}
	}
	
	public function generateUserId($username) {
		return md5($username);
	}
	
	public function generateSessionId($username) {
		$sessionId = sha1($username . time());
		$stmt = $this->db->prepare("INSERT INTO `sessions` (`id`, `username`) VALUES (:id, :username)");
		$stmt->bindValue(":id", $sessionId, PDO::PARAM_STR);
		$stmt->bindValue(":username", $username, PDO::PARAM_STR);
		$stmt->execute();
		return $sessionId;
	}
	
	public function getGameVersion() {
		return time() * 1000;
	}
	
	public function generateServerJoinRequest($username, $sessionId, $serverId) {
		if(!$this->authenticateUserSession($username, $sessionId)) {
			return FALSE;
		}
		$stmt = $this->db->prepare("INSERT INTO `server_joins` (`username`, `server_id`) VALUES (:username, :server_id)");
		$stmt->bindValue(":username", $username, PDO::PARAM_STR);
		$stmt->bindValue(":server_id", $serverId, PDO::PARAM_STR);
		$stmt->execute();
		return TRUE;
	}
	
	public function verifyServerJoinRequest($username, $serverId) {
		$query = $this->db->prepare("SELECT `server_id` FROM `server_joins` WHERE `username`=:username LIMIT 1");
		$query->bindValue(":username", $username, PDO::PARAM_STR);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result === FALSE) {
			return FALSE; // User has not initiated server join
		}
		if($serverId == $result['server_id']) {
			$query = $this->db->prepare("DELETE FROM `server_joins` WHERE `username`=:username");
			$query->bindValue(":username", $username, PDO::PARAM_STR);
			$query->execute();
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>