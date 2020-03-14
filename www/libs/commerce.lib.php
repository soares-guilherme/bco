<?php
#**************************************************************************#
#                  PluGzOne - Tecnologia com Inteligência                  #
#                             André Rutz Porto                             #
#                           andre@plugzone.com.br                          #
#                        http://www.plugzone.com.br                        #
#__________________________________________________________________________#

##########################################################################*/

class Commerce {

	public $cart;

	public $payment_prazo = '15'; // id do departamento do ecommerce

	public $vars;
	public $shipping_code = NULL;
	public $shipping_cep = NULL;
	public $shipping_type = NULL;
	public $shipping_cost = NULL;
	public $shipping_transit = NULL;
	public $shipping_address = NULL;
	public $payment_type = NULL;
	public $payment_type_param = NULL;
	public $client_id = NULL;

	function __construct()
		{
			if(!isset($_SESSION["COMMERCE"]))
				$_SESSION["COMMERCE"] = array();
	
			if(!isset($_SESSION['COMMERCE']['CART']))
				{
					$this->cart = array();
					$this->vars = (object) array();
					$this->shipping_address = (object) array();
					$this->payment_type = NULL;
					$this->payment_type_param = NULL;
					$this->vars->observacoes = NULL;
				}
			else
				{
					$this->load($this);
				}
		}

	function buy($id, $qnt=1)
		{
			if($qnt==0)
				return $this->unbuy($id);

			$this->cart[$id] = (object) array(
									'id' => $id,
									'qnt' => $qnt,
								);
		}

	function isincart($id)
		{
			if(isset($this->cart[$id]))
				return true;

			return false;
		}

	function prepare_shipping($code, $cep)
		{
			$this->shipping_code = $code;
			$this->shipping_cep = $cep;
		}

	function set_shipping($id, $cost, $transit)
		{
			if(!empty($id))
				{
					$this->shipping_type = $id;
					$this->shipping_cost = $cost;
					$this->shipping_transit = $transit;
				}
		}

	function set_delivery($destination)
		{
			$this->shipping_address = $destination;
		}

	function set_payment($id, $param=NULL)
		{
			$this->payment_type = $id;
			$this->payment_type_param = $param;
		}

	function set_client($id)
		{
			$this->client_id = $id;
		}
	
	function set_var($key, $value)
		{
			$this->vars->{$key} = $value;
		}
	
	function get_var($key)
		{
			$ret = NULL;
			
			if(isset($this->vars->{$key}))
				$ret = ($this->vars->{$key});
			
			return $ret;
		}

	function unbuy($id)
		{
			$this->cart[$id] = NULL;
			unset($this->cart[$id]);
		}

	function total()
		{
			return count($this->cart);
		}

	function save()
		{
			$_SESSION['COMMERCE']['CART'] = serialize($this->cart);
			$_SESSION['COMMERCE']['VARS'] = serialize($this->vars);

			$_SESSION['COMMERCE']['shipping_code'] = serialize($this->shipping_code);
			$_SESSION['COMMERCE']['shipping_cep'] = serialize($this->shipping_cep);
			$_SESSION['COMMERCE']['shipping_type'] = serialize($this->shipping_type);
			$_SESSION['COMMERCE']['shipping_cost'] = serialize($this->shipping_cost);
			$_SESSION['COMMERCE']['shipping_transit'] = serialize($this->shipping_transit);
			$_SESSION['COMMERCE']['shipping_address'] = serialize($this->shipping_address);
			$_SESSION['COMMERCE']['payment_type'] = serialize($this->payment_type);
			$_SESSION['COMMERCE']['payment_type_param'] = serialize($this->payment_type_param);
			$_SESSION['COMMERCE']['client_id'] = serialize($this->client_id);
		}

	function load($obj)
		{
			$obj->cart = unserialize($_SESSION['COMMERCE']['CART']);
			$obj->vars = unserialize($_SESSION['COMMERCE']['VARS']);
			
			$obj->shipping_code = unserialize($_SESSION['COMMERCE']['shipping_code']);
			$obj->shipping_cep = unserialize($_SESSION['COMMERCE']['shipping_cep']);
			$obj->shipping_type = unserialize($_SESSION['COMMERCE']['shipping_type']);
			$obj->shipping_cost = unserialize($_SESSION['COMMERCE']['shipping_cost']);
			$obj->shipping_transit = unserialize($_SESSION['COMMERCE']['shipping_transit']);
			$obj->shipping_address = unserialize($_SESSION['COMMERCE']['shipping_address']);
			$obj->payment_type = unserialize($_SESSION['COMMERCE']['payment_type']);
			$obj->payment_type_param = unserialize($_SESSION['COMMERCE']['payment_type_param']);
			$obj->client_id = unserialize($_SESSION['COMMERCE']['client_id']);
		}

	function p_save($con)
		{
			$this->save();

			$data = base64_encode(serialize($_SESSION['COMMERCE']));

			return bd_executa("INSERT INTO carrinhos (id_cliente, commerce, creation_date) VALUES ('".$this->client_id."', '".$data."', NOW())", $con);
		}

	function p_load($id, $con)
		{
			$q = bd_executa("SELECT commerce FROM carrinhos WHERE `id` = '".$id."'", $con);

			if($q->nada)
				return false;

			$_SESSION['COMMERCE'] = unserialize(base64_decode($q->res->rid0->commerce));

			$this->load();

			return true;
		}

	function checkout()
		{
			$this->save();
			
			$data = (object) array();
			
			$this->load($data);
			
			$z1 = new z1Service();
			
			$z1->request($this->key_ecommerce, 'Commerce', array('commerce' => serialize($data)));
			
			unset($_SESSION['COMMERCE']);

			$this->__construct();
			
			return $z1->reply['z1main']['reply']['venda'];
		}

	function __destruct()
		{
			$this->save();
		}

}

##########################################################################*/
?>