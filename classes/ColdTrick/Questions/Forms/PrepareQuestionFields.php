<?php

namespace ColdTrick\Questions\Forms;

/**
 * Prepare the form fields for a Question
 */
class PrepareQuestionFields {
	
	/**
	 * Set default values for the form
	 *
	 * @param \Elgg\Event $event 'form:prepare:fields', 'object/question/save'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event): array {
		$vars = $event->getValue();
		
		$values = [
			'title' => '',
			'description' => '',
			'tags' => '',
			'comments_enabled' => 'on',
			'access_id' => null,
			'container_guid' => elgg_get_page_owner_guid(),
		];
		
		// edit of an entity
		$entity = elgg_extract('entity', $vars);
		if ($entity instanceof \ElggQuestion) {
			foreach ($values as $name => $default_value) {
				$values[$name] = $entity->$name;
			}
		}
		
		return array_merge($values, $vars);
	}
}
