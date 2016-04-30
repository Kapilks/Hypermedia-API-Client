<?php
	
	namespace kapilks\HyperMediaClient;

	
	/**
	*	Class to send GET request to server 
	*/
	class HttpRequest
	{
		private $url_;
		private $responseBody_;
		private $channel_;
		private $requestHeaders_;
		private $responseHeaders_;


		const HEADER_DELIMITER = "\r\n";
		

		function __construct($url)
		{
			$this->url_ = $url;
			$this->channel_ = curl_init();

			// to send the Referer header on Redirection
			curl_setopt($this->channel_, CURLOPT_AUTOREFERER, true);

			// to follow any redirection
			curl_setopt($this->channel_, CURLOPT_FOLLOWLOCATION, true);

			// not to display the output
			curl_setopt($this->channel_, CURLOPT_RETURNTRANSFER, true);
			
			// verify the SSL certificate of server
			curl_setopt($this->channel_, CURLOPT_SSL_VERIFYPEER, false);
			
			// header in response
			curl_setopt($this->channel_, CURLOPT_HEADER, true);

			// to track the request headers
			curl_setopt($this->channel_, CURLINFO_HEADER_OUT, true);

			// Accept - Encoding header ( accepting all encoding type )
			curl_setopt( $this->channel_, CURLOPT_ENCODING, "" );

			// User - Agent header (my username)
			curl_setopt($this->channel_, CURLOPT_USERAGENT, "kapilks");
		
			// set the request url
			curl_setopt($this->channel_, CURLOPT_URL, $this->url_);

			// GET
			curl_setopt($this->channel_, CURLOPT_HTTPGET, true);
		}

		

		public function send()
		{
			$response = curl_exec($this->channel_);
			$info = curl_getinfo($this->channel_);

			// Header firt followed by response body
			$header_size = $info['header_size'];
			$header = substr($response, 0, $header_size);
			$this->responseBody_ = substr($response, $header_size);

			$this->populateRequestHeaders_($info['request_header']);
			$this->populateResponseHeaders_($header);

			curl_close($this->channel_);
		}


		public function getResponse()
		{
			return $this->responseBody_;
		}


		public function getRequestHeaders()
		{
			return $this->requestHeaders_;
		}


		public function getResponseHeaders()
		{
			return $this->responseHeaders_;
		}


		private function populateRequestHeaders_($headers)
 		{
 			$this->requestHeaders_ = $this->extractHeaders_($headers);
 		}

 		
 		private function populateResponseHeaders_($headers)
 		{
			if(curl_getinfo($this->channel_)['redirect_count'] > 0)
			{
				$headers = substr($headers, strrpos($headers, "HTTP")); 
			}

			$this->responseHeaders_ = $this->extractHeaders_($headers);
 		}


 		private function extractHeaders_($headers)
 		{
 			$headers = explode(self::HEADER_DELIMITER, $headers);
 			$headersCount = count($headers);
 			$headerArray = array();

 			for($i = 0; $i < $headersCount - 2 /* last two are empty line*/; $i++)
 			{
 				$parts = explode(": ", $headers[$i]);
 				
 				if($i === 0)
 				{
 					$parts[1] = $parts[0];
 					$parts[0] = "main";
 				}
 				
 				$headerArray[$parts[0]] = $parts[1];
 			}

 			return $headerArray;
 		}
	}

?>