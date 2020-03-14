<?php

require($CFG->dir_lib.'facebook/base_facebook.php');
require($CFG->dir_lib.'facebook/facebook.php');

class FacebookI {
	
	public static $fb = NULL;
	public static $user = NULL;
	public static $user_profile = NULL;
	
	private static function init()
		{
			if(self::$fb == NULL)
				{
					self::$fb = new Facebook(array(
					  'appId' => $GLOBALS['CFG']->fb_appid,
					  'secret' => $GLOBALS['CFG']->fb_secret,
					));
				}
		}
	
	public static function verify()
		{
			self::init();
			
			return self::$fb->getUser();
		}
	
	public static function user()
		{
			self::init();
			
			if(self::$user == NULL)
				{
					self::$user = self::$fb->getUser();

					if (self::$user)
						{
							try
								{
									self::$user_profile = self::$fb->api('/me');
								}
							catch (FacebookApiException $e)
								{
									self::$user = NULL;
								}
						}
				}
			
			return self::$user;
		}
	
	public static function avatarOf($user_id)
		{
			return 'https://graph.facebook.com/'.$user_id.'/picture';
		}
	
	public static function publishFeed($data, $user_id='me')
		{
			self::init();
			
       		try 
       			{
					$permissions = FacebookI::$fb->api('/'.$user_id.'/permissions');

					if(array_key_exists('publish_stream', $permissions['data'][0])) 
			            {
       					   	FacebookI::$fb->api('/'.$user_id.'/feed', 'post', $data);	
							
							return true;
						}
        		}
 			catch (FacebookApiException $e) 
 				{ ;	}
 			
 			return false;
		}
}

?>