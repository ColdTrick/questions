<?php
/**
 * Elgg questions plugin todo page
 *
 * @package ElggQuestions
 */

use Elgg\Database\QueryBuilder;
use Elgg\EntityPermissionsException;

if (!questions_is_expert()) {
	$e = new EntityPermissionsException();
	$e->setRedirectUrl(elgg_generate_url('collection:object:question:all'));
	throw $e;
}

// check for a group filter
$group_guid = (int) elgg_extract('group_guid', $vars);
if (!empty($group_guid)) {
	$group = get_entity($group_guid);
	if ($group instanceof ElggGroup) {
		// make sure the user is an expert of this group
		if (!questions_is_expert($group)) {
			$e = new EntityPermissionsException();
			$e->setRedirectUrl(elgg_generate_url('collection:object:question:group', [
				'guid' => $group->guid,
			]));
			throw $e;
		}
		$page_owner = $group;
	}
}

if (empty($page_owner)) {
	$page_owner = elgg_get_logged_in_user_entity();
}

elgg_push_collection_breadcrumbs('object', ElggQuestion::SUBTYPE, $page_owner);

// set page owner and add breadcrumb
elgg_set_page_owner_guid($page_owner->getGUID());

// add title button
elgg_register_title_button('questions', 'add', 'object', ElggQuestion::SUBTYPE);

// prepare options
$options = [
	'type' => 'object',
	'subtype' => ElggQuestion::SUBTYPE,
	'wheres' => [
		function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery('entities', 'a')
				->select('a.container_guid')
				->join('a', 'metadata', 'asmd', $qb->compare('a.guid', '=', 'asmd.entity_guid'))
				->where($qb->compare('asmd.name', '=', 'correct_answer', ELGG_VALUE_STRING))
				->andWhere($qb->compare('a.type', '=', 'object', ELGG_VALUE_STRING))
				->andWhere($qb->compare('a.subtype', '=', ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
			
			return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
		},
	],
	'full_view' => false,
	'list_type_toggle' => false,
	'order_by_metadata' => ['name' => 'solution_time'],
	'no_results' => elgg_echo('questions:todo:none'),
];

if ($page_owner instanceof ElggGroup) {
	$options['container_guid'] = $page_owner->guid;
} else {
	$options['wheres'][] = questions_get_expert_where_sql();
}

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
$title = elgg_echo('questions:todo');

$content = elgg_list_entities($options);

// draw page
echo elgg_view_page($title, [
	'content' => $content,
	'filter_id' => 'questions',
	'filter_value' => ($page_owner instanceof ElggGroup) ? 'todo_group' : 'todo',
]);
