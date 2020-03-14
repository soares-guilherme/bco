<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

############################################################################

require_once($CFG->dir_lib.'mail/phpmailer.lib.php');
require_once($CFG->dir_lib.'mail/smtp.lib.php');

############################################################################

function mail_smtp($server, $user, $pass, $from, $fromname, $to, $subject, $msg, $is_html=true, $reply_to=NULL)
	{
		global $CFG;
		
		$mail    = new PHPMailer();
		
		$mail->IsSMTP();
		$mail->SMTPDebug = false;
		$mail->IsHTML(true);
		
		$mail->Host     = $server;
		$mail->Hostname = $server;
		$mail->Mailer 	= 'smtp';
		$mail->Username	= $user;
		$mail->Password	= $pass;
		$mail->Sender   = $from;
		$mail->SMTPAuth = true;
		
		$mail->From     = $from;
		$mail->FromName = $fromname;
		$mail->Subject 	= $subject;
		
		if(!empty($reply_to))
			{
				$mail->AddReplyTo($reply_to);
			}
		
		if($is_html)
			{
				$mail->MsgHTML($msg);
			}
		else
			{
				$mail->IsHTML(false);
				$mail->Body = $msg;
			}
		
		if(is_array($to))
			{
				foreach($to as $dest)
					{
						$mail->AddAddress($dest);
					}
			}
		else
			$mail->AddAddress($to);
		
		$result = $mail->Send();
		
		$CFG->erros .= $mail->ErrorInfo;
		
		return $result;
	}

############################################################################
?>