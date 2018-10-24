<?php

namespace ColdTrick\Questions;

class Cron {
	
	/**
	 * Automaticly close open questions after x days
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param mixed  $return_value current return value
	 * @param mixed  $params       supplied params
	 *
	 * @return void
	 */
	public static function autoCloseQuestions($hook, $type, $return_value, $params) {
		
		$auto_close_days = (int) elgg_get_plugin_setting('auto_close_time', 'questions');
		if ($auto_close_days < 1) {
			return;
		}
		
		echo "Starting Questions auto-close processing" . PHP_EOL;
		elgg_log("Starting Questions auto-close processing", 'NOTICE');
		
		$time = (int) elgg_extract('time', $params, time());
		$site = elgg_get_site_entity();
		
		// ignore access
		$ia = elgg_set_ignore_access(true);
		
		// get open questions last modified more than x days ago
		$batch = new \ElggBatch('elgg_get_entities', [
			'type' => 'object',
			'subtype' => \ElggQuestion::SUBTYPE,
			'limit' => false,
			'metadata_name_value_pairs' => [
				'status' => 'open',
			],
			'modified_time_upper' => $time - ($auto_close_days * 24 * 60 * 60),
		]);
		$batch->setIncrementOffset(false);
		
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
		
		// restore access
		elgg_set_ignore_access($ia);
		
		echo "Finished Questions auto-close processing" . PHP_EOL;
		elgg_log("Finished Questions auto-close processing", 'NOTICE');
	}
	
	/**
	 * A plugin hook for the CRON, so we can send out notifications to the experts about their workload
	 *
	 * @param string $hook        the name of the hook
	 * @param string $type        the type of the hook
	 * @param string $returnvalue current return value
	 * @param array  $params      supplied params
	 *
	 * @return void
	 */
	public static function notifyQuestionExperts($hook, $type, $returnvalue, $params) {
	
		// are experts enabled
		if (!questions_experts_enabled()) {
			return;
		}
		
		echo "Starting Questions experts todo notifications" . PHP_EOL;
		elgg_log("Starting Questions experts todo notifications", 'NOTICE');
			
		$time = (int) elgg_extract('time', $params, time());
		$dbprefix = elgg_get_config('dbprefix');
		$site = elgg_get_site_entity();
	
		// get all experts
		$expert_options = [
			'type' => 'user',
			'site_guids' => false,
			'limit' => false,
			'joins' => ["JOIN {$dbprefix}entity_relationships re2 ON e.guid = re2.guid_one"],
			'wheres' =>["(re2.guid_two = {$site->getGUID()} AND re2.relationship = 'member_of_site')"],
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'inverse_relationship' => true,
		];
		$experts = new ElggBatch('elgg_get_entities', $expert_options);
		
		// sending could take a while
		set_time_limit(0);
				
		$status_where = "NOT EXISTS (
			SELECT 1
			FROM {$dbprefix}metadata md
			WHERE md.entity_guid = e.guid
			AND md.name = 'status'
			AND md.value = 'closed')";
	
		$question_options = [
			'type' => 'object',
			'subtype' => 'question',
			'limit' => 3,
		];
		
		$backup_user = elgg_get_logged_in_user_entity();
		$session = elgg_get_session();
		
		// loop through all experts
		foreach ($experts as $expert) {
			// fake a logged in user
			$session->setLoggedInUser($expert);
			
			$subject = elgg_echo('questions:daily:notification:subject', [], get_current_language());
			$message = '';
			
			$container_where = [];
			if (check_entity_relationship($expert->getGUID(), QUESTIONS_EXPERT_ROLE, $site->getGUID())) {
				$container_where[] = "(e.container_guid NOT IN (
					SELECT ge.guid
					FROM {$dbprefix}entities ge
					WHERE ge.type = 'group'
					AND ge.site_guid = {$site->getGUID()}
					AND ge.enabled = 'yes'
				))";
			}
			
			$groups = elgg_get_entities([
				'type' => 'group',
				'limit' => false,
				'relationship' => QUESTIONS_EXPERT_ROLE,
				'relationship_guid' => $expert->guid,
				'callback' => function ($row) {
					return (int) $row->guid;
				},
			]);
			if (!empty($groups)) {
				$container_where[] = '(e.container_guid IN (' . implode(',', $groups) . '))';
			}
			
			if (empty($container_where)) {
				// no groups or site? then skip to next expert
				continue;
			}
			$container_where = '(' . implode(' OR ', $container_where) . ')';
			
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
				$message .= elgg_echo('questions:daily:notification:message:overdue', [], get_current_language()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], get_current_language());
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
				$message .= elgg_echo('questions:daily:notification:message:due', [], get_current_language()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', [], get_current_language());
				$message .= ' ' . elgg_normalize_url('questions/todo') . PHP_EOL . PHP_EOL;
			}
			
			// get new questions
			// eg: time_created >= ($time - 1 day)
			unset($question_options['metadata_name_value_pairs']);
			unset($question_options['order_by_metadata']);
			$question_options['wheres'] = [
				$container_where,
				'(e.time_created > ' . ($time - (24 * 60 *60)) . ')'
			];
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				$message .= elgg_echo('questions:daily:notification:message:new', [], get_current_language()) . PHP_EOL;
				
				foreach ($questions as $question) {
					$message .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
				
				$message .= elgg_echo('questions:daily:notification:message:more', array(), get_current_language());
				$message .= ' ' . elgg_normalize_url('questions/all') . PHP_EOL . PHP_EOL;
			}
			
			// is there content in the message
			if (!empty($message)) {
				// force to email
				notify_user($expert->getGUID(), $site->getGUID(), $subject, $message, [], 'email');
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
