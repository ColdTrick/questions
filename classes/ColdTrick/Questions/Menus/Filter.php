<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;
use Elgg\Router\Route;

class Filter {
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:questions'
	 *
	 * @return void|MenuItems
	 */
	public static function registerQuestions(\Elgg\Hook $hook) {
		
		/* @var $items MenuItems */
		$items = $hook->getValue();
		
		$page_owner = elgg_get_page_owner_entity();
		
		// remove friends
		$items->remove('friend');
		
		if ($page_owner instanceof \ElggGroup) {
			$items->remove('mine');
			
			$all = $items->get('all');
			if ($all instanceof \ElggMenuItem) {
				$all->setHref(elgg_generate_url('collection:object:question:group', [
					'guid' => $page_owner->guid,
				]));
				
				$route = elgg_get_current_route();
				if ($route instanceof Route && !get_input('tags')) {
					if ($route->getName() === 'collection:object:question:group') {
						$all->setSelected(true);
					}
				}
				
				$items->add($all);
			}
		}
		
		if (questions_is_expert()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'todo',
				'text' => elgg_echo('questions:menu:filter:todo'),
				'href' => elgg_generate_url('collection:object:question:todo'),
				'priority' => 700,
			]);
			
			if ($page_owner instanceof \ElggGroup && questions_is_expert($page_owner)) {
				$items[] = \ElggMenuItem::factory([
					'name' => 'todo_group',
					'text' => elgg_echo('questions:menu:filter:todo_group'),
					'href' => elgg_generate_url('collection:object:question:todo', [
						'group_guid' => $page_owner->guid,
					]),
					'priority' => 710,
				]);
			}
		}
		
		if (questions_experts_enabled()) {
			$route_params = [];
			if ($page_owner instanceof \ElggGroup) {
				$route_params['group_guid'] = $page_owner->guid;
			}
			
			$items[] = \ElggMenuItem::factory([
				'name' => 'experts',
				'text' => elgg_echo('questions:menu:filter:experts'),
				'href' => elgg_generate_url('collection:object:question:experts', $route_params),
				'priority' => 800,
			]);
		}
		
		return $items;
	}
}
