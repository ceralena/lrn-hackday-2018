<?php

// global settings
global $consumerKey, $consumerSecret;

function getConsumerKey() {
	return 'yis0TYCu7U9V4o7M';
}

function getConsumerSecret() {
	return '74c5fd430cf1242a527f6223aebd42d30464be22';
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

function getLanguageConfig($shortLabel) {
	$languageConfigs = [
		'ja' => [
			'name' => 'Japanese',
			'shortCode' => 'ja'
		],
        'es' => [
            'name' => 'Spanish',
            'shortcode' => 'es'
        ]
	];

	if (!isset($languageConfigs[$shortLabel])) {
		throw new \Exception('unknown language label: ' . $shortLabel);
	}

	return $languageConfigs[$shortLabel];
}

function generateSecurity($userId, $domain) {
	$timestamp = gmdate('Ymd-Hi');

	return [
		'consumer_key' => getConsumerKey(),
		'domain' => $domain,
		'timestamp' => $timestamp,
		'user_id' => $userId
	];
}