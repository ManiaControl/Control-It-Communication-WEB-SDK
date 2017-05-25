<?php
// Include ManiaControl Control-It
use ManiaControlCommunicationSDK\Connection;
use ManiaControlCommunicationSDK\Enums\AuthLevels;
use ManiaControlCommunicationSDK\Enums\CommunicationMethods;
use ManiaControlCommunicationSDK\Enums\MessageTypes;
use ManiaControlCommunicationSDK\Exceptions\CallException;
use ManiaControlCommunicationSDK\Exceptions\ConnectException;

require_once __DIR__ . '/../autoload.php';

//Your IP or Host
$host           = "";
//Port the Communcation Socket is Running on the Maniacontrol (Can be set in Communcation Manager Settings of ManiaControl)
$port           = "";
//Connection Pass which is set in Communcation Manager Settings of ManiaControl
$connectionPass = "";

//Create the Connection (it will closed automatically via its destructor)
try {
	$communicationController = new Connection($host, $port, $connectionPass);
} catch(ConnectException $e) {
	var_dump($e->getMessage());
	exit();
}

//TODO MC-Info Method

//Example 1a: Send a Chat Message
try {
	$communicationController->sendChatMessage("Example Message 1", '$f0f', MessageTypes::TYPE_ERROR);
} catch(CallException $e) {
	var_dump($e->getMessage());
}

//Example 1b: Plain Call (You can use this way for calling own defined Communication Messages)
try {
	$communicationController->call(CommunicationMethods::SEND_CHAT_MESSAGE, array("message" => "Im a Test Message", "type" => MessageTypes::TYPE_SUCCESS));
} catch(CallException $e) {
	var_dump($e->getMessage());
}


//Example 2: Get Server Chat (Notice the $ codes could be parsed nicely with the ManiaLib SDK)
try {
	echo "<pre>";
	print_r($communicationController->getServerChat());
	echo "</pre>";
} catch(CallException $e) {
	var_dump($e->getMessage());
}

//Example 3: Grand Auth Level
try {
	echo "<pre>";
	print_r($communicationController->grandAuthLevel("loginxyz", AuthLevels::AUTH_LEVEL_SUPERADMIN));
	echo "</pre>";
} catch(CallException $e) {
	var_dump($e->getMessage());
}

//Example 4: Revoke Auth Level
try {
	echo "<pre>";
	print_r($communicationController->revokeAuthLevel("loginxyz"));
	echo "</pre>";
} catch(CallException $e) {
	var_dump($e->getMessage());
}

//Example 5: Updating ManiaControl
try {
	//print_r($communicationController->updateManiaControlCore());
} catch(CallException $e) {
	var_dump($e->getMessage());
}


//Example 6: Restarting ManiaControl
try {
	//print_r($communicationController->restartManiaControl());
} catch(CallException $e) {
	var_dump($e->getMessage());
}
