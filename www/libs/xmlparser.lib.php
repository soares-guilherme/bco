<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

############################################################################

// classe
class XMLPARSER
	{
		public $def_id = 'id';
		public $def_value = 'value';
		public $def_attributes = 'attributes';
		public $short = true; // case false tags values are put in ['values']
		public $atributes_separated = true; // case false attributes wont be put in ['attributes']
		public $output = '';
		
		#\_ Função construtora da class
		public function XMLPARSER ($data, $isfile=false)
			{
				if(!$isfile)
					$this->data = trim($data);
				else
					$this->data = trim(file_get_contents($data));
			}
		
		#\_ função para parsing do conteudo xml
		public function parse ()
			{
				$ret = array();				

				$ret = $this->build($this->data);
				$this->output = $ret;
				return $ret;
			}
		
		#\_ Função que constrói o array
		private function build($buffer)
			{
				$ret = array();
				$repeated = array();
				while(strlen($buffer) > 0)
					{
						$tmp = array();
						$tag_type = $this->getTagType($buffer);

						if ($tag_type == 'head' or $tag_type == 'doctype' or $tag_type == 'comment')
							{
								$tag_end = strpos($buffer, '>') + 1;
								$buffer = substr($buffer, $tag_end);
								continue;
							}
						
						if ($tag_type == 'cdata')
							{
								$ini = strpos($buffer, 'CDATA[')+6;
								$len = strpos($buffer, ']')-$ini;
								$content = substr($buffer, $ini, $len);

								return $content;
							}

						$tag_name = $this->getTagName($buffer);
						
						if ( $tag_type == 'aberta' )
							{
								$tag_open_end = strpos($buffer, '>')+1;
								$tag_close_ini = strpos($buffer, '</'.$tag_name.'>', $tag_open_end);
								if($tag_close_ini == false) // tag sem fechamento
									{
										$buffer = substr($buffer, $tag_open_end+strlen($tag_name)+1);
										continue;
									}
								$tag_close_end = $tag_close_ini + 3 + strlen($tag_name);
								$tag_content_length = $tag_close_ini - $tag_open_end;
								$tag_content_value = trim(substr($buffer, $tag_open_end, $tag_content_length));

								// tags child
								if(strpos($tag_content_value, '<') !== false)
									$tmp[$tag_name] = $this->build($tag_content_value);
								else
									{
										if($this->short)
											$tmp[$tag_name] = $tag_content_value;
										else
											$tmp[$tag_name][$this->def_value] = $tag_content_value;
									}
								
								$att_ini = strpos($buffer, '<') + 1 + strlen($tag_name);
								$att_len = strpos($buffer, '>', $att_ini) - $att_ini;
								$str_att = trim(substr($buffer, $att_ini, $att_len));
		
								$att = $this->getTagAttributes($str_att);
								
								if($att != false)
									{
										foreach($att as $p)
											{
												if(is_array($p))
													{
														if(!is_array($tmp[$tag_name]) and !empty($tmp[$tag_name]))
															$tmp[$tag_name] = array($this->def_value => $tmp[$tag_name]);

														if($this->atributes_separated)
															$tmp[$tag_name][$this->def_attributes][$p['key']] = $p['value'];
														else
															$tmp[$tag_name][$p['key']] = $p['value'];
													}
											}
									}
								$buffer = substr($buffer, $tag_close_end);
							}
						elseif ( $tag_type == 'fechada' )
							{
								$tag_end = strpos($buffer, '>') + 1;
								$att_ini = strpos($buffer, '<') + 1 + strlen($tag_name);
								$att_len = $tag_end - 3 - $att_ini;
								$str_att = trim(substr($buffer, $att_ini, $att_len));
								
								$att = $this->getTagAttributes($str_att);
								
								if($att != false)
									{
										foreach($att as $p)
											{
												if(is_array($p))
													{
														if($this->atributes_separated)
															$tmp[$tag_name][$this->def_attributes][$p['key']] = $p['value'];
														else
															$tmp[$tag_name][$p['key']] = $p['value'];
													}
											}
									}

								$buffer = substr($buffer, $tag_end);
							}

						foreach($tmp as $k => $v)
							{
								if(isset($ret[$k]) and !$repeated[$k])
									{
										$temp = $ret[$k];
										unset($ret[$k]);
										if(!isset($temp['id']))
											$ret[$k][] = $temp;
										else
											$ret[$k][$temp['id']] = $temp;
										if(!isset($v['id']))
											$ret[$k][] = $v;
										else
											$ret[$k][$v['id']] = $v;
										
										$repeated[$k] = true;
									}
								elseif(isset($ret[$k]) and $repeated[$k])
									{
										if(!isset($v['id']))
											$ret[$k][] = $v;
										else
											$ret[$k][$v['id']] = $v;
									}
								else
									{
										$ret[$k] = $v;
									}
							}
					}

				return $ret;	
			}
		
		#\_ função que verifica se a tag é aberta ou fechada
		private function getTagType ($buffer)
			{				
				$ini	= strpos($buffer, '<');
				$limit 	= strpos($buffer, '>', $ini);
				
				if($ini !== false and $limit !== false)
					{
						if ($buffer{$limit-1} == '/') // verifica se é uma tag fechada
							return 'fechada';
						elseif ($buffer{$limit-1} == '?') // verifica se é do cabeçalho
							return 'head';
						elseif ($buffer{$ini+1} == '-' and $buffer{$ini+2} == '-') // verifica se é do cabeçalho
							return 'comment';
						elseif ($buffer{$ini+1} == '!' and $buffer{$ini+2} == '[') // verifica se é do cabeçalho
							return 'cdata';
						elseif ($buffer{$ini+1} == '!') // verifica se é do cabeçalho
							return 'doctype';
					}
				
				return 'aberta';
			}
		
		#\_ função que retorna o nome da tag
		private function getTagName ($buffer)
			{				
				$ini	= strpos($buffer, '<')+1;
				$end 	= strpos($buffer, ' ', $ini);
				$limit	= strpos($buffer, '>', $ini);

				if($end === false or $end > $limit)
					{
						$end = strpos($buffer, '/');
						if($end === false or $end > $limit)
							$end = $limit;
					}
				return trim(substr($buffer, $ini, $end-$ini));
			}
		
		#\_ função que retorna os atributos de uma tag
		private function getTagAttributes($buffer) // função que extrai os parâmetros da tag
			{
				$ret = array();
				
				$sep = strpos($buffer, '=');
				if($sep === false)
					return false; // verificação de formatação
				
				$key = substr($buffer, 0, $sep);
				$delimiter = $buffer{$sep+1};
				if($delimiter != '"' and $delimiter != "'")
					{
						$end = strpos($buffer, ' ');
						if($end === false)
							{
								$value = substr($buffer, $sep);
								$end = strlen($buffer);
							}
						else
							$value = substr($buffer, $sep+1, $end-$sep-1);
							
					}
				else
					{
						$end = strpos($buffer, $delimiter, $sep+2);
						if($end === false)
							return false; // verificação de formatação
						
						$value = substr($buffer, $sep+2, $end-$sep-2);
					}
				
				if($end+1 == strlen($buffer))
					return array(array('key' => $key, 'value' => $value));
				
				$ret = $this->getTagAttributes(trim(substr($buffer, $end+1, strlen($buffer)-$end-1 )) );

				$ret[] = array('key' => $key, 'value' => $value);
				
				return $ret;
			}
	}

############################################################################
?>