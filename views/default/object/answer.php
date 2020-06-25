<?php

use Elgg\Values;

$answer = elgg_extract('entity', $vars);
if (!$answer instanceof ElggAnswer) {
	return;
}

$full_view = (bool) elgg_extract('full_view', $vars, false);

/* @var $question ElggQuestion */
$question = elgg_call(ELGG_IGNORE_ACCESS, function() use ($answer) {
	return $answer->getContainerEntity();
});

$params = [
	'entity' => $answer,
	'access' => false,
	'icon_entity' => $answer->getOwnerEntity(),
	'imprint' => [],
];

$correct_answer = $answer->getCorrectAnswerMetadata();
if ($correct_answer) {
	
	$date = Values::normalizeTime($correct_answer->time_created);
	
	$content = elgg_format_element('time', [
		'datetime' => $date->format('c'),
	], elgg_echo('questions:answer:checkmark:title', [
		$date->formatLocale(elgg_echo('friendlytime:date_format')),
	]));
	
	$params['imprint'][] = [
		'icon_name' => 'check',
		'content' => $content,
	];
}

if (!$full_view) {
	// listing view
	
	// make title
	$answer_link = elgg_view('output/url', [
		'text' => elgg_echo('questions:search:answer:title'),
		'href' => $answer->getURL(),
		'is_trusted' => true,
	]);
	$question_link = elgg_view('output/url', [
		'text' => $question->getDisplayName(),
		'href' => $question->getURL(),
		'is_trusted' => true,
	]);
	
	$params['title'] = elgg_echo('generic_comment:on', [$answer_link, $question_link]);
	
	// excerpt
	$excerpt = elgg_get_excerpt($answer->description);
	
	$params['content'] = $excerpt;
	
	$params = $params + $vars;
	echo elgg_view('object/elements/summary', $params);
}

// full view
$params['title'] = false;
$params['show_summary'] = true;
$params['body'] = elgg_view('output/longtext', [
	'value' => $answer->description,
]);

$params['responses'] = elgg_view_comments($answer, true, [
	'inline' => true,
	'limit' => false,
	'offset' => 0,
]);

$params = $params + $vars;
echo elgg_view('object/elements/full', $params);
