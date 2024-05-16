<?php
/**
 * Show all Questions contained in the given group
 *
 * @uses $vars['entity']  the group
 * @uses $vars['options'] additional options
 */

$options = (array) elgg_extract('options', $vars);
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggGroup) {
	return;
}

$group_options = [
	'container_guid' => $entity->guid,
	'preload_containers' => false,
];

$vars['options'] = array_merge($options, $group_options);

echo elgg_view('questions/listing/all', $vars);
