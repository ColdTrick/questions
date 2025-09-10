<?php
/**
 *	Questions widget content
 **/

use Elgg\Database\EntityTable;
use Elgg\Database\QueryBuilder;

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

$limit = (int) $widget->limit;
if ($limit < 1) {
	$limit = 5;
}

$options = [
	'type' => 'object',
	'subtype' => \ElggQuestion::SUBTYPE,
	'limit' => $limit,
	'full_view' => false,
	'pagination' => false,
	'no_results' => true,
	'widget_more' => elgg_view_url($widget->getURL(), elgg_echo('widget:questions:more'))
];

switch ($widget->context) {
	case 'profile':
		$options['owner_guid'] = $widget->owner_guid;
		break;
	case 'dashboard':
		$type = $widget->content_type;
		if (($type === 'todo') && !questions_is_expert()) {
			$type = 'mine';
		}
		
		// user shows owned
		switch ($type) {
			case 'todo':
				// prepare options
				$options['wheres'] = [
					function (QueryBuilder $qb, $main_alias) {
						$sub = $qb->subquery(EntityTable::TABLE_NAME, 'a');
						$sub->joinMetadataTable($sub->getTableAlias(), 'guid', 'correct_answer');
						$sub->select('a.container_guid')
							->andWhere($qb->compare("{$sub->getTableAlias()}.type", '=', 'object', ELGG_VALUE_STRING))
							->andWhere($qb->compare("{$sub->getTableAlias()}.subtype", '=', \ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
						
						return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
					},
					questions_get_expert_where_sql(),
				];
				$options['sort_by'] = [
					'property' => 'solution_time',
					'signed' => true,
				];
				break;
			case 'all':
				// just get all questions
				break;
			case 'mine':
			default:
				$options['owner_guid'] = $widget->owner_guid;
				break;
		}
		break;
	case 'groups':
		// only in this container
		$options['container_guid'] = $widget->owner_guid;
		break;
}

// check if a group was selected using the grouppicker
$groups = $widget->group_guid;
if (!empty($groups)) {
	// only in this container
	$options['container_guid'] = $groups;
}

// add tags filter
$filter_tags = $widget->filter_tags;
if (!empty($filter_tags)) {
	$filter_tags = is_string($filter_tags) ? elgg_string_to_array($filter_tags) : $filter_tags;
	
	$options['metadata_name_value_pairs'] = [
		'name' => 'tags',
		'value' => $filter_tags,
	];
	
	$route_params['tags'] = $filter_tags;
}

echo elgg_list_entities($options);
