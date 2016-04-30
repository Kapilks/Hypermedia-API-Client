<?php

	namespace kapilks\HyperMediaClient;
	

	include_once 'URITemplate.php';
	include_once 'HttpRequest.php';

	use kapilks\HyperMediaClient\HttpRequest;
	use kapilks\HyperMediaClient\URITemplate;
	use \ArrayAccess;
	use \Closure;
	

	/**
	* Client
	*/
	class Client implements ArrayAccess
	{
		private $url_;
		private $member_;
		private $attribute_;
		private $methods_;
		private $paginated_;
		private $responseHeaders_;
		private $paginatedArray_;


		function __construct($data)
		{
			$this->paginated_ = false;
			$this->attribute_ = array();
			$this->methods_ = array();

			if(is_array($data))
			{
				// Initialize through data we already have
				$this->member_ = $data;
				$this->loadNewState_();
			}
			else
			{
				// First get data from server then initialize
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
			
			//echo "$url\n";

			$req = new HttpRequest($this->url_);
			$req->send();
			$response = $req->getResponse();
			$this->responseHeaders_ = $req->getResponseHeaders();

			// Convert into associated array
			$this->member_ = json_decode($response, true);
			$this->loadNewState_();
		}
		

		private function loadNewState_()
		{
			// Set new class attribute and methods specific to this state
			$allAttribute = array();
			$allMethod = array();

			foreach($this->member_ as $key => $data) 
			{
				if($this->isAttribute_($key))
				{
					// Attribute
					$attributeName = $this->createMemberName_($key);

					// Recursively build object from array type
					if(is_array($data))
						$this->{$attributeName} = new Client($data);
					else
						$this->{$attributeName} = $data;

					array_push($allAttribute, $attributeName);
				}
				else
				{
					// Method
					$methodName = $this->createMemberName_($key);
					$methodBody = function() use ($data)
					{
						//echo "$data\n";

						$args = func_get_args()[0];
						$data = URITemplate::expand($data, $args);

						return new Client($data);
					};

					$allMethod[$methodName] = Closure::bind($methodBody, $this, get_class());
				}
			}

			$this->attribute_ = $allAttribute;
			$this->methods_ = $allMethod;
			
			$this->paginate_();

			//print_r($this->attribute_);
			//print_r($this->methods_);
		}


		private function paginate_()
		{
			if($this->isPaginationRequire_())
			{
				$this->paginated_ = true;
				$this->paginatedArray_ = array();
				foreach($this->attribute_ as $value) 
				{
					array_push($this->paginatedArray_, new Client($this->member_[$value]));
				}

				if(isset($this->responseHeaders_['Link']))
				{
					$linkHeader = explode(",", $this->responseHeaders_['Link']);
					foreach($linkHeader as $link) 
					{
						// Separate each url and their actions(like 'next', 'prev' etc.) 
						$match = array();
						preg_match("#<([^>]+)>; rel=\"([^\"]+)\"#", $link, $match);
						
						$target = $match[1];
						$action = $match[2];

						$methodBody = function() use ($target)
						{
							//echo "$target\n";

							$args = func_get_args()[0];
							$data = URITemplate::expand($target, $args);

							return new Client($data);
						};

						$this->methods_[$action] = Closure::bind($methodBody, $this, get_class());
					}
				}				
			}
		}


		private function isPaginationRequire_()
		{
			// Already paginated and have 'more' result than that can be displayed in 1 request
			if(isset($this->responseHeaders_['Link']))
				return true;

			// Not paginated and have results 'less' than that can be displayed in 1 request
			$limit = count($this->attribute_);
			
			// No attribute but may have actions
			if($limit == 0)
				return false;

			for($i = 0; $i < $limit; $i++)
				if($this->attribute_[$i] != $i)
					return false;

			return true;
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


		///////////////////////////////////////////////////////////////////////////
		// Public interface


		public function offsetSet($offset, $value)
		{
			// Setting a index is not allowed
		}


		public function offsetExists($offset)
		{
			return $this->paginated_ && isset($this->paginatedArray_[$offset]);
		}


		public function offsetUnset($offset)
		{

		}


		public function offsetGet($offset)
		{
			if($this->offsetExists($offset))
				return $this->paginatedArray_[$offset];

			return null;
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