<?php
namespace core\classes;

class httprequest
{
    private $url;
	private $http = array();
	
    private $data = '';
    private $header = '';
    private $context = '';
    
    function __construct($url)
    {
        if (!is_null($url))
        {
            $this->url = $url;
        }
    }
    
    public function __call($name, $arguments)
    {
        $name = strtoupper($name);
        if (!in_array($name,array('POST','GET','PUT','DELETE','DEL')))
        {
            throw new \Exception('La fonction ' . $name . ' n\'existe pas');
        }
 
        $this->http['method'] = $name;
        if($this->context == '')
		{
			$this->context = "Content-type: application/x-www-form-urlencoded\r\n";
		}
        
		return $this->request();
    }
    
    public function set_data($data)
    {
		$this->context = "Content-type: application/octet-stream\r\n";
        $this->data = $data;
    }
	    
    public function set_json_data($data)
    {
		$this->context = "Content-type: application/json\r\n";
        $this->data = json_encode($data);
    }
	
	public function set_xml_data($data)
    {
		$this->context = "Content-type: application/xml\r\n";
        $this->data = $data;
    }

    public function set_header($header)
    {
        $this->header = trim($header, RN) . RN;
    }

    public function add_header($header)
    {
        $this->header .= $header . RN;
    }

    public function request($data=null)
    {
        $this->http['content'] = $this->data;
        $this->http['header'] = $this->context . $this->header . RN;

        $this->http['ignore_errors'] = true;
		
		$params	 = array('http' => $this->http,"ssl"=>array("verify_peer"=>false, "verify_peer_name"=>false,));
		\HTML::print_r($this->data);
		try
		{
			$fp	 = @fopen( $this->url, 'rb', false, stream_context_create($params));
			
			$response = new \stdClass();
			$response->infos = $http_response_header;
			
			$response->values = $this->read_header($http_response_header);
			$response->values->headers = $http_response_header;
			$response->values->data = null;
			
			if ($fp)
			{
				if ($data = stream_get_contents($fp))
				{
					
					if (substr($response->values->code,0,1) == '2')
					{
						$response->values->brutedata = $data;
						$response->values->type = explode(';',$response->values->type);
						if(isset($response->values->type[1]))
						{
							$response->values->charset = $response->values->type[1];
						}
						else
						{
							$response->values->charset = '';
						}
						
						$response->values->type = $response->values->type[0];

						switch($response->values->type)
						{
							case 'application/xml' : 	libxml_use_internal_errors(true);
														$response->values->data = simplexml_load_string($data); 
														if(!empty(libxml_get_errors()))
														{
															$response->values->data = $data; 
														}
														break;
							case 'application/json' : $response->values->data = json_decode($data); break;
							default : $response->values->data = $data;break;
						}
					}
					else
					{
						$response->values->data = $data;
					}
				}
			}
			return $response->values;
		}
		catch (\Exception $e)
		{
			echo 'Exception: ' . $e->getMessage() . '<br>';
			return null;
		}
	}
	
	function read_header($tab)
	{
		$ret = new \stdClass();
		$ret->code = 0;
		$ret->length = 0;
		$ret->type = '';

		foreach ($tab as $ligne)
		{
			if(substr($ligne, 0, 4) == 'HTTP')
			{
				$ret->code = substr($ligne, 9, 3);
			}
			elseif(substr($ligne, 0, 16) == 'Content-Length: ')
			{
				$ret->length = substr($ligne, 16);
			}
			elseif(substr($ligne, 0, 14) == 'Content-Type: ')
			{
				$ret->type = substr($ligne, 14);
			}
		}
		return $ret;
	}
}