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

$subject_link = elgg_view_entity_url($subject, ['class' => 'elgg-river-subject']);
$object_link = elgg_view_entity_url($question, ['class' => 'elgg-river-object']);

echo elgg_view('river/elements/layout', [
	'item' => $item,
	'message' => elgg_get_excerpt((string) $answer->description),
	'summary' => elgg_echo('river:object:answer:create', [$subject_link, $object_link]),
]);
