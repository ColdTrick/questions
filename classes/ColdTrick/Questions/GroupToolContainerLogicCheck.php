<?php

namespace ColdTrick\Questions;

use Elgg\Groups\ToolContainerLogicCheck;

/**
 * Prevent questions from being created if the group tool option is disabled
 */
class GroupToolContainerLogicCheck extends ToolContainerLogicCheck {

	/**
	 * {@inheritdoc}
	 */
	public function getContentType(): string {
		return 'object';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getContentSubtype(): string {
		return \ElggQuestion::SUBTYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getToolName(): string {
		return 'questions';
	}
}
