<?php
/**
 * Elgg questions plugin everyone page
 *
 * @package ElggQuestions
 */

use Elgg\Database\QueryBuilder;

elgg_gatekeeper();
if (!questions_is_expert()) {
	forward('questions/all');
}

elgg_push_breadcrumb(elgg_echo('questions'), 'questions/all');

// check for a group filter
$group_guid = (int) elgg_extract('group_guid', $vars);
if (!empty($group_guid)) {
	$group = get_entity($group_guid);
	if ($group instanceof ElggGroup) {
		// make sure the user is an expert of this group
		if (!questions_is_expert($group)) {
			forward("questions/group/{$group->guid}/all");
		}
		$page_owner = $group;
		elgg_push_breadcrumb($group->getDisplayName(), "questions/group/{$group->guid}/all");
	}
}

if (empty($page_owner)) {
	$page_owner = elgg_get_logged_in_user_entity();
}

// set page owner and add breadcrumb
elgg_set_page_owner_guid($page_owner->getGUID());
elgg_push_breadcrumb(elgg_echo('questions:todo'));

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
	$options['container_guid'] = $page_owner->getGUID();
} else {
	$options['wheres'][] = questions_get_expert_where_sql();
}

$tags = get_input('tags');
if (!empty($tags)) {
	if (is_string($tags)) {
		$tags = string_to_tag_array($tags);

	}
	$options['metadata_name_value_pairs'] = [
		'name' => 'tags',
		'value' => $tags,
	];
}

// build page elements
$title = elgg_echo('questions:todo');

$content = elgg_list_entities($options);

// build page
$body = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter_context' => '',
]);

// draw page
echo elgg_view_page($title, $body);
