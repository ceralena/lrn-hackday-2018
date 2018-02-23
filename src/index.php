<?php

if (!isset($_GET['user_id'])) {
    echo 'need user_id in query string';
    die();
}

$userId = $_GET['user_id'];

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Learnosity Flash Cards</title>
<link rel="stylesheet" href="question/flash-card.css" />
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>
<body class="index">

<div class="intro-container">
    <div class="row">
        <div class="choose-language">Choose a language</div>
    </div>
    <div class="row">
        <a href="test.php?user_id=<?php echo $userId;  ?>&lang=es" class="language-selection" style="background-image: url('/images/icon_es.png')" title="Spanish"></a>
        <a href="test.php?user_id=<?php echo $userId;  ?>&lang=ja" class="language-selection" style="background-image: url('/images/icon_ja.png')" title="Japanese"></a>
        <a href="test.php?user_id=<?php echo $userId;  ?>&lang=fr" class="language-selection" style="background-image: url('/images/icon_fr.png')" title="French"></a>
    </div>
</div>
</body>
</html>
