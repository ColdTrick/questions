<?php
/**
 * Question entity full view
 *
 * @package Questions
*/

use Elgg\Values;

$question = elgg_extract('entity', $vars);
if (!$question instanceof ElggQuestion) {
	return;
}

$imprint = [];

// is the question due for an answer
$solution_time = (int) $question->solution_time;
if ($solution_time && !$question->getMarkedAnswer()) {
	$solution_class = [
		'question-solution-time',
	];
	if ($solution_time < time()) {
		$solution_class[] = 'question-solution-time-late';
	} elseif ($solution_time < Values::normalizeTimestamp('+1 day')) {
		$solution_class[] = 'question-solution-time-due';
	}
	
	$imprint[] = [
		'icon_name' => 'stopwatch',
		'content' => elgg_view('output/date', ['value' => $question->solution_time]),
		'class' => $solution_class,
	];
}

$params = [
	'icon_entity' => $question->getOwnerEntity(),
	'title' => false,
	'show_summary' => true,
	'body' => elgg_view('output/longtext', ['value' => $question->description]),
	'responses' => elgg_view_comments($question, true, [
		'inline' => true,
	]),
	'imprint' => $imprint,
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
