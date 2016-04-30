<?php
	
	require_once __DIR__. '/../vendor/autoload.php';

	use kapilks\HyperMediaClient\Client;


	$client = new Client("https://api.github.com/");
	print_r($client->getActions());

?>