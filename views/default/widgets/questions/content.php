<?php
/**
 *	Questions widget content
 **/

use Elgg\Database\QueryBuilder;

/* @var $widget ElggWidget */
$widget = elgg_extract('entity', $vars);

$limit = (int) $widget->limit;
if ($limit < 1) {
	$limit = 5;
}

$options = [
	'type' => 'object',
	'subtype' => 'question',
	'limit' => $limit,
	'full_view' => false,
	'pagination' => false,
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
						$sub = $qb->subquery('entities', 'a')
							->select('a.container_guid')
							->join('a', 'metadata', 'asmd', $qb->compare('a.guid', '=', 'asmd.entity_guid'))
							->where($qb->compare('asmd.name', '=', 'correct_answer', ELGG_VALUE_STRING))
							->andWhere($qb->compare('a.type', '=', 'object', ELGG_VALUE_STRING))
							->andWhere($qb->compare('a.subtype', '=', ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
						
						return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
					},
					questions_get_expert_where_sql(),
				];
				$options['order_by_metadata'] = ['name' => 'solution_time'];
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
	$filter_tags = string_to_tag_array($filter_tags);
	
	$options['metadata_name_value_pairs'] = [
		'name' => 'tags',
		'value' => $filter_tags,
	];
	
	$route_params['tags'] = $filter_tags;
}

$content = elgg_list_entities($options);
if (empty($content)) {
	$content = elgg_view('output/longtext', ['value' => elgg_echo('questions:none')]);
} else {
	$content .= elgg_format_element('div', ['class' => 'elgg-widget-more'], elgg_view('output/url', [
		'text' => elgg_echo('widget:questions:more'),
		'href' => elgg_generate_url($route, $route_params),
		'is_trusted' => true,
	]));
}

echo $content;
