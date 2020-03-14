/**
* Ajax class and ajax object
* 
*/
function Ajax () {
	this.onload = null;
	this.embed_js = false;
}
Ajax.prototype.handler = function () {
	var xmlhttp;
	try{
		xmlhttp = new XMLHttpRequest();
	} catch(err) {
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(err) {
			try{
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(err) {
				xmlhttp = false;
			}
		}
	}
	return xmlhttp;
}
Ajax.prototype.get = function (uri, callback, opts) {
	
	var l = this;
	l.response = '';
	l.embedded_js = '';
	
	if(typeof(opts) == 'undefined') {
		var opts = {};
	}
	
	var xmlhttp = l.handler();
	
	xmlhttp.open("GET", uri, true);
	xmlhttp.onreadystatechange = function() {
		if (this.readyState==4) {
			
			l.response = this.responseText;
			l.response = l.response.replace(/\+/g," ");
			l.response = unescape(l.response);
			
			if(l.embed_js === true || opts.embed_js === true) { // js embutido
				l.response = l.response.split('<!-- ENDJS -->');
	
				if(l.response.length == 2) {
					l.embedded_js = l.response[0];
					l.response = l.response[1];
					try {
						eval(l.embedded_js);
					} catch(err) { console.log('Ajax.get.embedded_js -> '+err); }
				} else {
					l.response = l.response[0];
				}			
			}
			
			try {
				if(typeof(l.onload) == 'function') {
					l.onload(l.response);
				}
			} catch (err) { console.log('Ajax.get.onload -> '+err); }
			
			try {
				if(typeof(callback) == 'function') {
					callback(l.response);
				}
			} catch (err) { console.log('Ajax.get.callback -> '+err+' -> '+l.response); }
		}
	}
    xmlhttp.send(null);
}
// global ajax handler
ajax = new Ajax();

function ajaxGet(url, callback) {
	ajax.get(url, callback);
}

function ajaxGet_embedjs(url, callback) {
	ajax.get(url, callback, { embed_js : true });
}