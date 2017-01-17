<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

function GetRandInt($max){

	if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
	     do{
	         $result = floor($max*(hexdec(bin2hex(openssl_random_pseudo_bytes(4)))/0xffffffff));
	     }while($result == $max);
	} else {

		$result = mt_rand( 0, $max );
	}

    return $result;
}

function generate_auth_key() {

    $arr = array('a','b','c','d','e','f',
                 'g','h','i','j','k','l',
                 'm','n','o','p','r','s',
                 't','u','v','x','y','z',
                 'A','B','C','D','E','F',
                 'G','H','I','J','K','L',
                 'M','N','O','P','R','S',
                 'T','U','V','X','Y','Z',
                 '1','2','3','4','5','6',
                 '7','8','9','0','.',',',
                 '(',')','[',']','!','?',
                 '&','^','%','@','*',' ',
                 '<','>','/','|','+','-',
                 '{','}','`','~','#',';',
                 '/','|','=',':','`');

    $key = "";
    for($i = 0; $i < 64; $i++)
    {
      $index = GetRandInt(count($arr))-1;
      $key .= $arr[$index];
    }
    return $key;
}

function xfieldssave($data) {
	
    $data = array_values($data);
	$filecontents = "";
		
    foreach ($data as $index => $value) {
      $value = array_values($value);
      foreach ($value as $index2 => $value2) {
        $value2 = stripslashes($value2);
        $value2 = str_replace("|", "&#124;", $value2);
        $value2 = str_replace("\r\n", "__NEWL__", $value2);
        $filecontents .= $value2 . ($index2 < count($value) - 1 ? "|" : "");
      }
      $filecontents .= ($index < count($data) - 1 ? "\r\n" : "");
    }
		
    $filehandle = fopen(ENGINE_DIR.'/data/xfields.txt', "w+");

    if (!$filehandle) die ("error");
	
	$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/javascript/i' );
	$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript" );
		
	$filecontents = preg_replace( $find, $replace, $filecontents );
	$filecontents = preg_replace( "#<iframe#i", "&lt;iframe", $filecontents );
	$filecontents = preg_replace( "#<script#i", "&lt;script", $filecontents );
	$filecontents = str_replace( "<?", "&lt;?", $filecontents );
	$filecontents = str_replace( "?>", "?&gt;", $filecontents );
	$filecontents = str_replace( "$", "&#036;", $filecontents );
		
    fwrite($filehandle, $filecontents);
    fclose($filehandle);
	

}

$config['version_id'] = "10.0";
$config['start_site'] = "1";
$config['clear_cache'] = "0";
$config['use_admin_mail'] = "0";
$config['allow_complaint_mail'] = "0";
$config['spam_api_key'] = "";
$config['sec_addnews'] = "2";

if ( $config['allow_read_count'] == "yes" ) $config['allow_read_count'] = "1";
if ( $config['allow_read_count'] == "no" ) $config['allow_read_count'] = "0";

if ( $config['safe_xfield'] ) $xfsafe_xfield = 1; else $xfsafe_xfield = 0;

unset($config['safe_xfield']);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` CHANGE `date` `date` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_pm` CHANGE `date` `date` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_rss` CHANGE `lastdate` `lastdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_sendlog` CHANGE `date` `date` INT(11) UNSIGNED NOT NULL DEFAULT '0'";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_read_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_read_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_spam_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_spam_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `is_spammer` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `is_spammer` (`is_spammer`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

foreach($tableSchema as $table) {
	$db->query ($table);
}


$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/config.php</b>.<br />Check CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => '{$value}',\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


$config_dbhost = DBHOST;
$config_dbname = DBNAME;
$config_dbuser = DBUSER;
$config_dbpasswd = DBPASS;
$config_dbprefix = PREFIX;
$config_userprefix = USERPREFIX;
$config_dbcollate = COLLATE;
$auth_key = generate_auth_key();
$config_dbpasswd = str_replace ('"', '\"', str_replace ("$", "\\$", $config_dbpasswd) );

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}");

define ("USERPREFIX", "{$config_userprefix}");

define ("COLLATE", "{$config_dbcollate}");

define('SECURE_AUTH_KEY', '{$auth_key}');

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Sorry! Unable to write information to <b>.engine/data/dbconfig.php</b>.<br />Check CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$fdir = opendir( ENGINE_DIR . '/cache/system/' );
while ( $file = readdir( $fdir ) ) {
	if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
		@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
	}
}

$xfields = xfieldsload();

$i=0;
if (count($xfields)) {

	foreach ( $xfields as $value ) {	
	
		if( $value[3] == "textarea" ) { $xfields[$i][7] = 1; $xfields[$i][8] = $xfsafe_xfield; }
		else { $xfields[$i][7] = 0; $xfields[$i][8] = 1; }
	
		$i++;
	}
	
	xfieldssave($xfields);
}

@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) {

	$error_info = "Total scheduled queries: <b>".$db->query_num."</b> Failed to execute: <b>".$db->error_count."</b>. Perhaps they were executed before.<br /><br /><div class=\"quote\"><b>List of failed requests:</b><br /><br />"; 

	foreach ($db->query_list as $value) {

		$error_info .= $value['query']."<br /><br />";

	}

	$error_info .= "</div>";

} else $error_info = "";

msgbox("info","Information", "Database update from version <b>9.8</b> to version <b>10.0</b> has been completed successfully.<br /><br />{$error_info}<br />Click Next to continue the engine update process.");
?>