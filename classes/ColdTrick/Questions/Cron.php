<?php

namespace ColdTrick\Questions;

use Elgg\Database\MetadataTable;
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
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($auto_close_days) {
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
				/* @var $owner \ElggUser */
				$owner = $question->getOwnerEntity();
				
				// close the question
				$question->close();
				
				// notify the user that the question was closed
				$owner->notify('close', $question, [
					'days' => $auto_close_days,
				]);
			}
		});
	}
	
	/**
	 * An event handler for the CRON, so we can send out notifications to the experts about their workload
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
			$sub = $qb->subquery(MetadataTable::TABLE_NAME)
				->select('entity_guid')
				->where($qb->compare('name', '=', 'status', ELGG_VALUE_STRING))
				->andWhere($qb->compare('value', '=', \ElggQuestion::STATUS_CLOSED, ELGG_VALUE_STRING));
			
			return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
		};
		
		$question_options = [
			'type' => 'object',
			'subtype' => \ElggQuestion::SUBTYPE,
			'limit' => 3,
		];
		
		$backup_user = elgg_get_logged_in_user_entity();
		$session_manager = elgg()->session_manager;
		
		// loop through all experts
		/* @var $expert \ElggUser */
		foreach ($experts as $expert) {
			// fake a logged-in user
			$session_manager->setLoggedInUser($expert);
			
			$overdue = '';
			$due = '';
			$new = '';
			
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
				'type' => ELGG_VALUE_INTEGER,
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
				foreach ($questions as $question) {
					$overdue .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
			}
			
			// get due questions
			// eg: solution_time >= $time && solution_time < ($time + 1 day) && status != closed
			$question_options['metadata_name_value_pairs'] = [
				[
					'name' => 'solution_time',
					'value' => $time,
					'operand' => '>=',
					'type' => ELGG_VALUE_INTEGER,
				],
				[
					'name' => 'solution_time',
					'value' => Values::normalizeTime($time)->modify('+1 day')->getTimestamp(),
					'operand' => '<',
					'type' => ELGG_VALUE_INTEGER,
				],
			];
			
			$questions = elgg_get_entities($question_options);
			if (!empty($questions)) {
				foreach ($questions as $question) {
					$due .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
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
				foreach ($questions as $question) {
					$new .= " - {$question->getDisplayName()} ({$question->getURL()})" . PHP_EOL;
				}
			}
			
			// is there content for the message
			if (!empty($overdue) || !empty($due) || !empty($new)) {
				$expert->notify('questions_expert_workload', $expert, [
					'overdue' => $overdue,
					'due' => $due,
					'new' => $new,
				]);
			}
		}
		
		if (!empty($backup_user)) {
			$session_manager->setLoggedInUser($backup_user);
		} else {
			$session_manager->removeLoggedInUser();
		}
	}
}
