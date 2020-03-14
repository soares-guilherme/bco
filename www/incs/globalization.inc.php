<?php
#ДДДД PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

############################################################################

if(!defined('LANGUAGE')) { define('LANGUAGE', 'pt_BR');	}

############################################################################

function __($str)
	{
		$str = utf8_decode_once($str);
		
		$args = @func_get_args();

		if(count($args) > 1)
			{
				array_shift($args);
				
				foreach($args as $k => $e)
					{
						$args[$k] = utf8_decode_once($e);
					}
				
				$str = @vsprintf($str, $args);
			}
		
		return $str;
	}

############################################################################
?>