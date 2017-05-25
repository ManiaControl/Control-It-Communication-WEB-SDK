<?php

namespace ManiaControlCommunicationSDK\Enums;

/**
 * AuthLevel Types Enumeration Interface
 *
 * @author    ManiaControl Team <mail@maniacontrol.com>
 * @copyright 2017 ManiaControl Team
 * @license   http://www.gnu.org/licenses/ GNU General Public License, Version 3
 */
interface AuthLevels {
	const AUTH_LEVEL_PLAYER      = 0;
	const AUTH_LEVEL_MODERATOR   = 1;
	const AUTH_LEVEL_ADMIN       = 2;
	const AUTH_LEVEL_SUPERADMIN  = 3;
}