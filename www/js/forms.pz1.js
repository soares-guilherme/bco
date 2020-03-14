// Select Alt
function SelectAlt_click(el, submit) {
    var chbox = el.getElementsByTagName('INPUT').item(0);
    
    if(isChecked(chbox)) {
        el.className = "SelectAlt_option";
        chbox.checked = false;
    } else {
        el.className = "SelectAlt_selected";
        chbox.checked = true;
    }
    
    console.log(el);
    
    if(typeof(submit) != 'undefined') {
		document.getElementById(submit).submit();
	}
}

// Verica CNPJ
function verificaCNPJ(cid)
	{
		var Valor = cid.value.replace(/[^0-9]+/g, "");
		var tamanho = Valor.length;
		var a = [];
		var b = [];
		var soma=0;
		var resto=0;

		if (tamanho==14)
			{
				var cnpj;
				var c = [6,5,4,3,2,9,8,7,6,5,4,3,2];
				cnpj = cid.value.replace(/[^0-9]+/g, "");
	
				for(i=0;i<14;i++)
					a[i] = cnpj.charAt(i);
	
				for(i=0,j=1;i<12;i++,j++)
					{
						b[i] = a[i]*c[j];
						soma = soma + b[i];
					}
	
				resto = soma % 11;
	
				if(resto<2)
					diga=0;
				else
					diga=11-resto;
	
				resto = 0;
				soma = 0;
	
				for(i=0;i<13;i++)
					{
						b[i] = a[i]*c[i];
						soma = soma + b[i];
					}
	
				resto = soma % 11;
	
				if(resto<2)
					digb=0;
				else
					digb=11-resto;
	
				if(a[12]!=diga || a[13]!=digb)
					return false;
				else
					return true;
			}
		else
			return false;
	}
// Valida CPF
function verificaCPF(cid)
	{
		var Valor = cid.value.replace(/[^0-9]+/g, "");
		var tamanho = Valor.length;
		var a = [];
		var b = [];
		var soma=0;
		var resto=0;

		if(tamanho==11)
			{
				if(Valor == '11111111111' || Valor == '22222222222' || Valor == '33333333333' || Valor == '44444444444' || 
				   Valor == '55555555555' || Valor == '66666666666' || Valor == '77777777777' || Valor == '88888888888' || 
				   Valor == '99999999999')
				return false;

				var cpf;
				var c = [11,10,9,8,7,6,5,4,3,2];
				cpf = cid.value.replace(/[^0-9]+/g, "");

				for(i=0,j=1;i<11;i++,j++)
					{
						a[i] = cpf.charAt(i);
					}

				for(i=0,j=1;i<9;i++,j++)
					{
						b[i]=a[i]*c[j];
						soma = b[i] + soma;
					}

				resto = soma % 11;

				if(resto<2)
					diga=0;
				else
					diga=11-resto;

				resto=0;
				soma=0;

				for(i=0;i<10;i++)
					{
						b[i]=a[i]*c[i];
						soma = b[i] + soma;
					}

				resto = soma % 11;

				if(resto<2)
					digb=0;
				else
					digb=11-resto;

				if(a[9]!=diga || a[10]!=digb)
					return false;
				else
					return true;			
			}
		else
			return false;
	}
// Somente numeros inteiros
function StrictInt(el) {
	el.value = el.value.replace(/[^0-9]/g,'');
}
// Somente numeros com decimais
function StrictFloat(el) {
	el.value = el.value.replace(/[^0-9\.\,]/g,'');
}
