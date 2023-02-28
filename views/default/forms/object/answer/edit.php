<?php

$answer = elgg_extract('entity', $vars);
if ($answer instanceof \ElggAnswer) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $answer->guid,
	]);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'container_guid',
	'value' => elgg_extract('container_guid', $vars),
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'name' => 'description',
	'id' => 'answer_description',
	'value' => elgg_extract('description', $vars),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('submit'),
]);

elgg_set_form_footer($footer);
