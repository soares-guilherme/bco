var lastFieldsWithErrors = [];
function fm_{FORM_NAME}_check() {
	
	var hasErrors = false;
	var errorMessages = [];
	var withErrors = [];
	var errorMessage = '';
	var custom_error_check;
			
	for (var i=0;i<lastFieldsWithErrors.length;i++) {
		field = getE(lastFieldsWithErrors[i]);
		field.className = field.getAttribute('class_normal');
	}
	
	/* START ERRORS ROUTINE */

	<!-- START BLOCK : custom_error_check -->
	custom_error_check = {CUSTOM_ERROR_CHECK};
	if(custom_error_check != null){
		errorMessages.push( custom_error_check );
		withErrors.push('null');
		hasErrors = true;
	}
	<!-- END BLOCK : custom_error_check -->
	
	<!-- START BLOCK : error -->
	if(getE('{FORM_FIELD}').getAttribute('ignore') != 'yes' && getE('{FORM_FIELD}').getAttribute('disabled') != 'disabled') {
		<!-- START BLOCK : error_texto -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value == ""){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C}');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_texto -->
		<!-- START BLOCK : error_textarea -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value == "" || document.{FORM_NAME}.{FORM_FIELD}.value.length > {FORM_MAX}){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com no máximo {FORM_MAX} caracteres, você utilizou ' + document.{FORM_NAME}.{FORM_FIELD}.value.length + ' caracteres');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_textarea -->
		<!-- START BLOCK : error_email -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value.indexOf("@") == -1 || document.{FORM_NAME}.{FORM_FIELD}.value.indexOf(".") == -1 || document.{FORM_NAME}.{FORM_FIELD}.value == "") {
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com um e-mail válido');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_email -->		
		<!-- START BLOCK : error_ini -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value == "{FORM_INI}"){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com um valor diferente de "{PRINT_B}{FORM_INI}{PRINT_B_C}"');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_ini -->
		<!-- START BLOCK : error_igual -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value != document.{FORM_NAME}.{FORM_COMPARE_FIELD}.value){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} igual ao campo {PRINT_B}{FORM_COMPARE_LABEL}{PRINT_B_C}');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_igual -->
		<!-- START BLOCK : error_cpf -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value == "" || verificaCPF(document.{FORM_NAME}.{FORM_FIELD}) === false ){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com um CPF válido');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_cpf -->
		<!-- START BLOCK : error_cnpj -->
		if(document.{FORM_NAME}.{FORM_FIELD}.value == "" || verificaCNPJ(document.{FORM_NAME}.{FORM_FIELD}) === false ){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com um CNPJ válido');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_cnpj -->
		<!-- START BLOCK : error_captcha -->
		var fmcheck_{FORM_NAME}_{FORM_FIELD} = function() {
						
			$el = $(document.{FORM_NAME}.{FORM_FIELD});
			
			if(!$el.val()) {
				return false;
			}
			
			if($.md5($el.val().toLowerCase()+$el.attr('data-pepper')) == $el.attr('data-md5')) {
				return true;
			}
			
			return false;
		}
		if(fmcheck_{FORM_NAME}_{FORM_FIELD}() === false ){
			errorMessages.push('Preencher o campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} com o código de verificação humana correto');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_captcha -->
		<!-- START BLOCK : error_recaptchav2 -->
		var fmcheck_{FORM_NAME}_{FORM_FIELD} = function() {
			
			if(grecaptcha.getResponse()) {
				return true;
			}
			
			return false;
		}
		if(fmcheck_{FORM_NAME}_{FORM_FIELD}() === false ){
			errorMessages.push('Clique no campo {PRINT_B}{FORM_LABEL}{PRINT_B_C} conforme indicado');
			withErrors.push('{FORM_FIELD}');
			hasErrors = true;
		}
		<!-- END BLOCK : error_recaptchav2 -->
	}
	<!-- END BLOCK : error -->
	
	/* END ERRORS ROUTINE */
		
	if(hasErrors) { 
	
		lastFieldsWithErrors = [];
		
		errorMessage = "";
				
		for(var i=0;i<withErrors.length;i++) {
			
			errorMessage += "{PRINT_NL} - " + errorMessages[i];
			
			if(withErrors[i] != 'null' && withErrors[i] != null) {
				lastFieldsWithErrors.push(withErrors[i]);
				field = getE(withErrors[i]);
				field.className = field.getAttribute('class_onerror');
			}
		}	
		
		<!-- START BLOCK : print_alert -->		
		headerMessage = "      Atenção, não foi possível enviar o formulário devido a problemas {PRINT_NL}";
		headerMessage += " na validação dos dados, confira as exigências abaixo e {PRINT_NL}";
		headerMessage += " tente novamente.{PRINT_NL}";
		errorMessage = headerMessage + errorMessage;		
		errorMessage += "{PRINT_NL}{PRINT_NL}Pressione Ok e preencha corretamente o formulário.";
		
		alert(errorMessage);
		try { getE(withErrors[0]).focus(); } catch(e) { }
		<!-- END BLOCK : print_alert -->
		
		<!-- START BLOCK : print_z1alert -->
		headerMessage = "      Atenção, não foi possível enviar o formulário devido a problemas";
		headerMessage += " na validação dos dados, confira as exigências abaixo e";
		headerMessage += " tente novamente.{PRINT_NL}";
		errorMessage = headerMessage + errorMessage;
		errorMessage += "{PRINT_NL}{PRINT_NL}Pressione Ok e preencha corretamente o formulário.";
		
		z1Alert(errorMessage, function () { getE(withErrors[0]).focus(); } );
		<!-- END BLOCK : print_z1alert -->		
		
		return false;
	} else { 
		return true;
	}
}
