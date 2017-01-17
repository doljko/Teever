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
 Файл: comments.php
-----------------------------------------------------
 Назначение: работа с комментариями
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$id = intval( $_REQUEST['id'] );
$action = $_REQUEST['action'];
$subaction = $_REQUEST['subaction'];

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);

	$row = $db->super_query( "SELECT id, post_id, user_id, is_register, approve FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	

	if( $row['is_register'] ) {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
	}
	
	if($row['approve']) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}

if( $id and $action == "comm_edit" and $subaction != "addcomment" ) {

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter();
	$parse->safe_mode = true;
	$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
	$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
	
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$tpl->load_template( 'addcomments.tpl' );

	$have_perm = 0;
	$row['date'] = strtotime( $row['date'] );

	if( $is_logged and (($member_id['name'] == $row['autor'] and $row['is_register'] and $user_group[$member_id['user_group']]['allow_editc']) or $member_id['user_group'] == '1' or $user_group[$member_id['user_group']]['edit_allc']) ) $have_perm = 1;

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}
	
	if( $have_perm ) {
		
		if( $config['allow_comments_wysiwyg'] > 0 ) {
			$text = $parse->decodeBBCodes( $row['text'], TRUE, $config['allow_comments_wysiwyg'] );
			
			include_once ENGINE_DIR . '/editor/comments.php';
			
			$allow_comments_ajax = true;
			$tpl->set( '{editor}', $wysiwyg );
			
		} else {
			
			include_once ENGINE_DIR . '/modules/bbcode.php';
			
			$text = $parse->decodeBBCodes( $row['text'], false );
			
			$tpl->set( '{editor}', $bb_code );
		}
		
		$tpl->set( '{text}', $text );
		$tpl->set( '{title}', $lang['comm_title'] );
		$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
		$tpl->set( '{sec_code}', "" );
		$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
		$tpl->set( '{recaptcha}', "" );
		$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
		$tpl->set( '{question}', "" );

		$tpl->set_block( "'\\[not-logged\\].*?\\[/not-logged\\]'si", "" );
		
		$tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" action=\"\">" . $tpl->copy_template . "<input type=\"hidden\" name=\"subaction\" value=\"addcomment\" /><input type=\"hidden\" name=\"id\" value=\"{$id}\" /></form>";
		
		$tpl->compile( 'content' );
		$tpl->clear();
	
	} else
		msgbox( $lang['comm_err_2'], $lang['comm_err_3'] );

} elseif( $id and $action == "comm_edit" and $subaction == "addcomment" ) {

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	if( $config['allow_comments_wysiwyg'] > 0 ) {

		$parse = new ParseFilter( Array ('div', 'span', 'p', 'br', 'strong', 'em', 'ul', 'li', 'ol', 'b', 'u', 'i', 's' ), Array (), 0, 1 );
		
	} else {
		
		$parse = new ParseFilter();
	}

	$parse->safe_mode = true;
	$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
	$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
	
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_comments WHERE id = '$id'" );

	$have_perm = 0;
	$row['date'] = strtotime( $row['date'] );

	if( $row['autor'] and $is_logged and (($member_id['name'] == $row['autor'] and $row['is_register'] and $user_group[$member_id['user_group']]['allow_editc']) or $member_id['user_group'] == '1' or $user_group[$member_id['user_group']]['edit_allc']) ) $have_perm = 1;

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}
	
	if( $have_perm ) {

		if( $config['allow_comments_wysiwyg'] > 0 ) {
			
			$parse->wysiwyg = true;
			
			if( $user_group[$member_id['user_group']]['allow_url'] ) $parse->tagsArray[] = 'a';
			if( $user_group[$member_id['user_group']]['allow_image'] ) $parse->tagsArray[] = 'img';
			
			$comments = $parse->BB_Parse( $parse->process( $_POST['comments'] ) );	
		
		} else {
			
			if ($config['allow_comments_wysiwyg'] == "-1") $parse->allowbbcodes = false;
			
			$comments = $parse->BB_Parse( $parse->process( $_POST['comments'] ), false );
		}
		
		
		//* Автоперенос длинных слов
		if( intval( $config['auto_wrap'] ) ) {
			
			$comments = preg_split( '((>)|(<))', $comments, - 1, PREG_SPLIT_DELIM_CAPTURE );
			$n = count( $comments );
			
			for($i = 0; $i < $n; $i ++) {
				if( $comments[$i] == "<" ) {
					$i ++;
					continue;
				}
				
				$comments[$i] = preg_replace( "#([^\s\n\r]{" . intval( $config['auto_wrap'] ) . "})#i", "\\1<br />", $comments[$i] );
			}
			
			$comments = join( "", $comments );
		
		}
		
		if( dle_strlen( $comments, $config['charset'] ) > $config['comments_maxlen'] ) {
			
			msgbox( $lang['comm_err_2'], $lang['news_err_3'] . " <a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );

		} elseif( intval($config['comments_minlen']) AND dle_strlen( $comments, $config['charset'] ) < $config['comments_minlen'] ) {
			
			msgbox( $lang['comm_err_2'], $lang['news_err_40'] . " <a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
		
		} elseif( $parse->not_allowed_tags ) {
			
			msgbox( $lang['comm_err_2'], $lang['news_err_33'] . " <a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
		
		} elseif( $parse->not_allowed_text ) {
			
			msgbox( $lang['comm_err_2'], $lang['news_err_37'] . " <a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
		
		} else {
			$comments = $db->safesql($comments);
			$db->query( "UPDATE " . PREFIX . "_comments SET text='$comments' where id='$id'" );

			clear_cache( array( 'news_', 'rss', 'comm_', 'full_' ) );
		
			msgbox( $lang['comm_ok'], $lang['comm_ok_1'] . " <a href=\"{$_SESSION['referrer']}\">$lang[all_prev]</a>" );
		
		}
	
	} else
		msgbox( $lang['comm_err_2'], $lang['comm_err_3'] );

} elseif( $_POST['mass_action'] == "mass_combine" AND count($_POST['selected_comments']) > 1 ) {

	if( $_POST['dle_allow_hash'] != "" AND $_POST['dle_allow_hash'] == $dle_login_hash AND $is_logged AND $user_group[$member_id['user_group']]['del_allc'] ) {

		$comments_array = array();
		$ids_array = array();

		foreach ( $_POST['selected_comments'] as $id ) {
			$comments_array[] = intval( $id );
		}

		$comments = implode("','", $comments_array);
		$sql_result = $db->query( "SELECT id, text FROM " . PREFIX . "_comments where id IN ('" . $comments . "') ORDER BY date " . $config['comm_msort'] );

		$comments = array();
		while ( $row = $db->get_row( $sql_result ) ) {
			$ids_array[] = $row['id'];
			$comments[] = stripslashes( $row['text'] );
		}
		$db->free( $sql_result );

		$comment = $db->safesql( implode("<br /><br />", $comments) );

		$db->query( "UPDATE " . PREFIX . "_comments SET text='{$comment}' WHERE id='{$ids_array[0]}'" );

		$parent = $ids_array[0];
		unset ($ids_array[0]);
		
		foreach ( $ids_array as $id ) {
			
			if ( $config['tree_comments'] ) {
				$db->query( "UPDATE " . PREFIX . "_comments SET parent='{$parent}' WHERE parent ='{$id}'" );
			}
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_comments WHERE id = '{$id}'" );

			$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
			
			if( $row['is_register'] ) {
				$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
			}

			$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

		}

		clear_cache( array('news_', 'full_', 'comm_', 'rss' ) );
			
		header( "Location: {$_SESSION['referrer']}" );
		die();	

	} else msgbox( $lang['comm_err_2'], $lang['comm_err_4'] );

} elseif( $_POST['mass_action'] == "mass_delete" AND count($_POST['selected_comments']) ) {

	if( $_POST['dle_allow_hash'] != "" AND $_POST['dle_allow_hash'] == $dle_login_hash AND $is_logged AND $user_group[$member_id['user_group']]['del_allc'] ) {

		foreach ( $_POST['selected_comments'] as $id ) {
			
			$id = intval( $id );

			deletecomments( $id );

		}

		clear_cache( array('news_', 'full_', 'comm_', 'rss' ) );
			
		header( "Location: {$_SESSION['referrer']}" );
		die();	

	} else msgbox( $lang['comm_err_2'], $lang['comm_err_4'] );


} else
	msgbox( $lang['comm_err_2'], $lang['comm_err_5']."&nbsp;<a href=\"javascript:history.go(-1);\">{$lang['all_prev']}</a>" );

?>