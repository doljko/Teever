<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "5.2";
$config['show_sub_cats'] = "0";
$config['key'] = "";

unset ($config['comments_code']);

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `captcha` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rss";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rss (
  `id` smallint(5) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',
  `allow_main` tinyint(1) NOT NULL default '0',
  `allow_rating` tinyint(1) NOT NULL default '0',
  `allow_comm` tinyint(1) NOT NULL default '0',
  `text_type` tinyint(1) NOT NULL default '0',
  `date` tinyint(1) NOT NULL default '0',
  `search` text NOT NULL default '',
  `max_news` tinyint(3) NOT NULL default '0',
  `cookie` text NOT NULL default '',
  `category` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";


  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/usergroup.php');

msgbox("info","Information", "Database update from version <b>5.1</b> to version <b>5.2</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>