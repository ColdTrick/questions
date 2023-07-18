<?php

$question = elgg_extract('entity', $vars);
$show_group_selector = (bool) elgg_extract('show_group_selector', $vars, true);

$editing = false;
$container_options = false;
$show_access_options = true;
$access_setting = false;

if ($question instanceof \ElggQuestion) {
	$editing = true;
	
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $question->guid,
	]);
}

$container = null;
$container_guid = (int) elgg_extract('container_guid', $vars);
if (!empty($container_guid)) {
	$container = get_entity($container_guid);
}

// build form elements
echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('questions:edit:question:title'),
	'name' => 'title',
	'value' => elgg_extract('title', $vars),
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('questions:edit:question:description'),
	'name' => 'description',
	'value' => elgg_extract('description', $vars),
]);

echo elgg_view_field([
	'#type' => 'tags',
	'#label' => elgg_echo('tags'),
	'name' => 'tags',
	'value' => elgg_extract('tags', $vars),
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('comments'),
	'name' => 'comments_enabled',
	'value' => elgg_extract('comments_enabled', $vars),
	'options_values' => [
		'on' => elgg_echo('on'),
		'off' => elgg_echo('off'),
	],
]);

// access options
if ($container instanceof \ElggUser) {
	$access_setting = questions_get_personal_access_level();
	if ($access_setting !== false) {
		$show_access_options = false;
	}
} elseif ($container instanceof \ElggGroup) {
	$access_setting = questions_get_group_access_level($container);
	if ($access_setting !== false) {
		$show_access_options = false;
	}
}

if ($show_access_options) {
	echo elgg_view_field([
		'#type' => 'access',
		'#label' => elgg_echo('access'),
		'name' => 'access_id',
		'value' => elgg_extract('access_id', $vars),
		'entity_type' => 'object',
		'entity_subtype' => \ElggQuestion::SUBTYPE,
		'entity' => $question,
		'container_guid' => elgg_extract('container_guid', $vars),
	]);
} else {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'access_id',
		'value' => $access_setting,
	]);
}

// container selection options
if (!$editing || (questions_experts_enabled() && questions_is_expert($container))) {
	if ($show_group_selector && elgg_is_active_plugin('groups')) {
		$group_options = [
			'type' => 'group',
			'limit' => false,
			'metadata_name_value_pairs' => [
				'name' => 'questions_enable',
				'value' => 'yes'
			],
			'sort_by' => [
				'property' => 'name',
				'direction' => 'ASC',
			],
			'batch' => true,
		];
		
		if (!$editing) {
			$owner = elgg_get_logged_in_user_entity();
			
			$group_options['relationship'] = 'member';
			$group_options['relationship_guid'] = elgg_get_logged_in_user_guid();
		} else {
			$owner = $question->getOwnerEntity();
		}
		
		// group selector
		$groups = elgg_get_entities($group_options);
		// build group optgroup
		$group_optgroup = [];
		/* @var $group \ElggGroup */
		foreach ($groups as $group) {
			// can questions be asked in this group
			if (!questions_can_ask_question($group)) {
				continue;
			}
			
			$group_optgroup[] = [
				'text' => $group->getDisplayName(),
				'value' => $group->guid,
			];
		}
		
		if (!empty($group_optgroup)) {
			$container_options = true;
			$select_options = [];
			
			// add user to the list
			if (!questions_limited_to_groups()) {
				$select_options[] = [
					'text' => $owner->getDisplayName(),
					'value' => $owner->guid,
				];
			} else {
				$select_options[] = [
					'text' => elgg_echo('questions:edit:question:container:select'),
					'value' => '',
				];
			}
			
			$select_options[] = [
				'label' => elgg_echo('groups'),
				'options' => $group_optgroup,
			];
			
			echo elgg_view_field([
				'#type' => 'select',
				'#label' => elgg_echo('questions:edit:question:container'),
				'name' => 'container_guid',
				'value' => elgg_extract('container_guid', $vars),
				'options_values' => $select_options,
				'required' => true,
			]);
		}
	}
}

if (!$container_options) {
	echo elgg_view_field([
		'#type' => 'container_guid',
		'entity_type' => 'object',
		'entity_subtype' => \ElggQuestion::SUBTYPE,
		'value' => elgg_extract('container_guid', $vars),
	]);
}

// end of the form
$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('submit'),
]);
elgg_set_form_footer($footer);
