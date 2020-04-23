<?php
/**
 * Elgg questions plugin owner page
 *
 * @package Questions
 */

elgg_entity_gatekeeper(elgg_get_page_owner_guid(), 'group');
elgg_group_tool_gatekeeper('questions');

/* @var $page_owner ElggGroup */
$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', ElggQuestion::SUBTYPE, $page_owner);

elgg_register_title_button('questions', 'add', 'object', ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => ElggQuestion::SUBTYPE,
	'container_guid' => $page_owner->guid,
	'full_view' => false,
	'list_type_toggle' => false,
	'no_results' => elgg_echo('questions:none'),
	'wheres' => [],
];

$tags = get_input('tags');
if (!empty($tags)) {
	if (is_string($tags)) {
		$tags = string_to_tag_array($tags);
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
	'filter_value' => 'all',
]);
