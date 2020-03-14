<?php
#```` PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

$mid=empty($_GET['mid'])?false:$_GET['mid'];
                                    
// Cfg
require_once('incs/config.inc.php');

$tpl = new TemplatePower($CFG->dir_tpl.'Pagina.html');
$tpl->prepare();

$tpl->gotoBlock('_ROOT');

// Texto
$q = bd_executa("SELECT * FROM materias WHERE id = '".$mid."' LIMIT 1", $CFG->con);
		
if(!$q->nada)
	{
		$e = $q->res->rid0;                        

		$secoes = get_secao_branch($e->id_secao);

		foreach($secoes as $s)
			{
				$link = '';
				
				if($s['destaque'] == '1')
					{
						$link = go_area('Secao', $s['id'].'/'.url_simple($s['secao']));
					}
				
				$CFG->breadcrumb[$s['secao']] = $link;
				
				$CFG->titulo .= ' - '.$s['secao'];
			}

		$CFG->titulo .= ' - '.$e->titulo;

		/*if(!empty($e->publish_date))
			$e->data = $e->publish_date;*/

		$link_self = go_area($area, $mid.'/'.url_simple($e->titulo));
		
		$tpl->assign('titulo', mb_strtoupper($e->titulo, 'ISO-8859-1') );                    
		$tpl->assign('texto', $e->texto );
		$tpl->assign('resumo', $e->resumo );
		$tpl->assign('autor', mb_strtoupper($e->autor) );
		$tpl->assign('data', date('G\\Hi d/m/Y', strtotime($e->data)) );
        $tpl->assign('link_self', $link_self );
        $tpl->assign('link_self_encoded', urlencode($link_self) );
		
		$tpl->gotoBlock('_ROOT');
		
		// banner - arquivo
		$banner = bd_executa("SELECT a.arquivo 
								FROM arquivos AS a, rel AS r 
									WHERE r.um = 'materias'
										AND r.dois = 'arq_galerias'
										AND r.id_um = '".$mid."'
										AND r.id_dois = a.id_mama
									ORDER BY a.id DESC LIMIT 1", $CFG->con);
		
		if(!$banner->nada)
			{
				$banner = $CFG->url_files.$banner->res->rid0->arquivo;

				$tpl->newBlock('banner_materia');
					$tpl->assign('img', $banner);

				$tpl->gotoBlock('_ROOT');
			}
		
		// update hits
		bd_executa("UPDATE materias SET hits = hits+1 WHERE id = '".$mid."' LIMIT 1", $CFG->con);
        
        // open grath - facebook
        $CFG->head_opengraph['title'] = $e->titulo;
        $CFG->head_opengraph['description'] = trim( preg_replace('/\s+/', ' ', substr($e->resumo, 0, 195).'...') );
        $CFG->head_opengraph['url'] = $link_self;
    
        if(empty($e->thumb))
            {
                $thumb = bd_executa("SELECT i.img 
                                        FROM bd_img AS i, rel AS r 
                                            WHERE r.um = 'materias' 
                                                AND r.dois = 'galerias'
                                                AND r.id_um = '".$e->id."' 
                                                AND r.id_dois = i.id_mama
                                                AND i.size = '".$CFG->fullsize."'
                                            ORDER BY i.destaque DESC, i.img_order ASC, i.id DESC LIMIT 1", $CFG->con);
                
                if(!$thumb->nada)
                    {
                        $CFG->head_opengraph['image'] = $CFG->url_img.$thumb->res->rid0->img; // imagem do open graph
                        $CFG->head_opengraph['image_bydir'] = $CFG->dir_img.$thumb->res->rid0->img;
                        
                        /*/ imprime foto principal
                        if(!$ajax)
                            {
                                $tpl->newBlock('imagem');
                                    $tpl->assign('img', $thumb);
                            } /**/
                    }
            }
        else
            {
                $thumb = bd_executa("SELECT img FROM bd_img WHERE family = '".$e->thumb."' AND size = '".$CFG->fullsize."' LIMIT 1", $CFG->con);
                
                if(!$thumb->nada)
                    {
                        $CFG->head_opengraph['image'] = $CFG->url_img.$thumb->res->rid0->img; // imagem do open graph
                        $CFG->head_opengraph['image_bydir'] = $CFG->dir_img.$thumb->res->rid0->img;
                    }
            }
        
		$tpl->gotoBlock('_ROOT');
        
        // galeria
        $images = bd_executa("SELECT i.img, i.img_order
                            FROM bd_img AS i, rel AS r
                                WHERE r.um = 'materias'
                                    AND r.dois = 'galerias'
                                    AND r.id_um = '".$e->id."'
                                    AND r.id_dois = i.id_mama
                                    AND i.size = '".$CFG->fullsize."'
                                ORDER BY i.img_order ASC", $CFG->con);

        if($images->nada)
            {
                $tpl->newBlock('image');
                    $tpl->assign('img', $CFG->url_img.'default_'.$CFG->fullsize.'.png');
            }
        else
            {                	
                foreach($images->res as $thumb)
                    {
                        $tpl->newBlock('image');
                            $tpl->assign('img', $CFG->url_img.$thumb->img);
	                    
		                // imagem do open graph
		                $CFG->head_opengraph['images'][] = array( 'url' => $CFG->url_img.$thumb->img,
	                    										'path' => $CFG->dir_img.$thumb->img );
                    }
            }
	}

$tpl->gotoBlock('_ROOT');

// prints
assignGlobalLinks($tpl);
$tpl->gotoBlock('_ROOT');

##########################################################################*/

return $tpl->getOutputContent();

############################################################################
?>