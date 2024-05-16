<?php
/**
 * Elgg questions plugin owner page
 */

elgg_group_tool_gatekeeper('questions');

/* @var $page_owner \ElggGroup */
$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE, $page_owner);

elgg_register_title_button('add', 'object', \ElggQuestion::SUBTYPE);

// draw page
echo elgg_view_page(elgg_echo('questions:owner', [$page_owner->getDisplayName()]), [
	'content' => elgg_view('questions/listing/group', [
		'entity' => $page_owner,
		'tags' => get_input('tags'),
	]),
	'filter_id' => 'questions/groups',
	'filter_value' => 'all',
]);
