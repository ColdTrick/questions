<?php

$guid = (int) get_input('guid');

$answer = get_entity($guid);
if (!$answer instanceof ElggAnswer) {
	return elgg_error_response(elgg_echo('entity:delete:item_not_found'));
}

if (!$answer->canDelete()) {
	return elgg_error_response(elgg_echo('entity:delete:permission_denied'));
}

$question = $answer->getContainerEntity();
$title = $answer->getDisplayName();

if (!$answer->delete()) {
	return elgg_error_response(elgg_echo('entity:delete:fail', [$title]));
}

return elgg_ok_response('', elgg_echo('entity:delete:success'), get_input('forward', $question->getURL()));
