<?php
##########################################################################*/

#\_ Mapa
function print_abrangencia(&$tpl)
	{
	   $tpl->newBlock('mapa_mostra');

	   $tpl->gotoBlock('_ROOT');
	}
	 
#\_ Calendario
function print_calendario(&$tpl, $year=NULL, $month=NULL, $event_full=false)
	{
		CALENDAR::$class_semana        = 'CalendarioTextoNormal';
		CALENDAR::$class_day_normal    = 'CalendarioTextoNormal';
		CALENDAR::$class_day_fimde     = 'CalendarioTextoRed';
		CALENDAR::$class_day_atual     = 'CalendarioDiaAtual';
		CALENDAR::$class_day_evento    = 'CalendarioDiaEvento';;
		
		if($year == NULL)
			$year = date("Y");
			
		if($month == NULL)
			$month = date("m");
		
		$days = array();
		$events = array();

		$q = bd_executa("SELECT * FROM agenda 
								 WHERE data_agenda >= '".$year."-".$month."-01 00:00:00' 
								   AND data_agenda <= '".$year."-".$month."-31 23:59:59'
							  ORDER BY data_agenda ASC", $GLOBALS['CFG']->con);
		
		if(!$q->nada)
			{
				foreach($q->res as $e)
					{
						$e_day = intval(date('d', strtotime($e->data_agenda)));
						
								if(isset($days[$e_day]))
									$days[$e_day] = array($days[$e_day][0]." &#013;&bull; ".$e->titulo);
								else
									$days[$e_day] = array('&bull; '.$e->titulo);
								
								$events[] = array($e_day, $e->titulo, $e->resumo, $e->data_agenda, $e->autor, $e->id);                                
								
						if($e->observacoes != 'CURSO')
							{
								$tipoevento = "Evento";
							}
						else
							{
								$tipoevento = "Curso";
							}
					}
			}
		
		$p_month = $month-1;
		$p_year = $year;
		$n_month = $month+1;
		$n_year = $year;
		
		if($p_month == 0)
			{
				$p_month = 12;
				$p_year = $p_year-1;
			}
		
		if($n_month == 13)
			{
				$n_month = 1;
				$n_year = $n_year+1;
			}
		
		$tpl->newBlock('calendario');
			$tpl->assign('mes', z1::get_Months($month) );
			$tpl->assign('ano', $year );
			$tpl->assign('calendario', CALENDAR::generate($year, $month, $days) );
			$tpl->assign('link_voltar', '/Ajax/calendario&year='.$p_year.'&month='.$p_month.($event_full?'&event_full=yes':'') );
			$tpl->assign('link_avancar', '/Ajax/calendario&year='.$n_year.'&month='.$n_month.($event_full?'&event_full=yes':'') );
		
		foreach($events as $event_data)
			{
				$tpl->newBlock('calendario_eventos');
					$tpl->assign('dia', $event_data[0] );
					$tpl->assign('titulo', $event_data[1] );
					$tpl->assign('resumo', $event_data[2] );
					$tpl->assign('link', go_area('Evento', $event_data[5].'/'.url_simple($event_data[1])) );
				
				$tpl->gotoBlock('_ROOT');
				
				if($event_full)
					{
						$tpl->newBlock('evento');
							$tpl->assign('tipoevento', $tipoevento );
							$tpl->assign('titulo', $event_data[1] );
							$tpl->assign('dia', $event_data[0] );
							$tpl->assign('local', $event_data[2] );
							//$tpl->assign('horario', date('h:m', strtotime($event_data[3])) );
							$tpl->assign('autor', $event_data[4] );
							$tpl->assign('link', go_area('Evento', $event_data[5].'/'.url_simple($event_data[1])) );
					}   
			}

		$tpl->gotoBlock('_ROOT');
	}

// Formulario da Busca
function print_busca_simples(&$tpl)
    {   
        $Busca = new FORM('busca_simples_form', go_area('Produtos'), $GLOBALS['CFG']->estilo, 'z1alert', '', 'get');
            $Busca->create_tag_text_ini('k', 'o que você procura?', 40, 100, 'o que você procura?', array('texto') );
            $Busca->create_tag_submit('busca_simples_submit', '', 'img/IcoBusca.png');
        $Busca->build($tpl, 'busca_simples');

        $tpl->gotoBlock('_ROOT');
    }

function print_busca_topo(&$tpl)
    {
        $tpl->newBlock('topo_busca');    

        print_busca_simples($tpl);
    }

function print_busca(&$tpl)
    {
        global $CFG, $def_busca_codigo, 
            $bairros, $bairros_f, 
            $tipos, $tipos_f,
            $dormitorios, $dormitorios_f, 
            $garagens, $garagens_f, 
            $valores_venda, $valores_venda_f, $valores_aluguel, $valores_aluguel_f;
        
        // Valores guardados em sessao
        if(!isset($_SESSION['BUSCA']))
            $_SESSION['BUSCA'] = array();
        
        if(count($_SESSION['BUSCA']) > 0)
            {
                foreach($_SESSION['BUSCA'] as $key => $post)
                    {
                        if(isset($_SESSION['BUSCA'][$key]))
                            $_POST[$key] = $post;
                    }
            }

        // prepara dados
        $tipos_negocio = array('Venda' => 'V', 'Aluguel' => 'A');
        
        $tipos = bd_ray('id', 'tipo', 'imoveis_tipos', $CFG->con, " WHERE status = '1' ORDER BY tipo ASC");
        $tipos_f = array_flip($tipos);
        
		$bairros = array();
		$cidades = bd_ray('id', 'cidade', 'imoveis_cidades', $CFG->con, " WHERE status = '1' ORDER BY cidade ASC ");
		
		foreach($cidades as $cid => $cidade)
			{
				$bairros[$cidade] = bd_ray('id', 'bairro', 'imoveis_bairros', $CFG->con, " WHERE status = '1' AND id_cidade = '".$cid."' ORDER BY bairro ASC");
				$bairros_f[$cidade] = array_flip($bairros[$cidade]);
			}
		
        $dormitorios = array('0' => 'Zero', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5 ou mais');
        $dormitorios_f = array_flip($dormitorios);
        
        /*$garagens = array('0' => 'Zero', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5 ou mais');
        $garagens_f = array_flip($garagens);*/
        
        $valores_venda = array('0' => 'Zero',
                                '50000' => '50 mil', 
                                '100000' => '100 mil', 
                                '150000' => '150 mil', 
                                '200000' => '200 mil',
                                '250000' => '250 mil',
                                '300000' => '300 mil', 
                                '350000' => '350 mil', 
                                '400000' => '400 mil', 
                                '450000' => '450 mil', 
                                '500000' => '500 mil', 
                                '550000' => '550 mil', 
                                '600000' => '600 mil', 
                                '650000' => '650 mil', 
                                '700000' => '700 mil', 
                                '750000' => '750 mil', 
                                '800000' => '800 mil', 
                                '1200000' => '1,2 mi', 
                                '1600000' => '1,6 mi', 
                                '2000000' => '2 mi', 
                                '2000001' => '2 mi ou +');
        $valores_venda_f = array_flip($valores_venda);
        
        $valores_aluguel = array('0' => 'Zero',
                                 '500' => '500',
                                 '1000' => '1.000', 
                                 '1500' => '1.500', 
                                 '2000' => '2.000', 
                                 '2500' => '2.500', 
                                 '3000' => '3.000', 
                                 '3500' => '3.500 ou +');
        $valores_aluguel_f = array_flip($valores_aluguel);
        
        $valores_venda_min = reset($valores_venda_f);
        $valores_venda_max = end($valores_venda_f);
        $valores_aluguel_min = reset($valores_aluguel_f);
        $valores_aluguel_max = end($valores_aluguel_f);
        
        $CFG->venda_val_max = $valores_venda_max;
        $CFG->aluguel_val_max = $valores_aluguel_max;
        
		// set tab
		if(empty($_POST['busca_tipo_negocio']))
			{
				$negociacao = $CFG->negociacoes_alt['V'];
			}
		else
			{
				$negociacao = $CFG->negociacoes_alt[$_POST['busca_tipo_negocio']];
			} /**/

        $GLOBALS['tipos_de_imovel'] = &$tipos;

        // Formulario da Busca Completa
        $Busca = new FORM('busca_form', go_area('Busca'), $CFG->estilo, 'z1alert', '', 'get');
        
            $Busca->create_tag_select('busca_tipo', 'Tipo de Imóvel', $tipos_f, '', utf8_decode_once(''), ' data-placeholder="'.__('Tipo de Imóvel').'" ', array('none'), NULL, NULL, true );
			$Busca->create_tag_select('busca_bairro', 'Bairro', $bairros_f, '', utf8_decode_once(''), ' data-placeholder="'.__('Bairro').'" ', array('none'), NULL, NULL, true );

            $Busca->create_tag_select('busca_dormitorios', 'Dormitórios', $dormitorios_f, NULL, '', ' data-placeholder="'.__('Dormitórios').'" ', array('none'), NULL, NULL, true );
            //$Busca->create_tag_select('busca_garagens', 'Garagens', $garagens_f, NULL, '', '', array('none'), NULL, NULL, true );
            $Busca->create_tag_select('busca_valor_venda_min', 'Valor Mínimo para Venda', $valores_venda_f, $valores_venda_min, '', '', array('none'), $CFG->estilo['select'].' min' );
            $Busca->create_tag_select('busca_valor_venda_max', 'Valor Máximo para Venda', $valores_venda_f, $valores_venda_max, '', '', array('none'), $CFG->estilo['select'].' max' );
            $Busca->create_tag_select('busca_valor_aluguel_min', 'Valor Mínimo para Aluguel', $valores_aluguel_f, $valores_aluguel_min, '', '', array('none'), $CFG->estilo['select'].' min' );
            $Busca->create_tag_select('busca_valor_aluguel_max', 'Valor Máximo para Aluguel', $valores_aluguel_f, $valores_aluguel_max, '', '', array('none'), $CFG->estilo['select'].' max' );
        
            $Busca->create_tag_hidden('busca_ordem', '');
            $Busca->create_tag_hidden('busca_negociacao', 'V');
            $Busca->create_tag_submit('busca_submit', 'PROCURAR<br>PROPRIEDADE', '', 'style="margin-top: 20px; height: 123px;"', true);

        $Busca->build($tpl, 'formulario_busca');
        
        $tpl->gotoBlock('_ROOT'); /**/

        $tpl->newBlock('set_busca_negociacao');
            $tpl->assign('set_busca_negociacao', $negociacao );

        $tpl->gotoBlock('_ROOT');
    }

#\_ Formulario da Busca
function print_fundo(&$tpl)
	{
		if(isset($_GET['theme']))
			{
				$_SESSION['DEFAULT']['THEME'] = $GLOBALS['CFG']->themes[$_GET['theme']];

				ke_head( link_voltar() );
			}

		if(!empty($_SESSION['DEFAULT']['THEME']))
			{
				$tpl->newBlock('fundo_'.$_SESSION['DEFAULT']['THEME']);
			}

		$tpl->gotoBlock('_ROOT');
	}

#\_ Menu Goto
function print_menu_goto(&$tpl, $menu='0')
	{
		$tpl->newBlock('menu_goto');
			$tpl->assign('menu', $menu);
	}

function print_facebooksdk(&$tpl)
	{
		global $CFG;
		
		$tpl->newBlock('facebook');
			$tpl->assign('appid', $CFG->fb_appid);
			$tpl->assign('link_login', go_area(300, 'LoginFacebook') );
			$tpl->assign('link_logout', go_area(300, 'Logout') );

		$tpl->gotoBlock('_ROOT');
	}

#\_ Formulario de Login
function print_auth(&$tpl, $texto_submit='Entrar', $estilo_submit=NULL, $estilo_codigo=NULL, $estilo_codigo_error=NULL, $estilo_senha=NULL, $estilo_senha_error=NULL)
	{
		global $CFG;
		
		if(!Auth::Verify())
			{
				$Auth = new FORM('form_auth', go_area('AreaRestrita'), $GLOBALS['CFG']->estilo, 'z1alert');
				
				if(!empty($estilo_submit))
					$Auth->estilo['submit'] = $estilo_submit;
				
				$Auth->create_tag_text_ini('auth_email', 'E-mail', 18, 50, '', array('texto'), ' placeholder="E-mail" ', $estilo_codigo, $estilo_codigo_error);
				$Auth->create_tag_password_ini('auth_senha', 'Senha', 18, 30, '', array('texto'), ' placeholder="Digite sua Senha" ', $estilo_senha, $estilo_senha_error);
				$Auth->create_tag_chbox('auth_remember', 'Lembrar de Mim', 'yes');
				$Auth->create_tag_submit('auth_submit', $texto_submit);
				$Auth->build($tpl, 'auth');
			}
		
		print_auth_nome($tpl);

		$tpl->gotoBlock('_ROOT');
	}
	
function print_auth_nome(&$tpl)
	{
		global $CFG;
		
		if(Auth::Verify())
			{
				$tpl->newBlock('auth_ok');
					$tpl->assign('auth_nome', Auth::GetInfo('nome') );

				//$tpl->newBlock('auth_ok_2');
			}
		else
			{
				/*$tpl->newBlock('auth_off');
				$tpl->newBlock('auth_off_2');*/
			}

		$tpl->gotoBlock('_ROOT');
	}
	
#\_ Imprime submenu de esportes
function print_atividades(&$tpl)
	{
		$q = bd_executa("SELECT m.id, m.titulo, s.secao
						FROM materias AS m, mat_secoes AS s
							WHERE m.id_secao = s.id
								AND s.id = '8'
								AND m.destaque > '0'
							ORDER BY m.titulo ASC", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				$tpl->newBlock('atividades');

				foreach($q->res as $e)
					{
						$tpl->newBlock('atividade');
							$tpl->assign('titulo', htmlentities($e->titulo) );
							$tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );
					}
			}

		unset($q);
		$tpl->gotoBlock('_ROOT');
	}

#\_ Imprime cadastro de Newsletter
function print_newsletter(&$tpl)
	{
		$Newsletter = new FORM('newsletter_form', go_area('Newsletter'), $GLOBALS['CFG']->estilo, 'z1alert');

		$Newsletter->create_tag_text('newsletter_nome', 'NOME', 25, 100, '', array('none'), '  placeholder="NOME" ' );
		$Newsletter->create_tag_text('newsletter_email', 'EMAIL', 20, 100, '', array('email'), '  placeholder="EMAIL" ' );
		$Newsletter->create_tag_submit('newsletter_submit', 'ENVIAR');

		$Newsletter->build($tpl, 'newsletter');

		$tpl->gotoBlock('_ROOT');
	}

#\_ Conteudo do Carrinho
function print_carrinho(&$tpl)
	{
		$tpl->newBlock('barra_carrinho');
		
		$qnt = count($GLOBALS['Commerce']->cart);
		
		if($qnt > 1)
			{
                $tpl->assign('carrinho_itens', '&nbsp;'.$qnt );
				$tpl->assign('link', go_area('Carrinho') );
			}
		elseif($qnt == 1)
			{
                $tpl->assign('carrinho_itens', '&nbsp;'.$qnt );
                $tpl->assign('link', go_area('Carrinho') );
			}
		else
			{
				$tpl->assign('carrinho_itens', '' );
                $tpl->assign('link', go_area('Produtos') );
			}

		$tpl->gotoBlock('_ROOT');
	}

#\_ Menu de Categorias
function printMenuCategorias(&$tpl, $lateral=false)
	{
		global $CFG;
		
		$categorias = bd_executa("SELECT id, categoria FROM categorias WHERE status = '1' ORDER BY categoria ASC", $CFG->con);
		
		if($categorias->nada)
			return false;
		
		if($lateral)
			$arodutos = bd_executa("SELECT id, nome, id_categoria FROM produtos WHERE status = '1' AND show_navegacao = '1' ORDER BY nome ASC", $CFG->con);
		
		foreach($categorias->res as $c)
			{
				$link = go_area('Produtos', $c->id.'/'.url_simple($c->categoria));
				
				$tpl->newBlock('menu_categoria');
					$tpl->assign('categoria', $c->categoria );
					$tpl->assign('link', $link );
				
				$tpl->newBlock('rodape_categoria');
					$tpl->assign('categoria', $c->categoria );
					$tpl->assign('link', $link );
				
				if($lateral)
					{
						$tpl->newBlock('lateral_categoria');
							$tpl->assign('categoria', $c->categoria );
							$tpl->assign('link', $link );
						
						if(!$arodutos->nada)
							{
								$temp = NULL;
								$onetime = false;
								
								foreach($arodutos->res as $a)
									{
										if($a->id_categoria == $c->id)
											{
												if(!isset($temp))
													{
														$temp = $a;
														continue;
													}
												else
													{
														if(!$onetime)
															{
																$tpl->newBlock('lateral_produto');
																	$tpl->assign('produto', $temp->nome );
																	$tpl->assign('link_produto', go_area('Produto', $temp->id.'/'.url_simple($temp->nome)) );
																
																$onetime = true;
															}
														
														$tpl->newBlock('lateral_produto');
															$tpl->assign('produto', $a->nome );
															$tpl->assign('link_produto', go_area('Produto', $a->id.'/'.url_simple($a->nome)) );
													}
											}
									}
							}
					}
			}
		
		$tpl->gotoBlock('_ROOT');
	}

#\_ Enquete
function print_enquete(&$tpl, $limit=0)
	{
		global $CFG;

		if(!empty($limit))
			{
				$limit = ' LIMIT '.$limit;
			}
		
		$q = bd_executa("SELECT * FROM enquetes WHERE ini <= NOW() AND end >= NOW() AND status = '1' ORDER BY id DESC".$limit, $CFG->con);

		if(!$q->nada)
			{
				$q = $q->res->rid0;

				$tpl->newBlock('enquetes');
					$tpl->assign('titulo', $q->titulo );
					$tpl->assign('descricao', nl2br($q->descricao) );

				$Verify = bd_executa("SELECT * FROM enquete_votos
												WHERE ip = '".$_SERVER['REMOTE_ADDR']."'
													AND id_enquete = '".$q->id."'", $CFG->con);

				$imagens = bd_executa("SELECT i.img
											FROM bd_img AS i, rel AS r
												WHERE r.um = 'enquetes'
													AND r.dois = 'galerias'
													AND r.id_um = '".$q->id."'
													AND r.id_dois = i.id_mama
													AND i.size = '".$CFG->thumbsize."'
												ORDER BY i.id DESC", $CFG->con);

				if($Verify->nada)
					{
						$Enquete = new FORM('form_enquete', go_area('EnviaEnquete'), $CFG->estilo, 'z1alert');

							$Enquete->create_tag_hidden('eid', $q->id );
							$Enquete->create_tag_submit('submit', 'Votar');
						$Enquete->build($tpl, 'enquete');

						for($i=1;$i<=6;$i++)
							{
								if(!empty($q->{'opcao'.(string)$i}))
									{
										$tpl->newBlock('option');
											$tpl->assign('valor', $i);
											$tpl->assign('option', $q->{'opcao'.(string)$i});

										if( !empty($imagens->res->{'rid'.(string)($i-1)}->img) )
											{
												$tpl->assign('img', $CFG->url_img.$imagens->res->{'rid'.(string)($i-1)}->img );
											}
									}

								$tpl->gotoBlock('_ROOT');
							}
					}
				else
					{
						$tpl->newBlock('enquete_votou');
					}

				$tpl->newBlock('enq_resultados');

				for($i=1;$i<=6;$i++)
					{
						if(!empty($q->{'opcao'.(string)$i}))
							{
								$tpl->newBlock('resultado');
									$tpl->assign('valor', $i);
									$tpl->assign('option', $q->{'opcao'.(string)$i});
									$tpl->assign('pc', round(($q->{'votos'.(string)$i}/$q->total)*100) );
									$tpl->assign('porc', number_format(($q->{'votos'.(string)$i}/$q->total)*100, 1, ',', '.') );

								if( !empty($imagens->res->{'rid'.(string)$i-1}) )
									{
										$tpl->assign('img', $CFG->url_img.$imagens->res->{'rid'.(string)$i-1} );
									}
							}

						$tpl->gotoBlock('_ROOT');
					}
			}

		$tpl->gotoBlock('_ROOT');
	}

// Lateral Produtos
function print_lateral_produtos(&$tpl)
	{
		global $CFG;

		$q = bd_executa("SELECT id, nome, valor
							FROM produtos
								WHERE destaque > '1'
									AND status = '1'
									AND valor > '0'", $CFG->con);

		if(!$q->nada)
			{
				$tpl->newBlock($CFG->produtos_blocks[0]);

				foreach($q->res as $e)
					{
						$img = bd_executa("SELECT i.img 
												FROM bd_img AS i, rel AS r
													WHERE r.um = 'produtos'
														AND r.dois = 'galerias'
														AND r.id_um = '".$e->id."'
														AND r.id_dois = i.id_mama
														AND i.size = '".$CFG->thumbsize."'
													ORDER BY i.id DESC LIMIT 1", $CFG->con);

						if($img->nada)
							continue;

						$tpl->newBlock($CFG->produtos_blocks[1]);
							$tpl->assign('id', $e->id );
							$tpl->assign('nome', $e->nome );
							$tpl->assign('valor', $e->valor );
							$tpl->assign('img', $CFG->url_img.$img->res->rid0->img );
							$tpl->assign('link', go_area('Produto', $e->id.'/'.url_simple($e->nome) ) );
							$tpl->assign('link_comprar', go_area('Comprar', $e->id.'/1' ) );
					}
			}

		$tpl->gotoBlock('_ROOT');
	} /**/

// Lateral Materias
function print_editais(&$tpl)
	{
		global $CFG;

		$q = bd_executa("SELECT id, titulo
							FROM materias
								WHERE id_secao = '22'
									AND destaque > '1'
								ORDER BY destaque DESC, id DESC LIMIT 5", $CFG->con);

		if(!$q->nada)
			{
				foreach($q->res as $e)
					{
						$tpl->newBlock('editais_esquerda');
							$tpl->assign('titulo', $e->titulo );
							$tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo) ) );
					}
			}
		else
			{
				$tpl->newBlock('editais_esquerda');
					$tpl->assign('titulo', 'Não há Editais Disponíveis' );
					$tpl->assign('link', 'javascript:;' );
			}

		$tpl->gotoBlock('_ROOT');
	} /**/

// Categorias Blog
function print_blogCategorias(&$tpl)
	{
		$categorias = bd_executa("SELECT id,secao FROM mat_secoes WHERE id_parent = '13' ORDER BY secao ASC ", $GLOBALS['CFG']->con);

		if(!$categorias->nada)
			{
				foreach($categorias->res as $e)
					{
						$tpl->newBlock('blog_categoria');
							$tpl->assign('categoria', $e->secao );
							$tpl->assign('link',  go_area('BloGzOne', $e->id.'/'.url_simple($e->secao)) );
							$tpl->gotoBlock('_ROOT');
					}
			}
	}

// Categorias Topo
function print_contador(&$tpl)
	{
		$q = bd_executa("SELECT COUNT(*) AS contador FROM doacoes", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				$e = $q->res->rid0;

				$tpl->newBlock('contador');
					$tpl->assign('kg', number_format($e->contador, 0, ',', '.') );

				$tpl->gotoBlock('_ROOT');
			}
	}

// Contato
function print_contato(&$tpl)
    {
        global $CFG;

        // formularios
        $fm_width = 100;
        
        $Contato = new FORM('form_contato', go_area('Contato'), $CFG->estilo, 'z1alert');

            $Contato->create_tag_text('contato_nome', 'Nome', $fm_width, 100, '', array('texto'), ' placeholder="Preencha seu Nome" ');
            $Contato->create_tag_text('contato_email', 'E-mail', $fm_width, 100, '', array('email'), ' placeholder="Informe seu E-mail" ');
            $Contato->create_tag_text('contato_telefone', 'Telefone', $fm_width, 100, '', array('none'), ' placeholder="Telefone para contato" ');
            //$Contato->create_tag_text('contato_cidade', 'Cidade', $fm_width, 100, '', array('texto'), ' placeholder="Cidade" ');
            $Contato->create_tag_textarea('contato_mensagem', 'Mensagem', $fm_width, 6, '', array('texto'), ' placeholder="Mensagem, Sugestão ou Dúvidas" ');
            $Contato->create_tag_submit('form_contato_submit', 'enviar');
            
        $Contato->build($tpl, 'contato');/**/
        
        $tpl->gotoBlock('_ROOT');
    }

function print_contato_rodape(&$tpl)
	{
        global $CFG;

        // formularios
        $fm_width = 100;
        
        $Contato = new FORM('contato_rodape_form', go_area('Contato'), $CFG->estilo, 'z1alert');

	        $Contato->create_tag_text('contato_rodape_nome', 'Nome', $fm_width, 100, '', array('texto'), ' placeholder="NOME" ');
	        $Contato->create_tag_text('contato_rodape_email', 'E-mail', $fm_width, 100, '', array('email'), ' placeholder="EMAIL" ');
	        $Contato->create_tag_textarea('contato_rodape_mensagem', 'Mensagem', $fm_width, 3, '', array('texto'), ' placeholder="MENSAGEM" ');
	        $Contato->create_tag_submit('contato_rodape_submit', 'ENTRAR EM CONTATO', '', ' href="#Contato" ');
	        
        $Contato->build($tpl, 'contato_rodape');/**/
        
        $tpl->gotoBlock('_ROOT');
	}

// Pre matricula
function print_prematricula(&$tpl)
	{
        global $CFG;

		$Cursos = bd_ray('id', 'nome', 'cursos', $CFG->con, " ORDER BY ordem ASC ");

        // formularios
        $fm_width = 107;
        $fm_width_2 = 45;
        
        $PreMatricula = new FORM('form_prematricula', go_area('PreMatricula'), $CFG->estilo, 'z1alert');

	        $PreMatricula->create_tag_select('prematricula_curso', 'Curso pretendido', array_flip($Cursos), '', __('Curso pretendido'), ' placeholder="Curso pretendido" ');
	        $PreMatricula->create_tag_text('prematricula_email', 'Email', $fm_width_2, 100, '', array('email'), ' placeholder="Email" ');
	        $PreMatricula->create_tag_text('prematricula_nome', 'Nome', $fm_width_2, 100, '', array('texto'), ' placeholder="Nome" ');
	        $PreMatricula->create_tag_text('prematricula_rg', 'RG', $fm_width_2, 100, '', array('texto'), ' placeholder="RG" ');
	        $PreMatricula->create_tag_text('prematricula_cpf', 'CPF', $fm_width_2, 100, '', array('texto'), ' placeholder="CPF" data-mask="000.000.000-00" ');
	        $PreMatricula->create_tag_text('prematricula_nascimento', 'Data de Nascimento', $fm_width_2, 100, '', array('texto'), ' placeholder="Data de Nascimento" data-mask="00/00/0000" ');
	        
	        $PreMatricula->create_tag_text('prematricula_cep', 'CEP', $fm_width_2, 100, '', array('texto'), ' placeholder="CEP" data-mask="00000-000"  onchange="CEPChange();" ');
	        $PreMatricula->create_tag_text('prematricula_endereco', 'Endereço completo', $fm_width_2, 100, '', array('texto'), ' placeholder="Endereço completo" ');
	        $PreMatricula->create_tag_text('prematricula_cidade', 'Cidade', $fm_width_2, 100, '', array('texto'), ' placeholder="Cidade" ');
			$PreMatricula->create_tag_select('prematricula_estado', 'Uf', $CFG->vars->ufs_estado_f, '', __('Estado'), ' placeholder="Estado" ');
	        $PreMatricula->create_tag_text('prematricula_bairro', 'Bairro', $fm_width_2, 100, '', array('texto'), ' placeholder="Bairro" ');
	        
	        $PreMatricula->create_tag_text('prematricula_celular', 'Telefone', $fm_width_2, 100, '', array('texto'), ' placeholder="Telefone" data-mask="(00) 0000-00009" ');
	        $PreMatricula->create_tag_text('prematricula_pai', 'Nome do pai', $fm_width_2, 100, '', array('texto'), ' placeholder="Nome do pai" ');
	        $PreMatricula->create_tag_text('prematricula_mae', 'Nome da mãe', $fm_width_2, 100, '', array('texto'), ' placeholder="Nome da mãe" ');

	        $PreMatricula->create_tag_submit('form_prematricula_submit', 'Enviar');
	        
        $PreMatricula->build($tpl, 'prematricula');/**/
	}

// Doacao Sucesso
function print_inicial_sucesso(&$tpl, $obj, $block, $timeout=false)
	{
		global $CFG;

		$empresa = bd_executa("SELECT e.nome, e.link, i.img 
								FROM doac_empresas AS e, bd_img AS i, rel AS r
									WHERE e.id = '".$obj->id_empresa."'
										AND r.um = 'doac_empresas'
										AND r.dois = 'galerias'
										AND r.id_um = e.id
										AND r.id_dois = i.id_mama
										AND i.size = '".$CFG->thumbsize."'
									ORDER BY i.id DESC LIMIT 1", $CFG->con);

		if(!$empresa->nada)
			{
				$empresa = $empresa->res->rid0;
				
				$cidade = bd_executa("SELECT nome FROM doac_cidades WHERE id = '".$obj->id_cidade."'", $CFG->con);

				if(!$cidade->nada)
					{
						$cidade = $cidade->res->rid0;
						
						$tpl_ajax = new TemplatePower($CFG->dir_tpl.'ajax_sucesso.html');
						$tpl_ajax->prepare();

						assignGlobalLinks($tpl_ajax);

						$tpl_ajax->newBlock($block);
							$tpl_ajax->assign('rand', rand(1,4) );	
							$tpl_ajax->assign('cidade', $cidade->nome);
							$tpl_ajax->assign('nome', $empresa->nome );
							$tpl_ajax->assign('link', $empresa->link );
							$tpl_ajax->assign('img', $CFG->url_img.$empresa->img );

							print_prato_rand($tpl_ajax, substr($block, strpos($block, '_')));

						$html_content = $tpl_ajax->getOutputContent();

						$tpl->newBlock('inicial_sucesso');
							$tpl->assign('html_content', $html_content );
						
						if(!empty($timeout))
							$tpl->assign('timeout', ($timeout - (time() - strtotime($obj->creation_date))) );

						$tpl->gotoBlock('_ROOT');
					}
			}
	}
	
function print_prato_rand(&$tpl, $block_adt='')
	{
		global $CFG;
			
		$tpl->newBlock('cliquedoe'.$block_adt);
			$tpl->assign('rand', array_rand($CFG->ids_pratos) );
		
		$tpl->gotoBlock('_ROOT');
	}
	
function print_ranking(&$tpl)
	{
		global $CFG;
			
		// usuarios
		$q = bd_executa("SELECT nome, facebook, hits, tipos
							FROM clientes
								WHERE status = '1'
									AND ( NOT ".csv_filter_query('tipos', '1')." )
									AND ( NOT ".csv_filter_query('tipos', '2')." )
									OR tipos IS NULL
								ORDER BY hits DESC, id DESC LIMIT 5", $CFG->con);  

		if(!$q->nada)
			{
				$count = 1;
				$tpl->newBlock('Ranking');

				foreach($q->res as $e)
					{
						if(empty($e->facebook))
							{
								$img = $CFG->url_tpl.'img/Avatar.png';
							}										
						else
							{
								$img = FacebookI::avatarOf($e->facebook);												
							}

						$tpl->newBlock('cliente');
							//$tpl->assign('pos', $T+($count++).'º' ); 
							$tpl->assign('nome', $e->nome ); 
							$tpl->assign('link', $img ); 
							$tpl->assign('cliques', $e->hits ); 
					}

				$tpl->gotoBlock('_ROOT');

				//print_paginator($q, $tpl, $T, $aP, go_area($area).'/', 'paginador', 6);

			}
		
		$tpl->gotoBlock('_ROOT');
	}

function print_meu_ranking(&$tpl, $block)
	{
		$UserInfo =  Auth::GetInfo();

		// Ranking
		$q = bd_executa("SELECT COUNT(id) AS ranking
							FROM clientes
								WHERE status = '1'
									AND ".csv_filter_query('tipos', '3')."
									AND ( hits > '".$UserInfo->hits."'
										OR ( hits = '".$UserInfo->hits."' AND id > '".$UserInfo->id."' ) 
									)", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				$e = $q->res->rid0;

				$tpl->newBlock($block);
					$tpl->assign('ranking', (intval($e->ranking) > 0 ? $e->ranking + 1 : '1') );

				$tpl->gotoBlock('_ROOT');
			}
	}
	
function print_indices(&$tpl)
	{
		// anuncios
		$q = bd_executa("SELECT
							( SELECT COUNT( c.id ) AS total
								FROM clientes AS c
									WHERE c.status = '1') AS empresas, 
							( SELECT COUNT( id ) AS total
								FROM classificados
									WHERE status > '0'
							) AS classificados", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				$e = $q->res->rid0;

				$tpl->newBlock('indices');
					$tpl->assign('empresas', $e->empresas );
					$tpl->assign('anuncios', $e->classificados );

				$tpl->gotoBlock('_ROOT');
			}
	}   
	 
function print_categorias(&$tpl)
	{
		global $CFG;
		
		$parent = bd_executa("SELECT id, categoria 
								FROM categorias 
									WHERE id_parent = '0' 
										AND status = '1' 
									ORDER BY categoria ASC", $CFG->con);
		
		if($parent->nada)
			return false;
		
		foreach($parent->res as $p)
			{
				$tpl->newBlock('categoria_pai');
					$tpl->assign('categoria', $p->categoria );
					
				$child = bd_executa("SELECT id, categoria FROM categorias 
										WHERE id_parent = '".$p->id."' 
											AND status = '1' 
										ORDER BY categoria ASC", $CFG->con);
				
				if($child->nada)
					continue;
		
				foreach($child->res as $c)
					{
						$tpl->newBlock('categoria_filho');
							$tpl->assign('categoria', $c->categoria );
							$tpl->assign('link', go_area('Produtos', $c->id.'/'.url_simple($c->categoria)) );
					}
			}
		
		$tpl->gotoBlock('_ROOT');
	}
	
//ofertas   
function print_ofertas(&$tpl)
	{
		// anuncios
		$q = bd_executa("SELECT nome FROM classificados WHERE status = '1' ORDER BY creation_date LIMIT 10", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				foreach($q->res as $e)
					{
						$tpl->newBlock('oferta');
							$tpl->assign('nome', $e->nome );
					}
					
				$tpl->gotoBlock('_ROOT');
			}
	}

//caminho path
function print_path(&$tpl, $max=500)
	{
		$total_ch = 0;

		foreach($GLOBALS['CFG']->breadcrumb as $k => $e)
			{
				$k = mb_strtoupper($k);
				
				if($total_ch + strlen($k) >= $max)
					{
						$k = substr($k, 0, $max - $total_ch).'...';
					}
					
				if(!empty($e))
					{
						$k = '<a href="'.$e.'">'.$k.'</a>';
					}
				
				$tpl->newBlock('path');
					$tpl->assign('secao', $k );

				$total_ch += strlen($k);
			}

		$tpl->gotoBlock('_ROOT');
	}

// print esquerda lista de produtos
function print_lista(&$tpl)
	{
		global $CFG;

		$categorias = bd_executa("SELECT id, secao
									FROM mat_secoes
										WHERE id_parent = '6'
										ORDER BY secao ASC", $CFG->con);

		if(!$categorias->nada)
			{
				foreach($categorias->res as $c)
					{					
						$categorias_a[$c->id] = $c->secao;
						
					}
				
				foreach($categorias_a as $k => $v)
					{				
						//verifica se categoria tem produto cadastrado
						$aroduto = bd_executa("SELECT m.id, m.titulo 
												FROM materias AS m, mat_secoes AS s
													WHERE m.id_secao = s.id
														AND s.id = '".$k."' ", $CFG->con);

						if(!$aroduto->nada)
							{
								//print_r($aroduto);exit;
								$tpl->newBlock('lista_categoria');
									$tpl->assign('categoria', $v );
										$tpl->assign('link', go_area('Produtos', $k.'/'.url_simple($v)) );		
														
								foreach($aroduto->res as $a)
									{
										$tpl->newBlock('lista_produto');
											$tpl->assign('nome', $a->titulo );
												$tpl->assign('link', go_area('Produto', $a->id.'/'.url_simple($a->titulo)) );
									}
							}

					}

					$tpl->gotoBlock('_ROOT');		
			}
		else
			{
				$tpl->newBlock('none');
			}
	} 

function print_filtro_vinhos(&$tpl)
	{
		global $CFG;

		$categorias = bd_executa("SELECT id, id_parent, categoria
									FROM categorias
										WHERE id_parent = '2'
											OR id_parent = '3'
											OR id_parent = '4'
											OR id_parent = '5'
										ORDER BY id ASC", $CFG->con);

		if(!$categorias->nada)
			{
				foreach($categorias->res as $c)
					{
						switch($c->id_parent)
							{
								case 2:
									$tpl->newBlock('opcao_filtro_pais');
									break;
								case 3:
									$tpl->newBlock('opcao_filtro_tipo');
									break;
								case 4:
									$tpl->newBlock('opcao_filtro_uva');
									break;
								case 5:
									$tpl->newBlock('opcao_filtro_harmonizacao');
									break;
							}
				
						$tpl->assign('categoria', $c->categoria );
						$tpl->assign('link', go_area('Produtos', 'categorias[]='.$c->id.'|'.url_simple($c->categoria)) );
					}

				$tpl->gotoBlock('_ROOT');		
			}
	}

function print_filtro_veiculos(&$tpl)
    {
        global $CFG;        
    
        $modelos = bd_executa("SELECT m.id, m.modelo
                                FROM veic_modelos AS m, veiculos AS v
                            WHERE v.id_modelo = m.id
                                GROUP BY modelo", $CFG->con);

        if(!$modelos->nada)
            {
                $a_modelos = array();
                $a_modelos[0] = 'Modelo';
                foreach($modelos->res as $e)
                    {   
                        $a_modelos[$e->id] = $e->modelo;
                    }
            
                $a_modelos = array_flip($a_modelos);
                $tpl->gotoBlock('_ROOT');
            }
        
        $marcas = bd_executa("SELECT m.id, m.marca
                                FROM veic_marcas AS m, veiculos AS v
                            WHERE v.id_marca = m.id
                                GROUP BY marca
                                ORDER BY marca ASC", $CFG->con);
        
        if(!$marcas->nada)
            {
                $a_marcas = array();
                $a_marcas[0] = 'Marca';
                foreach($marcas->res as $e)
                    {   
                        $a_marcas[$e->id] = $e->marca;
                    }
            
                $a_marcas = array_flip($a_marcas);
                       
                $tpl->gotoBlock('_ROOT');
            }
                
        //ANO
        $anos = bd_executa("SELECT id, ano_modelo
                                    FROM veiculos
                                        ORDER BY ano_modelo ASC LIMIT 1", $CFG->con);
        
        if(!$anos->nada)
            {
                $i = $anos->res->rid0->ano_modelo; //pega ano mais antigo
                
                $a_anos = array();
                $a_anos[0] = 'Ano';
                for($i; $i <= date('Y'); $i++ )
                    {                       
                        $a_anos[] = $i;
                    }
            
                $a_anos = array_flip($a_anos);
                $tpl->gotoBlock('_ROOT');
            }
        
        //TIPO
        $tipos = bd_executa("SELECT id, tipo
                                    FROM veic_tipos
                                        ORDER BY tipo ASC", $CFG->con);
        
        if(!$tipos->nada)
            {
                $a_tipos = array();
                $a_tipos[0] = 'Tipo';
                foreach($tipos->res as $e)
                    {   
                        $a_tipos[$e->id] = $e->tipo;
                    }
                
                $a_tipos = array_flip($a_tipos);
                $tpl->gotoBlock('_ROOT');
            }
    
        //COMBUSTIVEL
        $combustivel = bd_executa("SELECT id, combustivel
                                    FROM veic_combustiveis
                                        ORDER BY combustivel ASC", $CFG->con);
        
        if(!$combustivel->nada)
            {
                $a_combustivel = array();
                $a_combustivel[0] = 'Combust&iacute;vel';
                foreach($combustivel->res as $k => $e)
                    {   
                        $a_combustivel[$e->id] = $e->combustivel;
                    }
            
                $a_combustivel = array_flip($a_combustivel);
                $tpl->gotoBlock('_ROOT');
            }
        
        // Formulario da Busca Completa
        $Busca = new FORM('busca_form', go_area('Veiculos'), $CFG->estilo, 'z1alert', '', 'get');
                        
            $Busca->create_tag_select('tipo', 'Tipo', $a_tipos, '', NULL, ' onchange="getMarcasFromTipo();"', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('marca', 'Marca', $a_marcas, '', NULL, ' onchange="getModelosFromMarca();"', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('modelo', 'Modelo', $a_modelos, '', NULL, ' onchange="getAnosFromModelo();"', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('valor', 'Preço', $CFG->valores_filtro_f, '', NULL, '', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('ano', 'Ano', $a_anos, '', NULL, ' onchange="getCombustiveisFromAno();"', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('combustivel', 'Combustível', $a_combustivel, '', NULL, '', array('none'), NULL, NULL, '');
            $Busca->create_tag_select('ordenar', 'Ordenar', $CFG->busca_ordem_f, '', NULL, '', array('none'), NULL, NULL, '');
            $Busca->create_tag_submit('submit_pesquisar', 'PESQUISAR');
            
        $Busca->build($tpl, 'formulario_busca');
        
        $tpl->gotoBlock('_ROOT');
    }

function print_filtro_veiculos_simples(&$tpl)
	{
		global $CFG;        
	
		// Formulario da Busca Completa
		$Busca = new FORM('form_filtro_veiculos_simples', go_area('Veiculos'), $CFG->estilo, 'z1alert', '', 'get');

			$Busca->create_tag_select('tipo', 'VEICULO', $CFG->tipos_f, NULL, 'VEICULO', ' onchange="getMarcasFromTipo();"', array('none'), NULL, NULL, '');
			$Busca->create_tag_select('marca', 'MARCA', $CFG->marcas_f, NULL, 'MARCA', '', array('none'), NULL, NULL, '');
			$Busca->create_tag_select('ordenar', 'ORDENAR POR', $CFG->busca_ordem_f, NULL, 'ORDENAR POR', '', array('none'), NULL, NULL, '');
			$Busca->create_tag_submit('submit_filtro_veiculos_simples', 'PESQUISAR');
			
		$Busca->build($tpl, 'filtro_veiculos_simples');
		
		$tpl->gotoBlock('_ROOT');
	}

function print_subchamadas(&$tpl, $blocos)
	{
		$blocos = explode(',', $blocos);
		
		foreach($blocos as $b)
			{
				$tpl->newBlock('subchamada_'.$b);
			}
		
		$tpl->gotoBlock('_ROOT');
	}

// $parents = array( id_parent => block,names )
function print_secoes(&$tpl, $parents)
	{
		global $CFG;

		$secoes_parent = array_keys($parents);
		
		$secoes = bd_executa("SELECT id, id_parent, secao
									FROM mat_secoes
										WHERE id_parent = '".implode("' OR id_parent = '", $secoes_parent)."'
											AND status = '1'
										ORDER BY secao ASC", $CFG->con);

		if(!$secoes->nada)
			{
				foreach($secoes->res as $s)
					{
						$blocos = explode(',', $parents[$s->id_parent]);
				
						if($s->id == '20')
							{
								$link = go_area('ElencoProfissional');
							}
						else
							{
								$link = go_area('Secao', $s->id.'/'.url_simple($s->secao));
							}
						
						foreach($blocos as $bloco)
							{
								$tpl->newBlock($bloco);
									$tpl->assign('secao', $s->secao );
									$tpl->assign('link', $link );
							}
					}
			}
		
		$tpl->gotoBlock('_ROOT');
	}

function print_programacao_noar(&$tpl, $blocos=array())
	{
		global $CFG;

		/*$Programacao = bd_executa("SELECT p.id, p.nome, p.descricao, p.hora_inicio, p.hora_fim
										FROM programacao AS p, prog_semanas AS s
											WHERE p.id_semana = s.id
												AND s.semana = '".date('W')."'
												AND s.ano = '".date('Y')."'
												AND p.dia = '".(date('N')-1)."'
												AND p.hora_inicio <= '".(date('H:i:s'))."'
												AND p.hora_fim > '".(date('H:i:s'))."'
											LIMIT 1", $CFG->con);*/
		
		$Programacao = bd_executa("SELECT p.id, p.nome, p.descricao, p.hora_inicio, p.hora_fim
										FROM programacao AS p, prog_semanas AS s
											WHERE p.id_semana = s.id
												AND p.dia = '".(date('N')-1)."'
												AND p.hora_inicio <= '".(date('H:i:s'))."'
												AND p.hora_fim > '".(date('H:i:s'))."'
											LIMIT 1", $CFG->con);
		
		if(!$Programacao->nada)
			{
				foreach($Programacao->res as $p)
					{
						foreach($blocos as $bloco)
							{
								$tpl->newBlock($bloco);
									$tpl->assign('programa', $p->nome );
							}
					}
			}
		
		$tpl->gotoBlock('_ROOT');
	}

function print_programacao_dia(&$tpl)
	{
		global $CFG;

		$now = strtotime('now');
		
		/*$Programacao = bd_executa("SELECT p.id, p.nome, p.descricao, p.hora_inicio, p.hora_fim
										FROM programacao AS p, prog_semanas AS s
											WHERE p.id_semana = s.id
												AND s.semana = '".date('W')."'
												AND s.ano = '".date('Y')."'
												AND p.dia = '".(date('N')-1)."'
											ORDER BY p.hora_inicio ASC", $CFG->con);*/
		
		$Programacao = bd_executa("SELECT p.id, p.nome, p.descricao, p.hora_inicio, p.hora_fim
										FROM programacao AS p, prog_semanas AS s
											WHERE p.id_semana = s.id
												AND p.dia = '".(date('N')-1)."'
											ORDER BY p.hora_inicio ASC", $CFG->con);
		
		if(!$Programacao->nada)
			{
				$tpl->newBlock('programacao_dia');
					$tpl->assign('dia', mb_strtoupper($CFG->dias_semana[date('w')]) );

				foreach($Programacao->res as $p)
					{
						$tpl->newBlock('programacao_dia_item');
							$tpl->assign('programa', $p->nome );
							$tpl->assign('hora_inicio', date('H:i', strtotime($p->hora_inicio)) );
						
						if(strtotime($p->hora_inicio) <= $now and strtotime($p->hora_fim) > $now )
							{
								$tpl->newBlock('programacao_dia_item_ouca');
							}
					}
			}
		
		$tpl->gotoBlock('_ROOT');
	}

function print_default(&$tpl)
	{
		print_busca($tpl);
		print_lateral($tpl);
		print_submenu($tpl);
		print_dia($tpl);
	}

function print_lateral(&$tpl, $sid=0)
    {
        global $CFG;
                                                  
        $Noticias = bd_executa("SELECT m.id, m.titulo, m.resumo, bd_img.img
                                FROM materias AS m, bd_img
                                    WHERE m.status = '1'
                                        AND m.id_secao = '2'
                                        AND m.destaque > '0'
                                        AND bd_img.id = ( SELECT img.id FROM bd_img AS img, rel AS r
                                                            WHERE r.um = 'materias'
                                                                AND r.dois = 'galerias'
                                                                AND r.id_um = m.id
                                                                AND r.id_dois = img.id_mama
                                                                AND img.size = '".$CFG->midsize."'
                                                            ORDER BY img.id ASC LIMIT 1 )
                                ORDER BY m.id DESC LIMIT 1", $CFG->con);    
                                
        if(!$Noticias->nada)
            {
                foreach($Noticias->res as $e)
                    {       
                        $tpl->newBlock('album');
                            $tpl->assign('mid', $e->id );
                            $tpl->assign('titulo', $e->titulo );
                            $tpl->assign('img', $CFG->url_img.$e->img );
                            $tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );     
                    }
            }

        $tpl->gotoBlock('_ROOT');
        
        if(!empty($sid))
            {
                $SubMenu = bd_executa("SELECT id, secao
                                        FROM mat_secoes
                                            WHERE status = '1'
                                                AND id_parent = '".$sid."'        
                                        ORDER BY secao ASC", $CFG->con); 
                                        
                if(!$SubMenu->nada)
                    {                  
                        foreach($SubMenu->res as $s)
                            {       
                                $tpl->newBlock('secao');
                                    $tpl->assign('sid', $s->id );
                                    $tpl->assign('secao', mb_strtoupper($s->secao) );          
                                    $tpl->assign('link', go_area('Secao', $s->id.'/'.url_simple($s->secao)) );     
                            }
                    }
            }
              

        $tpl->gotoBlock('_ROOT');
                                                          
    }

function print_lateral_pagina(&$tpl, $sid=0)
	{
		global $CFG;
		                                          
        $Noticias = bd_executa("SELECT m.id, m.titulo, m.resumo, bd_img.img
                                FROM materias AS m, bd_img
                                    WHERE m.status = '1'
                                        AND m.id_secao = '2'
                                        AND m.destaque > '0'
                                        AND bd_img.id = ( SELECT img.id FROM bd_img AS img, rel AS r
                                                            WHERE r.um = 'materias'
                                                                AND r.dois = 'galerias'
                                                                AND r.id_um = m.id
                                                                AND r.id_dois = img.id_mama
                                                                AND img.size = '".$CFG->midsize."'
                                                            ORDER BY img.id ASC LIMIT 1 )
                                ORDER BY m.id DESC LIMIT 1", $CFG->con);    
                                
        if(!$Noticias->nada)
            {
                foreach($Noticias->res as $e)
                    {       
                        $tpl->newBlock('album');
                            $tpl->assign('mid', $e->id );
                            $tpl->assign('titulo', $e->titulo );
                            $tpl->assign('img', $CFG->url_img.$e->img );
                            $tpl->assign('link', go_area('Pagina', $e->id.'/'.url_simple($e->titulo)) );     
                    }
            }

        $tpl->gotoBlock('_ROOT');
        
        if(!empty($sid))
            {
                $SubMenu = bd_executa("SELECT id, titulo
                                        FROM materias
                                            WHERE status = '1'
                                                AND id_secao = '".$sid."'        
                                        ORDER BY id ASC LIMIT 8", $CFG->con); 
                                        
                if(!$SubMenu->nada)
                    {                  
                        foreach($SubMenu->res as $s)
                            {       
                                $tpl->newBlock('secao');
                                    $tpl->assign('sid', $s->id );
                                    $tpl->assign('secao', mb_strtoupper($s->titulo) );          
                                    $tpl->assign('link', go_area('Pagina', $s->id.'/'.url_simple($s->titulo)) );     
                            }
                    }
            }
              

        $tpl->gotoBlock('_ROOT');
                                                          
	}
	
function print_submenu(&$tpl)
	{
		global $CFG;
		
		$SubMenu = bd_executa("SELECT id, secao
									FROM mat_secoes 
										WHERE status = 1
											AND id <> '17'
									ORDER BY ordem ASC, secao ASC", $CFG->con);
		
		if(!$SubMenu->nada)
			{
				$count = 0;
				
				foreach($SubMenu->res as $e)
					{
						$link = go_area('Secao', $e->id.'/'.url_simple($e->secao));
						
						if($e->id == 12)
							{
								$link = go_area('Contato');
							}
						
						$tpl->newBlock('submenu');
							$tpl->assign('secao', $e->secao );
							$tpl->assign('link', $link );

						if(($count++)%6 == 0)
							{
								$tpl->newBlock('rodape_coluna');
							}

						$tpl->newBlock('submenu_rodape');
							$tpl->assign('secao', $e->secao );
							$tpl->assign('link', $link );
					}
			}
	}

function print_banners_descricao(&$tpl)
	{
		global $CFG;
		
		$Banners = bd_executa("SELECT descricao
									FROM banners 
										WHERE situacao = '1'
											AND (secoes = '2' 
												OR secoes LIKE '2,%' 
												OR secoes LIKE '%,2' 
												OR secoes LIKE '%,2,%')
									ORDER BY destaque DESC", $CFG->con);

		if(!$Banners->nada)
			{
				foreach($Banners->res as $e)
					{
						$tpl->newBlock('topo_banners_descricao');
							$tpl->assign('descricao', $e->descricao );
					}
			}
	}

function print_dia(&$tpl)
    {
        global $CFG;

        $now = strtotime('now');
        
        $tpl->newBlock('dia_semana');
            $tpl->assign('dia_semana', $CFG->dias_semana[ date('w') ] );        
            $tpl->assign('dia', date('d', $now));        
            $tpl->assign('mes', $CFG->meses[ date('n') ] );        
            $tpl->assign('ano', date('Y', $now));        
        
        $tpl->gotoBlock('_ROOT');
    }  

function print_agenda(&$tpl)
    {
        global $CFG;

        $today = date('Y-m-d');
        
        $Agendas = bd_executa("SELECT * FROM agenda 
								 WHERE data_agenda >= '".$today." 00:00:00' 
							  ORDER BY data_agenda ASC", $GLOBALS['CFG']->con);
		
		if($Agendas->lin < 3)
			{
				$AgendasOld = bd_executa("SELECT * FROM agenda 
								 WHERE data_agenda < '".$today." 00:00:00' 
							  ORDER BY data_agenda DESC LIMIT ".(3-$Agendas->lin) , $GLOBALS['CFG']->con);
				
				if(!$AgendasOld->nada)
					{
						$Agendas->nada = 0;
						$Agendas->lin = $Agendas->lin + $AgendasOld->lin;
						
						$i = 3;
						
						$AgendasOld = (array) $AgendasOld->res;
						$AgendasRes = (array) $Agendas->res;

						$Agendas->res = (object) Array();

						while( $rid = array_pop($AgendasOld) )
							{
								$Agendas->res->{'rid'.$i--} = $rid;
							}	
						while(count($AgendasRes) > 0)
							{
								$Agendas->res->{'rid'.$i--} = array_pop($AgendasRes);
							}

						//$CFG->erros .= nl2br(NL.NL.print_r($Agendas, true).NL.NL);
					}
			}

		if(!$Agendas->nada)
			{
				foreach($Agendas->res as $e)
					{
						$e->data_agenda = strtotime($e->data_agenda);
						
				        $tpl->newBlock('item_agenda');    
				            $tpl->assign('dia', date('d', $e->data_agenda) );
				            $tpl->assign('mes', z1::get_Months(date('n', $e->data_agenda)) );
				            $tpl->assign('titulo', $e->titulo );
				            $tpl->assign('link', go_area('Evento', $e->id.'/'.url_simple($e->titulo)) );
					}
			}
        
        $tpl->gotoBlock('_ROOT');
    }  

function print_pontosdeinteresse(&$tpl)
    {
        global $CFG;

        $dots = array();

        $q = bd_executa("SELECT p.id, p.nome, p.latlng
                                FROM maps_poi AS p
                                    WHERE p.status = '1'
                                        AND p.latlng <> ''
                                        AND p.latlng IS NOT NULL", $CFG->con);

        foreach($q->res as $e)
            {
                $dots[] = array($e->latlng, utf8_encode_once($e->nome), utf8_encode_once($e->nome)/*, trim($e->img)*/);
            }

        if(!empty($dots))
            {
                $tpl->newBlock('pontosdeinteresse');
                    $tpl->assign('dots', json_encode($dots) );
            }

        $tpl->gotoBlock('_ROOT');
    }

function print_filtro(&$tpl)
    {
        global $CFG;

        // Formulario da Busca Completa
        $Busca = new FORM('busca_form', go_area('Produtos'), $CFG->estilo, 'z1alert', '', 'get');

            $Busca->create_tag_select_alt('busca_valor', 'Preço', $CFG->valores_filtro_f, '', NULL, '', array('none'), NULL, NULL, 'submit');
            $Busca->create_tag_select_alt('categorias', 'Categorias', $CFG->valores_filtro_f, '', NULL, '', array('none'), NULL, NULL, 'submit');
            
            $Busca->create_tag_hidden('promocao', '');
            $Busca->create_tag_hidden('ordem', '');
            $Busca->create_tag_hidden('PP', '');

        $Busca->build($tpl, 'formulario_busca');
        
        $tpl->gotoBlock('_ROOT');

        // opcoes dinamicas do filtro
        
        if( some_in_array(array_keys($CFG->categorias_raiz), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_raiz), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Tipo de produto', $CFG->categorias_raiz);
            }
        /*
        if( some_in_array(array_keys($CFG->categorias_vinho), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_vinho), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Vinhos por Tipo', $CFG->categorias_tipos);
                
                print_filtro_grupo($tpl, 'Vinhos por País', $CFG->categorias_paises);
                
                print_filtro_grupo($tpl, 'Vinhos por Uva', $CFG->categorias_uvas);
            }
        
        if( some_in_array(array_keys($CFG->categorias_cervejas), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_cervejas), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Cervejas', $CFG->categorias_cervejas);
            }
        
        if( some_in_array(array_keys($CFG->categorias_cestas), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_cestas), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Cestas', $CFG->categorias_cestas);
            }
        
        if( some_in_array(array_keys($CFG->categorias_outrasbebidas), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_outrasbebidas), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Outras bebidas', $CFG->categorias_outrasbebidas);
            }*/
        
        /*if( some_in_array(array_keys($CFG->categorias_acessorios), $CFG->filtro_results_categorias) > 0 or
            some_in_array(array_keys($CFG->categorias_acessorios), $_SESSION['BUSCA']['categorias']) > 0 )
            {
                print_filtro_grupo($tpl, 'Acessórios', $CFG->categorias_acessorios);
            }*/
        
        $tpl->gotoBlock('_ROOT');
    }

function print_filtro_grupo(&$tpl, $grupo, $ray_categorias)
    {
        if(empty($ray_categorias))
            return;
        
        // acessorios
        $tpl->newBlock('filtro_grupo');
            $tpl->assign('grupo', $grupo );
        
        foreach($ray_categorias as $cid => $cat)
            {
                $opcao = $cid.'|'.url_simple($cat['categoria']);
                
                $tpl->newBlock('filtro_opcao');
                    $tpl->assign('opcao', $opcao );
                    $tpl->assign('categoria', $cat['categoria'] );
                
                if(in_array($opcao, $_SESSION['BUSCA']['categorias']))
                    {
                        $tpl->assign('opcao_checked', 'checked="checked"' );
                        $tpl->assign('class_selected', 'SelectAlt_selected' );
                    }
            }
        
        $tpl->gotoBlock('_ROOT');
    }

function print_parcelamento($valor)
    {
        global $CFG;

        $maxparcelas = floor($valor/$CFG->parcela_min);
        
        if($maxparcelas > $CFG->parcelas_max)
            {
                $maxparcelas = $CFG->parcelas_max;
            }
        
        if($maxparcelas <= 1)
            {
                return '';
            }
        
        /*$multiplicador = ($CFG->parcelamento_juros_mes/100) / ( 1 - ( 1 / ( pow( (($CFG->parcelamento_juros_mes/100) + 1), $maxparcelas) ) ) );
        $valorparcela = $valor * $multiplicador;
        
        $CFG->erros .= '$multiplicador : '.$multiplicador.NL;
        $CFG->erros .= '$valorparcela : '.$valorparcela.NL; /**/
        
        /*$parcela = ($valor / $maxparcelas);
        
        $desagio_total = 0.00;
        
        for($i = 1; $i <= $maxparcelas; $i++)
            {
                $desagio = $parcela - ($parcela / pow( floatval( 1 + $CFG->parcelamento_juros_mes), $i ));
                
                $CFG->erros .= '$desagio : '.$desagio.NL;
        
                $desagio_total = $desagio_total + $desagio;
            }
        
        //$juros = $valor * $CFG->parcelamento_juros_mes * $maxparcelas / 100;
        
        $parcelado = $valor + $desagio_total;
        $parcela = $parcelado/$maxparcelas;
        
        $CFG->erros .= '$desagio_total : '.$desagio_total.NL;
        $CFG->erros .= '$parcelado : '.$parcelado.NL;
        $CFG->erros .= '$parcela : '.$parcela.NL; /**/
        
        $parcelado = $valor * $CFG->parcelamento_multiplicadores[$maxparcelas-1];
        $parcela = $parcelado/$maxparcelas;
        
        $ret = 'até '.$maxparcelas.'x de R$ '.number_format($parcela, 2, ',', '.');
        
        return $ret;
    }

// Bootrader
function print_bt(&$tpl)
	{
		global $CFG, $DolarQuotation;
		
		$q = bd_executa("SELECT id, dolar, renda, data
							FROM bt_rentabilidade
								WHERE status = '1'
							ORDER BY data DESC
							LIMIT 1", $GLOBALS['CFG']->con);

		if(!$q->nada)
			{
				$e = $q->res->rid0;
				
				$tpl->assign('bt_dolar', number_format($DolarQuotation + $CFG->dolar_adt, 2) );
				$tpl->assign('bt_renda', $e->renda );
				$tpl->assign('bt_data', date('d/m/Y', strtotime($e->data)) );
			}

		unset($q);
		$tpl->gotoBlock('_ROOT');
	}
    
############################################################################*/
?>