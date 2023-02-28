<?php
/**
 * Elgg questions plugin owner page
 */

$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE, $page_owner);

elgg_register_title_button('add', 'object', \ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => \ElggQuestion::SUBTYPE,
	'owner_guid' => $page_owner->guid,
	'full_view' => false,
	'list_type_toggle' => false,
	'no_results' => elgg_echo('questions:none'),
	'wheres' => [],
];

$tags = get_input('tags');
if (!empty($tags)) {
	if (is_string($tags)) {
		$tags = elgg_string_to_array($tags);
	}
	
	$options['wheres'][] = function(\Elgg\Database\QueryBuilder $qb, $main_alias) use ($tags) {
		$ands = [];
		foreach ($tags as $index => $tag) {
			$md = $qb->joinMetadataTable($main_alias, 'guid', 'tags', 'inner', "md{$index}");
		
			$ands[] = $qb->compare("{$md}.value", '=', $tag, ELGG_VALUE_STRING);
		}
		
		return $qb->merge($ands);
	};
}

// build page elements
$title = elgg_echo('questions:owner', [$page_owner->getDisplayName()]);

$filter = elgg_view('questions/filter', ['options' => $options]);

$content = elgg_list_entities($options);

// draw page
echo elgg_view_page($title, [
	'content' => $filter . $content,
	'filter_id' => 'questions',
	'filter_value' => ($page_owner->guid === elgg_get_logged_in_user_guid()) ? 'mine' : 'none',
]);
