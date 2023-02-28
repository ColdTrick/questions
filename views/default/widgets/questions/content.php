<?php
/**
 *	Questions widget content
 **/

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
	'no_results' => elgg_echo('questions:none'),
];

$route = 'collection:object:question:all';
$route_params = [];

switch ($widget->context) {
	case 'profile':
		$route = 'collection:object:question:owner';
		$route_params['username'] = $widget->getOwnerEntity()->username;
		
		$options['owner_guid'] = $widget->owner_guid;
		break;
	case 'dashboard':
		$route = 'collection:object:question:owner';
		$route_params['username'] = $widget->getOwnerEntity()->username;
		
		$type = $widget->content_type;
		if (($type === 'todo') && !questions_is_expert()) {
			$type = 'mine';
		}
		
		// user shows owned
		switch ($type) {
			case 'todo':
				$route = 'collection:object:question:todo';
				unset($route_params['username']);
				
				// prepare options
				$options['wheres'] = [
					function (QueryBuilder $qb, $main_alias) {
						$sub = $qb->subquery('entities', 'a');
						$sub->joinMetadataTable('a', 'guid', 'correct_answer');
						$sub->select('a.container_guid')
							->andWhere($qb->compare('a.type', '=', 'object', ELGG_VALUE_STRING))
							->andWhere($qb->compare('a.subtype', '=', \ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
						
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
				$route = 'collection:object:question:all';
				unset($route_params['username']);
				
				break;
			case 'mine':
			default:
				$options['owner_guid'] = $widget->owner_guid;
				break;
		}
		break;
	case 'groups':
		$route = 'collection:object:question:group';
		$route_params['guid'] = $widget->owner_guid;
		
		// only in this container
		$options['container_guid'] = $widget->owner_guid;
		break;
}

// check if a group was selected using the grouppicker
$groups = $widget->group_guid;
if (!empty($groups)) {
	$route = 'collection:object:question:group';
	$route_params['guid'] = $groups[0];
	
	// only in this container
	$options['container_guid'] = $groups[0];
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

$url = elgg_generate_url($route, $route_params);
if (!empty($url)) {
	$options['widget_more'] = elgg_view_url($url, elgg_echo('widget:questions:more'));
}

echo elgg_list_entities($options);
