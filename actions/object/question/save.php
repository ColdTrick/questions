<?php

use Elgg\Values;

elgg_make_sticky_form('question');

$guid = (int) get_input('guid');
if (empty($guid)) {
	$question = new ElggQuestion();
} else {
	$question = get_entity($guid);
	if (!$question instanceof ElggQuestion) {
		return elgg_error_response(elgg_echo('error:missing_data'));
	}
	
	if (!$question->canEdit()) {
		return elgg_error_response(elgg_echo('actionunauthorized'));
	}
}

$adding = empty($question->guid);
$editing = !$adding;
$moving = false;

$container_guid = (int) get_input('container_guid');
if (empty($container_guid)) {
	$container_guid = (int) $question->owner_guid;
}

if ($editing && ($container_guid !== $question->container_guid)) {
	$moving = true;
}

$container = get_entity($container_guid);
if ($adding && !questions_can_ask_question($container)) {
	return elgg_error_response(elgg_echo('questions:action:question:save:error:container'));
}

if (questions_limited_to_groups() && ($container_guid == $question->getOwnerGUID())) {
	return elgg_error_response(elgg_echo('questions:action:question:save:error:limited_to_groups'));
}

$title = elgg_get_title_input();
$description = get_input('description');
$tags = elgg_string_to_array( (string) get_input('tags', ''));
$access_id = (int) get_input('access_id');
$comments_enabled = get_input('comments_enabled');

if (empty($container_guid) || empty($title)) {
	return elgg_error_response(elgg_echo('questions:action:question:save:error:body', [$container_guid, $title]));
}

// make sure we have a valid access_id
$access_id = questions_validate_access_id($access_id, $container_guid);

$question->title = $title;
$question->description = $description;

$question->access_id = $access_id;
$question->container_guid = $container_guid;

$question->tags = $tags;
$question->comments_enabled = $comments_enabled;

try {
	$question->save();
	
	if ($adding) {
		// add river event
		elgg_create_river_item([
			'view' => 'river/object/question/create',
			'action_type' => 'create',
			'subject_guid' => elgg_get_logged_in_user_guid(),
			'object_guid' => $question->getGUID(),
			'target_guid' => $container->getGUID(),
			'access_id' => $question->access_id,
		]);
		
		// check for a solution time limit
		$solution_time = questions_get_solution_time($question->getContainerEntity());
		if ($solution_time) {
			// add x number of days when the question should be solved
			$question->solution_time = Values::normalizeTimestamp("+{$solution_time} days");
		}
	} elseif ($moving) {
		elgg_trigger_event('move', 'object', $question);
	}
} catch (Exception $e) {
	return elgg_error_response(elgg_echo('questions:action:question:save:error:save'));
}

elgg_clear_sticky_form('question');

if (!$adding) {
	$forward_url = $question->getURL();
} elseif ($container instanceof ElggUser) {
	$forward_url = elgg_generate_url('collection:object:question:owner', [
		'username' => $container->username,
	]);
} else {
	$forward_url = elgg_generate_url('collection:object:question:group', [
		'guid' => $container->guid,
	]);
}

return elgg_ok_response('', elgg_echo('save:success'), $forward_url);
