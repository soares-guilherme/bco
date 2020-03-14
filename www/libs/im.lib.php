<?php
##### Funчѕes de Imagens | im_* ##### EM DESENVOLVIMENTO!!! ################

function im_marcadagua($entrada, $marca, $saida, $pos = 3, $opac = 50)
	{
		$ext = strtolower(substr($marca, -3));

		if($ext=='jpg' or $ext=='peg')
			$marca_v = imagecreatefromjpeg($marca);
			
		elseif($ext=='png')
			$marca_v = imagecreatefrompng($marca);

		$entrada_v = imagecreatefromjpeg($entrada); 

		$entrada_w = imageSX($entrada_v); 
		$entrada_h = imageSY($entrada_v); 
		$marca_w = imageSX($marca_v); 
		$marca_h = imageSY($marca_v); 
	
		switch($pos)
			{
				case 0: //meio 
					$dest_x = ( $entrada_w / 2 ) - ( $marca_w / 2 ); 
					$dest_y = ( $entrada_h / 2 ) - ( $marca_h / 2 ); 
					break;
				case 1:  //topo esquerdo 
					$dest_x = 0; 
					$dest_y = 0; 
					break;
				case 2: //topo direito
					$dest_x = $entrada_w - $marca_w; 
					$dest_y = 0; 
					break;
				case 3: //base direita
					$dest_x = $entrada_w - $marca_w; 
					$dest_y = $entrada_h - $marca_h; 
					break;
				case 4: //base esquerda
					$dest_x = 0; 
					$dest_y = $entrada_h - $marca_h; 
					break;
				case 5: //topo no meio
					$dest_x = ( ( $entrada_w - $marca_w ) / 2 ); 
					$dest_y = 0; 
					break;
				case 6: //meio na direita
					$dest_x = $entrada_w - $marca_w; 
					$dest_y = ( $entrada_h / 2 ) - ( $marca_h / 2 ); 
					break;
				case 7: //base no meio
					$dest_x = ( ( $entrada_w - $marca_w ) / 2 ); 
					$dest_y = $entrada_h - $marca_h; 
					break;
				case 8: //meio na esquerda
					$dest_x = 0; 
					$dest_y = ( $entrada_h / 2 ) - ( $marca_h / 2 ); 
					break;
			}

		imagecopymerge($entrada_v, $marca_v, $dest_x, $dest_y, 0, 0, $marca_w, $marca_h, $opac);
		imagejpeg($entrada_v, $saida, 85);
		imagedestroy($entrada_v); //libera recurso do servidor deletando a imagem da memoria
	}

function im_redimensiona($entrada, $saida, $x, $y, $ext = '')
	{
		if($ext == '')
			$ext = strtolower(substr($entrada, -3));
		
		$ext = strtolower($ext);

		if($ext=="jpg" or $ext=="peg")
				$entrada_v = imagecreatefromjpeg($entrada);

		if($ext=="gif")
				$entrada_v = imagecreatefromgif($entrada);

		elseif($ext=="png")
				$entrada_v = imagecreatefrompng($entrada);
			
		$x1 = imagesx($entrada_v); //pega o tamanho horizontal da imagem
		$y1 = imagesy($entrada_v); //pega o tamanho vertical da imagem
		
		if(($x1 / $y1) >= ($x / $y))
			$y = floor($y1 / ($x1 / $x));
		else
			$x = floor($x1 / ($y1 / $y));
		
		$entrada_d = imagecreatetruecolor($x, $y); //cria imagem q servira de "area de trabalho"
		imagecopyresampled($entrada_d, $entrada_v, 0, 0, 0, 0, $x, $y, $x1, $y1); //cria a imagem redimensionada
		imagejpeg($entrada_d, $saida, 85); //transforma em jpg
		imagedestroy($entrada_d); //libera recurso do servidor deletando a imagem da memoria
		imagedestroy($entrada_v); //idem
	}

# nova funчуo !!!!
# serve para dar um CROP numa imagem bufferizando para uma string
function im_cropBuff($entrada, $x, $y, $w, $h, $ext = '')
	{
		if($ext == '')
			$ext = strtolower(substr($entrada, -3));

		if($ext=="jpg" or $ext=="peg")
				$entrada_v = imagecreatefromjpeg($entrada);

		elseif($ext=="png")
				$entrada_v = imagecreatefrompng($entrada);
			
		$x1 = imagesx($entrada_v); //pega o tamanho horizontal da imagem
		$y1 = imagesy($entrada_v); //pega o tamanho vertical da imagem
				
		$entrada_d = imagecreatetruecolor($w, $h); //cria imagem q servira de "area de trabalho"
		imagecopy($entrada_d, $entrada_v, 0, 0, 0, 0, $x, $y, $w, $y); //cria a imagem redimensionada
		ob_start(); // abre leitura da imagem
		imagejpeg($entrada_d); //transforma em jpg
		$buff = ob_get_contents(); //pega o conteudo da imagem
		ob_end_clean(); //acaba com a leitura da img
		imagedestroy($entrada_d); //libera recurso do servidor deletando a imagem da memoria
		imagedestroy($entrada_v); //idem
		return $buff;
	}

############################################################################
?>