
// jquery
function $BindAll() {
	// windows
	$('.window').draggable( { handle: '.window_title', opacity : 0.75 } );
	$('.window_close').click( function () {
		$(this).parents('.window').css({ 'display' : 'none' });
	});
}
$(function () { 
	// rollover
	$('.rollover').hover( function() {
		if(typeof($(this).attr('hover_normal')) == 'undefined') {
			$(this).attr('hover_normal', $(this).attr('src'));
		}
		$(this).attr('src', $(this).attr('hover'));
	}, function() {
		$(this).attr('src', $(this).attr('hover_normal'));
	});
	// binding fix
	$BindAll(); 
});


// framework - z1.*

var z1 = {
	events : {
		onResize : []
	},
	timer_onResize : null,
	run_onResize : function() {
		
		for (fn in z1.events.onResize) {
			z1.events.onResize[fn]();
		}
		
	},
	onResizeReady : function() {
		
        var cached_width = window.innerWidth;
        
		z1.run_onResize();
		
		window.onresize = function() {
			
            //console.log(z1.events.onResize.join());
            
            if(cached_width == window.innerWidth){ return false; }
            
            cached_width = window.innerWidth;
            
			clearTimeout(z1.timer_onResize);
			z1.timer_onResize = setTimeout( z1.run_onResize(), 100);
		}
	},
	slideMenu : function (a_els, a_direction, a_offset, a_step, a_parent) {
		var l = this;
		
		l.els = a_els;
		
		l.ttl = 500;
		l.inc_in = 0;
		l.inc_out = 0;
		l.speed_in = 300;
		l.speed_out = 600;
		l.live = { };
		
		l.direction = 'right';
		l.offset = 0;
		l.step = 20;
		
		l.parent = null;
		
		if(typeof(a_direction) != 'undefined') {
			l.direction = a_direction;
		}
		if(typeof(a_offset) != 'undefined') {
			l.offset = a_offset;
		}
		if(typeof(a_step) != 'undefined') {
			l.step = a_step;
		}
		if(typeof(a_parent) != 'undefined') {
			l.parent = a_parent;
		}		
		
		// functions
		l.kill = function (id) { 
			window.setTimeout( function () {
				l.hide(id);
			}, l.ttl);
		}		
		l.keepalive = function (id, flag) { 
			l.live[id] = flag;
		}		
		l.show = function (id) { 
		
			l.keepalive(id, true);
			
			if($('#s'+id).css('display') == 'none') {
				$('#s'+id).css({ 'display' : 'inline' });
				$('#m'+id).addClass('activeMain');
				
				if(l.direction == 'right') {
					$('#s'+id).animate({ opacity: 1, left: "+" + l.inc_in }, l.speed_in, 'linear', function () {
							l.kill(id);
						}
					);
				} else if(l.direction == 'down') {
					$('#s'+id).animate({ opacity: 1, top: "+" + l.inc_in }, l.speed_in, 'linear', function () {
							l.kill(id);
						}
					);
				}
			}
		}
		l.hide = function (id) { 
			
			if(l.live[id] === true) {
				l.kill(id);
			} else if(l.live[id] === false) {
				
				$('#m'+id).removeClass('activeMain');

				if(l.direction == 'right') {
					$('#s'+id).animate({ opacity: 0, left: "+" + l.inc_out }, l.speed_out, 'linear', function () { 
							$('#s'+id).css({ 'display' : 'none' });	
						}
					);
				} else if(l.direction == 'down') {
					$('#s'+id).animate({ opacity: 0, top: "+" + l.inc_out }, l.speed_out, 'linear', function () { 
							$('#s'+id).css({ 'display' : 'none' });	
						}
					);
				}
			}
		}
		l.setup = function (id) {			
			l.keepalive(id, false);
			
			$('#m'+id).hover(
				function () { l.show(id); }, 
				function () { l.keepalive(id, false); }
			);	
			$('#m'+id+' a').hover(
				function () { l.show(id); }, 
				function () { /*l.keepalive(id, false);*/ }
			);	
			$('#s'+id).hover(
				function () { l.keepalive(id, true); }, 
				function () { l.keepalive(id, false); }
			);
			$('#m'+id).bind('touchleave touchend', function(event) {
				
				if(!event) {
					event = window.event;
				}
				
				if(l.live[id]) {
					l.keepalive(id, false);
				} else {
					l.show(id);
				}
				
				// new events
				$('#m'+id+',#s'+id).unbind('mouseenter mouseleave');
			});
		}
		
		// init
		l.inc_in = l.offset + l.step;
		l.inc_out = l.offset;
		
		for(i in l.els) {
			l.setup(l.els[i]);	
		}
	},
	// Paginador com animation slide
	SlidePager : function (holder_class, step, orientation, interval_timer, offsetpages, autorun, class_hover) {
		var l = this;
		
		if(typeof(autorun) != 'undefined') {
			l.autorun = autorun;
		} else {
			l.autorun = true;
		}
		
		if(typeof(orientation) != 'undefined') {
			l.orientation = orientation;
		} else {
			l.orientation = 'horizontal';
		}
		
		if(typeof(offsetpages) != 'undefined') {
			l.offsetpages = offsetpages;
		} else {
			l.offsetpages = 0;
		}
		
		if(typeof(interval_timer) != 'undefined' || interval_timer == null) {
			l.timer = parseInt(parseFloat(interval_timer)*1000);
			l.daemon_on = true;
		} else {
			l.timer = 0;
			l.daemon_on = false;
		}
		
		if(typeof(class_hover) == 'undefined') {
			l.class_hover = null;
		} else {
			l.class_hover = class_hover;
		}
		
		// methods
		l.goto = function (page_num, sender) {
			
			if(typeof(sender) != 'undefined') {
				if(sender == 'bullet' || sender == 'pagination') {
					l.daemon_on = false;
				}
				if(sender == 'script') {
					l.noanimation = true;
				}
			}
			
			if (page_num < 0) {
				/*page_num = 0;*/
				
				page_num = (l.num - l.offsetpages - 1);
			} else if (page_num >= (l.num - l.offsetpages)) {
				/*page_num = (l.num - l.offsetpages - 1);*/
				
				page_num = 0;
			}
			
			/*if (page_num == 0) {
				$(l.left_holder).css({visibility : 'hidden'});
				$(l.right_holder).css({visibility : 'visible'});
			} else if (page_num == (l.num - 1 - l.offsetpages)) {
				$(l.left_holder).css({visibility : 'visible'});
				$(l.right_holder).css({visibility : 'hidden'});
			} else {
				$(l.left_holder).css({visibility : 'visible'});
				$(l.right_holder).css({visibility : 'visible'});
			}*/
			
			if(l.num - l.offsetpages <= 1) {
				$(l.left_holder).css({visibility : 'hidden'});
				$(l.right_holder).css({visibility : 'hidden'});
			}
			
			l.page = page_num;
			
			if($(l.current_page).size() > 0) {
				$(l.current_page).text(l.page + 1);
			}
			if($(l.total_page).size() > 0) {
				$(l.total_page).text(l.num);
			}
			
			if(l.has_bullets) {
				if(l.class_hover !== null) {
					$(l.bullet_item).removeClass(l.class_hover);
					$(l.bullet_item+':eq('+ l.page +')').addClass(l.class_hover);
				} else {
					for(var j = 0; j < (l.num); j++) {
						$(l.bullet_img+':eq('+ j +')').attr({src : $(l.bullet_img+':eq('+ j +')').attr('hover_normal')});
					}
					
					$(l.bullet_img+':eq('+ l.page +')').attr({src : $(l.bullet_img+':eq('+ l.page +')').attr('hover')});
				}
			}
			
			if(l.noanimation) { // no animation	
				l.noanimation = false;
				if(l.orientation == 'vertical') {			
					$(l.pages_holder).css('marginTop', (l.step * (-1) * page_num).toString() + 'px');
				} else {
					$(l.pages_holder).css('marginLeft', (l.step * (-1) * page_num).toString() + 'px');
				}
			} else {
				if(l.orientation == 'vertical') {			
					$(l.pages_holder).animate({ marginTop : ((l.step * (-1) * page_num).toString() + 'px') }, 600);
				} else {
					$(l.pages_holder).animate({ marginLeft : ((l.step * (-1) * page_num).toString() + 'px') }, 600);
				}
			}
				
			if(l.timer > 0) {
				l.daemon_id = setTimeout(l.daemon, l.timer);
			}
			
			return true;
		};
		l.left = function () {
			return l.goto(parseInt(l.page)+1, 'pagination');
		};
		l.right = function () {
			return l.goto(parseInt(l.page)-1, 'pagination');
		};
		l.first = function () {
			return l.goto(0, 'pagination');
		};
		l.last = function () {
			return l.goto(parseInt(l.num)-1, 'pagination');
		};
		l.daemon = function () {
			if(l.daemon_on) {
				var page = parseInt(l.page)+1;
				if(page == l.num - l.offsetpages) {
					page = 0;
				}
				l.goto(page, 'daemon');
			}
		};
		l.destroy = function() {
			clearTimeout(l.daemon_id);
		}
		
		// initialization
		l.noanimation = false;
		l.holder = holder_class;
		l.step = step;
		l.page = 0;
		l.has_bullets = false;
		
		l.pages_holder = l.holder + ' .holder';
		l.left_holder = l.holder + ' .goto_left';
		l.right_holder = l.holder + ' .goto_right';
		l.first_holder = l.holder + ' .goto_first';
		l.last_holder = l.holder + ' .goto_last';
		l.bullet_item = l.holder + ' .bullet';
		l.bullet_img = l.holder + ' .bullet img';
		l.bullet_span = l.bullet_item + ' span';
		l.page_item = l.holder + ' .page';
		l.current_page = l.holder + ' .current_page';
		l.total_page = l.holder + ' .total_page';
		
        l.resize_pages_min = 1;
        l.resize_pages_medium = 1;
        l.resize_pages_max = 1;
        
		l.num = $(l.page_item).size();

		// bullets
		l.has_bullets = z1.InflateBullets(l);

		if(l.autorun) {
			l.goto(0);
		}

		// add events
		$(l.right_holder).click(l.left);
		$(l.left_holder).click(l.right);
		$(l.first_holder).click(l.first);
		$(l.last_holder).click(l.last);
		
		// touch navigation
		$(l.holder).bind('touchstart touchcancel touchleave touchend', function(event) {
			
			console.log('touch : '+l.holder);
			
			var delta = 0;
			
			if(!event) {
				event = window.event
			}
			
			if (event.type == 'touchstart') {
				$(this).data('touch-start-X', event.originalEvent.changedTouches[0].pageX);
			} else {
				delta = event.originalEvent.changedTouches[0].pageX - $(this).data('touch-start-X');
				
				if(delta > 30) {
					l.right();
				} else if(delta < -30) {
					l.left();
				}
			}
			
			return true;
		}); /**/
		
        // on resize
        if(typeof(l.step) == 'string') {
            l.step = l.step.split(',');
            
            //console.log(l.holder+' : '+l.step);
            
            l.resize_pages_min = l.step[0];
            l.resize_pages_medium = l.step[1];
            l.resize_pages_max = l.step[2];
        }
        
        z1.events.onResize.push(function () {
            var step_pages = 1;

            //console.log(l.holder+' : resize : '+l.resize_pages_max);
            
            if(step_pages > l.resize_pages_min) {
                step_pages = l.resize_pages_min;
            }

            l.destroy();

            // set stage
            var stage_width = 0;

            if ($(l.holder + ' .mask').size() <= 0) {
                stage_width = Math.ceil( $(l.holder)[0].getBoundingClientRect().width );
            } else {
                stage_width = Math.ceil( $(l.holder + ' .mask')[0].getBoundingClientRect().width );
            }

            // calc pages
            var page_width = $(l.page_item).outerWidth();

            if( $(window).width() > 1000 ) { // define for pages_max
                page_width = stage_width / l.resize_pages_max;
                //step_pages = pages_max;
            } else if( $(window).width() > 700 ) { // define for pages_medium
                page_width = stage_width / l.resize_pages_medium;
                //step_pages = pages_medium;
            } else {
                page_width = stage_width / l.resize_pages_min;
                //step_pages = pages_min;
            }

            var pages2show = 1;
            var page_margin = 0;
            var new_step = page_width;

            if(page_width < stage_width) {
                pages2show = Math.floor(stage_width / (page_width * 1));
                page_margin = ( (stage_width - pages2show * page_width ) / pages2show) / 2;
                //new_step = (page_width + (page_margin * 2)) * step_pages;
            } else {
                page_width = stage_width;
                //new_step = page_width * step_pages;
            }

            // set new info
            $(l.page_item).css({'width': page_width, 'marginLeft': page_margin, 'marginRight': page_margin});
            l.step = new_step;
            l.offsetpages = pages2show - step_pages;

            //l.num = Math.ceil(l.num / pages2show);

            l.goto(l.page, 'script');

            //console.log('resize : '+l.holder);
            
            // posiciona setas
            $(l.left_holder,l.right_holder).css({ marginTop : '-'+(($(l.page_item).outerHeight()/2)+35)+'px' });
        }); /**/
	},
	/****************************************************************
	 * Image Navigator - navega na imagem extragrande, com thumb.
	 */
	imageNav : function (image_holder, loading_el) {
		var l = this;
		
		l.holder = image_holder;
		l.win_size = getWinSize(window);
		
		l.event_mousemove = function (e) {
			
			l.t_l = $(l.thumb_holder).offset().left;
			l.t_t = $(l.thumb_holder).offset().top;
			
			l.t_w = $(l.thumb_holder).width();
			l.t_h = $(l.thumb_holder).height();
			
			l.z_w = $(l.zoom_holder).width();
			l.z_h = $(l.zoom_holder).height();
			
			l.m = l.z_w / l.t_w;
			
			l.pw = l.win_size.w / l.z_w;
			l.ph = l.win_size.h / l.z_h;
			
			l.lens_w = (l.pw * l.t_w);
			l.lens_h = (l.ph * l.t_h);
			
			l.x = e.pageX - l.t_l - (l.lens_w / 2);
			l.y = e.pageY - l.t_t - (l.lens_h / 2);
			
			if(l.x < 0) {
				l.x = 0;
			} else if(l.x > (l.t_w - l.lens_w)) {
				l.x = (l.t_w - l.lens_w);
			}
			
			if(l.y < 0) {
				l.y = 0;
			} else if(l.y > (l.t_h - l.lens_h)) {
				l.y = (l.t_h - l.lens_h);
			}			
			
			if($(l.thumb_holder).parent('div').find('div').size() > 0) {
				l.t_l += l.x;
				l.t_t += l.y;
				
				$(l.thumb_holder).parent('div').find('div').css('left', l.t_l.toString()+'px').css('top',  l.t_t.toString()+'px').css('display', '').css('width' , l.lens_w.toString()+'px').css('height', l.lens_h.toString()+'px');
			}
			
			$(l.zoom_holder).css({
								display:'', 
								position:'absolute'}).css('left', ((l.x * (-1) * l.m).toString() + 'px')).css('top', ((l.y * (-1) * l.m).toString() + 'px'));
		};
		
		$(l.holder).css({overflow : 'hidden', display : ''}).css('width', l.win_size.w.toString() + 'px' ).css('height', l.win_size.h.toString() + 'px' );
		
		l.thumb_holder = l.holder +" img:eq(1)";
		l.zoom_holder = l.holder +" img:eq(0)";
		
		$(l.thumb_holder).css({
							display:'', 
							position:'absolute',
							borderTop : '5px solid #666666', 
							borderLeft : '5px solid #666666', 
							bottom : '0', 
							right : '0'}).css('cursor', 'crosshair').mousemove( l.event_mousemove ).parent('div').find('div').mousemove( l.event_mousemove ).css('cursor', 'crosshair');
		
		if(typeof(loading_el) != 'undefined') {
			$(l.zoom_holder).load( function () {
				$(loading_el).css({display:'none'});
			} );
		}
	},
	SlideSwitch : function (holder_class, heigth_offset) {
		l = this;
		
		l.holder = holder_class;
		
		if(typeof(heigth_offset) != 'undefined') {
			l.h_offset = heigth_offset.toString();
		} else {
			l.h_offset = '0';
		}
		
		$(l.holder+' .toggle').click(function () {			
			$(l.holder+' .block').each( function () {
				if($(this).css('display') != 'none') {
					$(this).animate({height : l.h_offset}, 400, 'linear', function () {
						
						$(l.holder+' .block').each( function () {
							if($(this).css('display') == 'none') {								
								var cs = $(this).height() + 'px';
								$(this).css('display', '');
								$(this).css('height', l.h_offset);
								$(this).animate({height : cs}, 400);
							}
						} );
						
						$(this).css('display', 'none');
						$(this).css('height', 'auto');
					} );
				}
			} );				
		});
	},
	Expand : function (holder_class) {
		l = this;
		
		l.holder = holder_class;
		
		$(l.holder+' .expand').click(function () {

			if(typeof(l.on_expand) == 'function') {
				l.on_expand();
			}

			l.h = $(this).parents('.holder');
			l.h.find('.spoiler').css( { display : 'none' } );
			l.h.find('.full').css( { display : 'block' } );
			
			if(l.h.find('.full_animate').size() > 0) {
			
				var full_a = l.h.find('.full_animate');
				
				l.D_h = full_a.height();
				l.D_m = parseInt( full_a.css('marginBottom') ) * 1;
				if(isNaN(l.D_m)) {
					l.D_m = 0;
				}
				l.D_ms = l.D_h + l.D_m;
				
				full_a.css( { height : '0px', marginBottom : l.D_ms + 'px' } );
				full_a.animate( { height : l.D_h + 'px', marginBottom : l.D_m + 'px' }, 400, 'linear', function () {
					//fixCssLayout();
				} );

				if(typeof(l.after_expand) == 'function') {
					l.after_expand();
				}

				//fixCssLayout();
			}		
		});
		
		$(l.holder+' .hide').click(function () {
			l.h = $(this).parents('.holder');
		
			if(typeof(l.on_hide) == 'function') {
				l.on_hide();
			}

			if(l.h.find('.full_animate').size() > 0) {
				
				var D_h = l.h.find('.full_animate').height().toString() + 'px';
				var D_m = l.h.find('.full_animate').css('marginBottom').toString();
				var D_ms = ( noPx(D_h) + noPx(D_m) ).toString() + 'px';
				
				l.h.find('.full_animate').animate( { height : '0px', marginBottom : D_ms }, 400, 'linear', function () {
					$(this).css( { marginBottom : D_m } );
					l.h.find('.spoiler').css( { display : 'block' } );
					l.h.find('.full').css( { display : 'none' } );
					l.h.find('.full_animate').css( { height : 'auto' } );
					//fixCssLayout();
				} );
			}
				
		});
	},
	// Paginador com animation
	FadingPager : function (holder_class, a_autoplay) {
		
		var l = this;
		
		if(typeof(a_autoplay) != 'undefined') {
			l.autoplay = a_autoplay;
		} else {
			l.autoplay = true;
		}
				
		// methods
		l.goto = function (page) {
			
			if (page < 0) {
				return;
			} else if (page >= l.num) {
				return;
			} else if (page == l.page) {
				return;
			}
			
			l.page = page;
			
			$(l.tabs).removeClass(l.class_on);
			
			$(l.tabs + ':eq('+ page +')').addClass(l.class_on);
			
			$(l.pages).css({ opacity : '0', display : 'none' });
			
			$(l.pages + ':eq('+ page +')').css({ display : '' }).animate({ opacity : '1' }, 400, 'linear', function () { 
				fixCssLayout();
			} );
			
			if(typeof(l.onchange) == 'function') {
				l.onchange();
			}
			
			fixCssLayout(); 
		};
		
		// initialization
		l.holder = holder_class;
		l.page = null;
		
		l.class_on = 'activated';
		
		l.tabs = l.holder + ' .tab';
		l.pages = l.holder + ' .page';
		
		l.num = $(l.pages).size();
		
		var i = 0;
		
		$(l.tabs).each(function () {
			$(this).attr('tabIndex', i++);
		});
		
		$(l.tabs).click(function () {
			l.goto($(this).attr('tabIndex'));
		});
		
		if(l.autoplay) {
			l.goto(0);
		}
	},
    // Paginador com animation slide
    AnimationFading : function (holder_class, a_step, interval_timer, class_hover) {
        var l = this;
        
        if(typeof(interval_timer) != 'undefined') {
            l.timer = parseInt(parseFloat(interval_timer)*1000);
            l.daemon_on = true;
        } else {
            l.timer = 0;
            l.daemon_on = false;
        }
        
        if(typeof(class_hover) == 'undefined') {
            l.class_hover = null;
        } else {
            l.class_hover = class_hover;
        }
                
        // methods
        l.goto = function (page_num, sender) {
            
            if(typeof(sender) != 'undefined') {
                if(sender == 'bullet' || sender == 'pagination') {
                    l.daemon_on = false;
                }
            }
            
            if (page_num < 0) {
                return;
            } else if (page_num >= l.num) {
                return;
            } else if (page_num == l.page) {
                return;
            }
                        
            l.page = page_num;
            
            if(l.has_bullets) {
                if(l.class_hover !== null) {
                    $(l.bullet_item).removeClass(l.class_hover);
                    $(l.bullet_item+':eq('+ l.page +')').addClass(l.class_hover);
                } else {
                    for(var j = 0; j < (l.num); j++) {
                        $(l.bullet_img+':eq('+ j +')').attr({src : $(l.bullet_img+':eq('+ j +')').attr('hover_normal')});
                    }
                    
                    $(l.bullet_img+':eq('+ l.page +')').attr({src : $(l.bullet_img+':eq('+ l.page +')').attr('hover')});
                }
            }
            
            $(l.stage).animate({ opacity : 0 } , 500 , 'linear', function () {
                $(l.stage).css('marginTop', (l.page * l.step).toString() + 'px').animate({ opacity : 1 } , 800, 'linear', function () {
                    $(l.stage).css('filter', '');
                });
            } );
        };
        l.daemon = function () {
            if(l.daemon_on) {
                var page = parseInt(l.page)+1;
                if(page == l.num) {
                    page = 0;
                }
                l.goto(page, 'daemon');
            }
        };
        
        // initialization
        l.holder = holder_class;
        l.step = a_step;
        l.page = 0;
        l.has_bullets = false;
        
        l.stage = l.holder + ' .holder';
        l.page_item = l.holder + ' .page';
        l.bullet_item = l.holder + ' .bullet';
        l.bullet_img = l.bullet_item + ' img';
        l.bullet_span = l.bullet_item + ' span';
        
        l.num = $(l.page_item).size();
        
        // bullets
        l.has_bullets = z1.InflateBullets(l);
            
        if(l.timer > 0) {
            setInterval(l.daemon, l.timer);
        }
        
        l.goto(0);
    },
	// Paginador com animation slide
	MostraGaleria : function (holder_class, step) {
		var l = this;
		
		// methods
		l.open = function () {
			$(l.holder).css({ opacity : 0, display : 'block' }).animate({ opacity : 1 }, 800, 'linear', function(){
                z1.run_onResize();
                $.scrollLock( true );
            });
		};
		l.close = function () {
            $.scrollLock( false );
            $(l.holder).animate({ opacity : 0 }, 800, 'linear', function(){
                $(this).css('display', 'none');
            });
		};
        
		// initialization
        l.holder = holder_class;
		l.step = step;
        l.fechar = l.holder + ' .fechar';
		l.abrir = '.abre-mostra-galeria[data-holder="'+l.holder.slice(1)+'"]';
        
        // slider
        l.slider = new z1.SlidePager(l.holder, l.step, 'horizontal', null);
        l.slider_index = 0;
        
        // setup
        $(l.abrir).click(function(){
            l.slider_index = $(this).index();
            l.slider.goto(l.slider_index);
            l.open();
        });
        
        // add events
        $(l.fechar).click(l.close);
	},
    // slide box
    SlideBox_vars : {
        scrooll_compensation : 0
    },
    SlideBox : function (holder_class, orientation, scrooll_compasation) {
        var l = this;
        
        if(typeof(orientation) != 'undefined') {
            l.orientation = orientation;
        } else {
            l.orientation = 'right';
        }
        
        if(typeof(scrooll_compasation) != 'undefined') {
            l.scrooll_compensation = l.scrooll_compensation;
        } else {
            l.scrooll_compensation = z1.SlideBox_vars.scrooll_compensation;
        }
        
        // methods
        l.open = function () {
            console.log( 'l.scrooll_compensation : ' + l.scrooll_compensation );
        
            $(l.slide).stop().addClass('opened').animate({ right : '0%' }, 600, 'linear');
            $('html, body').animate({ scrollTop: ( $(l.slide).offset().top - l.scrooll_compensation ) }, 800);
        };
        l.close = function () {
            $(l.slide).stop().removeClass('opened').animate({ right : '100%' }, 600, 'linear');
        };
        l.toogle = function () {
            if($(l.slide).hasClass('opened')) {
                l.close();
            } else {
                l.open();
            }
        };
        
        // initialization
        l.holder = holder_class;
        l.slide = l.holder + ' .slide-box';
        l.trigger = l.holder + ' .slide-box-trigger';
        
        // add events
        $(l.trigger).click(function(){
            l.toogle();
        });
    },
	// ANIMATION SUPPORT FUNCIONS
	InflateBullets : function (l) {
		if($(l.bullet_item).size() > 0) {
            
            page_num = l.num;
            
			if(typeof(l.offsetpages) != 'undefined') {
                page_num = (l.num - l.offsetpages);
            }
            
			if($(l.bullet_item).size() == 1) { // inflate
			
				for(var i = 0; i < (page_num-1); i++) {
					$(l.bullet_item+':last').clone().insertAfter(l.bullet_item+':last');
				}
				
				for(var j = 0; j < (page_num); j++) {
					$(l.bullet_item+':eq('+ j +')').attr({bulletIndex : j});
					$(l.bullet_item+':eq('+ j +')').css({display : 'inline-block'});
					$(l.bullet_img+':eq('+ j +')').attr('hover_normal', $(l.bullet_img+':eq('+ j +')').attr('src'));
					
					$(l.bullet_item+':eq('+ j +')').click( function () { l.goto($(this).attr('bulletIndex'), 'bullet'); } );
				}
				
				if($(l.bullet_span).size() > 0) {			
					for(var k = 0; k < page_num; k++) {
						$(l.bullet_span+':eq('+ k +')').html(k+1);
					}
				}
				
			} else {
				
				$(l.bullet_item).css({display : 'none'});
				
				for(var j = 0; j < (page_num); j++) {
					$(l.bullet_item+':eq('+ j +')').attr({bulletIndex : j});
					$(l.bullet_item+':eq('+ j +')').css({display : ''});
					$(l.bullet_img+':eq('+ j +')').attr('hover_normal', $(l.bullet_img+':eq('+ j +')').attr('src'));
					
					$(l.bullet_item+':eq('+ j +')').click( function () { l.goto($(this).attr('bulletIndex'), 'bullet'); } );
				}
				
			}
			
			$(l.bullet_img+':first').attr('src', $(l.bullet_img+':first').attr('hover'));
			
			j=0;
			
			return true;
		}		
		return false;
	}
} // end of frame work - z1