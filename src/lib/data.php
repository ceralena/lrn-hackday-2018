<?php

function getItemsQuestions($language, $id_suffix, $wordsCount) {
    $filename = __DIR__ . '/data/' . $language . '.json';
    $words = json_decode(file_get_contents($filename), true)["words"];

    $questions = array();
    $items = array();

    $wordsCount = min($wordsCount, count($words));

    for($i=0; $i < $wordsCount; ++$i) {
        array_push(
            $items, [
                "reference" => "item" . $i,
                "content" => '<span class="learnosity-response question-' . $id_suffix . '_Flash' . $i .'"></span',
                "response_ids" => [
                    $id_suffix . '_Flash' . $i,
                ],
            ]
        );
        array_push(
            $questions, [
                'type' => 'custom',
                'response_id' => $id_suffix . '_Flash' . $i,
                'js' => '//localhost:8080/question/flash-card.js',
                'custom_type' => 'Flash Card',
                'front_title' => $words[$i]["front"],
                'valid_response' => $words[$i]["back"]
            ]
        );
    };

    return ["items" => $items, "questions" => $questions];
}