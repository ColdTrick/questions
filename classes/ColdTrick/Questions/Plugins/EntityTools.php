<?php

namespace ColdTrick\Questions\Plugins;

use ColdTrick\Questions\Plugins\EntityTools\MigrateQuestions;

class EntityTools {
	
	/**
	 * Add questions to the supported types for EntityTools
	 *
	 * @param \Elgg\Hook $hook 'supported_types', 'entity_tools'
	 *
	 * @return array
	 */
	public static function registerQuestions(\Elgg\Hook $hook) {
		
		$result = $hook->getValue();
		
		$result[\ElggQuestion::SUBTYPE] = MigrateQuestions::class;
		
		return $result;
	}
}
