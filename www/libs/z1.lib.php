<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

############################################################################


/**
 * MFR_I - Classe estático com funções básicas, extendida para o Mainframe 1.
 * 
 * @copyright Copyright (C) 2003-2009, PluGzOne - http://www.plugzone.com.br
 * @author André Rutz Porto <andre@plugzone.com.br>
 * @package z1
 * @version 0.9
 * 
 */

class z1
	{
		const INTERVAL_DAY = 86400;
		
		/**
		 * Retorna array de Unidades federativas do Brasil
		 */
		public static function get_UFs()
			{
				$ret = array(
							'AC'=>'AC', 'AL'=>'AL', 'AM'=>'AM', 'AP'=>'AP', 'BA'=>'BA', 'CE'=>'CE', 'DF'=>'DF', 'ES'=>'ES', 'GO'=>'GO', 
							'MA'=>'MA', 'MG'=>'MG', 'MS'=>'MS', 'MT'=>'MT', 'PA'=>'PA', 'PB'=>'PB', 'PE'=>'PE', 'PI'=>'PI', 'PR'=>'PR', 
							'RJ'=>'RJ', 'RN'=>'RN', 'RO'=>'RO', 'RR'=>'RR', 'RS'=>'RS', 'SC'=>'SC', 'SE'=>'SE', 'SP'=>'SP', 'TO'=>'TO'
						);

				return $ret;
			}
		
		/**
		 * Retorna array com meses do ano ou Mês específico
		 * @param int $month Opcional, Mês específico.
		 * @return mixed array ou string
		 */
		public static function get_DaysOfWeek()
			{
				$ret = array( __('Domingo'), __('Segunda-feira'), __('Terça-feira'), __('Quarta-feira'), __('Quinta-feira'), __('Sexta-feira'), __('Sábado') );

				return $ret;
			}
		
		/**
		 * Retorna array com meses do ano ou Mês específico
		 * @param int $month Opcional, Mês específico.
		 * @return mixed array ou string
		 */
		public static function get_Months($month=NULL)
			{
				$ret = array(	1 => __('Janeiro'), 2 => __('Fevereiro'), 3 => __('Março'), 
								4 => __('Abril'), 5 => __('Maio'), 6 => __('Junho'), 
								7 => __('Julho'), 8 => __('Agosto'), 9 => __('Setembro'), 
								10 => __('Outubro'), 11 => __('Novembro'), 12 => __('Dezembro') );
				
				if($month==NULL)
					{
						return $ret;
					}
				else
					{
						return $ret[intval($month)];
					}
			}
		
		/**
		 * Retorna array com meses do ano ou Mês específico
		 * @param int $month Opcional, Mês específico.
		 * @return mixed array ou string
		 */
		public static function get_MonthsLastDays($month=NULL)
			{
				$ret = array( 1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 
							 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);
				
				if($month==NULL)
					{
						return $ret;
					}
				else
					{
						return $ret[intval($month)];
					}
			}
		
		public static function is_DateInRange($date, $range)
			{
				$date = strtotime($date);
				
				if($date > strtotime($range[0]) and $date < strtotime($range[1]))
					{
						return true;
					}
				
				return false;
			}
				
		/**
		 * Gera tag SPAN com estilo CSS de cor (color) definita com $color, em hexcolor
		 * @param string $str Texto para filtrar
		 * @param string $color Cor em Hexadecimal. Ex: #FFF
		 * @return string Tag completa
		 */
		public static function colorize($str, $color)
			{
				$str = '<span style="color:'.$color.';" >'.$str.'</span>';
				
				return $str;
			}
		
		/**
		 * Filtra Âncoras (anchors) nulas do texto para previnizar indesejáveis scrolls.
		 * @param string $str Texto para filtrar
		 * @return string Texto filtrado
		 */
		public static function fckeditor_sql_filter($str)
			{
				$str = strtr($str, array('href="#"' => 'href="javascript:void(null);"', 
										'href=\"#\"' => 'href=\"javascript:void(null);\"', 
										"href=\"#\"" => "href=\"javascript:void(null);\"") );
				
				return $str;
			}
		
		/**
		 * Retorna a propriedade $var setada a partir da UI
		 * @param string $var Propriedade a ser verificada
		 * @return mixed Valor da propriedade
		 */
		 public static function get_ui_dom($var)
			{				
				if(empty($GLOBALS['Session']['UI'][$var]))
					return NULL;
				
				return $GLOBALS['Session']['UI'][$var];
			}
		/**
		 * Retorna a data mais recente provável (hotfix)
		 * @param object $res Objeto de resposta (res) de DB::execute
		 * @return string Data em formato do banco de dados
		 */
		 public static function get_valid_date(&$res, $field=NULL)
			{
				if(!empty($field))
					{
						if(self::check_date($res->$field))
							return $res->$field;
					}
				
				if(self::check_date($res->modify_date))
					return $res->modify_date;
				elseif(self::check_date($res->creation_date))
					return $res->creation_date;
			}
		
		/**
		 * Verifica integridade da data em formato string
		 * @param string $str String da data
		 * @return bool Data válida ou não
		 */
		public static function check_date($str)
			{
				if($str == '00:00:00')
					return false;
				if($str == '00-00-00')
					return false;
				if($str == '00-00-00 00:00:00')
					return false;
				if($str == '0000-00-00')
					return false;
				if($str == '0000-00-00 00:00:00')
					return false;
				if(empty($str))
					return false;
				

				return true;
			}
		
		/**
		 * Retorna float a partir de número com vírgula (BRA)
		 * @param string $str
		 */
		public static function float_from_virgula($str)
			{
				return floatval(strtr($str, array('.' => '', ',' => '.')));
			}
		
		/**
		 * Retorna string formatada de acordo com a moead (currency) especificada
		 * @param float $num Número em float, ou double, ou int, string numérica
		 * @param string $currency Padrão BRL. Moeda para converter.
		 */
		public static function number_to_currency($num, $currency='BRL')
			{
				switch($currency)
					{
						case 'BRL':
						default:
						
							$output = 'R$ '.number_format($num, 2, ',', '.');
						
							break;
					}
				
				return $output;
			}
		
		/**
		 * Retorna string formatada em lista.
		 * @param string $title Título da lista
		 * @param array $list Lista de ítens ordenados da lista.
		 */
		public static function html_entitled_numbered_list($title, $list)
			{
				$i = 0;
				
				$output = '<b>'.$title.'</b><br />';
				
				foreach($list as $e)
					{
						$output .= '<br />'.(++$i).'. '.$e;
					}
				
				return $output;
			}
	}

############################################################################
?>