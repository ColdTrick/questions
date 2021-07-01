<?php

namespace ColdTrick\Questions;

use Elgg\Notifications\SubscriptionNotificationEvent;

class Notifications {
	
	/**
	 * Change the notification message for comments on answers
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'notification:create:object:comment'
	 *
	 * @return void|\Elgg\Notifications\Notification
	 */
	public static function createCommentOnAnswer(\Elgg\Hook $hook) {
		$return_value = $hook->getValue();
		if (!$return_value instanceof \Elgg\Notifications\Notification) {
			return;
		}
		
		$event = $hook->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent || $event->getAction() !== 'create') {
			return;
		}
		
		$comment = $event->getObject();
		if (!$comment instanceof \ElggComment) {
			return;
		}
		
		$object = $comment->getContainerEntity();
		if (!$object instanceof \ElggAnswer) {
			return;
		}
		
		$actor = $event->getActor();
		$question = $object->getContainerEntity();
		$language = $hook->getParam('language');
		$recipient = $hook->getParam('recipient');
	
		$return_value->subject = elgg_echo('questions:notifications:answer:comment:subject', [], $language);
		$return_value->summary = elgg_echo('questions:notifications:answer:comment:summary', [], $language);
		$return_value->body = elgg_echo('questions:notifications:answer:comment:message', [
			$actor->getDisplayName(),
			$question->getDisplayName(),
			$comment->description,
			$object->getURL(),
		], $language);
		
		return $return_value;
	}
	
	/**
	 * Add question owner to the subscribers for a comment on an answer
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function addQuestionOwnerToCommentSubscribers(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		$container = $object->getContainerEntity();
		if (!$container instanceof \ElggAnswer) {
			return;
		}
		
		$question = $container->getContainerEntity();
		if (!$question instanceof \ElggQuestion) {
			// something went wrong, maybe access
			return;
		}
		
		/* @var $owner \ElggUser */
		$owner = $question->getOwnerEntity();
		
		$filtered_methods = array_keys(array_filter($owner->getNotificationSettings()));
		if (empty($filtered_methods)) {
			return;
		}
		
		$return_value = $hook->getValue();
		$return_value[$owner->guid] = $filtered_methods;
		
		return $return_value;
	}
	
	/**
	 * Add question subscribers to the subscribers for a comment on an answer
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function addQuestionSubscribersToCommentSubscribers(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent) {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			return;
		}
		
		$container = $object->getContainerEntity();
		if (!$container instanceof \ElggAnswer) {
			return;
		}
		
		$question = $container->getContainerEntity();
		if (!$question instanceof \ElggQuestion) {
			// something went wrong, maybe access
			return;
		}
		
		$subscribers = elgg_get_subscriptions_for_container($question->guid);
		if (empty($subscribers)) {
			return;
		}
		
		return ($hook->getValue() + $subscribers);
	}
}
