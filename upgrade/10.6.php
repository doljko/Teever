<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "11.0";
$config['profile_news'] = '1';
$config['smilies'] = 'bowtie,smile,laughing,blush,smiley,relaxed,smirk,heart_eyes,kissing_heart,kissing_closed_eyes,flushed,relieved,satisfied,grin,wink,stuck_out_tongue_winking_eye,stuck_out_tongue_closed_eyes,grinning,kissing,stuck_out_tongue,sleeping,worried,frowning,anguished,open_mouth,grimacing,confused,hushed,expressionless,unamused,sweat_smile,sweat,disappointed_relieved,weary,pensive,disappointed,confounded,fearful,cold_sweat,persevere,cry,sob,joy,astonished,scream,tired_face,angry,rage,triumph,sleepy,yum,mask,sunglasses,dizzy_face,imp,smiling_imp,neutral_face,no_mouth,innocent';

$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_xfsearch";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_xfsearch (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `tagname` varchar(50) NOT NULL default '',
  `tagvalue` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `tagname` (`tagname`),
  KEY `tagvalue` (`tagvalue`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_social_login` ADD `waitlogin` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_links` ADD `targetblank` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_reg` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_reg_days` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_reg_group` SMALLINT(6) NOT NULL DEFAULT '4'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_news` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_news_count` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_news_group` SMALLINT(6) NOT NULL DEFAULT '4'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_comments` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_comments_count` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_comments_group` SMALLINT(6) NOT NULL DEFAULT '4'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_rating` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_rating_count` MEDIUMINT(9) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `force_rating_group` SMALLINT(6) NOT NULL DEFAULT '4'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `not_allow_cats` TEXT NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static_files` CHANGE `onserver` `onserver` VARCHAR(250) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` CHANGE `tags` `tags` VARCHAR(250) NOT NULL DEFAULT ''";

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
	
unset($video_config['flv_watermark']);
unset($video_config['flv_watermark_pos']);
unset($video_config['flv_watermark_al']);

$con_file = fopen(ENGINE_DIR.'/data/videoconfig.php', "w+") or die("Sorry! Unable to write information to <b>.engine/data/videoconfig.php</b>.<br />Check CHMOD!");
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

msgbox("info","Information", "Database update from version <b>10.6</b> to version <b>11.0</b> has been completed successfully.<br /><br />{$error_info}<br />Click Next to continue the update process.");

?>