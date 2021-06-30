<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

class Site {
	
	/**
	 * Register menu item to questions
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:site'
	 *
	 * @return MenuItems
	 */
	public static function registerQuestions(\Elgg\Hook $hook): MenuItems {
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions',
			'icon' => 'question',
			'text' => elgg_echo('questions'),
			'href' => elgg_generate_url('collection:object:question:all'),
		]);
		
		return $result;
	}
}
