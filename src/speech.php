<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

// Voices

// Japanese: Mizuki, Takumi
// French: Mathieu, Celine
// English: Nicole, Russell
// Spanish: Miguel, Penelope

$voices = [
    'en' => 'Russell',
    'ja' => 'Takumi',
    'fr' => 'Celine',
    'es' => 'Miguel',
];

$voice = $voices[$_GET['lang']];
$textType = isset($_GET['ssml']) ? 'ssml' : 'text';


$filename = __DIR__ . '/../speech/' . $_GET['text'] . '.' . $voice . '.mp3';
if (file_exists($filename)) {
    $resultData = file_get_contents($filename);
} else {
    $credentials    = new \Aws\Credentials\Credentials($awsAccessKeyId, $awsSecretKey);
    $client         = new \Aws\Polly\PollyClient([
        'version'     => '2016-06-10',
        'credentials' => $credentials,
        'region'      => 'ap-south-1',
    ]);
    $result         = $client->synthesizeSpeech([
        'OutputFormat' => 'mp3',
        'Text'         => $_GET['text'],
        'TextType'     => $textType,
        'VoiceId'      => $voice, // Mizuki, Takumi, Nicole, Russell
    ]);
    $resultData     = $result->get('AudioStream')->getContents();

    // Cache file
    file_put_contents($filename, $resultData);
}

// MP3 headers
header('Content-Transfer-Encoding: binary');
header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
header('Content-length: ' . strlen($resultData));
header('Content-Disposition: attachment; filename="speech.mp3"');
header('X-Pad: avoid browser bug');
header('Cache-Control: public');

echo $resultData;