<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "6.2";
$config['short_rating'] = "1";
$config['full_search'] = "1";
$config['news_captcha'] = "0";
$config['key'] = "";

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banners` ADD `grouplevel` varchar(100) NOT NULL default 'all'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_rating` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` DROP INDEX `title`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` DROP INDEX `xfields`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` DROP INDEX `short_story`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD FULLTEXT (`short_story` ,`full_story` ,`xfields` ,`title` )";
$tableSchema[] = "OPTIMIZE TABLE `" . PREFIX . "_post`";


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

msgbox("info","Information", "Database update from version <b>6.0</b> to version <b>6.2</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>