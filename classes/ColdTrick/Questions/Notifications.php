<?php

namespace ColdTrick\Questions;

use Elgg\Notifications\Notification;
use Elgg\Notifications\SubscriptionNotificationEvent;

/**
 * Change notifications
 */
class Notifications {
	
	/**
	 * Change the notification message for comments on answers
	 *
	 * @param \Elgg\Event $event 'prepare', 'notification:create:object:comment'
	 *
	 * @return null|\Elgg\Notifications\Notification
	 */
	public static function createCommentOnAnswer(\Elgg\Event $event): ?Notification {
		$return_value = $event->getValue();
		if (!$return_value instanceof Notification) {
			return null;
		}
		
		$notification_event = $event->getParam('event');
		if (!$notification_event instanceof SubscriptionNotificationEvent || $notification_event->getAction() !== 'create') {
			return null;
		}
		
		$comment = $notification_event->getObject();
		if (!$comment instanceof \ElggComment) {
			return null;
		}
		
		$object = $comment->getContainerEntity();
		if (!$object instanceof \ElggAnswer) {
			return null;
		}
		
		$actor = $notification_event->getActor();
		$question = $object->getContainerEntity();
		$language = $event->getParam('language');
		
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
	 * @param \Elgg\Event $event 'get', 'subscriptions'
	 *
	 * @return null|array
	 */
	public static function addQuestionOwnerToCommentSubscribers(\Elgg\Event $event): ?array {
		$notification_event = $event->getParam('event');
		if (!$notification_event instanceof SubscriptionNotificationEvent) {
			return null;
		}
		
		$object = $notification_event->getObject();
		if (!$object instanceof \ElggComment) {
			return null;
		}
		
		$container = $object->getContainerEntity();
		if (!$container instanceof \ElggAnswer) {
			return null;
		}
		
		$question = $container->getContainerEntity();
		if (!$question instanceof \ElggQuestion) {
			// something went wrong, maybe access
			return null;
		}
		
		/* @var $owner \ElggUser */
		$owner = $question->getOwnerEntity();
		if ($object->owner_guid === $owner->guid) {
			// don't add question owner if it's the comment owner
			return null;
		}
		
		$filtered_methods = array_keys(array_filter($owner->getNotificationSettings('create_comment')));
		if (empty($filtered_methods)) {
			return null;
		}
		
		$return_value = $event->getValue();
		$return_value[$owner->guid] = $filtered_methods;
		
		return $return_value;
	}
	
	/**
	 * Add question subscribers to the subscribers for a comment on an answer
	 *
	 * @param \Elgg\Event $event 'get', 'subscriptions'
	 *
	 * @return null|array
	 */
	public static function addQuestionSubscribersToCommentSubscribers(\Elgg\Event $event): ?array {
		$notification_event = $event->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent) {
			return null;
		}
		
		$comment = $notification_event->getObject();
		if (!$comment instanceof \ElggComment) {
			return null;
		}
		
		$answer = $comment->getContainerEntity();
		if (!$answer instanceof \ElggAnswer) {
			return null;
		}
		
		$question = $answer->getContainerEntity();
		if (!$question instanceof \ElggQuestion) {
			// something went wrong, maybe access
			return null;
		}
		
		$subscribers = elgg_get_subscriptions_for_container($question->guid);
		if (empty($subscribers)) {
			return null;
		}
		
		if (isset($subscribers[$comment->owner_guid])) {
			// remove the comment owner from the subscribers
			unset($subscribers[$comment->owner_guid]);
		}
		
		return ($event->getValue() + $subscribers);
	}
}
