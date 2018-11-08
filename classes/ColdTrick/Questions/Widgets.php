<?php

namespace ColdTrick\Questions;

class Widgets {
	
	/**
	 * Return the widget title url
	 *
	 * @param \Elgg\Hook $hook 'entity:url', 'object'
	 *
	 * @return void|string
	 */
	public static function getURL(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already set
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggWidget) {
			return;
		}
		
		if ($entity->handler !== 'questions') {
			return;
		}
		
		$owner = $entity->getOwnerEntity();
		
		if ($owner instanceof \ElggUser) {
			if ($entity->context === 'dashboard') {
				switch ($entity->content_type) {
					case 'all':
						return elgg_generate_url('collection:object:question:all');
						break;
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
