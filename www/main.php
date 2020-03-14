<?php
##########################################################################*/

$START = microtime(true);

require_once('incs/config.inc.php');

require($CFG->dir_inc.'menu.inc.php'); // menus e links
require($CFG->dir_inc.'print.inc.php'); // blocos

// Ajax
if(isset($_GET['do']))
	require('ajax.php');

// Pega a secao e a area
if(!empty($_GET['secao']))
	$area = $CFG->areas_f[$_GET['secao']];
else
	$area = false;

// Areas fake
if(!$area and isset($CFG->areas_fake[$_GET['secao']]))
	{
		$area = $CFG->areas_f[$CFG->areas_fake[$_GET['secao']]['area']];
		$_GET['sid'] = $CFG->areas_fake[$_GET['secao']]['sid'];
	}

#\_ Force url site
if('http://'.$_SERVER['HTTP_HOST'].'/' != $CFG->url_site and 'https://'.$_SERVER['HTTP_HOST'].'/' != $CFG->url_site)
	ke_head( $CFG->url_site . substr($_SERVER['REQUEST_URI'], 1) );

// Error 404 - Nao encontrado
if(!$area)
	throw_404();

$tpl_main = new TemplatePower($CFG->dir_tpl.'main.html'); // template base
$tpl_main->prepare();

assignGlobalLinks($tpl_main);

##########################################################################*/

$areaFile = $CFG->areas[$area].'.php'; // arquivo da secao

$output = require($areaFile);

$tpl_main->assign('miolo', $output); // inclusao do miolo
$tpl_main->assign('titulo', $CFG->titulo);
$tpl_main->assign('description', $CFG->description);
$tpl_main->assign('keywords', $CFG->keywords);

if($CFG->debug)
	{
		$tpl_main->assign('cacheKiller', time() );
	}

if(!$CFG->nomain)
	{
        // open graph info
        head_opengraph($tpl_main);
        
        if(!empty($CFG->google_analytics_id))
            {
                $tpl_main->newBlock('google_analytics');
                    $tpl_main->assign('google_analytics_id', $CFG->google_analytics_id);
                
                $tpl_main->gotoBlock('_ROOT');
            }
        
		$output = processBanners( $tpl_main->getOutputContent() ); // saida

		$output = safe_javascript($output);
	
		// Impressao do Debug
		if($CFG->debug) // config.inc.php
			$output .= print_debug_window($CFG->erros, (microtime(1) - $START));
	}

// Salva navegacao
$Session['NAVIGATION']['LAST'] = $_GET['secao'];

@header('Content-Type: text/html', true);

// Impressao
echo optimize($output);
exit;

##########################################################################*/
?>