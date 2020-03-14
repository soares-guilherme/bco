var old_cssjs_onload = window.onload;
var window_onload = function () {;};

function getE(id) {
	return document.getElementById(id);
}
function getElHeight(el) {
	try{
		h = el.style.pixelHeight;
		if(!isDefined(h) || h < 1) {
			h = el.offsetHeight;
		}
		if(!isDefined(h) || h < 1) {
			h = el.clientHeight;
		}
	} catch(e) {
		h = el.offsetHeight;
	}
	return h;
}
function getElWidth(el) {
	try{
		w = el.style.pixelWidth;
		
		if(!isDefined(w) || w < 1) {
			w = el.offsetWidth;
		}
		if(!isDefined(w) || w < 1) {
			w = el.clientWidth;
		}
	} catch(e) {
		w = el.offsetWidth;
	}
	return w;
}

function getElY(obj) {
	var y = 0;
	while (obj) {
		y += obj.offsetTop;
		obj = obj.offsetParent;
	}
	return(y);
}

function getElX(obj) {
	var x = 0;
	while (obj) {
		x += obj.offsetLeft;
		obj = obj.offsetParent;
	}
	return(x);
}
function getMouseXY(e) {
	if (!e) {
		var e = window.event;
	}
	x = e.clientX;
	y = e.clientY;

	return {'x':x,'y':y};
}
function returnfalse(e) { return false; }
function returntrue() { return true; }
function isIe() {
	if(navigator.appVersion.indexOf("MSIE")!=-1) { 
		return true;
	}
	return false;
}
var textsel_enabled = true;
function disableTextSel() {
	textsel_enabled = false;

	document.onselectstart=returnfalse;
	if (window.sidebar) {
		document.onmousedown=returnfalse;
		document.onclick=returntrue;
	}
}
function enableTextSel() {
	textsel_enabled = true;

	document.onselectstart=null;
	if (window.sidebar) {
		document.onmousedown=null;
		document.onclick=null;
	}
}
function noPx(v) {
	if(v) {
		return Math.round(v.substr(0, v.length-2));
	} else {
		return false;
	}
}

/* FIXES */
function getStyle( element, cssRule ) {
	if( document.defaultView && document.defaultView.getComputedStyle ) {
		var value = document.defaultView.getComputedStyle( element, '' ).getPropertyValue( cssRule );
	} else if ( element.currentStyle ) {
		var value = element.currentStyle[ cssRule ];
	} else {
		var value = false;
	}
	return value;
}

fixcss_events = [];
fixcss_run = false;
function fixCssLayout(e) {
	
	if(!fixcss_run) {
		fixcss_run = true;
		$('.hidemeOnload').css('display', 'none');
	}
	
	try{
		for (i in fixcss_events) {
			fixcss_events[i]();
		}
	} catch(err) { alert('Erro:'+err); }
	
	fixCssHeight();	
	fixCssWidth();

//	try{ old_cssjs_onload(e); }catch(e){;}
	try{ window_onload(e); }catch(e){;}
	try{ fixImgSpacing(); }catch(e){;}
	//try{ fixLeftMarginHiding(); }catch(e){;}
	
	try{ $(window).resize(function(){ 
		//fixLeftMarginHiding();
		fixCssHeight();	
		fixCssWidth();
	}); } catch(e) { ; }
}

var fixcss_w_ray = [];
var fixcss_w_ray_min = [];
var fixcss_ml_ray = [];

function fixCssWidth() {
	
		// inherit width fix
		for(i in fixcss_w_ray) {
			getE(i).style.width = getElWidth(getE(fixcss_w_ray[i])) + 'px';
		}
	
		// inherit width min fix
		for(i in fixcss_w_ray_min) {
			if(getElWidth(getE(i)) < getElWidth(getE(fixcss_w_ray_min[i]))) {
				getE(i).style.width = getElWidth(getE(fixcss_w_ray_min[i])) + 'px';
			}
		}

		// inherit margin-left fix
		for(i in fixcss_ml_ray) {
			getE(i).style.marginLeft = ( getElWidth(getE(fixcss_ml_ray[i])) / (-2) ) + 'px';
		}
	}

var fixcss_h_ray = [];
var fixcss_minh_ray = [];
var fixcss_h_ray_m = [];
var fixcss_flashloader = [];

function fixCssHeight() {
		// min height
		for(i in fixcss_minh_ray) {
			getE(i).style.height = null;
			
			if( getElHeight(getE(i)) < fixcss_minh_ray[i] ) {
				getE(i).style.height = fixcss_minh_ray[i] + 'px';
			}
		}

		// flash loader
		for(i in fixcss_flashloader) {
			getE(i).innerHTML = getE(fixcss_flashloader[i]).innerHTML;
		}

		// inherit width fix -- !!! always last
		for(i in fixcss_w_ray) {
			//if(getElHeight(getE(i)) <= getElHeight(getE(fixcss_h_ray[i])))
				$(getE(i)).width($(getE(fixcss_h_ray[i])).height());
		}

		// inherit height fix -- !!! always last
		for(i in fixcss_h_ray) {
			//if(getElHeight(getE(i)) <= getElHeight(getE(fixcss_h_ray[i])))
				getE(i).style.height = getElHeight(getE(fixcss_h_ray[i])) + 'px';
		}

		// inherit height fix + adjustment(m) -- !!! always last                
		for(i in fixcss_h_ray_m) {
            if(getElHeight(getE(i)) < getElHeight(getE(fixcss_h_ray_m[i][0])) + fixcss_h_ray_m[i][1]){
			    getE(i).style.height = (getElHeight(getE(fixcss_h_ray_m[i][0])) + fixcss_h_ray_m[i][1]) + 'px';
            }
		}
	}

function fixImgSpacing () {
	if(navigator.appName != 'Microsoft Internet Explorer') {						
		var here_imgs = document.getElementsByTagName('body').item(0).getElementsByTagName('img');
		for(i=0;i<here_imgs.length;i++) {
			try {
				here_imgs[i].style.marginLeft = here_imgs[i].getAttribute('hspace') + 'pt';
				here_imgs[i].style.marginRight = here_imgs[i].getAttribute('hspace') + 'pt';
			} catch(e) {}
			try {
				here_imgs[i].style.marginTop = here_imgs[i].getAttribute('vspace') + 'pt';
				here_imgs[i].style.marginBottom = here_imgs[i].getAttribute('vspace') + 'pt';
			} catch(e) {}
		}
	}
}

function fixImgMargin (id) {
	if(navigator.appName != 'Microsoft Internet Explorer') {						
		var here_imgs = document.getElementById(id).getElementsByTagName('img');
		for(i=0;i<here_imgs.length;i++) {
			try {
				here_imgs[i].style.marginLeft = here_imgs[i].getAttribute('hspace') + 'pt';
				here_imgs[i].style.marginRight = here_imgs[i].getAttribute('hspace') + 'pt';
			} catch(e) {}
			try {
				here_imgs[i].style.marginTop = here_imgs[i].getAttribute('vspace') + 'pt';
				here_imgs[i].style.marginBottom = here_imgs[i].getAttribute('vspace') + 'pt';
			} catch(e) {}
		}
	}
}

function getWinSize(winObj) { // retorna o tamanho da janela argumento
	var myWidth = 0, myHeight = 0;
	if( typeof( winObj.innerWidth ) == 'number' ) {
		myWidth = winObj.innerWidth;
		myHeight = winObj.innerHeight;
	} else if( winObj.document.documentElement && ( winObj.document.documentElement.clientWidth || winObj.document.documentElement.clientHeight ) ) {
		myWidth = winObj.document.documentElement.clientWidth;
		myHeight = winObj.document.documentElement.clientHeight;
	} else if( winObj.document.body && ( winObj.document.body.clientWidth || winObj.document.body.clientHeight ) ) {
		myWidth = winObj.document.body.clientWidth;
		myHeight = winObj.document.body.clientHeight;
	}
	return {'w':myWidth,'h':myHeight};
}

// FIX BROWSER WINDOW HIDING
fixLeftMarginHiding_flag = true;
fixLeftMarginHiding_status = 1;
function fixLeftMarginHiding() {
	if(!fixLeftMarginHiding_flag) {
		return false;
	}
	if(getE('Container').style.marginLeft && getE('Container').style.width) {
		var iwidth = noPx(getE('Container').style.width);
		var imargin = noPx(getE('Container').style.marginLeft);
	} else {
		var iwidth = noPx(getStyle(getE('Container'), 'width'));
		if(isIe()) {
			var imargin = noPx(getStyle(getE('Container'), 'marginLeft'));
		} else {
			var imargin = noPx(getStyle(getE('Container'), 'margin-left'));
		}
	}
	
	if(iwidth + 20 > getWinSize(window).w ) {
		if(imargin < 0) {
			getE('Container').style.marginLeft = 0;
			getE('Container').style.left = 0;
			if(getE('Fundo')) {
				getE('Fundo').style.width = iwidth + 'px';
			}
		}
	} else {
		if(noPx(getE('Container').style.leftMargin) == 0) {
			getE('Container').style.marginLeft = '-' + Math.round(iwidth / 2) + 'px';
			getE('Container').style.left = '50%';
			if(getE('Fundo')) {
				getE('Fundo').style.width = '100%';
			}
		}
	}
}

// FIX IMG HEIGHT MIDDLE
function fix_valign_middle(obj) {
		
	if($(obj)[0].naturalHeight > 0) {	
		
		var oh = getElHeight(obj);
		var ph = getElHeight(obj.parentNode);
		
		if(obj.parentNode.tagName == 'A' ) { 
			ph = getElHeight(obj.parentNode.parentNode);
		}
		
		if(ph != oh) {
			obj.style.marginTop = ((ph-oh)/2) + 'px';
		}
	} else {
		setTimeout(function() {
			fix_valign_middle(obj);
		}, 100);
	}
}
function fix_valign_middle_alt(obj) {
	
	var oh = getElHeight(obj);
	var ph = getElHeight(obj.parentNode);
	
	if(obj.parentNode.tagName == 'A' ) { 
		ph = getElHeight(obj.parentNode.parentNode);
	}
	
	if(ph != oh) {
		obj.style.marginTop = ((ph-oh)/2) + 'px';
	}
}

function preLoadImages() {
	var cache = [];
	var args_len = arguments.length;
	for (var i = args_len; i--;) {
	  var cacheImage = document.createElement('img');
	  cacheImage.src = arguments[i];
	  cache.push(cacheImage);
	}
}