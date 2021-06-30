<?php

namespace ColdTrick\Questions\Plugins\EntityTools;

use ColdTrick\EntityTools\Migrate;

class MigrateQuestions extends Migrate {
	
	/**
	 * {@inheritDoc}
	 */
	public function canBackDate() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeContainer() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeOwner() {
		return true;
	}
}
