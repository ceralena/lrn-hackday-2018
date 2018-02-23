<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/data.php';

use LearnositySdk\Request\Init;

$initialWordsCount = 5;

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

$sessionId = generateSessionId($languageConfig, $userId);

$uniqueResponseIdSuffix = $sessionId;

$languageData = getItemsQuestions($languageConfig['shortCode'], $uniqueResponseIdSuffix, $initialWordsCount);

// define the items
$items = $languageData['items'];
$questions = $languageData['questions'];

$name = 'Learnosity LRN ' . $languageConfig['name'];

$request = [
	'name' => $name,
	'title' => 'Learnosity Flash Cards',
	'subtitle' => 'Language: ' . $languageConfig['name'],
    'navigation' => [
        'show_intro' => false,
    ],
	'time' => [],
    'regions' => 'items-only',
	'configuration' => [
		'questionsApiVersion' => 'v2',
        'shuffle_items' => true,
	],
	'items' => $items,
	'questionsApiActivity' => [
		'type' => 'submit_practice',
		'state' => 'resume',
        'captureOnResumeError' => true,
		'id' => getActivityId($languageConfig),
		'name' => getActivityId($languageConfig),
		'course_id' => $courseId,
		'session_id' => $sessionId,
		'questions' => $questions,
	],
];

$init = new Init('assess', $security, getConsumerSecret(), $request);
$signedRequest = $init->generate();

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Learnosity Flash Cards</title>
<link rel="stylesheet" href="question/flash-card.css" />
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>
<body class="test">

<div class="hackday-assess"></div>

<script src="https://assess.learnosity.com"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    let eventOptions = {
        readyListener: function () {
            console.log('flash card app is ready');
        },
        errorListener: function (err) {
           console.log('flash card app error: ' + JSON.stringify(err));
        }
    },
    assessApp = LearnosityAssess.init(<?php echo $signedRequest; ?>, '.hackday-assess', eventOptions);

    assessApp.flashcardState = {
        lang: "<?php echo $languageConfig['shortCode']; ?>",
        sessionId: "<?php echo $sessionId; ?>",
        currentWordsCount: "<?php echo $initialWordsCount; ?>",
        cardsPerFetch: "<?php echo $initialWordsCount; ?>",
        speech: true,
    };

    function fetchMoreCards() {
        console.log('fetching more cards');
        const params = {
            lang: assessApp.flashcardState.lang,
            sessionId: assessApp.flashcardState.sessionId,
            count: assessApp.flashcardState.cardsPerFetch
        };
        const cardsUrl = "/cards.php?" + $.param(params);

        return fetch(cardsUrl).then((res) => {
            return res.json();
        });
    }

    assessApp.on('item:changing', (newItemIndex) => {
        const allowance = 5;
            if ((assessApp.flashcardState.currentWordsCount - newItemIndex) < allowance) {
            fetchMoreCards().then((cards) => {
                // TODO
            });
        }
    });
</script>

</body>
</html>

