<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

class Social {
	
	/**
	 * Removes comments link for answers
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:social'
	 *
	 * @return void|MenuItems
	 */
	public static function removeCommentsLinkForAnswers(\Elgg\Hook $hook) {
		
		if (!$hook->getEntityParam() instanceof \ElggAnswer) {
			return;
		}
		
		/* @var $items MenuItems */
		$items = $hook->getValue();
		$items->remove('comment');
		
		return $items;
	}
}
