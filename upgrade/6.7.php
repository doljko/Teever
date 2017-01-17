<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "7.0";
$config['key'] = "";
$config['catalog_sort'] = "date";
$config['catalog_msort'] = "DESC";
$config['related_number'] = "5";
$config['seo_type'] = "2";
$config['max_moderation'] = "0";


$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_static_files";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_static_files (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `static_id` mediumint(8) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(50) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `static_id` (`static_id`),
  KEY `author` (`author`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";


$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` CHANGE `id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` CHANGE `id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` CHANGE `news_read` `news_read` MEDIUMINT( 8 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` CHANGE `allow_cats` `allow_cats` TEXT NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` CHANGE `cat_add` `cat_add` TEXT NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` CHANGE `allowed_ip` `allowed_ip` VARCHAR( 255 ) NOT NULL DEFAULT ''";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_image_upload` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_file_upload` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_signature` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_url` TINYINT( 1 ) NOT NULL DEFAULT '1'";

if ($config['allow_upload'] == "yes") {

	$allow_upload = 1; 

} else {

	$allow_upload = 0; 

}

if ($allow_upload) {

	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_image_upload='1'";
	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_file_upload='1' WHERE id < '4'";

} else {

	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_image_upload='0'";
	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_file_upload='1' WHERE id = '1'";

}

$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_image_upload='1' WHERE id = '1'";
$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET allow_image_upload='0' WHERE id = '5'";

  foreach($tableSchema as $table) {
     $db->query ($table);
   }


@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

msgbox("info","Information", "Database update from version <b>6.7</b> to version <b>7.0</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>