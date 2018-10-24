<?php
/**
 * List all the experts of the page_owner (or site if missing)
 *
 * @package ElggQuestions
 */

elgg_push_breadcrumb(elgg_echo('questions'), 'questions/all');

$group_guid = (int) elgg_extract('group_guid', $vars);

$container = get_entity($group_guid);
if ($container instanceof ElggGroup) {
	elgg_push_breadcrumb($container->getDisplayName(), "questions/group/{$container->guid}/all");
} else {
	$container = elgg_get_site_entity();
}

elgg_set_page_owner_guid($container->getGUID());

// build page elements
$title_text = elgg_echo('questions:experts:title');
elgg_push_breadcrumb($title_text);

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

// build page
$page_data = elgg_view_layout('content', [
	'title' => $title_text,
	'content' => $desciption . $user_list,
	'filter_context' => '',
]);

// draw page
echo elgg_view_page($title_text, $page_data);
