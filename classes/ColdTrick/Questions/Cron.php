<?php

namespace ColdTrick\Questions;

use Elgg\Database\QueryBuilder;
use Elgg\Values;

/**
 * Cron handler
 */
class Cron {
	
	/**
	 * Automatically close open questions after x days
	 *
	 * @param \Elgg\Event $event 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function autoCloseQuestions(\Elgg\Event $event): void {
		$auto_close_days = (int) elgg_get_plugin_setting('auto_close_time', 'questions');
		if ($auto_close_days < 1) {
			return;
		}
		
		echo 'Starting Questions auto-close processing' . PHP_EOL;
		elgg_log('Starting Questions auto-close processing', 'NOTICE');
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($auto_close_days) {
			$site = elgg_get_site_entity();
			
			// backup session
			$backup_user = elgg_get_logged_in_user_entity();
			$session = elgg_get_session();
			
			// get open questions last modified more than x days ago
			/* @var $batch \ElggBatch */
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => \ElggQuestion::SUBTYPE,
				'limit' => false,
				'metadata_name_value_pairs' => [
					'status' => 'open',
				],
				'modified_before' => "-{$auto_close_days} days",
				'batch' => true,
				'batch_inc_offset' => false,
			]);
			/* @var $question \ElggQuestion */
			foreach ($batch as $question) {
				$owner = $question->getOwnerEntity();
				$session->setLoggedInUser($owner);
				
				// close the question
				$question->close();
				
				// notify the user that the question was closed
				$subject = elgg_echo('questions:notification:auto_close:subject', [$question->getDisplayName()]);
				$message = elgg_echo('questions:notification:auto_close:message', [
					$question->getDisplayName(),
					$auto_close_days,
					$question->getURL(),
				]);
				
				$notification_params = [
					'summary' => elgg_echo('questions:notification:auto_close:summary', [$question->getDisplayName()]),
					'object' => $question,
					'action' => 'close',
				];
				
				notify_user($owner->guid, $site->guid, $subject, $message, $notification_params);
			}
			
			// restore session
			if ($backup_user instanceof \ElggUser) {
				$session->setLoggedInUser($backup_user);
			} else {
				$session->invalidate();
			}
		});
		
		echo 'Finished Questions auto-close processing' . PHP_EOL;
		elgg_log('Finished Questions auto-close processing', 'NOTICE');
	}
	
	/**
	 * A plugin hook for the CRON, so we can send out notifications to the experts about their workload
	 *
	 * @param \Elgg\Event $event 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function notifyQuestionExperts(\Elgg\Event $event): void {
		// are experts enabled
		if (!questions_experts_enabled()) {
			return;
		}
		
		echo 'Starting Questions experts todo notifications' . PHP_EOL;
		elgg_log('Starting Questions experts todo notifications', 'NOTICE');
			
		$time = (int) $event->getParam('time', time());
		
		// get all experts
		$experts = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'inverse_relationship' => true,
			'batch' => true,
		]);
		
		// sending could take a while
		set_time_limit(0);
		
		$status_where = function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery('metadata')
				->select('entity_guid')
				->where($qb->compare('name', '=', 'status', ELGG_VALUE_STRING))
				->andWhere($qb->compare('value', '=', 'closed', ELGG_VALUE_STRING));
			
			return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
		};
		
		$question_options = [
			'type' => 'object',
			'subtype' => \ElggQuestion::SUBTYPE,
			'limit' => 3,
		];
		
		$backup_user = elgg_get_logged_in_user_entity();
		$session = elgg_get_session();
		
		// loop through all experts
		/* @var $expert \ElggUser */
		foreach ($experts as $expert) {
			// fake a logged in user
			$session->setLoggedInUser($expert);
			
			$subject = elgg_echo('questions:daily:notification:subject', [], $expert->getLanguage());
			$message = '';
			
			$container_where = questions_get_expert_where_sql($expert->guid);
			if (empty($container_where)) {
				// no groups or site? then skip to next expert
				continue;
			}
			
			// get overdue questions
			// eg: solution_time < $time && status != closed
			$question_options['metadata_name_value_pairs'] = [
				'name' => 'solution_time',
				'value' => $time,
				'operand' => '<',
				'as' => 'integer',
			];
			$question_options['wheres'] = [
				$status_where,
				$container_where,
			];
			$question_options['sort_by'] = [
				'property' => 'solution_time',
				'direction' => 'ASC',
				'signed' => true,
			];
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:overdue', [], $expert->getLanguage()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->getLanguage());
				$message .= ' ' . elgg_generate_url('collection:object:question:todo') . PHP_EOL . PHP_EOL;
			}
			
			// get due questions
			// eg: solution_time >= $time && solution_time < ($time + 1 day) && status != closed
			$question_options['metadata_name_value_pairs'] = [
				[
					'name' => 'solution_time',
					'value' => $time,
					'operand' => '>=',
					'as' => 'integer',
				],
				[
					'name' => 'solution_time',
					'value' => Values::normalizeTime($time)->modify('+1 day')->getTimestamp(),
					'operand' => '<',
					'as' => 'integer',
				],
			];
			
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:due', [], $expert->getLanguage()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->getLanguage());
				$message .= ' ' . elgg_generate_url('collection:object:question:todo') . PHP_EOL . PHP_EOL;
			}
			
			// get new questions
			// eg: time_created >= ($time - 1 day)
			unset($question_options['metadata_name_value_pairs']);
			unset($question_options['sort_by']);
			$question_options['wheres'] = [
				$status_where,
				$container_where,
			];
			$question_options['created_after'] = Values::normalizeTime($time)->modify('-1 day')->getTimestamp();
			
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:new', [], $expert->getLanguage()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->getLanguage());
				$message .= ' ' . elgg_generate_url('collection:object:question:all') . PHP_EOL . PHP_EOL;
			}
			
			// is there content in the message
			if (!empty($message)) {
				// force to email
				notify_user($expert->guid, 0, $subject, $message, [], 'email');
			}
		}
		
		if (!empty($backup_user)) {
			$session->setLoggedInUser($backup_user);
		} else {
			$session->invalidate();
		}
		
		echo 'Finished Questions experts todo notifications' . PHP_EOL;
		elgg_log('Finished Questions experts todo notifications', 'NOTICE');
	}
}
