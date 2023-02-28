<?php

namespace ColdTrick\Questions\Plugins;

/**
 * Groups support
 */
class Groups {
	
	/**
	 * When an expert leaves the group, remove the expert role
	 *
	 * @param \Elgg\Event $event 'leave', 'group'
	 *
	 * @return void
	 */
	public static function removeExpertRoleOnLeave(\Elgg\Event $event): void {
		$params = $event->getObject();
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		// is the user an expert in this group
		if (!$user->hasRelationship($group->guid, QUESTIONS_EXPERT_ROLE)) {
			return;
		}
		
		// remove the expert role
		$user->removeRelationship($group->guid, QUESTIONS_EXPERT_ROLE);
	}
}
