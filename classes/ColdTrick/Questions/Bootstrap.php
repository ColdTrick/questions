<?php

namespace ColdTrick\Questions;

use Elgg\DefaultPluginBootstrap;
use ElggQuestion;
use ElggAnswer;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		
		$this->extendViews();
		$this->registerEvents();
		$this->registerHooks();
		
		elgg_register_menu_item('site', [
			'name' => 'questions',
			'icon' => 'question',
			'text' => elgg_echo('questions'),
			'href' => elgg_generate_url('collection:object:question:all'),
		]);
		
		// register group options
		$this->elgg()->group_tools->register('questions', [
			'label' => elgg_echo('questions:enable'),
			'default_on' => false,
		]);
		
		// notifications
		elgg_register_notification_event('object', ElggQuestion::SUBTYPE, ['create', 'move']);
		elgg_register_notification_event('object', ElggAnswer::SUBTYPE, ['create', 'correct']);
	}
	
	/**
	 * register extend views
	 *
	 * @return void
	 */
	protected function extendViews() {
		elgg_extend_view('elgg.css', 'css/questions/site.css');
		elgg_extend_view('groups/edit', 'questions/groups_edit');
	}
	
	/**
	 * Register plugin hooks
	 *
	 * @return void
	 */
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('container_permissions_check', 'object', __NAMESPACE__ . '\Permissions::answerContainer');
		$hooks->registerHandler('container_permissions_check', 'object', __NAMESPACE__ . '\Permissions::questionsContainer');
		$hooks->registerHandler('cron', 'daily', __NAMESPACE__ . '\Cron::notifyQuestionExperts');
		$hooks->registerHandler('cron', 'daily', __NAMESPACE__ . '\Cron::autoCloseQuestions');
		$hooks->registerHandler('entity:url', 'object', __NAMESPACE__ . '\Widgets::getURL');
		$hooks->registerHandler('entity_types', 'content_subscriptions', __NAMESPACE__ . '\ContentSubscriptions::getEntityTypes');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::addExpertsToSubscribers');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::addQuestionOwnerToAnswerSubscribers');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::addAnswerOwnerToAnswerSubscribers');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::addQuestionSubscribersToAnswerSubscribers');
		$hooks->registerHandler('likes:is_likable', 'object:' . \ElggQuestion::SUBTYPE, '\Elgg\Values::getTrue');
		$hooks->registerHandler('likes:is_likable', 'object:' . \ElggAnswer::SUBTYPE, '\Elgg\Values::getTrue');
		$hooks->registerHandler('permissions_check', 'object', __NAMESPACE__ . '\Permissions::objectPermissionsCheck');
		$hooks->registerHandler('prepare', 'notification:create:object:' . ElggQuestion::SUBTYPE, __NAMESPACE__ . '\Notifications::createQuestion');
		$hooks->registerHandler('prepare', 'notification:move:object:' . ElggQuestion::SUBTYPE, __NAMESPACE__ . '\Notifications::moveQuestion');
		$hooks->registerHandler('prepare', 'notification:create:object:' . ElggAnswer::SUBTYPE, __NAMESPACE__ . '\Notifications::createAnswer');
		$hooks->registerHandler('prepare', 'notification:correct:object:' . ElggAnswer::SUBTYPE, __NAMESPACE__ . '\Notifications::correctAnswer');
		$hooks->registerHandler('prepare', 'notification:create:object:comment', __NAMESPACE__ . '\Notifications::createCommentOnAnswer');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\Menus::registerEntity');
		$hooks->registerHandler('register', 'menu:filter:questions', __NAMESPACE__ . '\Menus::registerFilter');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\Menus::registerOwnerBlock');
		$hooks->registerHandler('register', 'menu:social', __NAMESPACE__ . '\Menus::removeCommentsLinkForAnswers', 999);
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\Menus::registerUserHover');
		$hooks->registerHandler('search:config', 'type_subtype_pairs', __NAMESPACE__ . '\Search::typeSubtypePairsConfig');
		$hooks->registerHandler('search:options', 'all', __NAMESPACE__ . '\Search::optionsAddAnswers');
		$hooks->registerHandler('supported_types', 'entity_tools', __NAMESPACE__ . '\MigrateQuestions::supportedSubtypes');
	}
	
	/**
	 * Register event handlers
	 *
	 * @return void
	 */
	protected function registerEvents() {
		$events = $this->elgg()->events;
		
		$events->registerHandler('create', 'object', __NAMESPACE__ . '\ContentSubscriptions::createAnswer');
		$events->registerHandler('create', 'object', __NAMESPACE__ . '\ContentSubscriptions::createCommentOnAnswer');
		$events->registerHandler('leave', 'group', __NAMESPACE__ . '\Groups::leave');
		$events->registerHandler('update:after', 'object', __NAMESPACE__ . '\Access::updateQuestion');
	}
}
