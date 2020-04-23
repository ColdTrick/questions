<?php
/**
 * Elgg questions plugin everyone page
 *
 * @package ElggQuestions
 */

elgg_push_collection_breadcrumbs('object', ElggQuestion::SUBTYPE);

elgg_register_title_button('questions', 'add', 'object', ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => ElggQuestion::SUBTYPE,
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

// build content
$title = elgg_echo('questions:everyone');

$filter = elgg_view('questions/filter', ['options' => $options]);

$content = elgg_list_entities($options);

// draw page
echo elgg_view_page($title, [
	'content' => $filter . $content,
	'filter_id' => 'questions',
	'filter_value' => 'all',
]);
