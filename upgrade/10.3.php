<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( !$_SESSION['step_update'] ) {

	$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_comment_rating_log";
	$tableSchema[] = "CREATE TABLE " . PREFIX . "_comment_rating_log (
  `id` int unsigned NOT NULL auto_increment,
  `c_id` int NOT NULL default '0',
  `member` varchar(40) NOT NULL default '',
  `ip` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `c_id` (`c_id`),
  KEY `member` (`member`),
  KEY `ip` (`ip`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

	$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (7, 'wait_mail', 'Dear {%username%},\r\n\r\nYou have requested the association of you account on {$config['http_home_url']} with the social network account on {%network%}.  However, for security reasons you need to confirm this action on the following link: \r\n\r\n------------------------------------------------\r\n{%link%}\r\n------------------------------------------------\r\n\r\nNote! In the case of accounts association, your primary password on the website will be reset, and if you log in using your username and password, your password will no longer be valid.\r\n\r\nIf you did not make this request, then just delete this email. Your account details are stored in a secure place and are inaccessible to unauthorized persons.\r\n\r\nIP address of sender: {%ip%}\r\n\r\nSincerely,\r\n\r\nAdministration {$config['http_home_url']}')";
	$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (8, 'newsletter', '<html>\r\n<head>\r\n<title>{%title%}</title>\r\n<meta content=\"text/html; charset={%charset%}\" http-equiv=Content-Type>\r\n<style type=\"text/css\">\r\nhtml,body{\r\n    font-family: Verdana;\r\n    word-spacing: 0.1em;\r\n    letter-spacing: 0;\r\n    line-height: 1.5em;\r\n    font-size: 11px;\r\n}\r\n\r\np {\r\n	margin:0px;\r\n	padding: 0px;\r\n}\r\n\r\na:active,\r\na:visited,\r\na:link {\r\n	color: #4b719e;\r\n	text-decoration:none;\r\n}\r\n\r\na:hover {\r\n	color: #4b719e;\r\n	text-decoration: underline;\r\n}\r\n</style>\r\n</head>\r\n<body>\r\n{%content%}\r\n</body>\r\n</html>')";


	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_social_login` ADD `wait` TINYINT(1) NOT NULL DEFAULT '0'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `allow_rss` TINYINT(1) NOT NULL DEFAULT '1'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_comments_rating` TINYINT(1) NOT NULL DEFAULT '1'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_email` ADD `use_html` TINYINT(1) NOT NULL DEFAULT '0'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_admin_logs` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_login_log` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_read_log` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_spam_log` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_vote_result` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT ''";

	foreach($tableSchema as $table) {
		$db->query ($table);
	}

	if ($db->error_count) {
	
		$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed queries:</b><br /><br />"; 
	
		foreach ($db->query_list as $value) {
	
			$error_info .= $value['query']."<br /><br />";
	
		}
	
		$error_info .= "</div>";
	
	} else $error_info = "";

	$sql_info = "<div style=\"background:#F2DDDD;border:1px solid #992A2A;padding:5px;color: #992A2A;text-align: justify;\"><b>Important information:</b><br /><br />System must perform a heavy request for a news table on the next step of DLE update. On some large websites the performance of this query may take a long time and possibly might not be performed by PHP script. If the script will hang and the request will not be executed, you will have to perform the request manually by means of SSH. Copy the query that you will need to perform if it will not be executed automatically:<br/><br/><b>ALTER TABLE `" . PREFIX . "_users` CHANGE `logged_ip` `logged_ip` VARCHAR(40) NOT NULL DEFAULT '';</b><br /><br /></div>";

	$_SESSION['step_update'] = 1;

	if ( $error_info ) {

		msgbox("info","Information", "{$error_info}<br />{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<br /><div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>".$db->query_num."</b> queries have been performed.</div><br /><br />{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();
}

if( $_SESSION['step_update'] == 1 ) {

	$db->query ("ALTER TABLE `" . PREFIX . "_users` CHANGE `logged_ip` `logged_ip` VARCHAR(40) NOT NULL DEFAULT ''");

	if ($db->error_count) {
	
		$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed queries:</b><br /><br />"; 
	
		foreach ($db->query_list as $value) {
	
			$error_info .= $value['query']."<br /><br />";
	
		}
	
		$error_info .= "</div>";
	
	} else $error_info = "";

	$_SESSION['step_update'] = 2;

	$sql_info = "<div style=\"background:#F2DDDD;border:1px solid #992A2A;padding:5px;color: #992A2A;text-align: justify;\"><b>Important information:</b><br /><br />System must perform a heavy request for a news table on the next step of DLE update. On some large websites the performance of this query may take a long time and possibly might not be performed by PHP script. If the script will hang and the request will not be executed, you will have to perform the request manually by means of SSH. Copy the query that you will need to perform if it will not be executed automatically:<br/><br/><b>ALTER TABLE `" . PREFIX . "_comments` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT '', ADD `rating` INT(11) NOT NULL DEFAULT '0', ADD `vote_num` INT(11) NOT NULL DEFAULT '0';</b><br /><br /></div>";

	if ( $error_info ) {

		msgbox("info","Information", "{$error_info}<br />{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<br /><br /><div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>1 MySQL</b> query has been performed.</div><br /><br />{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();

}

if( $_SESSION['step_update'] == 2 ) {

	$db->query ("ALTER TABLE `" . PREFIX . "_comments` CHANGE `ip` `ip` VARCHAR(40) NOT NULL DEFAULT '', ADD `rating` INT(11) NOT NULL DEFAULT '0', ADD `vote_num` INT(11) NOT NULL DEFAULT '0'");

	if ($db->error_count) {
	
		$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed queries:</b><br /><br />"; 
	
		foreach ($db->query_list as $value) {
	
			$error_info .= $value['query']."<br /><br />";
	
		}
	
		$error_info .= "</div>";
	
	} else $error_info = "";

	$_SESSION['step_update'] = 3;

	$sql_info = "";

	if ( $error_info ) {

		msgbox("info","Information", "{$error_info}<br />{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>1 MySQL</b> query has been performed.</div><br /><br />{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();
}

if( $_SESSION['step_update'] == 3 ) {

	$config['version_id'] = "10.4";
	$config['login_ban_timeout'] = "20";
	$config['watermark_seite'] = "4";
	$config['auth_only_social'] = "0";
	$config['rating_type'] = "0";
	$config['allow_comments_rating'] = "1";
	$config['comments_rating_type'] = "1";

	$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
	fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
	foreach($config as $name => $value)
	{
		fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
	}
	fwrite($handler, ");\n\n?>");
	fclose($handler);
	
	$fdir = opendir( ENGINE_DIR . '/cache/system/' );
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
			@unlink( ENGINE_DIR . '/cache/system/' . $file );
			
		}
	}
	
	@unlink(ENGINE_DIR.'/data/snap.db');
	
	clear_cache();

	$_SESSION['step_update'] = false;

	msgbox("info","Information", "Database update from version <b>10.3</b> to version <b>10.4</b> has been completed successfully.<br /><br />Click Next to continue the update process.");

}

?>