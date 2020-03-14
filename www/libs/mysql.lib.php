<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

/*### Funções de Banco de Dados mySQL | bd_* | v0.5 ########################
 
 - Criação : 22/01/2007
 - Modificação : 09/10/2008

 - Descrição:
	Funções para gerenciamento do banco de dados

 - Importante:
	"BD" deve ser o objeto padrão
	"BD_MYSQL" é a classe

##### Configurando #####################################################/**/

#\_ Declarações
$GLOBALS['BD'] = new BD_MYSQL();

#\_ Definições
define('DB_MODE', 'MySQL');
define('DB_DATEFORMAT', "Y-m-d G:i:s");

if(!defined('BACKUP'))
	define('BACKUP', false);

#\_ Funções
function bd_conecta($con, $local, $user, $pass, $base) {
	return $GLOBALS['BD']->conecta($con, $local, $user, $pass, $base);
}
function bd_executa($sql, $con, $per_page=NULL, $index=NULL, $relacional=false) {
	return $GLOBALS['BD']->executa($sql, $con, $per_page, $index, $relacional);
}
function bd_ray($chave, $valor, $tabela, $con, $sql_param='') {
	return $GLOBALS['BD']->ray($chave, $valor, $tabela, $con, $sql_param);
}
function bd_random($tabela, $con, $id='id') {
	return $GLOBALS['BD']->random($tabela, $con, $id);
}
function bd_searchqry($keywords, $fields) {
	return $GLOBALS['BD']->searchqry($keywords, $fields);
}
function bd_escape($con, $str) {
	return $GLOBALS['BD']->escape($con, $str);
}
function bd_nextindex($tabela, $con) {
	return $GLOBALS['BD']->nextindex($tabela, $con);
}
function bd_ray_rel($chave, $valor, $rel, $tabela, $con, $ini_rel=0, $spacer=' ', $sql_param='') {
	return $GLOBALS['BD']->ray_rel($chave, $valor, $rel, $tabela, $con, $ini_rel, $spacer, $sql_param);
}
function bd_pause_backup() {
	return $GLOBALS['BD']->backup_enabled = false;
}
function bd_continue_backup() {
	return $GLOBALS['BD']->backup_enabled = true;
}

##### Classe ###############################################################

class BD_MYSQL {
	private $con;
	private $cons;
	
	function __construct ()
		{
			$this->backup_enabled = true; // habilita e desabilita os backups
			
			$this->con = (object) array(); // handler das conexões
			$this->cons = (object) array(); // handler das prepriedades das conexões
		}
	
	#\_ Função para proteção de chamada a bibliotecas externas - LOG
	function __log($str) // 09 10 2008
		{
			if(function_exists('log_do'))
				log_do($str);
		}
	
	#\_ Função para proteção de chamada a bibliotecas externas - REPORT
	function __report($str) // 09 10 2008
		{
			if(function_exists('report'))
				report($str);
			else
				{
					print($str);
					exit;
				}
		}

	#\_ Função para realizar múltiplas conexões no banco de dados
	function conecta($con, $local, $user, $pass, $bd)
		{
			if(isset($this->con->$con)) // conexão já existente
				{
					$this->__report("BD: A conexão $con já existe!!!".NL.
									'Não é possível sobreescrever uma conexão já existente.');
				}
			else
				{
					$this->__log('BD: Conecta -> Con -> '.$con.
									', Local -> '.$local.
									', User -> '.$user.
									', Pass -> '.$pass.
									', Base -> '.$bd);
					
					$new_link = false; // new link protection
					
					foreach($this->cons as $c) // new link check 13/06/2008
						{
							if($c->local == $local and $c->user == $user and $c->pass == $pass and $c->bd != $bd)
								$new_link = true;
						}
					
					$this->con->$con = 'off';
					
					$this->cons->{$con} = (object) array('local' => $local, 
														'user' => $user, 
														'pass' => $pass, 
														'bd' => $bd, 
														'new_link' => $new_link, 
														'status' => 'off');
				}
			
			return true;
		}
	
	#\_ Função para preparar conexão com o mysql
	private function prepare_con($con)
		{
			$obj = &$this->cons->{$con};
			
			if($obj->status == 'off')
				{
					if(!($this->con->$con = mysqli_connect($obj->local, $obj->user, $obj->pass/*, $obj->new_link*/)))
						{
							$this->__report('Não foi possível conectar.'.NL.
											'O Erro apontado foi o seguinte: '.NL.
											'     '.mysqli_error($this->con->$con));
						}
					else
						{
							if(!(mysqli_select_db($this->con->$con, $obj->bd)))
								{
									$this->__report("Não foi possível selecionar o Banco de Dados $bd.".NL.
													'O Erro apontado foi o seguinte: '.NL.
													'     '.mysqli_error($this->con->$con));
								}
							
							$obj->status = 'on';
							
							@register_shutdown_function('mysqli_close', $this->con->$con);
						}
				}
		}

	#\_ Função para executar um comando SQL, com ou sem paginação
	function executa($sql, $con, $pag=NULL, $ini=NULL, $relacional=false)
		{
			$this->__log('BD: Executa -> Sql -> '.$sql.', Con -> '.$con); // log
			
			$pag = ($pag==NULL)?'':$pag;
			$ini = ($ini==NULL)?0:$ini;
			
			$this->prepare_con($con);
			
			if(!isset($this->con->$con)) // verificação de existência
				$this->__report("BD: $con não é uma conexão válida com o Banco de Dados.");
			else
				{	
					$ret = (object) array(); // buffer do retorno
					$ret->res = (object) array(); // buffer do retorno de resposta
					$ret->nada = false; // buffer do retorno de resposta
				
					if($pag == '') # sem paginação
						{
							// Backup de dados alterados e/ou removidos - 2008 10 08
							$this->do_backup($sql, $con);
							
							$qry = mysqli_query($this->con->$con, $sql);
							
							if(!$qry)
								{
									$this->__report('Não foi possível executar o seguinte pedido no Banco de Dados: '.NL.
													'     '.$sql.NL.
													'O Erro apontado foi o seguinte: '.NL.
													'     '.mysqli_error($this->con->$con));
								}
	
							if((preg_match("/INSERT/i", $sql) or preg_match("/UPDATE/i", $sql) or 
								preg_match("/DELETE/i", $sql)) and !preg_match("/SELECT/i", $sql))
									return mysqli_insert_id($this->con->$con);
	
							$lin = mysqli_num_rows($qry);
							$ret->lin = $lin;

							if($lin == 0)
								$ret->nada = true;
						}
					else # com paginação
						{
							/* RESTORE 011 */
							
							$sql = $sql.' LIMIT ' . $ini . " , " . $pag;
							$sql = $this->_treatSql($sql);

							if(!($qry = mysqli_query($this->con->$con, $sql)))
								{
									$this->__report('Não foi possível executar o seguinte pedido no Banco de Dados: '.NL.
													'     '.$sql.NL.
													'O Erro apontado foi o seguinte: '.NL.
													'     '.mysqli_error($this->con->$con));
								}

							$ret->sql = $sql;
							$lin = $this->_lins($sql, $con); // linhas da query
							$totallin = $lin;								
							$ret->totallin = $lin;
							$ret->lin = $lin;
							$ret->voltar = NULL;
							$ret->avancar = NULL;

							if($lin > 0)
								{
									if(($ini + $pag) < $lin)
										$ret->avancar = $ini + $pag;

									if(($ini - $pag) >= 0)
										$ret->voltar = $ini - $pag;		

									$pagT = intval($lin / $pag);

									if(($lin - $pagT) > 0)
										$pagT++;

									for($c = 1; $c <= $pagT; $c++)
										{
											$cont = $pag * ($c - 1);
											if($cont < $lin)
												{
													if($cont == 0)
														$cont = 'zero';

													if($cont != $ini)
														$pagTR[$c] = $cont;
													else
														$pagTR[$c] = NULL;
												}
										}

									$ret->pags = $pagTR;								
								}
							else
								$ret->nada = true;
						}

					if(isset($qry))
						{
							$i = 0;
							while($res = mysqli_fetch_object($qry))
								{
									if($relacional) // 2008-10-06
										$o = $res->key;
									else
										$o = "rid".$i++;
									
									$ret->res->$o = $res;
								}
						}							
				}

			return $ret;
		}
	
	/* RESTORE 012 */
	
	#\_ Função que retorna as linhas de determinada tabela COM CONDIÇÕES
	function _lins($sql, $con)
		{
			$q = mysqli_query($this->con->$con, "SELECT FOUND_ROWS() as i");
			$r = mysqli_fetch_object($q);

			return $r->i;
		}
	#\_ Função que retorna a sql consertada para retornar o número de linas posteriormente
	function _treatSql($sql)
		{
			$first = strpos($sql, 'SELECT')+strlen('SELECT');
			$sql = substr($sql, 0, $first).' SQL_CALC_FOUND_ROWS'.substr($sql, $first);

			return $sql;
		}		
	#\_ Função q retorna um array do bd
	function ray($chave, $valor, $tabela, $con, $param = '')
		{
			$res = bd_executa("SELECT $chave, $valor FROM $tabela $param", $con);
	
			if($res->nada)
				return array();
	
			foreach($res->res as $key)
				{
					$chav = array();
					$valo = array();

					$keys = explode(',', $chave);
					$values = explode(',', $valor);
					
					foreach($keys as $k)
						$chav[] = $key->$k;
						
					foreach($values as $k)
						$valo[] = $key->$k;

					$r[implode(' - ',$chav)] = implode(' - ',$valo);
				}
			
			return $r;
		}	
	#\_ Função q retorna um array do bd relacionado e formato
	function ray_rel($chave, $valor, $rel, $tabela, $con, $ini=0, $spacer=' ', $param = '')
		{
			$res = bd_executa("SELECT $chave, $valor FROM $tabela WHERE $rel = '$ini' $param", $con);
	
			if($res->nada)
				return array();
	
			foreach($res->res as $key)
				{
					$chave_str = array();
					$valor_str = array();

					$keys = explode(',', $chave);
					$values = explode(',', $valor);
					
					foreach($keys as $k)
						$chave_str[] = $key->$k;
						
					foreach($values as $k)
						$valor_str[] = $key->$k;
						
					$main_key = array_shift($keys);

					$r[implode(' - ',$chave_str)] = $spacer.'<!--'.$key->$main_key.'-->'.implode(' - ',$valor_str);
					
					$childs = $this->ray_rel($chave, $valor, $rel, $tabela, $con, $key->$main_key, $spacer.$spacer, $param);
					
					$r = array_merge_safe($r, $childs);
				}
			
			return $r;
		}
	#\_ Função q retorna um id randômico
	function random($tabela, $con, $chave='id')
		{
			$res = bd_executa('SELECT ( RAND() * (SELECT MAX('.$chave.') FROM '.$tabela.') ) AS id', $con);
	
			return $res->res->rid0->id;
		}
	#\_ Escapa variavel para SQL
	function escape($con, $str)
		{
			$this->prepare_con($con);
			
			return mysqli_real_escape_string($this->con->$con, $str);
		}
	// ADITION 10/06/2008
	#\_ Função q retorna uma query de busca montada com os campos pedidos
	function searchqry($k, $campos)
		{
			if($k == '')
				return '';
			
			$qry = ' AND (';
			$k = str_replace(' ', '%', $k);
			
			foreach($campos as $c)
				{
					$qry .= "$c LIKE '%$k%' OR $c LIKE '%$k' OR $c LIKE '$k%' OR ";
				}
			
			$qry = substr($qry, 0, -4).')';
			
			return $qry;
		}
	#\_ Função q retorna o próximo autoindex
	function nextindex($table, $con)
		{
			$res = mysqli_query($this->con->$con, "SHOW TABLE STATUS LIKE \"".$table."\"");
			$rows = mysqli_fetch_assoc($res);
			
			if(isset($rows['Auto_increment']))
				return $rows['Auto_increment'];
			
			return $rows['auto_increment'];
		}
	
	#\_ Função para realizar backup no serviço de backup
	function do_backup($sql, $con)
		{
			if(!$this->backup_enabled)
				return false;
			
			if(!BACKUP)
				return false;
			
			$backup = false;
			
			$mirror = false;
			
			$user = isset($_SESSION['DEFAULT']['userid'])?$_SESSION['DEFAULT']['userid']:0;
							
			$sql = trim($sql);
			
			if(empty($GLOBALS['mod']))
				return false;
			
			$sql_original = $sql;
			
			$sql = str_replace('`', '', $sql);
			
			if(substr($sql, 0, 6) == "UPDATE")
				{
					$backup = true;
					
					$mirror = true;
					
					$table = substr($sql, 7, strpos($sql, ' ', 7)-7);
					
					$backup_query = 'SELECT * FROM '.$table.
									' '.substr($sql, strpos($sql, 'WHERE'));
				}
			if(substr($sql, 0, 6) == "DELETE")
				{
					$backup = true;
					
					$mirror = true;
					
					$table = substr($sql, 12, strpos($sql, ' ', 12)-12);
					
					$backup_query = 'SELECT * '.substr($sql, 7);
				}
			if(substr($sql, 0, 6) == "INSERT")
				{
					$mirror = true;
					
					$table = substr($sql, 12, strpos($sql, ' ', 12)-12);
				}
			
			$sql = $sql_original;
			
			// espalha pelos mirrors
			if($mirror and $con == SQL_CON and defined('GLOBAL') and $table != 'global_mirror')
				{
					$qry = "INSERT INTO `global_mirror` (`data`, `sql`) VALUES (NOW(), '".base64_encode($sql)."')";
					
					bd_executa($qry, $con);
				}
			
			// salva cópia de segurança
			if($backup)
				{
					$bak = bd_executa($backup_query, $con);
					
					if(!$bak->nada)
						{
							$GLOBALS['BackupService']->send( array('usuario' => $user,
																	'tabela' => $table,
																	'data' => serialize($bak->res)) );
						}
				}
		}
}

############################################################################
?>