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

$params = [
	'entity' => $question,
	'icon_entity' => $question->getOwnerEntity(),
];

$imprint = [];

$answer_options = [
	'type' => 'object',
	'subtype' => ElggAnswer::SUBTYPE,
	'container_guid' => $question->guid,
	'count' => true,
];

$num_answers = elgg_get_entities($answer_options);
if ($num_answers > 0) {
	$imprint[] = [
		'icon_name' => 'comments',
		'content' => elgg_view('output/url', [
			'href' => "{$question->getURL()}#question-answers",
			'text' => elgg_echo('answers') . " ({$num_answers})",
		]),
	];
}

$full = (bool) elgg_extract('full_view', $vars, false);
if (!$full) {
	
	if ($question->getMarkedAnswer()) {
		array_unshift($imprint, [
			'icon' => 'checkmark',
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
	return;
}

// full view
$params['title'] = false;
$params['show_summary'] = true;
$params['imprint'] = $imprint;
$params['body'] = elgg_view('output/longtext', ['value' => $question->description]);

$params = $params + $vars;

echo elgg_view('object/elements/full', $params);
