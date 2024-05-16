<?php
/**
 * Elgg questions plugin everyone page
 */

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE);

elgg_register_title_button('add', 'object', \ElggQuestion::SUBTYPE);

// draw page
echo elgg_view_page(elgg_echo('questions:everyone'), [
	'content' => elgg_view('questions/listing/all', [
		'tags' => get_input('tags'),
	]),
	'filter_id' => 'questions',
	'filter_value' => 'all',
]);
