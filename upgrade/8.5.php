<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "9.0";
$config['fast_search'] = "0";
$config['login_log'] = "0";
unset($config['ajax']);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `flood_news` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `max_day_news` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `force_leech` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `edit_limit` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `captcha_pm` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `max_pm_day` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `max_mail_day` SMALLINT( 6 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_flood` CHANGE `ip` `ip` VARCHAR( 40 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_flood` ADD `flag` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD INDEX ( `flag` )";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_sendlog";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_sendlog (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) NOT NULL DEFAULT '',
  `date` varchar(20) NOT NULL DEFAULT '',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `date` (`date`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_login_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_login_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `count` smallint(6) NOT NULL DEFAULT '0',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

foreach($tableSchema as $table) {
	$db->query ($table);
}


$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

require_once(ENGINE_DIR.'/data/videoconfig.php');

$video_config['tube_related'] = "1";
$video_config['tube_dle'] = "0";

$con_file = fopen(ENGINE_DIR.'/data/videoconfig.php', "w+") or die("Sorry! Unable to create <b>.engine/data/videoconfig.php</b>.<br />Check CHMOD!");
fwrite( $con_file, "<?PHP \n\n//Videoplayers Configurations\n\n\$video_config = array (\n\n" );
foreach ( $video_config as $name => $value ) {
		
	fwrite( $con_file, "'{$name}' => \"{$value}\",\n\n" );
	
}
fwrite( $con_file, ");\n\n?>" );
fclose($con_file);

$fdir = opendir( ENGINE_DIR . '/cache/system/' );
while ( $file = readdir( $fdir ) ) {
	if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
		@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
	}
}

@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) $error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before."; else $error_info = "";

msgbox("info","Information", "Database update from version <b>8.5</b> to version <b>9.0</b> has been completed successfully.<br />{$error_info}<br />Click Next to continue the engine update process.");
?>