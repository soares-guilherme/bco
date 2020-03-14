<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

/**
 * QUIZ - Classe para geracao e processamento de questionarios.
 *
 * @copyright Copyright (C) 2003-2014, PluGzOne - http://www.plugzone.com.br
 * @author André Rutz Porto <andre@plugzone.com.br>
 * @package z1
 * @version 1.0
 */
 
class QUIZ {
	
	public static $orders = array(' RAND() ', ' id ASC ', ' id DESC ', ' questao ASC ', ' ordem ASC ');
	public static $enable_notas = false;
	
	public static $block = 'qu_quiz';
	public static $block_list = 'qu_list';
	public static $block_question = 'qu_question';
	public static $block_noquestion = 'qu_noquestion';
	public static $block_alert = 'qu_alert';
	
	public static $tag_question = '<p><b>%s</b></p>';
	public static $tag_question_h2 = '<h2 data-label="%s">%s</h2>';
	public static $tag_question_h3 = '<h3 data-label="%s">%s</h3>';
	public static $tag_answer = '<p style="margin: 5px;line-height: 30px;">%s</p>';
	
	public static $block_validation_radio = 'qu_validation_radio';
	public static $block_validation_chbox = 'qu_validation_chbox';
	public static $block_validation_textarea = 'qu_validation_textarea';
	public static $block_validation_file = 'qu_validation_file';

	public static $errors = false;
	
	public static function generate($qid, $con, &$tpl, &$form, $force_questions='')
		{
			$quiz = bd_executa("SELECT *
									FROM questionarios
										WHERE id = '".$qid."'
											AND status = '1'", $con);

			if(!$quiz->nada)
				{
					$quiz = $quiz->res->rid0;

					if(!array_key_exists($quiz->ordem, self::$orders))
						$quiz->ordem = 1;

					$questions = bd_executa("SELECT *
												FROM qu_questoes
													WHERE id_questionario = '".$qid."'
														AND status = '1'
														".(!empty($force_questions)? " AND ( id = '".implode("' OR id = '", explode(',', $force_questions))."' ) " : '')."
													ORDER BY ".self::$orders[$quiz->ordem], $con);

					$tpl->newBlock(self::$block);
						$tpl->assign('nome', $quiz->nome);
						$tpl->assign('descricao', $quiz->descricao);
						$tpl->assign('qu_id', $form->create_tag_hidden('qu_id', $qid) );
						
						if(!empty($force_questions))
							{
								$tpl->assign('qu_force_questions', $form->create_tag_hidden('qu_force_questions', $force_questions) );
							}

						if(!empty($quiz->msg_sucesso))
							{
								$tpl->assign('qu_msg_success', $form->create_tag_hidden('qu_msg_success', $quiz->msg_sucesso) );
							}

					$tpl->newBlock(self::$block_list);

					if(!$questions->nada)
						{
							foreach($questions->res as $question)
								{
									$tpl->newBlock(self::$block_question);

									switch($question->tipo)
										{
											case 'RADIO':
											
												$tpl->assign('question', __(self::$tag_question, nl2br($question->questao)) );

												$answers = strtr($question->respostas, array("\r\n" => '|%%|', "\r" => '|%%|', "\n" => '|%%|'));;
												$answers = explode("|%%|", $answers);
												
												$answers_full = '';
												
												foreach($answers as $r)
													{
														$answers_full .= '<label>'.$form->create_tag_radio('resposta_'.$question->id, $r, $r).'&nbsp;'.$r.'</label><br />';
													}

												$tpl->assign('answers', __(self::$tag_answer, $answers_full) );

												if($question->opcional == '0')
													{
														$tpl->newBlock(self::$block_validation_radio);
															$tpl->assign('name', 'resposta_'.$question->id );
															$tpl->assign('label', nl2br($question->questao) );
													}

											break;
											case 'CHECKBOX':
											
												$tpl->assign('question', __(self::$tag_question, nl2br($question->questao)) );
											
												$answers = strtr($question->respostas, array("\r\n" => '|%%|', "\r" => '|%%|', "\n" => '|%%|'));;
												$answers = explode("|%%|", $answers);
												
												$answers_full = '';
												
												foreach($answers as $r)
													{
														$answers_full .= '<label>'.$form->create_tag_chbox('resposta_'.$question->id.'[]', $r, $r).'&nbsp;'.$r.'</label><br />';
													}
												
												$tpl->assign('answers', __(self::$tag_answer, $answers_full) );
											
												if($question->opcional == '0')
													{
														$tpl->newBlock(self::$block_validation_chbox);
															$tpl->assign('name', 'resposta_'.$question->id );
															$tpl->assign('label', nl2br($question->questao) );
													}

											break;
											case 'TEXTAREA':
											
												$tpl->assign('question', __(self::$tag_question, nl2br($question->questao)) );

												$ini = '';
												$params = '';
												$class_normal = NULL;
												$class_onerror = NULL;
												$max = NULL;

												if($question->opcional == '0')
													{
														$verify = array('texto');
													}
												else
													{
														$verify = array('none');
													}

												if($question->max > 0)
													{
														$verify = array('textarea');
														$max = $question->max;
													}

												$tpl->assign('answers', __(self::$tag_answer, $form->create_tag_textarea('resposta_'.$question->id, __($question->questao), 80, 3, $ini, $verify, $params, $class_normal, $class_onerror, $max)) );
											
											break;
											case 'TEXT':
											
												$tpl->assign('question', __(self::$tag_question, nl2br($question->questao)) );

												$ini = '';

												if($question->opcional == '0')
													{
														$verify = array('texto');
													}
												else
													{
														$verify = array('none');
													}

												$tpl->assign('answers', __(self::$tag_answer, $form->create_tag_text('resposta_'.$question->id, __($question->questao), 80, 100, $ini, $verify)) );
											
											break;
											case 'FILE':
											case 'ANEXO':
											
												$tpl->assign('question', __(self::$tag_question, nl2br($question->questao)) );

												$tpl->assign('answers', __(self::$tag_answer, $form->create_tag_file('resposta_'.$question->id, __($question->questao), 50)) );
											
												if($question->opcional == '0')
													{
														$tpl->newBlock(self::$block_validation_file);
															$tpl->assign('name', 'resposta_'.$question->id );
															$tpl->assign('label', nl2br($question->questao) );
													}

											break;
											case 'LABEL':
											case 'H2':
											
												$tpl->assign('question', __(self::$tag_question_h2, $question->id, nl2br($question->questao)) );

											break;
											case 'H3':
											
												$tpl->assign('question', __(self::$tag_question_h3, $question->id, nl2br($question->questao)) );

											break;
										}
								}

							$r = true;
						}
					else
						{
							$r = false;
						}
				}
			else
				{
					$r = false;
				}

			$tpl->gotoBlock('quiz');

			return $r;
		}

	public static function answers($qid, $id_tentativa, $con, $params=NULL)
		{
			$answers = array();

			$quiz = bd_executa("SELECT *
									FROM questionarios
										WHERE id = '".$qid."'
											AND status = '1'", $con);

			if(!$quiz->nada)
				{
					$quiz = $quiz->res->rid0;

					if(!array_key_exists($quiz->ordem, self::$orders))
						$quiz->ordem = 1;

					$questions = bd_executa("SELECT *
												FROM qu_questoes
													WHERE id_questionario = '".$qid."'
													ORDER BY ".self::$orders[$quiz->ordem], $con);
													
					if(!$questions->nada)
						{
							foreach($questions->res as $question)
								{
									switch($question->tipo)
										{
											case 'RADIO':
											case 'CHECKBOX':
											case 'TEXTAREA':
											case 'TEXT':
											
												$answer = bd_executa("SELECT id, resposta
																			FROM qu_respostas
																				WHERE id_tentativa = '".$id_tentativa."' 
																					AND id_questao = '".$question->id."'
																				ORDER BY id DESC", $con);

												if(!$answer->nada)
													{
														$answer = $answer->res->rid0;
														
														$answers[$question->id] = array('questao' => nl2br($question->questao),
																						'resposta' => nl2br($answer->resposta),
																						'type' =>'default' );
													}
												elseif(!empty($params['enabled_only']) and $question->status == 0)
													{
														continue 2;
													}
												else
													{
														$answers[$question->id] = array('questao' => nl2br($question->questao),
																						'type' =>'default' );
													}
												
											break;
											case 'FILE':
											case 'ANEXO':
											
												$answer = bd_executa("SELECT id, resposta
																			FROM qu_respostas
																				WHERE id_tentativa = '".$id_tentativa."' 
																					AND id_questao = '".$question->id."'
																				ORDER BY id DESC", $con);

												if(!$answer->nada)
													{
														$answer = $answer->res->rid0;

														$answers[$question->id] = array('questao' => nl2br($question->questao),
																						'resposta' => nl2br($answer->resposta),
																						'type' => 'file' );
													}
												elseif(!empty($params['enabled_only']) and $question->status == 0)
													{
														continue 2;
													}
												else
													{
														$answers[$question->id] = array('questao' => nl2br($question->questao),
																						'type' => 'file' );
													}

											break;
											case 'LABEL':
											case 'H2':
											
												if(!empty($params['enabled_only']) and $question->status == 0)
													{
														continue 2;
													}
												
												$answers[$question->id] = array('questao' => __(self::$tag_question_h2, $question->id, nl2br($question->questao)),
																				'type' => 'tag' );
												
											break;
											case 'H3':
											
												if(!empty($params['enabled_only']) and $question->status == 0)
													{
														continue 2;
													}
												
												$answers[$question->id] = array('questao' => __(self::$tag_question_h3, $question->id, nl2br($question->questao)),
																				'type' => 'tag' );

											break;
										}
								}
						}
				}

			return $answers;
		}

	public static function process($qid, $con, $id_tentativa=false, $id_cliente=false)
		{
			global $CFG;
			
			if(!$id_cliente)
				{
					if(Auth::Verify())
						{
							$id_cliente = Auth::GetInfo('id');
						}
					else
						{
							return false;
						}
				}

			$query_adt = '';
			
			if(!empty($_POST['qu_force_questions']))
				{
					$query_adt .= " AND ( q.id = '".implode("' OR q.id = '", explode(',', $_POST['qu_force_questions']))."' ) ";
				}
			else
				{
					$query_adt .= " AND qu.status = '1'
									AND q.status = '1' ";
				}
			
			if($_POST['qu_id'] == $qid)
				{
					$questions = bd_executa("SELECT q.*
											FROM qu_questoes AS q, questionarios AS qu
												WHERE qu.id = '".$qid."'
													AND q.id_questionario = qu.id
													".$query_adt, $con);

					if(!$questions->nada)
						{
							if(!$id_tentativa)
								{
									$tentativa = bd_executa("SELECT tentativa FROM qu_tentativas WHERE id_cliente = '".$id_cliente."' AND id_questionario = '".$qid."' ORDER BY tentativa DESC LIMIT 1", $CFG->con);
									
									if($tentativa->nada)
										$tentativa = 1;
									else
										$tentativa = $tentativa->res->rid0->tentativa + 1;

									$id_tentativa = bd_executa("INSERT INTO `qu_tentativas` ( `id_cliente`, `id_questionario`, `tentativa`, `creation_date`)
																			VALUES ( '".$id_cliente."', '".$qid."', '".$tentativa."', NOW() )" , $CFG->con);
								}

							$respostas_old = bd_ray('id_questao', 'id,resposta', 'qu_respostas', $con, " WHERE id_tentativa = '".$id_tentativa."' ");

							$msg = 	'';
							$nota_total = 0;
							$peso_total = 0;

							foreach($questions->res as $e)
								{
									$Edit = false;

									if(!empty($respostas_old[$e->id]))
										{
											$respostas_old[$e->id] = explode(' - ', $respostas_old[$e->id]);
											$Edit = true;
										}

									/*/
									$resposta = bd_executa("SELECT id, resposta 
																FROM qu_respostas 
																	WHERE id_tentativa = '".$id_tentativa."'
																		AND id_questao = '".$e->id."'
																	ORDER BY id DESC LIMIT 1", $con);
									
									if(!$resposta->nada)
										{
											$Edit = true;
											$resposta = $resposta->res->rid0;
										} /**/
									
									$nota = $e->peso;
									$respostas_bd = '';
									$respostas_full = '';
									$certas_full = '';
									$respostas_full = '';
									
									switch($e->tipo)
										{
											case 'RADIO':
											
												$respostas_bd = $_POST['resposta_'.$e->id];
												$respostas_full = $_POST['resposta_'.$e->id].';';
												
												$certas = strtr($e->respostas_certas, array("\r\n" => '|%%|', "\r" => '|%%|', "\n" => '|%%|') );
												$certas = explode("|%%|", $certas);
												
												$certas_full = '';
												
												foreach($certas as $c)
													{
														$certas_full .= $c.';';
														
														if($respostas_full != $c.';')
															$nota = 0;
													}
												
											break;
											case 'CHECKBOX':

												$respostas_bd = '';
												$respostas_full = '';
												
												foreach($_POST['resposta_'.$e->id] as $r)
													{
														$respostas_bd .= $r.'\n';
														$respostas_full .= $r.';';
													}
												
												$certas = strtr($e->respostas_certas, array("\r\n" => '|%%|', "\r" => '|%%|', "\n" => '|%%|') );
												$certas = explode("|%%|", $certas);
												
												$certas_full = '';
												
												foreach($certas as $c)
													{
														$certas_full .= $c.';';
													}
												
												if($respostas_full != $certas_full)
													$nota = 0;
												
											break;
											case 'TEXTAREA':
											case 'TEXT':
											
												$respostas_bd = $_POST['resposta_'.$e->id];
												$respostas_full = $_POST['resposta_'.$e->id].';';
												$certas_full = $e->respostas_certas.';';
												
												if(empty($_POST['resposta_'.$e->id]))
													$nota = 0;
												else
													$nota = '';
											
											break;
											case 'FILE':
											case 'ANEXO':
											
												// Anexo
												if(!empty($_FILES['resposta_'.$e->id]['tmp_name']))
													{
														@set_time_limit(300);

														$f_info = pathinfo($_FILES['resposta_'.$e->id]['name']);

														if(in_array(strtolower($f_info['extension']), $CFG->files_ext))
															{
																$file_name = url_simple(time().'_'.__($f_info['filename'])).'.'.$f_info['extension'];
																
																if(!@move_uploaded_file($_FILES['resposta_'.$e->id]['tmp_name'], $CFG->dir_files.$file_name))
																	{
																		$CFG->erros .= nl2br(NL.NL.'Erro no envio do arquivo <b>'.$e->questao.'</b>'.NL.NL);
																	}
															}

														if($Edit and file_exists($CFG->dir_files.$respostas_old[$e->id][1]))
															{
																@unlink($CFG->dir_files.$respostas_old[$e->id][1]);
															}
													}
												else
													{
														continue 2;
													}

												$respostas_bd = $file_name;
												$respostas_full = $CFG->url_files.$file_name.';';

											break;
										}
									
									$nota_total += $nota;
									$peso_total += $e->peso;
									
									$msg .= __('Questão: %s', $e->questao.NL);
									$msg .= __('Resposta: %s', $respostas_full.NL);
									$msg .= 'Resposta Certa: '.$certas_full.NL;
									$msg .= 'Nota: '.$nota.';'.NL;

									$msg .= NL;
									
									if(!$Edit)
										{
											$resposta = bd_executa("INSERT INTO `qu_respostas` ( `id_tentativa`, `id_questao`, `resposta`, `nota`, `creation_date`)
																			VALUES ( '".$id_tentativa."', '".$e->id."', '".$respostas_bd."', '".$nota."', NOW() )" , $con);
										}
									else
										{
											bd_executa("UPDATE `qu_respostas` SET `resposta` = '".$respostas_bd."', 
																					`nota` = '".$nota."', 
																					`modify_date` = NOW()
																				WHERE id = '".$respostas_old[$e->id][0]."' 
																					AND id_tentativa = '".$id_tentativa."' 
																					AND id_questao = '".$e->id."'", $con);
										}
								}

							return true;
						}
					else
						{
							return false;
						}
				}
			else
				{
					return false;
				}
		}
}

?>