<?php
	
	use kapilks\HyperMediaClient\Client;


	header("Content-Type: application/json");
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	require_once __DIR__. '/../vendor/autoload.php';

	$client = new Client("https://api.github.com/");

	$user = $client->user("kapilks");

	echo $user->name."\n";
	echo $user->location."\n";
	echo $user->email."\n";
	echo $user->publicRepos."\n";

	$first = $user->repos()[3];

	echo $first->name."\n";
	echo $first->owner->login."\n";

	$commit = $first->commits('4945bec');
	echo $commit->sha."\n";

	$committer = $commit->committer;
	echo $committer->login."\n";

	$detail = $commit->commit;
	echo $detail->message."\n";

?>