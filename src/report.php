<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/data.php';

use LearnositySdk\Request\Init;

$domain = $_SERVER['SERVER_NAME'];

$activities = [];

foreach (getAllLanguageConfigs() as $languageConfig) {
    $activities[] = [
        'id' => getActivityId($languageConfig),
        'name' => getActivityId($languageConfig)
    ];
}

$lrnRequest = [
    'reports' => [
        [
            'id' => 'report-1',
            'type' => 'sessions-list',
            'ui' => 'table',
            'activities' => $activities
        ]
    ]
];

$security = generateSecurity(null, $domain);

$init = new Init('reports', $security, getConsumerSecret(), $lrnRequest);
$signedRequest = $init->generate();

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Learnosity Flash Cards</title>
<link rel="stylesheet" href="question/flash-card.css" />
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>
<body>

<div class="hackday-reports">
    <span id="report-1"></span>
</div>

<script src="https://reports.learnosity.com"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
    var reportsApp = LearnosityReports.init(<?php echo $signedRequest; ?>, '.hackday-reports');
</script>


</body>