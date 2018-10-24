<?php

$group_guid = (int) get_input('group_guid');
$solution_time = (int) get_input('solution_time');
$who_can_ask = get_input('who_can_ask');
$who_can_answer = get_input('who_can_answer');
$auto_mark_correct = get_input('auto_mark_correct');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);
if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// save the settings
if (elgg_get_plugin_setting('solution_time_group', 'questions') === 'yes') {
	$group->setPrivateSetting('questions_solution_time', $solution_time);
}

if (questions_experts_enabled()) {
	$group->setPrivateSetting('questions_who_can_ask', $who_can_ask);
	$group->setPrivateSetting('questions_auto_mark_correct', $auto_mark_correct);
	
	if (!questions_experts_only_answer()) {
		$group->setPrivateSetting('questions_who_can_answer', $who_can_answer);
	}
}

return elgg_ok_response('', elgg_echo('questions:action:group_settings:success'), $group->getURL());
