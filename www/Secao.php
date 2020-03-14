<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

$T=empty($_GET['T'])?0:$_GET['T']; // paginador
$PP=empty($_GET['PP'])?10:$_GET['PP']; // itens por pagina
$sid=empty($_GET['sid'])?0:$_GET['sid']; // id da seção

require_once('incs/config.inc.php');

$tpl = new TemplatePower($CFG->dir_tpl.'Secao.html');
$tpl->prepare();
 
// Materias
$q = bd_executa("SELECT m.*, s.secao, s.destaque AS secao_destaque
					FROM materias AS m, mat_secoes AS s
						WHERE (s.id = '".$sid."' OR s.id_parent = '".$sid."')
							AND m.status = '1'
							AND m.destaque > '0'
							AND m.id_secao = s.id   
						ORDER BY m.publish_date DESC, m.id DESC", $CFG->con, $PP, $T);
						
if($q->nada)
	{
		$tpl->newBlock('nada');
	}
elseif($q->lin == 1) //abre materia
	{
		$e = $q->res->rid0;
		
		ke_head( go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );
	} 
else//imprime lista de materias
	{
		foreach($q->res as $e)
			{
				// thumb
				$img = bd_executa("SELECT i.img, i.family
									FROM bd_img AS i, rel AS r
										WHERE r.um = 'materias'
											AND r.dois = 'galerias'
											AND r.id_um = '".$e->id."'
											AND r.id_dois = i.id_mama
											AND i.size = '".$CFG->fullsize."'
										ORDER BY i.descricao ASC, i.id DESC LIMIT 1", $CFG->con);

				if($img->nada and !empty($e->thumb))
					{
						$img = bd_executa("SELECT img, family
											FROM bd_img 
												WHERE family = '".$e->thumb."' 
													AND size = '".$CFG->midsize."'
												LIMIT 1", $CFG->con);
					}

				if($img->nada) // img default
					{
						$img = $CFG->url_tpl.'/img/imagem_default.png';
					}
				else
					{
						$img = $CFG->url_img.$img->res->rid0->img;
					}

					
            	if(empty($e->publish_date))
            		{
						$e->publish_date = $e->data;
            		}
            	
            	$date_time = strtotime($e->publish_date);
            	$data = mb_strtoupper( date('d', $date_time).' DE '.z1::get_Months(date('m', $date_time)).' '.date('Y', $date_time) );
            	
                $tpl->newBlock('item');
                    $tpl->assign('titulo', $e->titulo.'| '.'Pagina/'.$e->id.'/'.url_simple($e->titulo) );
                    $tpl->assign('resumo', $e->resumo );
                    $tpl->assign('img', $img );
                    $tpl->assign('data', $data );
                    $tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );
			}

		$tpl->gotoBlock('_ROOT');

		// Paginador
        if($q->lin > $PP)
            {
    		    print_paginator($q, $tpl, $T, $PP, go_area($area, $sid.'/'.url_simple($secao->secao).'/'), 'paginador', 5);
            }
	}

// prints
assignGlobalLinks($tpl);
$tpl->gotoBlock('_ROOT');
                                            
##########################################################################*/

return $tpl->getOutputContent();

############################################################################
?>