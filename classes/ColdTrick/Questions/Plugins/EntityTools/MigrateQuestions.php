<?php

namespace ColdTrick\Questions\Plugins\EntityTools;

use ColdTrick\EntityTools\Migrate;

/**
 * Support entity_tools migrations
 */
class MigrateQuestions extends Migrate {
	
	/**
	 * {@inheritdoc}
	 */
	public function canBackDate(): bool {
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeContainer(): bool {
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeOwner(): bool {
		return true;
	}
}
