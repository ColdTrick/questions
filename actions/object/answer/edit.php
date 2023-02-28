<?php

$guid = (int) get_input('guid');

$adding = false;
if (empty($guid)) {
	$answer = new \ElggAnswer();
	$adding = true;
} else {
	$answer = get_entity($guid);
	if (!$answer instanceof \ElggAnswer) {
		return elgg_error_response(elgg_echo('error:missing_data'));
	}
	
	if (!$answer->canEdit()) {
		return elgg_error_response(elgg_echo('actionunauthorized'));
	}
}

$container_guid = (int) get_input('container_guid');
$description = get_input('description');

if (empty($container_guid) || empty($description)) {
	return elgg_error_response(elgg_echo('questions:action:answer:save:error:body', [$container_guid, $description]));
}

$question = get_entity($container_guid);
if (!$question instanceof \ElggQuestion) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if ($adding && !$question->canWriteToContainer(0, 'object', 'answer')) {
	return elgg_error_response(elgg_echo('questions:action:answer:save:error:container'));
}

if ($question->getStatus() !== \ElggQuestion::STATUS_OPEN) {
	return elgg_error_response(elgg_echo('questions:action:answer:save:error:question_closed'));
}

$answer->description = $description;
$answer->access_id = $question->access_id;
$answer->container_guid = $container_guid;

if (!$answer->save()) {
	return elgg_error_response(elgg_echo('questions:action:answer:save:error:save'));
}
	
if ($adding) {
	// check for auto mark as correct
	$answer->checkAutoMarkCorrect($adding);
	
	// create river event
	elgg_create_river_item([
		'view' => 'river/object/answer/create',
		'action_type' => 'create',
		'subject_guid' => elgg_get_logged_in_user_guid(),
		'object_guid' => $answer->guid,
		'target_guid' => $question->guid,
		'access_id' => $answer->access_id,
	]);
}

return elgg_ok_response('', elgg_echo('save:success'), get_input('forward', $answer->getURL()));
