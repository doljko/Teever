<?PHP
/*
=====================================================
 Webman - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: default.skin.php
-----------------------------------------------------
 Назначение: макет админпанели
=====================================================
*/

if ($is_loged_in) {

	if ( count(explode("@", $member_id['foto'])) == 2 ) {
		
		$avatar = '//www.gravatar.com/avatar/' . md5(trim($member_id['foto'])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']);
		
	} else {
		
		if( $member_id['foto'] ) {
			
			if (strpos($member_id['foto'], "//") === 0) $avatar = "http:".$member_id['foto']; else $avatar = $member_id['foto'];

			$avatar = @parse_url ( $avatar );

			if( $avatar['host'] ) {
				
				$avatar = $member_id['foto'];
				
			} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $member_id['foto'];

		} else $avatar = "engine/skins/images/noavatar.png";
	}
	if ( $member_id['pm_unread'] ) {
		$pop_notice = "<span class=\"badge badge-dark-red\">{$member_id['pm_unread']}</span>";
	} else $pop_notice = "";

	if ( $member_id['pm_all'] ) {
		$message_notice = "<span class=\"label label-dark-red pull-right\">{$member_id['pm_all']}</span>";
	} else $message_notice = "";
	
	$profile_link = $config['http_home_url'] . "user/" . urlencode ( $member_id['name'] ) . "/";
	
	$options = array ();
	
	$options['config'] = array (
								
								array (
											'name' => $lang['opt_all'], 
											'url' => "?mod=options&action=syscon", 
											'mod' => "options", 
											'access' => "admin" 
								), 
								
								array (
											'name' => $lang['opt_cat'], 
											'url' => "?mod=categories", 
											'mod' => "categories", 
											'access' => $user_group[$member_id['user_group']]['admin_categories'] 
								), 
								
								array (
											'name' => $lang['opt_db'], 
											'url' => "?mod=dboption", 
											'mod' => "dboption", 
											'access' => "admin" 
								), 

								array (
											'name' => $lang['opt_vconf'], 
											'url' => "?mod=videoconfig", 
											'mod' => "videoconfig", 
											'access' => "admin" 
								),
								
								array (
											'name' => $lang['opt_xfil'], 
											'url' => "?mod=xfields&xfieldsaction=configure", 
											'mod' => "xfields", 
											'access' => $user_group[$member_id['user_group']]['admin_xfields'] 
								),

								array (
											'name' => $lang['opt_question'], 
											'url' => "?mod=question", 
											'mod' => "question", 
											'access' => "admin" 
								)
	);
	
	$options['user'] = array (
							
							array (
										'name' => $lang['opt_user'], 
										'url' => "?mod=editusers&action=list", 
										'mod' => "editusers", 
										'access' => $user_group[$member_id['user_group']]['admin_editusers'] 
							), 
							
							array (
										'name' => $lang['opt_xprof'], 
										'url' => "?mod=userfields&xfieldsaction=configure", 
										'mod' => "userfields", 
										'access' => $user_group[$member_id['user_group']]['admin_userfields'] 
							), 
							
							array (
										'name' => $lang['opt_group'], 
										'url' => "?mod=usergroup", 
										'mod' => "usergroup", 
										'access' => "admin" 
							),
							array (
										'name' => $lang['opt_social'], 
										'url' => "?mod=social", 
										'mod' => "social", 
										'access' => "admin"
							)
	);
	
	$options['templates'] = array (
									
									array (
											'name' => $lang['opt_t'], 
											'url' => "?mod=templates&user_hash=" . $dle_login_hash, 
											'mod' => "templates", 
											'access' => "admin" 
									), 
									
									array (
											'name' => $lang['opt_email'], 
											'url' => "?mod=email", 
											'mod' => "email", 
											'access' => "admin" 
									) 
	);

	
	
	$options['filter'] = array (
								
								array (
											'name' => $lang['opt_fil'], 
											'url' => "?mod=wordfilter", 
											'mod' => "wordfilter", 
											'access' => $user_group[$member_id['user_group']]['admin_wordfilter'] 
								), 
								
								array (
											'name' => $lang['opt_ipban'], 
											'url' => "?mod=blockip", 
											'mod' => "blockip", 
											'access' => $user_group[$member_id['user_group']]['admin_blockip'] 
								), 
								
								array (
											'name' => $lang['opt_iptools'], 
											'url' => "?mod=iptools", 
											'mod' => "iptools", 
											'access' => $user_group[$member_id['user_group']]['admin_iptools'] 
								), 
								array (
											'name' => $lang['opt_sfind'], 
											'url' => "?mod=search", 
											'mod' => "search", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_srebuild'], 
											'url' => "?mod=rebuild", 
											'mod' => "rebuild", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_complaint'], 
											'url' => "?mod=complaint", 
											'mod' => "complaint",  
											'access' => $user_group[$member_id['user_group']]['admin_complaint'] 
								),
								array (
											'name' => $lang['opt_check'], 
											'url' => "?mod=check", 
											'mod' => "check",
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_links'], 
											'url' => "?mod=links", 
											'mod' => "links",
											'access' => "admin" 
								)
	);

	
	
	$options['others'] = array (
								array (
											'name' => $lang['opt_rules'], 
											'url' => "?mod=static&action=doedit&page=rules", 
											'mod' => "rules",
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_static'], 
											'url' => "?mod=static", 
											'mod' => "static",
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_clean'], 
											'url' => "?mod=clean", 
											'mod' => "clean",
											'access' => "admin" 
								), 								
								
								array (
											'name' => $lang['main_newsl'], 
											'url' => "?mod=newsletter", 
											'mod' => "newsletter",
											'access' => $user_group[$member_id['user_group']]['admin_newsletter'] 
								), 
								array (
											'name' => $lang['opt_vote'], 
											'url' => "?mod=editvote", 
											'mod' => "editvote",
											'access' => $user_group[$member_id['user_group']]['admin_editvote'] 
								), 
								
								array (
											'name' => $lang['opt_img'], 
											'url' => "?mod=files", 
											'mod' => "files",
											'access' => "admin" 
								), 
								
								array (
											'name' => $lang['opt_banner'], 
											'url' => "?mod=banners&action=list", 
											'mod' => "banners",
											'access' => $user_group[$member_id['user_group']]['admin_banners'] 
								), 
								array (
											'name' => $lang['opt_google'], 
											'url' => "?mod=googlemap", 
											'mod' => "googlemap",
											'access' => $user_group[$member_id['user_group']]['admin_googlemap'] 
								),
								array (
											'name' => $lang['opt_rss'], 
											'url' => "?mod=rss", 
											'mod' => "rss",
											'access' => $user_group[$member_id['user_group']]['admin_rss'] 
								), 
								array (
											'name' => $lang['opt_rssinform'], 
											'url' => "?mod=rssinform", 
											'mod' => "rssinform",
											'access' => $user_group[$member_id['user_group']]['admin_rssinform'] 
								),
								array (
											'name' => $lang['opt_tagscloud'], 
											'url' => "?mod=tagscloud", 
											'mod' => "tagscloud",
											'access' => $user_group[$member_id['user_group']]['admin_tagscloud'] 
								),

								array (
											'name' => $lang['opt_logs'], 
											'url' => "?mod=logs", 
											'mod' => "logs",
											'access' => "admin" 
								),
	);


	$db->query( "SELECT * FROM " . PREFIX . "_admin_sections" );

	while ( $row = $db->get_array() ) {

		if ($row['allow_groups'] != "all") {

			$groups = explode(",", $row['allow_groups']);

			if ( !in_array($member_id['user_group'], $groups) AND $member_id['user_group'] !=1 ) continue;

		}

		$row['name'] = totranslit($row['name'], true, false);
		$row['title'] = strip_tags(stripslashes($row['title']));

		$options['admin_sections'][] = array (
											'name' => $row['title'], 
											'url' => "?mod={$row['name']}", 
											'mod' => "{$row['name']}",
											'access' => 1 
										);

	}


	foreach ( $options as $sub_options => $value ) {
		$count_options = count( $value );
		
		for($i = 0; $i < $count_options; $i ++) {

			if ($member_id['user_group'] == 1 ) continue;

			if ($member_id['user_group'] != 1 AND  $value[$i]['access'] == "admin") unset( $options[$sub_options][$i] );

			if ( !$value[$i]['access'] ) unset( $options[$sub_options][$i] );
		}
	}
	
	$subs = 0;
	$sidebar= "";
	$menu_item = array();
		
	foreach ( $options as $sub_options ) {
	
		$menu_item_header = $lang['opt_hopt'];
		$icon= "wrench";
		if( $subs == 1 ) { $menu_item_header = $lang['opt_s_acc']; $icon= "user";}
		if( $subs == 2 ) { $menu_item_header = $lang['opt_s_tem']; $icon= "laptop";}
		if( $subs == 3 ) { $menu_item_header = $lang['opt_s_fil']; $icon= "leaf";}
		if( $subs == 4 ) { $menu_item_header = $lang['opt_s_oth']; $icon= "link";}
		if( $subs == 5 ) { $menu_item_header = $lang['admin_other_section']; $icon= "list-alt";}
		
		$subs ++;
		
		if( !count( $sub_options ) ) continue;
		
		$submenu_item = array();
		$active_menu = "";
		$collapsed = "";
		foreach ( $sub_options as $option ) {
		
			if ($mod == $option['mod']) {
				$active_submenu = "active";
				$active_menu = " active";
				$collapsed = " in";
			} else $active_submenu = "";
		
			if ($mod == "options" AND $action != "syscon") {
				$active_submenu = "";
				$active_menu = "";
				$collapsed = "";
			}
			if ($mod == "static" AND $_GET['page'] == "rules") {
				$active_submenu = "";
				$active_menu = "";
				$collapsed = "";
			}			
			$submenu_item[] = "<li class=\"{$active_submenu}\"><a href=\"{$option['url']}\">{$option['name']}</a></li>";
		}
		
		$submenu_item = implode("", $submenu_item);
	
		$menu_item[] = "<li class=\"dark-nav{$active_menu}\"><span class=\"glow\"></span><a class=\"accordion-toggle\" data-toggle=\"collapse\" href=\"#submenu{$subs}\"><i class=\"icon-{$icon} icon-2x\"></i><span>{$menu_item_header} <i class=\"icon-caret-down\"></i></span></a><ul id=\"submenu{$subs}\" class=\"collapse{$collapsed}\">".$submenu_item."</ul></li>";	
	}
	
	if( count( $menu_item ) ) $sidebar= "<ul class=\"nav navbar-collapse collapse navbar-collapse-primary\"><li><span class=\"glow\"></span><a href=\"?mod=options&action=options\"><i class=\"icon-globe icon-2x\"></i><span>{$lang['header_all']}</span></a></li>".implode("", $menu_item)."</ul>";
	else $sidebar= "<ul class=\"nav navbar-collapse collapse navbar-collapse-primary\"><li><span class=\"glow\"></span><a href=\"?mod=options&action=options\"><i class=\"icon-globe icon-2x\"></i><span>{$lang['header_all']}</span></a></li></ul>";
	
} else $sidebar= "";

if( @file_exists( ROOT_DIR . '/templates/'. $config['skin'].'/adminpanel.css' ) ) {
	
		$custom_css = "<link href=\"templates/{$config['skin']}/adminpanel.css\" rel=\"stylesheet\" type=\"text/css\" />";
		
} else $custom_css = "";

$skin_header = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="{$config['charset']}">
  <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <title>Webman - {$lang['skin_title']}</title>

  <link rel="stylesheet" href="engine/skins/styles/d6220a84.bootstrap.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/select2/select2.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/datatables.css" media="screen" />
  <link rel="stylesheet" href="engine/skins/styles/1b2c4b33.proton.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/animate.css">
  <link rel="stylesheet" href="engine/skins/styles/engine.css">

  <link rel="stylesheet" href="engine/skins/styles/9a41946e.font-awesome.css" type="text/css" />
  <link rel="stylesheet" href="engine/skins/styles/4d9a7458.font-titillium.css" type="text/css" />
  <script src="engine/skins/scripts/vendor/modernizr.js"></script>

  <link href="engine/skins/stylesheets/application.css" rel="stylesheet" type="text/css" />
  {$custom_css}
  {js_files}
</head>
<body>
<script type="text/javascript">
<!--
var dle_act_lang   = ["{$lang['p_yes']}", "{$lang['p_no']}", "{$lang['p_enter']}", "{$lang['p_cancel']}", "{$lang['media_upload']}"];
var cal_language   = {en:{months:['{$lang['January']}','{$lang['February']}','{$lang['March']}','{$lang['April']}','{$lang['May']}','{$lang['June']}','{$lang['July']}','{$lang['August']}','{$lang['September']}','{$lang['October']}','{$lang['November']}','{$lang['December']}'],dayOfWeek:["{$langdate['Sun']}", "{$langdate['Mon']}", "{$langdate['Tue']}", "{$langdate['Wed']}", "{$langdate['Thu']}", "{$langdate['Fri']}", "{$langdate['Sat']}"]}};
//-->
</script>
<div id="loading-layer">{$lang['ajax_info']}</div>


<nav class="main-menu">
            <ul>
                <li>
                    <a href="$PHP_SELF">
                        <img src="engine/skins/images/logo.png" alt="proton-logo" style="width: 30px; margin: 15px;">
                        <span class="nav-text">
                           <b>ВЭБ УДИРДАХ СИСТЕМ</b>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="admin.php">
                        <i class="icon-home nav-icon"></i>
                        <span class="nav-text">
                            НҮҮР ХУУДАС
                        </span>
                    </a>
                </li>
                <li class="has-subnav">
                    <a href="javascript:;">
                        <i class="icon-phone nav-icon"></i>
                        <span class="nav-text">
                            МЭДЭЭЛЭЛ
                        </span>
                        <i class="icon-angle-right"></i>
                    </a>
                    <ul>
                        <li>
                            <a class="subnav-text" href="{$PHP_SELF}?mod=categories">
                                МЭДЭЭНИЙ АНГИЛАЛ
                            </a>
                        </li>
                        <li>
                            <a class="subnav-text" href="{$PHP_SELF}?mod=xfields&xfieldsaction=configure">
                                НЭМЭЛТ ТАЛБАР
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="has-subnav">
                    <a href="javascript:;">
                        <i class="icon-user nav-icon"></i>
                        <span class="nav-text">
                            ХЭРЭГЛЭГЧ
                        </span>
                        <i class="icon-angle-right"></i>
                    </a>
                    <ul>
                        <li>
                            <a class="subnav-text" href="{$PHP_SELF}?mod=editusers&action=list">
                                НИЙТ ХЭРЭГЛЭГЧ
                            </a>
                        </li>
                        <li>
                            <a class="subnav-text" href="{$PHP_SELF}?mod=usergroup">
                                ХЭРЭГЛЭГЧ ГИШҮҮНЧЛЭЛ
                            </a>
                        </li>
                    </ul>
                </li>
               
               
               <li>
                    <a href="{$PHP_SELF}?mod=templates&user_hash=">
                        <i class="icon-html5 nav-icon"></i>
                        <span class="nav-text">
                            ДИЗАЙН ЗАСВАР
                        </span>
                    </a>
                </li>
               
               <li>
                    <a href="{$PHP_SELF}?mod=newsletter">
                        <i class="icon-envelope-alt nav-icon"></i>
                        <span class="nav-text">
                            И-МЭЙЛ МАРКЕТИНГ
                        </span>
                    </a>
                </li>
               
               <li>
                    <a href="{$PHP_SELF}?mod=editvote">
                        <i class="icon-signal nav-icon"></i>
                        <span class="nav-text">
                            САНАЛ АСУУЛГА
                        </span>
                    </a>
                </li>
               <li>
                    <a href="{$PHP_SELF}?mod=static">
                        <i class="icon-print nav-icon"></i>
                        <span class="nav-text">
                            НЭМЭЛТ ХУУДАС
                        </span>
                    </a>
                </li>
               <li>
                    <a href="{$PHP_SELF}?mod=banners">
                        <i class="icon-bullhorn nav-icon"></i>
                        <span class="nav-text">
                            СУРТАЛЧИЛГАА
                        </span>
                    </a>
                </li>
               <li>
                    <a href="{$PHP_SELF}?mod=options">
                        <i class="icon-tasks nav-icon"></i>
                        <span class="nav-text">
                            БУСАД СУУЛГАЦ
                        </span>
                    </a>
                </li>
            </ul>

            <ul class="logout">
                <li>
                    <a href="$PHP_SELF?action=logout">
                        <i class="icon-off nav-icon"></i>
                        <span class="nav-text">
                            СИСТЕМЭЭС ГАРАХ
                        </span>
                    </a>
                </li>  
            </ul>
        </nav>



<section class="wrapper retracted scrollable">
            <div class="row">
                <div class="col-md-12">
				
				
				<section class="panel stat stat-danger stat-color" style="margin-top: 10px;">
                         <div class="panel-heading"><div>
							<i><img src="engine/skins/avatar.png" width="40px" style="margin-top: -17px;"></i>
                                <h2 style="text-transform: uppercase;">
								     <b><span style="color:red">{$user_group[$member_id['user_group']]['group_name']}</span></b>
									 &nbsp;:&nbsp;{$member_id['name']}                               
							     </h2>
                            <div class="counter counter-small">
								<a href="$PHP_SELF?mod=addnews&amp;action=addnews"><button type="button" class="btn btn-sm btn-success">
								<i class="icon-plus" style="color:#fff;"></i>&nbsp; МЭДЭЭ НЭМЭХ</button>
								</a>
								
								<a href="$PHP_SELF?mod=editnews&amp;action=list"><button type="button" class="btn btn-sm btn-warning">
								<i class="icon-folder-close" style="color:#fff;"></i>&nbsp; МЭДЭЭ ЗАСАХ</button>
								</a>	
								
								<a href="{$config['http_home_url']}" target="_blank"><button type="button" class="btn btn-sm btn-success">
								<i class="icon-desktop" style="color:#fff;"></i>&nbsp; ВЭБ САЙТ</button>
								</a>								
								
							</div>
				</div>
                 </section>
					
				
					
			     <div class="panel panel-default panel-block" style="margin-top: 15px;">
	             <!-- maincontent beginn -->

HTML;

$skin_footer = <<<HTML

<!-- maincontent end -->
		            </div>
					
					
					
                </div>
            </div>
        </section>



<script src="engine/skins/scripts/3fa227ae.proton.js"></script>
</body>
</html>
HTML;

$skin_login = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="{$config['charset']}">
  <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <title>Webman - {$lang['skin_title']}</title>

  <link rel="stylesheet" href="engine/skins/styles/d6220a84.bootstrap.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/select2/select2.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/datatables.css" media="screen" />
  <link rel="stylesheet" href="engine/skins/styles/1b2c4b33.proton.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/animate.css">
  <link rel="stylesheet" href="engine/skins/styles/engine.css">

  <link rel="stylesheet" href="engine/skins/styles/9a41946e.font-awesome.css" type="text/css" />
  <link rel="stylesheet" href="engine/skins/styles/4d9a7458.font-titillium.css" type="text/css" />
  <script src="engine/skins/scripts/vendor/modernizr.js"></script>

  <link href="engine/skins/stylesheets/application.css" rel="stylesheet" type="text/css" />
  {$custom_css}
  <script type="text/javascript" src="engine/skins/javascripts/application.js"></script>
<style type="text/css">
div.selector {
  width: 100%;
  height: 38px;
  margin-left: 2px;
}
div.selector:after {
    top: 6px;
}
div.selector span {
    padding: 0;	
    padding-left: 40px;
    height: 36px;
    line-height: 36px;
}
body {
	background: url("engine/skins/images/bg.png");

}
.box {
	margin-bottom: 5px;
}
label {
    margin-bottom:0px;
}

</style>
</head>
<body>

<div class="login-page">
		<form  name="login" action="" method="post">
		<input type="hidden" name="subaction" value="dologin">
            <section class="wrapper scrollable animated fadeInDown">
                <section class="panel panel-default">
                    <div class="panel-heading">
                        <div>
                            <h1>
                                <span class="title">
								   Тагтаа
                                </span>
                                <span class="subtitle">
                                    ВЭБ УДИРДАХ СИСТЕМ
                                </span>
                            </h1>
                        </div>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <span class="welcome-text">
                                ЗӨВХӨН ЗӨВШӨӨРӨГДСӨН 
                            </span>
                            <span class="member">
                                ХЭРЭГЛЭГЧ НЭВТРЭХ БОЛОМЖТОЙ
                            </span>
                        </li>
                        <li class="list-group-item">
                            <div class="form-group">
                                <input type="text" name="username" class="form-control input-lg" placeholder="НЭВТРЭХ НЭР">
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control input-lg" placeholder="НУУЦ ҮГ">
                            </div>
                        </li>
                    </ul>
                    <div class="panel-footer">
					<button class="btn btn-lg btn-success" onclick="submit();" type="submit" title="НЭВТРЭХ">НЭВТРЭХ</button>
		<input name="login" type="hidden" id="login" value="submit">
                    </div>
                </section>
            </section>
        </form>
		</div>
</body>
</html>
HTML;


$skin_not_logged_header = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="{$config['charset']}">
  <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <title>Webman - {$lang['skin_title']}</title>

  <link rel="stylesheet" href="engine/skins/styles/d6220a84.bootstrap.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/select2/select2.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/datatables.css" media="screen" />
  <link rel="stylesheet" href="engine/skins/styles/1b2c4b33.proton.css">
  <link rel="stylesheet" href="engine/skins/styles/vendor/animate.css">
  <link rel="stylesheet" href="engine/skins/styles/engine.css">

  <link rel="stylesheet" href="engine/skins/styles/9a41946e.font-awesome.css" type="text/css" />
  <link rel="stylesheet" href="engine/skins/styles/4d9a7458.font-titillium.css" type="text/css" />
  <script src="engine/skins/scripts/vendor/modernizr.js"></script>

  <link href="engine/skins/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css" />
  {$custom_css}
<style type="text/css">
body {
	background: url("engine/skins/images/bg.png");

}
.box {
	margin-bottom: 5px;
}
</style>
</head>
<body>
<script language="javascript" type="text/javascript">
<!--
var dle_act_lang   = [];
var cal_language   = {en:{months:[],dayOfWeek:[]}};
//-->
</script>

<div class="container">
  <div class="col-md-8 col-md-offset-2">
    <div class="padded">
<!--MAIN area-->
HTML;

?>