<?php
/**
 * River entry for new answers
 */

$item = elgg_extract('item', $vars);

$answer = $item->getObjectEntity();
if (!$answer instanceof ElggAnswer) {
	return;
}

$subject = $item->getSubjectEntity();
$question = $answer->getContainerEntity();

$subject_link = elgg_view('output/url', [
	'href' => $subject->getURL(),
	'text' => $subject->getDisplayName(),
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
]);

$object_link = elgg_view('output/url', [
	'href' => $question->getURL(),
	'text' => elgg_get_excerpt($question->getDisplayName(), 100),
	'class' => 'elgg-river-object',
	'is_trusted' => true,
]);

echo elgg_view('river/elements/layout', [
	'item' => $item,
	'message' =>  elgg_get_excerpt($answer->description),
	'summary' => elgg_echo('river:object:answer:create', [$subject_link, $object_link]),
]);
