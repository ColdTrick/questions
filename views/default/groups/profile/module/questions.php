<?php
/**
 * List most recent questions on group profile page
 */

$params = [
	'entity_type' => 'object',
	'entity_subtype' => \ElggQuestion::SUBTYPE,
];
$params = $params + $vars;
echo elgg_view('groups/profile/module', $params);
