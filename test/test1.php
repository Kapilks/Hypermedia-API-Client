<?php
	
	use kapilks\HyperMediaClient\Client;


	// From assignment
	header("Content-Type: application/json");
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	require_once __DIR__. '/../vendor/autoload.php';

	$client = new Client("https://api.github.com/");

	$user = $client->user('captn3m0');
	$repos = $user->repos();

	$firstRepo = $repos[0];

	// Extra lines
	echo $firstRepo->language."\n";
	echo $firstRepo->description."\n";
	
	$tags = $firstRepo->tags();
	
	$issue = $firstRepo->issues(1);
	echo $issue->title."\n";
	echo $issue->body."\n";

?>