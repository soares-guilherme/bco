<?php
############################################################################

require_once('incs/config.inc.php');

@session_write_close();

@set_time_limit(300);

$file = $CFG->dir_img;

if(!empty($_GET['img']))
	$file .= $_GET['img'];
else
	throw_403();

if(file_exists($file))
	{
		$file_type = MIME::type($file);
		$file_size = @filesize($file);
		$file_mtime = @filemtime($file);
		
		// method 1
		/*if( function_exists('http_send_file') )
			{
				ke_header_cache($CFG->control_cache_days);
				
				http_send_content_type($file_type);
				http_send_last_modified($file_mtime);
				
				http_throttle(0.1, 2048);
				http_send_file($file);
				
				exit;
			}*/
			
		// method 2
		/*ke_header_cache($CFG->control_cache_days, $file_mtime);
		
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
						@usleep(100000);
					}
				
				@fclose($fh);
			}
		
		while (@ob_get_level())
			{
	            @ob_end_clean();
	        }*/
	    
	    // method 3
		ke_header_cache($CFG->control_cache_days, $file_mtime);
		
		@header('Content-Type: '.$file_type);
		@header('Content-Length: '.$file_size);
		
		echo file_get_contents($file);

	    exit;
	}
else
	{
		throw_404();
	}

exit;

############################################################################
?>