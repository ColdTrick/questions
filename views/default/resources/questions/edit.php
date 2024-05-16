<?php
/**
 * Edit question page
 */

$question_guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($question_guid, 'object', \ElggQuestion::SUBTYPE, true);

/* @var $question \ElggQuestion */
$question = get_entity($question_guid);

elgg_push_entity_breadcrumbs($question);

// build page elements
$form_vars = [
	'sticky_enabled' => true,
];
if (questions_limited_to_groups()) {
	$form_vars['class'] = 'questions-validate-container';
}

// draw page
echo elgg_view_page(elgg_echo('edit'), [
	'content' => elgg_view_form('object/question/save', $form_vars, ['entity' => $question]),
	'filter_id' => 'question/edit',
]);
