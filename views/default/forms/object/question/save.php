<?php

$question = elgg_extract('entity', $vars);
$show_group_selector = (bool) elgg_extract('show_group_selector', $vars, true);

$editing = false;
$container_options = false;
$show_access_options = true;
$access_setting = false;

if ($question instanceof ElggQuestion) {
	$editing = true;
	
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $question->guid,
	]);
}

$container = get_entity(elgg_extract('container_guid', $vars));

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
if ($container instanceof ElggUser) {
	$access_setting = questions_get_personal_access_level();
	if ($access_setting !== false) {
		$show_access_options = false;
	}
} elseif ($container instanceof ElggGroup) {
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
		'entity_subtype' => ElggQuestion::SUBTYPE,
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
			'order_by_metadata' => [
				'name' => 'name',
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
		/* @var $group ElggGroup */
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

// end of the form
$footer_fields = [];
if (!$container_options) {
	$footer_fields[] = [
		'#type' => 'hidden',
		'name' => 'container_guid',
		'value' => elgg_extract('container_guid', $vars),
	];
}

$footer_fields[] = [
	'#type' => 'submit',
	'value' => elgg_echo('submit'),
];

if ($editing && questions_can_move_to_discussions($container)) {
	elgg_require_js('forms/object/question/moveToDiscussion');
	
	$footer_fields[] = [
		'#type' => 'button',
		'icon' => 'exchange-alt',
		'value' => elgg_echo('questions:edit:question:move_to_discussions'),
		'class' => ['elgg-button-action'],
		'id' => 'questions-move-to-discussions',
		'rel' => elgg_echo('questions:edit:question:move_to_discussions:confirm'),
	];
}

$footer = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => $footer_fields,
]);
elgg_set_form_footer($footer);
