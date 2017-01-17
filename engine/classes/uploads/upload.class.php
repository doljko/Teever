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
 Файл: upload.class.php
-----------------------------------------------------
 Назначение: загрузка файлов на сервер
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

include_once ENGINE_DIR . '/classes/thumb.class.php';

class UploadFileViaFTP {  

	private $path_file = "";
	private $file_name = "";

    function saveFile($path, $filename, $prefix=true) {

        if( !@file_exists( ROOT_DIR . "/uploads/files/" . $this->path_file . $filename ) ){
            return false;
        }

        return $this->path_file . $filename;
    }

    function getFileName() {
		global $config;

		if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($_POST['ftpurl']) ) {

			if( function_exists( 'mb_convert_encoding' ) ) {
	
				$_POST['ftpurl'] = mb_convert_encoding( $_POST['ftpurl'], "windows-1251", "UTF-8" );
	
			} elseif( function_exists( 'iconv' ) ) {
	
				$_POST['ftpurl'] = iconv( "UTF-8", "windows-1251//IGNORE", $_POST['ftpurl'] );
	
			}

		}

		$ftpurl = trim( htmlspecialchars( strip_tags( $_POST['ftpurl'] ) ) );
		$ftpurl = str_replace(chr(0), '', $ftpurl);
		$ftpurl = str_replace( "\\", "/", $ftpurl );

		$url = @parse_url ( $ftpurl );
		$file_path = @dirname ($this->clear_url_dir($url['path']));
		$file_name = @pathinfo($url['path']);
		$this->file_name = $file_name['basename'];

		if ($file_path AND $file_path != ".") $this->path_file = $file_path."/";

        return $this->file_name;
    }

	function clear_url_dir($var) {
		if ( is_array($var) ) return "";

		$var = str_replace(chr(0), '', $var);		
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = str_ireplace( ".phtm", ".pppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );

		return $var;
		
	}

    function getFileSize() {

		return @filesize( ROOT_DIR . "/uploads/files/" . $this->path_file . $this->file_name );

    }

    function getErrorCode() {

		return false;

    }
}

class UploadFileViaURL {  

	private $from = "";

    function saveFile($path, $filename, $prefix=true) {

		if ( $prefix ) {

			$file_prefix = time() + rand( 1, 100 );
			$file_prefix .= "_";

		} else $file_prefix = "";

		$filename = totranslit( $file_prefix.$filename );

        if(!@copy($this->from, $path.$filename)){
            return false;
        }

        return $filename;
    }
    function getFileName() {
		global $config;

		if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($_POST['imageurl']) ) {

			if( function_exists( 'mb_convert_encoding' ) ) {
	
				$_POST['imageurl'] = mb_convert_encoding( $_POST['imageurl'], "windows-1251", "UTF-8" );
	
			} elseif( function_exists( 'iconv' ) ) {
	
				$_POST['imageurl'] = iconv( "UTF-8", "windows-1251//IGNORE", $_POST['imageurl'] );
	
			}

		}

		$imageurl = trim( htmlspecialchars( strip_tags( $_POST['imageurl'] ) ) );
		$imageurl = str_replace(chr(0), '', $imageurl);
		$imageurl = str_replace( "\\", "/", $imageurl );

		$url = @parse_url ( $imageurl );

        if (!array_key_exists('host', $url)) {
            return '';
        }

		if($url['scheme'] != 'http' AND $url['scheme'] != 'https') {

            return '';
		}

		if($url['host'] == 'localhost' OR $url['host'] == '127.0.0.1') {

            return '';
		}

		if( stripos (  $url['host'], $_SERVER['HTTP_HOST'] ) !== false ) {

			return '';

		}

		if( stripos( $imageurl, ".php" ) !== false ) return '';
		if( stripos( $imageurl, ".phtm" ) !== false ) return '';

		$this->from = $imageurl;

		$imageurl = explode( "/", $imageurl );
		$imageurl = end( $imageurl );

        return $imageurl;
    }
    function getFileSize() {

		$url = @parse_url( $this->from );

		if ( $url ) {

			$fp = @fsockopen( $url['host'], 80, $errno, $errstr, 10);

			if ($fp) {
				$x='';
	
				fputs($fp,"HEAD {$url['path']} HTTP/1.0\nHOST: {$url['host']}\n\n");
				while(!feof($fp)) $x.=fgets($fp,128);
				fclose($fp);

				if ( preg_match("#Content-Length: ([0-9]+)#i",$x,$size) ) return intval($size[1]); else return 0;

			} else return 0;

		} else return 0;

    }

    function getErrorCode() {

		return false;

    }
}

class UploadFileViaForm {  

    function saveFile($path, $filename, $prefix=true) {

		if ( $prefix ) {

			$file_prefix = time() + rand( 1, 100 );
			$file_prefix .= "_";

		} else $file_prefix = "";

		$filename = totranslit( $file_prefix.$filename );

        if(!@move_uploaded_file($_FILES['qqfile']['tmp_name'], $path.$filename)){
            return false;
        }

        return $filename;
    }
    function getFileName() {
		global $config;

		if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($_FILES['qqfile']['name']) ) {

			if( function_exists( 'mb_convert_encoding' ) ) {
	
				$_FILES['qqfile']['name'] = mb_convert_encoding( $_FILES['qqfile']['name'], "windows-1251", "UTF-8" );
	
			} elseif( function_exists( 'iconv' ) ) {
	
				$_FILES['qqfile']['name'] = iconv( "UTF-8", "windows-1251//IGNORE", $_FILES['qqfile']['name'] );
	
			}

		}

		$path_parts = @pathinfo($_FILES['qqfile']['name']);

        return $path_parts['basename'];

    }
    function getFileSize() {
        return $_FILES['qqfile']['size'];
    }

    function getErrorCode() {

		$error_code = $_FILES['qqfile']['error'];

		if ($error_code !== UPLOAD_ERR_OK) {

		    switch ($error_code) { 
		        case UPLOAD_ERR_INI_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
		        case UPLOAD_ERR_FORM_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
		        case UPLOAD_ERR_PARTIAL: 
		            $error_code = 'PHP Error: The uploaded file was only partially uploaded'; break;
		        case UPLOAD_ERR_NO_FILE: 
		            $error_code = 'PHP Error: No file was uploaded'; break;
		        case UPLOAD_ERR_NO_TMP_DIR: 
		            $error_code = 'PHP Error: Missing a PHP temporary folder'; break;
		        case UPLOAD_ERR_CANT_WRITE: 
		            $error_code = 'PHP Error: Failed to write file to disk'; break;
		        case UPLOAD_ERR_EXTENSION: 
		            $error_code = 'PHP Error: File upload stopped by extension'; break;
		        default: 
		            $error_code = 'Unknown upload error';  break;
		    } 

		} else return false;

        return $error_code;
    }
}

class FileUploader {

	private $allowed_extensions = array ("gif", "jpg", "jpeg", "png" );
	private $allowed_video = array ("avi", "mp4", "wmv", "mpg", "flv", "mp3", "swf", "m4v", "m4a", "mov", "3gp", "f4v", "mkv" );
	private $allowed_files = array();
	private $area = "";
	private $author = "";
	private $news_id = "";
	private $t_size = "";
	private $t_seite = 0;
	private $make_thumb = true;
	private $m_size = "";
	private $m_seite = 0;
	private $make_medium = false;
	private $make_watermark = true;
	private $upload_path = "posts/";
	private $use_prefix = true;

    function __construct($area, $news_id, $author, $t_size, $t_seite, $make_thumb = true, $make_watermark = true, $m_size = 0, $m_seite = 0, $make_medium = false){        
		global $config, $db, $member_id, $user_group;

        $this->area = totranslit($area);

		if ( $this->area == "adminupload" ) {

			if (!isset($_FILES['qqfile']) OR $member_id['user_group'] != 1) die( "Hacking attempt!" );

			if( $_REQUEST['userdir'] ) $userdir = totranslit( $_REQUEST['userdir'], true, false ). "/"; else $userdir = "";
			if( $_REQUEST['subdir'] ) $subdir = totranslit( $_REQUEST['subdir'], true, false ). "/"; else $subdir = "";

			$this->upload_path = $userdir.$subdir;
			$this->use_prefix = false;

		} else {

	        $this->allowed_files = explode( ',', strtolower( $user_group[$member_id['user_group']]['files_type'] ) );
		}


		if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($author) ) {

			if( function_exists( 'mb_convert_encoding' ) ) {
	
				$author = mb_convert_encoding( $author, "windows-1251", "UTF-8" );
	
			} elseif( function_exists( 'iconv' ) ) {
	
				$author = iconv( "UTF-8", "windows-1251//IGNORE", $author );
	
			}

		}

        $this->author = $db->safesql( $author );
        $this->news_id = intval($news_id);
        $this->t_size = $t_size;
        $this->t_seite = $t_seite;
        $this->make_thumb = $make_thumb;
        $this->m_size = $m_size;
        $this->m_seite = $m_seite;
        $this->make_medium = $make_medium;
        $this->make_watermark = $make_watermark;
		$ftp_upload_flag = false;
      
        if (isset($_FILES['qqfile'])) {

            $this->file = new UploadFileViaForm();

        } elseif ( $_POST['imageurl'] != "" ) {

            $this->file = new UploadFileViaURL();

        } elseif ( $member_id['user_group'] == 1 AND $_POST['ftpurl'] != "" ) {

            $this->file = new UploadFileViaFTP();
			$ftp_upload_flag = true;
        } else {

            $this->file = false; 

        }

		if (@ini_get( 'safe_mode' ) == 1 OR $ftp_upload_flag OR $this->area == "adminupload" )
			define( 'FOLDER_PREFIX', "" );
		else
			define( 'FOLDER_PREFIX', date( "Y-m" )."/" );

    }

	private function check_filename ( $filename ) {

		if( $filename != "" ) {

			$filename = str_replace( "\\", "/", $filename );
			$filename = preg_replace( '#[.]+#i', '.', $filename );
			$filename = str_replace( "/", "", $filename );

			$filename_arr = explode( ".", $filename );
			$type = totranslit( end( $filename_arr ) );
				
			$curr_key = key( $filename_arr );
			unset( $filename_arr[$curr_key] );
				
			$filename = totranslit( implode( ".", $filename_arr ) ) . "." . $type;

		} else return false;

		$filename = preg_replace( '#[.]+#i', '.', $filename );
		$filename = str_ireplace( "php", "", $filename );
		$filename = str_ireplace( ".pl", "", $filename );

		if( stripos ( $filename, "php" ) !== false ) return false;
		if( stripos ( $filename, "phtm" ) !== false ) return false;
		if( stripos ( $filename, "shtm" ) !== false ) return false;
		if( stripos ( $filename, ".htaccess" ) !== false ) return false;
		if( stripos ( $filename, ".cgi" ) !== false ) return false;
		if( stripos ( $filename, ".html" ) !== false ) return false;
		if( stripos ( $filename, ".ini" ) !== false ) return false;

		if( stripos ( $filename, "." ) === 0 ) return false;
		if( stripos ( $filename, "." ) === false ) return false;

		return $filename;

	}

	private function msg_error($message, $code = 500) {
		global $config;

		return "{\"error\":\"{$message}\"}";
	}

	function FileUpload(){
		global $config, $db, $lang, $member_id, $user_group;

		$_IP = get_ip();
		$added_time = time();
		$xfvalue = "";

		if( !is_dir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX ) ) {
			
			@mkdir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX, 0777 );
			@chmod( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX, 0777 );
			@mkdir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "thumbs", 0777 );
			@chmod( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "thumbs", 0777 );

		}

		if( !is_dir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "medium") ) {

			@mkdir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "medium", 0777 );
			@chmod( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "medium", 0777 );

		}

		if( !is_dir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {

			@mkdir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX, 0777 );
			@chmod( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX, 0777 );

		}

		if( !is_dir( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( $lang['upload_error_0']." /uploads/posts/" . FOLDER_PREFIX, 403 );
		}

		if( !is_dir( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( $lang['upload_error_0']." /uploads/files/" . FOLDER_PREFIX, 403 );
		}

		if( !is_writable( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( $lang['upload_error_1']." /uploads/" . $this->upload_path . FOLDER_PREFIX . " ".$lang['upload_error_2'], 403 );
		}

		if( !is_writable( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX ) ) {
			
			return $this->msg_error( $lang['upload_error_1']." /uploads/files/" . FOLDER_PREFIX . " ".$lang['upload_error_2'], 403 );
		}

		if( !is_writable( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "thumbs" ) ) {
			
			return $this->msg_error( $lang['upload_error_1']." /uploads/posts/" . FOLDER_PREFIX . "thumbs/ ".$lang['upload_error_2'], 403 );
		}

		if( !is_writable( ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "medium" ) ) {
			
			return $this->msg_error( $lang['upload_error_1']." /uploads/posts/" . FOLDER_PREFIX . "medium/ ".$lang['upload_error_2'], 403 );
		}

		if (!$this->file){
			return $this->msg_error( $lang['upload_error_3'], 405 );
        }

		$filename = $this->check_filename( $this->file->getFileName() );
		$size = $this->file->getFileSize();

		if (!$filename){
			return $this->msg_error( $lang['upload_error_4'], 405 );
        }

		$filename_arr = explode( ".", $filename );
		$type = end( $filename_arr );

		if (!$type){
			return $this->msg_error( $lang['upload_error_4'], 405 );
        }

		$error_code = $this->file->getErrorCode();

		if ( $error_code ){
			return $this->msg_error( $error_code, 405 );
        }		

        if ($size == 0) {
            return $this->msg_error( $lang['upload_error_5'], 403 );
        }

		if( $config['files_allow'] AND $user_group[$member_id['user_group']]['allow_file_upload'] AND in_array($type, $this->allowed_files ) ) {

			if( intval( $user_group[$member_id['user_group']]['max_file_size'] ) AND $size > ($user_group[$member_id['user_group']]['max_file_size'] * 1024) ) {
				
				return $this->msg_error( $lang['files_too_big'], 500 );
			
			}

			if( $this->area != "template" AND $user_group[$member_id['user_group']]['max_files'] ) {
				
				$row = $db->super_query( "SELECT COUNT(*) as count  FROM " . PREFIX . "_files WHERE author = '{$this->author}' AND news_id = '{$this->news_id}'" );
				$count_files = $row['count'];
		
				if ($count_files AND $count_files >= $user_group[$member_id['user_group']]['max_files'] ) return $this->msg_error( $lang['error_max_files'], 403 );
		
			}

			$uploaded_filename = $this->file->saveFile(ROOT_DIR . "/uploads/files/".FOLDER_PREFIX, $filename, $this->use_prefix);

			if ( $uploaded_filename ) {

				@chmod( ROOT_DIR . "/uploads/files/" . FOLDER_PREFIX . $uploaded_filename, 0666 );
				$added_time = time();

				if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '36', '{$uploaded_filename}')" );

				if( $this->area == "template" ) {
					
					$db->query( "INSERT INTO " . PREFIX . "_static_files (static_id, author, date, name, onserver) values ('{$this->news_id}', '{$this->author}', '{$added_time}', '{$filename}', '". FOLDER_PREFIX ."{$uploaded_filename}')" );
					$id = $db->insert_id();

					if( in_array( $type, $this->allowed_video ) ) {
							
						if( $type == "mp3" ) {
								
							$file_link = $config['http_home_url'] . "engine/skins/images/mp3_file.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "audio";
							
						} elseif ($type == "swf") {
			
							$file_link = $config['http_home_url'] . "engine/skins/images/file_flash.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "flash";
			
						} else {
								
							$file_link = $config['http_home_url'] . "engine/skins/images/video_file.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "video";
						}
						
					} else { $file_link = $config['http_home_url'] . "engine/skins/images/all_file.png";  $data_url = "#"; $file_play = ""; };

					$return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><a class=\"uploadfile\" href=\"{$data_url}\" data-src=\"{$id}:{$filename}\" data-type=\"file\" data-play=\"{$file_play}\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $file_link . "\" /></a></div><div class=\"info\"><input type=\"checkbox\" id=\"file\" name=\"static_files[]\" value=\"{$id}\" data-type=\"file\">&nbsp;".formatsize($size)."</div></div>";
				
				} else {
					
					$db->query( "INSERT INTO " . PREFIX . "_files (news_id, name, onserver, author, date) values ('{$this->news_id}', '{$filename}', '". FOLDER_PREFIX ."{$uploaded_filename}', '{$this->author}', '{$added_time}')" );
					$id = $db->insert_id();

					if( in_array( $type, $this->allowed_video ) ) {
							
						if( $type == "mp3" ) {
								
							$file_link = $config['http_home_url'] . "engine/skins/images/mp3_file.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "audio";
							
						} elseif ($type == "swf") {
			
							$file_link = $config['http_home_url'] . "engine/skins/images/file_flash.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "flash";
			
						} else {
								
							$file_link = $config['http_home_url'] . "engine/skins/images/video_file.png";
							$data_url = $config['http_home_url'] . "uploads/files/" . FOLDER_PREFIX . $uploaded_filename;
							$file_play = "video";
						}
						
					} else { $file_link = $config['http_home_url'] . "engine/skins/images/all_file.png";  $data_url = "#"; $file_play = ""; };

					if( $this->area == "xfieldsfile" ) {
						
						$return_box = "&nbsp;<button class=\"qq-upload-button btn btn-sm btn-red\" onclick=\"xffiledelete('".$_REQUEST['xfname']."','".$id."');return false;\">{$lang['xfield_xfid']}</button>";
						$xfvalue = "[attachment={$id}:{$filename}]";
						$xfvalue = addcslashes($xfvalue, "\t\n\r\"\\/");
						
					} else $return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><a class=\"uploadfile\" href=\"{$data_url}\" data-src=\"{$id}:{$filename}\" data-type=\"file\" data-play=\"{$file_play}\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $file_link . "\" /></a></div><div class=\"info\"><input type=\"checkbox\" id=\"file\" name=\"files[]\" value=\"{$id}\" data-type=\"file\">&nbsp;".formatsize($size)."</div></div>";
				
				}

			} else return $this->msg_error( $lang['images_uperr_3'], 403 );


		} elseif ( in_array( $type, $this->allowed_extensions ) AND $user_group[$member_id['user_group']]['allow_image_upload'] ) {

			if( intval( $config['max_up_size'] ) AND $size > ($config['max_up_size'] * 1024) ) {
				
				return $this->msg_error( $lang['images_big'], 500 );
			
			}

			if( $this->area != "template" AND $this->area != "adminupload" AND $user_group[$member_id['user_group']]['max_images'] ) {
				
				$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE author = '{$this->author}' AND news_id = '{$this->news_id}'" );
				if ($row['images']) $count_images = count(explode( "|||", $row['images'] )); else $count_images = false;		
				if( $count_images AND $count_images >= $user_group[$member_id['user_group']]['max_images'] ) return $this->msg_error( $lang['error_max_images'], 403 );
			}

			$uploaded_filename = $this->file->saveFile(ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX, $filename, $this->use_prefix);

			if ( $uploaded_filename ) {

				$added_time = time();

				@chmod( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename, 0666 );

				$i_info = @getimagesize(ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename); 
		
				if( !in_array( $i_info[2], array (1, 2, 3 ) ) )	{
					@unlink( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
					return $this->msg_error( $lang['upload_error_6'], 500 );
				}

				$thumb = new thumbnail( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );

				if( $this->area != "template" AND $this->area != "adminupload" ) {
					
					$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_images WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
					
					if( ! $row['count'] ) {
						
						$inserts = FOLDER_PREFIX . $uploaded_filename;
						$db->query( "INSERT INTO " . PREFIX . "_images (images, author, news_id, date) values ('{$inserts}', '{$this->author}', '{$this->news_id}', '{$added_time}')" );
					
					} else {
						
						$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
						
						if( $row['images'] == "" ) $listimages = array ();
						else $listimages = explode( "|||", $row['images'] );
						
						foreach ( $listimages as $dataimages ) {
							
							if( $dataimages == FOLDER_PREFIX . $uploaded_filename ) $error_image = "stop";
						
						}
						
						if( $error_image != "stop" ) {
							
							$listimages[] = FOLDER_PREFIX . $uploaded_filename;
							$row['images'] = implode( "|||", $listimages );
							
							$db->query( "UPDATE " . PREFIX . "_images SET images='{$row['images']}' WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
						
						}
					}
				}

				if( $this->area == "template" ) {
					
					$inserts = FOLDER_PREFIX . $uploaded_filename;
					$db->query( "INSERT INTO " . PREFIX . "_static_files (static_id, author, date, name) values ('{$this->news_id}', '{$this->author}', '{$added_time}', '{$inserts}')" );
					$id = $db->insert_id();				
				}

				if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '36', '{$uploaded_filename}')" );

				if( $this->make_thumb ) {
					
					if( $thumb->size_auto( $this->t_size, $this->t_seite ) ) {
						
						$thumb->jpeg_quality( $config['jpeg_quality'] );
						
						if( $this->make_watermark ) $thumb->insert_watermark( $config['max_watermark'] );
						
						$thumb->save( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename );
						
						@chmod( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename, 0666 );
					}
				}

				if( $this->make_medium ) {

					$thumb = new thumbnail( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
					
					if( $thumb->size_auto( $this->m_size, $this->m_seite ) ) {
						
						$thumb->jpeg_quality( $config['jpeg_quality'] );
						
						if( $this->make_watermark ) $thumb->insert_watermark( $config['max_watermark'] );
						
						$thumb->save( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename );
						
						@chmod( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename, 0666 );
					}
				}

				if ( $member_id['user_group'] == 1 ) {

					if( $this->make_watermark OR $config['max_up_side'] ) {
						
						$thumb = new thumbnail( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
						$thumb->jpeg_quality( $config['jpeg_quality'] );

						$re_save = false;
						
						if( intval($config['max_up_side']) > 1 AND $thumb->size_auto( $config['max_up_side'], $config['o_seite'] ) ) $re_save = true;
						
						if( $this->make_watermark ) { $thumb->insert_watermark( $config['max_watermark'] ); $re_save = true; }
						
						if( $re_save ) $thumb->save( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
					}
				
				} else {
	
					$thumb = new thumbnail( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
					$thumb->jpeg_quality( $config['jpeg_quality'] );
					
					if( $config['max_up_side'] ) $thumb->size_auto( $config['max_up_side'], $config['o_seite'] );
					
					if( $this->make_watermark ) $thumb->insert_watermark( $config['max_watermark'] );
					
					$thumb->save( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
				}

				if( $config['max_up_side'] ) $i_info = @getimagesize( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename );

				$img_url = 	$config['http_home_url'] . "uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;

				if( file_exists( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename ) ) {
					$img_url = 	$config['http_home_url'] . "uploads/" . $this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename;
					$medium_data = "yes";
				} else {
					$medium_data = "no";

				}

				if( file_exists( ROOT_DIR . "/uploads/" . $this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename ) ) {
					$img_url = 	$config['http_home_url'] . "uploads/" . $this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename;
					$thumb_data = "yes";
				} else {
					$thumb_data = "no";

				}

				$data_url = $config['http_home_url'] . "uploads/" . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;

				if( $this->area == "xfieldsimage" ) {
					
					$return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"xfimagedelete('".$_REQUEST['xfname']."','".FOLDER_PREFIX . $uploaded_filename."');return false;\">{$lang['xfield_xfid']}</a></div></div>";
					$xfvalue = FOLDER_PREFIX . $uploaded_filename;
					$xfvalue = addcslashes($xfvalue, "\t\n\r\"\\/");
					
				} elseif( $this->area != "template" ) {
				
					$return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><a class=\"uploadfile\" href=\"{$data_url}\" data-src=\"{$data_url}\" data-thumb=\"{$thumb_data}\" data-medium=\"{$medium_data}\" data-type=\"image\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></a></div><div class=\"info\"><input type=\"checkbox\" name=\"images[" . FOLDER_PREFIX . $uploaded_filename . "]\" value=\"" . FOLDER_PREFIX . $uploaded_filename . "\" data-thumb=\"{$thumb_data}\" data-medium=\"{$medium_data}\" data-type=\"image\" data-src=\"{$data_url}\">&nbsp;{$i_info[0]}x{$i_info[1]}</div></div>";
				
				} else {

					$return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$filename}</div><div class=\"uploadimage\"><a class=\"uploadfile\" href=\"{$data_url}\" data-src=\"{$data_url}\" data-thumb=\"{$thumb_data}\" data-medium=\"{$medium_data}\" data-type=\"image\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></a></div><div class=\"info\"><input type=\"checkbox\" name=\"static_files[]\" value=\"{$id}\" data-thumb=\"{$thumb_data}\" data-medium=\"{$medium_data}\" data-type=\"image\" data-src=\"{$data_url}\">&nbsp;{$i_info[0]}x{$i_info[1]}</div></div>";
				
				
				}

			} else return $this->msg_error( $lang['images_uperr_3'], 403 );

		} else return $this->msg_error( $lang['images_uperr_2'], 403 );

		$return_box = addcslashes($return_box, "\t\n\r\"\\/");
		return htmlspecialchars("{\"success\":true, \"returnbox\":\"{$return_box}\", \"xfvalue\":\"{$xfvalue}\"}", ENT_NOQUOTES, $config['charset']);

	}

}

?>