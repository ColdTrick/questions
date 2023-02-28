<?php

namespace ColdTrick\Questions\Forms;

/**
 * Prepare the form fields for an Answer
 */
class PrepareAnswerFields {
	
	/**
	 * Set default values for the form
	 *
	 * @param \Elgg\Event $event 'form:prepare:fields', 'object/answer/edit'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event): array {
		$vars = $event->getValue();
		
		$values = [
			'description' => '',
			'container_guid' => elgg_get_page_owner_guid(),
		];
		
		// edit of an entity
		$entity = elgg_extract('entity', $vars);
		if ($entity instanceof \ElggAnswer) {
			foreach ($values as $name => $default_value) {
				$values[$name] = $entity->$name;
			}
		}
		
		return array_merge($values, $vars);
	}
}
