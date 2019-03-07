<?php

use Elgg\Database\Clauses\OrderByClause;

class ElggQuestion extends ElggObject {
	
	const SUBTYPE = 'question';
	const STATUS_OPEN = 'open';
	const STATUS_CLOSED = 'closed';
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::initializeAttributes()
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
		
		$this->status = self::STATUS_OPEN;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::canComment()
	 */
	public function canComment($user_guid = 0, $default = null) {
		
		if (!$this->commentsEnabled()) {
			return false;
		}
		
		return parent::canComment($user_guid, $default);
	}
	
	/**
	 * Get the answers on this question
	 *
	 * @param array $options accepts all elgg_get_entities options
	 *
	 * @return false|int|ElggAnswer[]
	 */
	public function getAnswers(array $options = []) {
		$defaults = [
			'order_by' => new OrderByClause('time_created', 'asc'),
		];
		
		$overrides = [
			'type' => 'object',
			'subtype' => ElggAnswer::SUBTYPE,
			'container_guid' => $this->guid,
		];
		
		$options = array_merge($defaults, $options, $overrides);
		
		return elgg_get_entities($options);
	}
	
	/**
	 * List the answers on this question
	 *
	 * @param array $options accepts all elgg_list_entities options
	 *
	 * @return string
	 */
	public function listAnswers(array $options = []) {
		return elgg_list_entities($options, [$this, 'getAnswers']);
	}
	
	/**
	 * Get the answer that was marked as the correct answer.
	 *
	 * @return false|ElggAnswer
	 */
	public function getMarkedAnswer() {
		$result = false;
		
		$answers = elgg_get_entities([
			'type' => 'object',
			'subtype' => ElggAnswer::SUBTYPE,
			'limit' => 1,
			'container_guid' => $this->guid,
			'metadata_name_value_pairs' => [
				'name' => 'correct_answer',
				'value' => true,
			],
		]);
		if (!empty($answers)) {
			$result = $answers[0];
		}
		
		return $result;
	}
	
	/**
	 * Helper function to close a question from further answers.
	 *
	 * @return void
	 */
	public function close() {
		$this->status = self::STATUS_CLOSED;
	}
	
	/**
	 * Reopen the question for more answers.
	 *
	 * @return void
	 */
	public function reopen() {
		$this->status = self::STATUS_OPEN;
	}
	
	/**
	 * Get the current status of the question.
	 *
	 * This can be
	 * - 'open'
	 * - 'closed'
	 *
	 * @return string the current status
	 */
	public function getStatus() {
		$result = $this->status;
		
		// should we check if the status is correct
		if (elgg_get_plugin_setting('close_on_marked_answer', 'questions') !== 'yes') {
			return $result;
		}
		
		// make sure the status is correct
		switch ($result) {
			case self::STATUS_OPEN:
				// is it still open, so no marked answer
				if ($this->getMarkedAnswer()) {
					$result = self::STATUS_CLOSED;
				}
				break;
		}
		
		return $result;
	}
	
	/**
	 * Are comments enabled
	 *
	 * @return bool
	 */
	public function commentsEnabled() {
		return $this->comments_enabled !== 'off';
	}
}
