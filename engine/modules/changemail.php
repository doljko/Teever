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
 Файл: changemail.php
-----------------------------------------------------
 Назначение: Смена E-mail адреса на сайте
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if( !$is_logged ) {

	msgbox( $lang['all_err_1'], $lang['change_mail_4'] );

} else {

	$hashid = @$db->safesql(totranslit($_GET['id']), true, false);

	if ( $hashid ) {

		$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_mail_log WHERE hash='{$hashid}'" );
	
		if ($row['user_id'] AND $member_id['user_id'] AND $row['user_id'] == $member_id['user_id']) {
	
			if ( $user_group[$member_id['user_group']]['admin_editusers'] ) {
	
				msgbox( $lang['all_err_1'], "<ul>".$lang['news_err_42']."</ul>" );
	
			} else {
	
				$row['mail'] = $db->safesql($row['mail']);
				$db->query( "UPDATE " . USERPREFIX . "_users SET email='{$row['mail']}' WHERE user_id = '{$row['user_id']}'" );
				$db->query( "UPDATE " . PREFIX . "_subscribe SET email='{$row['mail']}' WHERE user_id = '{$row['user_id']}'" );
				$db->query( "DELETE FROM " . USERPREFIX . "_mail_log WHERE user_id='{$row['user_id']}'" );
				msgbox( $lang['all_info'], $lang['change_mail_6'] );
	
			}

		} else {
	
			msgbox( $lang['all_err_1'], $lang['change_mail_5'] );
	
		}

	} else msgbox( $lang['all_err_1'], $lang['change_mail_5'] );

}

?>