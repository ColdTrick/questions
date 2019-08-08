<?php

namespace ColdTrick\Questions;

class ContentSubscriptions {
	
	/**
	 * Add questions to Content Subscriptions supported types
	 *
	 * @param \Elgg\Hook $hook         the name of the hook
	 *
	 * @return void|array
	 */
	public static function getEntityTypes(\Elgg\Hook $hook) {
		$return_value = $hook->getValue();
		if (!is_array($return_value)) {
			// someone blocked all
			return;
		}
		
		$return_value['object'][] = \ElggQuestion::SUBTYPE;
		
		return $return_value;
	}
	
	/**
	 * Subscribe to a question when you create an answer
	 *
	 * @param \Elgg\Event $event 'create', 'object'
	 *
	 * @return void
	 */
	public static function createAnswer(\Elgg\Event $event) {
		
		if (!elgg_is_active_plugin('content_subscriptions')) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggAnswer) {
			return;
		}
		
		$owner = $object->getOwnerEntity();
		$question = $object->getContainerEntity();
		
		if (!content_subscriptions_can_subscribe($question, $owner->getGUID())) {
			return;
		}
		
		// subscribe to the question
		content_subscriptions_autosubscribe($question->getGUID(), $owner->getGUID());
	}
	
	/**
	 * Subscribe to a question when you create a comment on an answer
	 *
	 * @param \Elgg\Event $event 'create', 'object'
	 *
	 * @return void
	 */
	public static function createCommentOnAnswer(\Elgg\Event $event) {
		
		if (!elgg_is_active_plugin('content_subscriptions')) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		$answer = $object->getContainerEntity();
		if (!$answer instanceof \ElggAnswer) {
			return;
		}
		
		$owner = $object->getOwnerEntity();
		$question = $answer->getContainerEntity();
		if (!content_subscriptions_can_subscribe($question, $owner->getGUID())) {
			return;
		}
		
		// subscribe to the question
		content_subscriptions_autosubscribe($question->getGUID(), $owner->getGUID());
	}
}
