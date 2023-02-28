<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;
use Elgg\Router\Route;

/**
 * Add menu items to the filter menu
 */
class Filter {
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:questions'
	 *
	 * @return null|MenuItems
	 */
	public static function registerQuestions(\Elgg\Event $event): ?MenuItems {
		/* @var $items MenuItems */
		$items = $event->getValue();
		
		if (questions_is_expert()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'todo',
				'text' => elgg_echo('questions:menu:filter:todo'),
				'href' => elgg_generate_url('collection:object:question:todo'),
				'priority' => 700,
			]);
		}
		
		if (questions_experts_enabled()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'experts',
				'text' => elgg_echo('questions:menu:filter:experts'),
				'href' => elgg_generate_url('collection:object:question:experts'),
				'priority' => 800,
			]);
		}
		
		return $items;
	}
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:questions/groups'
	 *
	 * @return null|MenuItems
	 */
	public static function registerQuestionsGroups(\Elgg\Event $event): ?MenuItems {
		/* @var $items MenuItems */
		$items = $event->getValue();
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			return null;
		}
		
		if (questions_is_expert($page_owner)) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'todo_group',
				'text' => elgg_echo('questions:menu:filter:todo_group'),
				'href' => elgg_generate_url('collection:object:question:todo', [
					'group_guid' => $page_owner->guid,
				]),
				'priority' => 710,
			]);
		}
		
		if (questions_experts_enabled()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'experts',
				'text' => elgg_echo('questions:menu:filter:experts'),
				'href' => elgg_generate_url('collection:object:question:experts', [
					'group_guid' => $page_owner->guid,
				]),
				'priority' => 800,
			]);
		}
		
		if ($items->count()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'all',
				'text' => elgg_echo('all'),
				'href' => elgg_generate_url('collection:object:question:group', [
					'guid' => $page_owner->guid,
				]),
				'priority' => 200,
			]);
		}
		
		return $items;
	}
}
