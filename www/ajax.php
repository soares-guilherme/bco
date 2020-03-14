<?php
##########################################################################*/

if(!isset($CFG) or !isset($_GET['do']))
	exit;

#\_ Ajax integrante
if($_GET['do'] == 'LoadProduto')
	{
		$tid=empty($_GET['tid'])?0:$_GET['tid'];

		$tpl_ajax = new TemplatePower($CFG->dir_tpl.'ajax_produto.html');
		$tpl_ajax->prepare();
		
		// Produtos
		$q = bd_executa("SELECT m.*, i.img
							FROM produtos AS m, rel AS r, bd_img AS i
								WHERE m.status = '1'
									AND r.um = 'produtos'
									AND r.dois = 'galerias'
									AND r.id_um = m.id
									AND r.id_dois = i.id_mama
									AND i.size = '".$CFG->thumbsize."'
									AND m.id = ".$tid." 
								ORDER BY m.id DESC LIMIT 1", $CFG->con);
								
		/*echo "SELECT m.*, i.img
							FROM produtos AS m, rel AS r, bd_img AS i
								WHERE m.status = '1'
									AND r.um = 'produtos'
									AND r.dois = 'galerias'
									AND r.id_um = m.id
									AND r.id_dois = i.id_mama
									AND i.size = '".$CFG->thumbsize."'
									AND m.id = ".$tid." 
								ORDER BY m.id DESC ";exit;*/

		if(!$q->nada)
			{
				foreach($q->res as $e)
					{
						$tpl_ajax->newBlock('imagem_produto');
							$tpl_ajax->assign('id', $e->id );
							$tpl_ajax->assign('nome', $e->nome );
							$tpl_ajax->assign('texto', $e->texto );
							/*$tpl_ajax->assign('img', $CFG->url_img.$e->img );
							$tpl_ajax->assign('caracteristicas', $e->caracteristicas );*/
						
					}
			}
		else
			{
				$tpl_ajax->newBlock('nada_portifolio');
			}

		$tpl_ajax->gotoBlock('_ROOT');

		echo optimize( $tpl_ajax->getOutputContent() );
		exit;
	}

#\_ Ajax Matérias
if($_GET['do'] == 'calendario' and !empty($_GET['year']) and !empty($_GET['month']) )
	{
		$tpl_ajax = new TemplatePower($CFG->dir_tpl.'include_agenda.html');
		$tpl_ajax->prepare();
		
		print_calendario($tpl_ajax, $_GET['year'], $_GET['month']);

		echo optimize( $tpl_ajax->getOutputContent() );
	}

#\_ Ajax Envia para Amigo
if($_GET['do'] == 'sendamigo')
	{
		if(!empty($n) and !empty($e) and !empty($r))
			{
				$tpl_msg = new TemplatePower($CFG->dir_tpl.'MailAmigo.html');
				$tpl_msg->prepare();
				$tpl_msg->assign('amigo', $n);
				$tpl_msg->assign('nome', $r);
				$tpl_msg->assign('link', $_SERVER['HTTP_REFERER']);
				$tpl_msg->assign('mensagem', $m);
				
				$msg = $tpl_msg->getOutputContent();
		
				if(mailTo( $e , $CFG->contato, 'Página do '.$CFG->url_site, $msg))
					{
						mailTo( $CFG->contatos , $CFG->contato, 'Página do '.$CFG->url_site, $msg);
						addMail($n, $e);
						echo 'OK';
						exit;
					}
				else
					{
						echo 'Sistema indisponivel no momento, tente novamente mais tarde.';
						exit;
					}
					
			}
		else
			{
				echo 'Você deve preencer todos os campos do formulário, exceto o campo "Mensagem" que é opcional.';
				exit;
			}
	}

#\_ Ajax Consulta UF
if($_GET['do'] == 'consultauf' and !empty($_GET['uf']) )
	{
		$clientes = bd_executa("SELECT * FROM ibge_cidades WHERE uf = '".$_GET['uf']."'", $CFG->con);
		
		if($clientes->nada)
			{
				$tag = 'ERROR';
			}
		else
			{
				$tag = '';
								
				foreach($clientes->res as $e)
					{
						$tag .= '<option value="'.$e->id.'">'.utf8_encode_once($e->cidade).'</option>';
					}
			}
		
		echo $tag;
		exit;
	}

#\_ Ajax Consulta CEP
if($_GET['do'] == 'getGeoCode')
	{
		$coords = httpReplyBody( httpsRequest('z1panel.net',
												'/Service/f4ef7f544f078b2348a00463067a3c99/getGeoCode/q='.urlencode($_GET['q'])
											) );

		if($coords === false)
			{
				echo 'ERROR';
				exit;
			}
		else
			{
				echo $coords;
				exit;
			}
	}	
	
#\_ Ajax Consulta CEP
if($_GET['do'] == 'consultacep')
	{
		$field_prefix = !empty($_GET['field_prefix']) ? $_GET['field_prefix'] : '';
		
		$CEP = httpReplyBody( httpsRequest($CFG->z1panel_host, 
											'/Service/'.$CFG->key_consultacep.'/consultaCep', 
											array('cep' => $_GET['cep'])
											) );
		
		if($CEP === false)
			{
				echo 'ERROR';
				exit;
			}
		else
			{
				$xml = new XMLPARSER($CEP);
				
				$r = $xml->parse();
				$r = $r['z1main']['reply'];
				
				echo "getE('".$field_prefix."endereco').value = '".utf8_encode_once($r['endereco'])."';".NL;
				echo "if(getE('".$field_prefix."id_cidade')) { getE('".$field_prefix."id_cidade').innerHTML = '<option value=\"".$r['id_cidade']."\" selected=\"selected\">".utf8_encode_once($r['cidade'])."</option>'; }".NL;
				echo "if(getE('".$field_prefix."cidade')) { getE('".$field_prefix."cidade').value = '".utf8_encode_once($r['cidade'])."'; }".NL;
				echo "getE('".$field_prefix."bairro').value = '".utf8_encode_once($r['bairro'])."';".NL;
				echo "getE('".$field_prefix."estado').value = '".utf8_encode_once($r['uf'])."';".NL;
				echo '<!-- ENDJS -->';
				exit;
			}
	}

#\_ Ajax BPag Probe
if($_GET['do'] == 'bpagprobe')
	{
		httpsRequest(URL_Z1PANEL, 'Service/'.$Commerce->key_bpag.'/Bell/receita='.$_GET['receita'].'&probe=yes');
	}
	
#\_ Mapa
if($_GET['do'] == 'DiagnosticosOrganizacionais')
	{
		$error = true;
		
		$tpl = new TemplatePower($CFG->dir_tpl.'mapa_resultado.html');
		$tpl->prepare();

		if(!empty($_GET['uf']))
			{
				$uf = $_GET['uf'];
				
				$Diagnosticos = bd_executa("SELECT COUNT(*) AS total, t.tipo
												FROM diag_org AS d, diag_org_tipos AS t
													WHERE d.estado = '".$uf."' 
														AND d.status = '1'
														AND d.data > DATE_SUB(NOW(), INTERVAL 1 YEAR )
														AND d.id_tipo = t.id
													GROUP BY d.id_tipo
													ORDER BY t.tipo ASC", $CFG->con);
				
				if(!$Diagnosticos->nada)
					{
						$error = false;
						$total = 0;

						$tpl->newBlock('lista');
							$tpl->assign('titulo', 'DIAGNÓSTICOS');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );
							$tpl->assign('adt', ' (últimos 12 meses)' );
							
						// imprime
						foreach($Diagnosticos->res as $t)
							{
								$total += $t->total;

								$tpl->newBlock('item');
									$tpl->assign('tipo', $t->tipo);
									$tpl->assign('soma', $t->total);
							}

						$tpl->assign('lista.total', $total);

						$Fm = new FORM('form_mapa_filtro', go_area($area), $CFG->estilo, 'z1alert');
							$Fm->create_tag_hidden('estado', $uf);
							$Fm->create_tag_submit('mapa_submit', 'VER TODOS PARA ESTE ESTADO', '', 'style="float:right;"');
						$Fm->build($tpl, 'mapa_filtro');
					}
				else
					{
						$error = false;

						$tpl->newBlock('nenhum');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );
					}
			}

		if($error)
			{
				$tpl->newBlock('error');
			}
		
		echo $tpl->getOutputContent();
		exit;
	}

if($_GET['do'] == 'OrganizacoesCertificadas')
	{
		$error = true;
		
		$tpl = new TemplatePower($CFG->dir_tpl.'mapa_resultado.html');
		$tpl->prepare();

		if(!empty($_GET['uf']))
			{
				$uf = $_GET['uf'];
				
				$Certificadas = bd_executa("SELECT COUNT(*) AS total, t.tipo
												FROM opss AS c, opss_tipos AS t
												WHERE c.status = '1'
													AND c.divulgar = '1'
													AND c.estado = '".$uf."' 
													AND c.data_validade > NOW()
													AND ( c.data_suspensao_ini = '0000-00-00 00:00:00'	
														OR c.data_suspensao_ini IS NULL 
														OR c.data_suspensao_ini > NOW() 
														OR c.data_suspensao_end < NOW() )
													AND c.id_tipo = t.id
												GROUP BY c.id_tipo
												ORDER BY t.tipo ASC", $CFG->con);
				
				if(!$Certificadas->nada)
					{
						$error = false;
						$total = 0;

						$tpl->newBlock('lista');
							$tpl->assign('titulo', 'CERTIFICADOS');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );

						// imprime
						foreach($Certificadas->res as $t)
							{
								$total += $t->total;

								$tpl->newBlock('item');
									$tpl->assign('tipo', $t->tipo);
									$tpl->assign('soma', $t->total);
							}

						$tpl->assign('lista.total', $total);

						$Fm = new FORM('form_mapa_filtro', go_area($area), $CFG->estilo, 'z1alert');
							$Fm->create_tag_hidden('estado', $uf);
							$Fm->create_tag_submit('mapa_submit', 'VER TODOS PARA ESTE ESTADO', '', 'style="float:right;"');
						$Fm->build($tpl, 'mapa_filtro');
					}
				else
					{
						$error = false;

						$tpl->newBlock('nenhum');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );
					}
			}

		if($error)
			{
				$tpl->newBlock('error');
			}
		
		echo $tpl->getOutputContent();
		exit;
	}
	
if($_GET['do'] == 'CertificadosSuspensos')
	{
		$error = true;
		
		$tpl = new TemplatePower($CFG->dir_tpl.'mapa_resultado.html');
		$tpl->prepare();

		if(!empty($_GET['uf']))
			{
				$uf = $_GET['uf'];
				
				$Suspensos = bd_executa("SELECT COUNT(*) AS total, t.tipo
												FROM opss AS c, opss_tipos AS t
													WHERE c.estado = '".$uf."' 
														AND c.status = '1'
														AND c.id_tipo = t.id
														AND ( c.data_suspensao_ini <> '0000-00-00 00:00:00'	
															AND c.data_suspensao_ini IS NOT NULL 
															AND c.data_suspensao_ini < NOW() 
															AND ( c.data_suspensao_end = '0000-00-00 00:00:00'	
																OR c.data_suspensao_end IS NULL 
																OR c.data_suspensao_end > NOW() ) )
														GROUP BY c.id_tipo
														ORDER BY t.tipo ASC", $CFG->con);		
				
				if(!$Suspensos->nada)
					{
						$error = false;
						$total = 0;

						$tpl->newBlock('lista');
							$tpl->assign('titulo', 'CERTIFICADOS SUSPENSOS');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );

						// imprime
						foreach($Suspensos->res as $t)
							{
								$total += $t->total;

								$tpl->newBlock('item');
									$tpl->assign('tipo', $t->tipo);
									$tpl->assign('soma', $t->total);
							}

						$tpl->assign('lista.total', $total);

						$Fm = new FORM('form_mapa_filtro', go_area($area), $CFG->estilo, 'z1alert');
							$Fm->create_tag_hidden('estado', $uf);
							$Fm->create_tag_submit('mapa_submit', 'VER TODOS PARA ESTE ESTADO', '', 'style="float:right;"');
						$Fm->build($tpl, 'mapa_filtro');
					}
				else
					{
						$error = false;

						$tpl->newBlock('nenhum');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );
					}
			}

		if($error)
			{
				$tpl->newBlock('error');
			}
		
		echo $tpl->getOutputContent();
		exit;
	}
	
if($_GET['do'] == 'CertificadosCancelados')
	{
		$error = true;
		
		$tpl = new TemplatePower($CFG->dir_tpl.'mapa_resultado.html');
		$tpl->prepare();

		if(!empty($_GET['uf']))
			{
				$uf = $_GET['uf'];
				
				$Cancelados = bd_executa("SELECT COUNT(*) AS total, t.tipo
												FROM opss AS c, opss_tipos AS t
													WHERE c.estado = '".$uf."' 
														AND c.status = '1'
														AND c.id_tipo = t.id
														AND c.data_cancelamento <> '0000-00-00 00:00:00'
														AND c.data_cancelamento > DATE_SUB(NOW(), INTERVAL 1 YEAR)
													GROUP BY c.id_tipo
												ORDER BY t.tipo ASC", $CFG->con);
				
				if(!$Cancelados->nada)
					{
						$error = false;
						$total = 0;

						$tpl->newBlock('lista');
							$tpl->assign('titulo', 'CANCELADOS');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );

						// imprime
						foreach($Cancelados->res as $t)
							{
								$total += $t->total;

								$tpl->newBlock('item');
									$tpl->assign('tipo', $t->tipo);
									$tpl->assign('soma', $t->total);
							}

						$tpl->assign('lista.total', $total);

						$Fm = new FORM('form_mapa_filtro', go_area($area), $CFG->estilo, 'z1alert');
							$Fm->create_tag_hidden('estado', $uf);
							$Fm->create_tag_submit('mapa_submit', 'VER TODOS PARA ESTE ESTADO', '', 'style="float:right;"');
						$Fm->build($tpl, 'mapa_filtro');
					}
				else
					{
						$error = false;

						$tpl->newBlock('nenhum');
							$tpl->assign('estado', mb_strtoupper($CFG->vars->ufs_estado[$uf], 'UTF-8') );
					}
			}

		if($error)
			{
				$tpl->newBlock('error');
			}
		
		echo $tpl->getOutputContent();
		exit;
	}

if($_GET['do'] == 'getMarcas')
	{
    		
		if(!empty($_GET['tipo']))
			{
				$tipo = $_GET['tipo'];

				$Marcas = bd_executa("SELECT m.marca, m.id
                                        FROM veiculos AS v, veic_marcas AS m
                                            WHERE v.id_marca = m.id
                                                AND v.id_tipo = '".$tipo."' 
                                                AND v.status = '1'
                                            GROUP BY m.id
                                        ORDER BY m.marca ASC", $CFG->con);
    
                if($Marcas->nada)
                    {
                        $tag = '<option value="0">Nenhum Marca</option>';
                    }
                else
                    {
                        $tag = '<option value="0">Marcas</option>';

                        foreach($Marcas->res as $e)
                            {
                                $tag .= '<option value="'.$e->id.'">'.utf8_encode_once($e->marca).'</option>';
                            }
                    }
            }
		
		echo $tag;
		exit;
	}

if($_GET['do'] == 'getModelos')
	{

		if(!empty($_GET['marca']))
			{
				$marca = $_GET['marca'];

				$Modelos = bd_executa("SELECT m.modelo, m.id
                                        FROM veiculos AS v, veic_modelos AS m
                                            WHERE v.id_modelo = m.id
                                                AND v.id_marca = '".$marca."' 
                                                AND v.status = '1'
                                            GROUP BY m.id
                                        ORDER BY m.modelo ASC", $CFG->con);
                            
                if($Modelos->nada)
                    {
                        $tag = '<option value="0">Nenhum Modelo</option>';
                    }
                else
                    {
                        $tag = '<option value="0">Modelo</option>';

                        foreach($Modelos->res as $e)
                            {
                                $tag .= '<option value="'.$e->id.'">'.utf8_encode_once($e->modelo).'</option>';
                            }
                    }
            }
		
		echo $tag;
		exit;
	}

if($_GET['do'] == 'getAnos')
	{

		if(!empty($_GET['modelo']))
			{
				$modelo = $_GET['modelo'];

				$Anos = bd_executa("SELECT v.ano_modelo, v.id
                                        FROM veiculos AS v
                                            WHERE v.id_modelo = '".$modelo."' 
                                                AND v.status = '1'
                                        ORDER BY v.ano_modelo ASC", $CFG->con);
                            
                if($Anos->nada)
                    {
                        $tag = '<option value="0">Nenhum Ano</option>';
                    }
                else
                    {
                        $tag = '<option value="0">Anos</option>';

                        foreach($Anos->res as $e)
                            {
                                $tag .= '<option value="'.$e->ano_modelo.'">'.utf8_encode_once($e->ano_modelo).'</option>';
                            }
                    }
            }
		
		echo $tag;
		exit;
	}

if($_GET['do'] == 'getCombustiveis')
	{

		if(!empty($_GET['ano']))
			{
				$ano = $_GET['ano'];

				$Combustiveis = bd_executa("SELECT c.combustivel, c.id
                                        FROM veiculos AS v, veic_combustiveis AS c
                                            WHERE v.id_combustivel = c.id
                                                AND v.ano_modelo = '".$ano."' 
                                                AND v.status = '1'
                                            GROUP BY c.id
                                        ORDER BY c.combustivel ASC", $CFG->con);
                
                if($Combustiveis->nada)
                    {
                        $tag = '<option value="0">Nenhum Combustível</option>';
                    }
                else
                    {
                        $tag = '<option value="0">Combustíveis</option>';

                        foreach($Combustiveis->res as $e)
                            {
                                $tag .= '<option value="'.$e->id.'">'.utf8_encode_once($e->combustivel).'</option>';
                            }
                    }
            }
		
		echo $tag;
		exit;
	}

if($_GET['do'] == 'AtuacaoCidade' and !empty($_GET['cid']))
	{
		$tpl = new TemplatePower($CFG->dir_tpl.'ajax_atuacaocidade.html');
		$tpl->prepare();

		$Cidade = bd_executa("SELECT *
								FROM maps_poi
									WHERE id = '".intval($_GET['cid'])."' ", $CFG->con);		

		if(!$Cidade->nada)
			{
				$Cidade = $Cidade->res->rid0;
				
				$tpl->assign('mid', $Cidade->id );
				$tpl->assign('cidade', $Cidade->nome );
				$tpl->assign('descricao', $Cidade->descricao );
			}
		else
			{
				echo 'ERRO';
			}
		
		echo $tpl->getOutputContent();
		exit;
	}

#\_ Ajax Show Pop Up
if($_GET['do'] == 'denyShowPopup')
    {
        $_SESSION['DEFAULT']['denyShowPopup'] = time();

        echo 'OK';
        exit;
    }

exit;
############################################################################
?>