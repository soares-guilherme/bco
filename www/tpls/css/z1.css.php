<?php

require('../../incs/config.inc.php');

$output = '';

// estilos
foreach($CFG->files_css as $file)
	{
		$output .= file_get_contents('./'.$file).NL;
	}

ke_header_cache($CFG->control_cache_days);

// imprime
@header('Content-Type: text/css', true);

echo optimize($output);
exit;

?>