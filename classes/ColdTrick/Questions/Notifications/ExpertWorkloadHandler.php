<?php

namespace ColdTrick\Questions\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification about a Questions expert workload
 */
class ExpertWorkloadHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:daily:notification:subject');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		return elgg_echo('questions:daily:notification:subject');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$message = '';
		
		$overdue = $this->getParam('overdue');
		if (!empty($overdue)) {
			$message .= elgg_echo('questions:daily:notification:message:overdue') . PHP_EOL;
			$message .= $overdue;
			$message .= elgg_echo('questions:daily:notification:message:more');
			$message .= ' ' . elgg_generate_url('collection:object:question:todo') . PHP_EOL . PHP_EOL;
		}
		
		$due = $this->getParam('due');
		if (!empty($due)) {
			$message .= elgg_echo('questions:daily:notification:message:due') . PHP_EOL;
			$message .= $due;
			$message .= elgg_echo('questions:daily:notification:message:more');
			$message .= ' ' . elgg_generate_url('collection:object:question:todo') . PHP_EOL . PHP_EOL;
		}
		
		$new = $this->getParam('new');
		if (!empty($new)) {
			$message .= elgg_echo('questions:daily:notification:message:new') . PHP_EOL;
			$message .= $new;
			$message .= elgg_echo('questions:daily:notification:message:more');
			$message .= ' ' . elgg_generate_url('collection:object:question:all') . PHP_EOL . PHP_EOL;
		}
		
		return trim($message);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationURL(\ElggUser $recipient, string $method): string {
		return elgg_generate_url('collection:object:question:todo');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
}
