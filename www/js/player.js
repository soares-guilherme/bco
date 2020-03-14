//PluGzOne's Javasript Player v1.0
soundManager = new SoundManager();
Player = {
	//ajax : new Ajax(),
	//sm : new SoundManager(),
	autoplay : true,
	print_playlist : true,
	print_playerstatus : true,
	playlist_holder : 'Playlist',
	playerstatus_holder : 'Playerstatus',
	
	playlist : [],
	playlist_songnames : [],
	i : 0,
	current : 1,
	current_duration : '',
	current_time : '',
	current_daemon : '',
	initiated : false,
	loaded : false,
	playing : false,
	init : function () { 
		if(!this.initiated) {
			this.initiated = true;
			soundManager.url = '/player/swf/';
			soundManager.debugMode = window.debugging;
			soundManager.onload = function() { 
				Player.loaded = true;
				
				window.setInterval(Player.daemon, 1000);
				
				for ( var k in Player.playlist ) {
					Player.i++;
					soundManager.createSound( { 
						id : ('s' + Player.i.toString()) ,
						url : Player.playlist[k],
						onfinish : function () { Player.next(); }
					} );
				}
				
				if( Player.autoplay ) {
					Player.play();
				}
				
				if( Player.print_playlist ) {
					Player.print();
				}
			};
			Player.init();
		}
	},
	play : function () {
		this.init();
		if( this.current > this.i || this.current < 1 ) { 
			this.current = 1;
		}
		soundManager.stopAll( );
		soundManager.play( 's'+this.current.toString() );
		this.playing = true;
		this.print();
	},
	playById : function (id) {
		this.current = parseInt(id);
		this.play();
	},
	pause : function () {
		soundManager.pause( 's'+this.current.toString() );
		this.playing = false;
	},
	playPause : function () {
		if ( this.playing ){
			this.pause();
		}else{
			if ( soundManager.getSoundById( 's'+this.current.toString() ).paused ){
				this.resume();
			}else{
				this.play();
			}
		}
	},
	resume : function () {
		soundManager.resume( 's'+this.current.toString() );
		this.playing = true;
	},
	stop : function () {
		soundManager.stopAll( );
		this.playing = false;
	},
	next : function () {
		this.current++;
		this.play();
	},
	previous : function () {
		this.current--;
		this.play();
	},
	toggleMute : function () {
		soundManager.toggleMute();
	},
	daemon : function () {					
		if(Player.playing) {
			var time = new Date( soundManager.getSoundById( 's'+Player.current.toString() ).durationEstimate );
			Player.current_duration = time.getMinutes() + ':' + zero_fill(time.getSeconds().toString(), 2);
			
			time = new Date( soundManager.getSoundById( 's'+Player.current.toString() ).position );
			Player.current_time = time.getMinutes() + ':' + zero_fill(time.getSeconds().toString(), 2);
					
			Player.current_daemon = Player.current_time + ' / ' + Player.current_duration;
		
			if( Player.print_playerstatus ) {
				getE(Player.playerstatus_holder).innerHTML = Player.current_daemon;
			}
		}
	},
	print : function () {
		var output = '';
		var c=1;
		var holder = getE(Player.playlist_holder);
		
		try {
			
			if(holder.innerHTML == '') {
			
				for ( var k in this.playlist ) { 
					id = c++;
					output = output + '<p class="playlist_normal" onclick="Player.playById(\''+ id +'\');" id="playlist_item_'+ id +'">'+ k +'</p>';
				}
				
				holder.innerHTML = output;
			}
			
			var els = holder.getElementsByTagName('p');
			
			for(var i_els=0; i_els < els.length ; i_els++) {
				els[i_els].className = 'playlist_normal';
			}
			
			getE('playlist_item_' + this.current.toString()).className = 'playlist_current';
			
		} catch(err) { }
		
	}
	
	/*loading : function () {
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
*/
} 