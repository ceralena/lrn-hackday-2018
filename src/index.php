<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/data.php';

use LearnositySdk\Request\Init;
use LearnositySdk\Utils\Uuid;

$wordsCount = 10;

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

$languageConfig = getLanguageConfig($_GET['lang']);

// set up the security for the key signing
$security = generateSecurity($userId, $domain);

$sessionId = generateSessionId($language, $userId);
$activityId = Uuid::generate(); // TODO(cera) - do we need to infer this too?

$uniqueResponseIdSuffix = Uuid::generate();

$languageData = getItemsQuestions($languageConfig['shortCode'], $uniqueResponseIdSuffix, $wordsCount);

// define the items
$items = $languageData['items'];
$questions = $languageData['questions'];

$name = 'Learnosity LRN ' . $languageConfig['name'];

$request = [
	'name' => $name,
	'state' => 'initial',
	'title' => 'Learnosity Flash Cards',
	'subtitle' => 'Language: ' . $languageConfig['name'],
    'navigation' => [
        'show_intro' => false,
    ],
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

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Learnosity Flash Cards</title>
<link rel="stylesheet" href="question/flash-card.css" />
</head>
<body>

<div class="intro-container">
    <div class="language-selection">Japanese</div>
    <div class="language-selection">Spanish</div>
    <div class="language-selection">Arabic</div>
</div>

<div class="hackday-assess"></div>

<script src="https://assess-au.learnosity.com"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    var eventOptions = {
        readyListener: function () {
            console.log('flash card app is ready');
        },
        errorListener: function (err) {
           console.log('flash card app error: ' + JSON.stringify(err));
        }
    },
    assessApp = LearnosityAssess.init(<?php echo $signedRequest; ?>, '.hackday-assess', eventOptions);

    $(function () {
        $('.language-selection').on('click', function () {
            $('.intro-container').hide();
            $('.hackday-assess').fadeIn();
        });
    });
</script>

</body>
</html>

