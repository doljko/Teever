<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "6.3";
$config['allow_multi_category'] = "1";
$config['key'] = "";

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


$config_dbhost = DBHOST;
$config_dbname = DBNAME;
$config_dbuser = DBUSER;
$config_dbpasswd = DBPASS;
$config_dbprefix = PREFIX;
$config_userprefix = USERPREFIX;

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}"); 

define ("USERPREFIX", "{$config_dbprefix}"); 

define ("COLLATE", "cp1251");

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/dbconfig.php</b>.<br />Check CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_notice";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_notice (
  `id` mediumint(8) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `notice` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci */";


$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `symbol` VARCHAR( 3 ) /*!40101 CHARACTER SET cp1251 COLLATE cp1251_general_ci */ NOT NULL default ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD INDEX ( `symbol` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_rss` ADD `lastdate` VARCHAR( 20 ) NOT NULL default ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_offline` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "UPDATE `" . PREFIX . "_usergroups` SET allow_offline='1' WHERE id='1'";


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

msgbox("info","Information", "Database update from version <b>6.2</b> to version <b>6.3</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>