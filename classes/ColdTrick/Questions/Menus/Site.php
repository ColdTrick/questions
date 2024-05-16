<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the site menu
 */
class Site {
	
	/**
	 * Register menu item to questions
	 *
	 * @param \Elgg\Event $event 'register', 'menu:site'
	 *
	 * @return MenuItems
	 */
	public static function registerQuestions(\Elgg\Event $event): MenuItems {
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions',
			'icon' => 'question',
			'text' => elgg_echo('questions'),
			'href' => elgg_generate_url('default:object:question'),
		]);
		
		return $result;
	}
}
