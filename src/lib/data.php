<?php

function getItemsQuestions($language, $id_suffix) {
    $filename = __DIR__ . 'data/' . $language . '.json';
    $words = json_decode(file_get_contents($filename), true)["words"];

    $questions = array();
    $items = array();

    for($i=0; $i < count($words); ++$i) {
        array_push(
            $items, [
                "reference" => "item" . $i,
                "content" => '<span class="learnosity-response question-' . $id_suffix . '_Flash1"></span',
                "response_ids" => [
                    $id_suffix . '_Flash1',
                ],
            ]
        );
        array_push(
            $questions, [
                'type' => 'custom',
                'custom_type' => 'flashcard',
                'response_id' => $id_suffix . '_Flash1',
                'js' => '//localhost:8080/question/flash-card.js',
                'custom_type' => 'Flash Card',
                'front_title' => $words[$i]["front"],
                'valid_response' => '$words[$i]["back"]'
            ]
        );
    };

    return ["items" => $items, "questions" => $questions];
}