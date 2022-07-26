<?php
/**
 * This action toggles the expert role for a user on or off
 */

$user_guid = (int) get_input('user_guid');
$page_owner_guid = (int) get_input('guid');

if (empty($user_guid) || empty($page_owner_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$user = get_user($user_guid);
$page_owner = get_entity($page_owner_guid);
if (empty($user) || empty($page_owner) || (!$page_owner instanceof ElggSite && !$page_owner instanceof ElggGroup) || !$page_owner->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// check if the user is an expert
if ($user->hasRelationship($page_owner->guid, QUESTIONS_EXPERT_ROLE)) {
	// yes, so remove
	$user->removeRelationship($page_owner->guid, QUESTIONS_EXPERT_ROLE);
	
	return elgg_ok_response('', elgg_echo('questions:action:toggle_expert:success:remove', [$user->getDisplayName(), $page_owner->getDisplayName()]));
}

// no, so add
$user->addRelationship($page_owner->guid, QUESTIONS_EXPERT_ROLE);

return elgg_ok_response('', elgg_echo('questions:action:toggle_expert:success:make', [$user->getDisplayName(), $page_owner->getDisplayName()]));
