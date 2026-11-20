<?php

namespace ColdTrick\Questions;

/**
 * Change permissions
 */
class Permissions {
	
	/**
	 * Check if a user can create a question in a container
	 *
	 * @param \Elgg\Event $event 'container_logic_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function questionContainerLogic(\Elgg\Event $event): ?bool {
		$container = $event->getParam('container');
		$subtype = $event->getParam('subtype');
		if ($subtype !== \ElggQuestion::SUBTYPE || !$container instanceof \ElggUser) {
			return null;
		}
		
		return elgg_get_plugin_setting('limit_to_groups', 'questions') === 'no';
	}
	
	/**
	 * limit the container write permissions
	 *
	 * @param \Elgg\Event $event 'container_permissions_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function questionGroupContainer(\Elgg\Event $event): ?bool {
		$subtype = $event->getParam('subtype');
		if ($subtype !== \ElggQuestion::SUBTYPE) {
			return null;
		}
		
		$user = $event->getUserParam();
		$container = $event->getParam('container');
		if (!$user instanceof \ElggUser || !$container instanceof \ElggGroup) {
			return null;
		}
		
		if (!questions_experts_enabled() || $container->getPluginSetting('questions', 'who_can_ask') !== 'experts') {
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
		
		/** @var bool $returnvalue  */
		$returnvalue = $event->getValue();
		if ($entity instanceof \ElggAnswer) {
			$container = $entity->getContainerEntity()?->getContainerEntity();

			// use default access for group editors to moderate answers otherwise only allow for owners
			if (!$container instanceof \ElggGroup || !$container->canEdit($user->guid)) {
				$returnvalue = $entity->owner_guid === $user->guid;
			}
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
		$question = $event->getParam('container');
		$user = $event->getUserParam();
		$subtype = $event->getParam('subtype');
		
		if ($subtype !== \ElggAnswer::SUBTYPE || !$user instanceof \ElggUser || !$question instanceof \ElggQuestion) {
			return null;
		}
		
		return questions_can_answer_question($question, $user);
	}
	
	/**
	 * Check if a user can create an answer in a container
	 *
	 * @param \Elgg\Event $event 'container_logic_check', 'object'
	 *
	 * @return null|bool
	 */
	public static function answerContainerLogic(\Elgg\Event $event): ?bool {
		if ($event->getParam('subtype') !== \ElggAnswer::SUBTYPE) {
			return null;
		}
		
		return $event->getParam('container') instanceof \ElggQuestion;
	}
	
	/**
	 * Prevent comments on Answers when disabled on the parent Question
	 *
	 * @param \Elgg\Event $event 'container_logic_check', 'object'
	 *
	 * @return bool|null
	 */
	public static function preventAnswerCommentsWhenDisabled(\Elgg\Event $event): ?bool {
		if ($event->getParam('subtype') !== 'comment') {
			return null;
		}
		
		$container = $event->getParam('container');
		if (!$container instanceof \ElggAnswer) {
			return null;
		}
		
		$question = $container->getContainerEntity();
		if ($question instanceof \ElggQuestion && !$question->commentsEnabled()) {
			return false;
		}
		
		return null;
	}
	
	/**
	 * Prevent comments on Questions when disabled
	 *
	 * @param \Elgg\Event $event 'container_logic_check', 'object'
	 *
	 * @return bool|null
	 */
	public static function preventQuestionCommentsWhenDisabled(\Elgg\Event $event): ?bool {
		if ($event->getParam('subtype') !== 'comment') {
			return null;
		}
		
		$container = $event->getParam('container');
		if (!$container instanceof \ElggQuestion) {
			return null;
		}
		
		if (!$container->commentsEnabled()) {
			return false;
		}
		
		return null;
	}
}
