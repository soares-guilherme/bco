<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

##### Funções de Xml | xml_* | v0.1 ########################################

##### Configurando #########################################################

#\_ Parser
//require_once(DIR_LIB.'xml/parser.lib.php');
require_once($CFG->dir_lib .'xmlparser.lib.php');

##### Classe ###############################################################

class XML
	{
		var $close = array();
		var $indent = 0; // controle de indent e outdent
		var $txt = '';

		#\_ Função inicial
		function XML($main='z1main', $main_atributos=array(), $header=true)
			{
				if($header)
					$this->txt = '<?xml version="1.0" encoding="iso-8859-1"?>'.NL;
				else
					$this->txt = '';

				$this->addTag($main, false, $main_atributos);
			}

		#\_ Adiciona uma tag ao corpo do xml
		function addTag($name, $value=false, $atributos=array(), $full=true)
			{
				$this->txt .= $this->getIndent().'<'.$name; // insere inicio da tag

				if(is_array($atributos))
					{
						foreach($atributos as $k => $e)
							$this->txt .= ' '.$k.'="'.$this->escape($e).'"'; // insere os atributos
					}

				if($full)
					{
						if(!$value)
							{
								$this->txt .= '>'.NL;
								array_unshift($this->close, $name);
								$this->indent++;
							}
						else
							$this->txt .= '>'.$this->escape($value).'</'.$name.'>'.NL;

					}
				else
					$this->txt .= ' />'.NL;
			}
		#\_ Escapa a string xml
		private function escape($str)
			{
				return str_replace('&', '&amp;amp;', ($str) );
			}
		#\_ Retorna o indent eoutdent atual
		private function getIndent()
			{
				$t = '';
				for( $i=0; $i<$this->indent ; $i++ )
					$t .= "\t";

				return $t;
			}
		#\_ Fecha última tag
		function closeTag()
			{
				$this->indent--;
				$this->txt .= $this->getIndent().'</'.array_shift($this->close).'>'.NL;
			}
		#\_ Retorna o processamento do xml
		function retorna()
			{
				while(count($this->close) > 0)
					$this->closeTag();

				return $this->txt;
			}

		function __toString() { return $this->retorna(); }

		#\_ Parser de XML para Array
		public static function toRay($fileName, $file=true, $includeTopTag = false, $lowerCaseTags = true)
			{
				if (!$file)
					$fileContent = $fileName;
				else
					$fileContent = file_get_contents($fileName);

				$p = xml_parser_create('ISO-8859-1');
				xml_parse_into_struct($p, $fileContent, $vals, $index);
				xml_parser_free($p);
				$xml = array();
				$levels = array();
				$multipleData = array();
				$prevTag = "";
				$currTag = "";
				$topTag = false;
				foreach ($vals as $val)
					{
						// Open tag
						if ($val["type"] == "open")
							{
								if (!self::_xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags,
														   $levels, $prevTag, $multipleData, $xml))
									continue;
							}
						// Close tag
						elseif ($val["type"] == "close")
							{
								if (!self::_xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags,
															$levels, $prevTag, $multipleData, $xml))
									continue;
							}
						// Data tag
						elseif ($val["type"] == "complete" && isset($val["value"]))
							{
								$loc =& $xml;
								foreach ($levels as $level)
									{
										$temp =& $loc[str_replace(":arr#", "", $level)];
										$loc =& $temp;
									}
								$tag = $val["tag"];
								if ($lowerCaseTags)
									$tag = strtolower($val["tag"]);
								$loc[$tag] = str_replace("\\n", "\n", $val["value"]);
							}
						// Tag without data
						elseif ($val["type"] == "complete")
							{
								self::_xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags,
												  $levels, $prevTag, $multipleData, $xml);
								self::_xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags,
												  $levels, $prevTag, $multipleData, $xml);
							}
					}
				return $xml;
			}

		private static function _xmlFileToArrayOpen(& $topTag, & $includeTopTag, & $val, & $lowerCaseTags,
										 & $levels, & $prevTag, & $multipleData, & $xml)
			{
				// don't include top tag
				if (!$topTag && !$includeTopTag)
					{
						$topTag = $val["tag"];
						return false;
					}
				$currTag = $val["tag"];
				if ($lowerCaseTags)
					$currTag = str_replace(':', '', strtolower($val["tag"]) );
				$levels[] = $currTag;
				// Multiple items w/ same name. Convert to array.
				if ($prevTag === $currTag)
					{
						if (!array_key_exists($currTag, $multipleData) ||
							!$multipleData[$currTag]["multiple"])
							{
								$loc =& $xml;
								foreach ($levels as $level)
									{
										$temp =& $loc[$level];
										$loc =& $temp;
									}
								$loc = array($loc);
								$multipleData[$currTag]["multiple"] = true;
								$multipleData[$currTag]["multiple_count"] = 0;
							}
						$multipleData[$currTag]["popped"] = false;
						$levels[] = ":arr#" . ++$multipleData[$currTag]["multiple_count"];
					}
				else
					$multipleData[$currTag]["multiple"] = false;
				// Add attributes array
				if (array_key_exists("attributes", $val))
					{
						$loc =& $xml;
						foreach ($levels as $level)
							{
								$temp =& $loc[str_replace(":arr#", "", $level)];
								$loc =& $temp;
							}
						$keys = array_keys($val["attributes"]);
						foreach ($keys as $key)
							{
								$tag = $key;
								if ($lowerCaseTags)
									$tag = str_replace(':', '', strtolower($tag) );
								$loc["attributes"][$tag] = & $val["attributes"][$key];
							}
					}
				return true;
			}

		private static function _xmlFileToArrayClose(& $topTag, & $includeTopTag, & $val, & $lowerCaseTags,
									  & $levels, & $prevTag, & $multipleData, & $xml)
			{
				if ($topTag && !$includeTopTag && $val["tag"] == $topTag)
					return false;

				if(isset($currTag))
					{
						if ($multipleData[$currTag]["multiple"])
							{
								$tkeys = array_reverse(array_keys($multipleData));
								foreach ($tkeys as $tkey)
									{
										if ($multipleData[$tkey]["multiple"] && !$multipleData[$tkey]["popped"])
											{
												array_pop($levels);
												$multipleData[$tkey]["popped"] = true;
												break;
											}
										elseif (!$multipleData[$tkey]["multiple"])
											break;
									}
							}
					}

				$prevTag = array_pop($levels);
				if (strpos($prevTag, "arr#"))
					$prevTag = array_pop($levels);

				return true;
			}
	}

############################################################################
?>