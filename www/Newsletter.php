<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

// Cfg
require_once('incs/config.inc.php');

$tpl = new TemplatePower($CFG->dir_tpl.'Newsletter.html');
$tpl->prepare();

$titulo = __('Newsletter');

$CFG->titulo .= ' - '.$titulo;
$CFG->breadcrumb[$titulo] = '';
$tpl->assign('titulo', $titulo );

if(!empty($_GET['ajax']))
    {
        $CFG->nomain = true;
    }

fix_post_encoding();

// Cadastra na newsletter
if(!empty($_POST['newsletter_nome']) and !empty($_POST['newsletter_email']))
	{
		fix_post_encoding();
		
		addMail($_POST['newsletter_nome'], $_POST['newsletter_email']);
		
		if(!empty($_GET['ajax']))
			{
				// faz esquema para aparecer mensagem em ajax confirmando cadastro
				// por exemplo ---->
				
				$tpl = new TemplatePower($CFG->dir_tpl.'MostraContato.html');
				$tpl->prepare();

				$tpl->assign('msg', 'Sua mensagem foi enviada com sucesso!' );
				
				echo $tpl->getOutputContent();
				exit;
			}
		else
		    {
		        $tpl->newBlock('form_sucesso');
		    }
	}

// Processa o form do optout
if(!empty($_POST['optout_email']))
	{
		fix_post_encoding();
		
		$Assinante = bd_executa("SELECT * FROM `maillist` WHERE `email` = '".bd_escape($CFG->con, $_POST['optout_email'])."'", $CFG->con);
		
		if(!$Assinante->nada)
			{
				$Assinante = $Assinante->res->rid0;
				
				$link = go_area($area, 'optout_id='.$Assinante->id.'&optout_email='.$Assinante->email.'&verify='.md5(microtime(true)));
				
				$msg = 	"Recebemos um pedido de cancelamento do seu cadastro na nossa Newsletter, <br />".NL.
						"para confirmar a sua exclus&atilde;o DEFINITIVA do nosso banco de dados clique no <br />".NL.
						"link abaixo:<br /><br />".NL.NL.
						'<a href="'.$link.'">'.$link.'</a><br /><br />'.NL.NL.
						"Se voc&ecirc; n&atilde;o deseja cancelar o recenimento ou acha que recebeu este <br />".NL.
						"e-mail por engano, simplesmente desconsidere esta mensagem.<br /><br />".NL.NL.
						"Obrigado!";
		
				mailTo( $Assinante->email, $CFG->contato, 'PEDIDO DE SAIDA DA NEWSLETTER '.$CFG->url_site, $msg);
			}
		
		$tpl->newBlock('form_optout_envio');
	}

// Realiza verdadeiro saida do optout
if(!empty($_GET['optout_id']) and !empty($_GET['optout_email']))
	{
		fix_post_encoding();
		
		remMail($_GET['optout_id'], $_GET['optout_email']);
		
		$tpl->newBlock('form_optout_sucesso');
	}

// Formulario de cadastro
$News = new FORM('form_newsletter_interna', go_area($area), $CFG->estilo, 'z1alert');

$News->create_tag_text_ini('newsletter_nome', 'Nome Completo', 45, 100, 'Nome Completo');
$News->create_tag_text_ini('newsletter_email', 'E-mail', 45, 100, 'E-mail', array('email'));
$News->create_tag_submit('submit', 'Enviar');
$News->build($tpl, 'newsletter_interna');

$tpl->gotoBlock('_ROOT');

// Formulario de optout
$Newsletter = new FORM('form_optout', go_area($area), $CFG->estilo, 'z1alert');

$Newsletter->create_tag_text_ini('optout_email', 'E-mail', 45, 100, 'E-mail', array('email'));
$Newsletter->create_tag_submit('optout_submit', 'Enviar', '', 'style="margin-top:10px"');

$Newsletter->build($tpl, 'optout');

$tpl->gotoBlock('_ROOT');

// prints
assignGlobalLinks($tpl);
$tpl->gotoBlock('_ROOT');

##########################################################################*/

return $tpl->getOutputContent();

############################################################################
?>