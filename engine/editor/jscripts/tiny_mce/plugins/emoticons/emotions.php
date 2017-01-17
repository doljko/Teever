<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2007 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: comments.php
-----------------------------------------------------
 Назначение: WYSIWYG для комментариев
=====================================================
*/
define('DATALIFEENGINE', true);
define('ROOT_DIR', '../../../../../..');
define('ENGINE_DIR', '../../../../..');

error_reporting(7);
ini_set('display_errors', true);
ini_set('html_errors', false);

include ENGINE_DIR.'/data/config.php';

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/editor/jscripts/tiny_mce/plugins/emoticons/emotions.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

	$i = 0;
	$output = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr>";

	$smilies = explode(",", $config['smilies']);
	$count_smilies = count($smilies);
	
	foreach($smilies as $smile)
	{
		$i++;
		$smile = trim($smile);
		$sm_image ="";
		if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . ".png" ) ) {
			if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.png" ) ) {
				$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.png 2x\" />";
			} else {
				$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\" />";	
			}
		} elseif ( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . ".gif" ) ) {
			if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.gif" ) ) {
				$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.gif 2x\" />";
			} else {
				$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\" />";	
			}
		}
		
		$output .= "<td style=\"padding:5px;\" align=\"center\"><a href=\"#\" onclick=\"dle_smiley(':$smile:'); return false;\">{$sm_image}</a></td>";
		if ($i%7 == 0 AND $i < $count_smilies) $output .= "</tr><tr>";
	
	}

	$output .= "</tr></table>";

echo <<<HTML

{$output}
<script language='javascript'>
    function dle_smiley(finalImage) {

		parent.tinyMCE.execCommand('mceInsertContent',false,finalImage);
		parent.tinyMCE.activeEditor.windowManager.close();


	}
</script>
HTML;
?>