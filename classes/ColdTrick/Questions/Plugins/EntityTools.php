<?php

namespace ColdTrick\Questions\Plugins;

use ColdTrick\Questions\Plugins\EntityTools\MigrateQuestions;

/**
 * Support entity_tools
 */
class EntityTools {
	
	/**
	 * Add questions to the supported types for EntityTools
	 *
	 * @param \Elgg\Event $event 'supported_types', 'entity_tools'
	 *
	 * @return array
	 */
	public static function registerQuestions(\Elgg\Event $event): array {
		$result = $event->getValue();
		
		$result[\ElggQuestion::SUBTYPE] = MigrateQuestions::class;
		
		return $result;
	}
}
