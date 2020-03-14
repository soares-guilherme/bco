var z1Alert_onclose = '';
var z1Alert_rm_tag;

function z1Alert(txt) {
	try { 
		z1Alert_onclose = arguments[1];
	} catch(err) { }

	var htmlTag = document.createElement("div");

	htmlTag.style.width = '100%';
	htmlTag.style.height = '100%';

	ray = document.getElementsByTagName("input");
	for (i=0;i<ray.length;i++)
		{
			if(ray[i].disabled != null && ray[i].disabled != '') {
				ray[i].setAttribute('was_disabled', 'true');
			} else {
				ray[i].setAttribute('was_disabled', 'false');
				ray[i].disabled = 'true';
			}
		}
	ray = document.getElementsByTagName("select");
	for (i=0;i<ray.length;i++)
		{
			if(ray[i].disabled != null && ray[i].disabled != '') {
				ray[i].setAttribute('was_disabled', 'true');
			} else {
				ray[i].setAttribute('was_disabled', 'false');
				ray[i].disabled = 'true';
			}
		}
	
	htmlTag.innerHTML = z1Alert_html.replace('{TITLE}', 'Aten&ccedil;&atilde;o!').replace('{ICO}', '!').replace('{CONTENT}', txt);
	document.getElementsByTagName("body").item(0).appendChild(htmlTag);
	getE("AlertWindow").style.display = '';
	getE("AlertArea").style.maxHeight = (getElHeight(getE("AlertWindow")) - 100) + 'px' ;
	
	z1Alert_rm_tag = htmlTag;
	
	//$('html,body').animate({'scrollTop': '0'}, 300);
	
	try { $BindAll(); } catch(err) { } 
	
	return true;
}
function z1Confirm(txt, action) {
	z1Alert_onclose = action;

	var htmlTag = document.createElement("div");

	htmlTag.style.width = '100%';
	htmlTag.style.height = '100%';

	ray = document.getElementsByTagName("input");
	for (i=0;i<ray.length;i++)
		{
			ray[i].disabled = 'true';
		}
	ray = document.getElementsByTagName("select");
	for (i=0;i<ray.length;i++)
		{
			ray[i].disabled = 'true';
		}
	
	htmlTag.innerHTML = z1Alert_html.replace('{TITLE}', 'Pergunta!').replace('{ICO}', '?').replace('{CONTENT}', txt);
	document.getElementsByTagName("body").item(0).appendChild(htmlTag);
	getE("AlertWindow").style.display = '';
	getE("AlertCancel").style.display = '';

	z1Alert_rm_tag = htmlTag;
	
	return true;
}
function z1Alert_Ok() {
	document.getElementById("AlertWindow").style.display = 'none';
	
	ray = document.getElementsByTagName("input");
	for (i=0;i<ray.length;i++) {
		if(ray[i].getAttribute('was_disabled') != 'true') {
			ray[i].disabled = '';
		}
	}
	ray = document.getElementsByTagName("select");
	for (i=0;i<ray.length;i++) {
		if(ray[i].getAttribute('was_disabled') != 'true') {
			ray[i].disabled = '';
		}
	}
		
	try {
		document.getElementsByTagName("body").item(0).removeChild(z1Alert_rm_tag);
	} catch(err) { }
	
	try {
		if(typeof(z1Alert_onclose) == 'function') {
			z1Alert_onclose();
		}
	} catch(err) { }
	
	z1Alert_onclose = null;
	
	return true;
}
function z1Alert_Cancel() {
	document.getElementById("AlertWindow").style.display = 'none';
	
	ray = document.getElementsByTagName("input");
	for (i=0;i<ray.length;i++) {
		ray[i].disabled = '';
	}
	ray = document.getElementsByTagName("select");
	for (i=0;i<ray.length;i++) {
		ray[i].disabled = '';
	}
		
	try {
		document.getElementsByTagName("body").item(0).removeChild(z1Alert_rm_tag);
	} catch(err) { }
	
	z1Alert_onclose = null;
	
	return true;
}