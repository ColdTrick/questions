<?php
/**
 * Question entity view
 *
 * @package Questions
*/

$question = elgg_extract('entity', $vars);
if (!$question instanceof ElggQuestion) {
	return;
}
$full = (bool) elgg_extract('full_view', $vars, false);
if ($full) {
	echo elgg_view('object/question/full', $vars);
	return;
}

$params = [
	'entity' => $question,
	'icon_entity' => $question->getOwnerEntity(),
];

$imprint = [];

$num_answers = $question->getAnswers(['count' => true]);
if ($num_answers > 0) {
	$imprint[] = [
		'icon_name' => 'comments',
		'content' => elgg_view('output/url', [
			'href' => "{$question->getURL()}#question-answers",
			'text' => elgg_echo('answers'),
			'badge' => $num_answers,
		]),
	];
}
	
if ($question->getMarkedAnswer()) {
	array_unshift($imprint, [
		'icon_name' => 'checkmark',
		'content' => elgg_echo('questions:marked:correct'),
	]);
}

$excerpt = elgg_get_excerpt($question->description);

// is the question due for an answer
$solution_time = (int) $question->solution_time;
if ($solution_time && !$question->getMarkedAnswer()) {
	$solution_class = [
		'question-solution-time',
	];
	if ($solution_time < time()) {
		$solution_class[] = ' question-solution-time-late';
	} elseif ($solution_time < (time() + (24 * 60 * 60))) {
		$solution_class[] = ' question-solution-time-due';
	}
	
	$solution_date = elgg_view('output/date', ['value' => $question->solution_time]);
	$solution_date = elgg_format_element('span', ['class' => $solution_class], $solution_date);
	
	$excerpt .= elgg_format_element('div', ['class' => 'elgg-subtext'], $solution_date);
}

$params['content'] = $excerpt;
$params['imprint'] = $imprint;
$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
