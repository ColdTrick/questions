<?php

namespace ColdTrick\Questions;

/**
 * Change permissions
 */
class Permissions {
	
	/**
	 * limit the container write permissions
	 *
	 * @param \Elgg\Event $event 'container_permissions_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function questionsContainer(\Elgg\Event $event): ?bool {
		$subtype = $event->getParam('subtype');
		if ($subtype !== \ElggQuestion::SUBTYPE) {
			return null;
		}
		
		$user = $event->getUserParam();
		$container = $event->getParam('container');
		if (!$user instanceof \ElggUser || !$container instanceof \ElggEntity) {
			return false;
		}
		
		if (!$container instanceof \ElggGroup) {
			if (questions_limited_to_groups()) {
				// questions only in groups
				return false;
			}
			
			// personal
			return null;
		}
		
		// group
		if (!$container->isToolEnabled('questions')) {
			// questions not enabled in this group
			return false;
		}
		
		if (!questions_experts_enabled() || ($container->getPluginSetting('questions', 'who_can_ask') !== 'experts')) {
			// no experts enabled, or not limited to experts
			return null;
		}
		
		return questions_is_expert($container, $user);
	}

	/**
	 * Check if a user has permissions
	 *
	 * @param \Elgg\Event $event 'permissions_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function objectPermissionsCheck(\Elgg\Event $event): ?bool {
		// get the provided data
		$user = $event->getUserParam();
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggQuestion && !$entity instanceof \ElggAnswer) {
			return null;
		}
		
		/* @var $returnvalue bool */
		$returnvalue = $event->getValue();
		
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
		
		// questions can't be edited by owner if it is closed
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
	 * @param \Elgg\Event $event 'container_permissions_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function answerContainer(\Elgg\Event $event): ?bool {
		if ($event->getValue()) {
			return null;
		}
		
		$question = $event->getParam('container');
		$user = $event->getUserParam();
		$subtype = $event->getParam('subtype');
		
		if ($subtype !== \ElggAnswer::SUBTYPE || !$user instanceof \ElggUser || !$question instanceof \ElggQuestion) {
			return null;
		}
		
		return questions_can_answer_question($question, $user);
	}
}
