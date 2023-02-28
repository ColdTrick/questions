<?php

namespace ColdTrick\Questions\Notifications;

use Elgg\Notifications\NotificationEventHandler;

/**
 * Notification handler for when a Question is moved to a different container (group)
 */
class MoveQuestionNotificationEventHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritDoc}
	 */
	public function getSubscriptions(): array {
		if (!questions_experts_enabled()) {
			return [];
		}
		
		$question = $this->getQuestion();
		$container = $question->getContainerEntity();
		if (!$container instanceof \ElggGroup) {
			$container = elgg_get_site_entity();
		}
		
		$experts = [];
		
		$users = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'relationship_guid' => $container->guid,
			'inverse_relationship' => true,
		]);
		if (!empty($users)) {
			$experts = $users;
		}
		
		// trigger a hook so others can extend the list
		$params = [
			'entity' => $question,
			'experts' => $experts,
			'moving' => true,
		];
		$experts = elgg_trigger_event_results('notify_experts', 'questions', $params, $experts);
		if (!is_array($experts)) {
			return [];
		}
		
		$result = [];
		foreach ($experts as $expert) {
			if (!$expert instanceof \ElggUser) {
				continue;
			}
			
			$result[$expert->guid] = ['email'];
		}
		
		return $result;
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:move:subject', [], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:move:summary', [], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:move:message', [
			$this->getQuestion()->getDisplayName(),
			$this->getQuestion()->getURL(),
		], $recipient->getLanguage());
	}
	
	/**
	 * Get the question of this event
	 *
	 * @return \ElggQuestion
	 */
	protected function getQuestion(): \ElggQuestion {
		return $this->event->getObject();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
