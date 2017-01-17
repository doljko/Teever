<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2009-2016 IT-Security (Asafov Sergey)
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: api.class.php
-----------------------------------------------------
 Назначение: API для написания модификаций или интеграции в другие скрипты
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	define( 'DATALIFEENGINE', true );
}
if( !defined( 'ROOT_DIR' ) ) {
	define( 'ROOT_DIR', substr( dirname( __FILE__ ), 0, - 11 ) );
}

if( !defined( 'ENGINE_DIR' ) ) {
	define( 'ENGINE_DIR', ROOT_DIR . '/engine' );
}

if(!class_exists('DLE_API'))
{
	class DLE_API
	{
		/**
		 * Экземпляр класса DB
		 * @var object
		 */
     	var $db = false;
    	 	
		/**
		 * Версия API
		 * @var string
		 */
      	var $version = '0.07';
    	  	
		/**
		 * Копия конфига DLE
		 * @var array
		 */
      	var $dle_config = array ();
      	
		/**
		 * Путь до директории с кешем
		 * @var string
		 */
      	var $cache_dir = false;
      	
		/**
		 * Массив со всеми файлами кеша
		 * @var array
		 */      	
      	var $cache_files = array();
    	  	
		/**
		 * Конструктор класса
		 * @return boolean
		 */
		function DLE_API()
		{
			if (!$this->cache_dir)
			{
				$this->cache_dir = ENGINE_DIR."/cache/";
			}
			return true;
		}
			
		/**
		 * Получение информации о пользователе по его ID
		 * @param $id int - ID пользователя
		 * @param $select_list string - Перечень полей с информации или * для всех
		 * @return Массив с данными в случае успеха и false если пользователь не найден
		 */	
		function take_user_by_id ($id, $select_list = "*")
		{
			$id = intval( $id );
			if( $id == 0 ) return false;
			$row = $this->load_table(USERPREFIX."_users", $select_list, "user_id = '$id'");
			if( count( $row ) == 0 )
				return false;
			else
				return $row;
		}
		
		/**
		 * Получение информации о пользователе по его имени
		 * @param $name string - Имя пользователя
		 * @param $select_list string - Перечень полей с информации или * для всех
		 * @return Массив с данными в случае успеха и false если пользователь не найден
		 */
		function take_user_by_name($name, $select_list = "*")
		{
			$name = $this->db->safesql( $name );
			if( $name == '' ) return false;
			$row = $this->load_table(USERPREFIX."_users", $select_list, "name = '$name'");
			if( count( $row ) == 0 )
				return false;
			else
				return $row;
		}
			
		/**
		 * Получение информации о пользователе по его емайлу
		 * @param $email string - Емайл пользователя
		 * @param $select_list string - Перечень полей с информации или * для всех
		 * @return Массив с данными в случае успеха и false если пользователь не найден
		 */	
		function take_user_by_email($email, $select_list = "*")
		{
			$email = $this->db->safesql( $email );
			if( $email == '' ) return false;
			$row = $this->load_table(USERPREFIX."_users", $select_list, "email = '$email'");
			if( count( $row ) == 0 )
				return false;
			else
				return $row;
		}
		
		/**
		 * Получение данных пользователей определённой группы
		 * @param $group int - ID группы
		 * @param $select_list string - Перечень полей с информации или * для всех
		 * @param $limit int - Количество получаемых пользователей
		 * @return 2-х мерный массив с данными в случае успеха и false если пользователь не найден
		 */
		function take_users_by_group ($group, $select_list = "*", $limit = 0)
		{
			$group = intval( $group );
			$data = array();
			if( $group == 0 ) return false;
			$data = $this->load_table(USERPREFIX."_users", $select_list, "user_group = '$group'", true, 0, $limit);
			if( count( $data ) == 0 )
				return false;
			else
				return $data;
		}
		
		/**
		 * Получение данных пользователей, засветившихся под определённым IP
		 * @param $ip string - Интересующий нас IP
		 * @param $like bool - использовать ли маску при поиске
		 * @param $select_list string - Перечень полей с информации или * для всех
		 * @param $limit int - Количество получаемых пользователей
		 * @return 2-х мерный массив с данными в случае успеха и false если пользователь не найден
		 */
		function take_users_by_ip ($ip, $like = false, $select_list = "*", $limit = 0)
		{
			$ip = $this->db->safesql( $ip );
			$data = array();
			if( $ip == '' ) return false;
			if( $like )
				$condition  = "logged_ip like '$ip%'";
			else
				$condition  = "logged_ip = '$ip'";
			$data = $this->load_table(USERPREFIX."_users", $select_list, $condition, true, 0, $limit);
			if( count( $data ) == 0 )
				return false;
			else
				return $data;
		}
		
		/**
		 * Смена имени пользователя
		 * @param $user_id int - ID пользователя
		 * @param $new_name string - Новое имя пользователя
		 * @return bool - true в случае успеха и false ежели новое имя уже занято другим пользователем
		 */
		function change_user_name ($user_id, $new_name)
		{
			$user_id = intval( $user_id );
			$new_name = $this->db->safesql( $new_name );
			$count_arr = $this->load_table(USERPREFIX."_users", "count(user_id) as count", "name = '$new_name'");
			$count = $count_arr['count'];
			
			if( $count > 0 ) return false;

			$old_name_arr = $this->load_table(USERPREFIX."_users", "name", "user_id = '$user_id'");
			$old_name = $old_name_arr['name'];
			$this->db->query( "UPDATE " . PREFIX . "_post SET autor='$new_name' WHERE autor='{$old_name}'" );
			$this->db->query( "UPDATE " . PREFIX . "_comments SET autor='$new_name' WHERE autor='{$old_name}' AND is_register='1'" );
			$this->db->query( "UPDATE " . USERPREFIX . "_pm SET user_from='$new_name' WHERE user_from='{$old_name}'" );
			$this->db->query( "UPDATE " . PREFIX . "_vote_result SET name='$new_name' WHERE name='{$old_name}'" );
			$this->db->query( "UPDATE " . PREFIX . "_images SET author='$new_name' WHERE author='{$old_name}'" );
			$this->db->query( "update " . USERPREFIX . "_users set name = '$new_name' where user_id = '$user_id'" );
			return true;

		}

		/**
		 * Изменение пароля пользователя
		 * @param $user_id int - ID пользователя
		 * @param $new_password string - новый пароль
		 * @return null
		 */
		function change_user_password($user_id, $new_password)
		{
			$user_id = intval( $user_id );
			$new_password = md5( md5( $new_password ) );
			$this->db->query( "update " . USERPREFIX . "_users set password = '$new_password' where user_id = '$user_id'" );
		}
		
		/**
		 * Изменение емайла пользователя
		 * @param $user_id int - ID пользователя
		 * @param $new_email string - новый емайл пользователя
		 * @return int - некий код
		 * 		-2: некорректный емайл
		 * 		-1: новый емайл используется другим пользователем
		 * 		 1: операция прошла успешно
		 */
		function change_user_email($user_id, $new_email)
		{
			$user_id = intval( $user_id );

			if( (! preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])'.'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $new_email )) or (empty( $new_email )) )
			{
				return -2;
			}

			$new_email = $this->db->safesql( $new_email );
			$email_exist_arr = $this->load_table(USERPREFIX."_users", "count(user_id) as count", "email = '$new_email'");
			if ($email_exist_arr['count'] > 0) return -1;

			$q = $this->db->query( "update " . USERPREFIX . "_users set email = '$new_email' where user_id = '$user_id'" );
			return 1;			
		}
			
			
		/**
		 * Изменение группы пользователя
		 * @param $user_id int - ID пользователя
		 * @param $new_group int - ID новой группы пользователя
		 * @return bool - true в случае успеха и false если указан ID несуществующей группы
		 */
		function change_user_group($user_id, $new_group)
		{
			$user_id = intval( $user_id );
			$new_group = intval( $new_group );
			if($this->checkGroup($new_group) === false) return false;
			$this->db->query( "update " . USERPREFIX . "_users set user_group = '$new_group' where user_id = '$user_id'" );
			return true;
		}
		
		/**
		 * Авторизация пользователя по имени и паролю
		 * @param $login string - имя пользователя
		 * @param $password string - пароль пользователя
		 * @return bool
		 * 		true:	разрешаем авторизацию
		 * 		false:	авторизация не пройдена
		 */
		function external_auth($login, $password)
		{
			$login = $this->db->safesql( $login );
			$password = md5( md5( $password ) );
			$arr = $this->load_table(USERPREFIX."_users", "user_id", "name = '$login' AND password = '$password'");
			if( ! empty( $arr['user_id'] ) )
				return true;
			else
				return false;
		}
		
		/**
		 * Добавление в базу нового пользователя
		 * @param $login string - имя пользователя
		 * @param $password string - пароль пользователя
		 * @param $email string - емайл пользователя
		 * @param $group int - группа пользователя
		 * @return int - код
		 * 		-4: задана несуществующая группа
		 * 		-3: некорректный емайл
		 * 		-2: емайл занят другим пользователем
		 * 		-1: имя пользователя тоже занято, вот неудача
		 * 		 1: операция прошла успешно
		 */
		function external_register($login, $password, $email, $group)
		{
			$login = $this->db->safesql( $login );

			if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\{\+]/", $login ) ) return -1;

			$password = md5( md5( $password ) );
			$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
			$email = $this->db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $email ) ) ) ) );
			$group = intval( $group );
			
			$login_exist_arr = $this->load_table(USERPREFIX."_users", "count(user_id) as count", "name = '$login'");
			if( $login_exist_arr['count'] > 0 ) return -1;
			
			$email_exist_arr = $this->load_table(USERPREFIX."_users", "count(user_id) as count", "email = '$email'");
			if( $email_exist_arr['count'] > 0 ) return -2;
			
			if (empty( $email ) OR strlen( $email ) > 50 OR @count(explode("@", $email)) != 2)
			{
				return -3;
			}
			
			if($this->checkGroup($group) === false) return -4;
			
			$now = time();
			$q = $this->db->query( "insert into " . USERPREFIX . "_users (email, password, name, user_group, reg_date) VALUES ('$email', '$password', '$login', '$group', '$now')" );
			return 1;
		}		

		/**
		 * Отправка пользователю персонального сообщения
		 * @param $user_id int - ID получателя
		 * @param $subject string - тема сообщения
		 * @param $text string - текст сообщения
		 * @param $from string - имя отправителя
		 * @return int - код
		 * 		-1: получатель не существует
		 * 		 0: операция неудалась
		 * 		 1: операция прошла успешно
		 */
		function send_pm_to_user($user_id, $subject, $text, $from)
		{
			$user_id = intval( $user_id );
			// Check if user exist
			$count_arr = $this->load_table(USERPREFIX."_users", "count(user_id) as count", "user_id = '$user_id'");
			if($count_arr['count'] == 0 ) return - 1;			

			$subject = $this->db->safesql( $subject );
			$text = $this->db->safesql( $text );
			$from = $this->db->safesql( $from );
			$now = time();
			$q = $this->db->query( "insert into " . PREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder) VALUES ('$subject', '$text', '$user_id', '$from', '$now', '0', 'inbox')" );
			if( ! $q ) return 0;

			
			$this->db->query( "update " . USERPREFIX . "_users set pm_unread = pm_unread + 1, pm_all = pm_all+1  where user_id = '$user_id'" );
			return 1;

		}
      	
		/**
		 * Service function - take params from table
		 * @param $table string - название таблицы
		 * @param $fields string - необходимые поля через запятйю или * для всех
		 * @param $where string - условие выборки
		 * @param $multirow bool - забирать ли один ряд или несколько
		 * @param $start int - начальное значение выборки
		 * @param $limit int - количество записей для выборки, 0 - выбрать все
		 * @param $sort string - поле, по которому осуществляется сортировка
		 * @param $sort_order - направление сортировки
		 * @return array с данными или false если mysql вернуль 0 рядов
		 */
		function load_table ($table, $fields = "*", $where = '1', $multirow = false, $start = 0, $limit = 0, $sort = '', $sort_order = 'desc')
		{
			if (!$table) return false;

			if ($sort!='') $where.= ' order by '.$sort.' '.$sort_order;
			if ($limit>0) $where.= ' limit '.$start.','.$limit;
			$q = $this->db->query("Select ".$fields." from ".$table." where ".$where);
			if ($multirow)
			{
				while ($row = $this->db->get_row())
				{
					$values[] = $row;
				}
			}
			else
			{
				$values = $this->db->get_row();
			}
			if (count($values)>0) return $values;
			
			return false;

		}
        
		/**
		 * Запись данных в кеш
		 * @param $fname string - имя файла для кеша без расширения
		 * @param $vars - данные для записи
		 * @return unknown_type
		 */
		function save_to_cache ($fname, $vars)
		{
			// @TODO собачка - зло
			$filename = $fname.".tmp";
			$f = @fopen($this->cache_dir.$filename, "w+");
			@chmod('0777', $this->cache_dir.$filename);
			if (is_array($vars)) $vars = serialize($vars);
			@fwrite($f, $vars);
			@fclose($f);
			return $vars;
		}
			
			
		/**
		 * Загрузка данных из кеша
		 * @param $fnamee string - имя файла для кеша без расширения
		 * @param $timeout int - время жизни кэша в секундах
		 * @param $type string - тип данных в кеше. если не text - считаем, что хранился массив
		 * @return unknown_type
		 */
		function load_from_cache ($fname, $timeout=300, $type = 'text')
		{
			$filename = $fname.".tmp";
			if (!file_exists($this->cache_dir.$filename)) return false;
			if ((filemtime($this->cache_dir.$filename)) < (time()-$timeout)) return false;

			if ($type=='text')
			{
				return file_get_contents($this->cache_dir.$filename);
			}
			else
			{
				return unserialize(file_get_contents($this->cache_dir.$filename));
			}
		}			

		/**
		 * Удаление кеша
		 * @param $name string - имя файла для удаления. При значении GLOBAL удаляем весь кеш
		 * @return null
		 */				
		function clean_cache($name = "GLOBAL")
		{
			$this->get_cached_files();
			
			if ($name=="GLOBAL")
			{
				foreach ($this->cache_files as $cached_file)
				{
					@unlink($this->cache_dir.$cached_file);
				}
			}
			elseif (in_array($name.".tmp", $this->cache_files))
			{
				@unlink($this->cache_dir.$name.".tmp");
			}
		}

		/**
		 * Получение массива содержащего названия файлов кеша
		 * @return array
		 */		
		function get_cached_files()
		{
			$handle = opendir($this->cache_dir);
			while (($file = readdir($handle)) !== false)
			{
				if ($file != '.' && $file != '..' && (!is_dir($this->cache_dir.$file) && $file !='.htaccess'))
				{
					$this->cache_files [] = $file;
				}
			}
			closedir($handle);
		}		

		/**
		 * Сохранение параметров скрипта
		 * @param $key string или array
		 * 		string: Название параметра
		 * 		 array: ассоциативный массив параметров
		 * @param $new_value - значение параметра. Не используется, если $key массив
		 * @return null;
		 */				
		function edit_config ($key, $new_value = '')
		{
			$find[] = "'\r'";
			$replace[] = "";
			$find[] = "'\n'";
			$replace[] = "";
			$config = $this->dle_config;
			if (is_array($key))
			{
				foreach ($key as $ckey=>$cvalue)
				{
					if ($config[$ckey])
					{
						$config[$ckey] = $cvalue;
					}
				}
			}
			else
			{
				if ($config[$key])
				{
					$config[$key] = $new_value;
				}
			}
			// Записываем новый конфиг
			$handle = @fopen(ENGINE_DIR.'/data/config.php', 'w');
			fwrite( $handle, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n" );
			foreach ( $config as $name => $value )
			{
				if( $name != "offline_reason" )
				{
					$value = trim( stripslashes( $value ) );
					$value = htmlspecialchars( $value);
					$value = preg_replace( $find, $replace, $value );
					$name = trim( stripslashes( $name ) );
					$name = htmlspecialchars( $name, ENT_QUOTES );
					$name = preg_replace( $find, $replace, $name );
				}
				$value = str_replace( "$", "&#036;", $value );
				$value = str_replace( "{", "&#123;", $value );
				$value = str_replace( "}", "&#125;", $value );
				$name = str_replace( "$", "&#036;", $name );
				$name = str_replace( "{", "&#123;", $name );
				$name = str_replace( "}", "&#125;", $name );
				fwrite( $handle, "'{$name}' => \"{$value}\",\n\n" );
			}
			fwrite( $handle, ");\n\n?>" );
			fclose( $handle );
			$this->clean_cache();
		}
         		
		/**
		 * Получение новостей
		 * @param $cat string - категории новостей, через запятую
		 * @param $fields string - перечень получаемых полей новостей или * для всех
		 * @param $start int - начальное значение выборки
		 * @param $limit int - количество новостей для выборки, 0 - выбрать все новости
		 * @param $sort string - поле, по которому осуществляется сортировка
		 * @param $sort_order - направление сортировки
		 * @return array - ассоциативный 2-х мерный массив с новостями
		 */
		function take_news ($cat, $fields = "*", $start = 0, $limit = 10, $sort = 'id', $sort_order = 'desc')
		{
			if ($this->dle_config['allow_multi_category'] == 1)
			{
				$condition = 'category regexp "[[:<:]]('.str_replace(',', '|', $cat).')[[:>:]]"';
			}
			else
			{
				$condition = 'category IN ('.$cat.')';
			}
			return $this->load_table (PREFIX."_post", $fields, $condition, $multirow = true, $start, $limit, $sort, $sort_order);
			 
		}
        	
        	
		/**
		 * Проверка существования группы с указанным ID
		 * @param $group int - ID группы
		 * @return bool - true если существует и false если нет
		 */		
		function checkGroup($group)
		{
			$row = $this->db->super_query('SELECT group_name FROM '.USERPREFIX.'_usergroups WHERE id = '.intval($group));
			return isset($row['group_name']);
		}        	
        	

		/**
		 * Установка административной части модуля
		 * @param $name string		- название модуля, а именно файла .php находящегося в папке engine/inc/,
									но без расширения файла
		 * @param $title string		- заголовок модуля
		 * @param $descr string		- описание модуля
		 * @param $icon string		- имя иконки для модуля, без указания пути.
		 							Иконка обязательно при этом должна находится в папке engine/skins/images/
		 * @param $perm string		- информация о группах которым разрешен показ данного модуля.
		 							Данное поле может принимать следующие значения: all или ID групп через запятую.
									Например: 1,2,3. если указано значение all то модуль будет показываться всем
									пользователям имеющим доступ в админпанель
		 * @return bool - true если успешно установлено и false если нет
		 */
		function install_admin_module ($name, $title, $descr, $icon, $perm = '1')
		{
			$name = $this->db->safesql($name);
			$title = $this->db->safesql($title);
			$descr = $this->db->safesql($descr);
			$icon = $this->db->safesql($icon);
			$perm = $this->db->safesql($perm);
			// Для начала проверяем наличие модуля
			$this->db->query("Select name from `".PREFIX."_admin_sections` where name = '$name'");
			if ($this->db->num_rows()>0)
			{
				// Модуль есть, обновляем данные
				$this->db->query("UPDATE `".PREFIX."_admin_sections` set title = '$title', descr = '$descr', icon = '$icon', allow_groups = '$perm' where name = '$name'");
				return true;
			}
			else
			{
				// Модуля нету, добавляем
				$this->db->query("INSERT INTO `".PREFIX."_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('$name', '$title', '$descr', '$icon', '$perm')");
				return true;
			}

			return false;
		}

		/**
		 * Удаление административной части модуля
		 * @param $name string - название модуля
		 * @return null
		 */
		function uninstall_admin_module ($name)
		{
			$name = $this->db->safesql($name);
			$this->db->query("DELETE FROM `".PREFIX."_admin_sections` where name = '$name'");
		}

		/**
		 * Изменение прав административной части модуля
		 * @param $name string 		- название модуля
		 * @param $perm string		- информация о группах которым разрешен показ данного модуля.
		 							Данное поле может принимать следующие значения: all или ID групп через запятую.
									Например: 1,2,3. если указано значение all то модуль будет показываться всем
									пользователям имеющим доступ в админпанель
		 * @return null
		 */
		function change_admin_module_perms ($name, $perm)
		{
            $name = $this->db->safesql($name);
            $perm = $this->db->safesql($perm);
			$this->db->query("UPDATE `".PREFIX."_admin_sections` set allow_groups = '$perm' where name = '$name'");
		}
        	

	}
}

	$dle_api = new DLE_API ();
	if( !$config['version_id'] ) { include_once (ENGINE_DIR . '/data/config.php'); date_default_timezone_set ( $config['date_adjust'] ); }
	$dle_api->dle_config = $config;
	if( !isset( $db ) ) {
		include_once (ENGINE_DIR . '/classes/mysql.php');
		include_once (ENGINE_DIR . '/data/dbconfig.php');
	}
	$dle_api->db = $db;
?>