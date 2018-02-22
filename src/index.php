<?php

require __DIR__ . '/../vendor/autoload.php';

use LearnositySdk\Request\Init;
use LearnositySdk\Utils\Uuid;

// global settings
$consumerKey = "yis0TYCu7U9V4o7M";
$consumerSecret = "74c5fd430cf1242a527f6223aebd42d30464be22";

// domain and timestamp for API signature

$domain = $_SERVER['SERVER_NAME'];
$timestamp = gmdate('Ymd-Hi');

$courseid   = 'flashcard_demo_' . $consumer_key;

if (!isset($_GET['user_id'])) {
	echo 'need user_id in query string';
	die();
}

if (!isset($_GET['language'])) {
	echo 'need language in query string';
	die();
}

$userId = $_GET['user_id'];

// set up the security for the key signing
$security = [
	'consumer_key' => $consumerKey,
	'domain' => $domain,
	'timestamp' => $timestamp
];

// define the items
$items = [];

// TODO - infer session ID from hash of user + language
$sessionId = Uuid::generate();

echo "lets go!";