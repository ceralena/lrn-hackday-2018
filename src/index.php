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

$courseId   = 'flashcard_demo_' . $consumer_key;

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
	'timestamp' => $timestamp,
	'user_id' => $userId
];


// TODO - infer session ID from hash of user + language
$sessionId = Uuid::generate();
$uniqueResponseIdSuffix = Uuid::generate();

// define the items
$items = [
	[
		'reference' => 'item1',
		'content' => '<span class="learnosity-response question-' . $uniqueResponseIdSuffix._Flash1 . '"></span>',
		'response_ids' => [
			$uniqueResponseIdSuffix.'_Flash1'
		]
	]
];

$request = [
	'name' => 'Learnosity LRN ' . $language,
	'state' => 'initial',
	'title' => 'Hello',
	'subtitle' => 'ok',
	'navigation' => [],
	'time' => [],
	'regions' => 'main',
	'configuration' => [
		'questionsApiVersion' => 'v2',
	],
	'items' => $items,
	'questionsApiActivity' => [
		'type' => 'submit_practice',
		'state' => 'initial',
		'id' => 'hi', // TODO
		'name' => 'Hello Again',
		'course_id' => $courseId,
		'session_id' => $sessionId,

		'questions' => [
			[
			    "stimulus" => "<p>[This is the stem.]</p>",
			    "type" => "longtextV2",
			    'response_id' => $uniqueResponseIdSuffix.'_Flash1'
			]
		]
	],

	// 'user_id' => $userId,
	// 'rendering_type' => 'inline',

	// 'activity_id' => 'lrn_hackday2018_flashcard_' . $language,
	// 'session_id' => $sessionId,
	
	// 'type' => 'submit_practice',
	// 'questionsApiActivity' => [
	// 	'consumer_key' => $consumerKey,
	// 	'timestamp' => $timestamp,
	// 	'signature' => 
	// ]
	// 'config' => [
	// 	'renderSubmitButton' => true, // TODO - maybe not?
	// ],
];

$init = new Init('assess', $security, $consumerSecret, $request);
$signedRequest = $init->generate();

?>

<div class='hackday-assess'>
</div>

<script src="https://assess-au.learnosity.com"></script>
<script>
    var eventOptions = {
            readyListener: function () {
                console.log('Learnosity Assess API is ready');
            }
        },
        assessApp = LearnosityAssess.init(<?php echo $signedRequest; ?>, '.hackday-assess', eventOptions);
</script>