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
 Файл: social.class.php
-----------------------------------------------------
 Назначение: Авторизация через социальные сети
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

class AuthViaVK {

    function get_user( $social_config ) {
		global $config, $lang;

		if(isset($_GET['email'])) $ext_uri="&email=".$_GET['email']; else $ext_uri = "";

		$params = array(
			'client_id'     => $social_config['vkid'],
			'client_secret' => $social_config['vksecret'],
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=vk".$ext_uri
		);

		$token = @json_decode(http_get_contents('https://oauth.vk.com/access_token' . '?' . http_build_query($params)), true);

		if (isset($token['access_token'])) {

			$params = array(
				'uids'         => $token['user_id'],
				'fields'       => 'uid,first_name,last_name,nickname,photo_big',
				'access_token' => $token['access_token']
			);

			$user = @json_decode(http_get_contents('https://api.vk.com/method/users.get' . '?' . http_build_query($params)), true);

			if (isset($user['response'][0]['uid'])) {

	            $user = $user['response'][0];

				if( !$token['email'] AND isset($_GET['email']) ) $token['email'] = $_GET['email'];

				return array ('sid' => sha1 ('vkontakte'.$user['uid']), 'nickname' => $user['nickname'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $token['email'], 'avatar' => $user['photo_big'], 'provider' => 'vkontakte' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaGoogle {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['googleid'],
			'client_secret' => $social_config['googlesecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=google",

		);

		$token = @json_decode(http_get_contents('https://accounts.google.com/o/oauth2/token', $params), true);

		if (isset($token['access_token'])) {

			$params['access_token'] = $token['access_token'];

			$user = @json_decode(http_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {

				return array ('sid' => sha1 ('google'.$user['id']), 'nickname' => $user['name'], 'name' => $user['given_name'].' '.$user['family_name'], 'email' => $user['email'], 'avatar' => $user['picture'], 'provider' => 'Google' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaMailru {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['mailruid'],
			'client_secret' => $social_config['mailrusecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=mailru",

		);

		$token = @json_decode(http_get_contents('https://connect.mail.ru/oauth/token', $params), true);

		if (isset($token['access_token'])) {

			$sign = md5("app_id={$social_config['mailruid']}method=users.getInfosecure=1session_key={$token['access_token']}{$social_config['mailrusecret']}");

			$params = array(
				'method'       => 'users.getInfo',
				'secure'       => '1',
				'app_id'       => $social_config['mailruid'],
				'session_key'  => $token['access_token'],
				'sig'          => $sign
			);

			$user = @json_decode(http_get_contents('http://www.appsmail.ru/platform/api' . '?' . http_build_query($params)), true);

			if (isset($user[0]['uid'])) {

	            $user = array_shift($user);

				return array ('sid' => sha1 ('mailru'.$user['uid']), 'nickname' => $user['nick'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => $user['pic_180'].'.jpg', 'provider' => 'Mail.Ru' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaYandex {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['yandexid'],
			'client_secret' => $social_config['yandexsecret'],
			'grant_type' 	=> 'authorization_code',
			'code' => $_GET['code']

		);

		$token = @json_decode(http_get_contents('https://oauth.yandex.ru/token', $params), true);

		if (isset($token['access_token'])) {

			$params = array(
				'format'       => 'json',
				'oauth_token'  => $token['access_token']
			);


			$user = @json_decode(http_get_contents('https://login.yandex.ru/info' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {

				return array ('sid' => sha1 ('yandex'.$user['id']), 'nickname' => $user['display_name'], 'name' => $user['real_name'], 'email' => $user['default_email'], 'avatar' => '', 'provider' => 'Yandex' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaFacebook {

    function get_user( $social_config ) {
		global $config, $lang;

		$params = array(
			'client_id'     => $social_config['fcid'],
			'client_secret' => $social_config['fcsecret'],
			'code' => $_GET['code'],
			'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=fc"
		);

		@parse_str(http_get_contents('https://graph.facebook.com/oauth/access_token' . '?' . http_build_query($params)), $token);

		if (isset($token['access_token'])) {

			$params = array('access_token' => $token['access_token'], 'fields' => "id,name,email,first_name,last_name");

			$user = @json_decode(http_get_contents('https://graph.facebook.com/me' . '?' . http_build_query($params)), true);

			if (isset($user['id'])) {

				return array ('sid' => sha1 ('facebook'.$user['id']), 'nickname' => $user['name'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => '', 'provider' => 'Facebook' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class AuthViaOdnoklassniki {

    function get_user( $social_config ) {
		global $config, $lang;

		if ( !isset($_SESSION['od_access_token']) ) {

			$params = array(
				'client_id'     => $social_config['odid'],
				'client_secret' => $social_config['odsecret'],
				'grant_type' => 'authorization_code',
				'code' => $_GET['code'],
				'redirect_uri'  => $config['http_home_url'] . "index.php?do=auth-social&provider=od"
			);

			$token = @json_decode(http_get_contents('https://api.odnoklassniki.ru/oauth/token.do', $params), true);

		} else $token=array('access_token'     => $_SESSION['od_access_token'] );

		if (isset($token['access_token'])) {

			$sign = md5("application_key={$social_config['odpublic']}format=jsonmethod=users.getCurrentUser" . md5("{$token['access_token']}{$social_config['odsecret']}"));

			$params = array(
				'method'          => 'users.getCurrentUser',
				'access_token'    => $token['access_token'],
				'application_key' => $social_config['odpublic'],
				'format'          => 'json',
				'sig'             => $sign
			);

			$user = @json_decode(http_get_contents('http://api.odnoklassniki.ru/fb.do' . '?' . http_build_query($params)), true);

			if (isset($user['uid'])) {

				if ( !isset($_SESSION['od_access_token']) ) { $_SESSION['od_access_token'] = $token['access_token']; $_SESSION['od_access_code'] = $_GET['code']; }

				if(!$user['email'] AND isset($_GET['email']) ) $user['email'] = $_GET['email'];

				return array ('sid' => sha1 ('odnoklassniki'.$user['uid']), 'nickname' => $user['name'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'avatar' => $user['pic_2'], 'provider' => 'Odnoklassniki' );

			} else return $lang['social_err_3'];

		} else return $lang['social_err_1'];

    }

}

class SocialAuth {

	private $auth = false;
	private $social_config = array();

    function __construct( $social_config ){

        if ($_GET['provider'] == "vk" AND $social_config['vk']) {

            $this->auth = new AuthViaVK();

        } elseif ($_GET['provider'] == "google" AND $social_config['google']) {

            $this->auth = new AuthViaGoogle();

        } elseif ( $_GET['provider'] == "mailru" AND $social_config['mailru']) {

            $this->auth = new AuthViaMailru();

        } elseif ($_GET['provider'] == "yandex" AND $social_config['yandex']) {

            $this->auth = new AuthViaYandex();

        } elseif ($_GET['provider'] == "fc" AND $social_config['fc']) {

            $this->auth = new AuthViaFacebook();

        } elseif ($_GET['provider'] == "od" AND $social_config['od']) {

            $this->auth = new AuthViaOdnoklassniki();

        } else {

            $this->auth = false;

        }

		$this->social_config = $social_config;

    }

    function getuser(){
		global $config, $lang;

		if( $this->auth !== false ) {

			$user = $this->auth->get_user( $this->social_config );

			if ( is_array($user) AND $config['charset'] == "windows-1251" ) {

				if( function_exists( 'mb_convert_encoding' ) ) {

					$user['name'] = mb_convert_encoding( $user['name'], "windows-1251", "UTF-8" );
					$user['nickname'] = mb_convert_encoding( $user['nickname'], "windows-1251", "UTF-8" );

				} elseif( function_exists( 'iconv' ) ) {

					$user['name'] = iconv( "UTF-8", "windows-1251//IGNORE", $user['name'] );
					$user['nickname'] = iconv( "UTF-8", "windows-1251//IGNORE", $user['nickname'] );
				}

			}


			if( is_array($user) ) {

				if( !$user['nickname'] ) {

					$user['nickname'] = $user['name'];

				}

				$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
				$user['email'] = str_replace( $not_allow_symbol, '',  $user['email']);

				$user['nickname'] = preg_replace("/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/",'', $user['nickname'] );
				$user['nickname'] = str_ireplace( ".php", ".ppp", $user['nickname'] );
				$user['nickname'] = trim( htmlspecialchars( $user['nickname'], ENT_QUOTES, $config['charset'] ) );
				$user['name'] = trim( htmlspecialchars( $user['name'], ENT_QUOTES, $config['charset'] ) );

				if(dle_strlen( $user['nickname'], $config['charset'] ) > 37) $user['nickname'] = dle_substr( $user['nickname'], 37, $count, $config['charset'] );

			}


			return $user;

		} else return $lang['social_err_2'];

	}

}

?>