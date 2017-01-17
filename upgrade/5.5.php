<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "5.7";
$config['mail_news'] = "0";
$config['mail_comments'] = "0";
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

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}"); 

define ("USERPREFIX", "{$config_dbprefix}"); 

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/dbconfig.php</b>.<br />Check CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_vote` CHANGE `category` `category` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_pm` ADD `reply` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `metadescr` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `metakeys` TEXT NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `icon` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (4, 'new_news', 'Dear Administrator,\r\n\r\nThe article was added on  {$config['http_home_url']} , which is currently awaiting moderation.\r\n\r\n------------------------------------------------\r\nSummary of the article\r\n------------------------------------------------\r\n\r\nAuthor: {%username%}\r\nArticle title: {%title%}\r\nCategory: {%category%}\r\nDate: {%date%}\r\n\r\nSincerely,\r\n\r\nAdministration {$config['http_home_url']}')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (5, 'comments', 'Dear Administrator,\r\n\r\nThe comment was added on  {$config['http_home_url']}.\r\n\r\n------------------------------------------------\r\nSummary of the comment\r\n------------------------------------------------\r\n\r\nAuthor: {%username%}\r\nDate: {%date%}\r\nLink to the article: {%link%}\r\nIP address: {%ip%}\r\n\r\n------------------------------------------------\r\nComment text\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nSincerely,\r\n\r\nAdministration {$config['http_home_url']}')";

  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');

clear_cache();

msgbox("info","Information", "Database update from version <b>5.5</b> to version <b>5.7</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>