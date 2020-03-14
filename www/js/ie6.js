if(navigator.userAgent.toLowerCase().indexOf('msie 6') != -1) {
	window.ie6go = function () {
		window.location.assign('http://ie6.plugzone.com.br');
	}
	var doc=document;
	doc.write('<script type="text/javascript" src="http://ie6.plugzone.com.br/ie6.php"></script>');
}