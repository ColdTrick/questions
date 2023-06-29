<?php

use Elgg\Database\Clauses\OrderByClause;

/**
 * Question entity class
 *
 * @property string $comments_enabled are comments enabled for this question (on|off)
 * @property int    $solution_time    timestamp by which time an answer should have been provided
 * @property string $status           status of the question (open|closed)
 */
class ElggQuestion extends \ElggObject {
	
	const SUBTYPE = 'question';
	const STATUS_OPEN = 'open';
	const STATUS_CLOSED = 'closed';
	
	/**
	 * {@inheritDoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
		
		$this->status = self::STATUS_OPEN;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canComment(int $user_guid = 0): bool {
		if (!$this->commentsEnabled()) {
			return false;
		}
		
		return parent::canComment($user_guid);
	}
	
	/**
	 * Get the answers on this question
	 *
	 * @param array $options accepts all elgg_get_entities options
	 *
	 * @return false|int|ElggAnswer[]
	 * @see elgg_get_entities()
	 */
	public function getAnswers(array $options = []) {
		$defaults = [
			'order_by' => new OrderByClause('time_created', 'asc'),
		];
		
		$overrides = [
			'type' => 'object',
			'subtype' => \ElggAnswer::SUBTYPE,
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
	 * @see elgg_list_entities()
	 */
	public function listAnswers(array $options = []): string {
		return elgg_list_entities($options, [$this, 'getAnswers']);
	}
	
	/**
	 * Get the answer that was marked as the correct answer.
	 *
	 * @return null|\ElggAnswer
	 */
	public function getMarkedAnswer(): ?\ElggAnswer {
		$answers = elgg_get_entities([
			'type' => 'object',
			'subtype' => \ElggAnswer::SUBTYPE,
			'limit' => 1,
			'container_guid' => $this->guid,
			'metadata_name_value_pairs' => [
				'name' => 'correct_answer',
				'value' => true,
			],
		]);
		if (empty($answers)) {
			return null;
		}
		
		return $answers[0];
	}
	
	/**
	 * Helper function to close a question from further answers.
	 *
	 * @return void
	 */
	public function close(): void {
		$this->status = self::STATUS_CLOSED;
	}
	
	/**
	 * Reopen the question for more answers.
	 *
	 * @return void
	 */
	public function reopen(): void {
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
	public function getStatus(): string {
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
	public function commentsEnabled(): bool {
		return $this->comments_enabled !== 'off';
	}
}
