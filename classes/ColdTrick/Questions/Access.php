<?php

namespace ColdTrick\Questions;

/**
 * Change access permissions
 */
class Access {
	
	/**
	 * After the question is updated in the database make sure the answers have the same access_id
	 *
	 * @param \Elgg\Event $event 'update:after', 'object'
	 *
	 * @return void
	 */
	public static function updateAnswerAccessToQuestionAccess(\Elgg\Event $event): void {
		$entity = $event->getObject();
		if (!$entity instanceof \ElggQuestion) {
			return;
		}
		
		$org_attributes = $entity->getOriginalAttributes();
		if (elgg_extract('access_id', $org_attributes) === null) {
			// access wasn't updated
			return;
		}
		
		// ignore access for this part
		elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DELETED_ENTITIES, function() use ($entity) {
			$answers = $entity->getAnswers([
				'limit' => false,
				'batch' => true,
			]);
			
			/* @var $answer \ElggAnswer */
			foreach ($answers as $answer) {
				// update the access_id with the questions access_id
				$answer->access_id = $entity->access_id;
				
				$answer->save();
			}
		});
	}
}
