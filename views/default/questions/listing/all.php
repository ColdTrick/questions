<?php
/**
 * List all questions
 *
 * @uses $vars['options']     additional options
 * @uses $vars['show_filter'] show tag filter (default: true)
 * @uses $vars['tags']        filter based on tags
 */

use Elgg\Database\QueryBuilder;

$defaults = [
	'type' => 'object',
	'subtype' => \ElggQuestion::SUBTYPE,
	'no_results' => elgg_echo('questions:none'),
	'wheres' => [],
];

$options = (array) elgg_extract('options', $vars);
$options = array_merge($defaults, $options);

$tags = elgg_extract('tags', $vars);
if (!empty($tags)) {
	if (is_string($tags)) {
		$tags = elgg_string_to_array($tags);
	}
	
	$options['wheres'][] = function(QueryBuilder $qb, $main_alias) use ($tags) {
		$ands = [];
		foreach ($tags as $index => $tag) {
			$md = $qb->joinMetadataTable($main_alias, 'guid', 'tags', 'inner', "md{$index}");
			
			$ands[] = $qb->compare("{$md}.value", '=', $tag, ELGG_VALUE_STRING);
		}
		
		return $qb->merge($ands);
	};
}

if ((bool) elgg_extract('show_filter', $vars, true)) {
	echo elgg_view('questions/filter', ['options' => $options]);
}

echo elgg_list_entities($options);
