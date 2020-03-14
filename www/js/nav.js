function setT(f, t) {
	setTimeout(f, t);
}
function goTo (uri) {
	window.location.assign(uri);
	return true;
}
function urlencode(str) {
	return escape(str).replace(/\+/g, '%2B').replace(/\"/g,'%22').replace(/\'/g, '%27').replace(/\//g,'%2F');
}
function abreJanela(uri, nome, w, h) {
	return window.open(uri, nome, 'width='+w+',height='+h+',dependent=no,scrolling=no,scrollbars=no,toolbar=no,location=no,status=no,menubar=no');
}

/*function AbreImgClassic(uri) {
	var siz_w = z1img_siz_w;
	var siz_h = z1img_siz_h;

	var win = abreJanela(z1img_win+escape(ByDeaDz1(uri)), 'Z1IMGZOOM', siz_w, siz_h);
	win.moveTo(screen.width/2 - siz_w/2, screen.height/2 - siz_h/2 - 50);
	win.focus();
}*/
function AbreAoVivo() {
	var siz_w = aovivo_siz_w;
	var siz_h = aovivo_siz_h;

	var win = abreJanela(url_aovivo, 'AoVivo', siz_w, siz_h);
	win.moveTo(screen.width - siz_w, screen.height - siz_h - 50);
	win.focus();
}
function AbreImg(uri) {
	MostraGaleria.Open(z1img_win+escape(ByDeaDz1(uri)));
}
function AbreVideo(id_video) {
	MostraGaleria.Open(url_mvid+id_video);
}
function AbreAEmpresa() {
	MostraGaleria.Open(url_maemp);
}
function AbreLocal(id) {
	MostraGaleria.Open(url_mlocal+id);
}
function AbreMateria(mid) {
    console.log(url_mmateria+mid);
	MostraGaleria.Open(url_mmateria+mid);
}
function AbreCidade(mid) {
	MostraGaleria.Open(url_mcidade+mid);
}

function proccessHashData(hashi) {
	var obj = {};
	var sc = hashi.indexOf(':');
	
	if(sc > 0) {
		obj.fn = hashi.substring(1, sc);
		obj.arg = hashi.substr(sc+1);
		obj.args = [];
		if(obj.arg.indexOf(':') > 0) {
			obj.args = obj.arg.split(':');
		}
	} else {
		obj.fn = hashi.substr(1);
		obj.arg = '';
		obj.args = [];
	}

	return obj;
}

MostraGaleria = {
	initiated : false,
	ready : true,
	ajax : new Ajax(),
	Open : function (uri) {
		try {
			if(this.ready) {
				this.init();
				this.ajax.embed_js = true;
				this.ajax.get(uri);
				this.loading();
			}
		} catch(err) { }
	},
	Close : function () {
		$('.MostraFillBoxImg iframe').remove();
		 $('.MostraGaleria').fadeOut(400, function () {
			$('.MostraGaleriaArea').html('');
		});
		
		
		this.initiated = false;
		this.ready = true;
	},
	init : function () {
		if(this.initiated) {
			return;
		}
		this.initiated = true;

		$('.MostraGaleria').mouseup(function(event) {
			var el = $(event.target);
			if(el.hasClass('MostraGaleria') ||
				el.hasClass('MostraGaleriaHolder') ||
				el.hasClass('MostraGaleriaInner') ||
				el.hasClass('MostraGaleriaArea') ||
				el.hasClass('MostraGaleriaFundo') ||
				el.hasClass('MostraImagem')) {
				if(typeof(event.target.ontouchstart) == 'undefined') {
					MostraGaleria.Close();
				}
			}
		});

		var c_h = $('#Container').height();

		if($(window).height() > c_h) {
			c_h = $(window).height();
		}

		$('.MostraGaleriaFundo').fadeTo(0, '0.8', function () {
			$('.MostraGaleria').fadeIn(0).height(c_h);
			$('.MostraGaleriaFundo').height(c_h);
		});
	},
	loading : function () {
        
		$('.MostraGaleriaLoading').show();
		$('.MostraGaleriaArea a').hide();
		$('.MostraGaleriaArea').fadeTo(300, '0.5');

		this.ready = false;
	},
	loaded : function () {
		$('.MostraGaleriaLoading').hide();
		$('.MostraGaleriaArea').show().fadeTo(200, '1');
		
		if($.browser.msie && $.browser.version <= 7) {
			$('.MostraGaleriaArea').width( $('.MostraGaleriaArea .img img').width() );
		}

		this.ready = true;
	},
	fixes : [],
	runFixes : function () {
		for(var i = 0; i < this.fixes.length; i++) {
			this.fixes[i]();
		}
	}
}
MostraGaleria.ajax.onload = function (response) {
	$('.MostraGaleriaArea').fadeTo(400, '0', function () {
		$('.MostraGaleriaArea').html(response);

		if($('.MostraImg img').size() > 0) {
			$('.MostraImg img').load(function () {
				
				// key navigation
				$(document).keyup(function(el) {
					if (el.keyCode == 27) {
						MostraGaleria.Close();
					}
					if (el.keyCode == 37) {
						if($('.MostraGaleria #Anterior').attr('ref') != 'javascript:void(null);') {
							MostraGaleria.Open( $('.MostraGaleria #Anterior').attr('ref') );
						}
					}
					if (el.keyCode == 39) {
						if($('.MostraGaleria #Proximo').attr('ref') != 'javascript:void(null);') {
							MostraGaleria.Open( $('.MostraGaleria #Proximo').attr('ref') );
						}
					}
				});
				
				// touch navigation
				$('.MostraGaleria').bind('touchstart touchcancel touchleave touchend', function(event) {
					
					var delta = 0;
					
					if(!event) {
						event = window.event
					}
					
					if (event.type == 'touchstart') {
						$(this).data('touch-start-X', event.originalEvent.changedTouches[0].pageX);
					} else {
						delta = event.originalEvent.changedTouches[0].pageX - $(this).data('touch-start-X');
						
						if(delta > 20) {
							if($('.MostraGaleria #Anterior').attr('ref') != 'javascript:void(null);') {
								MostraGaleria.Open( $('.MostraGaleria #Anterior').attr('ref') );
							}
						} else if(delta < -20) {
							if($('.MostraGaleria #Proximo').attr('ref') != 'javascript:void(null);') {
								MostraGaleria.Open( $('.MostraGaleria #Proximo').attr('ref') );
							}
						}
					}
					
					return true;
				});
				
				fix_valign_middle(this);
				
				MostraGaleria.loaded();
				
				if($('.ThumbsGaleria').size() > 0) {
					$ThumbsGaleria = $('.ThumbsGaleria');
					try { window.SPMostraGaleria.destroy(); } catch(err) { ; };
					try { 
						window.SPMostraGaleria = new z1.SlidePager('.ThumbsGaleria', $ThumbsGaleria.data('page-width'), 'horizontal', 5, $ThumbsGaleria.data('page-offset'), false);
						window.SPMostraGaleria.daemon_on = true;
						window.SPMostraGaleria.goto($('.MostraImagem .aPag').text()-1, 'script');
					} catch(err) { ; };
				}
			});
		} else {
			MostraGaleria.loaded();
		}
		
		MostraGaleria.runFixes();
	});
}

function AbreLocalizacao(uri) {
	var siz_w = (parseInt(z1img_siz_w) + 50);
	var siz_h = z1img_siz_h;

	var win = abreJanela(uri, 'LOCALIZACAO', siz_w, siz_h);
	win.moveTo(screen.width/2 - siz_w/2, screen.height/2 - siz_h/2 - 50);
	win.focus();
}

function AbreProduto(uri) {
	var siz_w = 650;
	var siz_h = 550;

	var win = abreJanela(uri, 'PRODUTO', siz_w, siz_h);
	win.moveTo(screen.width/2 - siz_w/2, screen.height/2 - siz_h/2 - 50);
	win.focus();
}

function ByDeaDz1(n) {
	n = n.substr(0, n.lastIndexOf('_')) + '_' + z1img_size + n.substr(n.lastIndexOf('.'));
	n = n.split('/');
	return n.pop();
}

var showEnviaAmigo_content = '';
function showEnviaAmigo () {
	$('html,body').animate({'scrollTop': '0'}, 300);

	if(showEnviaAmigo_content == '') {
		showEnviaAmigo_content = $('.EnviaAmigo:first').html();
		$('.EnviaAmigo:first').html('');
	}

	var htmlTag = document.createElement("div");
	htmlTag.setAttribute('id', 'EnviaAmigo');
	htmlTag.setAttribute('class', 'EnviaAmigo window');
	htmlTag.style.display = 'none';
	htmlTag.innerHTML = showEnviaAmigo_content;
	document.getElementsByTagName("body").item(0).appendChild(htmlTag);

	var win = getE('EnviaAmigo');
	if(win.style.display == 'none') {
		getE('amigo_remetente').value = '';
		getE('amigo_nome').value = '';
		getE('amigo_email').value = '';
		getE('amigo_msg').value = '';
		win.style.display = '';
		getE('amigo_remetente').focus();
	} else {
		win.style.display = 'none';
	}

	try { $BindAll(); } catch(err) { }
}

function doEnviaAmigo () {
	var uri = z1pup_url;
	var r = getE('amigo_remetente').value;
	var n = getE('amigo_nome').value;
	var e = getE('amigo_email').value;
	var m = getE('amigo_msg').value;
	var s = getE('amigo_submit');

	s.value = 'Enviando ...';

	if(r == '' || n == '' || e == '') {
		z1Alert("Você deve preencher todos os campos, exceto o campo \"Mensagem\" que &eacute; opcional.");
		s.value = 'Enviar';
		return false;
	}

	uri += 'r=' + escape(r) + '&n=' + escape(n) + '&e=' + escape(e) + '&m=' + escape(m);

	ajax.get(uri, function (response) {
		if(response.substr(0,2) == 'OK') {
			z1Alert("Página enviada para seu amigo com sucesso, preencha o formulário novamente para enviar a outro amigo.");
			s.value = 'Enviar';
			getE('amigo_remetente').value = '';
			getE('amigo_nome').value = '';
			getE('amigo_email').value = '';
			getE('amigo_msg').value = '';
			return true;
		} else {
			z1Alert(response);
			s.value = 'Enviar';
			return false;
		}

	});
}

// NAVEGABILIDADE
var min_fontsize = 8;
var max_fontsize = 14;
function changeFontSize(op) {
	current_fontsize = current_fontsize + default_fontsize/10*op;
	if(current_fontsize >= min_fontsize) {
		if(current_fontsize <= max_fontsize) {
			document.body.style.fontSize = current_fontsize + 'px';
			fixCssHeight();
			if(op != 0) {
				ajaxGet(url_fontsize + escape(current_fontsize), '' );
				if(isIe()) {
					window.location.assign(window.location.href);
				}
			}
		} else {
			current_fontsize = max_fontsize;
		}
	} else {
		current_fontsize = min_fontsize;
	}
}

function showImprime() {
	var newloc = window.location.href;
	if(newloc.indexOf('=') > -1) {
		newloc = newloc+'&imprime=true';
	} else {
		if(newloc.charAt(newloc.length-1) == '/') {
			newloc = newloc+'imprime=true';
		} else {
			newloc = newloc+'/imprime=true';
		}
	}

	var win = window.open(newloc, 'IMPRIME', 'width='+600+',height='+500+',dependent=no,scrolling=yes,scrollbars=yes,toolbar=no,location=no,status=no,menubar=no');
	win.moveTo(screen.width/2 - 600/2, screen.height/2 - 500/2 - 50);
	win.focus();
}

/* FORMS */
function resetForm (id) {
	var el = getE(id).getElementsByTagName('input');
	for(i in el)
		el[i].value = "";
	el = getE(id).getElementsByTagName('textarea');
	for(i in el)
		el[i].value = "";
}

function CheckUncheck(id) {
	if(getE(id).disabled != true && getE(id).disabled != 'disabled') {
		if(getE(id).checked == true)
			getE(id).checked = true;
		else
			getE(id).checked = true;
	}
	try { getE(id).onchange(); } catch(err) { ; } ;
}

function isChecked(el) {
	if(el.checked == true) {
		return true;
	}
	if(el.checked == 'checked') {
		return true;
	}
	if(el.checked == 'true') {
		return true;
	}
	if(el.checked == 'yes') {
		return true;
	}

	return false;
}

/* FRONT-END */

Produtos = {
	ajax : new Ajax(),
	Voltar : function () {
		try {
			this.loading();
			this.ajax.get('/Ajax/do=produtos&direction=left&reference=' + getE('ProdutosReferenceArea').innerHTML );
		} catch(err) { }
	},
	Avancar : function () {
		try {
			this.loading();
			this.ajax.get('/Ajax/do=produtos&direction=right&reference=' + getE('ProdutosReferenceArea').innerHTML );
		} catch(err) { }
	},
	loading : function () {
		getE('ProdutosLoadingArea').style.display = '';
		getE('ProdutosArea').style.display = 'none';
		getE('ProdutosBotaoVoltar').style.display = 'none';
		getE('ProdutosBotaoAvancar').style.display = 'none';
	},
	loaded : function () {
		getE('ProdutosLoadingArea').style.display = 'none';
		getE('ProdutosArea').style.display = '';
		fadeIn(getE('ProdutosArea'), 0);
		getE('ProdutosBotaoVoltar').style.display = '';
		getE('ProdutosBotaoAvancar').style.display = '';
	}
}
Produtos.ajax.embed_js = false;
Produtos.ajax.onload = function (response) {
	getE('ProdutosArea').innerHTML = response;
	Produtos.loaded();
}

Galeria = {
	ajax : new Ajax(),
	direction : 'left',
	Voltar : function () {
		this.loading();
		this.direction = 'left';
		this.ajax.get('/Ajax/do=galeria&direction=left&reference=' + getE('GaleriaReferenceArea').innerHTML );
	},
	Avancar : function () {
		this.loading();
		this.direction = 'right';
		this.ajax.get('/Ajax/do=galeria&direction=right&reference=' + getE('GaleriaReferenceArea').innerHTML );
	},
	loading : function () {
		getE('GaleriaLoadingArea').style.display = '';
		getE('GaleriaBotaoVoltar').style.display = 'none';
		getE('GaleriaBotaoAvancar').style.display = 'none';
	},
	loaded : function () {
		getE('GaleriaLoadingArea').style.display = 'none';

		move_smooth_horizontal_onload = function () {

			getE('GaleriaBotaoVoltar').style.display = '';
			getE('GaleriaBotaoAvancar').style.display = '';

			getE('GaleriaArea').innerHTML = getE('GaleriaAjaxAreaL').innerHTML;
			getE('GaleriaScrollArea').style.left = '-482px';

		};
		if(this.direction == 'left') {
			move_smooth_horizontal(getE('GaleriaScrollArea'), -229, -458);
		} else {
			move_smooth_horizontal(getE('GaleriaScrollArea'), -229, 0);
		}
	}
}
Galeria.ajax.onload = function (response) {
	getE('GaleriaAjaxAreaL').innerHTML = response;
	getE('GaleriaAjaxAreaR').innerHTML = response;
	Galeria.loaded();
}

Calendario = {
	ready_to_load : true,
	response : null,
	ajax : new Ajax(),
	Voltar : function (uri) {
		try {
			this.loading();
			this.ajax.get(uri);
		} catch(err) { }
	},
	Avancar : function (uri) {
		try {
			this.loading();
			this.ajax.get(uri);
		} catch(err) { }
	},
	loading : function () {
		this.ready_to_load = false;
		$('#CalendarioHolder .CalendarioSetas a').css({ visibility : 'hidden' });
		$('#CalendarioHolder').css({ opacity : '1' });
		$('#CalendarioHolder').animate({ opacity : '0' }, 400, 'linear', function () {
			$('#CalendarioCarregando').css({ display: '' });
			Calendario.ready_to_load = true;
		} );
	},
	loaded : function () {
		if(!this.ready_to_load) {
			setTimeout( function () { Calendario.loaded(); }, 50);
			return;
		}
		getE('CalendarioHolder').innerHTML = Calendario.response;
		$('#CalendarioCarregando').css({ display:'none' });
		$('#CalendarioHolder').css({ opacity : '0'});
		$('#CalendarioHolder').animate({ opacity : '1'}, 400 );
		setTimeout( function () {
			$('#CalendarioHolder .CalendarioSetas a').css({ visibility : 'visible' });
		}, 400);
		fixCssLayout();
	},
	fix : function () {
		$('#CalendarioEventos').css('height', ( getElHeight(getE('CalendarioInner')) - getElHeight(getE('CalendarioPrint')) ) + 'px' );
	}
}
Calendario.ajax.onload = function (response) {
	Calendario.response = response;
	Calendario.loaded();
}

function MostraMateria(ref, link) {
    var holder = '.Bg'+ref;
    var stage = $(holder+' .BoxPagina');
    var loading = $(holder+' .Loadeando');
    var ini_h = $(holder+' .BoxPagina').height();

    if( typeof(link) == 'undefined' || link == '' ) {
        
        if( !$(holder).hasClass('opened') ) {
            $('a[data-materia-ref='+ref+'][data-materia-trigger=main]').addClass('opened');
            
            $(holder).addClass('opened').stop().animate({ height : ini_h }, 1600, 'linear');
            $(stage).stop().animate({ opacity : 1 }, 1600, 'linear');
        } else {
            $(holder).removeClass('opened').stop().animate({ height : 0 }, 1000, 'linear');
            
            $('a[data-materia-ref='+ref+'][data-materia-trigger=main]').removeClass('opened');
        }
        
        return true;
        
    } else {
        
        var l_h = $(loading).height();
        
        if($(holder).height() < l_h) {
            $(holder).stop().animate({ height : l_h }, 600);
        }
        
        $(loading).show();

        $(holder).removeClass('opened');
        $(stage).stop().animate({ opacity : 0 }, 600);
        
        $('html,body').animate({'scrollTop': $('#'+ref).offset().top }, 800);
        
        $.get(link+'/ajax=true').done(function(data){
            
            setTimeout(function(){
                $(holder+' .ContentMostraPagina').html(data);
                
                var imgs_to_load = $('.ContentMostraPagina img').size();
                
                if(imgs_to_load > 0) {
                    
                    $('.ContentMostraPagina img').load(function () {
                        
                        imgs_to_load--;
                        
                        if(imgs_to_load == 0) { 
                            MostraMateria(ref);
                            $(loading).hide();
                        }
                    });
                    
                } else {
                    MostraMateria(ref);
                    $(loading).hide();
                }
            }, 800);
        });
        
    }
}

function scroll_To(top){
    $('html,body').animate({'scrollTop': top}, 300);
}

$(document).ready(function(){
    // abre paginas
    $('a[data-materia-ref]').click(function(){
        var ref = $(this).attr('data-materia-ref');
        var link = $(this).attr('data-materia-link');
        MostraMateria(ref, link);
    });
    
    //scroll
    $(".scrollToAnchor").on('click', function(event) {

        var hash = $(this).attr('href').replace(/^.*?(#|$)/,'');

        if(hash != "") {
            
            hash = '#' + hash;
            
            var perform = function() {
                if($(hash).size() > 0) {
            		event.preventDefault();
                    $('html, body').animate({ scrollTop: $(hash).offset().top }, 800, function(){
                        window.location.hash = hash;
                    });
                }
            }

            perform();
        }
    });
} );
