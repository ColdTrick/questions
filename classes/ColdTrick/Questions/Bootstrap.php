<?php

namespace ColdTrick\Questions;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		
		require_once(self::plugin()->getPath() . '/lib/functions.php');
		require_once(self::plugin()->getPath() . '/lib/hooks.php');
		
		// extend CSS/JS
		elgg_extend_view('css/elgg', 'css/questions/site.css');
		elgg_extend_view('js/elgg', 'js/questions/site.js');
		
		elgg_register_menu_item('site', [
			'name' => 'questions',
			'text' => elgg_echo('questions'),
			'href' => 'questions/all',
		]);
		
		elgg_register_plugin_hook_handler('search', 'object:question', '\ColdTrick\Questions\Search::handleQuestionsSearch');
		elgg_register_plugin_hook_handler('search_params', 'search:combined', '\ColdTrick\Questions\SearchAdvanced::combinedParams');
		
		// register page handler for nice urls
		elgg_register_page_handler('questions', '\ColdTrick\Questions\PageHandler::questions');
		elgg_register_page_handler('answers', '\ColdTrick\Questions\PageHandler::answers');
		
		// register group options
		add_group_tool_option('questions', elgg_echo('questions:enable'), false);
		elgg_extend_view('groups/tool_latest', 'questions/group_module');
		
		elgg_extend_view('groups/edit', 'questions/groups_edit');
		
		// plugin hooks
		elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'questions_owner_block_menu_handler');
		elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'questions_user_hover_menu_handler');
		elgg_register_plugin_hook_handler('register', 'menu:entity', 'questions_entity_menu_handler');
		elgg_register_plugin_hook_handler('register', 'menu:filter', 'questions_filter_menu_handler');
		elgg_register_plugin_hook_handler('container_permissions_check', 'object', 'questions_container_permissions_handler');
		elgg_register_plugin_hook_handler('container_permissions_check', 'object', '\ColdTrick\Questions\Permissions::questionsContainer');
		elgg_register_plugin_hook_handler('permissions_check', 'object', 'questions_permissions_handler');
		elgg_register_plugin_hook_handler('entity:url', 'object', '\ColdTrick\Questions\WidgetManager::widgetURL');
		elgg_register_plugin_hook_handler('cron', 'daily', 'questions_daily_cron_handler');
		elgg_register_plugin_hook_handler('cron', 'daily', '\ColdTrick\Questions\Cron::autoCloseQuestions');
		
		elgg_register_plugin_hook_handler('index_entity_type_subtypes', 'elasticsearch', '\ColdTrick\Questions\Elasticsearch::indexTypes');
		
		elgg_register_plugin_hook_handler('likes:is_likable', 'object:' . \ElggQuestion::SUBTYPE, '\Elgg\Values::getTrue');
		elgg_register_plugin_hook_handler('likes:is_likable', 'object:' . \ElggAnswer::SUBTYPE, '\Elgg\Values::getTrue');
		
		// notifications
		elgg_register_notification_event('object', ElggQuestion::SUBTYPE, ['create', 'move']);
		elgg_register_notification_event('object', ElggAnswer::SUBTYPE, ['create', 'correct']);
		elgg_register_plugin_hook_handler('prepare', 'notification:create:object:' . ElggQuestion::SUBTYPE, '\ColdTrick\Questions\Notifications::createQuestion');
		elgg_register_plugin_hook_handler('prepare', 'notification:move:object:' . ElggQuestion::SUBTYPE, '\ColdTrick\Questions\Notifications::moveQuestion');
		elgg_register_plugin_hook_handler('prepare', 'notification:create:object:' . ElggAnswer::SUBTYPE, '\ColdTrick\Questions\Notifications::createAnswer');
		elgg_register_plugin_hook_handler('prepare', 'notification:correct:object:' . ElggAnswer::SUBTYPE, '\ColdTrick\Questions\Notifications::correctAnswer');
		elgg_register_plugin_hook_handler('prepare', 'notification:create:object:comment', '\ColdTrick\Questions\Notifications::createCommentOnAnswer');
		elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\Questions\Notifications::addExpertsToSubscribers');
		elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\Questions\Notifications::addQuestionOwnerToAnswerSubscribers');
		elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\Questions\Notifications::addAnswerOwnerToAnswerSubscribers');
		elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\Questions\Notifications::addQuestionSubscribersToAnswerSubscribers');
		
		elgg_register_plugin_hook_handler('entity_types', 'content_subscriptions', '\ColdTrick\Questions\ContentSubscriptions::getEntityTypes');
		elgg_register_event_handler('create', 'object', '\ColdTrick\Questions\ContentSubscriptions::createAnswer');
		elgg_register_event_handler('create', 'object', '\ColdTrick\Questions\ContentSubscriptions::createCommentOnAnswer');
		
		elgg_register_plugin_hook_handler('supported_types', 'entity_tools', '\ColdTrick\Questions\MigrateQuestions::supportedSubtypes');
		
		// events
		elgg_register_event_handler('leave', 'group', '\ColdTrick\Questions\Groups::leave');
		elgg_register_event_handler('update:after', 'object', '\ColdTrick\Questions\Access::updateQuestion');
		
		
	}
}
