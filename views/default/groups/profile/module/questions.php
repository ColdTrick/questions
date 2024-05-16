<?php
/**
 * List most recent questions on group profile page
 */

$params = [
	'title' => elgg_echo('questions:group'),
	'entity_type' => 'object',
	'entity_subtype' => \ElggQuestion::SUBTYPE,
	'no_results' => elgg_echo('questions:none'),
];
$params = $params + $vars;
echo elgg_view('groups/profile/module', $params);
