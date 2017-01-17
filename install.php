<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
 This code is protected by copyrights
=====================================================
 File: install.php
-----------------------------------------------------
 Purpose: Script installation
=====================================================
*/
session_start();

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');

$distr_charset = "utf-8";
$db_charset = "utf8";

header("Content-type: text/html; charset=".$distr_charset);

require_once(ROOT_DIR.'/language/English/adminpanel.lng');
require_once(ENGINE_DIR.'/inc/include/functions.inc.php');


$skin_header = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="{$distr_charset}">
  <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <title>DataLife Engine - Installation</title>
  <link href="engine/skins/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="engine/skins/javascripts/application.js"></script>
<style type="text/css">
body {
	background: url("engine/skins/images/bg.png");

}
</style>
</head>
<body>
<script language="javascript" type="text/javascript">
<!--
var dle_act_lang   = ["Yes", "No", "Enter", "Cancel", "Upload images and files"];
var cal_language   = {en:{months:['January','February','March','April','May','June','July','August','September','October','November','December'],dayOfWeek:["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]}};
//-->
</script>
<nav class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
  <div class="navbar-header">
    <a class="navbar-brand" href="">DataLife Engine Installation Wizard</a>
  </div>
</nav>
<div class="container">
  <div class="col-md-8 col-md-offset-2">
    <div class="padded">
	    <div style="margin-top: 10px;">
<!--MAIN area-->
HTML;


$skin_footer = <<<HTML
	 <!--MAIN area-->
    </div>
  </div>
</div>
</div>

</body>
</html>
HTML;

function msgbox($type, $title, $text, $back=FALSE){
global $lang, $skin_header, $skin_footer;

  if ($back) $back = "onclick=\"history.go(-1); return false;\""; else $back = "";

  echo $skin_header;

echo <<<HTML
<form action="install.php" method="get">
<div class="box">
  <div class="box-header">
    <div class="title">{$title}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
		{$text}
	</div>
	<div class="row box-section">
		<input {$back} class="btn btn-green" type=submit value="Next">
	</div>

  </div>
</div>
</form>
HTML;

  echo $skin_footer;

  exit();
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

extract($_REQUEST, EXTR_SKIP);

if($_REQUEST['action'] == "eula")
{

if ( !$_SESSION['dle_install'] ) msgbox( "", "Error", "Script installation was started not from the beginning. Return to homepage to start the script installation: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

  echo $skin_header;

echo <<<HTML
<form id="check-eula" method="get" action="">
<input type=hidden name=action value="function_check">
<script language='javascript'>
function check_eula(){

	if( document.getElementById( 'eula' ).checked == true )
	{
		return true;
	}
	else
	{
		DLEalert( 'You must accept the license agreement before continuing the installation.', 'Information' );
		return false;
	}
};
document.getElementById( 'check-eula' ).onsubmit = check_eula;
</script>
<div class="box">
  <div class="box-header">
    <div class="title">License Agreement</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
		Please read carefully and accept before using DataLife Engine.<br /><br /><div style="height: 300px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;"><b>Dear User! Please, read the terms of use of the Program contained in this Agreement before starting the installation, copying or other use of the Program. Installation, launch or other use of the Program means the proper conclusion of this Agreement and your full acceptance of all its terms. If you do not agree to unconditionally accept the terms of this Agreement, you have no right to install and use the Program and must remove all of its components from your computer (PC).</b><br /><br />This EULA (hereinafter - the Agreement) is concluded between LLC "SoftNews Media Group" (hereinafter - the Licensee), and any natural person, an individual entrepreneur, or a legal entity (hereinafter - the User).<br /><br /><div style="text-align:center;"><b>1. Definitions</b></div><br /><b>1.1.</b> <b>Program</b> – is "DataLife Engine" computer program of the appropriate version (as a whole and its components), which is provided in the form of an objective set of data and commands, including the source code, databases, audiovisual works included in the Program by the Licensee, as well as any documentation on its use.<br /><br /><b>1.2. Use of Program</b> - any activity related to the functioning of the Program in accordance with its intended purpose (including saving in PC memory).<br /><br /><b>1.3. Technical Support</b> – activities carried out by the Licensee within the established limits and volumes to ensure the functioning of the Program, including information and advisory support to Users about the use of the Program.<br /><br /><b>1.4. Licensor</b> - keeper, owner and franchisor of the Program, who issues the License to another person (Licensee), that gives the right to use the Program within limits established by the License.<br /><br /><b>1.5. Licensee</b> - a legal entity or individual entrepreneur who has the License.<br /><br /><b>1.6. User</b> - a natural person, legal entity, or individual entrepreneur who purchased the license to use DataLife Engine according to the agreement.<br /><br /><div style="text-align:center;"><b>2. Subject of the license agreement</b></div><br /><b>2.1.</b> The subject of this license agreement is the right to use a single licensed copy of "DataLife Engine" computer program in the manner and on the conditions set forth in this Agreement. If you do not agree with the terms of this agreement, you can not use this product. Installation and use of the product indicates your complete acceptance of all the clauses of this Agreement.<br /><br /><b>2.2.</b> All provisions of this Agreement shall apply to the Program as a whole and to its individual components, except for the cases when a different type of license is applied to the system component.<br /><br /><b>2.3.</b> This Agreement is concluded before or directly at the start of the Program use and works throughout the entire period of its lawful use by the User within the term of copyright on it subject to the terms of this User Agreement.<br /><br /><div style="text-align:center;"><b>3. Content of the agreement</b></div><br /><b>3.1. </b>The term of customer service since the purchase of one licensed copy of the "DataLife Engine" program is one year. If you choose not to renew the service period at the end of it, your program will be fully operational, but without our support and without the provision of new versions of Program, except for critical software updates.<br /><br /><b>3.2. </b>Licensee provides Technical support to Users, including the issues related to the Program functionality, features of installation and operation at standard configurations of supported (popular) operating, mail and other systems in the manner and under the conditions specified in its technical documentation.<br /><br /><b>3.3. </b>In case of the purchase and use of only the basic license for the Program, the customer service is limited only by the delivery of standard service for the duration of the license agreement: the provision of distribution kits, new versions of Program, critical software updates. Technical support is not available for the basic license. User must have a license that includes technical support, or be a subscriber to technical support to get technical support for the program.<br /><br /><b>3.4. </b>If the User has purchased the Program for the transfer of the finished project or the website directly to the customer, the User is required to notify the customer of this license agreement, and to give him/her the client access on dle-news.com. Otherwise, a person who directly purchased the license, and having a client access on dle-news.com will still be the User of the license. Third parties who do not have this access will be denied to get the support and distribution kits.<br /><br /><b>3.5. </b>We reserve the right to publish lists of favourite sites with the agreement of the User of software that use "DataLife Engine" program. We reserve the right to modify the terms of this agreement at any time, but these changes are not retroactive. Changes in this agreement will be sent to users by e-mail to the addresses indicated when purchased the license for the Program.<br /><br /><div style="text-align:center;"><b>4. Limitations of Program Use</b></div><br /><b>4.1. </b>The program is the result of intellectual activity and subject to copyrights (computer program), which are regulated and protected by the laws of the Russian Federation intellectual property law and international law.<br /><br /><b>4.2. </b>The name "DataLife Engine" and scripts of this program  are the property of the Licensor, use of which is possible only within this Agreement, except for the cases when a different type of license is used for the system component. Any published original material created using the Program, and the associated rights to them are the property of the User and protected by law. Licensor and Licensee are not responsible for the content of websites, created by User of "DataLife Engine" Program.<br /><br /><b>4.3. </b>The algorithm of the program and its source code (including its parts) are the property of Licensor and Licensee. Any use of it or use of the Program that violates the terms of this Agreement shall be considered as a violation of the rights of the Licensor and is sufficient reason for deprivation of rights of the User provided in this Agreement.<br /><br /><b>4.4. </b>Licensee guarantees that it has all necessary rights under this Agreement to provide them to Users, including the program documentation.<br /><br /><b>4.5. </b>You should know that you do not purchase copyrights for the Program by purchasing a license for the "DataLife Engine". You purchase the right to use the software on a single website only (a second-level domain and its subdomains) owned by you or your client. You need to purchase another license to use the program on another website.<br /><br /><b>4.6. </b>Program can not be resold to third parties.<br /><br /><b>4.7. </b>The User of this Agreement is not provided with any right to use the trademarks and service marks of Licensor and Licensee.<br /><br /><b>4.8. </b>The User is not allowed to remove or change the information appearance and information about the copyrights, trademark or patent indicated in the source code of the Program under any circumstances.<br /><br /><div style="text-align:center;"><b>5. Rights and obligations of the Parties</b></div><br /><b>5.1. User may:</b><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.1.1 </b>Changing the design and structure of the code in accordance with the needs of your site.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.1.2 </b>Produce and distribute the instructions on custom templates and language files modifications, if they have a reference to the original developer of the software that is modified by you. The modifications made by you are not owned by the Licensor, if there is no source code of the Program itself.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.1.3 </b>Create your own modules for the Program that will interact with the source code of the Program, with the indication that this is an original product of the User.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.1.4 </b>Move the Program to another site after obligatory notification to the Licensee, as well as the complete removal of the Program from the previous website.<br /><br /><b>5.2. User may not:</b><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.1</b> Transfer the right to use the Program to third parties.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.2 </b>Use or change the structure of the program code and functions of the program to create related products.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.3 </b>Create separate standalone products based on our code.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.4 </b>Use copies of the "DataLife Engine" program with one license on more than one website (one second-level domain and its subdomains).<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.5 </b>Advertise, sell or publish pirated copies of the Program on User's website.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.6 </b>Distribute or promote the distribution of unlicensed copies of the "DataLife Engine" program.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>5.2.7 </b>Modify and delete the original license validation procedure.<br /><br /><div style="text-align:center;"><b>6. Limitation of warranty</b></div><br /><b>6.1. </b>We note that security mechanisms that are installed on "DataLife Engine", have its limitations, and despite the fact that we are making every effort to ensure the safety of the Program, you must know that we can not absolutely guarantee that your site will not be hacked. Also, our warranty and technical support is not subject to the modifications made by third parties, including changes in the code, style, language packs, as well as if these changes are made by the Licensee. If the program is modified by you or a third party, we refuse to provide technical support.<br /><br /><b>6.2. </b>DataLife Engine" program can not be returned or exchanged due to the lack of guarantees that protect the program from being copied.<br /><br /><div style="text-align:center;"><b>7. Early termination of the contractual obligations</b></div><br /><b>7.1. </b>This agreement shall be terminated automatically if you refuse to comply with the terms of our agreement. This sublicense agreement may be terminated by us unilaterally in case of violation of the sublicense agreement. In case of early termination of the Agreement, you must delete all of your copies of our Program within 3 working days from the date of receipt of the notification.</div>
		<br /><br /><input type="checkbox" name="eula" id="eula" class="icheck"><label for="eula"> I accept this agreement</label>
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type="submit" value="Next">
	</div>

  </div>
</div>
</form>
HTML;

} elseif($_REQUEST['action'] == "function_check") {

    if ( !$_SESSION['dle_install'] ) msgbox( "", "Error", "Script installation was started not from the beginning. Return to homepage to start the script installation: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

  echo $skin_header;

echo <<<HTML
<form method="get" action="">
<input type=hidden name="action" value="chmod_check">
<div class="box">
  <div class="box-header">
    <div class="title">Check of the installed PHP components</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
<table class="table table-normal table-bordered">
<tr>
<td width="250">Minimal script requirements</td>
<td colspan="2">Current value</td>
</tr>
HTML;

    $status = phpversion() < '5.3' ? '<font color=red><b>No</b></font>' : '<font color=green><b>Yes</b></font>';

   echo "<tr>
         <td>PHP ver. 5.3 or higher</td>
         <td colspan=2>$status</td>
         </tr>";

    $status = function_exists('mysqli_connect') ? '<font color=green><b>Yes</b></font>' : '<font color=red><b>No</b></font>';

   echo "<tr>
         <td>MySQLi support</td>
         <td colspan=2>$status</td>
         </tr>";


    $status = extension_loaded('zlib') ? '<font color=green><b>Yes</b></font>' : '<font color=red><b>No</b></font>';

   echo "<tr>
         <td>ZLib compression support</td>
         <td colspan=2>$status</td>
         </tr>";

    $status = extension_loaded('xml') ? '<font color=green><b>Yes</b></font>' : '<font color=red><b>No</b></font>';

   echo "<tr>
         <td>XML support</td>
         <td colspan=2>$status</td>
         </tr>";

    $status = function_exists('mb_convert_encoding') ? '<font color=green><b>Yes</b></font>' : '<font color=red><b>No</b></font>';;

   echo "<tr>
         <td>Multibyte strings support</td>
         <td colspan=2>$status</td>
         </tr>";

   echo "<tr>
         <td colspan=3><br />If any of these items are highlighted in red, please follow the steps to remedy the situation. In the case of non-compliance with the minimum requirements of the script it can not work properly in the system.<br /><br /></td>
         </tr>";

    echo "<tr>
    <td>Recommended settings</td>
    <td width=\"200\">Recommended value</td>
    <td>Current value</td>
    </tr>";

    $status = ini_get('safe_mode') ? '<font color=red><b>Enabled</b></font>' : '<font color=green><b>Disabled</b></font>';

    echo"<tr>
         <td>Safe Mode</td>
         <td>Disabled</td>
         <td>$status</td>
         </tr>";

    $status = ini_get('file_uploads') ? '<font color=green><b>Enabled</b></font>' : '<font color=red><b>Disabled</b></font>';

    echo"<tr>
         <td>Files uploading</td>
         <td>Enabled</td>
         <td>$status</td>
         </tr>";

    $status = ini_get('output_buffering') ? '<font color=red><b>Enabled</b></font>' : '<font color=green><b>Disabled</b></font>';

   echo "<tr>
         <td>Output buffering</td>
         <td>Disabled</td>
         <td>$status</td>
         </tr>";

    $status = ini_get('magic_quotes_runtime') ? '<font color=red><b>Enabled</b></font>' : '<font color=green><b>Disabled</b></font>';

   echo "<tr>
         <td>Magic Quotes Runtime</td>
         <td>Disabled</td>
         <td>$status</td>
         </tr>";

    $status = ini_get('register_globals') ? '<font color=red><b>Enabled</b></font>' : '<font color=green><b>Disabled</b></font>';

   echo "<tr>
         <td>Register Globals</td>
         <td>Disabled</td>
         <td>$status</td>
         </tr>";

    $status = ini_get('session.auto_start') ? '<font color=red><b>Enabled</b></font>' : '<font color=green><b>Disabled</b></font>';

   echo "<tr>
         <td>Session auto start</td>
         <td>Disabled</td>
         <td>$status</td>
         </tr>";

   echo "<tr>
         <td colspan=3 class=\"note lagre\"><br />These settings are recommended to be fully compatible, but the script is able to operate even if the recommended settings does not match the current settings.<br /><br /></td>
         </tr>";

echo <<<HTML
</table>
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type=submit value="Next">
	</div>

  </div>
</div></form>
HTML;

}
// ********************************************************************************
// Checking write permissions
// ********************************************************************************
elseif($_REQUEST['action'] == "chmod_check")
{

if ( !$_SESSION['dle_install'] ) msgbox( "", "Error", "Script installation was started not from the beginning. Return to homepage to start the script installation: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

  echo $skin_header;

echo <<<HTML
<form method="get" action="">
<input type=hidden name="action" value="doconfig">
<div class="box">
  <div class="box-header">
    <div class="title">Checking the write access for important system files</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
<table class="table table-normal table-bordered">
HTML;

echo"<thead><tr>
<td>Directory/File</td>
<td width=\"100\">CHMOD</td>
<td width=\"100\">Status</td></tr></thead><tbody>";

$important_files = array(
'./backup/',
'./engine/data/',
'./engine/cache/',
'./engine/cache/system/',
'./uploads/',
'./uploads/files/',
'./uploads/fotos/',
'./uploads/posts/',
'./uploads/posts/thumbs/',
'./uploads/posts/medium/',
'./uploads/thumbs/',
'./templates/',
'./templates/Default/',
);


$chmod_errors = 0;
$not_found_errors = 0;
    foreach($important_files as $file){

        if(!file_exists($file)){
            $file_status = "<font color=red>is not found!</font>";
            $not_found_errors ++;
        }
        elseif(is_writable($file)){
            $file_status = "<font color=green>is permitted</font>";
        }
        else{
            @chmod($file, 0777);
            if(is_writable($file)){
                $file_status = "<font color=green>is permitted</font>";
            }else{
                @chmod("$file", 0755);
                if(is_writable($file)){
                    $file_status = "<font color=green>is permitted</font>";
                }else{
                    $file_status = "<font color=red>is prohibited</font>";
                    $chmod_errors ++;
                }
            }
        }
        $chmod_value = @decoct(@fileperms($file)) % 1000;

    echo"<tr>
         <td>$file</td>
         <td>$chmod_value</td>
         <td>$file_status</td>
         </tr>";
    }
    
if($chmod_errors == 0 and $not_found_errors == 0){

    $status_report = 'Checking is successfully completed! You can continue the installation!';

} else {
    
    if($chmod_errors > 0){
        $status_report = "<font color=red>Warning!!!</font><br /><br />The following errors were found during the check: <b>$chmod_errors</b>. It is permitted to write to the file.<br />You must set CHMOD 777 for folders and CHMOD 666 for files using FTP client.<br /><br /><font color=red><b>It is strongly recommended</b></font> not to continue the installation until changes will be done.<br />";
    }

    if($not_found_errors > 0){
        $status_report .= "<font color=red>Warning!!!</font><br />The following errors were found during the check: <b>$not_found_errors</b>. Files not found!<br /><br /><font color=red><b>It is not recommended</b></font> to continue the installation until changes will be done.<br />";
    }
}

echo"<tr><td height=\"25\" colspan=3>&nbsp;&nbsp;Check status</td></tr><tr><td style=\"padding: 5px\" colspan=3>$status_report</td></tr>";

echo <<<HTML
</tbody></table>
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type=submit value="Next">
	</div>

  </div>
</div></form>
HTML;

} elseif($_REQUEST['action'] == "doconfig") {

    if ( !$_SESSION['dle_install'] ) msgbox( "", "Error", "Script installation was started not from the beginning. Return to homepage to start the script installation: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );
    
    
    $url  = preg_replace( "'/install.php'", "", $_SERVER['HTTP_REFERER']);
    $url  = preg_replace( "'\?(.*)'", "", $url);
    
    if(substr("$url", -1) == "/"){
        $url = substr($url, 0, -1);
    }

  echo $skin_header;

  echo <<<HTML
<form method="post" action="">
<input type=hidden name="action" value="doinstall">
<div class="box">
  <div class="box-header">
    <div class="title">System configuration settings</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
<table width="100%">
HTML;

if ( $distr_charset == "utf-8" ) {
  
  $mb4 = '<tr><td valign="top" style="padding: 5px;">Use 4-Byte UTF-8<td style="padding: 5px;"><input type="checkbox" id="allow_mb4" name="allow_mb4" value="1"><br /><span class="note large">Some non-common symbols (such as historical scripts, music symbols and Emoji) require more space in the database to be stored. If you leave this setting disabled, users will not be able to use these symbols on your site. If enabled, these characters will be able to be used, but the database will use more disk space.</span></tr>';

} else $mb4 = "";

echo '<tr>
<td width="175" style="padding: 5px;">Website URL:
<td style="padding: 5px;"><input name=url value="'.$url.'/" size=38 type=text><br><span class="note large">Specify the path without the filename. <font color="red">/</font>Slash on the end is required.</span></tr>
<tr><td colspan="3" height="40">&nbsp;&nbsp;<b>Data for access to MySQL server</b><td></tr>
<tr><td style="padding: 5px;">MySQL Server Host:<td style="padding: 5px;"><input type=text size="28" name="dbhost" value="localhost"></tr>
<tr><td style="padding: 5px;">Database Name:<td style="padding: 5px;"><input type=text size="28" name="dbname"></tr>
<tr><td style="padding: 5px;">MySQL User Name:<td style="padding: 5px;"><input type=text size="28" name="dbuser"></tr>
<tr><td style="padding: 5px;">MySQL Password:<td style="padding: 5px;"><input type=text size="28" name="dbpasswd"></tr>
<tr><td style="padding: 5px;">Prefix:<td style="padding: 5px;"><input type=text size="28" name="dbprefix" value="dle"> <span class="note large">Do not change the parameter unless you know what it is for</span></tr>
'.$mb4.'
<tr><td colspan="3"  height="40">&nbsp;&nbsp;<b>Data for the access to the control panel</b><td></tr>
<tr><td style="padding: 5px;">Login for Administrator:<td style="padding: 5px;"><input type=text size="28" name="reg_username" ></tr>
<tr><td style="padding: 5px;">Password:<td style="padding: 5px;"><input type=password size="28" name="reg_password1"> <span class="note large"><b>do not</b> forget the password!</span></tr>
<tr><td style="padding: 5px;">Confirm Your Password:<td style="padding: 5px;"><input type=password size="28" name="reg_password2"></tr>
<tr><td style="padding: 5px;">E-mail:<td style="padding: 5px;"><input type=text size="28" name="reg_email"></tr>
<tr><td colspan="3"  height="40">&nbsp;&nbsp;<b>Extra Settings</b><td></tr>
<tr><td style="padding: 5px;">Enable User-Friendly URL Support:
<td style="padding: 5px;">
<select class=rating name="alt_url"><option value="1">Yes</option><option value="0">No</option></select>&nbsp;&nbsp;<span class="note large">If you disable User-Friendly URL support, be sure to remove .htaccess file from the root folder</span>
</tr>';

echo <<<HTML
</table>
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type=submit value="Next">
	</div>

  </div>
</div></form>
HTML;

}
// ********************************************************************************
// Do Install
// ********************************************************************************
elseif($_REQUEST['action'] == "doinstall")
{

	if ( !$_SESSION['dle_install'] ) msgbox( "", "Error", "Script installation was started not from the beginning. Return to homepage to start the script installation: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

    if(!$reg_username or !$reg_email or !$reg_password1 or !$url or $reg_password1 != $reg_password2){ msgbox("error", "Error!!!" ,"Fill in the required fields!", "history.go(-1)"); }
	if (preg_match("/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $reg_username))
	{
		msgbox("error", "Error!!!" ,"The entered Administrator name is unacceptable for the registration!", "history.go(-1)");
	}

    $reg_password = md5(md5($reg_password1));

	$url = htmlspecialchars( $url, ENT_QUOTES, 'ISO-8859-1');
	$reg_email = htmlspecialchars( $reg_email, ENT_QUOTES, 'ISO-8859-1');
	$alt_url = intval( $alt_url );
	$url = str_replace( "$", "&#036;", $url );
	$reg_email = str_replace( "$", "&#036;", $reg_email );
	$timezone = date_default_timezone_get();
	
	$timezones = array('Pacific/Midway','US/Samoa','US/Hawaii','US/Alaska','US/Pacific','America/Tijuana','US/Arizona','US/Mountain','America/Chihuahua','America/Mazatlan','America/Mexico_City','America/Monterrey','US/Central','US/Eastern','US/East-Indiana','America/Lima','America/Caracas','Canada/Atlantic','America/La_Paz','America/Santiago','Canada/Newfoundland','America/Buenos_Aires','Greenland','Atlantic/Stanley','Atlantic/Azores','Africa/Casablanca','Europe/Dublin','Europe/Lisbon','Europe/London','Europe/Amsterdam','Europe/Belgrade','Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Budapest','Europe/Copenhagen','Europe/Madrid','Europe/Paris','Europe/Prague','Europe/Rome','Europe/Sarajevo','Europe/Stockholm','Europe/Vienna','Europe/Warsaw','Europe/Zagreb','Europe/Athens','Europe/Bucharest','Europe/Helsinki','Europe/Istanbul','Asia/Jerusalem','Europe/Kiev','Europe/Minsk','Europe/Riga','Europe/Sofia','Europe/Tallinn','Europe/Vilnius','Asia/Baghdad','Asia/Kuwait','Africa/Nairobi','Asia/Tehran','Europe/Kaliningrad','Europe/Moscow','Europe/Volgograd','Europe/Samara','Asia/Baku','Asia/Muscat','Asia/Tbilisi','Asia/Yerevan','Asia/Kabul','Asia/Yekaterinburg','Asia/Tashkent','Asia/Kolkata','Asia/Kathmandu','Asia/Almaty','Asia/Novosibirsk','Asia/Jakarta','Asia/Krasnoyarsk','Asia/Hong_Kong','Asia/Kuala_Lumpur','Asia/Singapore','Asia/Taipei','Asia/Ulaanbaatar','Asia/Urumqi','Asia/Irkutsk','Asia/Seoul','Asia/Tokyo','Australia/Adelaide','Australia/Darwin','Asia/Yakutsk','Australia/Brisbane','Pacific/Port_Moresby','Australia/Sydney','Asia/Vladivostok','Asia/Sakhalin','Asia/Magadan','Pacific/Auckland','Pacific/Fiji');

	if ( !in_array($timezone, $timezones) ) {
		$timezone = "Europe/Moscow";
		date_default_timezone_set ( $timezone );
	}

  if ( $distr_charset == "utf-8" AND $_POST['allow_mb4']) {
    $db_charset = "utf8mb4";
  }

$config = <<<HTML
<?PHP

//System Configurations

\$config = array (

'version_id' => "11.0",

'home_title' => "DataLife Engine",

'http_home_url' => "{$url}",

'charset' => "{$distr_charset}",

'admin_mail' => "{$reg_email}",

'description' => "Demo page of DataLife Engine",

'keywords' => "DataLife, Engine, CMS, PHP engine",

'date_adjust' => "{$timezone}",

'site_offline' => "0",

'allow_alt_url' => "{$alt_url}",

'langs' => "English",

'skin' => "Default",

'allow_gzip' => "0",

'allow_admin_wysiwyg' => "0",

'allow_static_wysiwyg' => "0",

'news_number' => "10",

'smilies' => "bowtie,smile,laughing,blush,smiley,relaxed,smirk,heart_eyes,kissing_heart,kissing_closed_eyes,flushed,relieved,satisfied,grin,wink,stuck_out_tongue_winking_eye,stuck_out_tongue_closed_eyes,grinning,kissing,stuck_out_tongue,sleeping,worried,frowning,anguished,open_mouth,grimacing,confused,hushed,expressionless,unamused,sweat_smile,sweat,disappointed_relieved,weary,pensive,disappointed,confounded,fearful,cold_sweat,persevere,cry,sob,joy,astonished,scream,tired_face,angry,rage,triumph,sleepy,yum,mask,sunglasses,dizzy_face,imp,smiling_imp,neutral_face,no_mouth,innocent",

'timestamp_active' => "j-m-Y, H:i",

'news_sort' => "date",

'news_msort' => "DESC",

'hide_full_link' => "0",

'allow_site_wysiwyg' => "0",

'allow_comments' => "1",

'comm_nummers' => "30",

'comm_msort' => "ASC",

'flood_time' => "30",

'auto_wrap' => "80",

'timestamp_comment' => "j F Y H:i",

'allow_comments_wysiwyg' => "0",

'allow_registration' => "1",

'allow_cache' => "0",

'allow_votes' => "1",

'allow_topnews' => "1",

'allow_read_count' => "1",

'allow_calendar' => "1",

'allow_archives' => "1",

'files_allow' => "1",

'files_count' => "1",

'reg_group' => "4",

'registration_type' => "0",

'allow_sec_code' => "1",

'allow_skin_change' => "1",

'max_users' => "0",

'max_users_day' => "0",

'max_up_size' => "200",

'max_image_days' => "2",

'allow_watermark' => "1",

'max_watermark' => "150",

'max_image' => "200",

'jpeg_quality' => "85",

'files_antileech' => "1",

'allow_banner' => "1",

'log_hash' => "0",

'show_sub_cats' => "1",

'tag_img_width' => "0",

'mail_metod' => "php",

'smtp_host' => "localhost",

'smtp_port' => "25",

'smtp_user' => "",

'smtp_pass' => "",

'mail_bcc' => "0",

'speedbar' => "1",

'extra_login' => "0",

'image_align' => "left",

'ip_control' => "1",

'cache_count' => "0",

'related_news' => "1",

'no_date' => "1",

'mail_news' => "1",

'mail_comments' => "1",

'admin_path' => "admin.php",

'rss_informer' => "1",

'allow_cmod' => "0",

'max_up_side' => "0",

'files_force' => "1",

'short_rating' => "1",

'full_search' => "0",

'allow_multi_category' => "1",

'short_title' => "Website Demo",

'allow_rss' => "1",

'rss_mtype' => "0",

'rss_number' => "10",

'rss_format' => "1",

'comments_maxlen' => "3000",

'offline_reason' => "The website is currently on the reconstruction. After the completion of all works the website will be opened.<br /><br />We apologize for any inconvenience.",

'catalog_sort' => "date",

'catalog_msort' => "DESC",

'related_number' => "5",

'seo_type' => "2",

'max_moderation' => "0",

'allow_quick_wysiwyg' => "0",

'sec_addnews' => "2",

'mail_pm' => "1",

'allow_change_sort' => "1",

'registration_rules' => "1",

'allow_tags' => "1",

'allow_add_tags' => "1",

'allow_fixed' => "1",

'max_file_count' => "0",

'allow_smartphone' => "0",

'allow_smart_images' => "0",

'allow_smart_video' => "0",

'allow_search_print' => "1",

'allow_search_link' => "1",

'allow_smart_format' => "1",

'thumb_dimming' => "0",

'thumb_gallery' => "1",

'max_comments_days' => "0",

'allow_combine' => "1",

'allow_subscribe' => "1",

'parse_links' => "0",

't_seite' => "0",

'comments_minlen' => "10",

'js_min' => "0",

'outlinetype' => "0",

'fast_search' => "1",

'login_log' => "5",

'allow_recaptcha' => "0",

'recaptcha_public_key' => "",

'recaptcha_private_key' => "",

'search_number' => "10",

'news_navigation' => "1",

'smtp_mail' => "",

'seo_control' => "0",

'news_restricted' => "0",

'comments_restricted' => "0",

'auth_metod' => "0",

'comments_ajax' => "0",

'create_catalog' => "0",

'mobile_news' => "10",

'reg_question' => "0",

'news_future' => "0",

'cache_type' => "0",

'memcache_server' => "localhost:11211",

'allow_comments_cache' => "1",

'reg_multi_ip' => "1",

'top_number' => "10",

'tags_number' => "40",

'mail_title' => "",

'o_seite' => "0",

'online_status' => "1",

'avatar_size' => "100",

'allow_share' => "1",

'auth_domain' => "0",

'start_site' => "1",

'clear_cache' => "0",

'allow_complaint_mail' => "0",

'spam_api_key' => "",

'create_metatags' => '1',

'admin_allowed_ip' => '',

'related_only_cats' => '0',

'allow_links' => '1',

'comments_lazyload' => '0',

'category_separator' => '/',

'speedbar_separator' => '&raquo;',

'adminlog_maxdays' => '30',

'allow_social' => '0',

'medium_image' => '450',

'login_ban_timeout' => '20',

'watermark_seite' => '4',

'auth_only_social' => '0',

'rating_type' => '0',

'allow_comments_rating' => '1',

'comments_rating_type' => '1',

'tree_comments' => '0',

'tree_comments_level' => '5',

'simple_reply' => '0',

'recaptcha_theme' => "light",

'smtp_secure' => '',

'search_pages' => '5',

'profile_news' => '1',

'key' => '',

);

?>
HTML;

$dbhost = str_replace ('"', '\"', str_replace ("$", "\\$", $dbhost) );
$dbname = str_replace ('"', '\"', str_replace ("$", "\\$", $dbname) );
$dbuser = str_replace ('"', '\"', str_replace ("$", "\\$", $dbuser) );
$dbpasswd = str_replace ('"', '\"', str_replace ("$", "\\$", $dbpasswd) );
$dbprefix = str_replace ('"', '\"', str_replace ("$", "\\$", $dbprefix) );
$auth_key = generate_auth_key();

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$dbhost}");

define ("DBNAME", "{$dbname}");

define ("DBUSER", "{$dbuser}");

define ("DBPASS", "{$dbpasswd}");

define ("PREFIX", "{$dbprefix}");

define ("USERPREFIX", "{$dbprefix}");

define ("COLLATE", "{$db_charset}");

define('SECURE_AUTH_KEY', '{$auth_key}');

\$db = new db;

?>
HTML;


$video_config = <<<HTML
<?PHP

//Videoplayers Configurations

\$video_config = array (

'width' => "425",

'height' => "325",

'play' => "false",

'progressBarColor' => "0xFFFFFF",

'tube_related' => "0",

'tube_dle' => "0",

'audio_width' => "425",

'preload' => '1',

);

?>
HTML;


$social_config = <<<HTML
<?PHP

//Social Configurations

\$social_config = array (

'vk' => '0',

'vkid' => '',

'vksecret' => '',

'od' => '0',

'odid' => '',

'odpublic' => '',

'odsecret' => '',

'fc' => '0',

'fcid' => '',

'fcsecret' => '',

'google' => '0',

'googleid' => '',

'googlesecret' => '',

'mailru' => '0',

'mailruid' => '',

'mailrusecret' => '',

'yandex' => '0',

'yandexid' => '',

'yandexsecret' => '',

);

?>
HTML;

$con_file = fopen("engine/data/config.php", "w+") or die("Sorry! Unable to create <b>.engine/data/config.php</b>.<br />Check the CHMOD!");
fwrite($con_file, $config);
fclose($con_file);
@chmod("engine/data/config.php", 0666);

$con_file = fopen("engine/data/dbconfig.php", "w+") or die("Sorry! Unable to create <b>.engine/data/dbconfig.php</b>.<br />Check the CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);
@chmod("engine/data/dbconfig.php", 0666);

$con_file = fopen("engine/data/videoconfig.php", "w+") or die("Sorry! Unable to create <b>.engine/data/videoconfig.php</b>.<br />Check the CHMOD!");
fwrite($con_file, $video_config);
fclose($con_file);
@chmod("engine/data/videoconfig.php", 0666);

$con_file = fopen("engine/data/socialconfig.php", "w+") or die("Sorry! Unable to create <b>.engine/data/socialconfig.php</b>.<br />Check the CHMOD!");
fwrite($con_file, $social_config);
fclose($con_file);
@chmod("engine/data/socialconfig.php", 0666);

$con_file = fopen("engine/data/wordfilter.db.php", "w+") or die("Sorry! Unable to create <b>.engine/data/wordfilter.db.php</b>.<br />Check the CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/wordfilter.db.php", 0666);

$con_file = fopen("engine/data/xfields.txt", "w+") or die("Sorry! Unable to create <b>.engine/data/xfields.txt</b>.<br />Check the CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/xfields.txt", 0666);

$con_file = fopen("engine/data/xprofile.txt", "w+") or die("Sorry! Unable to create <b>.engine/data/xprofile.txt</b>.<br />Check the CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/xprofile.txt", 0666);

@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/cache/system/informers.php');
@unlink(ENGINE_DIR.'/data/snap.db');

define ("PREFIX", $dbprefix);
define ("COLLATE", $db_charset);

$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_category";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_category (
  `id` mediumint(8) NOT NULL auto_increment,
  `parentid` mediumint(8) NOT NULL default '0',
  `posi` mediumint(8) NOT NULL default '1',
  `name` varchar(50) NOT NULL default '',
  `alt_name` varchar(50) NOT NULL default '',
  `icon` varchar(200) NOT NULL default '',
  `skin` varchar(50) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `keywords` text NOT NULL,
  `news_sort` varchar(10) NOT NULL default '',
  `news_msort` varchar(4) NOT NULL default '',
  `news_number` smallint(5) NOT NULL default '0',
  `short_tpl` varchar(40) NOT NULL default '',
  `full_tpl` varchar(40) NOT NULL default '',
  `metatitle` varchar(255) NOT NULL default '',
  `show_sub` tinyint(1) NOT NULL default '0',
  `allow_rss` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_comments";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_comments (
  `id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '2000-01-01 00:00:00',
  `autor` varchar(40) NOT NULL default '',
  `email` varchar(40) NOT NULL default '',
  `text` text NOT NULL,
  `ip` varchar(40) NOT NULL default '',
  `is_register` tinyint(1) NOT NULL default '0',
  `approve` tinyint(1) NOT NULL default '1',
  `rating` int(11) NOT NULL default '0',
  `vote_num` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  KEY `approve` (`approve`),
  KEY `parent` (`parent`),
  FULLTEXT KEY `text` (`text`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_email";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_email (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL default '',
  `template` text NOT NULL,
  `use_html` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";


$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_flood";

$tableSchema[] = "CREATE TABLE  " . PREFIX . "_flood (
  `f_id` int(11) unsigned NOT NULL auto_increment,
  `ip` varchar(40) NOT NULL default '',
  `id` varchar(20) NOT NULL default '',
  `flag` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`f_id`),
  KEY `ip` (`ip`),
  KEY `id` (`id`),
  KEY `flag` (`flag`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_images";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_images (
  `id` int(10) unsigned NOT NULL auto_increment,
  `images` text NOT NULL,
  `news_id` int(10) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `author` (`author`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_logs";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_logs (
  `id` int(10) unsigned NOT NULL auto_increment,
  `news_id` int(10) NOT NULL default '0',
  `member` varchar(40) NOT NULL default '',
  `ip` varchar(40) NOT NULL default '',
  `rating` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `member` (`member`),
  KEY `ip` (`ip`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_vote";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_vote (
  `id` mediumint(8) NOT NULL auto_increment,
  `category` text NOT NULL,
  `vote_num` mediumint(8) NOT NULL default '0',
  `date` varchar(25) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `body` text NOT NULL,
  `approve` tinyint(1) NOT NULL default '1',
  `start` varchar(15) NOT NULL default '',
  `end` varchar(15) NOT NULL default '',
  `grouplevel` varchar(250) NOT NULL default 'all',
  PRIMARY KEY  (`id`),
  KEY `approve` (`approve`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_vote_result";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_vote_result (
  `id` int(10) NOT NULL auto_increment,
  `ip` varchar(40) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `vote_id` mediumint(8) NOT NULL default '0',
  `answer` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `answer` (`answer`),
  KEY `vote_id` (`vote_id`),
  KEY `ip` (`ip`),
  KEY `name` (`name`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_lostdb";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_lostdb (
  `id` mediumint(8) NOT NULL auto_increment,
  `lostname` mediumint(8) NOT NULL default '0',
  `lostid` varchar( 40 ) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `lostid` (`lostid`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_pm";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_pm (
  `id` int(11) unsigned NOT NULL auto_increment,
  `subj` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `user` MEDIUMINT(8) NOT NULL default '0',
  `user_from` varchar(40) NOT NULL default '',
  `date` int(11) unsigned NOT NULL default '0',
  `pm_read` TINYINT(1) NOT NULL default '0',
  `folder` varchar(10) NOT NULL default '',
  `reply` tinyint(1) NOT NULL default '0',
  `sendid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `folder` (`folder`),
  KEY `user` (`user`),
  KEY `user_from` (`user_from`),
  KEY `pm_read` (`pm_read`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_post (
  `id` int(11) NOT NULL auto_increment,
  `autor` varchar(40) NOT NULL default '',
  `date` datetime NOT NULL default '2000-01-01 00:00:00',
  `short_story` text NOT NULL,
  `full_story` text NOT NULL,
  `xfields` text NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `keywords` text NOT NULL,
  `category` varchar(200) NOT NULL default '0',
  `alt_name` varchar(200) NOT NULL default '',
  `comm_num` mediumint(8) unsigned NOT NULL default '0',
  `allow_comm` tinyint(1) NOT NULL default '1',
  `allow_main` tinyint(1) unsigned NOT NULL default '1',
  `approve` tinyint(1) NOT NULL default '0',
  `fixed` tinyint(1) NOT NULL default '0',
  `allow_br` tinyint(1) NOT NULL default '1',
  `symbol` varchar(3) NOT NULL default '',
  `tags` VARCHAR(250) NOT NULL default '',
  `metatitle` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `autor` (`autor`),
  KEY `alt_name` (`alt_name`),
  KEY `category` (`category`),
  KEY `approve` (`approve`),
  KEY `allow_main` (`allow_main`),
  KEY `date` (`date`),
  KEY `symbol` (`symbol`),
  KEY `comm_num` (`comm_num`),
  KEY `tags` (`tags`),
  KEY `fixed` (`fixed`),
  FULLTEXT KEY `short_story` (`short_story`,`full_story`,`xfields`,`title`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post_extras";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_post_extras (
  `eid` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL DEFAULT '0',
  `news_read` int(11) NOT NULL DEFAULT '0',
  `allow_rate` tinyint(1) NOT NULL DEFAULT '1',
  `rating` int(11) NOT NULL DEFAULT '0',
  `vote_num` int(11) NOT NULL DEFAULT '0',
  `votes` tinyint(1) NOT NULL DEFAULT '0',
  `view_edit` tinyint(1) NOT NULL DEFAULT '0',
  `disable_index` tinyint(1) NOT NULL DEFAULT '0',
  `related_ids` varchar(255) NOT NULL DEFAULT '',
  `access` varchar(150) NOT NULL DEFAULT '',
  `editdate` int(11) NOT NULL DEFAULT '0',
  `editor` varchar(40) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eid`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_static";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_static (
  `id` MEDIUMINT(8) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `template` text NOT NULL,
  `allow_br` tinyint(1) NOT NULL default '0',
  `allow_template` tinyint(1) NOT NULL default '0',
  `grouplevel` varchar(100) NOT NULL default 'all',
  `tpl` varchar(40) NOT NULL default '',
  `metadescr` varchar(200) NOT NULL default '',
  `metakeys` text NOT NULL,
  `views` mediumint(8) NOT NULL default '0',
  `template_folder` varchar(50) NOT NULL default '',
  `date` int(11) unsigned NOT NULL default '0',
  `metatitle` varchar(255) NOT NULL default '',
  `allow_count` tinyint(1) NOT NULL default '1',
  `sitemap` tinyint(1) NOT NULL default '1',
  `disable_index` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  FULLTEXT KEY `template` (`template`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_users";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_users (
  `email` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `user_id` int(11) NOT NULL auto_increment,
  `news_num` mediumint(8) NOT NULL default '0',
  `comm_num` mediumint(8) NOT NULL default '0',
  `user_group` smallint(5) NOT NULL default '4',
  `lastdate` varchar(20) default NULL,
  `reg_date` varchar(20) default NULL,
  `banned` varchar(5) NOT NULL default '',
  `allow_mail` tinyint(1) NOT NULL default '1',
  `info` text NOT NULL,
  `signature` text NOT NULL,
  `foto` varchar(255) NOT NULL default '',
  `fullname` varchar(100) NOT NULL default '',
  `land` varchar(100) NOT NULL default '',
  `favorites` text NOT NULL,
  `pm_all` smallint(5) NOT NULL default '0',
  `pm_unread` smallint(5) NOT NULL default '0',
  `time_limit` varchar(20) NOT NULL default '',
  `xfields` text NOT NULL,
  `allowed_ip` varchar(255) NOT NULL default '',
  `hash` varchar(32) NOT NULL default '',
  `logged_ip` varchar(40) NOT NULL default '',
  `restricted` TINYINT(1) NOT NULL default '0',
  `restricted_days` SMALLINT(4) NOT NULL default '0',
  `restricted_date` VARCHAR(15) NOT NULL default '',
  `timezone` VARCHAR(100) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_banned";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_banned (
  `id` smallint(5) NOT NULL auto_increment,
  `users_id` mediumint(8) NOT NULL default '0',
  `descr` text NOT NULL,
  `date` varchar(15) NOT NULL default '',
  `days` smallint(4) NOT NULL default '0',
  `ip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`users_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_files";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_files (
  `id`  MEDIUMINT(8) NOT NULL auto_increment,
  `news_id` int(10) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  `onserver` varchar(250) NOT NULL default '',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  `dcount` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_usergroups";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_usergroups (
  `id` smallint(5) NOT NULL auto_increment,
  `group_name` varchar(32) NOT NULL default '',
  `allow_cats` text NOT NULL,
  `allow_adds` tinyint(1) NOT NULL default '1',
  `cat_add` text NOT NULL,
  `allow_admin` tinyint(1) NOT NULL default '0',
  `allow_addc` tinyint(1) NOT NULL default '0',
  `allow_editc` tinyint(1) NOT NULL default '0',
  `allow_delc` tinyint(1) NOT NULL default '0',
  `edit_allc` tinyint(1) NOT NULL default '0',
  `del_allc` tinyint(1) NOT NULL default '0',
  `moderation` tinyint(1) NOT NULL default '0',
  `allow_all_edit` tinyint(1) NOT NULL default '0',
  `allow_edit` tinyint(1) NOT NULL default '0',
  `allow_pm` tinyint(1) NOT NULL default '0',
  `max_pm` smallint(5) NOT NULL default '0',
  `max_foto` VARCHAR(10) NOT NULL default '',
  `allow_files` tinyint(1) NOT NULL default '0',
  `allow_hide` tinyint(1) NOT NULL default '1',
  `allow_short` tinyint(1) NOT NULL default '0',
  `time_limit` tinyint(1) NOT NULL default '0',
  `rid` smallint(5) NOT NULL default '0',
  `allow_fixed` tinyint(1) NOT NULL default '0',
  `allow_feed`  tinyint(1) NOT NULL default '1',
  `allow_search`  tinyint(1) NOT NULL default '1',
  `allow_poll`  tinyint(1) NOT NULL default '1',
  `allow_main`  tinyint(1) NOT NULL default '1',
  `captcha`  tinyint(1) NOT NULL default '0',
  `icon` varchar(200) NOT NULL default '',
  `allow_modc`  tinyint(1) NOT NULL default '0',
  `allow_rating` tinyint(1) NOT NULL default '1',
  `allow_offline` tinyint(1) NOT NULL default '0',
  `allow_image_upload` tinyint(1) NOT NULL default '0',
  `allow_file_upload` tinyint(1) NOT NULL default '0',
  `allow_signature` tinyint(1) NOT NULL default '0',
  `allow_url` tinyint(1) NOT NULL default '1',
  `news_sec_code` tinyint(1) NOT NULL default '1',
  `allow_image` tinyint(1) NOT NULL default '0',
  `max_signature` SMALLINT(6) NOT NULL default '0',
  `max_info` SMALLINT(6) NOT NULL default '0',
  `admin_addnews` tinyint(1) NOT NULL default '0',
  `admin_editnews` tinyint(1) NOT NULL default '0',
  `admin_comments` tinyint(1) NOT NULL default '0',
  `admin_categories` tinyint(1) NOT NULL default '0',
  `admin_editusers` tinyint(1) NOT NULL default '0',
  `admin_wordfilter` tinyint(1) NOT NULL default '0',
  `admin_xfields` tinyint(1) NOT NULL default '0',
  `admin_userfields` tinyint(1) NOT NULL default '0',
  `admin_static` tinyint(1) NOT NULL default '0',
  `admin_editvote` tinyint(1) NOT NULL default '0',
  `admin_newsletter` tinyint(1) NOT NULL default '0',
  `admin_blockip` tinyint(1) NOT NULL default '0',
  `admin_banners` tinyint(1) NOT NULL default '0',
  `admin_rss` tinyint(1) NOT NULL default '0',
  `admin_iptools` tinyint(1) NOT NULL default '0',
  `admin_rssinform` tinyint(1) NOT NULL default '0',
  `admin_googlemap` tinyint(1) NOT NULL default '0',
  `allow_html` tinyint(1) NOT NULL default '1',
  `group_prefix` text NOT NULL,
  `group_suffix` text NOT NULL,
  `allow_subscribe` tinyint(1) NOT NULL default '0',
  `allow_image_size` tinyint(1) NOT NULL default '0',
  `cat_allow_addnews` text NOT NULL,
  `flood_news` smallint(6) NOT NULL default '0',
  `max_day_news` smallint(6) NOT NULL default '0',
  `force_leech` tinyint(1) NOT NULL default '0',
  `edit_limit` smallint(6) NOT NULL default '0',
  `captcha_pm` tinyint(1) NOT NULL default '0',
  `max_pm_day` smallint(6) NOT NULL default '0',
  `max_mail_day` smallint(6) NOT NULL default '0',
  `admin_tagscloud` tinyint(1) NOT NULL default '0',
  `allow_vote` tinyint(1) NOT NULL default '0',
  `admin_complaint` tinyint(1) NOT NULL default '0',
  `news_question` tinyint(1) NOT NULL default '0',
  `comments_question` tinyint(1) NOT NULL default '0',
  `max_comment_day` smallint(6) NOT NULL default '0',
  `max_images` smallint(6) NOT NULL default '0',
  `max_files` smallint(6) NOT NULL default '0',
  `disable_news_captcha` smallint(6) NOT NULL default '0',
  `disable_comments_captcha` smallint(6) NOT NULL default '0',
  `pm_question` tinyint(1) NOT NULL default '0',
  `captcha_feedback` tinyint(1) NOT NULL default '1',
  `feedback_question` tinyint(1) NOT NULL default '0',
  `files_type` varchar(255) NOT NULL default '',
  `max_file_size` mediumint(9) NOT NULL default '0',
  `files_max_speed` smallint(6) NOT NULL default '0',
  `allow_lostpassword` tinyint(1) NOT NULL default '1',
  `spamfilter` tinyint(1) NOT NULL default '2',
  `allow_comments_rating` tinyint(1) NOT NULL default '1',
  `max_edit_days` tinyint(1) NOT NULL default '0',
  `spampmfilter` tinyint(1) NOT NULL default '0',
  `force_reg` TINYINT(1) NOT NULL default '0',
  `force_reg_days` MEDIUMINT(9) NOT NULL default '0',
  `force_reg_group` SMALLINT(6) NOT NULL default '4',
  `force_news` TINYINT(1) NOT NULL default '0',
  `force_news_count` MEDIUMINT(9) NOT NULL default '0',
  `force_news_group` SMALLINT(6) NOT NULL default '4',
  `force_comments` TINYINT(1) NOT NULL default '0',
  `force_comments_count` MEDIUMINT(9) NOT NULL default '0',
  `force_comments_group` SMALLINT(6) NOT NULL default '4',
  `force_rating` TINYINT(1) NOT NULL default '0',
  `force_rating_count` MEDIUMINT(9) NOT NULL default '0',
  `force_rating_group` SMALLINT(6) NOT NULL default '4',
  `not_allow_cats` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_poll";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `frage` varchar(200) NOT NULL default '',
  `body` text NOT NULL,
  `votes` mediumint(8) NOT NULL default '0',
  `multiple` tinyint(1) NOT NULL default '0',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_poll_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll_log (
  `id` int(10) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `member` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`,`member`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_banners";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_banners (
  `id` smallint(5) NOT NULL auto_increment,
  `banner_tag` varchar(40) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `code` text NOT NULL,
  `approve` tinyint(1) NOT NULL default '0',
  `short_place` tinyint(1) NOT NULL default '0',
  `bstick` tinyint(1) NOT NULL default '0',
  `main` tinyint(1) NOT NULL default '0',
  `category` VARCHAR(255) NOT NULL default '',
  `grouplevel` varchar(100) NOT NULL default 'all',
  `start` varchar(15) NOT NULL default '',
  `end` varchar(15) NOT NULL default '',
  `fpage` tinyint(1) NOT NULL default '0',
  `innews` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rss";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rss (
  `id` smallint(5) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `allow_main` tinyint(1) NOT NULL default '0',
  `allow_rating` tinyint(1) NOT NULL default '0',
  `allow_comm` tinyint(1) NOT NULL default '0',
  `text_type` tinyint(1) NOT NULL default '0',
  `date` tinyint(1) NOT NULL default '0',
  `search` text NOT NULL,
  `max_news` tinyint(3) NOT NULL default '0',
  `cookie` text NOT NULL,
  `category` smallint(5) NOT NULL default '0',
  `lastdate` INT(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_views";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_views (
  `id` int(11) NOT NULL auto_increment,
  `news_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";


$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rssinform";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rssinform (
  `id` smallint(5) NOT NULL auto_increment,
  `tag` varchar(40) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `category` varchar(200) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `template` varchar(40) NOT NULL default '',
  `news_max` smallint(5) NOT NULL default '0',
  `tmax` smallint(5) NOT NULL default '0',
  `dmax` smallint(5) NOT NULL default '0',
  `approve` tinyint(1) NOT NULL default '1',
  `rss_date_format` VARCHAR(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_notice";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_notice (
  `id` mediumint(8) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `notice` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_static_files";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_static_files (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `static_id` mediumint(8) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(50) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `onserver` varchar(250) NOT NULL default '',
  `dcount` mediumint(8) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `static_id` (`static_id`),
  KEY `onserver` (`onserver`),
  KEY `author` (`author`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_tags";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_tags (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `tag` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_post_log (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `expires` varchar(15) NOT NULL default '',
  `action` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `expires` (`expires`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_admin_sections";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_admin_sections (
  `id` mediumint(8) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `icon` varchar(255) NOT NULL default '',
  `allow_groups` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_subscribe";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_subscribe (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `email`  varchar(50) NOT NULL default '',
  `news_id` int(11) NOT NULL default '0',
  `hash` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_sendlog";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_sendlog (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) NOT NULL DEFAULT '',
  `date` INT(11) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `date` (`date`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_login_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_login_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL DEFAULT '',
  `count` smallint(6) NOT NULL DEFAULT '0',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_mail_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_mail_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `hash` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_complaint";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_complaint (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NOT NULL DEFAULT '0',
  `c_id` int(11) NOT NULL DEFAULT '0',
  `n_id` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `from` varchar(40) NOT NULL DEFAULT '',
  `to` varchar(255) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `c_id` (`c_id`),
  KEY `p_id` (`p_id`),
  KEY `n_id` (`n_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_ignore_list";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_ignore_list (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL default '0',
  `user_from` VARCHAR(40) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `user_from` (`user_from`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_admin_logs";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_admin_logs (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `action` int(11) NOT NULL DEFAULT '0',
  `extras` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_question";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_question (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_read_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_read_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_spam_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_spam_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL DEFAULT '',
  `is_spammer` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `is_spammer` (`is_spammer`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_links";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_links (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `only_one` tinyint(1) NOT NULL DEFAULT '0',
  `replacearea` tinyint(1) NOT NULL DEFAULT '1',
  `rcount` tinyint(3) NOT NULL DEFAULT '0',
  `targetblank` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_social_login";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_social_login (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(40) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `password` varchar(32) NOT NULL DEFAULT '',
  `provider` varchar(15) NOT NULL DEFAULT '',
  `wait` tinyint(1) NOT NULL DEFAULT '0',
  `waitlogin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_comment_rating_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_comment_rating_log (
  `id` int unsigned NOT NULL auto_increment,
  `c_id` int NOT NULL default '0',
  `member` varchar(40) NOT NULL default '',
  `ip` varchar(40) NOT NULL default '',
  `rating` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `c_id` (`c_id`),
  KEY `member` (`member`),
  KEY `ip` (`ip`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_xfsearch";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_xfsearch (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `tagname` varchar(50) NOT NULL default '',
  `tagvalue` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `tagname` (`tagname`),
  KEY `tagvalue` (`tagvalue`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

if (strpos($url, "//") === 0) $url = "http:".$url;
elseif (strpos($url, "/") === 0) $url = "http://".$_SERVER['HTTP_HOST'].$url;

$tableSchema[] = "INSERT INTO " . PREFIX . "_rssinform VALUES (1, 'dle', 'News from Google', '0', 'https://news.google.com/news?cf=all&hl=en&pz=1&ned=en&topic=h&num=3&output=rss', 'informer', 3, 0, 200, 1, 'j F Y H:i')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (1, 'Administrators', 'all', 1, 'all', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_1.gif', 0, 1, 1, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,1,'<b><span style=\"color:red\">','</span></b>',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,exe,doc,pdf,swf', 4096, 0, 0, 2, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (2, 'Chief Editors', 'all', 1, 'all', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 2, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_2.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,exe,doc,pdf,swf', 4096, 0, 1, 2, 1, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 2, 0, 0, 2, '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (3, 'Journalists', 'all', 1, 'all', 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 50, 101, 1, 1, 1, 0, 3, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_3.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,exe,doc,pdf,swf', 4096, 0, 1, 2, 1, 0, 0, 0, 0, 3, 0, 0, 3, 0, 0, 3, 0, 0, 3, '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (4, 'Visitors', 'all', 1, 'all', 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 20, 101, 1, 1, 1, 0, 4, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_4.gif', 0, 1, 0, 1, 0, 1, 1, 1, 0,500,1000,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,0,'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,exe,doc,pdf,swf', 4096, 0, 1, 2, 1, 0, 2, 0, 0, 4, 0, 0, 4, 0, 0, 4, 0, 0, 4, '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (5, 'Guests', 'all', 0, 'all', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 5, 0, 1, 1, 1, 0, 1, '{THEME}/images/icon_5.gif', 0, 1, 0, 0, 0, 0, 1, 1, 0,1,1,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0,'','',0,0,'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', 0, 0, 0, 2, 1, 0, 2, 0, 0, 5, 0, 0, 5, 0, 0, 5, 0, 0, 5, '')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_rss VALUES (1, 'http://dle-news.com/rss.xml', 'DataLife Engine Official Website', 1, 1, 1, 1, 1, '<div class=\"full-post-content row\">{get}</div><div class=\"full-post-footer ignore-select\">', 5, '', 1, 0)";

$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (1, 'reg_mail', '{%username%},\r\n\r\nThis letter was sent from the $url\r\n\r\nYou are receiving this email because this e-mail address was used for registration on the website. If you are not registered on this website, just ignore this email and delete it. You will not get this letter in the future.\r\n\r\n------------------------------------------------\r\nYour username and password on the website:\r\n------------------------------------------------\r\n\r\nUsername: {%username%}\r\nPassword: {%password%}\r\n\r\n------------------------------------------------\r\nActivation Instructions\r\n------------------------------------------------\r\n\r\nThank you for registering.\r\nWe require you to confirm your registration to verify that e-mail address that you have entered is real. This is required to protect against unwanted spam and abuse.\r\n\r\nTo activate your account, go to the following link:\r\n\r\n{%validationlink%}\r\n\r\nIf these actions do not work, maybe your account has been deleted. In this case, contact the administrator to resolve the problem.\r\n\r\nSincerely,\r\n\r\nAdministration $url.', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (2, 'feed_mail', '{%username_to%},\r\n\r\n{%username_from%} has sent this letter from $url\r\n\r\n------------------------------------------------\r\nMessage text\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nIP address of the sender: {%ip%}\r\nGroup: {%group%}\r\n\r\n------------------------------------------------\r\nRemember that website administration is not responsible for the content of this letter\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (3, 'lost_mail', 'Dear {%username%},\r\n\r\nYou have requested the password recovery on $url However, passwords are stored in encrypted form for security, so we can not tell you your old password. If you want generate a new password, go to the following link: \r\n\r\n{%lostlink%}\r\n\r\nIf you did not make a request for a password recovery, then simply delete this email. Your password in a safe place and is inaccessible to unauthorized persons.\r\n\r\nIP address of sender: {%ip%}\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (4, 'new_news', 'Dear Administrator,\r\n\r\nThe article was added on $url, which is currently awaiting moderation.\r\n\r\n------------------------------------------------\r\nSummary of the article\r\n------------------------------------------------\r\n\r\nAuthor: {%username%}\r\nArticle title: {%title%}\r\nCategory: {%category%}\r\nDate: {%date%}\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (5, 'comments', 'Dear {%username_to%},\r\n\r\nThe comment for the article that you have subscribed to was added on $url.\r\n\r\n------------------------------------------------\r\nSummary of the comment\r\n------------------------------------------------\r\n\r\nAuthor: {%username%}\r\nDate: {%date%}\r\nLink to the article: {%link%}\r\n\r\n------------------------------------------------\r\nComment text\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\n------------------------------------------------\r\n\r\nIf you do not want to receive notifications about new comments to this article, then follow this link: {%unsubscribe%}\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (6, 'pm', 'Dear {%username%},\r\n\r\nYou received a personal message on $url.\r\n\r\n------------------------------------------------\r\nSummary of the message\r\n------------------------------------------------\r\n\r\nSender: {%fromusername%}\r\nDate: {%date%}\r\nSubject: {%title%}\r\n\r\n------------------------------------------------\r\nMessage text\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (7, 'wait_mail', 'Dear {%username%},\r\n\r\nYou have requested the association of you account on $url with the social network account on {%network%}. However, for security reasons you need to confirm this action on the following link: \r\n\r\n------------------------------------------------\r\n{%link%}\r\n------------------------------------------------\r\n\r\nNote! In the case of accounts association, your primary password on the website will be reset, and if you log in using your username and password, your password will no longer be valid.\r\n\r\nIf you did not make this request, then just delete this email. Your account details are stored in a secure place and are inaccessible to unauthorized persons.\r\n\r\nIP address of sender: {%ip%}\r\n\r\nSincerely,\r\n\r\nAdministration $url', 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (8, 'newsletter', '<html>\r\n<head>\r\n<title>{%title%}</title>\r\n<meta content=\"text/html; charset={%charset%}\" http-equiv=Content-Type>\r\n<style type=\"text/css\">\r\nhtml,body{\r\n    font-family: Verdana;\r\n    word-spacing: 0.1em;\r\n    letter-spacing: 0;\r\n    line-height: 1.5em;\r\n    font-size: 11px;\r\n}\r\n\r\np {\r\n	margin:0px;\r\n	padding: 0px;\r\n}\r\n\r\na:active,\r\na:visited,\r\na:link {\r\n	color: #4b719e;\r\n	text-decoration:none;\r\n}\r\n\r\na:hover {\r\n	color: #4b719e;\r\n	text-decoration: underline;\r\n}\r\n</style>\r\n</head>\r\n<body>\r\n{%content%}\r\n</body>\r\n</html>', 0)";


$tableSchema[] = "INSERT INTO " . PREFIX . "_category (name, alt_name, keywords) values ('Information', 'main', '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_banners (banner_tag, descr, code, approve, short_place, bstick, main, category) values ('header', 'Top banner', '<div align=\"center\"><a href=\"http://dle-news.com/\" target=\"_blank\"><img src=\"{$url}templates/Default/images/_banner_.gif\" style=\"border: none;\" alt=\"\" /></a></div>', 1, 0, 0, 0, 0)";

$add_time = time();
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_static (`name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`, `date`) VALUES ('dle-rules-page', 'General rules on the website', '<b>General rules of conduct on the website:</b><br /><br />To begin with, hundreds of people of different religions and beliefs are communicate on the website, and all of them are full-fledged visitors of our website, so if we want a community of people to function, then we need rules. We strongly recommend that you read these rules. It will take just five minutes, but it will save your and our time and will help make the website more interesting and organized.<br /><br />Firstly, you should behave respectfully to all visitors on our website. Do not insult to the participants, it is always unwanted. If you have a complaint - contact administrators or moderators (use personal messages). We considered insulting of other visitors one of the most serious violations and it is severely punished by the administration. <b>Racism, religious and political speech are strictly forbidden.</b> Thank you for your understanding and desire to make our website more polite and friendly.<br /><br /><b>The following is strictly prohibited:</b> <br /><br />- messages not related to the content of the article or to the context of the discussion<br />- insults and threats to other visitors<br />- expressions that contain profanity, degrading, inciting ethnic strife are prohibited in comments<br />- spam and advertising of any goods and services, other resources, media or events not related to the context of discussion of the article<br /><br />Let us respect each other and the site where you and other readers come to talk and express their thoughts. The Administration reserves the right to remove comments, or comment parts, if they do not meet these requirements.<br /><br />If you violate the rules you may be given a <b>warning</b>. In some cases, you may be banned <b>without warning</b>. Contact the Administrator regarding ban removal.<br /><br /><b>Insulting</b> of administrators and moderators is also punished by a <b>ban</b> - Respect other people\'s labor.<br /><br /><div align=\"center\">{ACCEPT-DECLINE}</div>', 1, 1, 'all', '', 'General rules', 'General rules', 0, '', '{$add_time}')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_users (name, password, email, reg_date, lastdate, user_group, news_num, info, signature, favorites, xfields) values ('$reg_username', '$reg_password', '$reg_email', '$add_time', '$add_time', '1', '3', '', '', '', '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_vote (category, vote_num, date, title, body) VALUES ('all', '0', '$thistime', 'Please, rate the engine', 'The best of news engines<br />A good engine<br />It\'s ok, but...<br />I have seen better<br />Don\'t like it at all')";

$title = "Welcome";
$short_story = "<div align=\"center\"><img src=\"".$url."uploads/boxsmall.jpg\" alt=\"\" /></div>Welcome to the demo page of DataLife Engine. DataLife Engine is a multi-user news engine with a high functionality. First of all, the engine is designed to create news blogs and websites with a large information context. However, it has a lot of settings that allow you to use it for any purpose. The engine can be integrated into almost any existing design and it has no limatations for making new templates for it. Another key feature of DataLife Engine - is a low load on system resources. Server load will be minimal even when many users will be online, and you will not experience any problems with the information displaying. The engine is optimized for search engines. You can read about all the functional features on <a href=\"http://dle-news.com/\" target=\"_blank\">our page</a>.<br /><br />Discussion of all issues on the script is conducted <a href=\"http://forum.dle-news.ru/index.php\" target=\"_blank\">here</a>. Also, you can get prompt assistance there.";
$full_story = "";

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('1', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post1', '1', '1', '1', 'by, news')";

$title = "Script purchase and payment";
$short_story = "Dear webmaster, we would like to add something. Before asking our technical support any questions, make sure that you carefully read the documentation on script and did not find the needed answer. We reserve the right to ignore questions received by us from users using a non-commercial version of the script or users who did not pay for the license that includes the technical support service. You can purchase one of two types of DataLife Engine license of your choice:<br /><br />- <b>Basic license.</b> When you purchase this license, you also get the opportunity to receive new versions of script for free within <b>one year</b>.<br /><br />- <b>Advanced license.</b> When you purchase this license you get everything that is included in the basic license and additionally you get technical support of the script and you are allowed to remove copyrights from the user part of the website (that is visible to visitors).<br /><br /><b>Validity of the license</b> is <span style=\"color:#FF0000\">1 year</span>, during which you will receive all the new versions and updates of the script for free, and if you purchase the advanced license you\'ll get the technical support. After expiration of the license, you can extend it, or use the current version of the script without updates.<br /><br /><b>Here you can read how to purchase the script</b> <a href=\"http://dle-news.com/price.html\" target=\"_blank\">http://dle-news.com/price.html</a><br /><br />Note that one license can be used for one domain (project) and it can not be used on other websites. It is also prohibited to give your license file to others.<br /><br /><b>Sincerely,<br /><br />SoftNews Media Group</b>";

$add_time = time()-20;
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('2', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post2', '1', '1', '1', 'by, news')";

$title = "Technical support of the engine";
$short_story = "<b>Technical support is </b> provided by the <a href=\"http://forum.dle-news.ru/index.php\" target=\"_blank\">support forum</a> and via E-Mail. We try to answer all your questions, but due to the large number of visitors, it is not always possible. Therefore, guaranteed technical support is available only to users who have purchased the extended license. <br /><br /><b>Technical support service includes:</b><br /><br />1. Users who faced with the script for the first time and of course do not know all the nuances of its work have the priority of answering the questions. Only failures of the script itself are the responsibility of technical support. If the reason of failure is your template that doesn\'t meet the requirements of the engine, then the support you may be denied.<br /><br />2. Also you get a one-time opportunity to install the script on your server, including setting it up to full operation with current server settings (sometimes it is needed to correctly disable the User-Friendly URL, enable the specific features of Russian Apache for the correct images uploading, etc...).<br /><br />3. You receive a consultancy support on working with the structure of the script. For example, if you want to make small changes to the script for more convenient work for you, you can save time on finding the correct piece of code by just asking us. You will be given an advice where to find it and how to implement your task better. (Note that we do not write additional modules for you, but only help you to understand the structure of the script better, so always ask related questions. Questions like: \"How to make this stuff\" may be ignored by customer support)<br /><br />4. Another common problem is incorrect updating of the script. For example the server has failed during the update, where part of the new data was entered into the database and configurations, and the other part was not entered. As a result, you get a non-working script with all the consequences. In this case, you will be held manual correction of the damaged database structure.<br /><br />If you are not subscribed to additional support services, your questions can be ignored and unanswered.<br /><br /><b>Sincerely,<br /><br />SoftNews Media Group</b>";

$add_time = time()-50;
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('3', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post4', '1', '1', '1', '')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_post_extras (news_id, user_id) values ('1', '1'), ('2', '1'), ('3', '1')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_tags (news_id, tag) values ('1', 'software'), ('2', 'software'), ('3', 'software'), ('1', 'news'), ('2', 'news')";

include ENGINE_DIR.'/classes/mysql.php';
include ENGINE_DIR.'/data/dbconfig.php';

      foreach($tableSchema as $table) {

        $db->query($table);

      }

  echo $skin_header;


echo <<<HTML
<form action="index.php" method="get">
<div class="box">
  <div class="box-header">
    <div class="title">Installation Complete</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
Congratulations, DataLife Engine has been successfully installed on your server. Now you can go to the <a class="status-info" href="index.php">Homepage of your website</a> and try the features of the engine. Or you can <a class="status-info" href="admin.php">enter</a> the DataLife Engine control panel and change other system settings.
<br><br><font color="red">Attention: when you install the engine, the database structure and administrator's account are created, and basic system settings are performed, so you need to delete <b>install.php</b> after the successful installation in order to avoid re-installation of the engine!</font><br><br>
Enjoy your work with the engine,<br><br>
SoftNews Media Group<br><br>
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type=submit value="Next">
	</div>

  </div>
</div>

HTML;

}
else {

	if (@file_exists(ENGINE_DIR.'/data/config.php')) {

		msgbox( "", "Scirpt installation is blocked automatically", "Installed copy of DataLife Engine is detected on the server. If you want to re-install the engine, you must manually delete <b>/engine/data/config.php</b> using FTP protocol." );

		die ();
	}

$_SESSION['dle_install'] = 1;

// ********************************************************************************
// Greeting
// ********************************************************************************

  echo $skin_header;

echo <<<HTML
<form method="get" action="">
<input type="hidden" name="action" value="eula">
<div class="box">
  <div class="box-header">
    <div class="title">DataLife Engine Installation Wizard</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
	Welcome to the DataLife Engine installation wizard. This wizard will help you install the script in just a few minutes. However, we strongly recommend that you to read the documentation on the work of the script, and documentation on its installation, which comes with a script.<br><br>
	Before you begin the installation, please, make sure that all the distribution files was uploaded to the server. Don't forget to set the necessary permissions for folders and files.<br><br>
	Please note that DataLife Engine supports User-Friendly URL, and it requires <b>modrewrite</b> module to be installed and allowed for use. If you want to disable this feature, then delete <b>.htaccess</b> from the root directory and disable support for this feature during the script installation process.<br><br>
	<font color="red">Attention: when you install the engine, the database structure and administrator's account are created, and basic system settings are performed, so you need to delete <b>install.php</b> after the successful installation in order to avoid re-installation of the engine!</font><br><br>
	Enjoy your work with the engine,<br><br>
	SoftNews Media Group
	</div>
	<div class="row box-section">
		<input class="btn btn-green" type=submit value="Start the installation">
	</div>

  </div>
</div>
</form>
HTML;
}


echo $skin_footer;
?>