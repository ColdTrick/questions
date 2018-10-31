<?php

namespace ColdTrick\Questions;

use ColdTrick\EntityTools\Migrate;

class MigrateQuestions extends Migrate {
	
	/**
	 * Add questions to the supported types for EntityTools
	 *
	 * @param \Elgg\Hook $hook 'supported_types', 'entity_tools'
	 *
	 * @return array
	 */
	public static function supportedSubtypes(\Elgg\Hook $hook) {
		
		$result = $hook->getValue();
		
		$result[\ElggQuestion::SUBTYPE] = self::class;
		
		return $result;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canBackDate()
	 */
	public function canBackDate() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canChangeContainer()
	 */
	public function canChangeContainer() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canChangeOwner()
	 */
	public function canChangeOwner() {
		return true;
	}
}
