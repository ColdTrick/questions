<?php
/**
 * List most recent questions on group profile page
 *
 * @package Questions
 */

$group = elgg_extract('entity', $vars);
if (!$group instanceof ElggGroup) {
	return;
}

$all_link = elgg_view('output/url', [
	'href' => elgg_generate_url('collection:object:question:group', [
		'guid' => $group->guid,
	]),
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
]);

elgg_push_context('widgets');
$options = [
	'type' => 'object',
	'subtype' => ElggQuestion::SUBTYPE,
	'container_guid' => $group->guid,
	'limit' => 6,
	'full_view' => false,
	'pagination' => false,
];
$content = elgg_list_entities($options);
elgg_pop_context();

if (!$content) {
	$content = elgg_view('output/longtext', ['value' => elgg_echo('questions:none')]);
}

$new_link = '';
if ($group->canWriteToContainer(0, 'object', ElggQuestion::SUBTYPE)) {
	$new_link = elgg_view('output/url', [
		'href' => elgg_generate_url('add:object:question', [
			'guid' => $group->guid,
		]),
		'text' => elgg_echo('questions:add'),
		'is_trusted' => true,
	]);
}

echo elgg_view('groups/profile/module', [
	'title' => elgg_echo('questions:group'),
	'content' => $content,
	'all_link' => $all_link,
	'add_link' => $new_link,
]);
