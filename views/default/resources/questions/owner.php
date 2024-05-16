<?php
/**
 * Elgg questions plugin owner page
 */

$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE, $page_owner);

elgg_register_title_button('add', 'object', \ElggQuestion::SUBTYPE);

// draw page
echo elgg_view_page(elgg_echo('questions:owner', [$page_owner->getDisplayName()]), [
	'content' => elgg_view('questions/listing/owner', [
		'entity' => $page_owner,
		'tags' => get_input('tags'),
	]),
	'filter_id' => 'questions',
	'filter_value' => ($page_owner->guid === elgg_get_logged_in_user_guid()) ? 'mine' : 'none',
]);
