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
		$ia = elgg_set_ignore_access(true);
		
		// get the container/question
		$container_entity = $this->getContainerEntity();
		
		$url = $container_entity->getURL() . "#elgg-object-{$this->guid}";
		
		// restore access
		elgg_set_ignore_access($ia);
		
		return $url;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::canComment()
	 */
	public function canComment($user_guid = 0, $default = null) {
		
		$container = $this->getContainerEntity();
		if (!($container instanceof ElggQuestion)) {
			return false;
		}
		
		return $container->canComment($user_guid, $default);
	}
	
	/**
	 * {@inheritDoc}
	 * @see ElggObject::getDisplayName()
	 */
	public function getDisplayName() {
		$question = $this->getContainerEntity();
		
		return elgg_echo('questions:object:answer:title', [$question->title]);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function delete($recursive = true) {
		
		// make sure the question gets reopened
		if ($this->isCorrectAnswer()) {
			// only if this is the correct answer
			$ia = elgg_set_ignore_access(true);
			
			$this->undoMarkAsCorrect();
			
			elgg_set_ignore_access($ia);
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
