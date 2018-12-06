<?php
/**
 * Question entity full view
 *
 * @package Questions
*/

$question = elgg_extract('entity', $vars);
if (!$question instanceof ElggQuestion) {
	return;
}

$params = [
	'icon_entity' => $question->getOwnerEntity(),
	'title' => false,
	'show_summary' => true,
	'body' => elgg_view('output/longtext', ['value' => $question->description]),
	'responses' => elgg_view_comments($question, true, [
		'inline' => true,
	]),
];

$params = $params + $vars;

echo elgg_view('object/elements/full', $params);
