<?php

namespace ManiaControlCommunicationSDK;


use ManiaControlCommunicationSDK\Enums\CommunicationMethods;
use ManiaControlCommunicationSDK\Enums\MessageTypes;
use ManiaControlCommunicationSDK\Exceptions\CallException;
use ManiaControlCommunicationSDK\Exceptions\ConnectException;

/**
 * Connection Class for ManiaControl Control-It Communication SDK
 *
 * @author    ManiaControl Team <mail@maniacontrol.com>
 * @copyright 2017 ManiaControl Team
 * @license   http://www.gnu.org/licenses/ GNU General Public License, Version 3
 */
class Connection {
	/** @var  resource $socket */
	private $socket;
	private $connectionPassword;

	const SOCKET_TIMEOUT = 2;
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
	 * Restarts ManiaControl
	 *
	 * @param string $message
	 * @return object
	 */
	public function restartManiaControl($message = "") {
		return $this->call(CommunicationMethods::RESTART_MANIA_CONTROL, array("message" => $message));
	}
}