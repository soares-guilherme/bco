<?php

require('../incs/config.inc.php');

$output = '';

// debugging
if($CFG->debug)
	{
        $output = 'window.onerror = function (m,a1,a2,a3) { alert("ERRO: "+m+"\n\n"+a1+"\n"+a2); }'.NL;
	}

// z1Alert
$alert = file_get_contents('./z1alert.html');
$alert = str_replace(array(NL, "\n", "\t", "'"), array("", "", "", "\\'"), $alert);

// vars globais
$CFG->vars_js['z1img_size'] = $CFG->fullsize;
$CFG->vars_js['z1img_win'] = $CFG->url_mimg;
$CFG->vars_js['url_mvid'] = $CFG->url_mvid;
$CFG->vars_js['url_maemp'] = $CFG->url_maemp;
$CFG->vars_js['url_mlocal'] = $CFG->url_mlocal;
$CFG->vars_js['url_mmateria'] = $CFG->url_mmateria;
$CFG->vars_js['url_mcidade'] = $CFG->url_mcidade;
$CFG->vars_js['url_files'] = $CFG->url_files;
//$CFG->vars_js['z1img_siz_w'] = $CFG->mimgsize_ray[0];
//$CFG->vars_js['z1img_siz_h'] = $CFG->mimgsize_ray[1];
$CFG->vars_js['z1pup_url'] = $CFG->url_sit.'Ajax/sendamigo/';
$CFG->vars_js['default_fontsize'] = '10';
$CFG->vars_js['current_fontsize'] = !empty($_SESSION['DEFAULT']['FONTSIZE'])?$_SESSION['DEFAULT']['FONTSIZE']:'default_fontsize';
$CFG->vars_js['url_fontsize'] = $CFG->url_sit.'Ajax/updatefontsize&fontsize=';
$CFG->vars_js['url_ajax'] = $CFG->url_sit.'Ajax/';
$CFG->vars_js['z1Alert_html'] = $alert;
$CFG->vars_js['url_aovivo'] = $CFG->url_sit.'AoVivo';
$CFG->vars_js['aovivo_siz_w'] = 574;
$CFG->vars_js['aovivo_siz_h'] = 240;

// vars
foreach($CFG->vars_js as $k => $e)
	{
		$output .= "var ".$k." = '".$e."';".NL;
	}

// libs
foreach($CFG->files_js as $file)
	{
		$output .= file_get_contents('./'.$file).NL;
	}


ke_header_cache($CFG->control_cache_days);

// imprime
@header('Content-Type: text/javascript', true);

echo optimize($output);
exit;

?>