<?php
/**
 * Search result view for Questions
 * - added: #answers in imprint
 *
 * @uses $vars['entity'] the search result
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggQuestion) {
	return;
}

$imprint = (array) elgg_extract('imprint', $vars, []);

$num_answers = $entity->getAnswers(['count' => true]);
if ($num_answers > 0) {
	$imprint[] = [
		'icon_name' => 'comments',
		'content' => elgg_view('output/url', [
			'href' => "{$entity->getURL()}#question-answers",
			'text' => elgg_echo('answers'),
			'badge' => $num_answers,
		]),
	];
}

$vars['imprint'] = $imprint;

echo elgg_view('search/entity/default', $vars);
