<?php
	
	class URITemplate
	{
		private static $glueChar = array("?" => "&", 
			"+" => ",", 
			"&" => "&", 
			"#" => ",", 
			"/" => "/", 
			";" => "&");

		public static function expand($uri, $param)
		{
			//print_r($uri);
			//print_r($param);

			$paramCount = count($param);
			$len = strlen($uri);

			// Extract all the expression from uri
			$expresion = array();
			$start = 0;
			for($i = 0; $i < $len; $i++) 
			{ 
				if($uri[$i] == '{')
					$start = $i;
				else if($uri[$i] == '}')
					array_push($expresion, array($start, $i));
			}

			$result = "";
			$idx = 0;
			$start = 0;
			$end = -1;
			foreach($expresion as $exp) 
			{
				$start = $exp[0];
				$result .= substr($uri, $end + 1, $start - $end - 1);
				$end = $exp[1];
				$part = "";

				$glue = ',';
				$startChar = $uri[$start + 1];
				if($startChar == '?' || $startChar == '+' || $startChar == '&' || $startChar == '#' ||
					$startChar == '/' || $startChar == ';')
				{
					$glue = URITemplate::$glueChar[$startChar];
					$start++;
				}

				$withoutBraces = substr($uri, $start + 1, $end - $start - 1);
				$withoutBraces = explode(",", $withoutBraces);
				$limit = count($withoutBraces);
				
				if($startChar == '?' || $startChar == '&' || $startChar == ';')
				{
					for($i = 0; $i < $limit && $idx < $paramCount; $i++, $idx++)
						$withoutBraces[$i] = $withoutBraces[$i] . "=" . $param[$idx];
				}
				else
				{
					for($i = 0; $i < $limit && $idx < $paramCount; $i++, $idx++)
						$withoutBraces[$i] = $param[$idx];
				}

				if($idx != $paramCount)
					echo "Invalid parameters";

				$part = implode($glue, $withoutBraces);
				$part = (($startChar != '?' && $startChar != '&' && $startChar != '#' && $startChar != '/' && $startChar != ';')? '' : $startChar) . $part;

				$result .= $part;
			}

			$result .= substr($uri, $end + 1);

			return $result;
		}
	}

?>