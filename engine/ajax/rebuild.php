<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: rebuild.php
-----------------------------------------------------
 Назначение: перестроение новостей
=====================================================
*/


@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR.'/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/rebuild.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';
dle_session();
require_once ENGINE_DIR.'/modules/sitelogin.php';

if(($member_id['user_group'] != 1)) {die ("error");}

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {

	  die ("error");

}

require_once ROOT_DIR.'/language/'.$config['langs'].'/adminpanel.lng';
include_once ENGINE_DIR . '/classes/parse.class.php';

@header("Content-type: text/html; charset=".$config['charset']);

if ($_POST['area'] == "related" ) {
	$db->query( "UPDATE " . PREFIX . "_post_extras SET related_ids=''" );
    echo "{\"status\": \"ok\"}";
	die();
}

$startfrom = intval($_POST['startfrom']);
$buffer = "";
$step = 0;

if ( intval( $config['tag_img_width'] ) ) $count_per_step = 5; else $count_per_step = 50;

if ($_POST['area'] == "static" ) {

	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	$parse->edit_mode = false;

	if ( $config['allow_static_wysiwyg'] ) $parse->allow_code = false;

	$result = $db->query("SELECT id, template, allow_br FROM " . PREFIX . "_static WHERE allow_br !='2' LIMIT ".$startfrom.", ".$count_per_step);

	while($row = $db->get_row($result))
	{

		if( $row['allow_br'] != '1' OR $config['allow_static_wysiwyg'] ) {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], true, $config['allow_static_wysiwyg'] );
		
		} else {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], false );
		
		}

		$template = $parse->process( $row['template'] );

		if( $config['allow_static_wysiwyg'] OR $row['allow_br'] != '1' ) {
			$template = $db->safesql($parse->BB_Parse( $template ));
		} else {
			$template = $db->safesql($parse->BB_Parse( $template, false ));
		}

		$db->query( "UPDATE " . PREFIX . "_static SET template='$template' WHERE id='{$row['id']}'" );

		$step++;
	}

	$rebuildcount = $startfrom + $step;
	$buffer = "{\"status\": \"ok\",\"rebuildcount\": {$rebuildcount}}";
	echo $buffer;

} else {


	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	$parse->edit_mode = false;
	if ( $config['allow_admin_wysiwyg'] ) $parse->allow_code = false;
	
	$parsexf = new ParseFilter( Array (), Array (), 1, 1 );
	$parsexf->edit_mode = false;
	if ( $config['allow_admin_wysiwyg'] ) $parsexf->allow_code = false;
	
	$result = $db->query("SELECT p.id, p.short_story, p.full_story, p.xfields, p.title, p.allow_br, e.news_id FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) LIMIT ".$startfrom.", ".$count_per_step);
	
	while($row = $db->get_row($result))
	{
	
		if( $row['allow_br'] != '1' OR $config['allow_admin_wysiwyg'] ) {
			$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, $config['allow_admin_wysiwyg'] );
			$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, $config['allow_admin_wysiwyg'] );
		} else {
			$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], false );
			$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], false );
		}
	
		$short_story = $parse->process( $row['short_story'] );
		$full_story = $parse->process( $row['full_story'] );
		$_POST['title'] = $row['title'];
	
		if( $config['allow_admin_wysiwyg'] OR $row['allow_br'] != '1' ) {
			
			$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
			$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
		
		} else {
			
			$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
			$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
		
		}

		$xf_search_words = array ();
		
		if ($row['xfields'] != "") {
	
			$xfields = xfieldsload();
			$postedxfields = xfieldsdataload($row['xfields']);
			$filecontents = array ();
			$newpostedxfields = array ();
	
			if( !empty( $postedxfields ) ) {
	
				foreach ($xfields as $name => $value) {
				
					if ($value[3] != "select" AND $value[3] != "image" AND $value[3] != "file" AND $value[8] == 0 AND $postedxfields[$value[0]] != "" ) {
				
						if( $config['allow_admin_wysiwyg'] OR $row['allow_br'] != '1' ) {
							$postedxfields[$value[0]] = $parsexf->decodeBBCodes($postedxfields[$value[0]], true, true);					
							$newpostedxfields[$value[0]] = $parsexf->BB_Parse($parsexf->process($postedxfields[$value[0]]));
								
						} else {
							$postedxfields[$value[0]] = $parsexf->decodeBBCodes($postedxfields[$value[0]], false);
							$newpostedxfields[$value[0]] = $parsexf->BB_Parse($parsexf->process($postedxfields[$value[0]]), false);
								
						}
				
					} elseif ( $postedxfields[$value[0]] != "" ) {
				
						$newpostedxfields[$value[0]] = strip_tags(stripslashes($postedxfields[$value[0]]));
				
					}
					
					if ( $value[6] AND !empty( $newpostedxfields[$value[0]] ) ) {
						$temp_array = explode( ",", $newpostedxfields[$value[0]] );
						
						foreach ($temp_array as $value2) {
							$value2 = trim($value2);
							if($value2) $xf_search_words[] = array( $db->safesql($value[0]), $db->safesql($value2) );
						}
					
					}
				
				}
	
				if (count ($newpostedxfields) ) {
		
					foreach ( $newpostedxfields as $xfielddataname => $xfielddatavalue ) {
						if( $xfielddatavalue === "" ) {
							continue;
						}
		
						$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
						$filecontents[] = $db->safesql("{$xfielddataname}|{$xfielddatavalue}");
					}
					
					$filecontents = implode( "||", $filecontents );
		
				} else	$filecontents = '';
			
			} else	$filecontents = '';
	
		} else	$filecontents = '';
	
		$db->query( "UPDATE " . PREFIX . "_post SET short_story='$short_story', full_story='$full_story', xfields='$filecontents' WHERE id='{$row['id']}'" );

		if ( !$row['news_id'] ) $db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate) VALUES('{$row['id']}', '1')" );

		$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$row['id']}'" );

		if ( count($xf_search_words) ) {
			
			$temp_array = array();
			
			foreach ( $xf_search_words as $value ) {
				
				$temp_array[] = "('" . $row['id'] . "', '" . $value[0] . "', '" . $value[1] . "')";
			}
			
			$xf_search_words = implode( ", ", $temp_array );
			$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
		}
	
		$step++;
	}
	
	clear_cache();
	$rebuildcount = $startfrom + $step;
	$buffer = "{\"status\": \"ok\",\"rebuildcount\": {$rebuildcount}}";
	echo $buffer;
}
?>