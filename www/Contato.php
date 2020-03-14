<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre@plugzone.com.br                                                #
#     http://www.plugzone.com.br                                           #
#__________________________________________________________________________#

##########################################################################*/

$ajax=empty($_GET['ajax'])?false:$_GET['ajax'];

// Cfg
require_once('incs/config.inc.php');

// Processa Formulario contato
$sucesso = false;

fix_post_encoding();

// contato rodape
if(!empty($_POST['contato_rodape_nome']) and !empty($_POST['contato_rodape_email']) and !empty($_POST['contato_rodape_mensagem']))
    {
        $_POST['contato_nome'] = $_POST['contato_rodape_nome'];
        $_POST['contato_email'] = $_POST['contato_rodape_email'];
        $_POST['contato_mensagem'] = $_POST['contato_rodape_mensagem'];
    }

if(!empty($_POST['contato_nome']) and !empty($_POST['contato_email']) and !empty($_POST['contato_mensagem']))
    {
        $identificacao = 'CONTATO';

        $msg =  "   Nome : ".$_POST['contato_nome'].NL.
                "   E-Mail : ".$_POST['contato_email'].NL.
                "   Telefone : ".$_POST['contato_telefone'].NL.
                "MENSAGEM:".NL.
                    $_POST['contato_mensagem'];

        informAdmin($identificacao, $msg);

        $sucesso = true;
    }

// retorno do form por ajax
if($ajax)
    {
        $CFG->nomain = true;

        $tpl = new TemplatePower($CFG->dir_tpl.'MostraContato.html');
        $tpl->prepare();

        // Processa Formulario contato
        if($sucesso)
	        {
		        $tpl->assign('msg', 'Sua mensagem foi enviada com sucesso!' );
	        }
        else
	        {
		        $tpl->assign('msg', 'Houve um erro no envio de sua mensagem.' );
            }

        return $tpl->getOutputContent();
	}

if($sucesso)
    {
        ke_head( go_area($area, 'done=true') );
    }

// pagina de contato
$tpl = new TemplatePower($CFG->dir_tpl.'Contato.html');
$tpl->prepare();

$CFG->breadcrumb['Contato'] = '';
$CFG->titulo .= ' - Contato';

$tpl->gotoBlock('_ROOT');

if(!empty($_GET['done']))
    {
        $tpl->newBlock('form_sucesso');
    }

// texto
$q = bd_executa("SELECT texto
                    FROM materias
                        WHERE id_secao = '3' 
                        ORDER BY data DESC LIMIT 1", $CFG->con);

if(!$q->nada)
    {
        $tpl->assign('texto', $q->res->rid0->texto );
    }

// prints
print_path($tpl);
assignGlobalLinks($tpl);
$tpl->gotoBlock('_ROOT');

##########################################################################*/

return $tpl->getOutputContent();

############################################################################
?>