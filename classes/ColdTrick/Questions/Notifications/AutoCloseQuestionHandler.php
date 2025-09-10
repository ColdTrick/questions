<?php

namespace ColdTrick\Questions\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification about the auto closing of a question
 */
class AutoCloseQuestionHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$question = $this->getEventEntity();
		if (!$question instanceof \ElggQuestion) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('questions:notification:auto_close:subject', [$question->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$question = $this->getEventEntity();
		if (!$question instanceof \ElggQuestion) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('questions:notification:auto_close:summary', [$question->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$question = $this->getEventEntity();
		if (!$question instanceof \ElggQuestion) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('questions:notification:auto_close:message', [
			$question->getDisplayName(),
			(int) $this->getParam('days'),
			$question->getURL(),
		]);
	}
}
