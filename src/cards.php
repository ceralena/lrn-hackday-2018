<?php

/**
 * This is an endpoint to get some cards, formatted as questions.
 *
 * Call it with a GET, with "count" and "lang" queries.
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/data.php';


assertHasKeys($_GET, ['count', 'lang', 'sessionId']);
$cardsCount = $_GET['count'];
$langCode = $_GET['lang'];
$sessionId = $_GET['sessionId'];

$languageConfig = getLanguageConfig($langCode);

// look up some questions
$cardsData = getItemsQuestions($languageConfig['shortCode'], $sessionId, $cardsCount);

// respond with the data as JSON
$questions = $cardsData['questions'];

header('Content-Type: application/json');
echo json_encode($questions);
die();