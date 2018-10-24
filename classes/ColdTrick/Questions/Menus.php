<?php

namespace ColdTrick\Questions;

class Menus {
	
	/**
	 * Add menu items to the owner_block menu
	 *
	 * @param string         $hook   the name of the hook
	 * @param string         $type   the type of the hook
	 * @param \ElggMenuItem[] $items  current return value
	 * @param array          $params supplied params
	 *
	 * @return void|ElggMenuItem[]
	 */
	public static function registerOwnerBlock($hook, $type, $items, $params) {
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (($entity instanceof \ElggGroup) && ($entity->questions_enable === 'yes')) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'questions',
				'href' => "questions/group/{$entity->guid}/all",
				'text' => elgg_echo('questions:group'),
			]);
		} elseif ($entity instanceof \ElggUser) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'questions',
				'href' => "questions/owner/{$entity->username}",
				'text' => elgg_echo('questions'),
			]);
		}
		
		return $items;
	}
	
	/**
	 * Add menu items to the entity menu
	 *
	 * @param \Elgg\Hook $hook hook
	 *
	 * @return void|ElggMenuItem[]
	 */
	public static function registerEntity(\Elgg\Hook $hook) {
			
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggAnswer) {
			return;
		}
						
		if (!$entity->canMarkAnswer()) {
			return;
		}
		
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'questions-mark',
			'text' => elgg_echo('questions:menu:entity:answer:mark'),
			'href' => "action/answers/toggle_mark?guid={$entity->guid}",
			'is_action' => true,
			'icon' => 'check',
			'item_class' => $entity->isCorrectAnswer() ? 'hidden' : '',
			'data-toggle' => 'questions-unmark',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'questions-unmark',
			'text' => elgg_echo('questions:menu:entity:answer:unmark'),
			'href' => "action/answers/toggle_mark?guid={$entity->guid}",
			'is_action' => true,
			'icon' => 'undo',
			'item_class' => $entity->isCorrectAnswer() ? '' : 'hidden',
			'data-toggle' => 'questions-mark',
		]);

		return $result;
	}
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param string         $hook   the name of the hook
	 * @param string         $type   the type of the hook
	 * @param \ElggMenuItem[] $items  current return value
	 * @param array          $params supplied params
	 *
	 * @return void|ElggMenuItem[]
	 */
	public static function registerFilter($hook, $type, $items, $params) {
	
		if (empty($items) || !is_array($items) || !elgg_in_context('questions')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		$page_owner_guid = elgg_get_page_owner_guid();
		
		// change some menu items
		foreach ($items as $key => $item) {
			// remove friends
			if ($item->getName() == 'friend') {
				unset($items[$key]);
			}
			
			// in group context
			if ($page_owner instanceof ElggGroup) {
				// remove mine
				if ($item->getName() == 'mine') {
					unset($items[$key]);
				}
	
				// check if all is correct
				if ($item->getName() === 'all') {
					// set correct url
					$item->setHref("questions/group/{$page_owner->getGUID()}/all");
					
					// highlight all
					$current_page = current_page_url();
					if (stristr($current_page, "questions/group/{$page_owner->getGUID()}/all") && !get_input('tags')) {
						$item->setSelected(true);
					}
				}
			}
		}
		
		// add tags search
		$session = elgg_get_session();
		$url = '';
		$tags = get_input('tags');
		if (!empty($tags)) {
			$url = 'questions/all';
			if ($page_owner instanceof ElggUser) {
				$url = "questions/owner/{$page_owner->username}";
			} elseif ($page_owner instanceof ElggGroup) {
				$url = "questions/group/{$page_owner->guid}/all";
			}
			
			$session->set("questions_tags_{$page_owner_guid}", [
				'tags' => $tags,
				'url' => $url,
			]);
		} elseif ($session->has("questions_tags_{$page_owner_guid}")) {
			$settings = $session->get("questions_tags_{$page_owner_guid}");
			
			$tags = elgg_extract('tags', $settings);
			$url = elgg_extract('url', $settings);
		}
		
		if (!empty($tags) && !empty($url)) {
			$tags_string = $tags;
			if (is_array($tags_string)) {
				$tags_string = implode(', ', $tags_string);
			}
			
			$items[] = \ElggMenuItem::factory([
				'name' => 'questions_tags',
				'text' => elgg_echo('questions:menu:filter:tags', [$tags_string]),
				'href' => elgg_http_add_url_query_elements($url, ['tags' => $tags, 'offset' => null]),
				'is_trusted' => true,
				'priority' => 600,
			]);
		}
		
		if (questions_is_expert()) {
			$items[] = \ElggMenuItem::factory([
				'name' => 'todo',
				'text' => elgg_echo('questions:menu:filter:todo'),
				'href' => 'questions/todo',
				'priority' => 700,
			]);
	
			if ($page_owner instanceof ElggGroup && questions_is_expert($page_owner)) {
				$items[] = \ElggMenuItem::factory([
					'name' => 'todo_group',
					'text' => elgg_echo('questions:menu:filter:todo_group'),
					'href' => "questions/todo/{$page_owner->getGUID()}",
					'priority' => 710,
				]);
			}
		}
	
		if (questions_experts_enabled()) {
			$experts_href = 'questions/experts';
			if ($page_owner instanceof ElggGroup) {
				$experts_href .= "/{$page_owner->getGUID()}";
			}
	
			$items[] = \ElggMenuItem::factory([
				'name' => 'experts',
				'text' => elgg_echo('questions:menu:filter:experts'),
				'href' => $experts_href,
				'priority' => 800,
			]);
		}
	
		return $items;
	}
	
	/**
	 * Add menu items to the user_hover menu
	 *
	 * @param string         $hook   the name of the hook
	 * @param string         $type   the type of the hook
	 * @param \ElggMenuItem[] $items  current return value
	 * @param array          $params supplied params
	 *
	 * @return void|ElggMenuItem[]
	 */
	public static function registerUserHover($hook, $type, $items, $params) {
		
		// are experts enabled
		if (!questions_experts_enabled()) {
			return;
		}
		
		// get the user for this menu
		$user = elgg_extract('entity', $params);
		if (!$user instanceof ElggUser) {
			return;
		}
		
		// get page owner
		$page_owner = elgg_get_page_owner_entity();
		if (!($page_owner instanceof ElggGroup)) {
			$page_owner = elgg_get_site_entity();
		}
		
		// can the current person edit the page owner, to assign the role
		// and is the current user not the owner of this page owner
		if (!$page_owner->canEdit()) {
			return;
		}
		
		$text = elgg_echo('questions:menu:user_hover:make_expert');
		$confirm_text = elgg_echo('questions:menu:user_hover:make_expert:confirm', [$page_owner->getDisplayName()]);
		if (check_entity_relationship($user->getGUID(), QUESTIONS_EXPERT_ROLE, $page_owner->getGUID())) {
			$text = elgg_echo('questions:menu:user_hover:remove_expert');
			$confirm_text = elgg_echo('questions:menu:user_hover:remove_expert:confirm', [$page_owner->getDisplayName()]);
		}
		
		$items[] = \ElggMenuItem::factory([
			'name' => 'questions_expert',
			'text' => $text,
			'href' => elgg_http_add_url_query_elements('action/questions/toggle_expert', [
				'user_guid' => $user->guid,
				'guid' => $page_owner->guid,
			]),
			'confirm' => $confirm_text,
			'section' => ($page_owner instanceof ElggSite) ? 'admin' : 'default',
		]);
		
		return $items;
	}
}
