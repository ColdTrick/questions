<?php

$guid = (int) get_input('guid');
$question = get_entity($guid);
if (!$question instanceof ElggQuestion) {
	return elgg_error_response(elgg_echo('entity:delete:item_not_found'));
}

if (!$question->canDelete()) {
	return elgg_error_response(elgg_echo('entity:delete:permission_denied'));
}

$container = $question->getContainerEntity();

$title = $question->getDisplayName();

if (!$question->delete()) {
	return elgg_error_response(elgg_echo('entity:delete:fail', [$title]));
}

$forward = get_input('forward');
if (empty($forward)) {
	if ($container instanceof ElggUser) {
		$forward = "questions/owner/{$container->username}";
	} elseif ($container instanceof ElggGroup) {
		$forward = "questions/group/{$container->getGUID()}/all";
	}
}

return elgg_ok_response('', elgg_echo('entity:delete:success', [$title]), $forward);
