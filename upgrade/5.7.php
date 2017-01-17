<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "6.0";
$config['admin_path'] = "admin.php";
$config['rss_informer'] = "0";
$config['allow_cmod'] = "0";
$config['max_up_side'] = "0";
$config['files_force'] = "1";
$config['files_max_speed'] = "0";
$config['key'] = "";

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to  <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rssinform";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rssinform (
  `id` smallint(5) NOT NULL auto_increment,
  `tag` varchar(40) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `category` varchar(200) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `template` varchar(40) NOT NULL default '',
  `news_max` smallint(5) NOT NULL default '0',
  `tmax` smallint(5) NOT NULL default '0',
  `dmax` smallint(5) NOT NULL default '0',
  `approve` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";

$tableSchema[] = "INSERT INTO " . PREFIX . "_rssinform VALUES (1, 'dle', 'Новости с Яндекса', '0', 'http://news.yandex.ru/index.rss', 'informer', 3, 0, 200, 1)";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_modc` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` ADD `approve` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` CHANGE `post_id` `post_id` INT( 11 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` CHANGE `autor` `autor` VARCHAR( 40 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` CHANGE `email` `email` VARCHAR( 40 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `views` MEDIUMINT( 8 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD FULLTEXT (`title`)";


  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');

clear_cache();

msgbox("info","Information", "Database update from version <b>5.7</b> to version <b>6.0</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>