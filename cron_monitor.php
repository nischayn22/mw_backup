<?php
$settings = array();

$settings['failure_email_address'] = 'nischayn22@gmail.com';
$settings['mail_success'] = true;

$line_to_check = $argv[1];
$file_to_check = $argv[2];


$headers =  'MIME-Version: 1.0' . "\r\n"; 
$headers .= 'From: Your name <cron_monitor@yourdomain.com>' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 

$file = escapeshellarg($file_to_check); // for the security concious (should be everyone!)
$line = `tail -n 1 $file`;

if (strpos($line, $line_to_check) !== false) {
	echo "Cron was successfully run \n";
	if ($settings['mail_success'] == true) {
		mail($settings['failure_email_address'], 'Cron was successfully run', "The cron was successfully run. Line: $line_to_check was found in the end of log file $file_to_check", $headers);
	}

	$file_to_check_without_ext = explode('.', $file_to_check)[0];
	$file_to_check_ext = explode('.', $file_to_check)[0];

	if (!file_exists($file_to_check_without_ext)) {
		mkdir( $file_to_check_without_ext, 0777, true);
	}

	exec( "cp $file_to_check " . $file_to_check_without_ext. "/$file_to_check_without_ext-" . date("Y-m-d-h-i") . "." . $file_to_check_ext );

} else {
	echo "Cron failed to run \n";
	mail($settings['failure_email_address'], 'Cron failed while executing', "Cron Monitor could not find line: $line_to_check in the log file $file_to_check. This might indicate that the cron failed to execute. Please check the log file for more details.", $headers);
}

