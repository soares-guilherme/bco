<?php
#**************************************************************************#
#                         PluGzOne Soluções na Web                         #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

############################################################################

/* 

	IMPRESSÕES DE CONTEÚDO 
	
	print_paginator					Imprime paginador padrão

*/

function print_paginator(&$q, &$tpl, $T, $PP, $link, $block='paginador', $items=0)
	{
		$tpl->newBlock($block);
		
		$item_ini = 0;
		$item_end = $items;
		
		$items_half = $items/2;
		
		$aPag = ($T / $PP) + 1; // pagina atual
		$tPag = ceil($q->lin / $PP); // total de paginas
		
		#\_ Páginas
		$tpl->assign("aPag", $aPag); /**/
		$tpl->assign("tPag", $tPag);
		
		$total_pags = count($q->pags);
		
		#\_ Números de Páginas
		if($items > 0)
			{
				if($aPag > $items_half and $total_pags > $items)
					{
						$item_ini = ceil($aPag - $items_half);
						$item_end = $item_ini+$items-1;
						
						if($item_end > $total_pags)
							{
								$item_end = $total_pags;
								$item_ini = $total_pags-$items+1;
							}
					}
				
				foreach($q->pags as $k => $e)
					{
						if( $k >= $item_ini and $k <= $item_end )
							{								
								$tpl->newBlock($block.'_item');
								
									$k_label = $k;
									
									if($k_label < 10)
										$k_label = str_pad($k_label, 1, '0', STR_PAD_LEFT);
									
									$tpl->assign($block.'_item.num', $k_label);
									
									if($e == '')
										{
											$tpl->assign($block.'_item.link', "javascript:void(null)");
											$tpl->assign($block.'_item.adt', 'class_paginator');
										}
									else
										{
											if($e == 'zero')
												$e = 0;
						
											$tpl->assign($block.'_item.link', $link.$e );
										}
							} 
					}
			} 
	
		#\_ Links
		if($q->pags[1] == 'zero' and $total_pags > 2)
			$tpl->assign($block.".link_primeiro", $link.'0');
		else
			$tpl->assign($block.".link_primeiro", "javascript:void(null)");

		if(isset($q->pags[$k]) and $total_pags > 2)
			$tpl->assign($block.".link_ultimo", $link.$q->pags[$k]);
		else
			$tpl->assign($block.".link_ultimo", "javascript:void(null)");
		
		if(isset($q->voltar))
			$tpl->assign($block.".link_voltar", $link.$q->voltar);
		else
			$tpl->assign($block.".link_voltar", "javascript:void(null)");
		
		if(isset($q->avancar))
			$tpl->assign($block.".link_avancar", $link.$q->avancar);
		else
			$tpl->assign($block.".link_avancar", "javascript:void(null)");
		
		$tpl->gotoBlock('_ROOT');
	}

/* 

	GERAL - Funções com proósitos diversos
	
	go_area						Vai para a área desejada

*/

#\_ Retorna texto com destaque da busca
function prepare_busca_destacado($str, $words, $len=NULL)
	{
		if($len != NULL)
			{
				$end = 0;
				$first = 100000000;
				$str_original = $str;
				$str_lower = strtolower($str_original);
				
				foreach($words as $p)
					{
						if(($pos = strpos($str_lower, $p)) !== false)
							{
								if($pos < $first)
									$first = $pos;
							}
					}
				
				$first = ($first - 50)>0?($first - 50):0;
				
				if($first+$len < strlen($str))
					$end = strpos($str, ' ', $first+$len)-$first;
				
				if($end<300)
					$end = 300;
				
				$str = substr($str, $first, $end);
		
				if(strlen($str) < $len)
					{
						if(strlen($str_original) < $len)
							$str = $str_original;
						else
							$str = substr($str_original, -$len);
					}
		
				if($first != 0)
					$str = '...'.$str;
				if($first+$len < strlen($str_original))
					$str = $str.'...';
			}
		
		foreach($words as $k)
			{
				$str = str_ireplace($k, '<b>'.strtoupper($k).'</b>', $str);
			}
		
		return $str;
	}
	
#\_ Retorna BuscaFull e BuscaAll para um campo de Busca Avançada
function prepare_busca_palavra($tag)
	{
		$Busca_Full = bd_escape($CFG->con, utf8_decode_once($tag));
		$Palavras = explode(' ', strtolower(bd_escape($CFG->con, utf8_decode_once($tag))) );
		
		foreach($Palavras as $k => $p)
			{
				if(strlen($p) <= 3)
					unset($Palavras[$k]);
			}
		
		$Busca_All = implode('%', $Palavras);
		array_unshift($Palavras, $Busca_Full);
		
		return array($Busca_Full, $Busca_All);
	}

#\_ Retorna uma string com zeros preenchidos
function zero_filled($number, $fills)
	{
		$str = strval($number);
		$len = $fills - strlen($str);
		$output = '';
		
		for($i=0;$i<$len;$i++)
			$output .= '0';
		
		$output .= $str;
		
		return $output;
	}

#\_ Função para informar admin do site de alguma atualização
function informAdmin($identificacao, $msg, $html=false)
	{
		global $CFG;
		
		if(!$html)
			$msg = $identificacao." ".$CFG->url_site.NL.$msg;

		mailTo( $CFG->contato, $CFG->contato, $identificacao." ".$CFG->url_site, $msg, $html);
		mailTo( $CFG->contatos, $CFG->contato, $identificacao." ".$CFG->url_site, $msg, $html);
		
		return true;
	}

#\_ getallheaders fix
if (!function_exists('getallheaders'))
	{
        function getallheaders()
        	{
	            foreach($_SERVER as $key=>$value)
	            	{
	                	if (substr($key,0,5)=="HTTP_")
	                		{
	                    		$key = str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
	                    		$out[$key]=$value;
	                    	}
	                    else
	                    	{
	                    		$out[$key]=$value;
	                    	}
	            	}
	            
	            return $out;
	        }
	}

#\_ Otimiza carregamento da página
function optimize($str)
	{
		ke_fix_http_302($str);
		
		return $str;
	}

#\_ Força CACHE de $days dias
function ke_header_cache($days, $lastmodified=NULL, $etag=NULL)
	{
		if($days > 0)
			{
				$expires = 86400*$days;
				@header("Pragma: public");
				@header("Cache-Control: max-age=".$expires.", public");
				@header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
			}
		else
			{
				@header("Pragma: public");
				@header("Cache-Control: no-cache, must-revalidate");
				@header("Expires: Thu, 23 Oct 2003 20:00:00 GMT");
			}
		
		if(!empty($lastmodified))
			{
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					{
						if(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified)
							{
								@header('HTTP/1.0 304 Not Modified');
				    			exit;
							}
					}
				
				@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastmodified).' GMT');
			}
		
		if(!empty($etag))
			{
				
			}
	}

#\ PHP BUG - array_merge reindexing - fix
function array_merge_safe()
    {
        $output = array();
        
        if(func_num_args() > 0)
            {
                $rays = func_get_args();
                
                foreach($rays as $r)
                    {
                        foreach($r as $k => $e)
                            {
                                $output[$k] = $e;
                            }
                    }
            }
        
        return $output;
    }

/**
 * Função que retorna um Array a partir de uma String CSV.
 * @param string $csv Dados em CSV
 * @param bool $index Define se índices serão processados. Padrão: true.
 * @return array Array no formato específico.
 */
 function array_from_csv($csv, $index=true)
 	{
		$ret = explode(',', $csv);
		
		if($index)
			{
				$itens = $ret;
				
				$ret = array();
				
				foreach($itens as $item)
					{
						if(strpos($item, ':') !== false)
							{
								$item = explode(':', $item);
								
								$k = array_shift($item);
								
								if(count($item) == 0)
									{
										$item = NULL;
									}
								elseif(count($item) == 1)
									{
										$item = $item[0];
									}
								else
									{
										$item = implode(':', $item);
									}
								
								$ret[$k] = $item;
							}
						else
							{
								$ret[$item] = $item;
							}
					}
			}
			
		return $ret;
	}

function array_to_csv($ray, $index=true)
	{
		$ret = array();
		
		foreach($ray as $k => $e)
			{				
				$ret[] = $index ? $k.':'.$e : $e;
			}
		
		$ret = implode(',', $ret);
		
		return $ret;
	}

/** 
 * retorna primeiro item encontrado do $needle em $haystack
 * @param array $needle
 * @param mixed array or csv $haystack
 * @return int 
 */
function some_in_array($needle, $haystack)
	{
        $haystack_keys = array_keys($haystack);
        
		foreach($needle as $n)
			{
                if(in_array($n, $haystack))
                    return $n;
                
				if(in_array($n, $haystack_keys))
					return $n;
			}
		
		return 0;
	}

// funcao que retorna array com todos filhos de chave 
// elementos devem ser arrays com chave 'id_parent'
function get_allchildren($array, $key=0, $include_key=false, $levels=10)
	{
		$children = array();
		
		if(is_array($array))
			{
				foreach($array as $k => $v)
					{
						if($v['id_parent'] == $key or ($include_key and $k == $key) )
							{
								$children[$k] = $v;
							}
					}
			}
		
		if(empty($children))
			{
				return array();
			}
		
        
		if($levels > 0)
			{
				foreach($children as $ckey => $cval)
					{
						$children = array_merge_safe($children, get_allchildren($array, $ckey, false, $levels--));
					}
			}
		
		return $children;
	}

// pega secao e pais até raiz
function get_secao_branch($id, $ray=null)
	{
		global $CFG;

        if( $ray === null ) $ray = $CFG->secoes_ray;
        
		if( empty($ray[$id]) ) return array();
		
		$ret = array($id => $ray[$id]);

		if($ray[$id]['id_parent'] > 0)
			{
				$parents = get_secao_branch( $ray[$id]['id_parent'], $ray );
				
				$ret = array_merge_safe($parents, $ret);
			}
		
		return $ret;
	}

#\_ Função para otimização HTTP, 302 fixes
function ke_fix_http_302(&$str)
	{
		global $CFG;
		
		$str = str_replace(' hover="img/', ' hover="'.$CFG->url_tpl.'img/', $str);
		$str = str_replace(' hover="/img/', ' hover="'.$CFG->url_tpl.'img/', $str);
		$str = str_replace(' src="img/', ' src="'.$CFG->url_tpl.'img/', $str);
		$str = str_replace(' src="/img/', ' src="'.$CFG->url_tpl.'img/', $str);
		$str = str_replace(' src="js/', ' src="'.$CFG->url_site.'js/', $str);
		$str = str_replace(' src="/js/', ' src="'.$CFG->url_site.'js/', $str);
		$str = str_replace(' src="swf/', ' src="'.$CFG->url_tpl.'swf/', $str);
		$str = str_replace(' src="/swf/', ' src="'.$CFG->url_tpl.'swf/', $str);
		$str = str_replace(' value="swf/', ' value="'.$CFG->url_tpl.'swf/', $str);
		$str = str_replace(' value="/swf/', ' value="'.$CFG->url_tpl.'swf/', $str);
		$str = str_replace(' href="css/', ' href="'.$CFG->url_tpl.'css/', $str);
		$str = str_replace(' href="/css/', ' href="'.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(../css/', 'url('.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(/css/', 'url('.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(css/', 'url('.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(\'../css/', 'url(\''.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(\'/css/', 'url(\''.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(\'css/', 'url(\''.$CFG->url_tpl.'css/', $str);
		$str = str_replace('url(../img/', 'url('.$CFG->url_tpl.'img/', $str);
		$str = str_replace('url(/img/', 'url('.$CFG->url_tpl.'img/', $str);
		$str = str_replace('url(img/', 'url('.$CFG->url_tpl.'img/', $str);
		$str = str_replace('url(\'../img/', 'url(\''.$CFG->url_tpl.'img/', $str);
		$str = str_replace('url(\'/img/', 'url(\''.$CFG->url_tpl.'img/', $str);
		$str = str_replace('url(\'img/', 'url(\''.$CFG->url_tpl.'img/', $str);
	}

/**
 * Função que retorna uma string query SQL condicional a partir
 * de uma string CSV que contenha.
 * @param string $campo Campo CSV para comparação
 * @param string $k Dados em CSV, ou chave simples
 * @return string SQL query string parcial. " ( CONDITIONS ) "
 */
function csv_filter_query($campo, $k)
	{
		if(strpos($k, ',') !== false)
			{
				$keys = explode(',', $k);
				$q = array();
				
				foreach($keys as $k)
					{
						$q[] .= csv_filter_query($campo, $k);
					}
				
				return ' ( '.implode(' OR ', $q).' ) ';
			}
		
		return " (".$campo." = '$k' OR ".$campo." LIKE '%,".$k.",%' OR ".$campo." LIKE '%,".$k."' OR ".$campo." LIKE '".$k.",%') ";
	}

##########################################################################*/

/* 
	CONTROLE DE ERRO E EXIBIÇÃO
	
	throw_404						Devolve erro 404 ao usuário

*/

function throw_404()
	{
		header("HTTP/1.0 404 Not Found");
		echo "Erro 404";
		exit;
	}

function throw_403()
	{
		header("HTTP/1.0 403 Access Denied");
		echo "Erro 403";
		exit;
	}

##########################################################################*/

/*

	OLD STUFF
	
*/

#\_ Função que retorna um link para a própria página com vars get adicionais
function go_area($area, $adt = "")
	{
		global $CFG;
		
		if(is_int($area))
			$area = $CFG->areas[$area];

		return(ke_link($CFG->url_sit.$area, $adt));
	}

#|_ Funcao q adiciona um email a maillist
function addMail($nome, $email, $cidade='', $estado='', $telefone='')
	{
		global $CFG;
		$q = bd_executa("SELECT * FROM maillist WHERE email = '".bd_escape($CFG->con, $email)."'", $CFG->con);

		if($q->nada)
			{
				bd_executa("INSERT INTO maillist (nome, email, cidade, estado, telefone) 
									VALUES ('".bd_escape($CFG->con, $nome)."', '".bd_escape($CFG->con, $email)."', '".bd_escape($CFG->con, $cidade)."', '".bd_escape($CFG->con, $estado)."', '".bd_escape($CFG->con, $telefone)."')", $CFG->con);
			}
	}

#|_ Função q adiciona um email a maillist
function remMail($id, $email)
	{
		global $CFG;
		
		$q = bd_executa("DELETE FROM `maillist` WHERE email = '$email' AND id = '$id'", $CFG->con);
	}

#\_ Envio de email
function mailTo($to, $from, $subject, $message, $format='html', $reply_to=NULL)
	{
		global $CFG;
		
		return mail_smtp($CFG->smtp_local, $CFG->smtp_user, $CFG->smtp_pass, $from, '', $to, $subject, $message, $is_html=($format=='html'), $reply_to);
	}

#\_ Url Amigável
function url_simple($str)
	{
		$str = trim($str);
		
		$v = '--------aaaaeeiooouucAAAAEEIOOOUUC--';
		
		$k = ' &[](){}áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ:,';
		
		if(!is_utf8($str))
			{
				$k = utf8_decode($k);
			}
		
		$str = strtr($str, $k, $v);
		
		while(strpos($str, '--') !== false)
			{
				$str = str_replace('--', '-', $str);
			}
		
		$len = strlen($str);
		
		$r = '';
		
		for($i=0;$i<$len;$i++)
			{
				$ascii_number = ord($str{$i});
				
				if($ascii_number < 43) // < +
					continue;
				
				if($ascii_number > 43 and $ascii_number < 45) // > + < -
					continue;
				
				if($ascii_number > 45 and $ascii_number < 48) // > - < 0
					continue;
				
				if($ascii_number > 57 and $ascii_number < 65) // > 9 < A
					continue;
				
				if($ascii_number > 90 and $ascii_number < 97) // > Z < a
					continue;
				
				if($ascii_number > 122) // > z
					continue;
				
				$r .= $str{$i};
			}
		
		return $r;
	}

#\_ Somente Números
function numbers_only($str)
	{
		$k = array( ' ', '.', ',', '-', '/', '_', ':', '|', ';', '(', ')');
		$v = array( '' , '' , '' , '' , '' , '' , '' , '' , '' , ''  , '' );
		
		$str = str_replace($k, $v, utf8_decode_once($str) );
		
		return $str;
	}

// link voltar
function link_voltar()
	{
		global $CFG;

		if(isset($_SERVER['HTTP_REFERER']))
			{
				if(strpos($_SERVER['HTTP_REFERER'], $CFG->url_site) !== false)
					return $_SERVER['HTTP_REFERER'];
			}
		
		return $CFG->url_site;
	}

#\_ Função reportar erros
function report($txt)
	{
		global $CFG;
		$CFG->erros .= " - <h2><b>Erro Fatal, o processamento do script não pode ser concluído!!!</b></h2><br /><h4>&nbsp;&nbsp;&nbsp;".$txt."</h4>";
	}

#\_ Função para ler arquivos
function ke_head($url)
	{
		global $CFG;
		
		header("Location: ".$url);
		
		if($CFG->debug)
			{
				echo '<pre>';
			    debug_print_backtrace();
			}
			
		exit;
	}

#\_ Função montar um link com suporte a sessão sem cookies
function ke_link($url, $get = "", $acceptoldget = false)
	{
		$pos = strpos($url, "#");
		if(!empty($pos))
			$url = substr($url, 0, $pos);
		$pos = strpos($url,"?");
		if(empty($pos))
			$link = $url;
		else
			{
				$link = substr($url, 0, $pos);
				if($acceptoldget)
					$parm = substr($url, ++$pos);
			}
		if(empty($parm))
			$parm  = $get;
		else
			$parm .= "&".$get;

		/*if(@constant("SID"))
			{
				if(!empty($parm))
					$parm .= "&".SID;
				else
					$parm = SID;
			}*/

		if(!empty($parm))
			return $link."/".$parm;
		else
			return $link;					
	}

#\_ Função que formata a data vinda do banco de dados
function formata_data($data)
	{
		$data = @explode(" ", $data);
		$hora = @explode(":", $data[1]);
		$data = @explode("-", $data[0]);
		
		$data1 = $data[2]."/".$data[1]."/".$data[0];
		$data2 = $data[2]."/".$data[1];
		
		return array("data" => $data1, "hora" => $hora, "data2" => $data2);
	}

#\_ Função que formata a data vinda do banco de dados em forma de texto
function texto_data($data)
	{
		$data = @explode(" ", $data);
		$hora = @explode(":", $data[1]);
		$data = @explode("-", $data[0]);
		
		$mes_nomes = array('01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Maio', '05' => 'Abril', '06' => 'Junho',
					'07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
		return array('mes' => $mes_nomes[$data[1]]);
	}

#\_ Função que retorna um link para a própria página com vars get adicionais
function goIn($adt = "")
	{
		return(ke_link($CFG->url_sit.$adt, '', true));
	}

#\_ Função q retorna um array do bd
function rayFromBd($chave, $valor, $tabela, $con, $param = '')
	{
		global $BD;

		$res = $BD->bd_executa("SELECT $chave, $valor FROM $tabela $param", $con);

		if($res->nada)
			return false;

		foreach($res->res as $key)
			{
				$r[$key->$chave] = $key->$valor;
			}
		
		return $r;
	}
#\_ Função para efetuar download de arquivo ou string
function ke_download_2($file, $name=false, $isfile=true)
	{
		if(!$name)
			$name = basename($file);
		if(!$isfile)
			{
				$content = $file;
				
				$file_type = 'application/octet-stream';
				$file_size = strlen($content);
				$file_mtime = date('D, d M Y H:i:s \G\M\T');
			}
		else
			{
				$file_type = MIME::type($file);
				$file_size = @filesize($file);
				$file_mtime = @filemtime($file);
			}
		
		ke_header_cache($CFG->control_cache_days, $file_mtime);
		
		@header('Content-Type: '.$file_type);
		@header('Content-Length: '.$file_size);
		
		if(!$isfile)
			die($content);
		else
			{
				$fh = @fopen($file, 'r');
				while(!@feof($fh) and !connection_aborted())
					{
						echo @fread($fh, 2048);
						@ob_flush();
						@flush();
						@usleep(50000);
					}
				
				@fclose($fh);
				exit;
			}
	}

#\_ Função para efetuar download de arquivo ou string
function ke_download($file, $name=false, $isfile=true)
	{
		if(!$name)
			$name = basename($file);
		if(!$isfile)
			{
				$content = $file;
				$size = strlen($content);
			}
		else
			$size = filesize($file);
		
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T'));
		header('Content-Length: '.$size);
		
		if(!$isfile)
			die($content);
		else
			{
				$fh = fopen($file, 'r');
				while(!feof($fh))
					{
						echo fgets($fh, 2048);
						flush();
					}
				
				exit;
			}
	}

#\_ FUNÇÃO QUE CONSERTA O ENCODING DO $_POST
function fix_post_encoding(&$arr=NULL)
	{
		if(is_null($arr))
			{
				$arr = &$_POST;
			}

		foreach($arr as $k => $v)
			{
				if(is_string($v))
					{
						if(is_utf8($v))
							$arr[$k] = utf8_decode($v);
					}
				elseif(is_array($v))
					{
						fix_post_encoding($arr[$k]);
					}
			}
	}

#\_ FUNÇÃO PARA EVITAR UTF8 ENCODING DUPLO
function utf8_encode_once($s)
	{
		if(!is_utf8($s)) {
			$s = utf8_encode($s);
		}		
		return $s;
	}
function utf8_decode_once($s)
	{
		if(is_array($s))
			{
				foreach($s as $k => $e)
					{
						$s[$k] = utf8_decode_once($e);
					}
				
				return $s;
			}
		
		if(is_utf8($s)) {
			$s = utf8_decode($s);
		}		
		return $s;
	}

function is_utf8($str)
	{
		if(function_exists('mb_detect_encoding'))
			{
				return ('UTF-8' == mb_detect_encoding($str, 'UTF-8', true));
			}
			
		if(function_exists('mb_convert_encoding'))
			{
				return ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"));
			}
		
		return (utf8_encode(utf8_decode($str)) == $str)?true:false;
	}

function compare_str($str1, $str2)
	{
		$len1 = strlen($str1);
		$len2 = strlen($str2);
		
		$len = $len1 > $len2 ? $len1 : $len2;
		
		$output1 = '';
		$output2 = '';
		
		$diff = 0;
		
		for($i=0;$i<$len;$i++)
			{
				$c = $i;
				
				if($str1{$i} != $str2{$i})
					{
						$output1 .= '<b>'.htmlentities(utf8_encode_once($str1{$i})).'</b>';
						$output2 .= '<b>'.htmlentities(utf8_encode_once($str2{($i+$diff)})).'</b>';
					}
				else
					{
						$output1 .= htmlentities(utf8_encode_once($str1{$i}));
						$output2 .= htmlentities(utf8_encode_once($str2{$i}));
					}
			}
		
		echo '<div style="overflow:hidden;clear:both;"><div style="float:left;width:50%;margin:0;padding:0;">'.$output1.'</div><div style="float:left;width:50%;margin:0;padding:0;">'.$output2.'</div></div>';
		exit;
	}

function unicode_entity($str)
	{
		$str = (string) $str;
		
		$output = "";
		$lenght = strlen ($str);
		
		for ($position = 0; $position < $lenght; $position++)
			{
				$Char = $str [$position];
				$AsciiChar = ord ($Char);
			
				if($AsciiChar < 128)
					{
						$output .= $Char;
					}
				elseif($AsciiChar >> 5 == 6)
					{
						$FirstByte = ($AsciiChar & 31);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$SecondByte = ($AsciiChar & 63);
						$AsciiChar = ($FirstByte * 64) + $SecondByte;
						$Entity = sprintf ("&#%d;", $AsciiChar);
						$output .= $Entity;
					}
				elseif($AsciiChar >> 4 == 14)
					{
						$FirstByte = ($AsciiChar & 31);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$SecondByte = ($AsciiChar & 63);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$ThidrByte = ($AsciiChar & 63);
						$AsciiChar = ((($FirstByte * 64) + $SecondByte) * 64) + $ThidrByte;
						
						$Entity = sprintf ("&#%d;", $AsciiChar);
						$output .= $Entity;
					}
				elseif($AsciiChar >> 3 == 30)
					{
						$FirstByte = ($AsciiChar & 31);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$SecondByte = ($AsciiChar & 63);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$ThidrByte = ($AsciiChar & 63);
						$position++;
						$Char = $str [$position];
						$AsciiChar = ord ($Char);
						$FourthByte = ($AsciiChar & 63);
						$AsciiChar = ((((($FirstByte * 64) + $SecondByte) * 64) + $ThidrByte) * 64) + $FourthByte;
						
						$Entity = sprintf ("&#%d;", $AsciiChar);
						$output .= $Entity;
					}
			}
		
		return $output;
	}

#\_ Função para retornar Javascript saudável
function safe_javascript($str)
	{
		$offset = 0;
		
		do {
			$pos = strpos($str, '<script', $offset);
			$offset = $pos;
			
			if($pos !== false) {
				$ini = strpos($str, '>', $pos);
				$end = strpos($str, '</script', $pos);
				
				if($ini !== false and $end !== false) {
					$safe = substr($str, $ini+1, $end-($ini+1));
					$safe = html_entity_decode($safe, ENT_NOQUOTES, 'UTF-8');
					$str = substr($str, 0, $ini+1).$safe.substr($str, $end);
					$offset = $end;
				}
			}
				
		} while($pos !== false);
		
		return $str;
	}

#\_ Função para Manipulação de Datas
function ke_data($days, $date = 0, $fmt = "Y-m-d")
	{
		if ($date == 0)
			$t1 = time();
		else
			{
				if(strpos($date, '/') !== false)
					{
						$date = explode('/', $date);
						$t1 = strtotime($date[2].'-'.$date[1].'-'.$date[0]);
					}
				else
					$t1 = strtotime($date);
			}

		$t2 = $days * 86400; // make days to seconds

		return date($fmt, ($t2 + $t1));
	}

#\_ base64 url safe
function base64_urlencode($str)
	{
		$str = base64_encode($str);
		$str = strtr($str, '+/', '-_');
		return $str;   
	}
function base64_urldecode($str)
	{
		$str = strtr($str, '-_', '+/');
		$str = base64_decode($str);
		return $str;   
	}

#\_ Função para controle de erros
function handleError ($errno, $errmsg, $filename, $linenum, $vars) 
	{
		global $CFG;
		
		if(!empty($CFG->debug))
			{	
				$types = array (
							E_ERROR           => "Error",
							E_WARNING         => "Warning",
							E_PARSE           => "Parsing Error",
							E_NOTICE          => "Notice",
							E_CORE_ERROR      => "Core Error",
							E_CORE_WARNING    => "Core Warning",
							E_COMPILE_ERROR   => "Compile Error",
							E_COMPILE_WARNING => "Compile Warning",
							E_USER_ERROR      => "User Error",
							E_USER_WARNING    => "User Warning",
							E_USER_NOTICE     => "User Notice",
							E_STRICT          => "Runtime Notice"
							);
			
				$errtype = 'Unknown Error';
				
				if(isset($types[$errno]))
					$errtype = $types[$errno];
				
				$msg = $errtype.' -> '.$errmsg.' no arquivo '.$filename.' na linha '.$linenum."\n";
				
				$CFG->erros .= $msg;
				
				if(defined('DEBUG'))
					{
						if(DEBUG)
							{
								echo $msg;
								flush();
								usleep(1);
							}
					}
			}
	}
set_error_handler("handleError");

#\_ Função para pegar os usuários online, baseado no ip
function getUsersOnline($con, $sufixo='')
	{
		global $CFG;
		
		// hora atual
		$timestamp = time();
		$timeout = 300;
		// pega o ip
		if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		}
		elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// querys
		bd_executa("DELETE FROM usersonline WHERE ip = '".$ip."' OR timestamp = '".$timestamp."'", $con);
		bd_executa("INSERT INTO usersonline (timestamp, ip) VALUES ('".$timestamp."', '".$ip."')", $con);
		bd_executa("DELETE FROM usersonline WHERE timestamp < ".($timestamp - $timeout), $con);
		// total
		$q = bd_executa("SELECT COUNT(*) AS num FROM usersonline", $con);
		
		$count = $q->res->rid0->num;
		
		if(isset($CFG->users_online_adt))
			{
				$count += $CFG->users_online_adt;
			}
		
		if($sufixo!='')
			{
				if($count > 1)
					$count .= ' '.$sufixo.'s';
				else
					$count .= ' '.$sufixo;
			}
		
		return $count;
	}

#\_ Função para formatar string de BD para html
function format_caption_str($str, $on_empty=NULL)
	{
		if(empty($str))
			$str = $on_empty;
		
		$str = nl2br($str);
		
		return $str;
	}

function processBanners($html)
	{
		global $CFG;

		if(!isset($CFG->processBanners_ids_taken))
			$CFG->processBanners_ids_taken = array();

		$prefixo = '<!-- BANNERS : ';
		$sufixo = ' -->';
		$ray_1 = array();
		$ray_2 = array();
		$ad = 0;
		$last_pos = 0;
		$int_pos = 0;
		$suf_pos = 0;
		$end_pos = 0;
		$norepeat = '1';

		// Banners por secoes
		while(($last_pos = strpos($html, $prefixo, $last_pos)) !== false)
			{
				$int_pos = $last_pos + strlen($prefixo);
				$suf_pos = strpos($html, $sufixo, $last_pos);
				$ad = intval(substr($html, $int_pos, $suf_pos));

				foreach($CFG->processBanners_ids_taken as $e)
					{
						$norepeat .= ' AND b.id <> '.$e;
					}
				unset($e);

				$sql = "SELECT b.*, s.size_w, s.size_h, s.tipo, a.arquivo 
							FROM banners AS b, banners_secoes AS s, rel AS r, arquivos AS a
								WHERE b.situacao = '1'
									AND b.data_ini < NOW()
									AND b.data_end > NOW()
									AND r.um = 'banners'
									AND r.dois = 'arq_galerias'
									AND r.id_um = b.id
									AND r.id_dois = a.id_mama
									AND a.status = '1'
									AND s.id = '$ad'
									AND (b.secoes = '$ad' 
										OR b.secoes LIKE '$ad,%' 
										OR b.secoes LIKE '%,$ad' 
										OR b.secoes LIKE '%,$ad,%')
									AND (".$norepeat.")
								ORDER BY b.destaque DESC";

				if(empty($CFG->banners_not_random))
					$sql .= ", RAND()";

				$q = bd_executa($sql, $CFG->con);

				$end_pos = $suf_pos + strlen($sufixo);
				
				if($q->nada)
					{
						$html = substr($html, 0, $last_pos).substr($html, $end_pos);
						continue;
					}
				
				$ret = '';
				
				foreach($q->res as $e)
					{
						$CFG->processBanners_ids_taken[] = $e->id;

						bd_executa("UPDATE banners SET vis = vis + 1 WHERE id = '".$e->id."'", $CFG->con);

						$adt = '';
						
						/*if(!empty($e->size_h))
							{
								$adt .= 'height="'.$e->size_h.'"';
							}
						if(!empty($e->size_w))
							{
								$adt .= 'width="'.$e->size_w.'"';
							}*/
						
						if($e->tipo == '0')
							{
								$code = '<img src="'.$CFG->url_files.$e->arquivo.'" '.$adt.' border="0" />';

								if(!empty($e->href))
									{
										if(substr($e->href, 0, strlen($CFG->url_sit)) != $CFG->url_sit) // dominio externo
											{
												$e->href .= '" target="_blank';
											}
										
										$code = '<a href="'.$e->href.'">'.$code.'</a>';
									}
							}
						elseif($e->tipo == '1')
							{
								$code = '<img src="'.$CFG->url_files.$e->arquivo.'" '.$adt.' border="0" />';

								if(!empty($e->href))
									{
										if(substr($e->href, 0, strlen($CFG->url_sit)) != $CFG->url_sit) // dominio externo
											{
												$e->href .= '" target="_blank';
											}
										
										$code = '<a href="'.$e->href.'">'.$code.'</a>';
									}

								$code = '<div class="page">'.$code.'</div>';
							}

						$ret .= $code.NL;
					} /**/

				$html = substr($html, 0, $last_pos).($ret).substr($html, $end_pos);
				
				$last_pos = $end_pos;
			}
		
		return $html;
	}

function processImgDetails_callback($matches)
	{
		global $CFG;

		$html_img = $matches[0];

		// img from galeria
		preg_match("/\/z1img\/(.*?).jpg/", $html_img, $img);
		$img = substr($img[0], 7);

		$sql = "SELECT autor, descricao FROM bd_img WHERE img = '".$img."'";

		$q = bd_executa($sql, $CFG->con);

		if(!$q->nada)
			{
				// inicia processo
				$i = $q->res->rid0;
				
				$width = '';
				$adt = '';
				
				// img width to div
				preg_match("/(?<!-)width:(.*?);/", $html_img, $width);
				
				if(!empty($width))
					{
						$width = $width[0];
					}
				
				// img margin to div
				preg_match("/margin:(.*?);/", $html_img, $margin);
				
				if(!empty($margin))
					{
						$adt .= $margin[0];
					}
				
				// img float from align to div
				preg_match("/ align=\"(.*?)\" /", $html_img, $float);
				
				if(!empty($float))
					{
						$adt .= 'float: '.$float[1].';';
					}
				
				// img float to div
				preg_match("/float:(.*?);/", $html_img, $float);
				
				if(!empty($float))
					{
						$adt .= $float[0];
					}
				
				// descricao
				if(!empty($i->descricao))
					{
						$html_img .= '<span class="descricao" style="'.$width.$adt.'">'.$i->descricao.'</span>';
					}

				// autor
				if(!empty($i->autor))
					{
						$html_img .= '<span class="autor" style="'.$width.$adt.'">Foto: '.$i->autor.'</span>';
					}

				$html_img = '<span class="TextoImgDetails" style="'.$adt.'">'.$html_img.'</span>';
				//$CFG->erros .= nl2br(NL.NL.'RESULT : '.htmlspecialchars($html_img).' ----- END IMAGE ');
			}

		return $html_img;
	}

function processImgDetails($html)
	{
		return preg_replace_callback("/\<img (.*?)\>/is", 'processImgDetails_callback', $html);
	}

function head_opengraph(&$tpl)
    {
        global $CFG;
        
        $tpl->newBlock('head_opengraph');
            $tpl->assign( $CFG->head_opengraph );
        
        $image_size = getimagesize($CFG->head_opengraph['image_bydir']);
        
        if($image_size !== false)
            {
                $tpl->newBlock('head_opengraph_imagesize');
                    $tpl->assign('image_width', $image_size[0] );
                    $tpl->assign('image_height', $image_size[1] );
            }

    }

/**
 * Função de integração com o Moodle
 */
function MoodleUser($user)
	{
		global $CFG;
		
		$cliente = bd_executa("SELECT * FROM clientes WHERE id='".$user."'", $CFG->con);	
		
		if($cliente->nada)
			return false;
		
		$cliente = $cliente->res->rid0;
		
		$cliente->sobrenome = explode(' ', $cliente->nome);
		$cliente->nome = array_shift($cliente->sobrenome);
		$cliente->sobrenome = implode(' ', $cliente->sobrenome);
		$cliente->pais = 'BR';
		
		if(empty($cliente->sobrenome))
			$cliente->sobrenome = ' ';
		
		$cliente->nome = mysql_escape_string($cliente->nome);
		$cliente->sobrenome = mysql_escape_string($cliente->sobrenome);
		$cliente->cidade = mysql_escape_string($cliente->cidade);
		
		$q = bd_executa("SELECT * FROM `zzead_user` WHERE `id` = '".$cliente->moodleuser."'", $CFG->con);
		
		if($q->nada)
			{
				$verify = bd_executa("SELECT * FROM `zzead_user` WHERE `username` = '".$cliente->email."'", $CFG->con);
				
				$cliente->username = $cliente->email;
				
				if(!$verify->nada)
					{
						$cliente->username = $cliente->email.'|'.$cliente->cpf.'|'.$cliente->cnpj;
					}
		
				$iid = bd_executa("INSERT INTO `zzead_user` 
								(`auth`, `confirmed`, `policyagreed`, `deleted`, `suspended`, `mnethostid`, `username`, `password`, `idnumber`, `firstname`, `lastname`, `email`, 
								`city`, `country`, `lang`, `timezone`, `maildisplay`, `autosubscribe`, `trustbitmask`, `description`) 
							VALUES
								('manual', 1, 0, 0, 0, 1, '".$cliente->username."', '".md5($cliente->senha)."', '".$cliente->id."', '".$cliente->nome."', '".$cliente->sobrenome."', '".$cliente->email."', 
								'".$cliente->cidade."', '".strtoupper($cliente->pais)."', 'pt_br', '99', 2, 1, 0, 'Aluno');", $CFG->con);
				
				bd_executa("UPDATE `clientes` SET `moodleuser` = '".$iid."' WHERE `id` = '".$cliente->id."'", $CFG->con);
			
				$q = bd_executa("SELECT * FROM `zzead_user` WHERE `id` = '".$iid."'", $CFG->con);
				
				if($q->nada)
					return false;
				
				$moodle = $q->res->rid0;
			}
		else
			{
				$moodle = $q->res->rid0;
				
				if($moodle->idnumber != $cliente->id)
					return false;
				
				if($cliente->modify_date != '' and $cliente->modify_date != '0000-00-00' and $cliente->modify_date != '0000-00-00 00:00:00')
					{
						if($moodle->timemodified + 60 < strtotime($cliente->modify_date))
							{
								bd_executa("UPDATE `zzead_user` 
												SET `username` = '".$cliente->email."', `password` = '".md5($cliente->senha)."', 
													`firstname` = '".$cliente->nome."', `lastname` = '".$cliente->sobrenome."', 
													`email` = '".$cliente->email."', `city` = '".$cliente->cidade."', 
													`country` = '".strtoupper($cliente->pais)."', `idnumber` = '".$cliente->id."',
													timemodified = '".strtotime($cliente->modify_date)."'
												WHERE id = '".$cliente->moodleuser."'", $CFG->con);
							}
					}
			}
			
		return $moodle;
	}

function print_debug_window($str, $et=NULL)
	{
		$str = str_replace("\r", "", $str);
		$errs = explode("\n", $str);
		
		$output = '<script type="text/javascript">try { 
						console.log("IP: '.$_SERVER['REMOTE_ADDR'].'");';
		
		foreach($errs as $err)
			{
				if(!empty($err))
					{
						$output .= 'console.log("Erros('.(++$i).'): '.addslashes($err).'");';
					}
			}
			
		$output.=	'	console.log("Tempo de execução: : '.$et.'");
					} catch(err) { } </script>';
		
		return $output;
	}

// MIME type
class MIME {
	private static $initiated = false;
	public static $mime_types = NULL;
	
	static function type( $filename = '' )
		{
			$ext = NULL;
			
			if(substr($filename, -4, 1) == '.')
				$ext = substr($filename, -3);
			elseif(substr($filename, -5, 1) == '.')
				$ext = substr($filename, -4);
			
			if( $ext )
				{				
					MIME::init();
					
					if( isset( MIME::$mime_types[$ext] ) )
						{
							return MIME::$mime_types[$ext];
						}
				}
				
			return 'application/octet-stream'; //if no ext, return generic binary stream
		}
		
	static function init()
		{
			if( MIME::$initiated )
				return;
				
			MIME::$mime_types = array (
				'txt' => 'text/plain',
				'html' => 'text/html',
				'htm'	=> 'text/html',
				'php' => 'text/plain',
				'css' => 'text/css',
				'js'	=> 'application/x-javascript',
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'tif' => 'image/tiff',
				'tiff'	=> 'image/tiff',
				'doc' => 'application/msword',
				'docx'	=> 'application/msword',
				'xls' => 'application/excel',
				'xlsx'	=> 'application/excel',
				'ppt' => 'application/powerpoint',
				'pptx' => 'application/powerpoint',
				'pdf'	=> 'application/pdf',
				'wmv' => 'application/octet-stream',
				'mpg' => 'video/mpeg',
				'mov' => 'video/quicktime',
				'mp4' => 'video/quicktime',
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'dmg' => 'application/x-apple-diskimage',
				'exe'	=> 'application/octet-stream'
			);
			
			MIME::$initiated = true;
		}
}

##########################################################################*/

/*

	DEFINES

*/

if(!defined('NL'))
	define('NL', "\r\n");
if(!defined('TAB'))
	define('TAB', "\t");

$CFG->vars = (object) array();

$CFG->vars->ufs = array(
	'AC'=>'AC', 'AL'=>'AL', 'AM'=>'AM', 'AP'=>'AP', 'BA'=>'BA', 'CE'=>'CE', 'DF'=>'DF', 'ES'=>'ES', 'GO'=>'GO', 
	'MA'=>'MA', 'MG'=>'MG', 'MS'=>'MS', 'MT'=>'MT', 'PA'=>'PA', 'PB'=>'PB', 'PE'=>'PE', 'PI'=>'PI', 'PR'=>'PR', 'RJ'=>'RJ', 
	'RN'=>'RN', 'RO'=>'RO', 'RR'=>'RR', 'RS'=>'RS', 'SC'=>'SC', 'SE'=>'SE', 'SP'=>'SP', 'TO'=>'TO'
);

$CFG->vars->ufs_f = array_flip($CFG->vars->ufs);

$CFG->vars->ufs_estado = array(
	'AC'=>'Acre', 'AL'=>'Alagoas', 'AP'=>'Amapá', 'AM'=>'Amazonas', 'BA'=>'Bahia', 'CE'=>'Ceará', 'DF'=>'Distrito Federal', 
	'ES'=>'Espírito Santo', 'GO'=>'Goiás', 'MA'=>'Maranhão', 'MT'=>'Mato Grosso', 'MS'=>'Mato Grosso do Sul', 
	'MG'=>'Minas Gerais', 'PA'=>'Pará', 'PB'=>'Paraíba', 'PR'=>'Paraná', 'PE'=>'Pernambuco', 'PI'=>'Piauí', 
	'RJ'=>'Rio de Janeiro', 'RN'=>'Rio Grande do Norte', 'RS'=>'Rio Grande do Sul', 'RO'=>'Rondônia', 'RR'=>'Roraima', 
	'SC'=>'Santa Catarina', 'SP'=>'São Paulo', 'SE'=>'Sergipe', 'TO'=>'Tocantins'
);

$CFG->vars->ufs_estado_f = array_flip($CFG->vars->ufs_estado);

$CFG->vars->simnao = array('S' => 'Sim', 'N' => 'Não');  
$CFG->vars->simnao_f = array_flip($CFG->vars->simnao);

$CFG->vars->simnao_flag = array('1' => 'Sim', '0' => 'Não');  
$CFG->vars->simnao_flag_f = array_flip($CFG->vars->simnao_flag);

##########################################################################*/
?>