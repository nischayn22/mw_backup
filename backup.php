<?php
error_reporting(E_ERROR | E_PARSE);

$settings = array();
$settings['dbName'] = 'core';
$settings['dbHost'] = 'localhost';
$settings['dbUser'] = 'root';
$settings['dbPassword'] = '';

// better to provide the absolute path here
$settings['uploadDir'] = 'core/images';
$settings['dailybackupsfolder'] = 'daily_backups';
$settings['weeklybackupsfolder'] = 'weekly_backups';
$settings['monthlybackupsfolder'] = 'monthly_backups';

echo "Hello! Lets begin making backups.\n";

if (!file_exists($settings['dailybackupsfolder'])) {
    mkdir( $settings['dailybackupsfolder'], 0777, true);
}

if (!file_exists($settings['weeklybackupsfolder'])) {
    mkdir( $settings['weeklybackupsfolder'], 0777, true);
}

if (!file_exists($settings['monthlybackupsfolder'])) {
    mkdir( $settings['monthlybackupsfolder'], 0777, true);
}

echo "Date: ". date("Y/m/d") . "\n";

echo "Creating mysql dump\n";
exec( "mysqldump -u ". $settings['dbUser'] ." --password=" . $settings['dbPassword'] . " " . $settings['dbName'] . " | gzip > mysqldump.sql.gz.tmp"  );

echo "Creating images backup\n";
exec("tar -cvpzf imagesbackup.tar.gz.tmp " . $settings['uploadDir']);

exec( "cp mysqldump.sql.gz.tmp " . $settings['dailybackupsfolder']. "/dbdump-" . date("Y-m-d") . ".sql.gz" );
exec( "cp imagesbackup.tar.gz.tmp " . $settings['dailybackupsfolder']. "/imagesbackup-" . date("Y-m-d") . ".tar.gz" );


$today = strtotime( 'today', time() );
$week_end = strtotime('next Sunday', time() - 24*60*60); // this will give us sunday of this week
if( $week_end == $today )
{
	echo "Today is sunday, copying to weekly backup folder as well\n";
	exec( "cp mysqldump.sql.gz.tmp " . $settings['weeklybackupsfolder']. "/dbdump-" . date("Y-m-d")  . ".sql.gz" );
        exec( "cp imagesbackup.tar.gz.tmp " . $settings['weeklybackupsfolder']. "/imagesbackup-" . date("Y-m-d") . ".tar.gz" );
}

$month_end = strtotime('last day of this month', time());
if( $today == $month_end )
{
	echo "Today is last day of month, copying to monthly backup folder as well\n";
	exec( "cp mysqldump.sgl.gz.tmp " . $settings['monthlybackupsfolder']. "/dbdump-" . date("Y-m-d")  . ".sql.gz" );
        exec( "cp imagesbackup.tar.gz.tmp " . $settings['monthlybackupsfolder']. "/imagesbackup-" . date("Y-m-d") . ".tar.gz" );
}

echo "Now deleting old and temporary backups \n";

exec("find " .$settings['dailybackupsfolder']. "/dbdump*.gz -maxdepth 1 -type f -mtime +7 -delete");
exec("find " .$settings['weeklybackupsfolder']. "/dbdump*.gz -maxdepth 1 -type f -mtime +32 -delete");
exec("find " .$settings['monthlybackupsfolder']. "/dbdump*.gz -maxdepth 1 -type f -mtime +92 -delete");
exec("find " .$settings['dailybackupsfolder']. "/imagesbackup*.gz -maxdepth 1 -type f -mtime +7 -delete");
exec("find " .$settings['weeklybackupsfolder']. "/imagesbackup*.gz -maxdepth 1 -type f -mtime +32 -delete");
exec("find " .$settings['monthlybackupsfolder']. "/imagesbackup*.gz -maxdepth 1 -type f -mtime +92 -delete");
exec("rm  mysqldump.sql.gz.tmp ");
exec("rm imagesbackup.tar.gz.tmp");