<?php
/**
 * Questions widget settings
 */

$widget = elgg_extract('entity', $vars);

if ($widget->context === 'dashboard') {
	$content_type_options = [
		'mine' => elgg_echo('mine'),
		'all' => elgg_echo('all')
	];
	if (questions_is_expert()) {
		$content_type_options['todo'] = elgg_echo('questions:todo');
	}
	
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('widget:questions:content_type'),
		'name' => 'params[content_type]',
		'value' => $widget->content_type,
		'options_values' => $content_type_options,
	]);
}

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'default' => 5,
	'name' => 'limit',
	'min' => 1,
]);

echo elgg_view_field([
	'#type' => 'tags',
	'#label' => elgg_echo('tags'),
	'name' => 'params[filter_tags]',
	'value' => $widget->filter_tags,
]);
