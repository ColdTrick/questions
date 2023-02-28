<?php

namespace ColdTrick\Questions\Plugins\EntityTools;

use ColdTrick\EntityTools\Migrate;

/**
 * Support entity_tools migrations
 */
class MigrateQuestions extends Migrate {
	
	/**
	 * {@inheritDoc}
	 */
	public function canBackDate(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeContainer(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeOwner(): bool {
		return true;
	}
}
