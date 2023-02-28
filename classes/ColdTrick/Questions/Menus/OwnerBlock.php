<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the owner_block menu
 */
class OwnerBlock {
	
	/**
	 * Add menu items to the owner_block menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return null|MenuItems
	 */
	public static function registerQuestions(\Elgg\Event $event): ?MenuItems {
		/* @var $items MenuItems */
		$items = $event->getValue();
		
		$entity = $event->getEntityParam();
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
