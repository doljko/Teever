<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "5.1";
$config['log_hash'] = "0";
$config['comments_code'] = "0";
$config['tag_img_width'] = "0";
$config['mail_metod'] = "php";
$config['smtp_host'] = "localhost";
$config['smtp_port'] = "25";
$config['smtp_user'] = "";
$config['smtp_pass'] = "";
$config['mail_bcc'] = "0";
$config['speedbar'] = "0";
$config['timestamp_active'] = "j F Y";
$config['timestamp_comment'] = "j F Y H:i";
$config['safe_xfield'] = "0";
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

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` CHANGE `password` `password` VARCHAR( 32 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `hash` VARCHAR( 32 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` ADD FULLTEXT `text` (`text`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD FULLTEXT (`template`)";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_images` CHANGE `news_id` `news_id` INT( 10 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_images` ADD INDEX ( `news_id` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` CHANGE `news_id` `news_id` INT( 10 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` ADD INDEX ( `news_id` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` ADD INDEX ( `member` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` ADD INDEX ( `ip` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_lostdb` CHANGE `lostid` `lostid` VARCHAR( 32 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_lostdb` ADD INDEX ( `lostid` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` DROP INDEX `news_read`";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` CHANGE `news_read` `news_read` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` CHANGE `allow_cats` `allow_cats` VARCHAR( 255 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` CHANGE `cat_add` `cat_add` VARCHAR( 255 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` CHANGE `news_id` `news_id` INT( 10 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` ADD INDEX ( `news_id` )";

           foreach($tableSchema as $table) {
           $db->query ($table);
           }

msgbox("info","Information", "Database update from version <b>5.0</b> to version <b>5.1</b> has been completed successfully.<br />Click Next to continue the engine update process.");
?>