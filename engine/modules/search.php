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
 Файл: search.php
-----------------------------------------------------
 Назначение: поиск по сайту
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['allow_search'] ) {
	
	$lang['search_denied'] = str_replace( '{group}', $user_group[$member_id['user_group']]['group_name'], $lang['search_denied'] );
	msgbox( $lang['all_info'], $lang['search_denied'] );

} else {

	function strip_data($text) {

		$quotes = array ( "\x60", "\t", "\n", "\r", ",", ";", ":", "[", "]", "{", "}", "=", "*", "^", "%", "$", "<", ">" );
		$goodquotes = array ("#", "'", '"' );
		$repquotes = array ("\#", "\'", '\"' );
		$text = stripslashes( $text );
		$text = trim( strip_tags( $text ) );
		$text = str_replace( $quotes, '', $text );
		$text = str_replace( $goodquotes, $repquotes, $text );
		return $text;
	}

	if ( !defined('BANNERS') ) {
		if ($config['allow_banner']) include_once ENGINE_DIR . '/modules/banners.php';
	}

	$count_result = 0;
	$sql_count = "";
	$sql_find = "";
	$full_s_addfield = "";

	// Минимальное количество символов в слове поиска
	$config['search_length_min'] = 4;

	$tpl->load_template( 'search.tpl' );
	
	$config['search_number'] = intval($config['search_number']);

	if ( $config['search_number'] < 1) $config['search_number'] = 1;
	
	$this_date = date( "Y-m-d H:i:s", $_TIME );
	if( $config['no_date'] AND !$config['news_future'] ) $this_date = " AND " . PREFIX . "_post.date < '" . $this_date . "'"; else $this_date = "";
	
	if( isset( $_REQUEST['story'] ) ) $story = dle_substr( strip_data( rawurldecode( $_REQUEST['story'] ) ), 0, 90, $config['charset'] ); else $story = "";
	if( isset( $_REQUEST['search_start'] ) ) $search_start = intval( $_REQUEST['search_start'] ); else $search_start = 0;
	if( isset( $_REQUEST['titleonly'] ) ) $titleonly = intval( $_REQUEST['titleonly'] ); else $titleonly = 0;
	if( isset( $_REQUEST['searchuser'] ) ) $searchuser = dle_substr( $_REQUEST['searchuser'], 0, 40, $config['charset'] ); else $searchuser = "";
	if( isset( $_REQUEST['exactname'] ) ) $exactname = $_REQUEST['exactname']; else $exactname = "";
	if( isset( $_REQUEST['all_word_seach'] ) ) $all_word_seach = intval($_REQUEST['all_word_seach']); else $all_word_seach = 0;
	if( isset( $_REQUEST['replyless'] ) ) $replyless = intval( $_REQUEST['replyless'] ); else $replyless = 0;
	if( isset( $_REQUEST['replylimit'] ) ) $replylimit = intval( $_REQUEST['replylimit'] ); else $replylimit = 0;
	if( isset( $_REQUEST['searchdate'] ) ) $searchdate = intval( $_REQUEST['searchdate'] ); else $searchdate = 0;
	if( isset( $_REQUEST['beforeafter'] ) ) $beforeafter = htmlspecialchars( $_REQUEST['beforeafter'], ENT_QUOTES, $config['charset'] ); else $beforeafter = "after";


	if( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $searchuser ) ) $searchuser="";

	if ($config['full_search']) {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = htmlspecialchars( $_REQUEST['sortby'], ENT_QUOTES, $config['charset']  ); else $sortby = "";
	} else {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = htmlspecialchars( $_REQUEST['sortby'], ENT_QUOTES, $config['charset']  ); else $sortby = "date";
	}

	if( isset( $_REQUEST['resorder'] ) ) $resorder = htmlspecialchars( $_REQUEST['resorder'], ENT_QUOTES, $config['charset'] ); else $resorder = "desc";
	if( isset( $_REQUEST['showposts'] ) ) $showposts = intval( $_REQUEST['showposts'] ); else $showposts = 0;
	if( isset( $_REQUEST['result_from'] ) ) $result_from = intval( $_REQUEST['result_from'] ); else $result_from = 1;

	$full_search = intval( $_REQUEST['full_search'] );

	if( !count( $_REQUEST['catlist'] ) ) {
		$catlist = array ();
		$catlist[] = '0';
	} else
		$catlist = $_REQUEST['catlist'];

	$category_list = array();
	
	foreach ( $catlist as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $story = stripslashes( $story );

	$findstory = stripslashes($story); // Для вывода в поле поиска
	$findstory = htmlspecialchars($findstory, ENT_QUOTES, $config['charset']);
	$story = addslashes( $story );

	if ($titleonly == 2 AND !empty( $searchuser ) ) $searchuser = "";
	if( empty( $story ) AND !empty( $searchuser ) AND $titleonly != 2) $story = "___SEARCH___ALL___"; // Для поиска всех статей
	if( $search_start < 0 ) $search_start = 0; // Начальная страница поиска
	if( $titleonly < 0 or $titleonly > 3 ) $titleonly = 0; // Искать в заголовках, статьях, комментариях
	if( $replyless < 0 or $replyless > 1 ) $replyless = 0; // Искать больше или меньше ответов
	if( $replylimit < 0 ) $replylimit = 0; // Лимит ответов
	if( $showposts < 0 or $showposts > 1 ) $showposts = 0; // Искать в статьях или комментариях юзера
	
	$listdate = array (0, - 1, 1, 7, 14, 30, 90, 180, 365 ); // Искать за период ХХХ дней
	if( ! (in_array( $searchdate, $listdate )) ) $searchdate = 0;
	if( $beforeafter != "after" and $beforeafter != "before" ) $beforeafter = "after"; // Искать до или после периода дней
	$listsortby = array ("date", "title", "comm_num", "news_read", "autor", "category", "rating" );

	if ($config['full_search']) {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = ""; // Сортировать по полям
	} else {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = "date"; // Сортировать по полям
	}

	$listresorder = array ("desc", "asc" );
	if( ! (in_array( $resorder, $listresorder )) ) $resorder = "desc"; // Сортировать по возрастающей или убывающей
	

	// Определение выбранных ранее опций, переданных в форме
	$titleonly_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$titleonly_sel[$titleonly] = 'selected="selected"';
	$replyless_sel = array ('0' => '', '1' => '' );
	$replyless_sel[$replyless] = 'selected="selected"';
	$searchdate_sel = array ('0' => '', '-1' => '', '1' => '', '7' => '', '14' => '', '30' => '', '90' => '', '180' => '', '365' => '' );
	$searchdate_sel[$searchdate] = 'selected="selected"';
	$beforeafter_sel = array ('after' => '', 'before' => '' );
	$beforeafter_sel[$beforeafter] = 'selected="selected"';
	$sortby_sel = array ('date' => '', 'title' => '', 'comm_num' => '', 'news_read' => '', 'autor' => '', 'category' => '', 'rating' => '' );
	$sortby_sel[$sortby] = 'selected="selected"';
	$resorder_sel = array ('desc' => '', 'asc' => '' );
	$resorder_sel[$resorder] = 'selected="selected"';
	$showposts_sel = array ('0' => '', '1' => '' );
	$showposts_sel[$showposts] = 'checked="checked"';
	if( $exactname == "yes" ) $exactname_sel = 'checked="checked"';
	else $exactname_sel = '';

	if( $all_word_seach == 1 ) $all_word_seach_sel = 'checked="checked"';
	else $all_word_seach_sel = '';
	
	// Вывод формы поиска
	if( $category_list == "" or $category_list == "0" ) {
		$catselall = "selected=\"selected\"";
	} else {
		$catselall = "";
		$category_list = preg_replace( "/^0\,/", '', $category_list );
	}
	
	// Определение и вывод доступных категорий
	$cats = "<select style=\"width:95%;height:200px;\" name=\"catlist[]\" size=\"13\" multiple=\"multiple\">";
	$cats .= "<option " . $catselall . " value=\"0\">" . $lang['s_allcat'] . "</option>";
	$cats .= CategoryNewsSelection( explode( ',', $category_list ), 0, false );
	$cats .= "</select>";
	
	$tpl->copy_template .= <<<HTML
<script type="text/javascript">
<!-- begin
function clearform(frmname){
  var frm = document.getElementById(frmname);
  for (var i=0;i<frm.length;i++) {
    var el=frm.elements[i];
    if (el.type=="checkbox" || el.type=="radio") {
    	if (el.name=='showposts') {document.getElementById('rb_showposts_0').checked=1; } else {el.checked=0; }
    }
    if ((el.type=="text") || (el.type=="textarea") || (el.type == "password")) { el.value=""; continue; }
    if ((el.type=="select-one") || (el.type=="select-multiple")) { el.selectedIndex=0; }
  }
  document.getElementById('replylimit').value = 0;
  document.getElementById('search_start').value = 0;
  document.getElementById('result_from').value = 1;
}
function list_submit(prm){
  var frm = document.getElementById('fullsearch');
	if (prm == -1) {
		prm=0;
		frm.result_from.value=1;
	} else {
		frm.result_from.value=(prm-1) * {$config['search_number']} + 1;
	}
	frm.search_start.value=prm;

  frm.submit();
  return false;
}
function full_submit(prm){
    document.getElementById('fullsearch').full_search.value=prm;
    list_submit(-1);
}
// end -->
</script>
HTML;
	
	$searchtable = <<<HTML
<form name="fullsearch" id="fullsearch" action="{$config['http_home_url']}index.php?do=search" method="post">
<input type="hidden" name="do" id="do" value="search" />
<input type="hidden" name="subaction" id="subaction" value="search" />
<input type="hidden" name="search_start" id="search_start" value="$search_start" />
<input type="hidden" name="full_search" id="full_search" value="$full_search" />
<input type="hidden" name="result_from" id="result_from" value="$result_from" />
HTML;
	
	if( $full_search ) {

		if ($config['full_search']) {
			$full_search_option = "<option value=\"\" selected=\"selected\">{$lang['s_fsrelate']}</option><option {$sortby_sel['date']} value=\"date\">{$lang['s_fsdate']}</option>";
			$all_word_option = "";
		} else {

			$full_search_option = "<option {$sortby_sel['date']} value=\"date\">{$lang['s_fsdate']}</option>";
			$all_word_option = "<div><label for=\"all_word_seach\"><input type=\"checkbox\" name=\"all_word_seach\" value=\"1\" id=\"all_word_seach\" {$all_word_seach_sel} />{$lang['s_fword']}</label></div>";
		}
		
		$searchtable .= <<<HTML
<table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="search">
      <div align="center">
        <table cellpadding="0" cellspacing="2" width="100%">

        <tr style="vertical-align: top;">
				<td class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_con']}</legend>
						<table cellpadding="0" cellspacing="3" border="0">
						<tr>
						<td class="search">
							<div>{$lang['s_word']}</div>
							<div><input type="text" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" onchange="document.getElementById('result_from').value = 1" /></div>
							{$all_word_option}
						</td>
						</tr>
						<tr>
						<td class="search">
							<select class="textin" name="titleonly" id="titleonly">
								<option {$titleonly_sel['0']} value="0">{$lang['s_ncom']}</option>
								<option {$titleonly_sel['1']} value="1">{$lang['s_ncom1']}</option>
                                <option {$titleonly_sel['2']} value="2">{$lang['s_static']}</option>
								<option {$titleonly_sel['3']} value="3">{$lang['s_tnews']}</option>
							</select>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>

				<td class="search" valign="top">					
					<fieldset style="margin:0px">
						<legend>{$lang['s_mname']}</legend>
						<table cellpadding="0" cellspacing="3" border="0">
						<tr>
						<td class="search">
							<div>{$lang['s_fname']}</div>
							<div id="userfield"><input type="text" name="searchuser" id="searchuser" value="$searchuser" class="textin" style="width:250px" /><br /><label for="exactname"><input type="checkbox" name="exactname" value="yes" id="exactname" {$exactname_sel} />{$lang['s_fgname']}</label>
							</div>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>
				</tr>

				<tr style="vertical-align: top;">

				<td width="50%" class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fart']}</legend>
						<div style="padding:3px">
							<select class="textin" name="replyless" id="replyless" style="width:200px">
								<option {$replyless_sel['0']} value="0">{$lang['s_fmin']}</option>
								<option {$replyless_sel['1']} value="1">{$lang['s_fmax']}</option>
							</select>
							<input type="text" name="replylimit" id="replylimit" size="5" value="$replylimit" class="textin" /> {$lang['s_wcomm']}
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fdaten']}</legend>

						<div style="padding:3px">					
							<select name="searchdate" id="searchdate" class="textin" style="width:200px">
								<option {$searchdate_sel['0']} value="0">{$lang['s_tall']}</option>
								<option {$searchdate_sel['-1']} value="-1">{$lang['s_tlast']}</option>
								<option {$searchdate_sel['1']} value="1">{$lang['s_tday']}</option>
								<option {$searchdate_sel['7']} value="7">{$lang['s_tweek']}</option>
								<option {$searchdate_sel['14']} value="14">{$lang['s_ttweek']}</option>
								<option {$searchdate_sel['30']} value="30">{$lang['s_tmoth']}</option>
								<option {$searchdate_sel['90']} value="90">{$lang['s_tfmoth']}</option>
								<option {$searchdate_sel['180']} value="180">{$lang['s_tsmoth']}</option>
								<option {$searchdate_sel['365']} value="365">{$lang['s_tyear']}</option>
							</select>
							<select name="beforeafter" id="beforeafter" class="textin">
								<option {$beforeafter_sel['after']} value="after">{$lang['s_fnew']}</option>
								<option {$beforeafter_sel['before']} value="before">{$lang['s_falt']}</option>
							</select>
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fsoft']}</legend>
							<div style="padding:3px">
								<select name="sortby" id="sortby" class="textin" style="width:200px">
									{$full_search_option}
									<option {$sortby_sel['title']} value="title" >{$lang['s_fstitle']}</option>
									<option {$sortby_sel['comm_num']} value="comm_num" >{$lang['s_fscnum']}</option>
									<option {$sortby_sel['news_read']} value="news_read" >{$lang['s_fsnnum']}</option>
									<option {$sortby_sel['autor']} value="autor" >{$lang['s_fsaut']}</option>
									<option {$sortby_sel['category']} value="category" >{$lang['s_fscat']}</option>
									<option {$sortby_sel['rating']} value="rating" >{$lang['s_fsrate']}</option>
								</select>
								<select name="resorder" id="resorder" class="textin">
									<option {$resorder_sel['desc']} value="desc">{$lang['s_fsdesc']}</option>
									<option {$resorder_sel['asc']} value="asc">{$lang['s_fsasc']}</option>
								</select>
							</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_vlegend']}</legend>

						<table cellpadding="0" cellspacing="3" border="0">
						<tr align="left" valign="middle">
						<td align="left" class="search">{$lang['s_vwie']}&nbsp;&nbsp;
							<label for="rb_showposts_0"><input type="radio" name="showposts" value="0" id="rb_showposts_0" {$showposts_sel['0']} />{$lang['s_vnews']}</label>
							<label for="rb_showposts_1"><input type="radio" name="showposts" value="1" id="rb_showposts_1" {$showposts_sel['1']} />{$lang['s_vtitle']}</label>
						</td>
						</tr>

						</table>
					</fieldset>
				</td>

				<td width="50%" class="search" valign="top">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fcats']}</legend>
							<div style="padding:3px">
								<div>$cats</div>
							</div>

					</fieldset>
				</td>
				</tr>

        <tr>
                <td class="search" colspan="2">
                    <div style="margin-top:6px">
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 0px;" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;" />
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 20px;" name="doclear" id="doclear" value="{$lang['s_fstop']}" onclick="javascript:clearform('fullsearch'); return false;" />
                        <input type="reset" class="bbcodes" style="margin:0px 20px 0 20px;" name="doreset" id="doreset" value="{$lang['s_freset']}" />
                    </div>

                </td>
                </tr>

        </table>
      </div>
    </td>
  </tr>
</table>
HTML;
	
	} else {

	if ( $smartphone_detected ) {

		$link_full_search = "";

	} else {

		$link_full_search = "<input type=\"button\" class=\"bbcodes\" name=\"dofullsearch\" id=\"dofullsearch\" value=\"{$lang['s_ffullstart']}\" onclick=\"javascript:full_submit(1); return false;\" />";

	}
		
		$searchtable .= <<<HTML
<table cellpadding="4" cellspacing="0" width="100%">
  <tr>
    <td class="search">
      <div style="margin:10px;">
                <input type="text" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" onchange="document.getElementById('result_from').value = 1" /><br /><br />
                <input type="button" class="bbcodes" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;" />
                {$link_full_search}
            </div>

        </td>
    </tr>
</table>
HTML;
	
	}
	
	$searchtable .= <<<HTML

</form>
HTML;
	
	$tpl->set( '{searchtable}', $searchtable );
	// По умолчанию, выводится только форма поиска
	if( $subaction != "search" ) {
		$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
		$tpl->compile( 'content' );
	}
	// Конец вывода формы поиска
	

	if( $subaction == "search" ) {
		// Вывод результатов поиска		

		if ($config['full_search']) {
	
			$arr = explode( ' ', $story );
			$story_maxlen = 0;
			$story = array ();
			
			foreach ( $arr as $word ) {
				$wordlen = dle_strlen( trim( $word ), $config['charset'] );
				
				if( $wordlen >= $config['search_length_min'] ) $story[] = $word;
				
				if( $wordlen > $story_maxlen ) {
					$story_maxlen = $wordlen;
				}
			}

			if ($titleonly < 3) $story = '+'.implode( " +", $story ); else $story = implode( "%", $story );
	
		} else {
	
			if ( !$all_word_seach ) $story = preg_replace( "#(\s+|__OR__)#i", '%', $story );

			$story_maxlen = dle_strlen( trim( $story ), $config['charset'] );
	
		}
	
		if( (empty( $story ) or ($story_maxlen < $config['search_length_min'])) and (empty( $searchuser ) or (strlen( $searchuser ) < $config['search_length_min'])) ) {
			
			msgbox( $lang['all_info'], $lang['search_err_3'] );
			
			$tpl->set( '{searchmsg}', '' );
			$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
			$tpl->compile( 'content' );
		
		} else {
			// Начало подготовки поиска
			if( $search_start ) {
				$search_start = $search_start - 1;
				$search_start = $search_start * $config['search_number'];
			}
			
			// Проверка разрешенных категорий из списка выбранных категорий
			$allow_cats = $user_group[$member_id['user_group']]['allow_cats'];
			$not_allow_cats = $user_group[$member_id['user_group']]['not_allow_cats'];
			
			$allow_list = explode( ',', $allow_cats );
			$not_allow_list = explode( ',', $not_allow_cats );
			$stop_list = "";
			$stop_not_allowed_list = "";
			
			if( $allow_list[0] == "all" AND $not_allow_list[0] == "") {
				// Все категории доступны для группы
				if( $category_list == "" OR $category_list == "0" ) {
					// Выбран поиск по всем категориям
					;
				} else {
					// Выбран поиск по некоторым категориям
					$stop_list = str_replace( ',', '|', $category_list );
				}
				
			} else {
				// Не все категории доступны для группы
				if( $category_list == "" or $category_list == "0" ) {
					// Выбран поиск по всем категориям
					if( $not_allow_list[0] == "" ) {
						
						$stop_list = str_replace( ',', '|', $allow_cats );
						
					} else {
						
						$stop_not_allowed_list = str_replace( ',', '|', $not_allow_cats );
						
					}
					
				} else {
					// Выбран поиск по некоторым категориям
					if( $not_allow_list[0] == "" ) {
						
						$cats_list = explode( ',', $category_list );
						foreach ( $cats_list as $id ) {
							if( in_array( $id, $allow_list ) ) $stop_list .= $id . '|';
						}
						$stop_list = substr( $stop_list, 0, strlen( $stop_list ) - 1 );
						
					} else {
						
						$cats_list = explode( ',', $category_list );
						foreach ( $cats_list as $id ) {
							if( !in_array( $id, $not_allow_list ) ) $stop_list .= $id . '|';
						}
						
						$stop_list = substr( $stop_list, 0, strlen( $stop_not_allowed_list ) - 1 );
						
						if( empty( $stop_list ) ) $stop_not_allowed_list = str_replace( ',', '|', $not_allow_cats );
						
					}
				}
			}
			
			// Ограничение по категориям
			$where_category = "";
			if( !empty( $stop_list ) ) {
				
				if( $config['allow_multi_category'] ) {
					
					$where_category = "category REGEXP '[[:<:]](" . $stop_list . ")[[:>:]]'";
				
				} else {
					
					$stop_list = str_replace( "|", "','", $stop_list );
					$where_category = "category IN ('" . $stop_list . "')";
				
				}
			}
			
			if( !empty( $stop_not_allowed_list ) ) {
				
				if( $config['allow_multi_category'] ) {
					
					$where_category = "category NOT REGEXP '[[:<:]](" . $stop_not_allowed_list . ")[[:>:]]'";
				
				} else {
					
					$stop_not_allowed_list = str_replace( "|", "','", $stop_not_allowed_list );
					$where_category = "category NOT IN ('" . $stop_not_allowed_list . "')";
				
				}
			}
			
			if( $story == "___SEARCH___ALL___" ) $story = '';
			$thistime = date( "Y-m-d H:i:s", time() );
			
			if( $exactname == 'yes' ) $likename = '';
			else $likename = '%';
			if( $searchdate != '0' ) {
				if( $searchdate != '-1' ) {
					$qdate = date( "Y-m-d H:i:s", (time() - $searchdate * 86400) );
				} else {
					if( $is_logged and isset( $_SESSION['member_lasttime'] ) ) $qdate = date( "Y-m-d H:i:s", $_SESSION['member_lasttime'] );
					else $qdate = $thistime;
				}
			}
			
			// Поиск по автору статьи или комментария
			$autor_posts = '';
			$autor_comms = '';
			$searchuser = $db->safesql($searchuser);
			if( ! empty( $searchuser ) ) {
				switch ($titleonly) {
					case 0 :
						// Искать только в статьях
						$autor_posts = PREFIX . "_post.autor like '$searchuser$likename'";
						break;
					case 3 :
						// Искать только в статьях
						$autor_posts = PREFIX . "_post.autor like '$searchuser$likename'";
						break;
					case 1 :
						// Искать только в комментариях
						$autor_comms = PREFIX . "_comments.autor like '$searchuser$likename'";
						break;
				}
			}
			
			$where_reply = "";
			if( ! empty( $replylimit ) ) {
				if( $replyless == 0 ) $where_reply = PREFIX . "_post.comm_num >= '" . $replylimit . "'";
				else $where_reply = PREFIX . "_post.comm_num <= '" . $replylimit . "'";
			}
			
			// Поиск по ключевым словам

			if ($config['full_search']) {
	
					$titleonly_where = array ('0' => "MATCH(title,short_story,full_story,xfields) AGAINST ('{story}' IN BOOLEAN MODE)", // Искать только в статьях
											  '1' => "MATCH(text) AGAINST ('{story}' IN BOOLEAN MODE)", // Искать только в комментариях
											  '2' => "MATCH(" . PREFIX . "_static.template) AGAINST ('{story}' IN BOOLEAN MODE)", // Искать только в статических страницах
											  '3' => "title LIKE '%{story}%'" ); // Искать только в заголовках статей

					if ($titleonly < 3 AND $sortby == "" ) {
						$full_s_addfield = ", ".$titleonly_where[$titleonly]." as score";
						$full_s_addfield = str_replace( "{story}", $db->safesql($story), $full_s_addfield );
						$sortby = "score";

					}

			} else {
	
					$titleonly_where = array ('0' => "short_story LIKE '%{story}%' OR full_story LIKE '%{story}%' OR xfields LIKE '%{story}%' OR title LIKE '%{story}%'", // Искать только в статьях
											  '1' => "text LIKE '%{story}%'", // Искать только в комментариях
											  '2' => PREFIX . "_static.template LIKE '%{story}%'", // Искать только в статических страницах
											  '3' => "title LIKE '%{story}%'" ); // Искать только в заголовках статей
			}

			if( !empty( $story ) ) {
	
				foreach ( $titleonly_where as $name => $value ) {
					$value2 = str_replace( "{story}", $db->safesql($story), $value );
						
					$titleonly_where[$name] = $value2;
				}
			}
			
			// Поиск по статьям
			if( in_array( $titleonly, array (0, 3 ) ) ) {
				$where_posts = "WHERE " . PREFIX . "_post.approve=1" . $this_date;
				if( ! empty( $where_category ) ) $where_posts .= " AND " . $where_category;

				if ($config['full_search']) {
					if( ! empty( $story ) ) $where_posts .= " AND " . $titleonly_where[$titleonly];
				} else {
					if( ! empty( $story ) ) $where_posts .= " AND (" . $titleonly_where[$titleonly] . ")";
				}

				if( ! empty( $autor_posts ) ) $where_posts .= " AND " . $autor_posts;

				$sdate = PREFIX . "_post.date";

				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_posts .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_posts .= " AND " . $where_reply;
				$where = $where_posts;

				if ($config['full_search']) if( $titleonly_where[$titleonly] == "" ) $titleonly_where[$titleonly] = "''";

				$posts_fields = "SELECT " . PREFIX . "_post.id, " . PREFIX . "_post.autor, " . PREFIX . "_post.date, " . PREFIX . "_post.short_story, CHAR_LENGTH(" . PREFIX . "_post.full_story) as full_story, " . PREFIX . "_post.xfields, " . PREFIX . "_post.title, " . PREFIX . "_post.category, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.comm_num, " . PREFIX . "_post.allow_comm, " . PREFIX . "_post.fixed, " . PREFIX . "_post.tags, " . PREFIX . "_post_extras.news_read, " . PREFIX . "_post_extras.allow_rate, " . PREFIX . "_post_extras.rating, " . PREFIX . "_post_extras.vote_num, " . PREFIX . "_post_extras.votes, " . PREFIX . "_post_extras.view_edit, " . PREFIX . "_post_extras.editdate, " . PREFIX . "_post_extras.editor, " . PREFIX . "_post_extras.reason{$full_s_addfield}";
				$sql_from = "FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id)";
				$sql_fields = $posts_fields;
				$sql_find = "$sql_fields $sql_from $where";
				$sql_from = "FROM " . PREFIX . "_post";


			}

			// Поиск по комментариям 
			if( $titleonly == 1) {
				$where_comms = "WHERE " . PREFIX . "_post.approve=1" . $this_date;
				if( ! empty( $where_category ) ) $where_comms .= " AND " . $where_category;
				if( ! empty( $story ) ) $where_comms .= " AND (" . $titleonly_where['1'] . ")";
				if( ! empty( $autor_comms ) ) $where_comms .= " AND " . $autor_comms;
				$sdate = PREFIX . "_comments.date";
				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_comms .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_comms .= " AND " . $where_reply;
				$where = $where_comms;

				if( $config['allow_cmod'] ) $where .= " AND " . PREFIX . "_comments.approve=1";
				$comms_fields = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, " . PREFIX . "_comments.date, " . PREFIX . "_comments.autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, " . PREFIX . "_comments.rating, " . PREFIX . "_comments.vote_num, name, " . USERPREFIX . "_users.email, news_num, " . USERPREFIX . "_users.comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, " . USERPREFIX . "_users.xfields, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category{$full_s_addfield}";
				$sql_from = "FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id";
				$sql_fields = $comms_fields;
				$sql_find = "$sql_fields $sql_from $where";
				$sql_from = "FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id";

			}
		
			$order_by = $sortby . " " . $resorder;
		
			// Поиск в статических страницах
			if( $titleonly == 2 ) {
				$sql_from = "FROM " . PREFIX . "_static";
				$sql_fields = "SELECT id, name AS static_name, descr AS title, template AS story, allow_template, grouplevel, date, views";
				if ( $titleonly_where[$titleonly] )	$where = "WHERE " . $titleonly_where[$titleonly];
				else $where = "";
				$sql_find = "$sql_fields $sql_from $where";
				$order_by = "id";
			}
			
			// ------ Запрос к базе	
			$from_num = $search_start + 1;
			
			if ($config['full_search']) {

				if( $sortby != "" ) $order_by = "ORDER BY " . $order_by; else $order_by = "";
				
				$sql_request = "$sql_find $order_by LIMIT $search_start,{$config['search_number']}";
	
			} else {
	
				$sql_request = "$sql_find ORDER BY $order_by LIMIT $search_start,{$config['search_number']}";
	
			}

			if ($titleonly != 1) {	
				$sql_result = $db->query( $sql_request );
				$found_result = $db->num_rows( $sql_result );
			}

			$result_count = $db->super_query( "SELECT COUNT(*) as count $sql_from $where" );
			$count_result = $result_count['count'];
			
			$config['search_pages'] = intval($config['search_pages']);
			
			if ( $config['search_pages'] > 0 ) {
				if( $count_result > ($config['search_number'] * $config['search_pages']) ) $count_result = ($config['search_number'] * $config['search_pages']);
			}
			
			// Не найдено
			if( !$count_result ) {

				msgbox( $lang['all_info'], $lang[search_err_2] );
				$tpl->set( '{searchmsg}', '' );
				$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
				$tpl->compile( 'content' );

			} else {

				$to_num = $search_start + $found_result;
				
				// Вывод информации о количестве найденных результатов
				if ($titleonly == 1) {
					$tpl->set( '{searchmsg}', '' );
					$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
				} else {
					$searchmsg = "$lang[search_ok] " . $count_result . " $lang[search_ok_1] ($lang[search_ok_2] " . $from_num . " - " . $to_num . ") :";
					$tpl->set( '{searchmsg}', $searchmsg );
					$tpl->set( '[searchmsg]', "" );
					$tpl->set( '[/searchmsg]', "" );
				}

				$tpl->compile( 'content' );


				if( $titleonly == 2 ) {
					// Результаты поиска в статических страницах
					while ( $row = $db->get_row( $sql_result ) ) {

						$row['grouplevel'] = explode( ',', $row['grouplevel'] );

						if( $row['grouplevel'][0] != "all" and ! in_array( $member_id['user_group'], $row['grouplevel'] ) ) {
							$tpl->result['content'] .= $lang['static_denied'];
						} else {
							$attachments[] = $row['id'];
							$row['story'] = stripslashes( $row['story'] );

							$news_seiten = explode( "{PAGEBREAK}", $row['story'] );
							$anzahl_seiten = count( $news_seiten );

							$row['story'] = $news_seiten[0];

							$news_seiten = "";
							unset( $news_seiten );

							if( $anzahl_seiten > 1 ) {

								if( $config['allow_alt_url'] ) {
									$replacepage = "<a href=\"" . $config['http_home_url'] . "page," . "\\1" . "," . $row['static_name'] . ".html\">\\2</a>";
								} else {
									$replacepage = "<a href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "&news_page=\\1\">\\2</a>";
								}

								$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", $replacepage, $row['story'] );

							} else {
								
								$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", "", $row['story'] );
							
							}

							$row['story'] = str_replace( '{ACCEPT-DECLINE}', "",  $row['story']);
	
							$title = stripslashes( strip_tags( $row['title'] ) );
							
							if( $row['allow_template'] ) {
								$tpl->load_template( 'static.tpl' );
								if( $config['allow_alt_url'] ) $static_descr = "<a title=\"" . $title . "\" href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $title . "</a>";
								else $static_descr = "<a title=\"" . $title . "\" href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "\" >" . $title . "</a>";
								$tpl->set( '{description}', $static_descr );

								if (dle_strlen( $row['story'], $config['charset'] ) > 2000) {

									$row['story'] = dle_substr( strip_tags ($row['story']), 0, 2000, $config['charset'])." .... ";
									if( $config['allow_alt_url'] ) $row['story'] .= "( <a href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $lang['search_s_go'] . "</a> )";
									else $row['story'] .= "( <a href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "\" >" . $lang['search_s_go'] . "</a> )";

								}

								$tpl->set( '{static}', $row['story'] );
								$tpl->set( '{pages}', '' );

								if( @date( "Ymd", $row['date'] ) == date( "Ymd", $_TIME ) ) {
									
									$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
								
								} elseif( @date( "Ymd", $row['date'] ) == date( "Ymd", ($_TIME - 86400) ) ) {
									
									$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
								
								} else {
									
									$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
								
								}
								$news_date = $row['date'];						
								$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

								$tpl->set( '{views}', $row['views'] );
			
								if( $config['allow_alt_url'] ) $print_link = $config['http_home_url'] . "print:" . $row['static_name'] . ".html";
								else $print_link = $config['http_home_url'] . "engine/print.php?do=static&amp;page=" . $row['static_name'];
								
								$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\">" );
								$tpl->set( '[/print-link]', "</a>" );
								
								$tpl->compile( 'content' );
								$tpl->clear();
							} else
								$tpl->result['content'] .= $row['story'];
							
							if( $config['files_allow'] ) {
								if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
									$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments, true );
								}
							}
						
						}
					}

				} else {
					// Вывод результатов поиска в новостях
					if ($titleonly == 0 OR $titleonly == 3) {

						$tpl->load_template( 'searchresult.tpl' );
						$build_navigation = false;
						$short_news_cache = false;
						
						include (ENGINE_DIR . '/modules/show.custom.php');

						$tpl->result['content'] = preg_replace ( "'\\[searchcomments\\](.+?)\\[/searchcomments\\]'si", '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[searchposts]', '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[/searchposts]', '', $tpl->result['content'] );

						if( $showposts == 0 ) {

							$tpl->result['content'] = preg_replace ( "'\\[shortresult\\](.+?)\\[/shortresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[fullresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/fullresult]', '', $tpl->result['content'] );

						} else {
							$tpl->result['content'] = preg_replace ( "'\\[fullresult\\](.+?)\\[/fullresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[shortresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/shortresult]', '', $tpl->result['content'] );

						}

						if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
							$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
						}
					}

					// Вывод результатов поиска в комментариях
					if ($titleonly == 1) {
						include_once ENGINE_DIR . '/classes/comments.class.php';

						if ( $search_start ) $_GET['cstart'] = ($search_start/$config['search_number'])+1;

						$comments = new DLE_Comments( $db, $count_result, intval($config['search_number']) );

						$comments->query = $sql_find." ORDER BY id desc";

						$comments->build_comments('searchresult.tpl', 'lastcomments' );

						$found_result = $comments->intern_count;
						$to_num = $search_start + $found_result;

						$tpl->result['content'] = preg_replace ( "'\\[searchposts\\].*?\\[/searchposts\\]'si", '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[searchcomments]', '', $tpl->result['content'] );
						$tpl->result['content'] = str_ireplace( '[/searchcomments]', '', $tpl->result['content'] );

						if( $showposts == 0 ) {

							$tpl->result['content'] = preg_replace ( "'\\[shortresult\\].*?\\[/shortresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[fullresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/fullresult]', '', $tpl->result['content'] );

						} else {
							$tpl->result['content'] = preg_replace ( "'\\[fullresult\\].*?\\[/fullresult\\]'si", '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[shortresult]', '', $tpl->result['content'] );
							$tpl->result['content'] = str_ireplace( '[/shortresult]', '', $tpl->result['content'] );

						}


					}	

				}

			}
		}
	}
	
	$tpl->clear();
	
	//####################################################################################################################
	//         Навигация
	//####################################################################################################################
	if( $found_result > 0 ) {
		$tpl->load_template( 'navigation.tpl' );
		
		//----------------------------------
		// Previous link
		//----------------------------------
		if( isset( $search_start ) and $search_start != "" and $search_start > 0 ) {
			$prev = $search_start / $config['search_number'];
			$prev_page = "<a name=\"prevlink\" id=\"prevlink\" onclick=\"javascript:list_submit($prev); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", $prev_page . "\\1</a>" );
		
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}

		//----------------------------------
		// Pages
		//----------------------------------
		if( $config['search_number'] ) {
			
			$pages_count = @ceil( $count_result / $config['search_number'] );
			$pages_start_from = 0;
			$pages = "";
						
			if( $pages_count <= 10 ) {
			
				for($j = 1; $j <= $pages_count; $j ++) {
					if( $pages_start_from != $search_start ) {
						$pages .= "<a onclick=\"javascript:list_submit($j); return(false)\" href=\"#\">$j</a> ";
					} else {
						$pages .= " <span>$j</span> ";
					}
					$pages_start_from += $config['search_number'];
				}
							
			} else {
								
				$start = 1;
				$end = 10;
				$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
				
				if( $search_start > 0 ) {
			
					if( ($search_start / $config['search_number']) > 6 ) {
										
						$start = @ceil( $search_start / $config['search_number'] ) - 4;
						$end = $start + 8;
										
						if( $end >= $pages_count-1 ) {
							$start = $pages_count - 9;
							$end = $pages_count - 1;
						}
							
						$pages_start_from = ($start - 1) * $config['search_number'];
					
					}
				
				}
				
				if( $end >= $pages_count-1 ) $nav_prefix = ""; else $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";

				if( $start >= 2 ) {
					
					if( $start >= 3 ) $before_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> "; else $before_prefix = "";
					
					$pages .= "<a onclick=\"javascript:list_submit(1); return(false)\" href=\"#\">1</a> ".$before_prefix;
								
				}
								
				for($j = $start; $j <= $end; $j ++) {
									
					if( $pages_start_from != $search_start ) {
				
						$pages .= "<a onclick=\"javascript:list_submit($j); return(false)\" href=\"#\">$j</a> ";
										
					} else {
					
						$pages .= "<span>$j</span> ";
					}
					
					$pages_start_from += $config['search_number'];
			
				}
								
				if( $pages_start_from != $search_start ) {
									
					$pages .= $nav_prefix . "<a onclick=\"javascript:list_submit($pages_count); return(false)\" href=\"#\">{$pages_count}</a>";
								
				} else $pages .= "<span>{$pages_count}</span> ";
							
			}

			$tpl->set( '{pages}', $pages );
		}
		
		//----------------------------------
		// Next link
		//----------------------------------
		if( $config['search_number'] < $count_result and $to_num < $count_result ) {
			$next_page = $to_num / $config['search_number'] + 1;
			$next = "<a name=\"nextlink\" id=\"nextlink\" onclick=\"javascript:list_submit($next_page); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", $next . "\\1</a>" );
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}
		
		if( ! $no_prev or ! $no_next ) {
			$tpl->compile( 'content' );
		}
		
		$tpl->clear();
	}
}
?>