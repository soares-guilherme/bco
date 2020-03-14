<?php
##### Funções de Formulário | fm_* ##### EM DESENVOLVIMENTO!!! #############

class FORM {
	
	public $tags;
	public $labels;
	public $custom_error_check;
	
	private $verificar;
	
	#\_ Função para iniciar um novo formulário
	public function __construct($nome, $action, $estilo, $output = "alert", $param = "", $method = "post", $file = false, $cab = false)
		{
			$this->method = $method;
			$this->fmnome = $nome;
			$this->output = $output;
			$this->estilo = $estilo;
			$this->cab = $cab;
			$this->tags = (object) array();
			$this->labels = (object) array();
			$this->verificar = array();
			$this->custom_error_check = null;
			$this->js = '';
			
			$this->utf8_encoded = !($output=='alert');
			
			$param = '';
			
			if($file)
				$param .= " enctype=\"multipart/form-data\"";
			
			if($this->cab)
				$this->js = "<script language=\"JavaScript\" type=\"text/JavaScript\">";

			$this->js .= "\n<!--\n";
			
			$this->tags->$nome = "<form name=\"$nome\" id=\"$nome\" method=\"$method\" action=\"$action\" onSubmit=\"return fm_".$nome."_check();\" $param>";
		}
	
	#\_ Função para resolver encoding
	private function encode($str)
		{
			if($this->utf8_encoded)
				{
					$str = htmlentities(utf8_encode_once($str), NULL, 'UTF-8');
				}
				
			return $str;
		}

	#\_ Função para terminar com um novo formulário
	public function build(&$tpl=NULL, $new_block=NULL, $print_javascript=true)
		{
			$verification = new TemplatePower($GLOBALS['CFG']->dir_lib.'forms.template.js');
			$verification->prepare();
			
			$verification->assignGlobal('FORM_NAME', $this->fmnome);
			
			// verificar
			foreach($this->verificar as $v)
				{
					// texto
					if($v[2][0] == 'texto')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_texto');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					// email
					if($v[2][0] == 'email')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_email');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					// textarea
					if($v[2][0] == 'textarea')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_textarea');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
								$verification->assign('FORM_MAX', $this->encode($v[4]), $this->utf8_encoded);
						}
					// igualdade
					if($v[2][0] == 'igual')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_igual');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
								$verification->assign('FORM_COMPARE_FIELD', $v[2][1]);
								$verification->assign('FORM_COMPARE_LABEL', $this->encode($v[2][2]), $this->utf8_encoded);
						}
					// CPF
					if($v[2][0] == 'cpf')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_cpf');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					// CNPJ
					if($v[2][0] == 'cnpj')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_cnpj');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					// CAPTCHA
					if($v[2][0] == 'captcha')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_captcha');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					// recaptchav2
					if($v[2][0] == 'recaptchav2')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_recaptchav2');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);
						}
					
					// has ini
					if(!empty($v[3]) and $v[2][0] != 'none')
						{
							$verification->newBlock('error');
							$verification->assign('FORM_FIELD', $v[0]);
								$verification->newBlock('error_ini');
								$verification->assign('FORM_FIELD', $v[0]);
								$verification->assign('FORM_LABEL', $this->encode($v[1]), $this->utf8_encoded);									
								$verification->assign('FORM_INI', $this->encode($v[3]), $this->utf8_encoded);
						}
				}
			
			if($this->output == 'alert')
				{
					$verification->newBlock('print_alert');
					
						$verification->assignGlobal('PRINT_NL', "\\n");
						
						$verification->assignGlobal('PRINT_B', "");
						$verification->assignGlobal('PRINT_B_C', "");
				}
			
			if($this->output == 'z1alert')
				{
					$verification->newBlock('print_z1alert');
					
						$verification->assignGlobal('PRINT_NL', "<br />");
						
						$verification->assignGlobal('PRINT_B', "<b>");
						$verification->assignGlobal('PRINT_B_C', "</b>");
				}
			
			if($this->custom_error_check != null)
				{
					$verification->newBlock('custom_error_check');
					
						$verification->assign('CUSTOM_ERROR_CHECK', $this->custom_error_check);
				}
				
			$this->js .= $verification->getOutputContent()."-->\n";
			
			if($this->cab)
				$this->js .= "</script>";
			
			if(!is_null($tpl))
				{
					if($new_block != NULL)
						$tpl->newBlock($new_block);

					if($print_javascript)
						$tpl->assign('javascript', $this->js, false);
					
					foreach($this->tags as $k => $tag)
						{
							if(is_array($tag))
								{
									foreach($tag as $tag_child_k => $tag_child_e)
										{
											$tpl->newBlock($k);
												$tpl->assign($k, $tag_child_e);
									
												if(isset($this->labels->$k))
													{
														$tpl->assign($k.'_label', $this->labels->{$k}[$tag_child_k]);
													}
										}
								}
							else
								{
									$tpl->assign($k, $tag);
									
									if(isset($this->labels->$k))
										{
											$tpl->assign($k.'_label', $this->labels->$k);
										}
								}
								
							if($new_block != NULL)
								$tpl->gotoBlock($new_block);
						}
					
					$tpl->assign('form_close', '</form>');
				}
		}
	
	private function set_tag($nome, $label, $tag)
		{
			if(substr($nome, -2) == '[]')
				{
					$nome = substr($nome, 0, -2);
					
					if(!isset($this->tags->$nome))
						$this->tags->$nome = array();
					
					if(!isset($this->labels->$nome))
						$this->labels->$nome = array();
					
					$this->tags->{$nome}[] = $tag;
					$this->labels->{$nome}[] = $label;
				}
			else
				{
					$this->tags->$nome = $tag;
					$this->labels->$nome = $label;
				}
		}

	#\_ Funcao que cria um botao de submit (submit ou image)
	public function create_tag_submit($nome, $label, $img = "", $param = "", $tagButton = false)
		{
			if($tagButton)
				{
					$this->tags->$nome = "<button type=\"submit\" name=\"$nome\" id=\"$nome\" value=\"$label\" class=\"".$this->estilo["submit"]."\" $param>$label</button>";
				}
			elseif($img != "")
				{
					$this->tags->$nome = "<input type=\"image\" border=\"0\" name=\"$nome\" id=\"$nome\" src=\"$img\" $param>";
				}
			else
				{
					$this->tags->$nome = "<input type=\"submit\" name=\"$nome\" id=\"$nome\" value=\"$label\" class=\"".$this->estilo["submit"]."\" $param>";
				}
		}

	#\_ Funcao que cria um campo de verificacao capctha
	public function create_tag_captcha($nome, $label, $params=NULL)
		{
			$this->verificar[] = array($nome, $label, array('captcha'));
			
			$class_normal = $this->estilo['input'];
			$class_onerror = $this->estilo['input_onerror'];
			
			$captcha_pepper = md5(time());
			
			$captcha_html = Captcha::getHTML();
			$captcha_id = Captcha::getId();
			$captcha_code = Captcha::getCode();
			
			$this->tags->$nome = '<input type="text" '.
										'name="'.$nome.'" '.
										'id="'.$nome.'" '.
										'size="10" '.
										'maxlength="6" '.
										'data-pepper="'.$captcha_pepper.'" '.
										'data-md5="'.md5($captcha_code.$captcha_pepper).'" '.
										'class="'.$class_normal.'" '.
										'class_normal="'.$class_normal.'" '.
										'class_onerror="'.$class_onerror.'" '.
										$params.'> '.$captcha_html.' <input type="hidden" name="'.$nome.'_salt" id="'.$nome.'_salt" value="'.$captcha_id.'"/> ';
		}

	#\_ Funcao que cria um campo de verificacao recapctha v2
	public function create_tag_recaptchav2($nome, $label, $params=NULL)
		{
			$this->verificar[] = array($nome, $label, array('recaptchav2'));
			
			$this->tags->$nome = '<div class="g-recaptcha" data-sitekey="'.$GLOBALS['CFG']->recaptchav2_sitekey.'"></div> <input type="hidden" name="'.$nome.'" id="'.$nome.'" value=""/>';
		}

	#\_ Funcao que cria um campo texto (text)
	public function create_tag_text($nome, $label, $size, $max_size, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL)
		{
			if(!empty($_POST[$nome]))
				$valor_inicial = $_POST[$nome];

			$this->verificar[] = array($nome, $label, $verificacao);
			
			if($class_normal == NULL)
				$class_normal = $this->estilo['input'];
			
			if($class_onerror == NULL)
				$class_onerror = $this->estilo['input_onerror'];
			
			$this->set_tag($nome, $label, '<input type="text" '.
												'name="'.$nome.'" '.
												'id="'.$nome.'" '.
												'size="'.$size.'" '.
												'maxlength="'.$max_size.'" '.
												'class="'.$class_normal.'" '.
												'class_normal="'.$class_normal.'" '.
												'class_onerror="'.$class_onerror.'" '.
												'value="'.$valor_inicial.'" '.
												$params.'>');
			
			return $this->tags->$nome;
		}

	#\_ Função que cria um campo texto com valor inicial inválido para verificação
	public function create_tag_text_ini($nome, $label, $size, $max_size, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL)
		{
			$this->verificar[] = array($nome, $label, $verificacao, $valor_inicial);

			$params = ' onfocus="'."if(this.value=='".$valor_inicial."'){this.value='';};".'" onblur="'."if(this.value==''){this.value='".$valor_inicial."';};".'" ' . $params;
			
			if(!empty($_POST[$nome]))
				$valor_inicial = $_POST[$nome];
			
			if(!empty($_GET[$nome]))
				$valor_inicial = $_GET[$nome];
			
			if($class_normal == NULL)
				$class_normal = $this->estilo['input'];
			
			if($class_onerror == NULL)
				$class_onerror = $this->estilo['input_onerror'];
			
			$this->tags->$nome = '<input type="text" '.
										'name="'.$nome.'" '.
										'id="'.$nome.'" '.
										'size="'.$size.'" '.
										'maxlength="'.$max_size.'" '.
										'class="'.$class_normal.'" '.
										'class_normal="'.$class_normal.'" '.
										'class_onerror="'.$class_onerror.'" '.
										'value="'.$valor_inicial.'" '.
										$params.'>';
			
			return $this->tags->$nome;
		}

	#\_ Função que cria um campo de senha (text)
	function create_tag_password($nome, $label, $size, $max_size, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL)
		{
			if(!empty($_POST[$nome]))
				$valor_inicial = $_POST[$nome];

			$this->verificar[] = array($nome, $label, $verificacao);
			
			if($class_normal == NULL)
				$class_normal = $this->estilo['input'];
			
			if($class_onerror == NULL)
				$class_onerror = $this->estilo['input_onerror'];
			
			$this->tags->$nome = '<input type="password" '.
										'name="'.$nome.'" '.
										'id="'.$nome.'" '.
										'size="'.$size.'" '.
										'maxlength="'.$max_size.'" '.
										'class="'.$class_normal.'" '.
										'class_normal="'.$class_normal.'" '.
										'class_onerror="'.$class_onerror.'" '.
										'value="'.$valor_inicial.'" '.
										$params.'>';
			
			return $this->tags->$nome;
		}

	#\_ Função que cria um campo de senha com valor inicial inválido para verificação
	function create_tag_password_ini($nome, $label, $size, $max_size, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL)
		{
			$this->verificar[] = array($nome, $label, $verificacao, $valor_inicial);

			$params = ' onfocus="'."if(this.value=='".$valor_inicial."'){this.value='';};".'" onblur="'."if(this.value==''){this.value='".$valor_inicial."';};".'" ' . $params;
			
			if(!empty($_POST[$nome]))
				$valor_inicial = $_POST[$nome];

			if($class_normal == NULL)
				$class_normal = $this->estilo['input'];
			
			if($class_onerror == NULL)
				$class_onerror = $this->estilo['input_onerror'];
			
			$this->tags->$nome = '<input type="password" '.
										'name="'.$nome.'" '.
										'id="'.$nome.'" '.
										'size="'.$size.'" '.
										'maxlength="'.$max_size.'" '.
										'class="'.$class_normal.'" '.
										'class_normal="'.$class_normal.'" '.
										'class_onerror="'.$class_onerror.'" '.
										'value="'.$valor_inicial.'" '.
										$params.'>';
			
			return $this->tags->$nome;
		}

		#\_ Função que cria um campo radio (radio)
		function create_tag_radio($nome, $lbl, $vlr, $sel = "", $param = "")
			{
				global $_POST;
				$checked = false;

				if(isset($_POST) and !empty($_POST[$nome]))
					{
						if($_POST[$nome] == $vlr)
							$checked = "checked";
					}
				elseif($sel)
					$checked = "checked";
		
				$ret = "<input type=\"radio\" name=\"$nome\" id=\"$nome\" value=\"$vlr\" $param $checked>";
				$this->tags->$lbl = $ret;
				return $ret;
			}

		#\_ Função que cria uma caixa de seleção (checkbox)
		function create_tag_chbox($nome, $lbl, $vlr, $sel = "", $param = "")
			{
				global $_POST;
				$checked = false;

				if(isset($_POST[$nome]) and strtoupper($this->method) == 'POST')
					{
						if($_POST[$nome] == $vlr)
							$checked = "checked";
					}
				elseif($sel)
					$checked = "checked";
		
				$this->tags->$nome = "<input type=\"checkbox\" name=\"$nome\" id=\"$nome\" value=\"$vlr\" $param $checked>";
				return $this->tags->$nome;
			}

		#\_ Funcao que cria um campo escondido (hidden)
		function create_tag_hidden($nome, $vlr)
			{
				global $_POST;
				
				if(isset($_POST[$nome]))
					{
						$vlr = $_POST[$nome];
					}

				$this->tags->$nome = "<input type=\"hidden\" name=\"$nome\" id=\"$nome\" value=\"$vlr\">";
				return $this->tags->$nome;
			}

	#\_ Funcao que cria uma combo (select)
	function create_tag_select($nome, $label, $valores=array(), $valor_inicial='', $dica='', $params='', $verificacao=array('texto'), $class_normal=NULL, $class_onerror=NULL, $multiple=NULL )
		{
			$this->verificar[] = array($nome, $label, $verificacao);
			if($class_normal == NULL)
				$class_normal = $this->estilo['select'];
			
			if($class_onerror == NULL)
				$class_onerror = $this->estilo['select_onerror'];
			
			$name = $nome;
			
			if($multiple != NULL)
				{
					$name .= '[]';
					$params .= ' multiple="true" size="'.$multiple.'"';
				}
			
			$tag = '<select name="'.$name.'" '.
							'id="'.$nome.'" '.
							'class="'.$class_normal.'" '.
							'class_normal="'.$class_normal.'" '.
							'class_onerror="'.$class_onerror.'" '.
							$params.'>';
	
            if(!empty($_POST[$nome]))
                $valor_inicial = $_POST[$nome];
    
			if(!empty($_GET[$nome]))
				$valor_inicial = $_GET[$nome];
	
			if(empty($ini) and !empty($dica))
				{
					if($multiple != NULL)
						$tag .= '<option value="" disabled>'.$dica."</option>\n";
					else
						$tag .= '<option value="" selected>'.$dica."</option>\n";
				}
			
			$has_optgroup = false;
			
			foreach($valores as $chave => $val)
				{
					if(is_array($val))
						{							
							if($has_optgroup)
								$tag .= '</optgroup>'.NL;
							
							$tag .= '<optgroup label="'.$chave.'">'.NL;
							
							$has_optgroup = true;
						}
					else
						{
							$val = array($chave => $val);
						}
						
					foreach($val as $chave => $valor)
						{
							
							$checked = "";
							
							if(isset($valor_inicial))
								{
									if(is_array($valor_inicial))
										{
											foreach($valor_inicial as $e_inicial)
												{
													if($valor == $e_inicial)
														{
															$checked = "selected";
														}
												}
										}
									elseif($valor == $valor_inicial)
										{
											$checked = "selected";
										}
								}

							$tag .= '<option value="'.$valor.'" '.$checked.'>'.$chave."</option>\n";
						}
				}
			
			if($has_optgroup)
				$tag .= '</optgroup>'.NL;
			
			$tag .= "</select>\n";

			$this->tags->$nome = $tag;
			
			return $tag;
		}

    #\_ Função que cria uma combo (select)
    function create_tag_select_alt($nome, $label, $valores=array(), $valor_inicial='', $dica='', $params='', $verificacao=array('texto'), $class_normal=NULL, $class_onerror=NULL, $onclick_submit=NULL)
        {
            $this->verificar[] = array($nome, $label, $verificacao);
            
            if($class_normal == NULL)
                $class_normal = $this->estilo['select_alt'];
            
            if($class_onerror == NULL)
                $class_onerror = $this->estilo['select_alt_onerror'];
            
            $name = $nome . '[]';
            
            $tag = '<div id="'.$nome.'_holder" '.
                            'class="'.$class_normal.'" '.
                            'class_normal="'.$class_normal.'" '.
                            'class_onerror="'.$class_onerror.'" '.
                            $params.'>';
    
            if(!empty($_POST[$nome]))
                $valor_inicial = $_POST[$nome];
    
            if(!empty($_GET[$nome]))
                $valor_inicial = $_GET[$nome];
    
            if(empty($ini) and !empty($dica))
                {
                    $tag .= '<p><em>'.$dica."</em></p>\n";
                }
    
            if(!empty($onclick_submit))
                {
                    $onclick_submit = ", '".$this->fmnome."'";
                }
    
            foreach($valores as $chave => $valor)
                {
                    $checked = "";
                    $classname = ' class="SelectAlt_option" ';
                    
                    if(isset($valor_inicial))
                        {
                            if(is_array($valor_inicial))
                                {
                                    foreach($valor_inicial as $e_inicial)
                                        {
                                            if($valor == $e_inicial)
                                                {
                                                    $checked = "checked";
                                                    $classname = ' class="SelectAlt_selected"';
                                                }
                                        }
                                }
                            elseif($valor == $valor_inicial)
                                {
                                    $checked = "checked";
                                    $classname = ' class="SelectAlt_selected" ';
                                }
                        }

                    $tag .= '<p onclick="SelectAlt_click(this'.$onclick_submit.');"'.$classname.'><span style="display:none;"><input type="checkbox" name="'.$name.'" value="'.$valor.'" '.$checked.'></span>'.$chave."</p>\n";
                }
    
            $tag .= "</div>\n";

            $this->tags->$nome = $tag;
            
            return $tag;
        }

		#\_ Função que cria uma área texto (textarea)
		function create_tag_textarea($nome, $label, $cols, $rows, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL, $max=NULL)
			{
				if(!empty($_POST[$nome]))
					$valor_inicial = $_POST[$nome];

				$this->verificar[] = array($nome, $label, $verificacao, $valor_inicial, $max);

				if($class_normal == NULL)
					$class_normal = $this->estilo['textarea'];
				
				if($class_onerror == NULL)
					$class_onerror = $this->estilo['textarea_onerror'];
				
				$this->tags->$nome = '<textarea type="password" '.
											'name="'.$nome.'" '.
											'id="'.$nome.'" '.
											'cols="'.$cols.'" '.
											'rows="'.$rows.'" '.
											'class="'.$class_normal.'" '.
											'class_normal="'.$class_normal.'" '.
											'class_onerror="'.$class_onerror.'" '.
											$params.'>'.$valor_inicial.'</textarea>';
				
				return $this->tags->$nome;
			}

		#\_ Função que cria uma área texto (textarea)
		function create_tag_textarea_ini($nome, $label, $cols, $rows, $valor_inicial='', $verificacao=array('texto'), $params='', $class_normal=NULL, $class_onerror=NULL, $max=NULL)
			{
				$this->verificar[] = array($nome, $label, $verificacao, $valor_inicial, $max);

				$params = ' onfocus="'."if(this.value=='".$valor_inicial."'){this.value='';};".'" onblur="'."if(this.value==''){this.value='".$valor_inicial."';};".'" ' . $params;

				if(!empty($_POST[$nome]))
					$valor_inicial = $_POST[$nome];
			
				if($class_normal == NULL)
					$class_normal = $this->estilo['textarea'];
				
				if($class_onerror == NULL)
					$class_onerror = $this->estilo['textarea_onerror'];
				
				$this->tags->$nome = '<textarea type="password" '.
											'name="'.$nome.'" '.
											'id="'.$nome.'" '.
											'cols="'.$cols.'" '.
											'rows="'.$rows.'" '.
											'class="'.$class_normal.'" '.
											'class_normal="'.$class_normal.'" '.
											'class_onerror="'.$class_onerror.'" '.
											$params.'>'.$valor_inicial.'</textarea>';
				
				return $this->tags->$nome;
			}

		#\_ Função que cria um campo file
		function create_tag_file($nome, $lbl, $sze, $param = "")
			{
				$this->tags->$nome = "<input type=\"file\" name=\"$nome\" id=\"$nome\" size=\"$sze\" class=\"".$this->estilo["input"]."\" $param>";
				
				return $this->tags->$nome;
			}

		#\_ Função que cria um campo de multiplo files # 2017-08-02
		function create_tag_files($nome, $lbl, $sze, $param = "", $a_msg = "+ Adicionar mais campos de anexo")
			{
				$file_tag = '<input type="file" name="'.$nome.'[]" size="'.$sze.'" class="'.$this->estilo["files"].'" '.$param.'><br>';
				
				$files_tag = '<div id="'.$nome.'">';
				
				if(!empty($_POST[$nome]))
					{						
						foreach($_POST[$nome] as $pfile)
							{
								$files_tag .= '<label>'.$this->create_tag_chbox($nome.'[]', $pfile, $pfile, true).'&nbsp;'.$pfile.'</label><br>';
							}
					}
				
				$files_tag .= $file_tag.
										'<button type="button" onclick="$(\''.str_replace('"', '&quot;', $file_tag).'\').insertBefore(this).hide().fadeIn(800);">'.$a_msg.'</button>'.
									'</div>';
				
				$this->tags->$nome = $files_tag;
				
				return $this->tags->$nome;
			}
	}

############################################################################
?>