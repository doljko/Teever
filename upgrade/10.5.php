<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( !$_SESSION['step_update'] ) {

	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_logs` ADD `rating` TINYINT(4) NOT NULL DEFAULT '0'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comment_rating_log` ADD `rating` TINYINT(4) NOT NULL DEFAULT '0'";
	$tableSchema[] = "ALTER TABLE `" . PREFIX . "_admin_sections` CHANGE `name` `name` VARCHAR(100) NOT NULL DEFAULT ''";
	

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

	$sql_info = "<br /><br /><div style=\"background:#F2DDDD;border:1px solid #992A2A;padding:5px;color: #992A2A;text-align: justify;\"><b>Important information:</b><br /><br />System must perform a heavy request for a table on the next step of DLE update. On some large websites the performance of this query may take a long time and possibly might not be performed by PHP script. If the script will hang and the request will not be executed, you will have to perform the request manually by means of SSH. Copy the query that you will need to perform if it will not be executed automatically:<br/><br/><b>ALTER TABLE `" . PREFIX . "_post` CHANGE `date` `date` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00';</b><br /><br /></div>";

	$_SESSION['step_update'] = 1;

	if ( $error_info ) {

		msgbox("info","Information", "{$error_info}{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<br /><div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>".$db->query_num."</b> queries have been successfully performed..</div>{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();
}

if( $_SESSION['step_update'] == 1 ) {

	$db->query ("ALTER TABLE `" . PREFIX . "_post` CHANGE `date` `date` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00'");

	if ($db->error_count) {
	
		$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed queries:</b><br /><br />"; 
	
		foreach ($db->query_list as $value) {
	
			$error_info .= $value['query']."<br /><br />";
	
		}
	
		$error_info .= "</div>";
	
	} else $error_info = "";

	$_SESSION['step_update'] = 2;

	$sql_info = "<br /><br /><div style=\"background:#F2DDDD;border:1px solid #992A2A;padding:5px;color: #992A2A;text-align: justify;\"><b>Important information:</b><br /><br />System must perform a heavy request for a table on the next step of DLE update. On some large websites the performance of this query may take a long time and possibly might not be performed by PHP script. If the script will hang and the request will not be executed, you will have to perform the request manually by means of SSH. Copy the query that you will need to perform if it will not be executed automatically:<br/><br/><b>ALTER TABLE `" . PREFIX . "_comments` CHANGE `date` `date` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00';</b><br /><br /></div>";

	if ( $error_info ) {

		msgbox("info","Information", "{$error_info}{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>1 MySQL</b> query has been successfully performed.</div>{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();

}

if( $_SESSION['step_update'] == 2 ) {

	$db->query ("ALTER TABLE `" . PREFIX . "_comments` CHANGE `date` `date` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00'");
	
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

		msgbox("info","Information", "{$error_info}{$sql_info}<br /><br />Click Next to continue the update process.");

	} else {

	    msgbox("info","Information", "<div style=\"border: 1px solid #475936; background: #6F8F52; color: #FFFFFF;padding:8px;text-align: justify;\"><b>1 MySQL</b> query has been successfully performed.</div>{$sql_info}<br /><br />Click Next to continue the update process.");

	}

	die();
}

if( $_SESSION['step_update'] == 3 ) {

	$config['version_id'] = "10.6";
	$config['search_pages'] = "5";
	
	unset($config['yandex_spam_check']);
	unset($config['yandex_api_key']);
	
	$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
	fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
	foreach($config as $name => $value)
	{
		fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
	}
	fwrite($handler, ");\n\n?>");
	fclose($handler);

	require_once(ENGINE_DIR.'/data/videoconfig.php');
	
	$video_config['preload'] = "1";
	
	unset($video_config['use_html5']);
	unset($video_config['youtube_q']);
	unset($video_config['startframe']);
	unset($video_config['preview']);
	unset($video_config['autohide']);
	unset($video_config['fullsizeview']);
	unset($video_config['buffer']);
	unset($video_config['progressBarColor']);
	unset($video_config['play']);

	$con_file = fopen(ENGINE_DIR.'/data/videoconfig.php', "w+") or die("Sorry! Unable to write information to <b>.engine/data/videoconfig.php</b>.<br />Check CHMOD!");
	fwrite( $con_file, "<?PHP \n\n//Videoplayers Configurations\n\n\$video_config = array (\n\n" );
	foreach ( $video_config as $name => $value ) {
			
		fwrite( $con_file, "'{$name}' => \"{$value}\",\n\n" );
		
	}
	fwrite( $con_file, ");\n\n?>" );
	fclose($con_file);
	
	$fdir = opendir( ENGINE_DIR . '/cache/system/' );
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
			@unlink( ENGINE_DIR . '/cache/system/' . $file );
			
		}
	}
	
	@unlink(ENGINE_DIR.'/data/snap.db');
	
	clear_cache();

	$_SESSION['step_update'] = false;

	msgbox("info","Information", "Database update from version <b>10.5</b> to version <b>10.6</b> has been completed successfully.<br /><br />Click Next to continue the update process.");

}

?>