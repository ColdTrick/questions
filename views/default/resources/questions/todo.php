<?php
/**
 * Elgg questions plugin todo page
 */

use Elgg\Database\QueryBuilder;
use Elgg\Exceptions\Http\EntityPermissionsException;

$page_owner = elgg_get_logged_in_user_entity();

// check for a group filter
$group_guid = (int) elgg_extract('group_guid', $vars);
if (!empty($group_guid)) {
	$group = get_entity($group_guid);
	if ($group instanceof \ElggGroup) {
		$page_owner = $group;
	}
}

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE, $page_owner);

// set page owner and add breadcrumb
elgg_set_page_owner_guid($page_owner->guid);

// add title button
elgg_register_title_button('add', 'object', \ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => \ElggQuestion::SUBTYPE,
	'wheres' => [
		function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery('entities', 'a');
			$sub->joinMetadataTable('a', 'guid', 'correct_answer');
			$sub->select('a.container_guid')
				->andWhere($qb->compare('a.type', '=', 'object', ELGG_VALUE_STRING))
				->andWhere($qb->compare('a.subtype', '=', \ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
			
			return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
		},
	],
	'full_view' => false,
	'list_type_toggle' => false,
	'sort_by' => [
		'property' => 'solution_time',
		'signed' => true,
	],
	'no_results' => elgg_echo('questions:todo:none'),
];

if ($page_owner instanceof \ElggGroup) {
	$options['container_guid'] = $page_owner->guid;
} else {
	$options['wheres'][] = questions_get_expert_where_sql();
}

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
$content = elgg_list_entities($options);

// draw page
echo elgg_view_page(elgg_echo('questions:todo'), [
	'content' => $content,
	'filter_id' => 'questions',
	'filter_value' => ($page_owner instanceof \ElggGroup) ? 'todo_group' : 'todo',
]);
