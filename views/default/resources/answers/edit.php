<?php
/**
 * Edit answer page
 *
 * @package ElggQuestions
 */

use Elgg\Exceptions\Http\EntityPermissionsException;

$answer_guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($answer_guid, 'object', ElggAnswer::SUBTYPE);

/* @var $answer ElggAnswer */
$answer = get_entity($answer_guid);
if (!$answer->canEdit()) {
	throw new EntityPermissionsException();
}

$question = $answer->getContainerEntity();

elgg_set_page_owner_guid($question->container_guid);
elgg_push_entity_breadcrumbs($question);

$content = elgg_view_form('object/answer/edit', [], ['entity' => $answer]);

echo elgg_view_page(elgg_echo('questions:answer:edit'), [
	'content' => $content,
	'filter_id' => 'answer/edit',
]);
