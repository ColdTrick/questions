<?php

namespace ColdTrick\Questions\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

class MigrateGroupSettings implements AsynchronousUpgrade {

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): int {
		return 2021070101;
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}

	/**
	 * {@inheritDoc}
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getOptions());
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(Result $result, $offset): Result {
		
		$groups = elgg_get_entities($this->getOptions([
			'offset' => $offset,
		]));
		/* @var $group \ElggGroup */
		foreach ($groups as $group) {
			// save in new location
			$group->setPluginSetting('questions', 'auto_mark_correct', $group->getPrivateSetting('questions_auto_mark_correct'));
			$group->setPluginSetting('questions', 'solution_time', $group->getPrivateSetting('questions_solution_time'));
			$group->setPluginSetting('questions', 'who_can_ask', $group->getPrivateSetting('questions_who_can_ask'));
			
			// remove old settings
			$group->removePrivateSetting('questions_auto_mark_correct');
			$group->removePrivateSetting('questions_solution_time');
			$group->removePrivateSetting('questions_who_can_ask');
			
			$result->addSuccesses();
		}
		
		return $result;
	}
	
	/**
	 * Get options for elgg_get_entities()
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getOptions(array $options = []): array {
		$defaults = [
			'type' => 'group',
			'limit' => 50,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
			'private_setting_names' => [
				'questions_auto_mark_correct',
				'questions_solution_time',
				'questions_who_can_ask',
			],
		];
		
		return array_merge($defaults, $options);
	}
}
