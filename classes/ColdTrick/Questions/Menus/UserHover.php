<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the user_hover menu
 */
class UserHover {
	
	/**
	 * Add menu items to the user_hover menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:user_hover'
	 *
	 * @return null|MenuItems
	 */
	public static function registerToggleExpert(\Elgg\Event $event): ?MenuItems {
		// are experts enabled
		if (!questions_experts_enabled()) {
			return null;
		}
		
		// get the user for this menu
		$user = $event->getEntityParam();
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		// get page owner
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			$page_owner = elgg_get_site_entity();
		}
		
		// can the current person edit the page owner, to assign the role
		// and is the current user not the owner of this page owner
		if (!$page_owner->canEdit()) {
			return null;
		}
		
		/* @var $items MenuItems */
		$items = $event->getValue();
		
		$is_expert = $user->hasRelationship($page_owner->guid, QUESTIONS_EXPERT_ROLE);
		
		$items[] = \ElggMenuItem::factory([
			'name' => 'questions_expert',
			'icon' => 'level-up-alt',
			'text' => elgg_echo('questions:menu:user_hover:make_expert'),
			'href' => elgg_generate_action_url('questions/toggle_expert', [
				'user_guid' => $user->guid,
				'guid' => $page_owner->guid,
			]),
			'section' => ($page_owner instanceof \ElggSite) ? 'admin' : 'action',
			'data-toggle' => 'questions-expert-undo',
			'item_class' => $is_expert ? 'hidden' : null,
		]);
		
		$items[] = \ElggMenuItem::factory([
			'name' => 'questions_expert_undo',
			'icon' => 'level-down-alt',
			'text' => elgg_echo('questions:menu:user_hover:remove_expert'),
			'href' => elgg_generate_action_url('questions/toggle_expert', [
				'user_guid' => $user->guid,
				'guid' => $page_owner->guid,
			]),
			'section' => ($page_owner instanceof \ElggSite) ? 'admin' : 'action',
			'data-toggle' => 'questions-expert',
			'item_class' => $is_expert ? null : 'hidden',
		]);
		
		return $items;
	}
}
