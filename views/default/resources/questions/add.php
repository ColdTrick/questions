<?php
/**
 * Add question page
 */

use Elgg\Exceptions\Http\EntityPermissionsException;

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner->canWriteToContainer(0, 'object', \ElggQuestion::SUBTYPE)) {
	throw new EntityPermissionsException();
}

elgg_push_collection_breadcrumbs('object', \ElggQuestion::SUBTYPE, $page_owner);

// build page elements
$form_vars = [
	'sticky_enabled' => true,
];
if (questions_limited_to_groups()) {
	$form_vars['class'] = 'questions-validate-container';
}

$content = elgg_view_form('object/question/save', $form_vars);

// draw page
echo elgg_view_page(elgg_echo('add:object:question'), [
	'content' => $content,
	'filter_id' => 'question/edit',
]);
