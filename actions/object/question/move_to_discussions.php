<?php

elgg_make_sticky_form('question');

$guid = (int) get_input('guid');

if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$entity = get_entity($guid);
if (!$entity instanceof ElggQuestion) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$container = $entity->getContainerEntity();
if (!$entity->canEdit() || !questions_can_move_to_discussions($container)) {
	return elgg_error_response(elgg_echo('questions:action:question:move_to_discussions:error:move'));
}

$title = get_input('title');
$description = get_input('description');
$tags = string_to_tag_array(get_input('tags', ''));
$access_id = (int) get_input('access_id');
$access_id = questions_validate_access_id($access_id, $container->guid);

// save the latest changes
$entity->title = $title;
$entity->description = $description;
$entity->tags = $tags;
$entity->access_id = $access_id;

$entity->save();

// make sure we have permission
$permission = function (\Elgg\Hook $hook) use ($entity) {
	$subtype = $hook->getParam('subtype');
	$user = $hook->getUserParam();
	$container = $hook->getParam('container');
	
	if ($subtype !== 'discussion' || $user->guid !== elgg_get_logged_in_user_guid() || $container->guid !== $entity->owner_guid) {
		return;
	}
	
	return true;
};
elgg_register_plugin_hook_handler('container_permissions_check', 'object', $permission);

// create new discussion
$topic = new ElggDiscussion();
$topic->owner_guid = $entity->owner_guid;
$topic->container_guid = $entity->container_guid;
$topic->access_id = $entity->access_id;
$topic->time_created = $entity->time_created;

$topic->title = $entity->title;
$topic->description = $entity->description;
$topic->tags = $entity->tags;
$topic->status = 'open';

if (!$topic->save()) {
	elgg_unregister_plugin_hook_handler('container_permissions_check', 'object', $permission);
	return elgg_error_response(elgg_echo('questions:action:question:move_to_discussions:error:topic'));
}

elgg_unregister_plugin_hook_handler('container_permissions_check', 'object', $permission);

// cleanup sticky form
elgg_clear_sticky_form('question');

// make sure we can copy all annotations
elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity, $topic) {
	
	// helper to move comments
	$move_comments = function (ElggEntity $from_container) use ($topic) {
		$comment_options = [
			'type' => 'object',
			'subtype' => 'comment',
			'container_guid' => $from_container->guid,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		];
		
		$comments = elgg_get_entities($comment_options);
		/* @var $comment ElggComment */
		foreach ($comments as $comment) {
			// change container to discussion
			$comment->container_guid = $topic->guid;
			$comment->save();
		}
	};
	
	// move question comments
	$move_comments($entity);
	
	// copy all answers on the question to topic replies
	$answers = elgg_get_entities([
		'type' => 'object',
		'subtype' => ElggAnswer::SUBTYPE,
		'container_guid' => $entity->guid,
		'limit' => false,
		'batch' => true,
		'batch_inc_offset' => false,
	]);
	/* @var $answer ElggAnswer */
	foreach ($answers as $answer) {
		// move answer to comment
		$answer_comment = new ElggComment();
		$answer_comment->owner_guid = $answer->owner_guid;
		$answer_comment->container_guid = $topic->guid;
		$answer_comment->access_id = $topic->access_id;
		
		$answer_comment->description = $answer->description;
		$answer_comment->time_created = $answer->time_created;
		
		$answer_comment->save();
		
		// move all comments on the answer to topic
		$move_comments($answer);
		
		// remove answer
		$answer->delete();
	}
	
	// cleaup the old question
	$entity->delete();
});

// set correct forward url
return elgg_ok_response('', elgg_echo('questions:action:question:move_to_discussions:success'), $topic->getURL());
