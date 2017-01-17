<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "9.3";
$config['search_number'] = "10";
$config['news_navigation'] = "1";
$config['mail_additional'] = "";
$config['smtp_mail'] = "";
$config['seo_control'] = "0";
$config['news_restricted'] = "0";
$config['comments_restricted'] = "0";

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_vote` TINYINT(1) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `admin_complaint` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `admin_complaint` = '1' WHERE id = '1'";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_mail_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_mail_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `hash` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_complaint";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_complaint (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `p_id` int(10) NOT NULL DEFAULT '0',
  `c_id` int(10) NOT NULL DEFAULT '0',
  `n_id` int(10) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `from` varchar(40) NOT NULL DEFAULT '',
  `to` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `c_id` (`c_id`),
  KEY `p_id` (`p_id`),
  KEY `n_id` (`n_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_ignore_list";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_ignore_list (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL default '0',
  `user_from` VARCHAR(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `user_from` (`user_from`)
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

unset($video_config['volumeStatusBarColor']);
unset($video_config['volumeBackgroundColor']);
unset($video_config['loadingBackgroundColor']);
unset($video_config['loadingBarColor']);
unset($video_config['outputBkgColor']);
unset($video_config['outputTxtColor']);
unset($video_config['btnsColor']);
unset($video_config['backgroundBarColor']);

$video_config['startframe'] = "0";
$video_config['progressBarColor'] = "0xFFFFFF";
$video_config['preview'] = "1";
$video_config['buffer'] = "3";
$video_config['autohide'] = "0";
$video_config['flv_watermark_pos'] = "left";
$video_config['flv_watermark_al'] = "1";
$video_config['youtube_q'] = "hd720";
$video_config['play'] = "0";

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

if ($db->error_count) {

	$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed queries:</b><br /><br />"; 

	foreach ($db->query_list as $value) {

		$error_info .= $value['query']."<br /><br />";

	}

	$error_info .= "</div>";

} else $error_info = "";

msgbox("info","Information", "Database update from version <b>9.2</b> to version <b>9.3</b> has been completed successfully.<br /><br />{$error_info}<br />Click Next to continue the engine update process.");
?>