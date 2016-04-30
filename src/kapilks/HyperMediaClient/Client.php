<?php 
	
	include_once 'URITemplate.php';
	include_once 'HttpRequest.php';

	/**
	* Client
	*/
	class Client
	{
		private $url_;
		private $member_;
		private $attribute_;
		private $methods_;
		private $paginated_;


		function __construct($data)
		{
			$this->attribute_ = array();
			$this->methods_ = array();

			if(is_array($data))
			{
				$this->member_ = $data;
				$this->loadNewState_();
			}
			else
			{
				$this->loadNewStateUrl_($data);
			}
		}

		
		function __call($method, $args)
		{
			if(isset($this->methods_[$method]))
				return call_user_func($this->methods_[$method], $args);
			else
				echo "'$method' action is not defined in current object.";
		}

		private function loadNewStateUrl_($url)
		{
			$this->url_ = $url;
		
			$req = new HttpRequest($this->url_);
			$req->send();
			$response = $req->getResponse();

			//print_r($req->getResponseHeaders());
			// Convert into associated array
			$this->member_ = json_decode($response, true);

			$this->loadNewState_();
		}
		
		private function loadNewState_()
		{
			// Set new class attribute and methods specific to this state
			$allAttribute = array();
			$allMethod = array();
			foreach ($this->member_ as $key => $data) 
			{
				if($this->isAttribute_($key))
				{
					// Attribute
					$attributeName = $this->createMemberName_($key);
					$this->{$attributeName} = $data;

					array_push($allAttribute, $attributeName);
				}
				else
				{
					// Method
					$methodName = $this->createMemberName_($key);
					$methodBody = function() use ($data)
					{
						$args = func_get_args()[0];
						$data = URITemplate::expand($data, $args);

						return new Client($data);
					};

					$allMethod[$methodName] = \Closure::bind($methodBody, $this, get_class());
				}
			}

			$this->attribute_ = $allAttribute;
			$this->methods_ = $allMethod;

			//print_r($this->attribute_);
			//print_r($this->methods_);
		}

		private function isAttribute_($name)
		{
			return strpos($name, "_url") === false;
		}

		private function createMemberName_($name)
		{
			if(!($this->isAttribute_($name)))
				$name = substr($name, 0, strpos($name, "_url"));
			
			$name = str_replace("_", " ", $name);
			$name = ucwords($name);
			$name = lcfirst(str_replace(" ", "", $name));

			return $name;
		}

		public function getAttributes()
		{
			return $this->attribute_;
		}

		public function getActions()
		{
			return array_keys($this->methods_);
		}
	}

?>