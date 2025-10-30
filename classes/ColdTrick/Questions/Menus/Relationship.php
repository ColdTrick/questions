<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the relationship menu
 */
class Relationship {
	
	/**
	 * (un)assign group question expert
	 *
	 * @param \Elgg\Event $event 'register', 'menu:relationship'
	 *
	 * @return MenuItems|null
	 */
	public static function toggleGroupExpert(\Elgg\Event $event): ?MenuItems {
		$user = elgg_get_logged_in_user_entity();
		if (!questions_experts_enabled() || !$user instanceof \ElggUser) {
			return null;
		}
		
		$relationship = $event->getParam('relationship');
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'member') {
			return null;
		}
		
		$member = get_user($relationship->guid_one);
		$group = get_entity($relationship->guid_two);
		if (!$member instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return null;
		}
		
		if (!$group->canEdit($user->guid) || !$group->isToolEnabled('questions')) {
			return null;
		}
		
		/** @var MenuItems $items */
		$result = $event->getValue();
		
		$is_expert = $member->hasRelationship($group->guid, QUESTIONS_EXPERT_ROLE);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions_expert',
			'icon' => 'level-up-alt',
			'text' => elgg_echo('questions:menu:user_hover:make_expert'),
			'href' => elgg_generate_action_url('questions/toggle_expert', [
				'user_guid' => $member->guid,
				'guid' => $group->guid,
			]),
			'data-toggle' => 'questions-expert-undo',
			'item_class' => $is_expert ? 'hidden' : null,
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions_expert_undo',
			'icon' => 'level-down-alt',
			'text' => elgg_echo('questions:menu:user_hover:remove_expert'),
			'href' => elgg_generate_action_url('questions/toggle_expert', [
				'user_guid' => $member->guid,
				'guid' => $group->guid,
			]),
			'data-toggle' => 'questions-expert',
			'item_class' => $is_expert ? null : 'hidden',
		]);
		
		return $result;
	}
}
