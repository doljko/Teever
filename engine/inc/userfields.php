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
 Файл: userfields.php
-----------------------------------------------------
 Назначение: дополнительные поля профиля
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if (!isset($xfieldsaction)) $xfieldsaction = $_REQUEST['xfieldsaction'];
if (isset ( $_REQUEST['xfieldssubactionadd'] )) $xfieldssubactionadd = $_REQUEST['xfieldssubactionadd'];
if (isset ( $_REQUEST['xfieldssubaction'] )) $xfieldssubaction = $_REQUEST['xfieldssubaction'];
if (isset ( $_REQUEST['xfieldsindex'] )) $xfieldsindex = intval($_REQUEST['xfieldsindex']);
if (isset ( $_REQUEST['editedxfield'] )) $editedxfield = $_REQUEST['editedxfield'];

if (isset ($xfieldssubactionadd))
if ($xfieldssubactionadd == "add") {
  $xfieldssubaction = $xfieldssubactionadd;
}

if (!isset($xf_inited)) $xf_inited = "";

if ($xf_inited !== true) { // Prevent "Cannot redeclare" error

	function profilesave($data) {
		global $lang, $dle_login_hash;
	
		if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {
	
			  die("Hacking attempt! User not found");
	
		}
	
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
	  
	    $filehandle = fopen(ENGINE_DIR.'/data/xprofile.txt', "w+");
	    if (!$filehandle)
	    msg("error", $lang['xfield_error'], "$lang[xfield_err_1] \"engine/data/xprofile.txt\", $lang[xfield_err_2]");
	
		$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/onmouseenter/i', '/onwheel/i', '/onshow/i');
		$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow');
		
		$filecontents = preg_replace( $find, $replace, $filecontents );
		$filecontents = preg_replace( "#<iframe#i", "&lt;iframe", $filecontents );
		$filecontents = preg_replace( "#<script#i", "&lt;script", $filecontents );
		$filecontents = str_replace( "<?", "&lt;?", $filecontents );
		$filecontents = str_replace( "?>", "?&gt;", $filecontents );
		$filecontents = str_replace( "$", "&#036;", $filecontents );
	
	    fwrite($filehandle, $filecontents);
	    fclose($filehandle);
	    header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .
	        "?mod=userfields&xfieldsaction=configure");
	    exit;
	}

	function profileload() {
	  global $lang;
	  $path = ENGINE_DIR.'/data/xprofile.txt';
	  $filecontents = file($path);
	
	    if (!is_array($filecontents)) $filecontents = array();
	  
	    foreach ($filecontents as $name => $value) {
	      $filecontents[$name] = explode("|", trim($value));
	      foreach ($filecontents[$name] as $name2 => $value2) {
	        $value2 = str_replace("&#124;", "|", $value2); 
	        $value2 = str_replace("__NEWL__", "\r\n", $value2);
	        $filecontents[$name][$name2] = $value2;
	      }
	    }
	    return $filecontents;
	}


	$xf_inited = true;
}

$xfields = profileload();

switch ($xfieldsaction) {
  case "configure":

	if( ! $user_group[$member_id['user_group']]['admin_userfields'] ) {
		msg( "error", $lang['index_denied'], $lang['index_denied'] );
	}

    switch ($xfieldssubaction) {
      case "delete":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_5'],"javascript:history.go(-1)");
        }
        msg("options", $lang['p_confirm'], "$lang[xfield_err_6]<br /><br /><input onclick=\"document.location='?mod=userfields&xfieldsaction=configure&xfieldsindex={$xfieldsindex}&xfieldssubaction=delete2&user_hash={$dle_login_hash}'\" type=\"button\" class=\"btn btn-green\" value=\"{$lang['opt_sys_yes']}\">&nbsp;&nbsp;<input onclick=\"document.location='?mod=userfields&xfieldsaction=configure'\" type=\"button\" class=\"btn btn-red\" value=\"{$lang['opt_sys_no']}\">");
        break;
      case "delete2":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_5'],"javascript:history.go(-1)");
        }
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '71', '{$xfields[$xfieldsindex][0]}')" );

        unset($xfields[$xfieldsindex]);
        @profilesave($xfields);
        break;
      case "add":
        $xfieldsindex = count($xfields);
        // Fall trough to edit
      case "edit":

        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_8'],"javascript:history.go(-1)");
        }
  
        if (!$editedxfield) {
          $editedxfield = $xfields[$xfieldsindex];
        } elseif (strlen(trim($editedxfield[0])) > 0 and
            strlen(trim($editedxfield[1])) > 0) {
          foreach ($xfields as $name => $value) {
            if ($name != $xfieldsindex and
                $value[0] == $editedxfield[0]) {
              msg("error", $lang['xfield_error'], $lang['xfield_err_9'],"javascript:history.go(-1)");
            }
          }
          $editedxfield[0] = totranslit(trim($editedxfield[0]));
          $editedxfield[1] = htmlspecialchars(trim($editedxfield[1]), ENT_QUOTES, $config['charset']);
          $editedxfield[2] = intval($editedxfield[2]);
          $editedxfield[4] = intval($editedxfield[4]);
          $editedxfield[5] = intval($editedxfield[5]);

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '72', '{$editedxfield[0]}')" );

          if ($editedxfield[3] == "select") {
            $options = array();
            foreach (explode("\r\n", $editedxfield["6_select"]) as $name => $value) {
              $value = trim($value);
              if (!in_array($value, $options)) {
                $options[] = $value;
              }
            }
            if (count($options) < 2) {
            msg("error", $lang['xfield_error'], $lang['xfield_err_10'],"javascript:history.go(-1)");
            }
            $editedxfield[6] = implode("\r\n", $options);
          } else { $editedxfield[6] = ""; }

          unset($editedxfield['6_select']);

          ksort($editedxfield);
          
          $xfields[$xfieldsindex] = $editedxfield;
          ksort($xfields);

          @profilesave($xfields);
          break;
        } else {
          msg("error", $lang['xfield_error'], $lang['xfield_err_11'],"javascript:history.go(-1)");
        }

        echoheader( "<i class=\"icon-user\"></i>".$lang['header_uf_1'], $lang['header_uf_2'] );

?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="xfieldsform" class="form-horizontal">
      <script language="javascript">
      function ShowOrHideEx(id, show) {
        var item = null;
        if (document.getElementById) {
          item = document.getElementById(id);
        } else if (document.all) {
          item = document.all[id];
        } else if (document.layers){
          item = document.layers[id];
        }
        if (item && item.style) {
          item.style.display = show ? "" : "none";
        }
      }
      function onTypeChange(value) {
        ShowOrHideEx("select_options", value == "select");
      }
      </script>
      <input type="hidden" name="mod" value="userfields">
      <input type="hidden" name="user_hash" value="<?php echo $dle_login_hash; ?>">
      <input type="hidden" name="xfieldsaction" value="configure">
      <input type="hidden" name="xfieldssubaction" value="edit">
      <input type="hidden" name="xfieldsindex" value="<?php echo $xfieldsindex; ?>">
<div class="box">
  <div class="box-header">
    <div class="title"><?php echo $lang['xfield_title']; ?></div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xfield_xname']; ?></label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width: 200px;" type="text" name="editedxfield[0]" value="<?php echo $editedxfield[0];?>" /> <i class="icon-warning-sign"></i> <?php echo $lang['xf_lat']; ?>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xfield_xdescr']; ?></label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width: 350px;" type="text" name="editedxfield[1]" value="<?php echo $editedxfield[1];?>" />
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xfield_xtype']; ?></label>
		  <div class="col-lg-10">
			<select class="uniform" name="editedxfield[3]" id="type" onchange="onTypeChange(this.value)">
          <option value="text"<?php echo ($editedxfield[3] != "text") ? " selected" : ""; ?>><?php echo $lang['xfield_xstr']; ?></option>
          <option value="textarea"<?php echo ($editedxfield[3] == "textarea") ? " selected" : ""; ?>><?php echo $lang['xfield_xarea']; ?></option>
          <option value="select"<?php echo ($editedxfield[3] == "select") ? " selected" : ""; ?>><?php echo $lang['xfield_xsel']; ?></option>
        </select>
		  </div>
		 </div>	
		<div class="form-group" id="select_options">
		  <label class="control-label col-lg-2"><?php echo $lang['xfield_xfaul']; ?></label>
		  <div class="col-lg-10">
			<textarea style="width:100%;max-width: 350px; height: 100px;" name="editedxfield[6_select]"><?php echo ($editedxfield[3] == "select") ? $editedxfield[6] : ""; ?></textarea><br><?php echo $lang['xfield_xfsel']; ?>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xp_reg']; ?></label>
		  <div class="col-lg-10">
			<input class="icheck" type="radio" id="yes1" name="editedxfield[2]" <?php echo ($editedxfield[2]) ? "checked" : ""; ?> value="1"><label for="yes1"><?php echo $lang['opt_sys_yes']; ?></label>&nbsp;&nbsp;&nbsp;<input class="icheck" type="radio" id="no1" name="editedxfield[2]" <?php echo (!$editedxfield[2]) ? "checked" : ""; ?> value="0"><label for="no1"><?php echo $lang['opt_sys_no']; ?>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="<?php echo $lang['xp_reg_hint']; ?>" >?</span></label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xp_edit']; ?></label>
		  <div class="col-lg-10">
			<input class="icheck" type="radio" id="yes2" name="editedxfield[4]" <?php echo ($editedxfield[4]) ? "checked" : ""; ?> value="1"><label for="yes2"><?php echo $lang['opt_sys_yes']; ?></label>&nbsp;&nbsp;&nbsp;<input class="icheck" type="radio" id="no2" name="editedxfield[4]" <?php echo (!$editedxfield[4]) ? "checked" : ""; ?> value="0"><label for="no2"><?php echo $lang['opt_sys_no']; ?>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="<?php echo $lang['xp_edit_hint']; ?>" >?</span></label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"><?php echo $lang['xp_privat']; ?></label>
		  <div class="col-lg-10">
			<input class="icheck" type="radio" id="yes3" name="editedxfield[5]" <?php echo ($editedxfield[5]) ? "checked" : ""; ?> value="1"><label for="yes3"><?php echo $lang['opt_sys_yes']; ?></label>&nbsp;&nbsp;&nbsp;<input class="icheck" type="radio" id="no3" name="editedxfield[5]" <?php echo (!$editedxfield[5]) ? "checked" : ""; ?> value="0"><label for="no3"><?php echo $lang['opt_sys_no']; ?>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="<?php echo $lang['xp_privat_hint']; ?>" >?</span></label>
		  </div>
		 </div>
		 
	</div>
	
   </div>
<div class="box-footer padded">
<input type="submit" class="btn btn-green" value=" <?php echo $lang['user_save']; ?> ">
</div>
</div>
</form>
    <script type="text/javascript">
    <!--
      var item_type = null;
      if (document.getElementById) {
        item_type = document.getElementById("type");
      } else if (document.all) {
        item_type = document.all["type"];
      } else if (document.layers) {
        item_type = document.layers["type"];
      }
      if (item_type) {
        onTypeChange(item_type.value);
      }
    // -->
    </script>
<?php
        echofooter();
        break;

      default:

	echoheader( "<i class=\"icon-user\"></i>".$lang['header_uf_1'], $lang['header_uf_2'] );
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="xfieldsform">
<input type="hidden" name="mod" value="userfields">
<input type="hidden" name="user_hash" value="<?php echo $dle_login_hash; ?>">
<input type="hidden" name="xfieldsaction" value="configure">
<input type="hidden" name="xfieldssubactionadd" value="">
<div class="box">
  <div class="box-header">
    <div class="title"><?php echo $lang['xp_xlist']; ?></div>
  </div>
  <div class="box-content">

	<div class="row box-section">
<?php
        if (count($xfields) == 0) {
		
          echo "<center><br /><br />{$lang['xfield_xnof']}<br /><br /></center>";

		  } else {

			$x_list = "<ol class=\"dd-list\">";
	
			foreach ($xfields as $name => $value) {
		
				if ( $value[3] == "text" ) $type=$lang['xfield_xstr'];
				elseif($value[3] == "textarea") $type=$lang['xfield_xarea'];
				elseif($value[3] == "select") $type=$lang['xfield_xsel'];
	
				$p1 = $value[2] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no'];
				$p2 = $value[4] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no'];
				$p3 = $value[5] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no'];
	
				$x_list .= "<li class=\"dd-item\" data-id=\"{$name}\"><div class=\"dd-handle\"><b id=\"x_uname\" class=\"s-el\">{$value[0]}</b><b id=\"x_cats\" class=\"s-el\">{$lang['xp_descr']}: {$value[1]}</b><b id=\"x_utype\" class=\"s-el\">{$lang['xfield_xtype']}: {$type}</b><b id=\"x_par\" class=\"s-el\">{$lang['xp_regh']}: {$p1}</b><b id=\"x_par\" class=\"s-el\">{$lang['xp_edith']}: {$p2}</b><b id=\"x_l\" class=\"s-el\">{$lang['xp_privath']}: {$p3}</b><span><a href=\"?mod=userfields&xfieldsaction=configure&xfieldssubaction=edit&xfieldsindex={$name}&user_hash={$dle_login_hash}\"><i title=\"{$lang['cat_ed']}\" alt=\"{$lang['cat_ed']}\" class=\"icon-pencil bigger-130\"></i></a>&nbsp;&nbsp;<a class=\"maintitle\" href=\"?mod=userfields&xfieldsaction=configure&xfieldssubaction=delete&xfieldsindex={$name}&user_hash={$dle_login_hash}\"><i title=\"{$lang['cat_del']}\" alt=\"{$lang['cat_del']}\" class=\"icon-trash bigger-130 status-error\"></i></a></span></div></li>";		
	
			}

			$x_list .= "</ol>";
			echo "<div class=\"dd\">{$x_list}</div>";

        }
?>
	</div>
	
   </div>
	<div class="box-footer padded">
		<div class="pull-left">
	<?php if (count($xfields) > 0) { ?>
		<button id="xfsort" class="btn btn-blue"><?php echo $lang['xf_posi']; ?></button>
	<?php } ?>
		<input type="submit" class="btn btn-green" value=" <?php echo $lang['b_create']; ?> " onclick="document.forms['xfieldsform'].xfieldssubactionadd.value = 'add';">
		</div>
		<div class="pull-right">
		<a class="status-info" onclick="javascript:Help('xprofile')" href="#"><?php echo $lang['xfield_xhelp']; ?></a>
		</div>
	</div>
</div>
</form>
<script>
	$(document).ready(function(){

		$('.dd').nestable({
			maxDepth: 1
		});
		
		$('.dd-handle a').on('mousedown', function(e){
			e.stopPropagation();
		});
		
		$('#xfsort').click(function(){
			var xfsort = window.JSON.stringify($('.dd').nestable('serialize'));
			var url = "action=userxfsort&user_hash=<?php echo $dle_login_hash; ?>&list="+xfsort;

			ShowLoading('');
			$.post('engine/ajax/adminfunction.php', url, function(data){
	
				HideLoading('');
	
				if (data == 'ok') {

					document.location.reload(false);

				} else {

					DLEalert('<?php echo $lang['cat_sort_fail']; ?>', '<?php echo $lang['p_info']; ?>');

				}
	
			});

			return false;

		});

	});
</script>
<?php
      echofooter();
    }
    break;
case "list":
    $output = "";
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);
	$xfieldinput = array();
	
    foreach ($xfields as $name => $value) {
      $fieldname = $value[0];

      if (!$xfieldsadd) {
        $fieldvalue = $xfieldsdata[$value[0]];
        $fieldvalue = $parse->decodeBBCodes($fieldvalue, false);
		if ((!$xfieldsadd) AND !intval($value[4]) AND ($is_logged AND $member_id['user_group'] != 1)) continue;
      }

if (intval($value[2]) OR (!$xfieldsadd)) {

     if ($value[3] == "textarea") {      

		if ( isset($adminmode) ) {
		
		      $output .= <<<HTML
				<div class="form-group">
				  <label class="col-lg-2">{$value[1]}:</label>
				  <div class="col-lg-10">
					<textarea name="xfield[$fieldname]" id="xf_$fieldname" style="width:100%;max-width:350px;height:100px;">{$fieldvalue}</textarea>
				  </div>
				 </div>
HTML;
		
		} else {

      $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><textarea name="xfield[$fieldname]" id="xf_$fieldname">$fieldvalue</textarea></td></tr>
HTML;

			$xfieldinput[$fieldname] = "<textarea name=\"xfield[$fieldname]\" id=\"xf_$fieldname\">$fieldvalue</textarea>";
		}

      } elseif ($value[3] == "text") {

		if ( isset($adminmode) ) {
		
		      $output .= <<<HTML
				<div class="form-group">
				  <label class="col-lg-2">{$value[1]}:</label>
				  <div class="col-lg-10">
					<input style="width:100%;max-width:350px;" type="text" name="xfield[$fieldname]" id="xfield[$fieldname]" value="$fieldvalue" />
				  </div>
				 </div>
HTML;
		
		} else {

        	$output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><input type="text" name="xfield[$fieldname]" id="xfield[$fieldname]" value="$fieldvalue" /></td>
</tr>
HTML;

			$xfieldinput[$fieldname] = "<input type=\"text\" name=\"xfield[$fieldname]\" id=\"xfield[$fieldname]\" value=\"$fieldvalue\" />";
		}

      } elseif ($value[3] == "select") {

		if (isset($adminmode)) {
			$select = "<select name=\"xfield[$fieldname]\" id=\"xf_$fieldname\" class=\"uniform\">";
		} else {
			$select = "<select name=\"xfield[$fieldname]\" id=\"xf_$fieldname\">";
		}
		
		$fieldvalue = str_replace('&amp;', '&', $fieldvalue);
		$fieldvalue = str_replace('&quot;', '"', $fieldvalue);
		
        foreach (explode("\r\n", $value[6]) as $index1 => $value1) {

		  $value1 = str_replace("'", "&#039;", $value1);
		  $value1 = explode("|", $value1);
		  if( count($value1) < 2) $value1[1] = $value1[0];
		  
          $select .= "<option value=\"$index1\"" . ($fieldvalue == $value1[0] ? " selected" : "") . ">{$value1[1]}</option>\r\n";
        }

		$select .= "</select>";

		if ( isset($adminmode) ) {
		
		      $output .= <<<HTML
				<div class="form-group">
				  <label class="col-lg-2">{$value[1]}:</label>
				  <div class="col-lg-10">
					{$select}
				  </div>
				 </div>
HTML;
		
		} else {

        $output .= <<<HTML

<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2">{$select}</td>
</tr>
HTML;

		$xfieldinput[$fieldname] = $select;

		}
      }
}

    }
    break;
case "admin":
    $output = "";
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);
    foreach ($xfields as $name => $value) {
        $fieldname = $value[0];

        $fieldvalue = $xfieldsdata[$value[0]];
        $fieldvalue = $parse->decodeBBCodes($fieldvalue, false);


     if ($value[3] == "textarea") {      
      $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><textarea name="xfield[$fieldname]" id="xf_$fieldname">$fieldvalue</textarea></td></tr>
HTML;
      } elseif ($value[3] == "text") {
        $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><input type="text" name="xfield[$fieldname]" id="xfield[$fieldname]" value="$fieldvalue" /></td>
</tr>
HTML;
      } elseif ($value[3] == "select") {

        $output .= <<<HTML

<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><select name="xfield[$fieldname]" id="xf_$fieldname">
HTML;
        foreach (explode("\r\n", $value[6]) as $index => $value) {

		  $value = str_replace("'", "&#039;", $value);
		  $value = explode("|", $value);
		  if( count($value) < 2) $value[1] = $value[0];
		  
          $output .= "<option value=\"$index\"" . ($fieldvalue == $value[0] ? " selected" : "") . ">{$value[1]}</option>\r\n";
        }

$output .= <<<HTML
</select></td>
</tr>
HTML;
      }

    }
    break;
  case "init":
    $postedxfields = $_POST['xfield'];
    $newpostedxfields = array();
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);

    foreach ($xfields as $name => $value) {
		if ((!$value[2] AND $xfieldsadd)) {
			continue;
		}

		if (intval($value[4]) OR $member_id['user_group'] == 1 OR ($value[2] AND $xfieldsadd))
	      $newpostedxfields[$value[0]] = substr($postedxfields[$value[0]], 0, 10000);
		else
	      $newpostedxfields[$value[0]] = $xfieldsdata[$value[0]];

	    if ($value[3] == "select") {
	        $options = explode("\r\n", $value[6]);

			if (intval($value[4]) OR $member_id['user_group'] == 1 OR ($value[2] AND $xfieldsadd)) {
				$options = explode("|", $options[$postedxfields[$value[0]]] );
		        $newpostedxfields[$value[0]] = $options[0];
			} else {
				$newpostedxfields[$value[0]] = $xfieldsdata[$value[0]];
			}
	    }

	}

    $postedxfields = $newpostedxfields;
    break;
  case "init_admin":
    $postedxfields = $_POST["xfield"];
    $newpostedxfields = array();

    foreach ($xfields as $name => $value) {
		$newpostedxfields[$value[0]] = substr($postedxfields[$value[0]], 0, 10000);

	    if ($value[3] == "select") {
	        $options = explode("\r\n", $value[6]);
			$options = explode("|", $options[$postedxfields[$value[0]]] );
	        $newpostedxfields[$value[0]] = $options[0];
	    }
	}

    $postedxfields = $newpostedxfields;
    break;
  default:
  if (function_exists('msg'))
    msg("error", $lang['xfield_error'], $lang['xfield_xerr2']);
}
?>