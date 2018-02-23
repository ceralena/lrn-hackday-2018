<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/data.php';

use LearnositySdk\Request\Init;
use LearnositySdk\Utils\Uuid;

$initialWordsCount = 10;

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

$uniqueResponseIdSuffix = $sessionId;

$languageData = getItemsQuestions($languageConfig['shortCode'], $uniqueResponseIdSuffix, $initialWordsCount);

// define the items
$items = $languageData['items'];
$questions = $languageData['questions'];

$name = 'Learnosity LRN ' . $languageConfig['name'];

$request = [
	'name' => $name,
	'state' => 'resume',
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
		'id' => getActivityId(),
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
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>
<body>

<div class="intro-container">
    <div class="row">
        <div class="choose-language">Choose a language</div>
    </div>
    <div class="row">
        <a href="#" class="language-selection" style="background-image: url('/images/icon_es.png')" title="Spanish"></a>
        <a href="#" class="language-selection" style="background-image: url('/images/icon_jp.png')" title="Japanese"></a>
        <a href="#" class="language-selection" style="background-image: url('/images/icon_fr.png')" title="French"></a>
    </div>
</div>

<div class="hackday-assess"></div>

<script src="https://assess-au.learnosity.com"></script>
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

    $(function () {
        $('.language-selection').on('click', function () {
            $('.intro-container').hide();
            $('.hackday-assess').fadeIn();
            $('body').css({
                "background-image" : "url('/images/bg.gif')",
                "background-size": "auto"
            });
        });
    });
</script>

</body>
</html>

