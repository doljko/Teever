<?PHP
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
 Файл: main.php
-----------------------------------------------------
 Назначение: Статистика и автопроверка
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

echoheader( "<i class=\"icon-home\"></i>".$lang['header_m_title'], $lang['header_m_subtitle'] );

$config['max_users_day'] = intval( $config['max_users_day'] );

$maxmemory = (@ini_get( 'memory_limit' ) != '') ? @ini_get( 'memory_limit' ) : $lang['undefined'];
$disabledfunctions = (strlen( ini_get( 'disable_functions' ) ) > 1) ? @ini_get( 'disable_functions' ) : $lang['undefined'];
$disabledfunctions = str_replace( ",", ", ", $disabledfunctions );
$safemode = (@ini_get( 'safe_mode' ) == 1) ? $lang['safe_mode_on'] : $lang['safe_mode_off'];
$licence = ($lic_tr) ? $lang['licence_trial'] : $lang['licence_full'];
$offline = (!$config['site_offline']) ? $lang['safe_mode_on'] : "<font color=\"red\">" . $lang['safe_mode_off'] . "</font>";

if( function_exists( 'apache_get_modules' ) ) {
	if( array_search( 'mod_rewrite', apache_get_modules() ) ) {
		$mod_rewrite = $lang['safe_mode_on'];
	} else {
		$mod_rewrite = "<font color=\"red\">" . $lang['safe_mode_off'] . "</font>";
	}
} else {
	$mod_rewrite = $lang['undefined'];
}

$os_version = @php_uname( "s" ) . " " . @php_uname( "r" );
$phpv = phpversion();

if ( function_exists( 'gd_info' ) ) {

	$array=gd_info ();
	$gdversion = "";

	foreach ($array as $key=>$val) {
	  
	  if ($val===true) {
	    $val="Enabled";
	  }
	
	  if ($val===false) {
	    $val="Disabled";
	  }
	
	  $gdversion .= $key.":&nbsp;{$val}, ";
	
	}

} else $gdversion = $lang['undefined'];


$maxupload = str_replace( array ('M', 'm' ), '', @ini_get( 'upload_max_filesize' ) );
$maxupload = formatsize( $maxupload * 1024 * 1024 );
$stats_arr = array();

if ( $config['allow_cache'] AND !$config['cache_type'] ) {

	$stats_cache = @file_get_contents( ENGINE_DIR . "/cache/news_adminstats.tmp" );
	if ( $stats_cache !== false ) $stats_arr = unserialize($stats_cache);
}

if ( !count($stats_arr) ) {

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );
	$stats_arr['stats_news'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_subscribe" );
	$stats_arr['count_subscribe'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments" );
	$stats_arr['count_comments'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE approve ='0'" );
	$stats_arr['count_c_app'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users" );
	$stats_arr['stats_users'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users where banned='yes'" );
	$stats_arr['stats_banned'] = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post where approve = '0'" );
	$stats_arr['approve']  = $row['count'];
	
	
	$db->query( "SHOW TABLE STATUS FROM `" . DBNAME . "`" );
	$mysql_size = 0;
	while ( $r = $db->get_array() ) {
		if( strpos( $r['Name'], PREFIX . "_" ) !== false ) $mysql_size += $r['Data_length'] + $r['Index_length'];
	}
	$db->free();
	
	$stats_arr['mysql_size'] = formatsize( $mysql_size );

	if ( $config['allow_cache'] AND !$config['cache_type'] ) {
		file_put_contents (ENGINE_DIR . "/cache/news_adminstats.tmp", serialize( $stats_arr ), LOCK_EX);
		@chmod( ENGINE_DIR . "/cache/news_adminstats.tmp", 0666 );
	}

}

if( $stats_arr['count_c_app'] ) {
	
	$stats_arr['count_c_app'] = $stats_arr['count_c_app'] . " [ <a class=\"status-info\" href=\"?mod=cmoderation\">{$lang['stat_cmod_link']}</a> ]";

}

if( $stats_arr['approve'] and $user_group[$member_id['user_group']]['allow_all_edit'] ) {
	
	$stats_arr['approve'] = $stats_arr['approve'] . " [ <a class=\"status-info\" href=\"?mod=editnews&action=list&news_status=2\">{$lang['stat_medit_link']}</a> ]";

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint" );
$c_complaint = $row['count'];
set_cookie ( "dle_compl", $row['count'], 365 );

if( $c_complaint AND $user_group[$member_id['user_group']]['admin_complaint'] ) {

	$c_complaint = $row['count'] . " [ <a class=\"status-info\" href=\"?mod=complaint\">{$lang['stat_complaint_1']}</a> ]";

	if ($row['count'] > intval ( $_COOKIE['dle_compl'] )) {

		$c_complaint .= <<<HTML
<script language="javascript" type="text/javascript">
<!--

$(function(){
	setTimeout(function() {
		Growl.info({
			title: '{$lang[p_info]}',
			text: '{$lang['opt_complaint_20']}'
		  });
	}, 1000);
});

//-->
</script>
HTML;

	}


}

function dirsize($directory) {
	
	if( ! is_dir( $directory ) ) return - 1;
	
	$size = 0;
	
	if( $DIR = opendir( $directory ) ) {
		
		while ( ($dirfile = readdir( $DIR )) !== false ) {
			
			if( @is_link( $directory . '/' . $dirfile ) || $dirfile == '.' || $dirfile == '..' ) continue;
			
			if( @is_file( $directory . '/' . $dirfile ) ) $size += filesize( $directory . '/' . $dirfile );
			
			else if( @is_dir( $directory . '/' . $dirfile ) ) {
				
				$dirSize = dirsize( $directory . '/' . $dirfile );
				if( $dirSize >= 0 ) $size += $dirSize;
				else return - 1;
			
			}
		
		}
		
		closedir( $DIR );
	
	}
	
	return $size;

}

$cache_size = formatsize( dirsize( "engine/cache" ) );

$dfs = @disk_free_space( "." );
$freespace = formatsize( $dfs );

if( $user_group[$member_id['user_group']]['admin_comments'] ) {
	$edit_comments = "&nbsp;[ <a class=\"status-info\" href=\"?mod=comments&action=edit\">{$lang['edit_comm']}</a> ]";
} else $edit_comments = "";

if( $member_id['user_group'] == 1 ) {

	if( $lic_tr ) {
		
		echo $activation_field;

	}
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['main_quick']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">	
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/uset.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=editusers&action=list">{$lang['opt_user']}</a></div>
			<div class="news-text">
			  <a href="?mod=editusers&action=list">{$lang['opt_userc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/rkl.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=banners">{$lang['opt_banner']}</a></div>
			<div class="news-text">
			  <a href="?mod=banners">{$lang['opt_bannerc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	</div>

	<div class="row box-section">
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/tools.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=options&action=syscon">{$lang['opt_all']}</a></div>
			<div class="news-text">
			  <a href="?mod=options&action=syscon">{$lang['opt_allc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/nset.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=newsletter">{$lang['main_newsl']}</a></div>
			<div class="news-text">
			  <a href="?mod=newsletter">{$lang['main_newslc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	</div>	

	<div class="row box-section">
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/spset.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=static">{$lang['opt_static']}</a></div>
			<div class="news-text">
			  <a href="?mod=static">{$lang['opt_staticd']}</a>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/clean.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=clean">{$lang['opt_clean']}</a></div>
			<div class="news-text">
			  <a href="?mod=clean">{$lang['opt_cleanc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	</div>	
	
	<div class="row box-section">
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/shield.png"></div>
		  <div class="news-content">
			<div class="news-title"><a onclick="check_files('lokal'); return false;" href="#">{$lang['mod_anti']}</a></div>
			<div class="news-text">
			  <a onclick="check_files('lokal'); return false;" href="#">{$lang['anti_descr']}</a>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/next.png"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod=options&action=options">{$lang['opt_all_rublik']}</a></div>
			<div class="news-text">
			  <a href="?mod=options&action=options">{$lang['opt_all_rublikc']}</a>
			</div>
		  </div>
		</div>
	  </div>
	</div>	

  </div>
</div>
<script language="javascript" type="text/javascript">
<!--
		function check_files ( folder ){

			if (folder == "snap") {

				DLEconfirm( '{$lang['anti_snapalert']}', '{$lang['p_confirm']}', function () {

					$('#antivirus').html('<div class="row box-section">{$lang['anti_box']}</div>');

					ShowLoading('');		
					$.post('engine/ajax/antivirus.php', { folder: folder, key: "{$config['key']}" }, function(data){
				
						HideLoading('');
				
						$('#antivirus').html(data);
				
					});

				} );

			} else {

				$('#antivirusbox').show();
				$('#antivirus').html('<div class="row box-section">{$lang['anti_box']}</div>');
				
				ShowLoading('');		
				$.post('engine/ajax/antivirus.php', { folder: folder, key: "{$config['key']}" }, function(data){
				
					HideLoading('');
				
					$('#antivirus').html(data);
				
				});

			}

			return false;
		}
		
		$(function(){

			$.ajaxSetup({
				cache: false
			});

			$('#clearbutton').click(function() {

				$.get("engine/ajax/adminfunction.php?action=clearcache", function( data ){

					$('#cachesize').html('0 b');
					Growl.info({
						title: '{$lang[p_info]}',
						text: data
					});

				});
				return false;
			});

			$('#clearsubscribe').click(function() {

			    DLEconfirm( '{$lang['confirm_action']}', '{$lang['p_confirm']}', function () {

					$.get("engine/ajax/adminfunction.php?action=clearsubscribe", function( data ){
						Growl.info({
							title: '{$lang[p_info]}',
							text: data
						});
					});
				} );
				return false;
			});

			$('#check_updates').click(function() {

				$('#main_box').html('{$lang['dle_updatebox']}');
				$.get("engine/ajax/updates.php?versionid={$config['version_id']}", function( data ){
					$('#main_box').html(data);
				});
				return false;
			});

			$('#send_notice').click(function() {

				$('#send_result').html('{$lang['dle_updatebox']}');
				var notice = $('#notice').val();
				$.post("engine/ajax/adminfunction.php?action=sendnotice", { notice: notice } , function( data ){
					$('#send_result').append('&nbsp;' + data);
				});
				return false;
			});

		});
//-->
</script>
<div id="antivirusbox" class="box" style="display:none;">
  <div class="box-header">
    <div class="title">{$lang['anti_title']}</div>
  </div>
  <div id="antivirus" class="box-content">
  <div class="row box-section">{$lang['anti_box']}</div>
  </div>
</div>

<div class="row">
	<div class="col-md-12">
		
		<div class="box">
		
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#statall" data-toggle="tab"><i class="icon-bar-chart"></i> {$lang['stat_all']}</a></li>
					<li><a href="#notinfo" data-toggle="tab"><i class="icon-edit"></i> {$lang['main_notice']}</a></li>
					<li><a href="#statauto" data-toggle="tab"><i class="icon-cog"></i> {$lang['stat_auto']}</a></li>
				</ul>
			</div>
		
            <div class="box-content">
                 <div class="tab-content">
                     <div class="tab-pane active" id="statall">
						<div class="row box-section">
							<div class="col-md-3">{$lang['site_status']}</div>
							<div class="col-md-9">{$offline}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_allnews']}</div>
							<div class="col-md-9">{$stats_arr['stats_news']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_mod']}</div>
							<div class="col-md-9">{$stats_arr['approve']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_complaint']}</div>
							<div class="col-md-9">{$c_complaint}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_comments']}</div>
							<div class="col-md-9">{$stats_arr['count_comments']} [ <a class="status-info" href="{$config['http_home_url']}index.php?do=lastcomments" target="_blank">{$lang['last_comm']}</a> ]{$edit_comments}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_cmod']}</div>
							<div class="col-md-9">{$stats_arr['count_c_app']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_users']}</div>
							<div class="col-md-9">{$stats_arr['stats_users']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_banned']}</div>
							<div class="col-md-9"><font color="red">{$stats_arr['stats_banned']}</font></div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_bd']}</div>
							<div class="col-md-9">{$stats_arr['mysql_size']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['cache_size']}</div>
							<div class="col-md-9"><span id="cachesize">{$cache_size}</span></div>
						</div>
						<div class="row box-section">
							<div class="col-md-12">
HTML;

	echo "<button id=\"check_updates\" name=\"check_updates\" class=\"btn btn-gray\"><i class=\"icon-exclamation-sign\"></i> {$lang['dle_udate']}</button>&nbsp;<button id=\"clearbutton\" name=\"clearbutton\" class=\"btn btn-red\"><i class=\"icon-trash\"></i> {$lang['btn_clearcache']}</button>";

	if ($stats_arr['count_subscribe']) echo "&nbsp;<button id=\"clearsubscribe\" name=\"clearsubscribe\" class=\"btn btn-gold\"><i class=\"icon-user\"></i> {$lang['btn_clearsubscribe']}</button>";

	echo "<br /><br /><div id=\"main_box\"></div>";

	$row = $db->super_query( "SELECT notice FROM " . PREFIX . "_notice WHERE user_id = '{$member_id['user_id']}'" );

	if( $row['notice'] == "" ) {
		
		$row['notice'] = $lang['main_no_notice'];

	} else {
		
		$row['notice'] = htmlspecialchars( $row['notice'], ENT_QUOTES, $config['charset'] );

	}

echo <<<HTML
                        </div>
						</div>
					</div>
                     <div class="tab-pane" id="notinfo" >
						<div class="row box-section">
							<textarea id="notice" name="notice" style="width:100%;height:200px;background-color:lightyellow;">{$row['notice']}</textarea>							
						</div>
						<div class="row box-section"><button id="send_notice" name="send_notice" class="btn btn-green"><i class="icon-ok"></i> {$lang['btn_send']}</button>&nbsp;&nbsp;<span id="send_result"></span></div>						
                     </div>
                     <div class="tab-pane" id="statauto" >
<table class="table table-normal">
    <tr>
        <td class="col-md-3 white-line">{$lang['dle_version']}</td>
        <td class="col-md-9 white-line">{$config['version_id']}</td>
    </tr>
    <tr>
        <td>{$lang['licence_info']}</td>
        <td>{$licence}</td>
    </tr>
    <tr>
        <td>{$lang['stat_os']}</td>
        <td>{$os_version}</td>
    </tr>
    <tr>
        <td>{$lang['stat_php']}</td>
        <td>{$phpv}</td>
    </tr>
    <tr>
        <td>{$lang['stat_mysql']}</td>
        <td>{$db->mysql_version} <b>{$db->mysql_extend}</b></td>
    </tr>
    <tr>
        <td>{$lang['stat_gd']}</td>
        <td>{$gdversion}</td>
    </tr>
    <tr>
        <td>Module mod_rewrite</td>
        <td>{$mod_rewrite}</td>
    </tr>
    <tr>
        <td>{$lang['stat_safemode']}</td>
        <td>{$safemode}</td>
    </tr>
    <tr>
        <td>{$lang['stat_maxmem']}</td>
        <td>{$maxmemory}</td>
    </tr>
    <tr>
        <td>{$lang['stat_func']}</td>
        <td>{$disabledfunctions}</td>
    </tr>
    <tr>
        <td>{$lang['stat_maxfile']}</td>
        <td>{$maxupload}</td>
    </tr>
    <tr>
        <td>{$lang['free_size']}</td>
        <td>{$freespace}</td>
    </tr>
</table>      
                     </div>
                 </div>
             </div>
			 
	     </div>
HTML;

	if( !is_writable( ENGINE_DIR . "/cache/" ) OR !is_writable( ENGINE_DIR . "/cache/system/" ) ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_cache']}</div>";
	
	}
	
	if( @file_exists( "install.php" ) ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_install']}</div>";
	}
	if( $dfs and $dfs < 20240 ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_nofree']}</div>";
	}
	
	if (!defined( 'SECURE_AUTH_KEY' ) OR strlen(SECURE_AUTH_KEY) < 20 ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_sec_auth']}</div>";
	}
	if( !@extension_loaded('xml') ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_not_min']}</div>";
	}

	if( !@extension_loaded('zlib') ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_not_min']}</div>";
	}
	if( preg_match('/1|yes|on|true/i', ini_get('register_globals')) ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_secfault']}</div>";
	}
	
	if( preg_match('/1|yes|on|true/i', ini_get('allow_url_include')) ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_secfault_3']}</div>";
	}
	if( version_compare($phpv, '5.3', '<') ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_phperror']}</div>";
	}

	if( $config['cache_type'] && !$mcache ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_m_fail']}</div>";
	}
	
	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) {
		echo "<div class=\"alert alert-error\">{$lang['stat_magic']}</div>";
	}

	$check_files       = array(
		"/templates/.htaccess",
		"/uploads/.htaccess",
		"/uploads/files/.htaccess",
		"/engine/data/.htaccess",
		"/engine/cache/.htaccess",
		"/engine/cache/system/.htaccess",
	);

	foreach ($check_files as $file) {

		if( !file_exists( ROOT_DIR .$file ) ) {
			echo "<div class=\"alert alert-error\">".str_replace("{folder}", $file, $lang['stat_secfault_2'])."</div>";
		}

	}
	
echo <<<HTML
</div>
HTML;

} else {

	$row = $db->super_query( "SELECT notice FROM " . PREFIX . "_notice WHERE user_id = '{$member_id['user_id']}'" );

	if( $row['notice'] == "" ) {
		
		$row['notice'] = $lang['main_no_notice'];

	} else {
		
		$row['notice'] = htmlspecialchars( stripslashes( $row['notice'] ), ENT_QUOTES, $config['charset'] );

	}

echo <<<HTML
<div class="box">
		
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#statall" data-toggle="tab"><i class="icon-bar-chart"></i> {$lang['stat_all']}</a></li>
					<li><a href="#notinfo" data-toggle="tab"><i class="icon-edit"></i> {$lang['main_notice']}</a></li>
				</ul>
			</div>
			
            <div class="box-content">
                 <div class="tab-content">
                     <div class="tab-pane active" id="statall">
						<div class="row box-section">
							<div class="col-md-3">{$lang['site_status']}</div>
							<div class="col-md-9">{$offline}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_allnews']}</div>
							<div class="col-md-9">{$stats_arr['stats_news']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_mod']}</div>
							<div class="col-md-9">{$stats_arr['approve']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_complaint']}</div>
							<div class="col-md-9">{$c_complaint}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_comments']}</div>
							<div class="col-md-9">{$stats_arr['count_comments']} [ <a href="{$config['http_home_url']}index.php?do=lastcomments" target="_blank">{$lang['last_comm']}</a> ]{$edit_comments}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_cmod']}</div>
							<div class="col-md-9">{$stats_arr['count_c_app']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_users']}</div>
							<div class="col-md-9">{$stats_arr['stats_users']}</div>
						</div>
						<div class="row box-section">
							<div class="col-md-3">{$lang['stat_banned']}</div>
							<div class="col-md-9"><font color="red">{$stats_arr['stats_banned']}</font></div>
						</div>
					</div>
					
                     <div class="tab-pane" id="notinfo" >
						<div class="row box-section">
							<textarea id="notice" name="notice" style="width:100%;height:200px;background-color:lightyellow;">{$row['notice']}</textarea>							
						</div>
						<div class="row box-section"><button id="send_notice" name="send_notice" class="btn btn-green"><i class="icon-ok"></i> {$lang['btn_send']}</button>&nbsp;&nbsp;<span id="send_result"></span></div>						
                     </div>
				</div>
			</div>
</div>
<script type="text/javascript">
		$(function(){

			$('#send_notice').click(function() {

				$('#send_result').html('{$lang['dle_updatebox']}');
				var notice = $('#notice').val();
				$.post("engine/ajax/adminfunction.php?action=sendnotice", { notice: notice } , function( data ){
					$('#send_result').append('&nbsp;' + data);
				});
				return false;
			});

		});
</script>
HTML;

}

echofooter();
?>