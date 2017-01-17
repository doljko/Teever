<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config = <<<HTML
<?PHP

//System Configurations

\$config = array (

'version_id' => "4.1",
'home_title' => "$config_home_title",
'http_home_url' => "$config_http_home_url",
'charset' => "$config_charset",
'admin_mail' => "$config_admin_mail",
'site_offline' => "$config_site_offline",
'allow_alt_url' => "$config_allow_alt_url",
'langs' => "$config_langs",
'skin' => "$config_skin",
'allow_gzip' => "$config_allow_gzip",
'allow_admin_wysiwyg' => "$config_allow_admin_wysiwyg",
'allow_static_wysiwyg' => "$config_allow_static_wysiwyg",
'news_number' => "$config_news_number",
'smilies' => "$config_smilies",
'date_adjust' => "$config_date_adjust",
'timestamp_active' => "$config_timestamp_active",
'news_sort' => "$config_news_sort",
'news_msort' => "$config_news_msort",
'hide_full_link' => "$config_hide_full_link",
'allow_site_wysiwyg' => "$config_allow_site_wysiwyg",
'allow_comments' => "yes",
'only_registered_comment' => "$config_only_registered_comment",
'allow_edit_comment' => "$config_allow_edit_comment",
'allow_delete_comment' => "$config_allow_delete_comment",
'allow_url_instead_mail' => "$config_allow_url_instead_mail",
'comm_nummers' => "$config_comm_nummers",
'comm_msort' => "$config_comm_msort",
'smiles_nummer' => "$config_smiles_nummer",
'flood_time' => "$config_flood_time",
'timestamp_comment' => "$config_timestamp_comment",
'allow_comments_wysiwyg' => "$config_allow_comments_wysiwyg",
'allow_registration' => "$config_allow_registration",
'allow_cache' => "$config_allow_cache",
'allow_votes' => "$config_allow_votes",
'allow_topnews' => "$config_allow_topnews",
'allow_read_count' => "$config_allow_read_count",
'allow_fixed_news' => "$config_allow_fixed_news",
'allow_calendar' => "$config_allow_calendar",
'allow_archives' => "$config_allow_archives",
'files_allow' => "$config_files_allow",
'files_type' => "$config_files_type",
'files_count' => "$config_files_count",
'files_access' => "$config_files_access",
'registration_type' => "$config_registration_type",
'allow_sec_code' => "$config_allow_sec_code",
'allow_skin_change' => "$config_allow_skin_change",
'max_users' => "$config_max_users",
'max_users_day' => "$config_max_users_day",
'max_foto' => "$config_max_foto",
'allow_upload' => "$config_allow_upload",
'max_up_size' => "$config_max_up_size",
'max_image_days' => "$config_max_image_days",
'allow_watermark' => "$config_allow_watermark",
'max_watermark' => "$config_max_watermark",
'max_image' => "$config_max_image",
'jpeg_quality' => "$config_jpeg_quality",
'description' => "Demo page of DataLife Engine",
'keywords' => "DataLife, Engine, CMS, PHP engine",
'auto_wrap' => "40",
'meta_generator' => "1",

);

?>
HTML;

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}"); 

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/dbconfig.php</b>.<br />Check CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$con_file = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($con_file, $config);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `descr` VARCHAR( 200 ) NOT NULL AFTER `title` , ADD `keywords` TEXT NOT NULL AFTER `descr`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `descr` VARCHAR( 200 ) NOT NULL , ADD `keywords` TEXT NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` DROP INDEX `id`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD PRIMARY KEY ( `id` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT";

           foreach($tableSchema as $table) {
           $db->query ($table);
           }

msgbox("info","Information", "Database update from version <b>4.0</b> to version <b>4.1</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>