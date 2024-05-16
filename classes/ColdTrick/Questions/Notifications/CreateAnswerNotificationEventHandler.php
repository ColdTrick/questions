<?php

namespace ColdTrick\Questions\Notifications;

use Elgg\Notifications\NotificationEventHandler;

/**
 * Notification handler for when an answer is created
 */
class CreateAnswerNotificationEventHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:answer:create:subject', [$this->getQuestion()->getDisplayName()], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:answer:create:summary', [$this->getQuestion()->getDisplayName()], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:notifications:answer:create:message', [
			$this->event->getActor()->getDisplayName(),
			$this->getQuestion()->getDisplayName(),
			$this->event->getObject()->description,
			$this->event->getObject()->getURL(),
		], $recipient->getLanguage());
	}
	
	/**
	 * Get the question for this answer
	 *
	 * @return \ElggQuestion
	 */
	protected function getQuestion(): \ElggQuestion {
		return $this->event->getObject()->getContainerEntity();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
