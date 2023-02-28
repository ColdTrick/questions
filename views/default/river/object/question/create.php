<?php
/**
 * River entry for new questions
 */

$item = elgg_extract('item', $vars);
if (!$item instanceof \ElggRiverItem) {
	return;
}

$question = $item->getObjectEntity();
if (!$question instanceof \ElggQuestion) {
	return;
}

$vars['message'] = elgg_get_excerpt($question->description);

echo elgg_view('river/elements/layout', $vars);
