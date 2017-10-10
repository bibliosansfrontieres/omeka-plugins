<?php
/**
 * BSF Companion Functions
 *
 * @copyright Copyright 2017 id[+] Technology - All Rights Reserved
 */


/**
  * Run a background command (Execute Command Without Waiting For It To Finish)
  * Usage : package_manager_bg_command("php -q server.php");
  *
  * From https://subinsb.com/how-to-execute-command-without-waiting-for-it-to-finish-in-php
  * And  http://stackoverflow.com/questions/11250799/php-execute-command-and-log-output-without-waiting
  */
function bsf_companion_bg_command($cmd, $output=false, $error=false) {
	$cmd = str_replace(array("\r\n", "\n\r", "\n", "\r"), ' ', $cmd); // fix line break
	if(substr(php_uname(), 0, 7) == "Windows"){
		$cmd = str_replace(array("^", "&", "|", "\\", "<", ">"), array("^^", "^&", "^|", "^\\", "^<", "^>"), $cmd); // fix Windows Special chars
		$out = (!$output) ? "NUL" : $output;
		$err = (!$error) ? "NUL" : $error;
		if($out == $err || $error===false) $err = "&1";
		$launcher = 'start /B cmd /C "' . $cmd . ' > ' .$out. ' 2>' .$err. '"';
		pclose(popen($launcher, "r")); 
	}
	else {
		$out = (!$output) ? "/dev/null" : $output;
		$err = (!$error) ? "/dev/null" : $error;
		if($out == $err || $error===false) $err = "&1";
		$launcher = $cmd . ' > '.$out.' 2>'.$err.' &';
		exec($launcher);
	}
	return $launcher;
}