<?php

class ElggAnswer extends ElggObject {
	
	const SUBTYPE = 'answer';
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::initializeAttributes()
	 */
	function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggEntity::getURL()
	 */
	public function getURL() {
		
		// make sure we can get the container
		$base_url = elgg_call(ELGG_IGNORE_ACCESS, function() {
			$container_entity = $this->getContainerEntity();
			
			return $container_entity->getURL();
		});
		
		$base_url .= "#elgg-object-{$this->guid}";
		
		return $base_url;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::canComment()
	 */
	public function canComment($user_guid = 0, $default = null) {
		
		$container = $this->getContainerEntity();
		if ($container instanceof ElggQuestion) {
			if (!$container->commentsEnabled()) {
				return false;
			}
		}
		
		return parent::canComment($user_guid, $default);
	}
	
	/**
	 * {@inheritDoc}
	 * @see ElggObject::getDisplayName()
	 */
	public function getDisplayName() {
		$question = $this->getContainerEntity();
		
		return elgg_echo('questions:object:answer:title', [$question->getDisplayName()]);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function delete($recursive = true) {
		
		// make sure the question gets reopened
		if ($this->isCorrectAnswer()) {
			// only if this is the correct answer
			elgg_call(ELGG_IGNORE_ACCESS, function() {
				$this->undoMarkAsCorrect();
			});
		}
		
		return parent::delete($recursive);
	}
	
	/**
	 * Get the metadata object for the correct answer
	 *
	 * @return false|ElggMetadata
	 */
	public function getCorrectAnswerMetadata() {
		$result = false;
		
		$metadata = elgg_get_metadata([
			'metadata_name' => 'correct_answer',
			'guid' => $this->guid,
		]);
		if ($metadata) {
			$result = $metadata[0];
		}
		
		return $result;
	}
	
	/**
	 * Is this the correct answer
	 *
	 * @return bool
	 */
	public function isCorrectAnswer() {
		return !empty($this->getCorrectAnswerMetadata());
	}
	
	/**
	 * Check if the user can mark this answer as the correct one
	 *
	 * @param \ElggUser $user user to check the ability for
	 *
	 * @return bool
	 */
	public function canMarkAnswer(\ElggUser $user = null) {
		
		// check if we have a user
		if (empty($user)) {
			$user = elgg_get_logged_in_user_entity();
		}
		
		if (empty($user)) {
			return false;
		}
		
		$question = $this->getContainerEntity();
		
		// are experts enabled
		if (!questions_experts_enabled()) {
			// no, so only question owner can mark
			return ($user->guid === $question->owner_guid);
		}
		
		// are only experts allowed to mark
		if (elgg_get_plugin_setting('experts_mark', 'questions') !== 'yes') {
			// no, so the owner of a question can also mark
			if ($user->guid == $question->owner_guid) {
				return true;
			}
		}
		
		// is the user an expert
		return questions_is_expert($question->getContainerEntity(), $user);
	}
	
	/**
	 * Mark an answer as the correct answer for this question
	 *
	 * @return void
	 */
	public function markAsCorrect() {
		// first set the mark
		$this->correct_answer = true;
		
		// trigger event for notifications
		elgg_trigger_event('correct', 'object', $this);
		
		// depending of the plugin settings, we also need to close the question
		if (elgg_get_plugin_setting('close_on_marked_answer', 'questions') === 'yes') {
			$question = $this->getContainerEntity();
			
			$question->close();
		}
	}
	
	/**
	 * This answer is no longer the correct answer for this question
	 *
	 * @return void
	 */
	public function undoMarkAsCorrect() {
		$this->correct_answer = null;
		
		// don't forget to reopen the question
		$question = $this->getContainerEntity();
			
		$question->reopen();
	}
	
	/**
	 * Check if we can auto mark this as the correct answer
	 *
	 * @param bool $creating new answer or editing (default: editing)
	 *
	 * @return void
	 */
	public function checkAutoMarkCorrect($creating = false) {
		
		$creating = (bool) $creating;
		if (empty($creating)) {
			// only on new entities
			return;
		}
		
		$question = $this->getContainerEntity();
		$container = $question->getContainerEntity();
		
		$user = $this->getOwnerEntity();
		
		if (questions_auto_mark_answer_correct($container, $user)) {
			$this->markAsCorrect();
		}
	}
}
