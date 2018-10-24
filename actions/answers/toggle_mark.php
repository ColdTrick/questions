<?php
/**
 * This action marks an answer as the correct answer for a question.
 */

$guid = (int) get_input('guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$entity = get_entity($guid);
if (!$entity instanceof ElggAnswer) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// are you allowed to mark answers as correct
if (!$entity->canMarkAnswer()) {
	return elgg_error_response(elgg_echo('questions:action:answer:toggle_mark:error:not_allowed'));
}

$question = $entity->getContainerEntity();
$answer = $question->getMarkedAnswer();

if (empty($answer)) {
	// no answer yet, so mark this one
	$entity->markAsCorrect();
	
	return elgg_ok_response('', elgg_echo('questions:action:answer:toggle_mark:success:mark'));
} elseif ($answer->getGUID() == $entity->getGUID()) {
	// the marked answer is this answer, so unmark
	$entity->undoMarkAsCorrect();
	
	return elgg_ok_response('', elgg_echo('questions:action:answer:toggle_mark:success:unmark'));
}

return elgg_error_response(elgg_echo('questions:action:answer:toggle_mark:error:duplicate'));
