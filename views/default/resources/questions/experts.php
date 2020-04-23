<?php
/**
 * List all the experts of the page_owner (or site if missing)
 *
 * @package ElggQuestions
 */

$group_guid = (int) elgg_extract('group_guid', $vars);

$container = get_entity($group_guid);
if ($container instanceof ElggGroup) {
	elgg_push_collection_breadcrumbs('object', ElggQuestion::SUBTYPE, $container);
} else {
	elgg_push_collection_breadcrumbs('object', ElggQuestion::SUBTYPE);
	
	$container = elgg_get_site_entity();
}

elgg_set_page_owner_guid($container->getGUID());

// build page elements
$title_text = elgg_echo('questions:experts:title');

// expert description
if ($container instanceof ElggGroup) {
	$desciption = elgg_view('output/longtext', [
		'value' => elgg_echo('questions:experts:description:group', [$container->getDisplayName()]),
	]);
} else {
	$desciption = elgg_view('output/longtext', [
		'value' => elgg_echo('questions:experts:description:site'),
	]);
}

// expert listing
$user_list = elgg_list_entities([
	'type' => 'user',
	'relationship' => QUESTIONS_EXPERT_ROLE,
	'relationship_guid' => $container->guid,
	'inverse_relationship' => true,
	'no_results' => elgg_echo('questions:experts:none', [$container->getDisplayName()]),
]);

// draw page
echo elgg_view_page($title_text, [
	'content' => $desciption . $user_list,
	'filter_id' => 'questions',
	'filter_value' => 'experts',
]);
