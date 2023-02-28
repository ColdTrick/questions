<?php
/**
 * Edit answer page
 */

$answer_guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($answer_guid, 'object', \ElggAnswer::SUBTYPE, true);

/* @var $answer ElggAnswer */
$answer = get_entity($answer_guid);

$question = $answer->getContainerEntity();

elgg_set_page_owner_guid($question->container_guid);
elgg_push_entity_breadcrumbs($question);

$content = elgg_view_form('object/answer/edit', ['sticky_enabled' => true], ['entity' => $answer]);

echo elgg_view_page(elgg_echo('questions:answer:edit'), [
	'content' => $content,
	'filter_id' => 'answer/edit',
]);
