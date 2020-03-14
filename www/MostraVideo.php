<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

/*if($CFG->debug)
	usleep(1000000); // 1 sec */

##########################################################################*/

$T=empty($_GET['T'])?0:$_GET['T'];

$CFG->nomain = true;

require_once('incs/config.inc.php');

$tpl_miolo = new TemplatePower($CFG->dir_tpl.'MostraVideo.html');
$tpl_miolo->prepare();

$q = bd_executa("SELECT * FROM bd_youtube WHERE id = '".$_GET['vid']."'", $CFG->con);

if($q->nada)
	die('<script>MostraGaleria.Close();</script>');

$e = $q->res->rid0;

//$tpl_miolo->assign('link_compartilhar', go_area('Imagem', $_GET['uri']) );

$tpl_miolo->assign('vid', $e->vid);

if(!empty($e->descricao))
	{
		$tpl_miolo->newBlock('descricao');
			$tpl_miolo->assign('descricao', utf8_encode($e->descricao) );
	}
if(!empty($e->autor))
	{
		$tpl_miolo->newBlock('autor');
			$tpl_miolo->assign('autor', 'Foto: '.utf8_encode($e->autor) );
	}

// anterior
$q = bd_executa("SELECT id, vid FROM bd_youtube
					WHERE id_mama = '".$e->id_mama."'
						AND id < ".$e->id."
					ORDER BY id DESC", $CFG->con);

$aPag = intval($q->lin) + 1;

$p = $q->res->rid0;

if(!$q->nada)
    { 
        $tpl_miolo->assign('link_anterior', go_area($area, urlencode($p->id)) );
    }
else
    {
        $tpl_miolo->assign('link_anterior', 'javascript:void(null);' );
        $tpl_miolo->assign('link_anterior_adt', 'display:none;' );
    }

// proxima
$q = bd_executa("SELECT id, vid ROM bd_youtube
                    WHERE id_mama = '".$e->id_mama."'
                        AND id > ".$e->id."
                    ORDER BY id ASC", $CFG->con);

$tPag = intval($q->lin) + $aPag;

$p = $q->res->rid0;

if(!$q->nada)
	{
    	$tpl_miolo->assign('link_proximo', go_area($area, urlencode($p->id)) );
	}
else
    {
        $tpl_miolo->assign('link_proximo', 'javascript:void(null);' );
        $tpl_miolo->assign('link_proximo_adt', 'display:none;' );
    }

$tpl_miolo->assign('aPag', $aPag );
$tpl_miolo->assign('tPag', $tPag );                     

$tpl_miolo->gotoBlock('_ROOT');

##########################################################################*/

return $tpl_miolo->getOutputContent();

############################################################################
?>