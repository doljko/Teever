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
 Файл: antivirus.class.php
-----------------------------------------------------
 Назначение: проверка файлов на наличие посторонних
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

class antivirus
{
	var $bad_files       = array();
	var $snap_files      = array();
	var $track_files      = array();
	var $snap      		 = false;
	var $checked_folders = array();
	var $dir_split       = '/';

	var $cache_files       = array(
	"./engine/cache/system/usergroup.php",
	"./engine/cache/system/category.php",
	"./engine/cache/system/vote.php",
	"./engine/cache/system/banners.php",
	"./engine/cache/system/banned.php",
	"./engine/cache/system/cron.php",
	"./engine/cache/system/informers.php",
	"./engine/cache/system/links.php",
	"./engine/data/config.php",
	"./engine/data/videoconfig.php",
	"./engine/data/socialconfig.php",
	"./engine/data/wordfilter.db.php",
	);

	var $good_files       = array(
	"./.htaccess",
	"./backup/.htaccess",
	"./engine/cache/.htaccess",
	"./engine/cache/system/.htaccess",
	"./engine/data/.htaccess",
	"./engine/data/emoticons/.htaccess",
	"./language/.htaccess",
	"./uploads/files/.htaccess",
	"./uploads/.htaccess",
	"./engine/ajax/quote.php",
	"./engine/ajax/vote.php",
	"./engine/ajax/feedback.php",
	"./engine/ajax/sitemap.php",
	"./engine/ajax/templates.php",
	"./engine/ajax/find_relates.php",
	"./engine/ajax/deletecomments.php",
	"./engine/ajax/calendar.php",
	"./engine/ajax/editcomments.php",
	"./engine/ajax/editnews.php",
	"./engine/ajax/favorites.php",
	"./engine/ajax/newsletter.php",
	"./engine/ajax/rating.php",
	"./engine/ajax/ratingcomments.php",
	"./engine/ajax/registration.php",
	"./engine/ajax/addcomments.php",
	"./engine/ajax/antivirus.php",
	"./engine/ajax/updates.php",
	"./engine/ajax/clean.php",
	"./engine/ajax/poll.php",
	"./engine/ajax/rss.php",
	"./engine/ajax/keywords.php",
	"./engine/ajax/pm.php",
	"./engine/ajax/bbcode.php",
	"./engine/ajax/upload.php",
	"./engine/ajax/typograf.php",
	"./engine/ajax/profile.php",
	"./engine/ajax/find_tags.php",
	"./engine/ajax/search.php",
	"./engine/ajax/message.php",
	"./engine/ajax/adminfunction.php",
	"./engine/ajax/allvotes.php",
	"./engine/ajax/rebuild.php",
	"./engine/ajax/complaint.php",
	"./engine/ajax/comments.php",
	"./engine/ajax/replycomments.php",
	"./engine/cache/system/usergroup.php",
	"./engine/cache/system/category.php",
	"./engine/cache/system/vote.php",
	"./engine/cache/system/banners.php",
	"./engine/cache/system/banned.php",
	"./engine/cache/system/cron.php",
	"./engine/cache/system/informers.php",
	"./engine/cache/system/links.php",
	"./engine/data/config.php",
	"./engine/data/videoconfig.php",
	"./engine/data/socialconfig.php",
	"./engine/data/dbconfig.php",
	"./engine/data/wordfilter.db.php",
	"./engine/skins/default.skin.php",
	"./engine/skins/.htaccess",
	"./engine/editor/fullnews.php",
	"./engine/editor/fullsite.php",
	"./engine/editor/newsletter.php",
	"./engine/editor/shortnews.php",
	"./engine/editor/shortsite.php",
	"./engine/editor/comments.php",
	"./engine/editor/static.php",
	"./engine/editor/emotions.php",
	"./engine/editor/.htaccess",
	"./engine/editor/jscripts/tiny_mce/plugins/emoticons/.htaccess",
	"./engine/editor/jscripts/tiny_mce/plugins/emoticons/emotions.php",
	"./engine/classes/.htaccess",
	"./engine/classes/mobiledetect.class.php",
	"./engine/classes/typograf.class.php",
	"./engine/classes/min/.htaccess",
	"./engine/classes/min/config.php",
	"./engine/classes/min/lib/JSMin.php",
	"./engine/classes/min/lib/Solar/Dir.php",
	"./engine/classes/min/lib/JSMinPlus.php",
	"./engine/classes/min/lib/Minify/Lines.php",
	"./engine/classes/min/lib/Minify/Cache/Memcache.php",
	"./engine/classes/min/lib/Minify/Cache/APC.php",
	"./engine/classes/min/lib/Minify/Cache/File.php",
	"./engine/classes/min/lib/Minify/Logger.php",
	"./engine/classes/min/lib/Minify/Packer.php",
	"./engine/classes/min/lib/Minify/CSS.php",
	"./engine/classes/min/lib/Minify/Controller/Groups.php",
	"./engine/classes/min/lib/Minify/Controller/Page.php",
	"./engine/classes/min/lib/Minify/Controller/Base.php",
	"./engine/classes/min/lib/Minify/Controller/MinApp.php",
	"./engine/classes/min/lib/Minify/Controller/Files.php",
	"./engine/classes/min/lib/Minify/Controller/Version1.php",
	"./engine/classes/min/lib/Minify/Build.php",
	"./engine/classes/min/lib/Minify/YUICompressor.php",
	"./engine/classes/min/lib/Minify/Source.php",
	"./engine/classes/min/lib/Minify/CommentPreserver.php",
	"./engine/classes/min/lib/Minify/ImportProcessor.php",
	"./engine/classes/min/lib/Minify/CSS/Compressor.php",
	"./engine/classes/min/lib/Minify/CSS/UriRewriter.php",
	"./engine/classes/min/lib/Minify/HTML.php",
	"./engine/classes/min/lib/FirePHP.php",
	"./engine/classes/min/lib/HTTP/Encoder.php",
	"./engine/classes/min/lib/HTTP/ConditionalGet.php",
	"./engine/classes/min/lib/MrClay/Cli/Arg.php",
	"./engine/classes/min/lib/MrClay/Cli.php",
	"./engine/classes/min/lib/Minify/JS/ClosureCompiler.php",
	"./engine/classes/min/lib/Minify/Cache/ZendPlatform.php",
	"./engine/classes/min/lib/Minify/YUI/CssCompressor.php",
	"./engine/classes/min/lib/Minify/HTML/Helper.php",
	"./engine/classes/min/lib/Minify/DebugDetector.php",
	"./engine/classes/min/lib/DooDigestAuth.php",
	"./engine/classes/min/lib/Minify/Loader.php",
	"./engine/classes/min/lib/Minify/ClosureCompiler.php",
	"./engine/classes/min/lib/Minify/Cache/XCache.php",
	"./engine/classes/min/lib/CSSmin.php",
	"./engine/classes/min/lib/Minify.php",
	"./engine/classes/min/index.php",
	"./engine/classes/min/groupsConfig.php",
	"./engine/classes/stopspam.class.php",
	"./engine/classes/mail/class.phpmailer.php",
	"./engine/classes/flashplayer/media_player.swf",
	"./engine/classes/flashplayer/youtube.swf",
	"./engine/classes/flashplayer/media_player_v3.swf",
	"./engine/editor/scripts/common/mediaelement/flashmediaelement.swf",
	"./engine/classes/html5player/flashmediaelement.swf",
	"./engine/modules/main.php",
	"./engine/modules/vote.php",
	"./engine/modules/addnews.php",
	"./engine/modules/antibot/antibot.php",
	"./engine/modules/antibot/.htaccess",
	"./engine/modules/antibot/fonts/.htaccess",
	"./engine/modules/banned.php",
	"./engine/modules/bbcode.php",
	"./engine/modules/calendar.php",
	"./engine/modules/comments.php",
	"./engine/modules/favorites.php",
	"./engine/modules/feedback.php",
	"./engine/modules/functions.php",
	"./engine/modules/lastcomments.php",
	"./engine/modules/lostpassword.php",
	"./engine/modules/offline.php",
	"./engine/modules/pm.php",
	"./engine/modules/pm_alert.php",
	"./engine/modules/profile.php",
	"./engine/modules/register.php",
	"./engine/modules/search.php",
	"./engine/modules/show.custom.php",
	"./engine/modules/show.full.php",
	"./engine/modules/show.short.php",
	"./engine/modules/sitelogin.php",
	"./engine/modules/static.php",
	"./engine/modules/stats.php",
	"./engine/modules/topnews.php",
	"./engine/modules/addcomments.php",
	"./engine/modules/poll.php",
	"./engine/modules/cron.php",
	"./engine/modules/banners.php",
	"./engine/modules/rssinform.php",
	"./engine/modules/deletenews.php",
	"./engine/modules/tagscloud.php",
	"./engine/modules/changemail.php",
	"./engine/modules/links.php",
	"./engine/modules/social.php",
	"./engine/modules/.htaccess",
	"./engine/api/api.class.php",
	"./engine/api/.htaccess",
	"./engine/inc/.htaccess",
	"./engine/inc/iptools.php",
	"./engine/classes/mail.class.php",
	"./engine/inc/mass_user_actions.php",
	"./engine/inc/blockip.php",
	"./engine/inc/social.php",
	"./engine/inc/categories.php",
	"./engine/inc/dboption.php",
	"./engine/inc/dumper.php",
	"./engine/inc/editnews.php",
	"./engine/inc/editusers.php",
	"./engine/inc/editvote.php",
	"./engine/inc/email.php",
	"./engine/inc/files.php",
	"./engine/inc/include/functions.inc.php",
	"./engine/inc/help.php",
	"./engine/inc/include/inserttag.php",
	"./engine/inc/main.php",
	"./engine/inc/videoconfig.php",
	"./engine/inc/tagscloud.php",
	"./engine/inc/complaint.php",
	"./engine/inc/links.php",
	"./engine/classes/social.class.php",
	"./engine/classes/thumb.class.php",
	"./engine/classes/comments.class.php",
	"./engine/classes/antivirus.class.php",
	"./engine/classes/uploads/upload.class.php",
	"./engine/inc/massactions.php",
	"./engine/classes/mysql.php",
	"./engine/inc/newsletter.php",
	"./engine/inc/options.php",
	"./engine/classes/parse.class.php",
	"./engine/inc/preview.php",
	"./engine/inc/static.php",
	"./engine/classes/templates.class.php",
	"./engine/inc/templates.php",
	"./engine/inc/userfields.php",
	"./engine/inc/usergroup.php",
	"./engine/inc/wordfilter.php",
	"./engine/inc/xfields.php",
	"./engine/inc/addnews.php",
	"./engine/inc/comments.php",
	"./engine/inc/banners.php",
	"./engine/inc/clean.php",
	"./engine/inc/rss.php",
	"./engine/inc/question.php",
	"./engine/inc/mass_static_actions.php",
	"./engine/inc/include/.htaccess",
	"./engine/inc/include/init.php",
	"./engine/classes/rss.class.php",
	"./engine/classes/recaptcha.php",
	"./engine/inc/search.php",
	"./engine/classes/download.class.php",
	"./engine/inc/cmoderation.php",
	"./engine/inc/rssinform.php",
	"./engine/inc/rebuild.php",
	"./engine/inc/logs.php",
	"./engine/classes/google.class.php",
	"./engine/inc/googlemap.php",
	"./engine/inc/check.php",
	"./engine/preview.php",
	"./engine/init.php",
	"./engine/opensearch.php",
	"./engine/engine.php",
	"./engine/print.php",
	"./engine/rss.php",
	"./engine/download.php",
	"./engine/go.php",
	"./index.php",
	"./cron.php",
	);

	function __construct()
	{   global $config;

		if(@file_exists(ENGINE_DIR.'/data/snap.db')) {
  			$filecontents = file(ENGINE_DIR.'/data/snap.db');

		    foreach ($filecontents as $name => $value) {
	    	  $filecontents[$name] = explode("|", trim($value));
	    	    $this->track_files[$filecontents[$name][0]] = $filecontents[$name][1];
		    }
		    
			$this->snap = true;

		}

		$this->good_files[] = "./{$config['admin_path']}";

	}
	
	function scan_files( $dir, $snap = false, $scanroot = false )
	{
		$this->checked_folders[] = $dir . $this->dir_split . $file;
	
		if ( $dh = @opendir( $dir ) )
		{
			while ( false !== ( $file = readdir($dh) ) )
			{
				if ( $file == '.' or $file == '..' or $file == '.svn' or $file == '.DS_store' )
				{
					continue;
				}
		
				if ( is_dir( $dir . $this->dir_split . $file ) )
				{

					if ($dir == ROOT_DIR AND !$scanroot) continue;

					$this->scan_files( $dir . $this->dir_split . $file, $snap, $scanroot );
				}
				else
				{

					if ($this->snap OR $snap) $templates = "|tpl|js|lng|htaccess|html"; elseif(strpos($dir.'/', ROOT_DIR.'/templates/') !== 0) $templates = "|htaccess|swf"; else $templates = "";

					if ( preg_match( "#.*\.(php|cgi|pl|perl".$templates.")#i", $file ) )
					{

					  $folder = str_replace(ROOT_DIR, ".",$dir);

					  if ($snap) {

						$file_crc = md5_file($dir . $this->dir_split . $file);

						$this->snap_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
													 'file_crc' => $file_crc );


					  } else {

						if ($this->snap) {

							$contin = false;
							$file_crc = md5_file($dir . $this->dir_split . $file);

							if ( $folder == "./engine/cache/system" AND strpos($file, "minify_") === 0 AND preg_match( "#.*\.(js)#i", $file ) AND !preg_match( "#.*\.(php)#i", $file )) $contin = true;

							if ($this->track_files[$folder . $this->dir_split . $file] != $file_crc AND !in_array($folder . $this->dir_split . $file, $this->cache_files) AND !$contin) {

								$file_date = date("d.m.Y H:i:s", filectime($dir . $this->dir_split . $file));
								$file_size = filesize($dir . $this->dir_split . $file);

								$this->bad_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
														'file_name' => $file,
														'file_date' => $file_date,
														'type' => 1,
														'file_size' => $file_size );
							}

					    } else { 

							 if (!in_array($folder . $this->dir_split . $file, $this->good_files)) {
								$file_date = date("d.m.Y H:i:s", filectime($dir . $this->dir_split . $file));
								$file_size = filesize($dir . $this->dir_split . $file);

							 	$this->bad_files[] = array( 'file_path' => $folder . $this->dir_split . $file,
														'file_name' => $file,
														'file_date' => $file_date,
														'type' => 0,
														'file_size' => $file_size );
							}

						}

					  }
					}
				}
			}
		}
	}
}

?>