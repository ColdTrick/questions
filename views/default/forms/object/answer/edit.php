<?php

$answer = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'longtext',
	'name' => 'description',
	'id' => 'answer_description',
	'value' => elgg_get_sticky_value('answer', 'description', $answer->description),
]);

$footer = elgg_view_field([
	'#type' => 'hidden',
	'name' => 'container_guid',
	'value' => $answer->getContainerGUID(),
]);

$footer .= elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $answer->guid,
]);

$footer .= elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('submit'),
]);

elgg_set_form_footer($footer);
