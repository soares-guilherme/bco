<?php
#´´´´ PluGzOne ````````````````````````````````````````````````````````````#
#     andre at plugzone.com.br                                             #
#     https://plugz.one                                                    #
#__________________________________________________________________________#

############################################################################

#\_ Cria objeto CFG, que contera toda a configuracao do site
class CFG { };
$CFG = new CFG;

#\_ Setup
$CFG->domain          	= 'redebancodealimentos.org.br';

$CFG->bd_local        	= "localhost";
$CFG->bd_user        	= "rodemed_web";
$CFG->bd_pass         	= "6AIPcfo1NhIk";
$CFG->bd_base         	= "rodemed_web";

$CFG->smtp_local		= "br5.plugzone.net:587";
$CFG->smtp_user 		= "no-reply@".$CFG->domain;
$CFG->smtp_pass 		= "6bXDH0VFszfd";

##########################################################################*/

$CFG->dir_root         	= dirname($_SERVER['DOCUMENT_ROOT']) . '/';
$CFG->dir_sit         	= $CFG->dir_root."www/";
$CFG->con             	= "geral";

$CFG->z1panel_host 		= 'z1panel.plugz.one';

$CFG->debug     		= $_SERVER['REMOTE_ADDR'] == '181.220.29.129';
$CFG->erros     		= '';
$CFG->timezone			= 'America/Sao_Paulo';
$CFG->nomain    		= false;

##########################################################################*/

if(strpos($_SERVER['SERVER_NAME'], '.test') !== false)
	{
		/*$CFG->bd_local			= $CFG->domain; /**/
		$CFG->bd_local			= 'br5.plugzone.net'; /**/
		/*$CFG->bd_local			= "localhost";
		$CFG->bd_user        	= "lpexcurs_web";
		$CFG->bd_pass         	= "0btjzlDFmrfB";
		$CFG->bd_base         	= "lpexcurs_web"; /**/
		
		$CFG->domain          	= $_SERVER['HTTP_HOST'];

		$CFG->debug 			= true;

		$CFG->z1panel_host 		= 'z1panel.net';
	}

##########################################################################*/

$CFG->control_cache_days = 7; // cache control

// url do site
if( empty($CFG->site_domain) )
	{
		$CFG->site_domain = $CFG->domain;
		
		if(!$CFG->debug)
			{
				$CFG->site_domain = 'www.' . $CFG->domain;
			}
	}
if( empty($CFG->url_sit) )
	{
		if( $_SERVER['SERVER_PORT'] == 443 or $_SERVER['SERVER_PORT'] == 8443 )
			{
				$CFG->url_sit = "https://".$CFG->site_domain."/";
			}
		else
			{
				$CFG->url_sit = "http://".$CFG->site_domain."/";
			}
	}

// todo: remove this and dependencis
define('URL_Z1PANEL', 'https://'.$CFG->z1panel_host);

$CFG->dir_site		= &$CFG->dir_sit;
$CFG->url_site    	= &$CFG->url_sit;
$CFG->url         	= &$CFG->url_site;
$CFG->dir_lib     	= $CFG->dir_sit."libs/";
$CFG->url_lib     	= $CFG->url_sit."libs/";
$CFG->dir_inc     	= $CFG->dir_sit."incs/";
$CFG->dir_tpl     	= $CFG->dir_sit."tpls/";
$CFG->url_tpl     	= $CFG->url_sit."tpls/";
$CFG->dir_img     	= $CFG->dir_root."z1img/";
$CFG->dir_files   	= $CFG->dir_root."z1files/";
$CFG->url_mmateria  = "/MostraMateria/";
$CFG->url_mimg    	= "/MostraImagem/";
$CFG->url_mvid    	= "/MostraVideo/";
$CFG->url_maemp   	= "/MostraAEmpresa/";
$CFG->url_mlocal  	= "/MostraLocal/";    
$CFG->url_mcidade  	= "/MostraCidade/";
$CFG->dir_chat    	= $CFG->dir_sit."chat/";

if(empty($CFG->url_files))
	$CFG->url_files = $CFG->url_sit."z1files/pub/";

if(empty($CFG->url_img))
	$CFG->url_img 	= $CFG->url_sit."z1img/";

$CFG->sizes = array('640x480', '1200x900');

$CFG->contato = 'contato@'.$CFG->domain;
$CFG->contato_remetente = $CFG->contato;

$CFG->contatos 		= 'contatos@plugzone.com.br'; // PluGzone Mail Backup

$CFG->thumbsize     = $CFG->sizes[0];
$CFG->midsize      	= $CFG->sizes[0];
$CFG->fullsize      = $CFG->sizes[1];
//$CFG->mimgsize_ray  = explode('x', $CFG->mimgsize);

if(defined('QUICK_CFG'))
	return;


#\_ Bibliotecas necessarias
require($CFG->dir_inc.'globalization.inc.php');
require($CFG->dir_lib.'z1.lib.php');
require($CFG->dir_lib.'mysql.lib.php');
require($CFG->dir_lib.'template.lib.php');
require($CFG->dir_lib.'forms.lib.php');
require($CFG->dir_lib.'framework.lib.php');
require($CFG->dir_lib.'commerce.lib.php');
require($CFG->dir_lib.'http.lib.php');
require($CFG->dir_lib.'xml.lib.php');
require($CFG->dir_lib.'im.lib.php');
require($CFG->dir_lib.'auth.lib.php');
require($CFG->dir_lib.'captcha.lib.php');
require($CFG->dir_lib.'calendar.lib.php');
require($CFG->dir_lib.'mail/mail.lib.php');
require($CFG->dir_lib.'facebook.lib.php');
require($CFG->dir_lib.'quiz.lib.php');

$CFG->vars_js = array();
$CFG->files_css = array();
$CFG->files_js = array();

//=============================TEMPLATE NOVO INICIO=================================
//$CFG->files_css[] = 'file_pz_theme/animate.css';
//$CFG->files_css[] = 'file_pz_theme/aos.css';
//$CFG->files_css[] = 'file_pz_theme/bootstrap.min.css';
//$CFG->files_css[] = 'file_pz_theme/bootstrap-datepicker.css';
//$CFG->files_css[] = 'file_pz_theme/flaticon.css';
//$CFG->files_css[] = 'file_pz_theme/icomoon.css';
//$CFG->files_css[] = 'file_pz_theme/ionicons.min.css';
//$CFG->files_css[] = 'file_pz_theme/jquery.timepicker.css';
//$CFG->files_css[] = 'file_pz_theme/magnific-popup.css';
//$CFG->files_css[] = 'file_pz_theme/open-iconic-bootstrap.min.css';
//$CFG->files_css[] = 'file_pz_theme/owl.carousel.min.css';
//$CFG->files_css[] = 'file_pz_theme/owl.theme.default.min.css';
//$CFG->files_css[] = 'file_pz_theme/style.css';
//=============================TEMPLATE NOVO FIM =================================

#\_ CSS
$CFG->files_css[] = 'vars.css';
//$CFG->files_css[] = 'Mostra.css';
//=============================COMENTEI ESTAS PASTAS=================================
//$CFG->files_css[] = 'grid.css';
$CFG->files_css[] = 'css.css';
//$CFG->files_css[] = 'font.css'    ;
//$CFG->files_css[] = 'topo.css';
//$CFG->files_css[] = 'rodape.css';
//$CFG->files_css[] = 'datepicker.css';
//$CFG->files_css[] = 'jquery-ui.css';
//=============================COMENTEI ESTAS PASTAS=================================
//$CFG->files_css[] = 'select2.css';
//$CFG->files_css[] = 'z1alert.css';
//$CFG->files_css[] = 'fa.min.css';


#\_ JS
//$CFG->files_js[] = 'css.js';
//$CFG->files_js[] = 'win.pz1.js';
//$CFG->files_js[] = 'ajax.pz1.js';
//$CFG->files_js[] = 'forms.pz1.js';
//$CFG->files_js[] = 'jquery.js';
//$CFG->files_js[] = 'jquery-ui.js';
//$CFG->files_js[] = 'jquery-mask.js';
//$CFG->files_js[] = 'jquery.ba-hashchange.min.js';
//$CFG->files_js[] = 'jquery.scrollLock.js';
//$CFG->files_js[] = 'nav.js';
//$CFG->files_js[] = 'framework.js';
//$CFG->files_js[] = 'datepicker.js';
//$CFG->files_js[] = 'soundmanager2-nodebug-jsmin.js';
//$CFG->files_js[] = 'select2.min.js';
//$CFG->files_js[] = 'qrcode.min.js';





#\_ Cria a conexao padrao, caso seja necessario
if(!empty($CFG->con))
	bd_conecta($CFG->con, $CFG->bd_local, $CFG->bd_user, $CFG->bd_pass, $CFG->bd_base);

#\_ Cria a sessao padrao
@session_start();
if(!isset($_SESSION['DEFAULT']))
	$_SESSION['DEFAULT'] = array();

$Session = &$_SESSION['DEFAULT'];

#\_ Ajusta o Debugging
if($CFG->debug)
	error_reporting(E_ALL);
else
	error_reporting(false);

#\_ Ajusta a Timezone
if(isset($CFG->timezone))
	@date_default_timezone_set($CFG->timezone);

#\_ Cria o array do estilo do site
$CFG->estilo = array("alertarea" => "estilo",
					"input" => "fmInput",
					"input_onerror" => "fmInput_onerror",
					"textarea" => "fmInput",
					"textarea_onerror" => "fmInput_onerror",
					"select" => "fmInput",
					"select_onerror" => "fmInput_onerror",
					"submit" => "fmSubmit",
					"alertarea" => "alertarea",
					"select_alt" => "SelectAlt",
					"select_alt_onerror" => "SelectAlt_onerror");

#\_ Areas do site
$CFG->areas = array(1 	=> 'Inicial', 
					2 	=> 'Contato', 
                    3   => 'Proposta', 
                    
					5 	=> 'FAQ', 
					
					9 	=> 'Captcha',
					
                    10  => 'Secao', 
                    11  => 'Pagina', 
                    
					20 	=> 'Veiculo', 
                    21  => 'Veiculos', 
                    
					101 => 'Newsletter', 
					
					201 => 'MostraImagem', 
					202 => 'MostraAEmpresa', 
					203 => 'MostraLocal', 
					204 => 'MostraTrabalheConosco', 
					205 => 'MostraMateria', 
					206 => 'MostraVideo', 
                    
                    300 => 'AreaRestrita',
                    301 => 'EsqueciaSenha',

                    401 => 'Cadastro', 
                    
					601 => 'Imagem',
					602 => 'File',
								        
                    900 => 'Home',

					901 => 'RSS',
					
					955 => 'z1img',
					956 => 'z1files');

$CFG->areas_f = array_flip($CFG->areas);
foreach($CFG->areas_f as $k => $e)
	{
		$CFG->areas_f[strtolower($k)] = $e;
		$CFG->areas_f[strtoupper($k)] = $e;
	}

// Vars
$CFG->breadcrumb = array();
$CFG->sitename = __('Rede de Bancos de Alimentos do Rio Grande do Sul');
$CFG->sitetitle = $CFG->sitename.__(' - Rede de Bancos de Alimentos do Rio Grande do Sul'); // titulo do site
$CFG->description = $CFG->sitetitle; // descricao do site
$CFG->keywords = $CFG->sitetitle; // palavras chave do site

$CFG->titulo = &$CFG->sitetitle; // titulo do site , todo: replace $CFG->titulo sitewide

$CFG->contact_whatsapp_prefix = '51';
$CFG->contact_whatsapp_number = '3026-8020';
$CFG->contact_phone1_prefix = '53';
$CFG->contact_phone1_number = '3026-8020';

$CFG->contact_facebook = 'REDEBANCOSALIMENTOSRS';
$CFG->contact_instagram = 'rededebancosdealimentosrs/';

$CFG->head_opengraph = array('title' => $CFG->titulo,
                            'site_name' => $CFG->titulo,
                            'description' => $CFG->description,
                            'image' => $CFG->url_tpl.'img/Logo.png',
                            'image_bydir' => $CFG->dir_tpl.'img/Logo.png',
                            'url' => $CFG->url_site,
                            'type' => 'website'); // open graph info (para compartilhar com facebook)
                            
$CFG->recaptchav2_sitekey = '';
$CFG->recaptchav2_secret = '';

$CFG->google_analytics_id = '';

$CFG->key_consultacep = '699e5133157a68a17d14a46415ba0533';

$CFG->files_ext = array('doc', 'docx', 'dot', 'pdf', 'odt', 'txt', 'xps', 'jpg', 'jpeg');

##########################################################################*/
?>