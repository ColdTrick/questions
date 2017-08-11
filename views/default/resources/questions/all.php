<?php
/**
 * Elgg questions plugin everyone page
 *
 * @package ElggQuestions
 */

elgg_register_title_button('questions', 'add', 'object', ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => 'question',
	'full_view' => false,
	'list_type_toggle' => false,
	'no_results' => elgg_echo('questions:none'),
];

$tags = get_input('tags');
if (!empty($tags)) {
	if (is_string($tags)) {
		$tags = string_to_tag_array($tags);
		
	}
	$options['metadata_name_value_pairs'] = [
		'name' => 'tags',
		'value' => $tags,
	];
}

// build content
$title = elgg_echo('questions:everyone');

$content = elgg_list_entities_from_metadata($options);

// build page
$body = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
]);

// draw page
echo elgg_view_page($title, $body);
