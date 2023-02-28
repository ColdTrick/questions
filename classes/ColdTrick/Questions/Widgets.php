<?php

namespace ColdTrick\Questions;

/**
 * Changes to widgets
 */
class Widgets {
	
	/**
	 * Return the widget title url
	 *
	 * @param \Elgg\Event $event 'entity:url', 'object'
	 *
	 * @return null|string
	 */
	public static function getURL(\Elgg\Event $event): ?string {
		if ($event->getValue()) {
			// already set
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggWidget || $entity->handler !== 'questions') {
			return null;
		}
		
		$owner = $entity->getOwnerEntity();
		if ($owner instanceof \ElggUser) {
			if ($entity->context === 'dashboard') {
				switch ($entity->content_type) {
					case 'all':
						return elgg_generate_url('collection:object:question:all');
						
					case 'todo':
						if (questions_is_expert()) {
							return elgg_generate_url('collection:object:question:todo');
						}
						break;
				}
			}
			
			return elgg_generate_url('collection:object:question:owner', [
				'username' => $owner->username,
			]);
		} elseif ($owner instanceof \ElggGroup) {
			return elgg_generate_url('collection:object:question:group', [
				'guid' => $owner->guid,
			]);
		}
		
		// custom group selected?
		$groups = $entity->group_guid;
		if (!empty($groups)) {
			return elgg_generate_url('collection:object:question:group', [
				'guid' => $groups[0],
			]);
		}
		
		return elgg_generate_url('collection:object:question:all');
	}
}
