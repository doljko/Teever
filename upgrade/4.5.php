<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config = <<<HTML
<?PHP

//System Configurations

\$config = array (

'version_id' => "5.0",
'home_title' => "{$config['home_title']}",
'http_home_url' => "{$config['http_home_url']}",
'charset' => "{$config['charset']}",
'admin_mail' => "{$config['admin_mail']}",
'description' => "{$config['description']}",
'keywords' => "{$config['keywords']}",
'date_adjust' => "{$config['date_adjust']}",
'site_offline' => "{$config['site_offline']}",
'allow_alt_url' => "{$config['allow_alt_url']}",
'langs' => "{$config['langs']}",
'skin' => "{$config['skin']}",
'allow_gzip' => "{$config['allow_gzip']}",
'allow_admin_wysiwyg' => "{$config['allow_admin_wysiwyg']}",
'allow_static_wysiwyg' => "{$config['allow_static_wysiwyg']}",
'news_number' => "{$config['news_number']}",
'meta_generator' => "{$config['meta_generator']}",
'smilies' => "{$config['smilies']}",
'timestamp_active' => "{$config['timestamp_active']}",
'news_sort' => "{$config['news_sort']}",
'news_msort' => "{$config['news_msort']}",
'hide_full_link' => "{$config['hide_full_link']}",
'allow_site_wysiwyg' => "{$config['allow_site_wysiwyg']}",
'allow_comments' => "{$config['allow_comments']}",
'allow_url_instead_mail' => "{$config['allow_url_instead_mail']}",
'comm_nummers' => "{$config['comm_nummers']}",
'comm_msort' => "{$config['comm_msort']}",
'smiles_nummer' => "{$config['smiles_nummer']}",
'flood_time' => "{$config['flood_time']}",
'auto_wrap' => "{$config['auto_wrap']}",
'timestamp_comment' => "{$config['timestamp_comment']}",
'allow_comments_wysiwyg' => "{$config['allow_comments_wysiwyg']}",
'allow_registration' => "{$config['allow_registration']}",
'allow_cache' => "{$config['allow_cache']}",
'allow_votes' => "{$config['allow_votes']}",
'allow_topnews' => "{$config['allow_topnews']}",
'allow_read_count' => "{$config['allow_read_count']}",
'allow_calendar' => "{$config['allow_calendar']}",
'allow_archives' => "{$config['allow_archives']}",
'files_allow' => "{$config['files_allow']}",
'files_type' => "{$config['files_type']}",
'files_count' => "{$config['files_count']}",
'registration_type' => "{$config['registration_type']}",
'allow_sec_code' => "{$config['allow_sec_code']}",
'allow_skin_change' => "{$config['allow_skin_change']}",
'max_users' => "{$config['max_users']}",
'max_users_day' => "{$config['max_users_day']}",
'allow_upload' => "{$config['allow_upload']}",
'max_up_size' => "{$config['max_up_size']}",
'max_image_days' => "{$config['max_image_days']}",
'allow_watermark' => "{$config['allow_watermark']}",
'max_watermark' => "{$config['max_watermark']}",
'max_image' => "{$config['max_image']}",
'jpeg_quality' => "{$config['jpeg_quality']}",
'reg_group' => "{$config['reg_group']}",
'files_antileech' => "{$config['files_antileech']}",
'allow_banner' => "1",
'ajax' => "0",

);

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($con_file, $config);
fclose($con_file);

		@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
		@unlink(ENGINE_DIR.'/cache/system/vote.php');

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_poll` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_main` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_vote` ADD `approve` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `votes` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `access` VARCHAR( 150 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `expires` DATE NOT NULL";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `frage` varchar(200) NOT NULL default '',
  `body` text NOT NULL default '',
  `votes` mediumint(8) NOT NULL default '0',
  `multiple` tinyint(1) NOT NULL default '0',
  `answer` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`)
  ) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll_log (
  `id` int(10) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `member` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`,`member`)
  ) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_banners (
  `id` smallint(5) NOT NULL auto_increment,
  `banner_tag` varchar(40) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `code` text NOT NULL default '',
  `approve` tinyint(1) NOT NULL default '0',
  `short_place` tinyint(1) NOT NULL default '0',
  `bstick` tinyint(1) NOT NULL default '0',
  `main` tinyint(1) NOT NULL default '0',
  `category` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
  ) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";

           foreach($tableSchema as $table) {
           $db->query ($table);
           }

msgbox("info","Information", "Database update from version <b>4.5</b> to version <b>5.0</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>