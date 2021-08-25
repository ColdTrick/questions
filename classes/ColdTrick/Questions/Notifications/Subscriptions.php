<?php

namespace ColdTrick\Questions\Notifications;

class Subscriptions {
	
	/**
	 * Subscribe to a question when you create an answer
	 *
	 * @param \Elgg\Event $event 'create', 'object'
	 *
	 * @return void
	 */
	public static function createAnswer(\Elgg\Event $event) {
		
		$object = $event->getObject();
		if (!$object instanceof \ElggAnswer) {
			return;
		}
		
		/* @var $owner \ElggUser */
		$owner = $object->getOwnerEntity();
		$question = $object->getContainerEntity();
		
		if ($question->hasMutedNotifications($owner->guid)) {
			return;
		}
		
		// subscribe to the question
		$content_preferences = $owner->getNotificationSettings('create_comment');
		$enabled_methods = array_keys(array_filter($content_preferences));
		if (empty($enabled_methods)) {
			return;
		}
		
		$question->addSubscription($owner->guid, $enabled_methods);
	}
	
	/**
	 * Subscribe to a question when you create a comment on an answer
	 *
	 * @param \Elgg\Event $event 'create', 'object'
	 *
	 * @return void
	 */
	public static function createCommentOnAnswer(\Elgg\Event $event) {
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		$answer = $object->getContainerEntity();
		if (!$answer instanceof \ElggAnswer) {
			return;
		}
		
		/* @var $owner \ElggUser */
		$owner = $object->getOwnerEntity();
		$question = $object->getContainerEntity();
		
		if ($question->hasMutedNotifications($owner->guid)) {
			return;
		}
		
		// subscribe to the question
		$content_preferences = $owner->getNotificationSettings('create_comment');
		$enabled_methods = array_keys(array_filter($content_preferences));
		if (empty($enabled_methods)) {
			return;
		}
		
		$question->addSubscription($owner->guid, $enabled_methods);
	}
}
