<?php

$group = elgg_extract('entity', $vars);
if (!($group instanceof ElggGroup)) {
	return;
}

if (!$group->canEdit()) {
	return;
}

if ($group->questions_enable !== 'yes') {
	return;
}

// default solution time
if (questions_can_groups_set_solution_time()) {
	$solution_time = questions_get_solution_time($group);
	$solution = [];
	$solution[] = elgg_echo('questions:settings:general:solution_time');
	$solution[] = elgg_view('input/select', [
		'name' => 'solution_time',
		'value' => $solution_time,
		'options' => range(0, 30),
		'class' => 'mls',
	]);
	$solution[] = elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('questions:group_settings:solution_time:description'));
	$content .= elgg_format_element('div', [], implode('', $solution));
}

// who can ask questions
if (questions_experts_enabled()) {
	$asker_options = [
		'members' => elgg_echo('questions:group_settings:who_can_ask:members'),
		'experts' => elgg_echo('questions:group_settings:who_can_ask:experts'),
	];
	
	$who_can_ask = [];
	$who_can_ask[] = elgg_echo('questions:group_settings:who_can_ask');
	$who_can_ask[] = elgg_view('input/select', [
		'name' => 'who_can_ask',
		'value' => $group->getPrivateSetting('questions_who_can_ask'),
		'options_values' => $asker_options,
		'class' => 'mls',
	]);
	
	$content .= elgg_format_element('div', [], implode('', $who_can_ask));
}

// form footer
$footer = [];
$footer[] = elgg_view('input/hidden', ['name' => 'group_guid', 'value' => $group->getGUID()]);
$footer[] = elgg_view('input/submit', ['value' => elgg_echo('save')]);

$content .= elgg_format_element('div', ['class' => 'elgg-foot'], implode('', $footer));

// build form
$form = elgg_view('input/form', [
	'body' => $content,
	'action' => 'action/questions/group_settings'
]);

// display content
echo elgg_view_module('info', elgg_echo('questions:group_settings:title'), $form);
