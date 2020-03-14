<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

/*if($CFG->debug)
	usleep(1000000); // 1 sec */
	
$T=empty($_GET['T'])?0:$_GET['T'];

$CFG->nomain = true;

require_once('incs/config.inc.php');

$tpl_miolo = new TemplatePower($CFG->dir_tpl.'MostraImagem.html');
$tpl_miolo->prepare();

// imagem
$q = bd_executa("SELECT * FROM bd_img WHERE img = '".$_GET['uri']."'", $CFG->con);

if($q->nada)
	{
		die('<script>MostraGaleria.Close();</script>');
	}

$e = $q->res->rid0;

$tpl_miolo->assign('img', $CFG->url_img.$e->img);
$tpl_miolo->assign('descricao', utf8_encode($e->descricao) );
$tpl_miolo->assign('fotografo', utf8_encode($e->autor) );
//$tpl_miolo->assign('link_compartilhar', go_area('Imagem', $_GET['uri']) );

$filter = '';

if($e->id_mama == '42')
	{
		$filter = " AND destaque > '0' ";
	}

$q = bd_executa("SELECT id, img FROM bd_img
					WHERE id_mama = '".$e->id_mama."'
						AND size = '".$e->size."'
						".$filter."
					ORDER BY destaque DESC, img_order ASC, id DESC", $CFG->con);

$s_i = 0;
$tPag = $q->lin;
$aPag = NULL;
$p = NULL;
$n = NULL;
$last_sibling = NULL;

foreach($q->res as $sibling)
	{
		$s_i++;
		
		if(!is_null($aPag) and is_null($n))
			{
				$n = $sibling;
			}
		
		if($sibling->id == $e->id)
			{
				$aPag = $s_i;
				$p = $last_sibling;
			}
		
		$last_sibling = $sibling;
	}

if(!is_null($p))
	{
		$tpl_miolo->assign('link_anterior', go_area($area, urlencode($p->img)) );
	}
else
	{
		$tpl_miolo->assign('link_anterior', 'javascript:void(null);' );
		$tpl_miolo->assign('link_anterior_adt', 'display:none;' );
	}

if(!is_null($n))
	{
		$tpl_miolo->assign('link_proximo', go_area($area, urlencode($n->img)) );
	}
else
	{
		$tpl_miolo->assign('link_proximo', 'javascript:void(null);' );
		$tpl_miolo->assign('link_proximo_adt', 'display:none;' );
	}

$tpl_miolo->assign('aPag', $aPag );
$tpl_miolo->assign('tPag', $tPag );

/*/ thumbs
$thumbs = bd_executa("SELECT img FROM bd_img
						WHERE id_mama = '".$e->id_mama."'
							AND size = '".$CFG->thumbsize."'
						ORDER BY id ASC", $CFG->con);

if(!$thumbs->nada and $thumbs->lin > 1)
	{
		$tpl_miolo->newBlock('thumbs');
		
		foreach($thumbs->res as $i)
			{
				$tpl_miolo->newBlock('thumb');
					$tpl_miolo->assign('img', $CFG->url_img.$i->img);
			}
	} /**/

##########################################################################*/

return $tpl_miolo->getOutputContent();

############################################################################
?>