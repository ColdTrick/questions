<?php
/**
 * All helper functions for the questions plugin can be found in this file.
 */

use Elgg\Database\QueryBuilder;

/**
 * This function checks if expert roles are enabled in the plugin settings
 *
 * @return bool
 */
function questions_experts_enabled() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	if (elgg_get_plugin_setting('experts_enabled', 'questions') === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * This function checks if only experts are allowed to answer in the plugin settings
 *
 * @return bool
 */
function questions_experts_only_answer() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	if (!questions_experts_enabled()) {
		return $result;
	}
	
	$setting = elgg_get_plugin_setting('experts_answer', 'questions');
	if ($setting === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * Check if a user is an expert
 *
 * @param ElggEntity $container the container where a question was asked, leave empty for any relationship
 * @param ElggUser   $user      the user to check (defaults to current user)
 *
 * @return bool
 */
function questions_is_expert(ElggEntity $container = null, ElggUser $user = null) {
	
	if (!questions_experts_enabled()) {
		return false;
	}
	
	// make sure we have a user
	if (!($user instanceof ElggUser)) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (empty($user)) {
		return false;
	}
	
	if ($container instanceof ElggEntity) {
		if ($container instanceof ElggUser) {
			$container = elgg_get_site_entity();
		}
		
		if (($container instanceof ElggSite) || ($container instanceof ElggGroup)) {
			if (check_entity_relationship($user->guid, QUESTIONS_EXPERT_ROLE, $container->guid)) {
				// user has the expert role
				return true;
			}
		}
	} else {
		// check if user has any expert relationship with entity on this site
		return (bool) elgg_get_entities([
			'count' => true,
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'relationship_guid' => $user->guid,
		]);
	}
	
	return false;
}

/**
 * Make sure the provided access_id is valid for this container
 *
 * @param int $access_id      the current access_id
 * @param int $container_guid the container where the entity will be placed
 *
 * @return int
 */
function questions_validate_access_id($access_id, $container_guid) {
	
	$access_id = sanitise_int($access_id);
	if ($access_id === ACCESS_DEFAULT) {
		$access_id = get_default_access();
	}
	
	if (empty($container_guid)) {
		return $access_id;
	}
	
	$container = get_entity($container_guid);
	if (empty($container)) {
		return $access_id;
	}
	
	if ($container instanceof ElggUser) {
		// is a default level defined in the plugin settings
		$personal_access_id = questions_get_personal_access_level();
		if ($personal_access_id !== false) {
			$access_id = $personal_access_id;
		} else {
			// make sure access_id is not a group acl
			$acl = get_access_collection($access_id);
			if ($acl instanceof ElggAccessCollection && ($acl->owner_guid != $container->guid)) {
				// this acl is a group acl, so set to something else
				$access_id = ACCESS_LOGGED_IN;
			}
		}
	} elseif ($container instanceof ElggGroup) {
		// is a default level defined in the plugin settings
		$group_access_id = questions_get_group_access_level($container);
		if ($group_access_id !== false) {
			$access_id = $group_access_id;
		} else {
			$group_acl = $container->getOwnedAccessCollection('group_acl');
			
			// friends access not allowed in groups
			if ($access_id === ACCESS_FRIENDS) {
				if ($group_acl instanceof ElggAccessCollection) {
					// so set it to group access
					$access_id = (int) $group_acl->id;
				} else {
					$access_id = ACCESS_LOGGED_IN;
				}
			}
			
			// check if access is an acl
			$acl = get_access_collection($access_id);
			if ($acl instanceof ElggAccessCollection && ($acl->owner_guid !== $container->guid)) {
				// this acl is an acl, make sure it's the group acl
				if ($group_acl instanceof ElggAccessCollection) {
					$access_id = (int) $group_acl->id;
				} else {
					$access_id = ACCESS_LOGGED_IN;
				}
			}
		}
	}
	
	return $access_id;
}

/**
 * Get the default defined peronal access setting.
 *
 * @return false|int
 */
function questions_get_personal_access_level() {
	static $result;
	
	if (!isset($result)) {
		$result = false;
		
		$setting = elgg_get_plugin_setting('access_personal', 'questions');
		if (!empty($setting) && ($setting !== 'user_defined')) {
			$result = (int) $setting;
		}
	}
	
	return $result;
}

/**
 * Get the default defined group access setting.
 *
 * @param ElggGroup $group the group if the setting is group_acl
 *
 * @return false|int
 */
function questions_get_group_access_level(ElggGroup $group) {
	static $plugin_setting;
	$result = false;
	
	if (!isset($plugin_setting)) {
		$plugin_setting = false;
		
		$setting = elgg_get_plugin_setting('access_group', 'questions');
		if (!empty($setting) && ($setting != 'user_defined')) {
			$plugin_setting = $setting;
		}
	}
	
	if ($plugin_setting) {
		if ($plugin_setting === 'group_acl') {
			$acl = $group->getOwnedAccessCollection('group_acl');
			if ($acl instanceof ElggAccessCollection) {
				$result = (int) $acl->id;
			}
		} else {
			$result = (int) $plugin_setting;
		}
	}
	
	return $result;
}

/**
 * Return the number of days it should take to solve a question.
 *
 * @param ElggEntity $container if a group is provided, first the setting of the group is checked, then the default setting of the site
 *
 * @return int
 */
function questions_get_solution_time(ElggEntity $container) {
	static $plugin_setting;
	
	if (!isset($plugin_setting)) {
		$plugin_setting = (int) elgg_get_plugin_setting('site_solution_time', 'questions');
	}
	
	// get site setting
	$result = $plugin_setting;
	
	// check is group
	if (($container instanceof ElggGroup) && (elgg_get_plugin_setting('solution_time_group', 'questions') === 'yes')) {
		// get group setting
		$group_setting = $container->getPrivateSetting('questions_solution_time');
		if (($group_setting !== false) && ($group_setting !== null)) {
			// we have a valid group setting
			$result = (int) $group_setting;
		}
	}
	
	return $result;
}

/**
 * Check the plugin setting if questions are limited to groups.
 *
 * @return bool
 */
function questions_limited_to_groups() {
	static $result;
	
	if (!isset($result)) {
		$result = false;
		
		$setting = elgg_get_plugin_setting('limit_to_groups', 'questions');
		if ($setting === 'yes') {
			$result = true;
		}
	}
	
	return $result;
}

/**
 * Checks if a question can be moved to the discussion in the container.
 *
 * @param ElggEntity $container the container where the question should become a discussion
 * @param ElggUser   $user      the user trying to move the question, defaults to current user
 *
 * @return bool
 */
function questions_can_move_to_discussions(ElggEntity $container, ElggUser $user = null) {
	
	// make sure we have a user
	if (!$user instanceof ElggUser) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (empty($user)) {
		return false;
	}
	
	// only if container is a group
	if (!$container instanceof ElggGroup) {
		return false;
	}
	
	// is disabled in plugin settings
	if (!elgg_get_plugin_setting('move_to_discussion_allowed', 'questions')) {
		return false;
	}
	
	// only experts can move
	if (!questions_is_expert($container, $user)) {
		return false;
	}
	
	// are discussions enabled
	if (!$container->isToolEnabled('forum')) {
		return false;
	}
	
	return true;
}

/**
 * Check if a user can ask a question in a container
 *
 * @param ElggEntity $container the container to check (default: page_owner)
 * @param ElggUser   $user      the user askting the question (default: current user)
 *
 * @return bool
 */
function questions_can_ask_question(ElggEntity $container = null, ElggUser $user = null) {
	
	// default to page owner
	if (!$container instanceof ElggEntity) {
		$container = elgg_get_page_owner_entity();
	}
	
	if (empty($container)) {
		return false;
	}
	
	// default to current user
	if (!$user instanceof ElggUser) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (empty($user)) {
		// not logged in
		return false;
	}
	
	return $container->canWriteToContainer($user->guid, 'object', ElggQuestion::SUBTYPE);
}

/**
 * Check if a user can answer a question
 *
 * @param ElggQuestion $question the question that needs answer
 * @param ElggUser     $user     the user askting the question (default: current user)
 *
 * @return bool
 */
function questions_can_answer_question(ElggQuestion $question, ElggUser $user = null) {
	static $general_experts_only;
	
	// default to page owner
	if (!($question instanceof ElggQuestion)) {
		return false;
	}
	
	// default to current user
	if (!($user instanceof ElggUser)) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (empty($user)) {
		// not logged in
		return false;
	}
	
	$container = $question->getContainerEntity();
	
	if (!questions_experts_enabled()) {
		
		if (!$container instanceof ElggGroup) {
			// personal question, anybody can answer
			return true;
		}
		
		// only group members can answer
		return questions_can_ask_question($container, $user);
	}
	
	// get plugin setting
	if (!isset($general_experts_only)) {
		$general_experts_only = questions_experts_only_answer();
	}
	
	$is_expert = questions_is_expert($container, $user);
	
	// check general setting
	if ($general_experts_only && !$is_expert) {
		return false;
	}
	
	if (!($container instanceof ElggGroup)) {
		return true;
	}
	
	// check group settings
	$group_experts_only = false;
	if ($container->getPrivateSetting('questions_who_can_answer') === 'experts') {
		$group_experts_only = true;
	}
	
	if ($group_experts_only && !$is_expert) {
		return false;
	}
	
	// are you a group member or can you edit the group
	return ($container->isMember($user) || $container->canEdit($user->getGUID()));
}

/**
 * Automaticly mark an answer as the correct answer, when created by an expert
 *
 * NOTE: for now this is only supported in groups
 *
 * @param ElggEntity $container the container of the questions (group or user)
 * @param ElggUser   $user      the user doing the action (default: current user)
 *
 * @return bool
 */
function questions_auto_mark_answer_correct(ElggEntity $container, ElggUser $user = null) {
	
	if (!($container instanceof ElggGroup)) {
		// for now only supported in groups
		return false;
	}
	
	if (!($user instanceof ElggUser)) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (!($user instanceof ElggUser)) {
		return false;
	}
	
	if (!questions_experts_enabled()) {
		// only applies to experts
		return false;
	}
	
	if (!questions_is_expert($container, $user)) {
		// not an expert
		return false;
	}
	
	// check group setting
	$group_setting = $container->getPrivateSetting('questions_auto_mark_correct');
	
	return ($group_setting === 'yes');
}

/**
 * Prepare the form vars for the question form
 *
 * @param ElggQuestion $question (optional) the question to edit
 *
 * @return array
 */
function questions_prepare_question_form_vars($question = null) {
	
	$defaults = [
		'title' => '',
		'description' => '',
		'tags' => '',
		'comments_enabled' => 'on',
		'access_id' => null,
		'container_guid' => elgg_get_page_owner_guid(),
	];
	
	// load saved values from entity
	if ($question instanceof ElggQuestion) {
		
		foreach ($defaults as $name => $value) {
			$defaults[$name] = $question->$name;
		}
		
		$defaults['entity'] = $question;
	}
	
	// load sticky form
	$sticky_values = elgg_get_sticky_values('question');
	if (!empty($sticky_values)) {
		foreach ($sticky_values as $name => $value) {
			$defaults[$name] = $value;
		}
		
		elgg_clear_sticky_form('question');
	}
	
	return $defaults;
}

/**
 * Get the where clauses to select todo items (open questions)
 *
 * @param int $user_guid GUID of the user to check for (default: current user)
 *
 * @return false|callable
 */
function questions_get_expert_where_sql($user_guid = 0) {
	
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	$user = get_user($user_guid);
	if (!$user instanceof ElggUser) {
		return false;
	}
	
	return function (QueryBuilder $qb, $main_alias) use ($user) {
		$site = elgg_get_site_entity();
		
		$wheres = [];
		
		// site expert
		if (questions_is_expert($site, $user)) {
			// filter all non group questions
			$sub = $qb->subquery('entities')
				->select('guid')
				->where($qb->compare('type', '=', 'group', ELGG_VALUE_STRING))
				->andWhere($qb->compare('enabled', '=', 'yes', ELGG_VALUE_STRING));
			
			$wheres[] = $qb->compare("{$main_alias}.container_guid", 'NOT IN', $sub->getSQL());
		}
		
		// fetch groups where user is expert
		$groups = elgg_get_entities([
			'type' => 'group',
			'limit' => false,
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'relationship_guid' => $user->guid,
			'callback' => function ($row) {
				return (int) $row->guid;
			},
		]);
		if (!empty($groups)) {
			$wheres[] = $qb->compare("{$main_alias}.container_guid", 'IN', $groups);
		}
		
		if (empty($wheres)) {
			return false;
		}
		
		return $qb->merge($wheres, 'OR');
	};
}
