<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| Access Constants
|--------------------------------------------------------------------------
|
*/

define('ACCESS_LOG_VIEW', 0x1);
define('ACCESS_LOG_UPLOAD', 0x2);
define('ACCESS_LOG_EDIT', 0x4);
define('ACCESS_LOG_REMOVE', 0x8);
define('ACCESS_GUILD_ADD', 0x10);
define('ACCESS_GUILD_REMOVE', 0x20);
define('ACCESS_GUILD_PROMOTE', 0x40);
define('ACCESS_GUILD_LEADER', 0x80);

/*
|--------------------------------------------------------------------------
| Permission Constants
|--------------------------------------------------------------------------
|
*/

define('PERMISSION_GUILD_MEMBER', ACCESS_LOG_VIEW);
define('PERMISSION_GUILD_OFFICER', PERMISSION_GUILD_MEMBER | ACCESS_LOG_UPLOAD | ACCESS_LOG_EDIT | ACCESS_LOG_REMOVE | ACCESS_GUILD_ADD | ACCESS_GUILD_REMOVE);
define('PERMISSION_GUILD_LEADER', PERMISSION_GUILD_OFFICER | ACCESS_GUILD_PROMOTE | ACCESS_GUILD_LEADER);

/*
|--------------------------------------------------------------------------
| Directory Constants
|--------------------------------------------------------------------------
|
*/

define('DIR_LOGS', APPPATH."combat_logs/");
define('DIR_CLASS', APPPATH."classes/");

/*
|--------------------------------------------------------------------------
| Other Constants
|--------------------------------------------------------------------------
|
*/

define('IS_LOCAL_TEST', $_SERVER['HTTP_HOST'] == 'localhost');
define('AJAX_REQUEST', isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' : false);
//Handle the valid ajax.
if(!IS_LOCAL_TEST) {
	define('IS_VALID_AJAX', AJAX_REQUEST ? $_SERVER['HTTP_HOST'] == 'www.raidrifts.com' : false);
} else {
	define('IS_VALID_AJAX', true);
}

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */