<?php

	use kapilks\HyperMediaClient\Client;


	//Tests pagination
	header("Content-Type: application/json");
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	require_once __DIR__. '/../vendor/autoload.php';

	$client = new Client("https://api.github.com/");

	$user = $client->user("Microsoft");
	$repo = $user->repos();

	echo $repo[0]->name."\n";
	echo $repo[0]->description."\n";

	$repo = $repo->next();

	echo $repo[0]->name."\n";
	echo $repo[0]->description."\n";

	$repo = $repo->last();

	echo $repo[0]->name."\n";
	echo $repo[0]->description."\n";

?>