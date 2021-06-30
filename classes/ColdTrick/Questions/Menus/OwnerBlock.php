<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

class OwnerBlock {
	
	/**
	 * Add menu items to the owner_block menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:owner_block'
	 *
	 * @return void|MenuItems
	 */
	public static function registerQuestions(\Elgg\Hook $hook) {
		
		/* @var $items MenuItems */
		$items = $hook->getValue();
		
		$entity = $hook->getEntityParam();
		if ($entity instanceof \ElggGroup && $entity->isToolEnabled('questions')) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'questions',
				'text' => elgg_echo('questions:group'),
				'href' => elgg_generate_url('collection:object:question:group', [
					'guid' => $entity->guid,
				]),
			]);
		} elseif ($entity instanceof \ElggUser) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'questions',
				'text' => elgg_echo('questions'),
				'href' => elgg_generate_url('collection:object:question:owner', [
					'username' => $entity->username,
				]),
			]);
		}
		
		return $items;
	}
}
