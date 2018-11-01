<?php
/**
 * View a question
 *
 * @package ElggQuestions
 */

use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\QueryBuilder;

$guid = (int) elgg_extract('guid', $vars);

elgg_entity_gatekeeper($guid, 'object', ElggQuestion::SUBTYPE);
$question = get_entity($guid);

elgg_push_breadcrumb(elgg_echo('questions'), 'questions/all');

// set page owner
$page_owner = $question->getContainerEntity();

// set breadcrumb
$crumbs_title = $page_owner->getDisplayName();

if ($page_owner instanceof ElggGroup) {
	elgg_push_breadcrumb($crumbs_title, "questions/group/{$page_owner->guid}");
} else {
	elgg_push_breadcrumb($crumbs_title, "questions/owner/{$page_owner->username}");
}

$title = $question->getDisplayName();

elgg_push_breadcrumb($title);

// build page elements
$title_icon = '';

$content = elgg_view_entity($question, ['full_view' => true]);

$answers = '';

// add the answer marked as the correct answer first
$marked_answer = $question->getMarkedAnswer();
if (!empty($marked_answer)) {
	$answers .= elgg_view_entity($marked_answer);
}

// add the rest of the answers
$options = [
	'type' => 'object',
	'subtype' => ElggAnswer::SUBTYPE,
	'container_guid' => $question->guid,
	'count' => true,
	'limit' => false,
];

if (!empty($marked_answer)) {
	// do not include the marked answer as it already  added to the output before
	$options['wheres'] = [
		function (QueryBuilder $qb, $main_alias) use ($marked_answer) {
			return $qb->compare("{$main_alias}.guid", '!=', $marked_answer->guid, ELGG_VALUE_GUID);
		},
	];
}

if (elgg_is_active_plugin('likes')) {
	// order answers based on likes
	$options['selects'] = [
		function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery('annotations')
				->select('count(name)')
				->where($qb->compare('entity_guid', '=', "{$main_alias}.guid"))
				->andWhere($qb->compare('name', '=', 'likes', ELGG_VALUE_STRING));
			
			return "({$sub->getSQL()}) as likes_count";
		},
	];
	$options['order_by'] = [
		new OrderByClause('likes_count', 'DESC'),
		new OrderByClause('e.time_created', 'ASC'),
	];
}

$answers .= elgg_list_entities($options);

$count = elgg_get_entities($options);
if (!empty($marked_answer)) {
	$count++;
}

if (!empty($answers)) {
	$content .= elgg_view_module('info', elgg_echo('answers') . " ({$count})", $answers, [
		'class' => 'mtm',
		'id' => 'question-answers',
	]);
}

// add answer form
if (($question->getStatus() === 'open') && $question->canWriteToContainer(0, 'object', 'answer')) {
	
	$add_form = elgg_view_form('object/answer/add', ['action' => elgg_generate_action_url('object/answer/edit', [], false)], ['container_guid' => $question->getGUID()]);
	
	$content .= elgg_view_module('info', elgg_echo('answers:addyours'), $add_form);
} elseif ($question->getStatus() === 'closed') {
	// add an icon to show this question is closed
	$title_icon = elgg_view_icon('lock-closed');
}

$body = elgg_view_layout('content', [
	'title' => $title_icon . $title,
	'content' => $content,
	'filter' => '',
]);

echo elgg_view_page($title, $body);
