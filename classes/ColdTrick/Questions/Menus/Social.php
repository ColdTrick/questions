<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the social menu
 */
class Social {
	
	/**
	 * Removes comments link for answers
	 *
	 * @param \Elgg\Event $event 'register', 'menu:social'
	 *
	 * @return null|MenuItems
	 */
	public static function removeCommentsLinkForAnswers(\Elgg\Event $event): ?MenuItems {
		if (!$event->getEntityParam() instanceof \ElggAnswer) {
			return null;
		}
		
		/* @var $items MenuItems */
		$items = $event->getValue();
		
		$items->remove('comment');
		
		return $items;
	}
}
