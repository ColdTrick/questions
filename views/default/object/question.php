<?php
/**
 * Question entity view
 *
 * @package Questions
*/

$question = elgg_extract('entity', $vars, false);
if (!$question instanceof ElggQuestion) {
	return;
}

$full = (bool) elgg_extract('full_view', $vars, false);

if (!$full) {
	$vars['icon_entity'] = $question->getOwnerEntity();
	
	if ($question->getMarkedAnswer()) {
		$vars['imprint'][] = [
			'icon' => 'checkmark',
			'content' => elgg_echo('questions:marked:correct'),
		];
	}
	
	$excerpt = elgg_get_excerpt($question->description);
	
	if (!empty($answer_text)) {
		$answer_text = elgg_format_element('div', ['class' => 'elgg-subtext'], $answer_text);
	}
	
	$vars['content'] = $excerpt . $answer_text;
	
	echo elgg_view('object/elements/summary', $vars);
	return;
}


$subtitle = [];

$poster = $question->getOwnerEntity();

$poster_icon = elgg_view_entity_icon($poster, 'tiny');
$poster_link = elgg_view('output/url', [
	'text' => $poster->getDisplayName(),
	'href' => $poster->getURL(),
	'is_trusted' => true
]);
$subtitle[] = elgg_echo('questions:asked', [$poster_link]);

$container = $question->getContainerEntity();
if (($container instanceof ElggGroup) && (elgg_get_page_owner_guid() !== $container->getGUID())) {
	$group_link = elgg_view('output/url', [
		'text' => $container->getDisplayName(),
		'href' => "questions/group/{$container->getGUID()}/all",
		'is_trusted' => true
	]);
	$subtitle[] = elgg_echo('river:ingroup', [$group_link]);
}

$subtitle[] = elgg_view_friendly_time($question->time_created);

$answer_options = [
	'type' => 'object',
	'subtype' => 'answer',
	'container_guid' => $question->getGUID(),
	'count' => true,
];

$num_answers = elgg_get_entities($answer_options);
$answer_text = '';

if ($num_answers != 0) {
	$subtitle[] = elgg_view('output/url', [
		'href' => "{$question->getURL()}#question-answers",
		'text' => elgg_echo('answers') . " ({$num_answers})",
	]);
}

$solution_time = (int) $question->solution_time;
if ($solution_time && !$question->getMarkedAnswer()) {
	$solution_class = [
		'question-solution-time',
		'float-alt'
	];
	if ($solution_time < time()) {
		$solution_class[] = ' question-solution-time-late';
	} elseif ($solution_time < (time() + (24 * 60 * 60))) {
		$solution_class[] = ' question-solution-time-due';
	}
	
	$solution_date = elgg_view('output/date', ['value' => $question->solution_time]);
	$answer_text .= elgg_format_element('span', ['class' => $solution_class], $solution_date);
}

	
$params = [
	'entity' => $question,
	'title' => false,
	'icon_entity' => $question->getOwnerEntity(),
	'subtitle' => implode(' ', $subtitle),
	'content' => elgg_view('output/longtext', ['value' => $question->description]),
];

echo elgg_view('object/elements/summary', $params);

