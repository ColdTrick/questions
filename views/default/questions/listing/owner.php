<?php
/**
 * Show all Questions owned by the given user
 *
 * @uses $vars['entity']  the owning user
 * @uses $vars['options'] additional options
 */

$options = (array) elgg_extract('options', $vars);
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggUser) {
	return;
}

$owner_options = [
	'owner_guid' => $entity->guid,
	'preload_owners' => false,
];

$vars['options'] = array_merge($options, $owner_options);

echo elgg_view('questions/listing/all', $vars);
