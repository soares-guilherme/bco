<?php if( !isset($_GET['key']) or $_GET['key'] != 'ce51900c9dfc90ffe27ee3b6845576fc') 
	{ 
		?><pre><h3 onDblClick="window.location.assign('?key='+prompt('Senha'));">403 - Acesso negado</h3></pre><?php 
		
		exit; 
	} 
if(empty($_GET['no_html']))
	{
?><html>
<head>
<title>z1</title>
</head>
<body>
<pre><?php
	}
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

############################################################################

$START = microtime(true);

error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);
ob_implicit_flush();

define('NL', "\r\n");

############################################################################

function log_do($str)
	{
		if(!empty($_GET['debug']))
			echo '<span style="color:#CC0000">'.$str.'</span>'.NL;
	}

function script_timeout($sec=30, $adt=NULL)
	{
		echo '</pre>'.NL.'<script> setTimeout( function () { window.location.assign("'.$_SERVER['SCRIPT_URI'].'?'.$GLOBALS['auth'].$adt.'"); } , '.($sec*1000).');</script>';
	}

/*
define('DIR_LIB', '../libs/');

require(DIR_LIB.'base.lib.php');
require(DIR_LIB.'misc.lib.php');
require(DIR_LIB.'http/http.lib.php');
require(DIR_LIB.'html/htmlparser.lib.php');
require(DIR_LIB.'html/translator.lib.php');
require(DIR_LIB.'html/stealer.lib.php');
require(DIR_LIB.'url/url.lib.php');
require(DIR_LIB.'bd/sgbd.lib.php');
require(DIR_LIB.'bd/mysql.lib.php');
require(DIR_LIB.'math/math.lib.php');

########################################################################/**/

$auth = 'key='.$_GET['key'];

if(!empty($_GET['script']))
	{
		include($_GET['script']);
	}
else
	{		
		$links = array();
		$dir_root = './';
		
		print_list($dir_root);
	}

function print_list($dir, $depth=0)
	{
		global $auth;
		$files = scandir($dir);
		
		$d_str = '';
		
		for($i=0;$i<$depth;$i++)
			{
				$d_str .= '    ';
			}

		$c = 0;				
		$scripts = array();
		
		foreach($files as $file)
			{
				if($file == '.' or $file == '..' or $file == '.file' or $file == '.svn')
					continue;
				
				if(is_dir($dir.$file)) // dirs
					{
						echo '<h3>'.$d_str.$file.'</h3>';
				
						print_list($dir.$file.'/', $depth+1);
					}
				elseif(substr($file, -4) == '.php') // php
					{
						$scripts[] = $file;
					}
			}
		
		if(!count($scripts) > 0)
			{
				echo '<p>'.$d_str.' - </p>';
			}
		else
			{
				foreach($scripts as $file)
					{
						$href = $dir.$file;
						$a = basename($file, '.php');
						
						echo '<p>'.$d_str.(++$c).'. <a href="'.$href.'?'.$auth.'">'.$a.'</a></p>';
					}
			}
	}

if(empty($_GET['no_html']))
	{
?>
<p>Tempo de Execu&ccedil;&atilde;o: <?php print(microtime(true) - $START); ?><br><a href="/scripts/">&lsaquo;- raiz</a></p>
</pre>
</body>
</html><?php
	}
?>