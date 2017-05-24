<?php
/**
 * Control It - Your Control Framework for ManiaControl
 *
 * @author    MC-Team
 * @version   0.0.1-beta
 * @link
 * @copyright ManiaControl Control-It Copyright © 2017 MC-Team
 * @license   http://www.gnu.org/licenses/ GNU General Public License, Version 3
 */
if (!defined('CONTROL_IT_PATH')) {
	/**
	 * @const CONTROL_IT_PATH Installation directory of ManiaControl Control-It
	 */
	define('CONTROL_IT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}


/*
 * Autoload function that loads ManiaControl Control-It class files on demand
 */
if (!defined('CONTROL_IT_AUTOLOAD_DEFINED')) {
	define('CONTROL_IT_AUTOLOAD_DEFINED', true);
	spl_autoload_register(function ($className) {
		$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);
		$filePath  = CONTROL_IT_PATH . $classPath . '.php';
		if (file_exists($filePath)) {
			require_once $filePath;
		}
	});
}