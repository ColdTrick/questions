<?php

/**
 * Answer entity class
 *
 * @property bool $correct_answer is this the correct answer
 */
class ElggAnswer extends \ElggObject {
	
	public const SUBTYPE = 'answer';
	
	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getURL(): string {
		// make sure we can get the container
		$base_url = elgg_call(ELGG_IGNORE_ACCESS, function() {
			$container_entity = $this->getContainerEntity();
			
			return $container_entity->getURL();
		});
		
		$base_url .= "#elgg-object-{$this->guid}";
		
		return $base_url;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canComment($user_guid = 0): bool {
		$container = $this->getContainerEntity();
		if ($container instanceof \ElggQuestion) {
			if (!$container->commentsEnabled()) {
				return false;
			}
		}
		
		return parent::canComment($user_guid);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName(): string {
		$question = $this->getContainerEntity();
		
		return elgg_echo('questions:object:answer:title', [$question->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function persistentDelete(bool $recursive = true): bool {
		// make sure the question gets reopened
		if ($this->isCorrectAnswer()) {
			// only if this is the correct answer
			elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DELETED_ENTITIES, function() {
				$this->undoMarkAsCorrect();
			});
		}
		
		return parent::persistentDelete($recursive);
	}
	
	/**
	 * Get the metadata object for the correct answer
	 *
	 * @return null|\ElggMetadata
	 */
	public function getCorrectAnswerMetadata(): ?\ElggMetadata {
		$metadata = elgg_get_metadata([
			'metadata_name' => 'correct_answer',
			'guid' => $this->guid,
		]);
		if (empty($metadata)) {
			return null;
		}
		
		return $metadata[0];
	}
	
	/**
	 * Is this the correct answer
	 *
	 * @return bool
	 */
	public function isCorrectAnswer(): bool {
		return !empty($this->getCorrectAnswerMetadata());
	}
	
	/**
	 * Check if the user can mark this answer as the correct one
	 *
	 * @param null|\ElggUser $user (optional) user to check the ability for (default: current user)
	 *
	 * @return bool
	 */
	public function canMarkAnswer(?\ElggUser $user = null): bool {
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
			if ($user->guid === $question->owner_guid) {
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
	public function markAsCorrect(): void {
		// first set the mark
		$this->correct_answer = true;
		
		// trigger event for notifications
		elgg_trigger_event('correct', 'object', $this);
		
		// depending on the plugin settings, we also need to close the question
		if (elgg_get_plugin_setting('close_on_marked_answer', 'questions') === 'yes') {
			/* @var $question \ElggQuestion */
			$question = $this->getContainerEntity();
			
			$question->close();
		}
	}
	
	/**
	 * This answer is no longer the correct answer for this question
	 *
	 * @return void
	 */
	public function undoMarkAsCorrect(): void {
		$this->correct_answer = null;
		
		// don't forget to reopen the question
		/* @var $question \ElggQuestion */
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
	public function checkAutoMarkCorrect(bool $creating = false): void {
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
