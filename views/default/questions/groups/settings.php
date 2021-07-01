<?php

$group = elgg_extract('entity', $vars);

$content = '';

// default solution time
if (elgg_get_plugin_setting('solution_time_group', 'questions') === 'yes') {
	$content .= elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('questions:settings:general:solution_time'),
		'#help' => elgg_echo('questions:group_settings:solution_time:description'),
		'name' => 'settings[questions][solution_time]',
		'value' => questions_get_solution_time($group),
		'options' => range(0, 30),
	]);
}

if (questions_experts_enabled()) {
	$expert_options = [
		'members' => elgg_echo('questions:group_settings:who_can_ask:members'),
		'experts' => elgg_echo('questions:group_settings:who_can_ask:experts'),
	];
	
	// who can ask?
	$content .= elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('questions:group_settings:who_can_ask'),
		'name' => 'settings[questions][who_can_ask]',
		'value' => $group instanceof ElggGroup ? $group->getPluginSetting('questions', 'who_can_ask') : null,
		'options_values' => $expert_options,
	]);
	
	if (!questions_experts_only_answer()) {
		// who can answer
		$content .= elgg_view_field([
			'#type' => 'select',
			'#label' => elgg_echo('questions:group_settings:who_can_answer'),
			'name' => 'settings[questions][who_can_answer]',
			'value' => $group instanceof ElggGroup ? $group->getPluginSetting('questions', 'who_can_answer') : null,
			'options_values' => $expert_options,
		]);
	} else {
		$content .= elgg_view('output/longtext', [
			'value' => elgg_echo('questions:group_settings:who_can_answer:experts_only'),
		]);
	}
	
	// auto mark answer
	$content .= elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('questions:group_settings:auto_mark_correct'),
		'name' => 'settings[questions][auto_mark_correct]',
		'value' => $group instanceof ElggGroup ? $group->getPluginSetting('questions', 'auto_mark_correct') : null,
		'options_values' => [
			'no' => elgg_echo('option:no'),
			'yes' => elgg_echo('option:yes'),
		],
	]);
}

if (empty($content)) {
	return;
}

// display content
echo elgg_view_module('info', elgg_echo('questions:group_settings:title'), $content);
