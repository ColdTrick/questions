<?php

namespace ColdTrick\Questions;

use Elgg\Database\QueryBuilder;

class Cron {
	
	/**
	 * Automaticly close open questions after x days
	 *
	 * @param \Elgg\Hook $hook 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function autoCloseQuestions(\Elgg\Hook $hook) {
		
		$auto_close_days = (int) elgg_get_plugin_setting('auto_close_time', 'questions');
		if ($auto_close_days < 1) {
			return;
		}
		
		echo "Starting Questions auto-close processing" . PHP_EOL;
		elgg_log("Starting Questions auto-close processing", 'NOTICE');
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($auto_close_days) {
			$site = elgg_get_site_entity();
			
			// get open questions last modified more than x days ago
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
				// close the question
				$question->close();
				
				// notify the user that the question was closed
				$owner = $question->getOwnerEntity();
				
				$subject = elgg_echo('questions:notification:auto_close:subject', [$question->getDisplayName()]);
				$message = elgg_echo('questions:notification:auto_close:message', [
					$owner->getDisplayName(),
					$question->getDisplayName(),
					$auto_close_days,
					$question->getURL(),
				]);
				
				$notification_params = [
					'summary' => elgg_echo('questions:notification:auto_close:summary', [$question->getDisplayName()]),
					'object' => $question,
					'action' => 'close',
				];
				
				notify_user($owner->getGUID(), $site->getGUID(), $subject, $message, $notification_params);
			}
		});
		
		echo "Finished Questions auto-close processing" . PHP_EOL;
		elgg_log("Finished Questions auto-close processing", 'NOTICE');
	}
	
	/**
	 * A plugin hook for the CRON, so we can send out notifications to the experts about their workload
	 *
	 * @param \Elgg\Hook $hook 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function notifyQuestionExperts(\Elgg\Hook $hook) {
	
		// are experts enabled
		if (!questions_experts_enabled()) {
			return;
		}
		
		echo "Starting Questions experts todo notifications" . PHP_EOL;
		elgg_log("Starting Questions experts todo notifications", 'NOTICE');
			
		$time = (int) $hook->getParam('time', time());
		
		// get all experts
		$expert_options = [
			'type' => 'user',
			'limit' => false,
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'inverse_relationship' => true,
			'batch' => true,
		];
		$experts = elgg_get_entities($expert_options);
		
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
			
			$subject = elgg_echo('questions:daily:notification:subject', [], $expert->language);
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
			];
			$question_options['wheres'] = [
				$status_where,
				$container_where
			];
			$question_options['order_by_metadata'] = [
				'name' => 'solution_time',
				'direction' => 'ASC',
				'as' => 'integer'
			];
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:overdue', [], $expert->language) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->language);
				$message .= ' ' . elgg_normalize_url('questions/todo') . PHP_EOL . PHP_EOL;
			}
			
			// get due questions
			// eg: solution_time >= $time && solution_time < ($time + 1 day) && status != closed
			$question_options['metadata_name_value_pairs'] = [
				[
					'name' => 'solution_time',
					'value' => $time,
					'operand' => '>=',
				],
				[
					'name' => 'solution_time',
					'value' => $time + (24 * 60 * 60),
					'operand' => '<',
				],
			];
			
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:due', [], $expert->language) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->language);
				$message .= ' ' . elgg_normalize_url(elgg_generate_url('collection:object:question:todo')) . PHP_EOL . PHP_EOL;
			}
			
			// get new questions
			// eg: time_created >= ($time - 1 day)
			unset($question_options['metadata_name_value_pairs']);
			unset($question_options['order_by_metadata']);
			$question_options['wheres'] = [
				$status_where,
				$container_where,
			];
			$question_options['created_after'] = ($time - (24 * 60 * 60));
			
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:new', [], $expert->language) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], $expert->language);
				$message .= ' ' . elgg_normalize_url(elgg_generate_url('collection:object:question:all')) . PHP_EOL . PHP_EOL;
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
		
		echo "Finished Questions experts todo notifications" . PHP_EOL;
		elgg_log("Finished Questions experts todo notifications", 'NOTICE');
	}
}
