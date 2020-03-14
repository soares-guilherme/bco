<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

/**
 * CALENDAR - Classe para impressão de calendário em forma de tabela.
 *
 * @copyright Copyright (C) 2003-2009, PluGzOne - http://www.plugzone.com.br
 * @author André Rutz Porto <andre@plugzone.com.br>
 * @package z1
 * @version 1.0
 */
 
class CALENDAR {
	
	public static $first_day = 1; // 0 : domingo, 1 : segunda
	
	public static $class_semana = '';
	public static $class_day_normal = '';
	public static $class_day_fimde = '';
	public static $class_day_atual = '';
	public static $class_day_evento = '';
	
	public static function generate($year, $month, $days = array())
		{			
			$first_of_month = gmmktime(0,0,0,$month,1,$year);
			
			$current_day = date('d');
			$current_month = date('m');
			$current_year = date('Y');

			list($month, $year, $weekday) = explode(',',gmstrftime('%m,%Y,%w',$first_of_month));
			
			$weekday = ($weekday + 7 - self::$first_day) % 7;
		
			$calendar = '<tr class="'.self::$class_semana.'">';
		
			if($weekday > 0)
				{
					$calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>';
				}
			
			for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++)
				{
					if($weekday == 7)
						{
							$weekday   = 0;
							$calendar .= '</tr><tr class="'.self::$class_semana.'">';
						}
					if(isset($days[$day]) and is_array($days[$day]))
						{
							$title = $days[$day][0];
							$content = isset($days[$day][1]) ? $days[$day][1] : $day;
							$link = isset($days[$day][2]) ? $days[$day][2] : NULL;
							$classes = isset($days[$day][3]) ? $days[$day][3] : NULL;
							
							if(is_null($content))
								{
									$content  = $day;
								}
							
							$classes = self::$class_day_evento.' '.$classes;
							
							$calendar .= '<td title="'.$title.'" ' . ( $classes ? ' class="'.$classes.'">' : '>' );							
							$calendar .= '<a title="'.$title.'" ' . ( $link ? ' href="'.$link.'"': '').'>'.$content.'</a>';
							$calendar .= '</td>';
						}
					elseif($current_month == $month and $current_year == $year and $current_day == $day)
						{
							$calendar .= '<td class="'.self::$class_day_atual.'">'.$day.'</td>';
						}
					elseif(!empty(self::$class_day_fimde) and ( (self::$first_day == 0 and ($weekday == 0 or $weekday == 6)) or (self::$first_day == 1 and ($weekday == 5 or $weekday == 6)) ) )
						{
							$calendar .= '<td class="'.self::$class_day_fimde.'">'.$day.'</td>';
						}
					else
						{
							$calendar .= '<td class="'.self::$class_day_normal.'">'.$day.'</td>';
						}
				}
				
			if($weekday != 7)
				{
					$calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>';
				}
		
			return $calendar.'</tr>';
		}
}
?>