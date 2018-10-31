<?php
/**
 *	Questions widget content
 **/

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

$route = 'questions/all';
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
				$dbprefix = elgg_get_config('dbprefix');
				
				$site = elgg_get_site_entity();
				$user = elgg_get_logged_in_user_entity();
				
				$container_where = [];
								
				$options['wheres'] = ["NOT EXISTS (
					SELECT 1
					FROM {$dbprefix}entities e2
					JOIN {$dbprefix}metadata md ON e2.guid = md.entity_guid
					WHERE e2.container_guid = e.guid
					AND md.name = 'correct_answer')"
				];
				$options['order_by_metadata'] = ['name' => 'solution_time'];
				
				if (check_entity_relationship($user->getGUID(), QUESTIONS_EXPERT_ROLE, $site->getGUID())) {
					$container_where[] = "(e.container_guid NOT IN (
						SELECT ge.guid
						FROM {$dbprefix}entities ge
						WHERE ge.type = 'group'
						AND ge.site_guid = {$site->guid}
						AND ge.enabled = 'yes'
					))";
				}
				
				$groups = elgg_get_entities([
					'type' => 'group',
					'limit' => false,
					'relationship' => QUESTIONS_EXPERT_ROLE,
					'relationship_guid' => $user->guid,
					'callback' => function ($row) {
						return (int) $row->guid;
					},
				]);
				if (!empty($groups)) {
					$container_where[] = '(e.container_guid IN (' . implode(',', $groups) . '))';
				}
				
				$container_where = '(' . implode(' OR ', $container_where) . ')';
				
				$options['wheres'][] = $container_where;
								
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
		$route = 'collection:object:question:group';
		$route_params['guid'] = $widget->owner_guid;
		
		// only in this container
		$options['container_guid'] = $widget->owner_guid;
		break;
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
