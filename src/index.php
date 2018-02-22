<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';

use LearnositySdk\Request\Init;
use LearnositySdk\Utils\Uuid;

$domain = $_SERVER['SERVER_NAME'];

$courseId   = 'flashcard_demo_' . $consumer_key;

if (!isset($_GET['user_id'])) {
	echo 'need user_id in query string';
	die();
}

if (!isset($_GET['lang'])) {
	echo 'need lang in query string';
	die();
}

$userId = $_GET['user_id'];
$languageShortLabel = $_GET['lang'];

$languageConfig = getLanguageConfig($languageShortLabel);

// set up the security for the key signing
$security = generateSecurity($userId, $domain);

// $sessionId = Uuid::generate();
$sessionId = generateSessionId($language, $userId);
$activityId = Uuid::generate(); // TODO(cera) - do we need to infer this too?
$uniqueResponseIdSuffix = Uuid::generate();

// define the items
$items = [
	[
		'reference' => 'item1',
		'content' => '<span class="learnosity-response question-' . $uniqueResponseIdSuffix. '_Flash1"></span>',
		'response_ids' => [
			$uniqueResponseIdSuffix.'_Flash1'
		]
	]
];

$questions = [
	[
	    'type' => 'custom',
	    'custom_type' => 'flashcard',
	    'response_id' => $uniqueResponseIdSuffix.'_Flash1',
	    'js' => '//localhost:8080/question/flash-card.js',
	    'custom_type' => 'Flash Card',
	    'stimulus' => 'Cerveza',
	    'valid_response' => 'Beer'
	]
];

$name = 'Learnosity LRN ' . $languageConfig['name'];

$request = [
	'name' => $name,
	'state' => 'initial',
	'title' => 'Learnosity Flash Cards',
	'subtitle' => 'Language: ' . $languageConfig['name'],
	'navigation' => [],
	'time' => [],
	'regions' => 'items-only',
	'configuration' => [
		'questionsApiVersion' => 'v2',
	],
	'items' => $items,
	'questionsApiActivity' => [
		'type' => 'submit_practice',
		'state' => 'initial',
		'id' => 'hi', // TODO(cera) - what the heck is this?
		'name' => $name,
		'course_id' => $courseId,
		'session_id' => $sessionId,
		'questions' => $questions,
	]
];

$init = new Init('assess', $security, getConsumerSecret(), $request);
$signedRequest = $init->generate();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Learnosity Flash Cards</title>
    <link rel="stylesheet" href="question/flash-card.css" />
</head>
<body>

<div class="intro-container"></div>
<div class="hackday-assess"></div>

<script src="https://assess-au.learnosity.com"></script>
<script>
    var eventOptions = {
            readyListener: function () {
                console.log('flash card app is ready');
            },
            errorListener: function (err) {
               console.log('flash card app error: ' + JSON.stringify(err));
               debugger;
            }
        },
        assessApp = LearnosityAssess.init(<?php echo $signedRequest; ?>, '.hackday-assess', eventOptions);
</script>

</body>
</html>

