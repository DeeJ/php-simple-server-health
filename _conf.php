<?php
/**
 * PHP Simple Server Health Config
 */

/**
 * DEBUG - Disable on any production system!!
 */
error_reporting(E_ALL);
ini_set("display_errors", true);

/**
 * DATABASE
 */
// If running a wordpress site include the wp-config to get DB details
$wpConfig = "../wp-config.php";
if (file_exists($wpConfig)) {
	require_once $wpConfig;
}


// If not WP, set DB logins
if (!defined("DB_HOST")) {
	define("DB_HOST", "");
}
if (!defined("DB_USER")) {
	define("DB_USER", "");
}
if (!defined("DB_PASSWORD")) {
	define("DB_PASSWORD", "");
}
if (!defined("DB_NAME")) {
	define("DB_NAME", "");
}


/**
 * PROC Memory info
 */
define("MEM_INFO", "/proc/meminfo");

/**
 * PROC CPU info
 */
define("CPU_INFO", "/proc/cpuinfo");


/**
 * Display colors
 */
define("GREEN", "01bc31");
define("ORANGE", "ff7e00");
define("RED", "F00");