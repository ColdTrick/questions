<?php
/**
 * Question entity full view
 *
 * @package Questions
*/

$question = elgg_extract('entity', $vars);
if (!$question instanceof ElggQuestion) {
	return;
}

$params = [
	'icon_entity' => $question->getOwnerEntity(),
	'title' => false,
	'show_summary' => true,
	'body' => elgg_view('output/longtext', ['value' => $question->description]),
	'responses' => elgg_view_comments($question, true, [
		'inline' => true,
	]),
];

if ($question->getStatus() === ElggQuestion::STATUS_CLOSED) {
	// add an icon to show this question is closed
	$params['imprint'][] = [
		'icon_name' => 'lock-closed',
		'content' => elgg_echo('status:closed'),
	];
}

$params = $params + $vars;

echo elgg_view('object/elements/full', $params);
