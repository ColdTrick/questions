<?php

namespace ColdTrick\Questions\Notifications;

use Elgg\Notifications\NotificationEventHandler;

/**
 * Notification handler for when a question is created
 */
class CreateQuestionNotificationEventHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	public function getSubscriptions(): array {
		$result = parent::getSubscriptions();
		
		if (!questions_experts_enabled()) {
			return $result;
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
		
		// trigger an event so others can extend the list
		$params = [
			'entity' => $question,
			'experts' => $experts,
			'moving' => true,
		];
		$experts = elgg_trigger_event_results('notify_experts', 'questions', $params, $experts);
		if (!is_array($experts)) {
			return $result;
		}
		
		foreach ($experts as $expert) {
			if (!$expert instanceof \ElggUser) {
				continue;
			}
			
			if (!isset($result[$expert->guid])) {
				$result[$expert->guid] = ['email'];
			} elseif (!in_array('email', $result[$expert->guid])) {
				$result[$expert->guid][] = 'email';
			}
		}
		
		return $result;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:create:subject', [], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:create:summary', [], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:create:message', [
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
	 * {@inheritdoc}
	 */
	protected static function isConfigurableForGroup(\ElggGroup $group): bool {
		return $group->isToolEnabled('questions');
	}
}
