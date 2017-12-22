<?php

namespace ColdTrick\Questions;

class Groups {
	
	/**
	 * When an expert leaves the group, remove the expert role
	 *
	 * @param string $event  the 'leave' event
	 * @param string $type   for the 'group' type
	 * @param array  $params the provided params
	 *
	 * @return void
	 */
	public static function leave($event, $type, $params) {
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		// is the user an expert in this group
		if (!check_entity_relationship($user->guid, QUESTIONS_EXPERT_ROLE, $group->guid)) {
			return;
		}
		
		// remove the expert role
		remove_entity_relationship($user->guid, QUESTIONS_EXPERT_ROLE, $group->guid);
	}
}
