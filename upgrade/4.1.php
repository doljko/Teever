<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config = <<<HTML
<?PHP

//System Configurations

\$config = array (

'version_id' => "4.2",
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
'reg_group' => "4",
'files_antileech' => "1",

);

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($con_file, $config);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` CHANGE `user_group` `user_group` SMALLINT( 5 ) NOT NULL DEFAULT '4'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `time_limit` VARCHAR( 20 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_flood` CHANGE `f_id` `f_id` INT NOT NULL AUTO_INCREMENT";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_usergroups";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_usergroups (
  `id` smallint(5) NOT NULL auto_increment,
  `group_name` varchar(32) NOT NULL default '',
  `allow_cats` varchar(200) NOT NULL default '',
  `allow_adds` tinyint(1) NOT NULL default '1',
  `cat_add` varchar(200) NOT NULL default '',
  `allow_admin` tinyint(1) NOT NULL default '0',
  `allow_addc` tinyint(1) NOT NULL default '0',
  `allow_editc` tinyint(1) NOT NULL default '0',
  `allow_delc` tinyint(1) NOT NULL default '0',
  `edit_allc` tinyint(1) NOT NULL default '0',
  `del_allc` tinyint(1) NOT NULL default '0',
  `moderation` tinyint(1) NOT NULL default '0',
  `allow_all_edit` tinyint(1) NOT NULL default '0',
  `allow_edit` tinyint(1) NOT NULL default '0',
  `allow_pm` tinyint(1) NOT NULL default '0',
  `max_pm` smallint(5) NOT NULL default '0',
  `max_foto` smallint(5) NOT NULL default '0',
  `allow_files` tinyint(1) NOT NULL default '1',
  `allow_hide` tinyint(1) NOT NULL default '1',
  `allow_short` tinyint(1) NOT NULL default '0',
  `time_limit` tinyint(1) NOT NULL default '0',
  `rid` smallint(5) NOT NULL default '0',
  `allow_fixed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";

$tableSchema[] = "insert into " . PREFIX . "_usergroups VALUES (1, 'Administrators', 'all', 1, 'all', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 1, 1)";
$tableSchema[] = "insert into " . PREFIX . "_usergroups VALUES (2, 'Chief editors', 'all', 1, 'all', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 2, 1)";
$tableSchema[] = "insert into " . PREFIX . "_usergroups VALUES (3, 'Journalists', 'all', 1, 'all', 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 50, 101, 1, 1, 1, 0, 3, 0)";
$tableSchema[] = "insert into " . PREFIX . "_usergroups VALUES (4, 'Visitors', 'all', 1, 'all', 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 20, 101, 1, 1, 1, 0, 4, 0)";
$tableSchema[] = "insert into " . PREFIX . "_usergroups VALUES (5, 'Guests', 'all', 0, 'all', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 5, 0)";

           foreach($tableSchema as $table) {
           $db->query ($table);
           }

msgbox("info","Information", "Database update from version <b>4.1</b> to version <b>4.2</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>