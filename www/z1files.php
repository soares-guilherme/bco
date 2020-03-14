<?php
############################################################################

require_once('incs/config.inc.php');

@session_write_close();

@set_time_limit(300);

$file = $CFG->dir_files;

if(!empty($_GET['path']))
	{
		$file .= $_GET['path'];
	}

if(strpos($file, '..') !== false)
	{
		throw_403();
	}

if(is_dir($file))
	{
		if($_GET['key'] != 'ce51900c9dfc90ffe27ee3b6845576fc')
			{
				throw_403();
			}

		$auth = 'key='.$_GET['key'];
		
		echo '<pre>';
		print_list($file);
		
		exit;
	}

if(file_exists($file))
	{
		$file_type = MIME::type($file);
		$file_size = @filesize($file);
		$file_mtime = @filemtime($file);
		
		// method 1
		if( function_exists('http_send_file') )
			{
				ke_header_cache($CFG->control_cache_days);
				
				http_send_content_type($file_type);
				http_send_last_modified($file_mtime);
				
				http_throttle(0.1, 2048);
				http_send_file($file);
				
				exit;
			}
			
		// method 2		
		ke_header_cache($CFG->control_cache_days, $file_mtime);
		
		@header('Content-Type: '.$file_type);
		@header('Content-Length: '.$file_size);
		
		if($file_size <= 24576)
			{
				echo ke_optimize_gzip( file_get_contents($file) );
				@ob_flush();
				@flush();
			}
		else
			{		
				$fh = @fopen($file, 'r');
				while(!@feof($fh) and !connection_aborted())
					{
						echo @fread($fh, 2048);
						@ob_flush();
						@flush();
						@usleep(50000);
					}
				
				@fclose($fh);
			}
		
		while (@ob_get_level())
			{
	            @ob_end_clean();
	        }
	    
	    exit;
	}
else
	{
		throw_404();
	}

############################################################################

function print_list($dir, $depth=0)
	{
		global $auth;
		$files = scandir($dir);
		$list = array();
		
		$d_str = '';
		
		for($i=0;$i<$depth;$i++)
			{
				$d_str .= '    ';
			}

		$c = 0;
		
		foreach($files as $file)
			{
				if($file == '.' or $file == '..' or $file == '.file' or $file == '.svn')
					continue;
				
				if(substr($file, -9) != '.htaccess' and substr($file, -9) != '.htpasswd')
					{
						$list[] = $file;
					}
			}
		
		if(!count($list) > 0)
			{
				echo '<p>'.$d_str.' - </p>';
			}
		else
			{
				foreach($list as $file)
					{
						$href = $dir.$file;
						$a = basename($file, '.php');
						
						if(is_dir($dir.$file))
							{
								echo '<h3>'.$d_str.'- <a href="'.$href.'/?'.$auth.'">'.$a.'</a></h3>';
								
								print_list($dir.$file.'/', $depth+1);
							}
						else
							{
								echo '<p>'.$d_str.(++$c).'. <a href="'.$href.'">'.$a.'</a></p>';
							}
					}
			}
	}

############################################################################
?>