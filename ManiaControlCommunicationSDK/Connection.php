<?php

namespace ManiaControlCommunicationSDK;


use ManiaControlCommunicationSDK\Enums\CommunicationMethods;
use ManiaControlCommunicationSDK\Enums\MessageTypes;
use ManiaControlCommunicationSDK\Exceptions\CallException;
use ManiaControlCommunicationSDK\Exceptions\ConnectException;

/**
 * Control It - Your Control Framework for ManiaControl
 *
 * Connection Class for ManiaControl Control-It Communication SDK
 *
 * @version   0.0.1-beta
 * @author    ManiaControl Team <mail@maniacontrol.com>
 * @copyright 2017 ManiaControl Team
 * @license   http://www.gnu.org/licenses/ GNU General Public License, Version 3
 */
class Connection {
	/** @var  resource $socket */
	private $socket;
	private $connectionPassword;

	const SOCKET_TIMEOUT = 30;
	const ENCRYPTION_IV = "kZ2Kt0CzKUjN2MJX";
	const ENCRYPTION_METHOD = "aes-192-cbc";

	/**
	 * Creates a new ManiaControl Connection
	 *
	 * @param $ip
	 * @param $port
	 * @throws ConnectException
	 */
	public function __construct($ip, $port, $connectionPassword) {
		$this->connectionPassword = $connectionPassword;

		//TODO proper error handling
		$errno        = null;
		$errstr       = null;
		$this->socket = fsockopen($ip, $port, $errno, $errstr, self::SOCKET_TIMEOUT);

		if(!$this->socket) {
			throw new ConnectException("Could not connect: " . $errstr . " (Code " . $errno . ")");
		}
	}


	/**
	 * Destructs the ManiaControl Connection
	 */
	public function __destruct() {
		fclose($this->socket);
	}

	/**
	 * Makes a Call on a Method
	 *
	 * @param               $method
	 * @param  string       $data
	 * @return object
	 * @throws CallException
	 */
	public function call($method, $data = "") {
		$data = json_encode(array("method" => $method, "data" => $data));
		$data = openssl_encrypt($data, self::ENCRYPTION_METHOD, $this->connectionPassword, OPENSSL_RAW_DATA, self::ENCRYPTION_IV);

		fwrite($this->socket, strlen($data) . "\n" . $data);

		// read a single msg below
		$len = (int)fgets($this->socket);

		$buff = '';
		while(!feof($this->socket) && strlen($buff) < $len) {
			$buff .= fgets($this->socket, $len - strlen($buff) + 1);
		}

		$decryptedData = openssl_decrypt($buff, self::ENCRYPTION_METHOD, $this->connectionPassword, OPENSSL_RAW_DATA, self::ENCRYPTION_IV);

		if(!$decryptedData) {
			throw new CallException("Could not decrypt the data, check your connection Password!");
		}

		$responseData = json_decode($decryptedData);

		if($responseData->error) {
			throw new CallException($responseData->error);
		}

		return $responseData->data;
	}

	/**
	 * Restarts ManiaControl
	 *
	 * @param string $message
	 * @return object
	 */
	public function restartManiaControl($message = "") {
		return $this->call(CommunicationMethods::RESTART_MANIA_CONTROL, array("message" => $message));
	}

	/**
	 * Perform Core update
	 *
	 * @return object
	 */
	public function updateManiaControlCore() {
		return $this->call(CommunicationMethods::RESTART_MANIA_CONTROL);
	}

	/**
	 * Grands an Authentication Level on a Player
	 *
	 * @param string $login (login of the player)
	 * @param int    $level (integer, 0-3 possible, @see AuthLevels)
	 * @return object
	 */
	public function grandAuthLevel($login, $level) {
		return $this->call(CommunicationMethods::GRANT_AUTH_LEVEL, array("login" => $login, "level" => $level));
	}

	/**
	 * Revokes an Authentication Level on a Player
	 *
	 * @param $login (login of the player)
	 * @return object
	 */
	public function revokeAuthLevel($login) {
		return $this->call(CommunicationMethods::REVOKE_AUTH_LEVEL, array("login" => $login));
	}

	/**
	 * Gets the Server Chat
	 *
	 * @return object
	 * @throws CallException
	 */
	public function getServerChat() {
		return $this->call(CommunicationMethods::GET_SERVER_CHAT);
	}

	/**
	 * Sends a Message To the Chat
	 *
	 * @param                     $message
	 * @param string|bool         $prefix     you can set a Custom Prefix
	 * @param string              $type       type of the message (information, error, success or usage) @see MessageTypes
	 * @param null                $receiverLogin
	 * @param int|null            $adminLevel minimum Admin Level if the Message should get sent to an Admin
	 * @internal param null|string $login login of a receiver if the message don't get sent to all
	 * @return object
	 */
	public function sendChatMessage($message, $prefix = false, $type = MessageTypes::TYPE_DEFAULT, $receiverLogin = null, $adminLevel = null) {
		return $this->call(CommunicationMethods::SEND_CHAT_MESSAGE, array("message" => $message, 'prefix' => $prefix, "login" => $receiverLogin, "adminLevel" => $adminLevel, "type" => $type));
	}

	/**
	 * Gets the Server Options
	 *
	 * @return object
	 */
	public function getServerOptions() {
		return $this->call(CommunicationMethods::GET_SERVER_OPTIONS);
	}

	/**
	 * Sets the Server Options
	 *
	 * @param array $serverOptions
	 * @return object
	 */
	public function setServerOptions($serverOptions) {
		return $this->call(CommunicationMethods::SET_SERVER_OPTIONS, array("serverOptions" => $serverOptions));
	}

	/**
	 * Gets the Script Settings
	 *
	 * @return object
	 */
	public function getScriptSettings() {
		return $this->call(CommunicationMethods::GET_SCRIPT_SETTINGS);
	}

	/**
	 * Sets the Script Settings
	 *
	 * @param array $scriptSettings
	 * @return object
	 */
	public function setScriptSettings($scriptSettings) {
		return $this->call(CommunicationMethods::SET_SCRIPT_SETTINGS, array("scriptSettings" => $scriptSettings));
	}

	/**
	 * Restarts the Current Map
	 *
	 * @return object
	 */
	public function restartMap() {
		return $this->call(CommunicationMethods::RESTART_MAP);
	}

	/**
	 * Skips the Current Map
	 *
	 * @return object
	 */
	public function skipMap() {
		return $this->call(CommunicationMethods::SKIP_MAP);
	}

	/**
	 * Skips to a Map by its MxId
	 *
	 * @param int $mxId
	 * @return object
	 */
	public function skiptToMapByMxId($mxId) {
		return $this->call(CommunicationMethods::SKIP_TO_MAP, array("mxId" => $mxId));
	}

	/**
	 * Skips to a Map by its UID
	 *
	 * @param string $mxId
	 * @return object
	 */
	public function skiptToMapByUid($mapUid) {
		return $this->call(CommunicationMethods::SKIP_TO_MAP, array("mapUid" => $mapUid));
	}

	/**
	 * Adds a Map from Mania Exchange to the Server by its MxId
	 *
	 * @param string $mxId
	 * @return object (no success returned due the asynchronous adding)
	 */
	public function addMap($mxId) {
		return $this->call(CommunicationMethods::ADD_MAP, array("mxId" => $mxId));
	}

	/**
	 * Removes a Map from the Server
	 *
	 * @param string $mapUid
	 * @param bool   $displayMessage
	 * @param bool   $eraseMapFile
	 * @return object
	 */
	public function removeMap($mapUid, $displayMessage = true, $eraseMapFile = false) {
		return $this->call(CommunicationMethods::REMOVE_MAP, array("mapUid" => $mapUid, "displayMessage" => $displayMessage, "eraseMapFile", $eraseMapFile));
	}

	/**
	 * Updates a Map by its MapId
	 *
	 * @param $mapUid
	 * @return object
	 */
	public function updateMap($mapUid) {
		return $this->call(CommunicationMethods::UPDATE_MAP, array("mapUid" => $mapUid));
	}

	/**
	 * Gets information about the Current running Map
	 *
	 * @return object
	 */
	public function getCurrentMap() {
		return $this->call(CommunicationMethods::GET_CURRENT_MAP);
	}

	/**
	 * Gets information about a Map by its MxId
	 *
	 * @param int $mxId
	 * @return object
	 */
	public function getMapByMxId($mxId) {
		return $this->call(CommunicationMethods::GET_MAP, array("mxId" => $mxId));
	}

	/**
	 * Gets information about a Map by its MapId
	 *
	 * @param string $mapUid
	 * @return object
	 */
	public function getMapByUId($mapUid) {
		return $this->call(CommunicationMethods::GET_MAP, array("mapUid" => $mapUid));
	}

	/**
	 * Gets the current Maplist
	 *
	 * @return object
	 */
	public function getMapList() {
		return $this->call(CommunicationMethods::GET_MAP_LIST);
	}
}