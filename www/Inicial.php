<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

// Cfg
require_once('incs/config.inc.php');

$tpl = new TemplatePower($CFG->dir_tpl.'Inicial.html');
$tpl->prepare();

$CFG->titulo .= '';

// Materias
$q = bd_executa("SELECT m.*, s.secao, s.destaque AS secao_destaque
					FROM materias AS m, mat_secoes AS s
						WHERE s.id = '2'
							AND m.destaque > '0'
							AND m.id_secao = s.id   
						ORDER BY m.publish_date DESC, m.id DESC
						LIMIT 3", $CFG->con);

if(!$q->nada)
    {
        foreach($q->res as $e)
            {
				$img = bd_executa("SELECT i.img
									FROM bd_img AS i, rel AS r, galerias AS g
									WHERE r.um = 'materias'
										AND r.dois = 'galerias'
										AND r.id_um = '".$e->id."'
										AND r.id_dois = g.id
										AND i.id_mama = g.id
										AND i.size = '".$CFG->thumbsize."'
									ORDER BY i.id ASC LIMIT 1", $CFG->con);

				if($img->nada)
					{
						$e->img = 'default_'.$CFG->thumbsize.'.png';
					}
				else
					{
						$e->img = $img->res->rid0->img;
					}
					
                $tpl->newBlock('item');
                    $tpl->assign('titulo', $e->titulo );
                    $tpl->assign('resumo', $e->resumo );
                    $tpl->assign('img', $CFG->url_img.$e->img );
                    $tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );
            }
    }

$tpl->gotoBlock('_ROOT');

// prints
print_bt($tpl);
print_contato($tpl);
assignGlobalLinks($tpl);
$tpl->gotoBlock('_ROOT');

##########################################################################*/

return $tpl->getOutputContent();

############################################################################
?>