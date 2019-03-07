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
		if (!$user instanceof \ElggUser || !$container instanceof \ElggEntity) {
			return false;
		}
		
		if (!$container instanceof \ElggGroup) {
			if (questions_limited_to_groups()) {
				// questions only in groups
				return false;
			}
			
			// personal
			return;
		}
		
		// group
		if (!$container->isToolEnabled('questions')) {
			// questions not enabled in this group
			return false;
		}
		
		if (!questions_experts_enabled() || ($container->getPrivateSetting('questions_who_can_ask') !== 'experts')) {
			// no experts enabled, or not limited to experts
			return;
		}
		
		return questions_is_expert($container, $user);
	}

	/**
	 * Check if a user has permissions
	 *
	 * @param string $hook        the name of the hook
	 * @param string $type        the type of the hook
	 * @param bool   $returnvalue current return value
	 * @param array  $params      supplied params
	 *
	 * @return void|bool
	 */
	public static function objectPermissionsCheck($hook, $type, $returnvalue, $params) {
		
		// get the provided data
		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);
		
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		if (!$entity instanceof \ElggQuestion && !$entity instanceof \ElggAnswer) {
			return;
		}
		
		// expert only changes
		if (questions_experts_enabled()) {
			// check if an expert can edit a question
			if (!$returnvalue && $entity instanceof \ElggQuestion) {
				$container = $entity->getContainerEntity();
				if (!$container instanceof \ElggGroup) {
					$container = elgg_get_site_entity();
				}
				
				if (questions_is_expert($container, $user)) {
					$returnvalue = true;
				}
			}
			
			// an expert should be able to edit an answer, so fix this
			if (!$returnvalue && $entity instanceof \ElggAnswer) {
				// user is not the owner
				if ($entity->owner_guid !== $user->guid) {
					$question = $entity->getContainerEntity();
					
					if ($question instanceof \ElggQuestion) {
						$container = $question->getContainerEntity();
						if (!$container instanceof \ElggGroup) {
							$container = elgg_get_site_entity();
						}
						
						// if the user is an expert
						if (questions_is_expert($container, $user)) {
							$returnvalue = true;
						}
					}
				}
			}
		}
		
		// questions can't be editted by owner if it is closed
		if ($returnvalue && $entity instanceof \ElggQuestion) {
			// is the question closed
			if ($entity->getStatus() === \ElggQuestion::STATUS_CLOSED) {
				// are you the owner
				if ($user->guid === $entity->owner_guid) {
					$returnvalue = false;
				}
			}
		}
		
		return $returnvalue;
	}
	
	/**
	 * Check if a user can write an answer
	 *
	 * @param string $hook        the name of the hook
	 * @param string $type        the type of the hook
	 * @param bool   $returnvalue current return value
	 * @param array  $params      supplied params
	 *
	 * @return void|bool
	 */
	public static function answerContainer($hook, $type, $returnvalue, $params) {
		
		if ($returnvalue) {
			return;
		}
		
		$question = elgg_extract('container', $params);
		$user = elgg_extract('user', $params);
		$subtype = elgg_extract('subtype', $params);
		
		if (($subtype !== \ElggAnswer::SUBTYPE) || !$user instanceof \ElggUser || !$question instanceof \ElggQuestion) {
			return;
		}
		
		return questions_can_answer_question($question, $user);
	}
}
