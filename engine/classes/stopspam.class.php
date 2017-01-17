<?php
/**
 * @StopForumSpam
 * 
 * Wrapper class for stopforumspam.com API. Uses json internally.
 *
 * @author Armin Rosu
 * @copyright 2011, Armin Rosu
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link http://www.stopforumspam.com/usage API Reference
 * @version 0.1
 */
 
 class StopSpam
 {
	/**
	* The API key.
	*
	* @var string
	*/
 	private $api_key;
 	private $check_level;
 	
	/**
	* The base url, for tha API/
	*
	* @var string
	*/
 	private $endpoint = 'http://www.stopforumspam.com/';
 	
 	
 	/**
 	* Constructor.
 	*
 	* @param string $api_key Your API Key, optional (unless adding to database).
 	*/
 	public function __construct( $api_key = null, $check_level = 1 ) {
 		// store variables
 		$this->api_key = $api_key;
 		$this->check_level = intval($check_level);
 	}
 	
 	/**
 	* Add to the database
 	*
 	* @param array $args associative array containing email, ip, username and optionally, evidence
 	* e.g. $args = array('email' => 'user@example.com', 'ip_addr' => '8.8.8.8', 'username' => 'Spammer?', 'evidence' => 'My favourite website http://www.example.com' );
 	* @return boolean Was the update succesfull or not.
 	*/
	public function add( $args )
	{				
		// should check first if not already in database
		
		// add api key
		$args['api_key'] = $this->api_key;
		
		// url to poll
		$url = $this->endpoint.'add.php?'.http_build_query($args, '', '&');
		
		// execute
		$response = file_get_contents($url);
		
		return (false == $response ? false : true);
	}
 	
 	/**
    * Get record from spammers database.
    *
    * @param array $args associative array containing either one (or all) of these: username / email / ip.
    * e.g. $args = array('email' => 'user@example.com', 'ip' => '8.8.8.8', 'username' => 'Spammer?' );
    * @return object Response.
    */
 	public function get( $args )
 	{
 		// should check first if not already in database
 		
 		// url to poll
 		$url = $this->endpoint.'api?f=json&'.http_build_query($args, '', '&');
 		
 		// 
 		return $this->poll_json( $url );
 	}
 	
 	/**
 	* Check if either details correspond to a known spammer. Checking for username is discouraged.
 	*
 	* @param array $args associative array containing either one (or all) of these: username / email / ip
 	* e.g. $args = array('email' => 'user@example.com', 'ip' => '8.8.8.8', 'username' => 'Spammer?' );
 	* @return boolean
 	*/
	public function is_spammer( $args )
	{
		// poll database
		$record = $this->get( $args );

		if( $record === false ) return false;

		// give the benefit of the doubt
		$spammer = false;

		// parse database record
		foreach( $record as $datapoint )
		{

			// not 'success' datapoint AND spammer
			if ( isset($datapoint->appears) && $datapoint->appears == true)
			{
				if( isset($datapoint->confidence) && $this->check_level > 1 ) {
					$datapoint->confidence = intval($datapoint->confidence);

					if ( $this->check_level == 2 AND $datapoint->confidence > 40 ) { $spammer = true; }

					if ( $this->check_level == 3 AND $datapoint->confidence > 80 ) { $spammer = true; }

				} else {

					$spammer = true;

				}
			}
		}

		return $spammer;
	}
 	
 	/**
 	* Get json and decode. Currently used for polling the database, but hoping for future 
 	* json response support, when adding.
 	*
 	* @param string $url The url to get
 	* @return object Response.
 	*/
 	protected static function poll_json( $url )
 	{
		$data = false;
			
		if( function_exists( 'curl_init' ) ) {
				
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_HEADER, 0);
				
			$data = curl_exec( $ch );
			curl_close( $ch );
	
			if( $data !== false ) return json_decode($data);
			
		} 

		if( preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen')) ) {
	
			$data = @file_get_contents( $url );
				
			if( $data !== false ) return json_decode($data);
	
		}
 		
 		return false;
 	}
 }
 ?>