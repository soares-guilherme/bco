<?php
#**************************************************************************#
#                         PlugZone Soluções na Web                         #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

#\_ Classe para autenticacao de usuarios
class Auth {

	const SQL_CON = 'geral';
	const SQL_TABLE = 'clientes';

	const USER_OK = 1;
	const USER_NOT_FOUND = 0;
	const USER_DISABLED = -1;

	const ALLOW_DISABLED = true;
	
	#\_ Autenticacao
	static public function LogIn($usuario, $senha)
		{
			if(empty($usuario) or empty($senha))
				return self::USER_NOT_FOUND;
			
			$sql_usuario = " `email` = '".$usuario."' ";

			$usuario_numeros = preg_replace('/\D/', '', $usuario);

			if(strlen($usuario_numeros) == 11 and strlen($usuario_numeros) > 0)
				{
					$sql_usuario .= " (`cpf` = '".$usuario_numeros."' 
										OR `cpf` = '".substr($usuario_numeros, 0, 3).'.'.substr($usuario_numeros, 3, 3).'.'.substr($usuario_numeros, 6, 3).'-'.substr($usuario_numeros, 9, 2)."') ";
				}
			elseif(strlen($usuario_numeros) == 14)
				{
					$sql_usuario .= " (`cnpj` = '".$usuario_numeros."' 
										OR `cnpj` = '".substr($usuario_numeros, 0, 2).'.'.substr($usuario_numeros, 2, 3).'.'.substr($usuario_numeros, 5, 3).'/'.substr($usuario_numeros, 8, 4).'-'.substr($usuario_numeros, 12, 2)."') ";
				}

			$Login = bd_executa("SELECT * 
									FROM `".self::SQL_TABLE."` 
										WHERE ( ".$sql_usuario." ) 
											AND `senha` = '".$senha."'", self::SQL_CON);

			if($Login->nada)
				return self::USER_NOT_FOUND;

			$Login = $Login->res->rid0;

			if($Login->status == '1' or self::ALLOW_DISABLED)
				{
					$_SESSION['DEFAULT']['userid'] = $Login->id;
					$_SESSION['DEFAULT']['userdata'] = $Login;
	
					return self::USER_OK;
				}
				
			if($Login->status == '0')
				{
					return self::USER_DISABLED;
				}
		}
	
	static public function RememberIn()
		{
			if(!empty($_COOKIE['AUTH_REMEMBER_USERID']) and !empty($_COOKIE['AUTH_REMEMBER_USERSESSION']))
				{
					$Login = bd_executa("SELECT * 
											FROM `".self::SQL_TABLE."` 
												WHERE id = '".$_COOKIE['AUTH_REMEMBER_USERID']."'", self::SQL_CON);

					if($Login->nada or md5($Login->res->rid0->senha) != $_COOKIE['AUTH_REMEMBER_USERSESSION'])
						{
							unset($_COOKIE['AUTH_REMEMBER_USERID']);
							unset($_COOKIE['AUTH_REMEMBER_USERSESSION']);
						}
					else
						{
							$_SESSION['DEFAULT']['userid'] = $Login->res->rid0->id;
							
							self::ResetInfo();
						}
					
				}
		}

	#\_ Funcao para resetar dados do usuario atual
	static public function ResetInfo()
		{
			$Login = bd_executa("SELECT * FROM `".self::SQL_TABLE."` WHERE `id` = '".$_SESSION['DEFAULT']['userid']."'", self::SQL_CON);
			
			if(!$Login->nada)
				{
					$Login = $Login->res->rid0;

					if($Login->status == '1')
						{
							$_SESSION['DEFAULT']['userid'] = $Login->id;
							$_SESSION['DEFAULT']['userdata'] = $Login;
						}
				}
		}

	#\_ Funcao para setar dados do usuario atual
	static public function SetInfo($info, $value)
		{
			$_SESSION['DEFAULT']['userdata']->$info = $value;
		}

	#\_ Funcao para pegar dados do usuario atual
	static public function GetInfo($info=NULL)
		{
			if($info == NULL)
				return $_SESSION['DEFAULT']['userdata'];

			return $_SESSION['DEFAULT']['userdata']->$info;
		}

	#\_ Funcao para pegar dados de usuarios
	static public function GetUserData($usuario, $column='email')
		{
			return bd_executa("SELECT * FROM `".self::SQL_TABLE."` WHERE `".$column."` = '".addslashes($usuario)."'", self::SQL_CON);
		}

	#\_ Funcao para verificar a existencia de usuarios pelo login
	static public function UserExists($usuario)
		{
			$User = self::GetUserData($usuario);

			if($User->nada)
				return false;

			$User = $User->res->rid0;

			if($User->status == '0')
				return false;

			if($User->status == '1')
				return true;
		}

	#\_ Função para verificação de tipo
	static public function IsTipo($tipo)
		{
			if( in_array($tipo, explode(',', self::GetInfo('tipos'))) )
				return true;

			return false;
		}

	#\_ Função para verificação de autenticação no sistema
	static public function Verify()
		{
			self::RememberIn();
			
			if(empty($_SESSION['DEFAULT']['userid']))
				return false;

			return true;
		}

	#\_ Lembrar autenticacao
	static public function Remember($days=30)
		{
			@setcookie("AUTH_REMEMBER_USERID", self::GetInfo('id'), time()+(86400*$days), '/');
			@setcookie("AUTH_REMEMBER_USERSESSION", md5(self::GetInfo('senha')), time()+(86400*$days), '/');

			return true;
		}

	#\_ Função para logout de usuários
	static public function LogOut()
		{
			unset($_SESSION['DEFAULT']['userid']);
			unset($_SESSION['DEFAULT']['userdata']);

			@session_commit();
			
			@setcookie("AUTH_REMEMBER_USERID", "", time()-3600, '/');
			@setcookie("AUTH_REMEMBER_USERSESSION", "", time()-3600, '/');
			
			unset($_COOKIE['AUTH_REMEMBER_USERID']);
			unset($_COOKIE['AUTH_REMEMBER_USERSESSION']);

			return true;
		}

	}


############################################################################
?>