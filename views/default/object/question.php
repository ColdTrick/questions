<?php
/**
 * Question entity view
 */

use Elgg\Values;

$question = elgg_extract('entity', $vars);
if (!$question instanceof \ElggQuestion) {
	return;
}

if ((bool) elgg_extract('full_view', $vars, false)) {
	echo elgg_view('object/question/full', $vars);
	return;
}

$params = [
	'icon_entity' => $question->getOwnerEntity(),
];

$imprint = [];

// correct answer
if ($question->getMarkedAnswer()) {
	$imprint[] = [
		'icon_name' => 'check',
		'content' => elgg_echo('questions:marked:correct'),
	];
}

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

// number answers
$num_answers = $question->getAnswers(['count' => true]);
if ($num_answers > 0) {
	$imprint[] = [
		'icon_name' => 'comments',
		'content' => elgg_view('output/url', [
			'text' => elgg_echo('answers'),
			'href' => "{$question->getURL()}#question-answers",
			'badge' => $num_answers,
		]),
	];
}

$params['content'] = elgg_get_excerpt((string) $question->description);
$params['imprint'] = $imprint;

$params = $params + $vars;
echo elgg_view('object/elements/summary', $params);
