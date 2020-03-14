<?php

require_once($CFG->dir_lib.'captcha/securimage.php');

class Captcha {
	
	static $img = NULL;
	
	public static function getInstance($id)
		{
		    $options = array('ttf_file' => $GLOBALS['CFG']->dir_lib.'captcha/securimage.ttf', 
    						'captchaId' => $id, 
    						'no_session' => true,
    						'use_database' => true,
    						'audio_path' => $GLOBALS['CFG']->dir_lib.'captcha/audio/pt/', 
    						'audio_noise_path' => $GLOBALS['CFG']->dir_lib.'captcha/audio/noise/', 
    						'audio_use_noise' => true,
    						'degrade_audio' => true,
    						'code_length' => 4, 
    						'image_width' => 200, 
    						'image_height' => 90, 
    						'font_size_multiplier' => 0.8, 
    						'charset' => 'ABCDEFGHKLMNPRSTUVWZ3689acdegijklmnopstuvxz', 
    						'text_angle_minimum' => -20, 
    						'text_angle_maximum' => 20, 
    						'text_color' => '#000000', 
    						'line_color' => '#eeeeee', 
    						'perturbation' => 0.75, 
    						'num_lines' => 3, 
    						'text_transparency_percentage' => 40, 
    						'text_angle_maximum' => 20);
    						
			if( empty($id) )
				{
					$options['captchaId'] = $_SESSION['CAPTCHA'] = md5(microtime() . '|' . rand(1,9));
				}
			
			$securimage = new Securimage( $options );
			
			if( empty($id) )
			    {
			        $securimage->createCode();
			    }
			
			return $securimage;
		}
	
	private static function init()
		{			
			if(self::$img == NULL)
				{
					self::$img = self::getInstance($_GET['salt']);
				}
		}
	
	public static function show()
		{
			self::init();
			
			self::$img->show($GLOBALS['CFG']->dir_lib.'captcha/securimage_'.round(rand(1,6)).'.png');
			
			exit;
		}
	
	public static function play()
		{
			self::init();
			
			self::$img->outputAudioFile();
			
			exit;
		}
	
	public static function get($action='show')
		{
			self::init();
			
			if($action=='show')
				{			
					return go_area('Captcha', self::getId());
				}
			
			return go_area('Captcha', self::getId().'/'.$action);
				
		}
	
	public static function getId()
		{
			self::init();
			
			return self::$img->getCaptchaId(false);
		}

	public static function getHTML()
		{
			self::init();
			
			$uri = '/libs/captcha/securimage_play.swf?audio_file='.urlencode(self::get('play')).'&amp;icon_file=/libs/captcha/audio_icon.png&amp;bgcol=#ffffff';
			
			$r = '<img align="absmiddle" src="'.self::get().'" style="height: 90px; width: 200px; margin-left: 15px;" />'.
					'<p style="margin: 0 5px 0 10px; font-size: 75%; display: inline-block;"><object type="application/x-shockwave-flash" data="'.$uri.'" height="32" width="32">'.
				    '<param name="movie" value="'.$uri.'" />'.
				    '</object> <br>ouvir</p>';

    		return $r;
		}
	
	public static function check($id, $code)
		{
			self::init();
			
			return self::$img->checkByCaptchaId($id, $code);
		}
	
	public static function getCode()
		{
			self::init();
			
			$code = self::$img->getCode();
			
			return $code['code'];
		}
}

?>