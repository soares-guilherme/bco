<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligъncia                  #
#                             Andrщ Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

############################################################################

#\_ Funчуo para enviar Dados por HTTP
function httpRequest($host, $path='/', $data=NULL, $headers=array())
    {    	
		$new_line = "\r\n";
		$new_line_len = strlen($new_line);
				
		if($data != NULL)
	        $method = 'POST';
		else
			$method = 'GET';
		
		if(is_array($data) or is_object($data))
			{
				$new_data = '';
				
				foreach($data as $k => $e)
					{
						$new_data .= $k.'='.urlencode($e).'&';
					}
				
				$data = substr($new_data, 0, -1);
			}
		unset($k, $e, $new_data);
		
		if(substr($host, 0, 8) == 'https://')
			$host = substr($host, 8);
			
		if(substr($host, 0, 7) == 'http://')
			$host = substr($host, 7);
		
		$sock = @fsockopen($host, 80, $errorn, $errorstr);
		
		if (!$sock)
			return false;
		
		$requestHeader = $method." ".$path."  HTTP/1.1".$new_line;
		$requestHeader.= "Host: ".$host.$new_line;
		$requestHeader.= "Accept: */*".$new_line;
		$requestHeader.= "User-Agent: z1panel/PluGzOne".$new_line;
		
		if ($method == "POST")
			{
				$requestHeader.= "Content-Length: ".strlen($data).$new_line;
				$requestHeader.= "Content-Type: application/x-www-form-urlencoded".$new_line;
			}
		
		foreach($headers as $h)
			$requestHeader.= $h.$new_line;
		
		$requestHeader.= $new_line;
		
		if ($method == "POST")
			$requestHeader.= $data;
		
		fwrite($sock, $requestHeader);	
		
		$output = '';
		
		while (!feof($sock)) {
			$reply = fgets($sock, 128);
	
			if($reply !== false)
				$output .= $reply;
		}
	
		fclose($sock);
		
		if(strpos($output, 'Transfer-Encoding: chunked') !== false)
			{
				$chunk_data = substr($output, strpos($output, $new_line.$new_line)+$new_line_len+$new_line_len);
				
				$chunk_all = '';
				
				$chunk_size = 0;
				
				do {
					$end_of_line = strpos($chunk_data, $new_line);
					
					if($end_of_line !== false)
						{							
							$chunk_size = substr($chunk_data, 0, $end_of_line);
							
							$comment = strpos($chunk_size, ";");
							
							if($comment !== false)
								$chunk_size = substr($chunk_size, 0, $comment);
							
							$chunk_size = hexdec($chunk_size);
							
							$chunk_all .= substr($chunk_data, $end_of_line+$new_line_len, $chunk_size);
							
							$chunk_data = substr($chunk_data, $end_of_line+$new_line_len+$chunk_size+$new_line_len);
						}
					
				} while($chunk_size != 0);
				
				$output = substr($output, 0, strpos($output, $new_line.$new_line)+$new_line_len+$new_line_len).$chunk_all;
			}

		return $output;
	}

#\_ Realiza uma requisiчуo SSL sem verificaчуo nenhuma / opчуo de host falso
function httpsRequest($host, $path='/', $data=NULL, $headers=array(), $nolocation=false)
	{
    	$odata = $data;
    	$oheaders = $headers;
    	
		if(substr($host, 0, 4) != 'http')
			$uri = 'https://'.$host.$path;
		else
			$uri = $host.$path;
		
		if(empty($path))
			$host = substr($uri, 0, strpos($uri, '/', 9));
		
		if(!function_exists('curl_init'))
			return httpRequest($host, $path, $data, $headers);
		
		if(is_array($data) or is_object($data))
			{
				$new_data = '';
				
				foreach($data as $k => $e)
					{
						$new_data .= $k.'='.urlencode($e).'&';
					}
				
				$data = substr($new_data, 0, -1);
			}
		unset($k, $e, $new_data);
		
		$curl = curl_init();
		
		$new_headers = array();
		
		if(is_array($headers))
			{				
				foreach($headers as $k => $e)
					{
						if(is_int($k))
							$new_headers[] = $e;
						else
							$new_headers[] = $k.': '.$e;
					}
			}
		
		$new_headers[] = 'User-Agent: z1panel/PluGzOne';
		
		$headers = $new_headers;
		
		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
		
		if($data != NULL)
			{
				curl_setopt ($curl, CURLOPT_POST, true);
				curl_setopt ($curl, CURLOPT_POSTFIELDS, $data);
			}
		
		$output = curl_exec($curl);
		
		// debug
		if(defined('HTTPS_REQUEST_DEBUG'))
			{
				log_do("HTTPS_REQUEST:\n Uri -> ".$uri."\n Host -> ".$host."\n Headers Sent -> ".print_r(curl_getinfo($curl, CURLINFO_HEADER_OUT),1));
				
				log_do("HTTPS_RESPONSE:\n ".$output);
			}
		
		curl_close($curl);
		
		if(!$nolocation)
			{
				$data = httpReplyBody($output, true);
				
				if( ($pos = strpos($data[0], 'Location:')) !== false)
					{
						$to = trim( substr($data[0], $pos+9, strpos($data[0], "\n", $pos)-$pos-9) );
						
						if(substr($to, 0, 4) != 'http')
							$to = $host.$to;
						
						return httpsRequest($to, '', $odata, $oheaders);
					}
			}
		
		return $output;
	}

#\_ Realiza uma requisiчуo SSL sem verificaчуo nenhuma / opчуo de host falso
function httpRequestCurl($host, $path='/', $data=NULL, $headers=array())
	{
		if(substr($host, 0, 5) == 'https')
			$host = substr($host, 8);
		elseif(substr($host, 0, 4) != 'http')
			$host = 'http://'.$host;
		
		return httpsRequest($host, $path, $data, $headers);
	}

#\_ Funчуo para retornar apenas body da reply
function httpReplyBody($str, $headertoo=false)
	{
		$header = array();
		$body = array();
		
		$str = str_replace("\r\n", "\n", $str);
		
		$tmp = explode("\n\n", $str);
		
		foreach($tmp as $t)
			{
				if(strtoupper(substr($t, 0, 5)) == 'HTTP ' or strtoupper(substr($t, 0, 5)) == 'HTTP/')
					{
						$header[] = $t;
					}
				else
					{
						$body[] = $t;
					}
			}
		
		$header = implode("\n\n", $header);
		$body = implode("\n\n", $body);
		
		if($headertoo)
			{
				return array($header, $body);
			}
		else
			{
				return $body;
			}
	}

#\_ Request de webservice z1panel


#\_ Funчуo de Webservice
class z1Service
	{
		function __construct()
			{
				$this->host = substr(URL_Z1PANEL, 0, -1);
				
				$this->error = false;
				
				$this->key = NULL;
				$this->do = NULL;
				$this->mode = 'xml'; // xml, html, raw
			}
		
		function send($data)
			{
				$this->request(NULL, NULL, $data);
			}
		
		function request($key=NULL, $do=NULL, $data=NULL)
			{
				if($key==NULL)
					$key = $this->key;
					
				if($do==NULL)
					$do = $this->do;
				
				if($key == NULL or $do == NULL)
					return false;
				
				$host = $this->host;
								
				try {
					$this->raw_reply = call_user_func('httpsRequest', $host, '/Service/'.$key.'/'.$do, $data, array());
				} catch (Exception $err) { 
					$this->error = true;
					$this->error_message = __('Erro em %s',$fn);
					
					return false;
				}
				
				$pos = strpos($this->raw_reply, "\r\n\r\n");
				
				if($pos === false)
					{
						$this->error = true;
						$this->error_message = 'Erro em raw_reply';
						
						return false;
					}
				
				$this->raw_reply_header = substr($this->raw_reply, 0, $pos);
				$this->raw_reply_body = substr($this->raw_reply, $pos+strlen("\r\n\r\n"));
				
				if(strpos($this->raw_reply_header, 'z1panelError: true') !== false)
					{
						$this->error = true;
						$this->error_message = 'Erro no serviчo, mensagem : "'.strip_tags($this->raw_reply_body).'"';
						
						return false;
					}
				
				if($this->mode == 'xml')
					{
						$this->reply = new XMLPARSER($this->raw_reply);
						$this->reply = $this->reply->parse();
					}
				
				return true;
			}
	}
	
############################################################################
?>