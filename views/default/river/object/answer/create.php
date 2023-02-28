<?php
/**
 * River entry for new answers
 */

$item = elgg_extract('item', $vars);
if (!$item instanceof \ElggRiverItem) {
	return;
}

$answer = $item->getObjectEntity();
if (!$answer instanceof \ElggAnswer) {
	return;
}

$subject = $item->getSubjectEntity();
$question = $answer->getContainerEntity();

$subject_link = elgg_view('output/url', [
	'text' => $subject->getDisplayName(),
	'href' => $subject->getURL(),
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
]);

$object_link = elgg_view('output/url', [
	'text' => elgg_get_excerpt($question->getDisplayName(), 100),
	'href' => $question->getURL(),
	'class' => 'elgg-river-object',
	'is_trusted' => true,
]);

echo elgg_view('river/elements/layout', [
	'item' => $item,
	'message' => elgg_get_excerpt($answer->description),
	'summary' => elgg_echo('river:object:answer:create', [$subject_link, $object_link]),
]);
