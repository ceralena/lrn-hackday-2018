<?php

// global settings
global $consumerKey, $consumerSecret;

function getConsumerKey() {
	return 'yis0TYCu7U9V4o7M';
}

function getConsumerSecret() {
	return '74c5fd430cf1242a527f6223aebd42d30464be22';
}

function getActivityId($languageConfig) {
    return 'Flash Card: ' . $languageConfig['name'];
}

/**
 * @param array $ar
 * @param array $keys
 * @throws Exception
 */
function assertHasKeys(array $ar, array $keys) {
    foreach($keys as $key) {
        if (!isset($ar[$key])) {
            throw new \Exception('missing key: ' . $key);
        }
    }
}

// given language and user ID, infer session ID
function generateSessionId($langauge, $userId) {
	$seed = implode('.', [$language, $userId]);

	$hash = md5($seed);

	// want:
	//
	// f47ac10b-58cc-4372-a567-0e02b2c3d479
	//
	// have:
	//
	// 5d41402a bc4b 2a76 b971 9d911017c592
	return implode('-', [
		substr($hash, 0, 8),
		substr($hash, 8, 4),
		substr($hash, 12, 4),
		substr($hash, 16, 4),
		substr($hash, 20, 12),
	]);

	/*
	/ :)
	*/
}

function getAllLanguageConfigs() {
    return [
        'ja' => [
            'name' => 'Japanese',
            'shortCode' => 'ja'
        ],
        'es' => [
            'name' => 'Spanish',
            'shortCode' => 'es'
        ],
        'fr' => [
            'name' => 'French',
            'shortCode' => 'fr'
        ]
    ];
}

function getLanguageConfig($shortLabel) {
    $languageConfigs = getAllLanguageConfigs();

	if (!isset($languageConfigs[$shortLabel])) {
		throw new \Exception('unknown language label: ' . $shortLabel);
	}

	return $languageConfigs[$shortLabel];
}

function generateSecurity($userId, $domain) {
	$timestamp = gmdate('Ymd-Hi');

	$sec = [
		'consumer_key' => getConsumerKey(),
		'domain' => $domain,
		'timestamp' => $timestamp,
	];

	if (!is_null($userId)) {
        $sec['user_id'] = $userId;
    }

    return $sec;
}