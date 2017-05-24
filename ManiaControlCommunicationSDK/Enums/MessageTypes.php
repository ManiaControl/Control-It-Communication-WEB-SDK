<?php

namespace ManiaControlCommunicationSDK\Enums;

/**
 * Communication Methods Interface
 *
 * @author    ManiaControl Team <mail@maniacontrol.com>
 * @copyright 2017 ManiaControl Team
 * @license   http://www.gnu.org/licenses/ GNU General Public License, Version 3
 */
interface MessageTypes {
	const TYPE_INFORMATION = "information";
	const TYPE_SUCCESS = "success";
	const TYPE_ERROR = "error";
	const TYPE_USAGE = "usage";
	const TYPE_DEFAULT = "default";
}