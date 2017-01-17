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
 Файл: addnews.php
-----------------------------------------------------
 Назначение: Добавление новости
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_addnews'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( $action == "addnews" ) {

	$id= "";
	$js_array[] = "engine/classes/uploads/html5/fileuploader.js";
	
	echoheader( "<i class=\"icon-file-alt\"></i>".$lang['header_n_title'], $lang['addnews'] );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = 0;	

	if( $config['allow_admin_wysiwyg'] == "2" ) $save = "tinyMCE.triggerSave();"; else $save = "";

	$xfieldsaction = "categoryfilter";
	include (ENGINE_DIR . '/inc/xfields.php');
	echo $categoryfilter;
	

	echo "
    <script type=\"text/javascript\">
    function preview(){";
	
	if( $config['allow_admin_wysiwyg'] == 1 ) {
		echo "submit_all_data();";
	}

	if( $config['allow_admin_wysiwyg'] == 2 ) {
		echo "document.getElementById('short_story').value = $('#short_story').html();
	document.getElementById('full_story').value = $('#full_story').html();";
	}
	
	echo "if(document.addnews.title.value == ''){ 			Growl.info({
				title: '{$lang[p_info]}',
				text: '{$lang['addnews_alert']}'
			}); return false; }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.addnews.mod.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.mod.value='addnews';document.addnews.target='_self'\",500)
    }
    }

	function auto_keywords ( key )
	{

		var wysiwyg = '{$config['allow_admin_wysiwyg']}';

		if (wysiwyg == \"1\") {
			submit_all_data();
		}

		if (wysiwyg == \"2\") {
			tinyMCE.triggerSave();
		}

		var short_txt = document.getElementById('short_story').value;
		var full_txt = document.getElementById('full_story').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, full_txt: full_txt, key: key }, function(data){
	
			HideLoading('');
	
			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').tokenfield('setTokens', data); }
	
		});

		return false;
	}


    function confirmDelete(url, id){

		var b = {};
	
		b[dle_act_lang[1]] = function() { 
						$(this).dialog(\"close\");						
				    };

		b['{$lang['p_message']}'] = function() { 
						$(this).dialog(\"close\");

						var bt = {};
					
						bt[dle_act_lang[3]] = function() { 
										$(this).dialog('close');						
								    };
					
						bt['{$lang['p_send']}'] = function() { 
										if ( $('#dle-promt-text').val().length < 1) {
											 $('#dle-promt-text').addClass('ui-state-error');
										} else {
											var response = $('#dle-promt-text').val()
											$(this).dialog('close');
											$('#dlepopup').remove();
											$.post('engine/ajax/message.php', { id: id,  text: response },
											  function(data){
											    if (data == 'ok') { document.location=url; } else { DLEalert('{$lang['p_not_send']}', '{$lang['p_info']}'); }
										  });
	
										}				
									};
					
						$('#dlepopup').remove();
					
						$('body').append(\"<div id='dlepopup' title='{$lang['p_title']}' style='display:none'><br />{$lang['p_text']}<br /><br /><textarea name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea></div>\");
					
						$('#dlepopup').dialog({
							autoOpen: true,
							width: 500,
							resizable: false,
							buttons: bt
						});
					
				    };
	
		b[dle_act_lang[0]] = function() { 
						$(this).dialog(\"close\");
						document.location=url;					
					};
	
		$(\"#dlepopup\").remove();
	
		$(\"body\").append(\"<div id='dlepopup' title='{$lang['p_confirm']}' style='display:none'><br /><div id='dlepopupmessage'>{$lang['edit_cdel']}</div></div>\");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			resizable: false,
			buttons: b
		});


    }

	function find_relates ( )
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post('engine/ajax/find_relates.php', { title: title }, function(data){
	
			HideLoading('');
	
			$('#related_news').html(data);
	
		});

		return false;

	};

	
	function xfimagedelete( xfname, xfvalue )
	{
		DLEconfirm( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
	
			$.post('engine/ajax/upload.php', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', 'images[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
			});
			
		} );
		
		return false;

	};

	function xffiledelete( xfname, xfvalue )
	{
		DLEconfirm( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
	
			$.post('engine/ajax/upload.php', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', 'files[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
				$('#xf_'+xfname).hide('');
			});
			
		} );
		
		return false;

	};
	
	function checkxf ( )
	{

		var status = '';

		{$save}

		$('[uid=\"essential\"]:visible').each(function(indx) {

			if($.trim($(this).find('[rel=\"essential\"]').val()).length < 1) {
				Growl.info({
					title: '{$lang[p_info]}',
					text: '{$lang['addnews_xf_alert']}'
				});
				status = 'fail';
			
			}

		});

		if(document.addnews.title.value == ''){

			Growl.info({
				title: '{$lang[p_info]}',
				text: '{$lang['addnews_alert']}'
			});

			status = 'fail';

		}

		return status;

	};

	$(function(){

		$('#tags').tokenfield({
		  autocomplete: {
		    source: 'engine/ajax/find_tags.php',
			minLength: 3,
		    delay: 500
		  },
		  createTokensOnBlur:true
		});

		$('[data-rel=links]').tokenfield({createTokensOnBlur:true});

		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});

	});
    </script>";
		
	$categories_list = CategoryNewsSelection( 0, 0 );

	if( $config['allow_multi_category'] ) $category_multiple = "class=\"categoryselect\" multiple";
	else $category_multiple = "class=\"categoryselect\"";


	if( $member_id['user_group'] == 1 ) {
		
		$author_info = "<span class=\"newauthor\">&nbsp;&nbsp;,&nbsp;{$lang['edit_eau']}&nbsp;<input type=\"text\" name=\"new_author\" size=\"20\"  value=\"{$member_id['name']}\"></span>";
	
	} else {
		
		$author_info = "";
	
	}

echo <<<HTML
<div class="box">
		
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#tabhome" data-toggle="tab"><i class="icon-home"></i> {$lang['tabs_news']}</a></li>
					<li><a href="#tabvote" data-toggle="tab"><i class="icon-bar-chart"></i> {$lang['tabs_vote']}</a></li>
					<li><a href="#tabextra" data-toggle="tab"><i class="icon-tasks"></i> {$lang['tabs_extra']}</a></li>
					<li><a href="#tabperm" data-toggle="tab"><i class="icon-lock"></i> {$lang['tabs_perm']}</a></li>
				</ul>
			</div>
			
            <div class="box-content">
			<form method="post" name="addnews" id="addnews" onsubmit="if(checkxf()=='fail') return false;" class="form-horizontal">
                 <div class="tab-content">			
                     <div class="tab-pane active" id="tabhome">
						<div class="row box-section">
						
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_title']}</label>
							  <div class="col-md-10">
								<input type="text" style="width:99%;max-width:437px;" name="title" id="title">&nbsp;<button onclick="find_relates(); return false;" class="btn btn-sm btn-black">{$lang['b_find_related']}</button>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_title']}" >?</span><span id="related_news"></span>
							  </div>
							 </div>
							 
							 <div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_date']}</label>
							  <div class="col-md-10">
								<input data-rel="calendar" type="text" name="newdate" size="20" >&nbsp;<input class="checkbox-inline" type="checkbox" id="allow_date" name="allow_date" value="yes" checked>&nbsp;<label for="allow_date">{$lang['edit_jdate']}</label>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang[hint_calendar]}" >?</span>{$author_info}
							  </div>
							</div>
							
							 <div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_cat']}</label>
							  <div class="col-md-10">
								<select data-placeholder="{$lang['addnews_cat_sel']}" name="category[]" id="category" onchange="onCategoryChange(this)" $category_multiple style="width:100%;max-width:350px;">{$categories_list}</select>
							  </div>
							</div>

							 <div class="form-group editor-group">
							  <label class="control-label col-lg-2">{$lang['addnews_short']}</label>
							  <div class="col-lg-10">
HTML;

	if( $config['allow_admin_wysiwyg'] ) {
		
		include (ENGINE_DIR . '/editor/shortnews.php');
	
	} else {

		$bb_editor = true;
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		echo "{$bb_code}<textarea style=\"width:100%;max-width: 950px;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"short_story\" id=\"short_story\" ></textarea>";
	}

echo <<<HTML
							  </div>
							</div>
							
							 <div class="form-group editor-group">
							  <label class="control-label col-lg-2">{$lang['addnews_full']}</label>
							  <div class="col-lg-10">
HTML;

	if( $config['allow_admin_wysiwyg'] ) {
		
		include (ENGINE_DIR . '/editor/fullnews.php');
	
	} else {

		echo "{$bb_panel}<textarea style=\"width:100%;max-width: 950px;height:350px;\" onfocus=\"setFieldName(this.name)\" name=\"full_story\" id=\"full_story\"></textarea>";
	}
	// XFields Call
	$xfieldsaction = "list";
	$xfieldsadd = true;
	$sess_id = session_id();
	$news_id = 0;
	$author = urlencode($member_id['name']);
	include (ENGINE_DIR . '/inc/xfields.php');
	// End XFields Call

	if( !$config['allow_admin_wysiwyg'] ) $output = str_replace("<!--panel-->", $bb_panel, $output);

	
	if( $user_group[$member_id['user_group']]['allow_fixed'] and $config['allow_fixed'] ) $fix_input = "<input class=\"icheck\" type=\"checkbox\" id=\"news_fixed\" name=\"news_fixed\" value=\"1\"><label for=\"news_fixed\">{$lang['addnews_fix']}</label>"; else $fix_input = "";
	if( $user_group[$member_id['user_group']]['allow_main'] ) $main_input = "<input class=\"icheck\" type=\"checkbox\" id=\"allow_main\" name=\"allow_main\" value=\"1\" checked><label for=\"allow_main\">{$lang['addnews_main']}</label>"; else $main_input = "";
	if($member_id['user_group'] < 3 ) $disable_index = "<input class=\"icheck\" type=\"checkbox\" id=\"disable_index\" name=\"disable_index\" value=\"1\"><label for=\"disable_index\">{$lang['add_disable_index']}</label>"; else $disable_index = "";
    if( !$config['allow_admin_wysiwyg'] ) $fix_br = "<input class=\"icheck\" type=\"checkbox\" id=\"allow_br\" name=\"allow_br\" value=\"1\" checked><label for=\"allow_br\">{$lang['allow_br']}</label>"; else $fix_br = "";
	
echo <<<HTML
							  </div>
							</div>
{$output}
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_option']}</label>
							  <div class="col-md-10">
								  <div class="row">
									<div class="col-md-12"><input class="icheck" type="checkbox" id="approve" name="approve" value="1" checked><label for="approve">{$lang['addnews_mod']}</label></div>
								  </div>
								  <div class="row">
									<div class="col-md-3" style="max-width:300px;" >{$main_input}</div>
									<div class="col-md-3" style="max-width:250px;"><input class="icheck" type="checkbox" id="allow_comm" name="allow_comm" value="1" checked><label for="allow_comm">{$lang['addnews_comm']}</label></div>
									<div class="col-md-6">{$disable_index}</div>
								  </div>
								  <div class="row">
									<div class="col-md-3" style="max-width:300px;" ><input class="icheck" type="checkbox" id="allow_rating" name="allow_rating" value="1" checked><label for="allow_rating">{$lang['addnews_allow_rate']}</label></div>
									<div class="col-md-3" style="max-width:250px;">{$fix_input}</div>
									<div class="col-md-6"></div>
								  </div>
								  <div class="row">
									<div class="col-md-12">{$fix_br}</div>
								  </div>
							  </div>
							 </div>

						</div>
					</div>
                    <div class="tab-pane" id="tabvote" >
						<div class="row box-section">
						
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['v_ftitle']}</label>
							  <div class="col-md-10">
								<input type="text" name="vote_title" style="width:100%;max-width:350px;">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang[hint_ftitle]}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['vote_title']}</label>
							  <div class="col-md-10">
								<input type="text" name="frage" style="width:100%;max-width:350px;">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang[hint_vtitle]}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['vote_body']}<div class="note large">{$lang['vote_str_1']}</div></label>
							  <div class="col-md-10">
								<textarea rows="7" style="width:100%;max-width:350px;" name="vote_body"></textarea>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2"></label>
							  <div class="col-md-10">
								<input class="icheck" type="checkbox" id="allow_m_vote" name="allow_m_vote" value="1"><label for="allow_m_vote">{$lang['v_multi']}</label>
							  </div>
							 </div>

							<div class="row">
								<div class="col-md-12"><span class="note large"> <i class="icon-warning-sign"></i> {$lang['v_info']}</span></div>
							</div>
							 
						</div>
                     </div>
                    <div class="tab-pane" id="tabextra" >
						<div class="row box-section">

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['catalog_url']}</label>
							  <div class="col-md-10">
								<input type="text" name="catalog_url" size="5">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['catalog_hint_url']}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_url']}</label>
							  <div class="col-md-10">
								<input type="text" name="alt_name" style="width:100%;max-width:437px;">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_url']}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_tags']}</label>
							  <div class="col-md-10">
								<input type="text" name="tags" id="tags" style="width:437px;" autocomplete="off" />
							  </div>
							 </div>	
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['date_expires']}</label>
							  <div class="col-md-10">
								<input type="text" name="expires" data-rel="calendardate" size="20">&nbsp;{$lang['cat_action']}&nbsp;<select class="uniform" name="expires_action"><option value="0">{$lang['mass_noact']}</option><option value="1">{$lang['edit_dnews']}</option><option value="2" >{$lang['mass_edit_notapp']}</option><option value="3" >{$lang['mass_edit_notmain']}</option><option value="4" >{$lang['mass_edit_notfix']}</option></select>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_expires']}" >?</span>
							  </div>
							 </div>	
							<div class="form-group">
							  <label class="control-label col-md-2"></label>
							  <div class="col-md-10">
								{$lang['add_metatags']}&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_metas']}" >?</span>
							  </div>
							 </div>	
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['meta_title']}</label>
							  <div class="col-md-10">
								<input type="text" name="meta_title" style="width:100%;max-width:437px;">
							  </div>
							 </div>	
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['meta_descr']}</label>
							  <div class="col-md-10">
								<input type="text" name="descr" id="autodescr" style="width:100%;max-width:437px;"> <span class="note large"> <i class="icon-warning-sign"></i> {$lang['meta_descr_max']}</span>
							  </div>
							 </div>	
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['meta_keys']}</label>
							  <div class="col-md-10">
								<textarea class="tags" name="keywords" id='keywords' style="width:437px;"></textarea><br /><br />
									<button onclick="auto_keywords(1); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_descr']}</button>&nbsp;
									<button onclick="auto_keywords(2); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_keyword']}</button>
							  </div>
							 </div>	
							 
						</div>
                     </div>
                    <div class="tab-pane" id="tabperm" >
						<div class="row box-section">
HTML;

	if( $member_id['user_group'] < 3 ) {
		foreach ( $user_group as $group ) {
			if( $group['id'] > 1 ) {
				echo <<<HTML
							<div class="form-group">
							  <label class="control-label col-md-2">{$group['group_name']}</label>
							  <div class="col-md-10">
								<select class="uniform" name="group_extra[{$group['id']}]">
										<option value="0">{$lang['ng_group']}</option>
										<option value="1">{$lang['ng_read']}</option>
										<option value="2">{$lang['ng_all']}</option>
										<option value="3">{$lang['ng_denied']}</option>
								</select>
							   </div>
							 </div>	
HTML;
			}
		}
	} else {
		
		echo <<<HTML
    <tr>
        <td style="padding:4px;"><br />{$lang['tabs_not']}</br /><br /></td>
    </tr>
HTML;
	
	}

echo <<<HTML
							<div class="row">
								<div class="col-md-12"><span class="note large"> <i class="icon-warning-sign"></i> {$lang['tabs_g_info']}</span></div>
							</div>
						</div>
                     </div>
				</div>
				<div class="padded">
					<input type="submit" class="btn btn-green" value="{$lang['news_add']}" >&nbsp;
					<button onclick="preview(); return false;" class="btn btn-gray"><i class="icon-desktop"></i> {$lang['btn_preview']}</button>
					<input type="hidden" name="mod" value="addnews">
					<input type="hidden" name="action" value="doaddnews">
					<input type="hidden" name="user_hash" value="$dle_login_hash" />
				</div>
</form>
			</div>
</div>
HTML;
	
	
	echofooter();

}

// ********************************************************************************
// Do add News
// ********************************************************************************
elseif( $action == "doaddnews" ) {
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	
	$allow_comm = isset( $_POST['allow_comm'] ) ? intval( $_POST['allow_comm'] ) : 0;
	$approve = isset( $_POST['approve'] ) ? intval( $_POST['approve'] ) : 0;
	$allow_rating = isset( $_POST['allow_rating'] ) ? intval( $_POST['allow_rating'] ) : 0;
	$news_fixed = isset( $_POST['news_fixed'] ) ? intval( $_POST['news_fixed'] ) : 0;
	$allow_br = isset( $_POST['allow_br'] ) ? intval( $_POST['allow_br'] ) : 0;
	$category = $_POST['category'];
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;

	if( $user_group[$member_id['user_group']]['allow_main'] ) $allow_main = intval( $_POST['allow_main'] );
	else $allow_main = 0;

	if($member_id['user_group'] > 2 ) $disable_index = 0;

	if( !count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );
	
	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
	
	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) and $member_id['user_group'] != "1" ) $approve = 0;
	}

	if( !$user_group[$member_id['user_group']]['moderation'] ) $approve = 0;

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	
	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) ) msg( "error", $lang['addnews_error'], $lang['news_err_41'], "javascript:history.go(-1)" );
	}

	$title = $parse->process(  trim( strip_tags ($_POST['title']) ) );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

		$_POST['short_story'] = strip_tags ($_POST['short_story']);
		$_POST['full_story'] = strip_tags ($_POST['full_story']);

	}

	if ( $config['allow_admin_wysiwyg'] ) $parse->allow_code = false;
	
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );

	if( $config['allow_admin_wysiwyg'] OR $allow_br != '1' ) {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
	
	} else {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
	}

	if( $parse->not_allowed_text ) {
		msg( "error", $lang['addnews_error'], $lang['news_err_39'], "javascript:history.go(-1)" );
	}
	
	$alt_name = $_POST['alt_name'];
	
	if( trim( $alt_name ) == "" or ! $alt_name ) $alt_name = totranslit( stripslashes( $title ), true, false );
	else $alt_name = totranslit( stripslashes( $alt_name ), true, false );
	
	$title = $db->safesql( $title );
	
	$metatags = create_metatags( $short_story." ".$full_story );
	
	$catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['catalog_url'] ) ) ), ENT_QUOTES, $config['charset'] ), 0, 3, $config['charset'] ) );

	if ($config['create_catalog'] AND !$catalog_url) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ), ENT_QUOTES, $config['charset'] ), 0, 1, $config['charset'] ) );
	
	if( @preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_POST['tags'] ) ) $_POST['tags'] = "";
	else $_POST['tags'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_COMPAT, $config['charset'] ) );

	if ( $_POST['tags'] ) {

		$temp_array = array();
		$tags_array = array();
		$temp_array = explode (",", $_POST['tags']);

		if (count($temp_array)) {

			foreach ( $temp_array as $value ) {
				if( trim($value) ) $tags_array[] = trim( $value );
			}

		}

		if ( count($tags_array) ) $_POST['tags'] = implode(", ", $tags_array); else $_POST['tags'] = "";

	}
	
	
	// обработка опроса
	if( trim( $_POST['vote_title'] != "" ) ) {
		
		$add_vote = 1;
		$vote_title = trim( $db->safesql( $parse->process( $_POST['vote_title'] ) ) );
		$frage = trim( $db->safesql( $parse->process( $_POST['frage'] ) ) );
		$vote_body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['vote_body'] ), false ) );
		$allow_m_vote = intval( $_POST['allow_m_vote'] );
	
	} else
		$add_vote = 0;
		
	// обработка доступа
	if( $member_id['user_group'] < 3 ) {
		
		$group_regel = array ();
		
		foreach ( $_POST['group_extra'] as $key => $value ) {
			if( $value ) $group_regel[] = intval( $key ) . ':' . intval( $value );
		}
		
		if( count( $group_regel ) ) $group_regel = implode( "||", $group_regel );
		else $group_regel = "";
	
	} else
		$group_regel = '';
	
	if( trim( $_POST['expires'] ) != "" ) {
		$expires = $_POST['expires'];
		if( (($expires = strtotime( $expires )) === - 1) OR !$expires ) {
			msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
		} 
	} else $expires = '';

		
	// Обработка даты и времени
	$added_time = time();
	$newdate = $_POST['newdate'];
	
	if( $_POST['allow_date'] != "yes" ) {
		
		if( (($newsdate = strtotime( $newdate )) === - 1) OR !$newsdate ) {
			msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
		} else {
			$thistime = date( "Y-m-d H:i:s", $newsdate );
		}
		
		if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
			$thistime = date( "Y-m-d H:i:s", $added_time );
		}
	
	} else
		$thistime = date( "Y-m-d H:i:s", $added_time );
		////////////////////////////	

	if( trim( $title ) == "") {
		msg( "error", $lang['addnews_error'], $lang['addnews_alert'], "javascript:history.go(-1)" );
	}

	if( dle_strlen( $title, $config['charset'] ) > 255 ) {
		msg( "error", $lang['addnews_error'], $lang['addnews_error'], "javascript:history.go(-1)" );
	}

	// Смена автора публикации
	$author = $member_id['name'];
	$userid = $member_id['user_id'];

	if( $member_id['user_group'] == 1 AND $_POST['new_author'] != $member_id['name'] ) {

		$_POST['new_author'] = $db->safesql( $_POST['new_author'] );
					
		$row = $db->super_query( "SELECT name, user_id  FROM " . USERPREFIX . "_users WHERE name = '{$_POST['new_author']}'" );
					
		if( $row['user_id'] ) {

			$author = $row['name'];
			$userid = $row['user_id'];

		}
	}

	$xfieldsid = $added_time;
	$xfieldsaction = "init";
	include (ENGINE_DIR . '/inc/xfields.php');

	
	$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, descr, keywords, category, alt_name, allow_comm, approve, allow_main, fixed, allow_br, symbol, tags, metatitle) values ('$thistime', '{$author}', '$short_story', '$full_story', '$filecontents', '$title', '{$metatags['description']}', '{$metatags['keywords']}', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '$news_fixed', '$allow_br', '$catalog_url', '{$_POST['tags']}', '{$metatags['title']}')" );
	
	$row = $db->insert_id();

	$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, access, user_id) VALUES('{$row}', '{$allow_rating}', '{$add_vote}', '{$disable_index}', '{$group_regel}', '{$userid}')" );
	
	if( $add_vote ) {
		$db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$row}', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );
	}

	$expires_action = intval($_POST['expires_action']);

	if( $expires AND $expires_action) {
		$db->query( "INSERT INTO " . PREFIX . "_post_log (news_id, expires, action) VALUES('{$row}', '$expires', '$expires_action')" );
	}
	
	if( $_POST['tags'] != "" AND $approve ) {
		
		$tags = array ();
		
		$_POST['tags'] = explode( ",", $_POST['tags'] );
		
		foreach ( $_POST['tags'] as $value ) {
			
			$tags[] = "('" . $row . "', '" . trim( $value ) . "')";
		}
		
		$tags = implode( ", ", $tags );
		$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
	
	}
	
	if ( count($xf_search_words) AND $approve ) {
		
		$temp_array = array();
		
		foreach ( $xf_search_words as $value ) {
			
			$temp_array[] = "('" . $row . "', '" . $value[0] . "', '" . $value[1] . "')";
		}
		
		$xf_search_words = implode( ", ", $temp_array );
		$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
	}
	
	$db->query( "UPDATE " . PREFIX . "_images SET news_id='{$row}', author = '{$author}' WHERE author = '{$member_id['name']}' AND news_id = '0'" );
	$db->query( "UPDATE " . PREFIX . "_files SET news_id='{$row}', author = '{$author}' WHERE author = '{$member_id['name']}' AND news_id = '0'" );
	$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num+1 WHERE user_id='{$userid}'" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
	
	clear_cache( array('news_', 'related_', 'tagscloud_', 'archives_', 'calendar_', 'topnews_', 'rss', 'stats') );
	
	msg( "info", $lang['addnews_ok'], $lang['addnews_ok_1'] . " \"" . stripslashes( stripslashes( $title ) ) . "\" " . $lang['addnews_ok_2'] );
}
?>