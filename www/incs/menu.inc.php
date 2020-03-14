<?php

if(!isset($CFG))
	exit;

function assignGlobalLinks(&$tpl)
	{
		global $CFG;

		$tpl->assignGlobal('link_'.'site', $CFG->url_site );

		$tpl->assignGlobal('link_'.'inicial', go_area(1) );
		$tpl->assignGlobal('link_'.'contato', go_area(2) );
        $tpl->assignGlobal('link_'.'faleconosco', go_area(2) );
        $tpl->assignGlobal('link_'.'faq', go_area('FAQ') );
        
		$tpl->assignGlobal('link_'.'rss', go_area('RSS') );
        
        $tpl->assignGlobal('email_contato', $CFG->contato );
		
		$tpl->assignGlobal('contact_'.'instagram', $CFG->contact_instagram );
		$tpl->assignGlobal('contact_'.'facebook', $CFG->contact_facebook );
		
		$tpl->assignGlobal('contact_'.'whatsapp_prefix', $CFG->contact_whatsapp_prefix );
		$tpl->assignGlobal('contact_'.'whatsapp_number', $CFG->contact_whatsapp_number );
		$tpl->assignGlobal('contact_'.'phone1_prefix', $CFG->contact_phone1_prefix );
		$tpl->assignGlobal('contact_'.'phone1_number', $CFG->contact_phone1_number );
		
		$tpl->assignGlobal('link_voltar', link_voltar() );
		$tpl->assignGlobal('link_z1panel', URL_Z1PANEL );
	}

############################################################################
?>