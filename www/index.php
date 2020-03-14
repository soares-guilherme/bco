<?php
############################################################################

// SSL
if( $_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8443 )
	header("Location: https://".$_SERVER['HTTP_HOST']); /**/

// Seção principal
$_GET['secao'] = 'Inicial';

require('main.php');

############################################################################
?>