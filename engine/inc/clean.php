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
 Файл: clean.php
-----------------------------------------------------
 Назначение: очистка и оптимизация базы данных
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if($member_id['user_group'] != 1){ msg("error", $lang['addnews_denied'], $lang['db_denied']); }

$db->query("SHOW TABLE STATUS FROM `".DBNAME."`");
			$mysql_size = 0;
			while ($r = $db->get_array()) {
			if (strpos($r['Name'], PREFIX."_") !== false)
			$mysql_size += $r['Data_length'] + $r['Index_length'] ;
			}
$db->free();

$lang['clean_all'] = str_replace ('{datenbank}', '<font color="red">'.formatsize($mysql_size).'</font>', $lang['clean_all']);

echoheader( "<i class=\"icon-briefcase\"></i>".$lang['header_opt_1'], $lang['clean_title']);

echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function start_clean ( step, size ){

	$("#status").html('{$lang['ajax_info']}');

	if (document.getElementById( 'f_date_c' )) {
		var date = document.getElementById( 'f_date_c' ).value;
	} else { var date = ''; }

	if (document.getElementById( 'next_button' )) {
		document.getElementById( 'next_button' ).disabled = true;
	}
	if (document.getElementById( 'skip_button' )) {
		document.getElementById( 'skip_button' ).disabled = true;
	}

	$.get("engine/ajax/clean.php", { step: step, date: date, size: size, user_hash: "{$dle_login_hash}" }, function(data){

	  $('#main_box').html(data);

	}, 'html');

	return false;
}
//-->
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['clean_title']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div id="main_box"><br />{$lang['clean_all']}<br /><br /><font color="red"><span id="status"></span></font><br /><br />
		<input id = "next_button" onclick="start_clean('1', '{$mysql_size}'); return false;" class="btn btn-green" type="button" value="{$lang['edit_next']}">
		</div>
	  
	</div>
	
   </div>
</div>
HTML;


echofooter();
?>