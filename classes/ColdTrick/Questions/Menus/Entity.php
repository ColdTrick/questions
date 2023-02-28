<?php

namespace ColdTrick\Questions\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the entity menu
 */
class Entity {
	
	/**
	 * Add menu items to the answer entity menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return null|MenuItems
	 */
	public static function registerAnswer(\Elgg\Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggAnswer) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$question = $entity->getContainerEntity();
		if ($question instanceof \ElggQuestion && $question->getStatus() === \ElggQuestion::STATUS_CLOSED) {
			$result->remove('edit');
		}
		
		if (!$entity->canMarkAnswer()) {
			return $result;
		}
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions-mark',
			'text' => elgg_echo('questions:menu:entity:answer:mark'),
			'href' => elgg_generate_action_url('answers/toggle_mark', [
				'guid' => $entity->guid,
			]),
			'icon' => 'check',
			'item_class' => $entity->isCorrectAnswer() ? 'hidden' : '',
			'data-toggle' => 'questions-unmark',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions-unmark',
			'text' => elgg_echo('questions:menu:entity:answer:unmark'),
			'href' => elgg_generate_action_url('answers/toggle_mark', [
				'guid' => $entity->guid,
			]),
			'icon' => 'undo',
			'item_class' => $entity->isCorrectAnswer() ? '' : 'hidden',
			'data-toggle' => 'questions-mark',
		]);
		
		return $result;
	}
}
