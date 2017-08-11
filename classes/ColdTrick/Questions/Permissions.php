<?php

namespace ColdTrick\Questions;

class Permissions {
	
	/**
	 * limit the container write permissions
	 *
	 * @param string $hook        the name of the hook
	 * @param string $type        the type of the hook
	 * @param bool   $returnvalue current return value
	 * @param array  $params      supplied params
	 *
	 * @return void|bool
	 */
	public static function questionsContainer($hook, $type, $returnvalue, $params) {
		
		$subtype = elgg_extract('subtype', $params);
		if ($subtype !== \ElggQuestion::SUBTYPE) {
			return;
		}
		
		$user = elgg_extract('user', $params);
		$container = elgg_extract('container', $params);
		if (!($user instanceof \ElggUser) || !($container instanceof \ElggEntity)) {
			return false;
		}
		
		if (!($container instanceof \ElggGroup)) {
			// personal
			return;
		}
		
		// group
		if ($container->questions_enable !== 'yes') {
			// questions not enabled in this group
			return false;
		}
		
		if (!questions_experts_enabled() || ($container->getPrivateSetting('questions_who_can_ask') !== 'experts')) {
			// no experts enabled, or not limited to experts
			return;
		}
		
		return questions_is_expert($container, $user);
	}
}
