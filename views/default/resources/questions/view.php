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

/* @var $question ElggQuestion */
$question = get_entity($guid);

// set breadcrumb
elgg_push_entity_breadcrumbs($question, false);

// build page elements
$title = $question->getDisplayName();

$content = elgg_view_entity($question, ['full_view' => true]);

$answers = '';

// add the answer marked as the correct answer first
$marked_answer = $question->getMarkedAnswer();
if (!empty($marked_answer)) {
	$answers .= elgg_view_entity_list([$marked_answer], [
		'limit' => false,
		'full_view' => true,
		'list_class' => ['mtm', 'questions-correct-answers'],
	]);
}

// add the rest of the answers
$options = [
	'type' => 'object',
	'subtype' => ElggAnswer::SUBTYPE,
	'container_guid' => $question->guid,
	'count' => true,
	'limit' => false,
	'full_view' => true,
	'list_class' => ['mtm'],
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
			
			return "({$sub->getSQL()}) as number_of_likes";
		},
	];
	$options['order_by'] = [
		new OrderByClause('number_of_likes', 'DESC'),
		new OrderByClause('e.time_created', 'ASC'),
	];
}

$answers .= elgg_list_entities($options);

$count = elgg_get_entities($options);
if (!empty($marked_answer)) {
	$count++;
}

$answer_menu = '';

// add answer form
if (($question->getStatus() === ElggQuestion::STATUS_OPEN) && $question->canWriteToContainer(0, 'object', ElggAnswer::SUBTYPE)) {
	
	$class = [
		'mtm',
	];
	if (!empty($count)) {
		$class[] = 'hidden';
	}
	
	$answers = elgg_view_form('object/answer/add', [
		'action' => elgg_generate_action_url('object/answer/edit', [], false),
		'class' => $class,
	], [
		'container_guid' => $question->guid,
	]) . $answers;
	
	if (!empty($count)) {
		$answer_menu .= elgg_view('output/url', [
			'icon' => 'plus',
			'text' => elgg_echo('answers:addyours'),
			'href' => false,
			'rel' => 'toggle',
			'class' => ['elgg-button', 'elgg-button-action'],
			'data-toggle-selector' => '.elgg-form-object-answer-add',
		]);
	}
}

if (!empty($answers)) {
	$content .= elgg_view_module('answers', elgg_echo('answers') . " ({$count})", $answers, [
		'id' => 'question-answers',
		'menu' => $answer_menu,
	]);
}

// draw page
echo elgg_view_page($title, [
	'entity' => $question,
	'content' => $content,
]);
