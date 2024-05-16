<?php

namespace ColdTrick\Questions;

use Elgg\Database\Seeds\Seed;
use Elgg\Exceptions\Seeding\MaxAttemptsException;
use Elgg\Values;

/**
 * Questions database seeder
 */
class Seeder extends Seed {
	
	/**
	 * {@inheritdoc}
	 */
	public function seed() {
		$this->advance($this->getCount());
		
		$logger = elgg()->logger;
		$session_manager = elgg()->session_manager;
		$logged_in = $session_manager->getLoggedInUser();
		
		$plugin = elgg_get_plugin_from_id('questions');
		$groups_only = $plugin->limit_to_groups === 'yes';
		
		$experts_enabled = $plugin->experts_enabled;
		$experts_answer = $plugin->experts_answer;
		$experts_mark = $plugin->experts_mark;
		
		// for seeding change the settings
		$plugin->experts_enabled = 'no';
		$plugin->experts_answer = 'no';
		$plugin->experts_mark = 'no';
		
		while ($this->getCount() < $this->limit) {
			$owner = $this->getRandomUser();
			
			$session_manager->setLoggedInUser($owner);
			
			$container_guid = $owner->guid;
			if ($groups_only || $this->faker()->boolean()) {
				$group = $this->getRandomGroup();
				$group->enableTool('questions');
				
				$container_guid = $group->guid;
			}
			
			try {
				$logger->disable();
				
				/* @var $entity \ElggQuestion */
				$entity = $this->createObject([
					'subtype' => \ElggQuestion::SUBTYPE,
					'owner_guid' => $owner->guid,
					'container_guid' => $container_guid,
					'comments_enabled' => $this->faker()->boolean() ? 'on' : 'off',
				]);
				
				$logger->enable();
			} catch (MaxAttemptsException $e) {
				// unable to create question with given options
				$logger->enable();
				continue;
			}
			
			if ($entity->commentsEnabled()) {
				$this->createComments($entity, $this->faker()->numberBetween(0, 5));
			}
			
			$this->createLikes($entity);
			$this->createAnswers($entity);
			
			// add river event
			elgg_create_river_item([
				'view' => 'river/object/question/create',
				'action_type' => 'create',
				'subject_guid' => $entity->owner_guid,
				'object_guid' => $entity->guid,
				'target_guid' => $entity->container_guid,
				'access_id' => $entity->access_id,
				'posted' => $entity->time_created,
			]);
			
			// check for a solution time limit
			$solution_time = questions_get_solution_time($entity->getContainerEntity());
			if ($solution_time) {
				// add x number of days when the question should be solved
				$entity->solution_time = Values::normalizeTimestamp("+{$solution_time} days");
			}
			
			if ($this->faker()->boolean(20)) {
				$entity->status = \ElggQuestion::STATUS_CLOSED;
			}
			
			$this->advance();
		}
		
		// restore plugin settings
		$plugin->experts_enabled = $experts_enabled;
		$plugin->experts_answer = $experts_answer;
		$plugin->experts_mark = $experts_mark;
		
		// restore logged in user
		if ($logged_in) {
			$session_manager->setLoggedInUser($logged_in);
		} else {
			$session_manager->removeLoggedInUser();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function unseed() {
		/* @var $entities \ElggBatch */
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => \ElggQuestion::SUBTYPE,
			'metadata_name' => '__faker',
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		]);
		
		/* @var $entity \ElggQuestion */
		foreach ($entities as $entity) {
			if ($entity->delete()) {
				$this->log("Deleted question {$entity->guid}");
			} else {
				$this->log("Failed to delete question {$entity->guid}");
				$entities->reportFailure();
				continue;
			}
			
			$this->advance();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function getType(): string {
		return \ElggQuestion::SUBTYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => \ElggQuestion::SUBTYPE,
		];
	}
	
	/**
	 * Add answers to the question
	 *
	 * @param \ElggQuestion $entity question to create answers for
	 *
	 * @return int
	 */
	protected function createAnswers(\ElggQuestion $entity): int {
		$logger = elgg()->logger;
		$session_manager = elgg()->session_manager;
		$logged_in = $session_manager->getLoggedInUser();
		
		$max_answers = $this->faker()->numberBetween(1, 10);
		$created = [];
		$owners = [];
		for ($i = 0; $i < $max_answers; $i++) {
			$owner = $this->getRandomUser($owners);
			
			$session_manager->setLoggedInUser($owner);
			
			try {
				$logger->disable();
				
				/* @var $answer \ElggAnswer */
				$answer = $this->createObject([
					'subtype' => \ElggAnswer::SUBTYPE,
					'owner_guid' => $owner->guid,
					'container_guid' => $entity->guid,
					'access_id' => $entity->access_id,
				]);
				
				$logger->enable();
			} catch (MaxAttemptsException $e) {
				// unable to create with the given options
				$logger->enable();
				continue;
			}
			
			// remove some seeding data
			unset($answer->title);
			unset($answer->tags);
			
			if ($entity->commentsEnabled()) {
				$this->createComments($answer, $this->faker()->numberBetween(0, 3));
			}
			
			$this->createLikes($answer);
			
			// create river event
			elgg_create_river_item([
				'view' => 'river/object/answer/create',
				'action_type' => 'create',
				'subject_guid' => $answer->owner_guid,
				'object_guid' => $answer->guid,
				'target_guid' => $entity->guid,
				'access_id' => $answer->access_id,
				'posted' => $answer->time_created,
			]);
			
			$created[] = $answer;
			$owners[] = $owner->guid;
		}
		
		// mark an answer as the correct answer
		if (!empty($created) && $this->faker()->boolean(10)) {
			$key = array_rand($created);
			
			/* @var $correct_answer \ElggAnswer */
			$correct_answer = $created[$key];
			$correct_answer->markAsCorrect();
		}
		
		if ($logged_in) {
			$session_manager->setLoggedInUser($logged_in);
		} else {
			$session_manager->removeLoggedInUser();
		}
		
		return count($created);
	}
}
