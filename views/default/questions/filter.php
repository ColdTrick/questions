<?php

use Elgg\Database\MetadataTable;
use Elgg\Database\QueryBuilder;

$tags = get_input('tags', []);
if (!is_array($tags)) {
	$tags = [$tags];
}

$options = (array) elgg_extract('options', $vars, []);
$options['threshold'] = 2;
$options['limit'] = 10;
$options['tag_names'] = 'tags';

$options['wheres'] = [];
$options['wheres'][] = function(QueryBuilder $qb, $main_alias) use ($tags) {
	$subquery = $qb->subquery(MetadataTable::TABLE_NAME, 'md_sub');
	$subquery->select("{$subquery->getTableAlias()}.entity_guid");
	
	foreach ($tags as $index => $tag) {
		$md = $subquery->joinMetadataTable($subquery->getTableAlias(), 'entity_guid', 'tags', 'inner', "mdf{$index}");
		
		$subquery->andWhere($qb->compare("{$md}.value", '=', $tag, ELGG_VALUE_STRING));
	}
	
	return $qb->compare("{$main_alias}.entity_guid", 'in', $subquery->getSQL());
};

$items = [];
foreach ($tags as $tag) {
	$new_tags = $tags;
	unset($new_tags[array_search($tag, $new_tags)]);
	
	if (empty($new_tags)) {
		$new_tags = null;
	}
	
	$items[] = \ElggMenuItem::factory([
		'name' => $tag,
		'text' => $tag,
		'href' => elgg_http_add_url_query_elements(elgg_get_current_url(), ['tags' => $new_tags]),
		'class' => 'elgg-state-active',
		'icon_alt' => 'delete',
	]);
}

if (count($tags) < 5) {
	$available_tags = elgg_get_tags($options);
	
	foreach ($available_tags as $tag) {
		if (in_array($tag->tag, $tags)) {
			continue;
		}
		
		$new_tags = $tags;
		$new_tags[] = $tag->tag;
		$new_tags = array_unique($new_tags);
		
		$items[] = \ElggMenuItem::factory([
			'name' => $tag->tag,
			'text' => $tag->tag,
			'href' => elgg_http_add_url_query_elements(elgg_get_current_url(), ['tags' => $new_tags]),
		]);
	}
}

if (empty($items)) {
	return;
}

$content = elgg_format_element('strong', [], elgg_echo('questions:filter_by_tag') . ': ');
$content .= elgg_view_menu('questions_tags', [
	'class' => ['elgg-menu-hz'],
	'items' => $items,
	'item_class' => 'elgg-tag',
]);

echo elgg_format_element('div', [
	'class' => ['questions-tags-filter', 'elgg-tags'],
], $content);
