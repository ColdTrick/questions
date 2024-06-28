<?php

use ColdTrick\Questions\Notifications\CreateQuestionNotificationEventHandler;
use ColdTrick\Questions\Notifications\MoveQuestionNotificationEventHandler;
use ColdTrick\Questions\Notifications\CreateAnswerNotificationEventHandler;
use ColdTrick\Questions\Notifications\CorrectAnswerNotificationEventHandler;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerGatekeeper;

if (!defined('QUESTIONS_EXPERT_ROLE')) {
	define('QUESTIONS_EXPERT_ROLE', 'questions_expert');
}

require_once(__DIR__ . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '13.0',
	],
	'settings' => [
		'close_on_marked_answer' => 'no',
		'solution_time_group' => 'yes',
		'limit_to_groups' => 'no',
		'experts_enabled' => 'no',
		'experts_answer' => 'no',
		'experts_mark' => 'no',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'question',
			'class' => \ElggQuestion::class,
			'capabilities' => [
				'commentable' => true,
				'searchable' => true,
				'likable' => true,
				'restorable' => true,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'answer',
			'class' => \ElggAnswer::class,
			'capabilities' => [
				'commentable' => true,
				'searchable' => true,
				'likable' => true,
			],
		],
	],
	'actions' => [
		'answers/toggle_mark' => [],
		'object/answer/edit' => [],
		'object/question/save' => [],
		'questions/toggle_expert' => [],
	],
	'events' => [
		'container_permissions_check' => [
			'object' => [
				'\ColdTrick\Questions\Permissions::answerContainer' => [],
				'\ColdTrick\Questions\Permissions::questionsContainer' => [],
			],
		],
		'create' => [
			'object' => [
				'\ColdTrick\Questions\Notifications\Subscriptions::createAnswer' => [],
				'\ColdTrick\Questions\Notifications\Subscriptions::createCommentOnAnswer' => [],
			],
		],
		'cron' => [
			'daily' => [
				'\ColdTrick\Questions\Cron::autoCloseQuestions' => [],
				'\ColdTrick\Questions\Cron::notifyQuestionExperts' => [],
			],
		],
		'entity:url' => [
			'object' => [
				'\ColdTrick\Questions\Widgets::getURL' => [],
			],
		],
		'form:prepare:fields' => [
			'object/answer/edit' => [
				\ColdTrick\Questions\Forms\PrepareAnswerFields::class => [],
			],
			'object/question/save' => [
				\ColdTrick\Questions\Forms\PrepareQuestionFields::class => [],
			],
		],
		'get' => [
			'subscriptions' => [
				'\ColdTrick\Questions\Notifications::addQuestionOwnerToCommentSubscribers' => [],
				'\ColdTrick\Questions\Notifications::addQuestionSubscribersToCommentSubscribers' => [],
			],
		],
		'leave' => [
			'group' => [
				'\ColdTrick\Questions\Plugins\Groups::removeExpertRoleOnLeave' => [],
			],
		],
		'permissions_check' => [
			'object' => [
				'\ColdTrick\Questions\Permissions::objectPermissionsCheck' => [],
			],
		],
		'prepare' => [
			'notification:create:object:comment' => [
				'\ColdTrick\Questions\Notifications::createCommentOnAnswer' => [],
			],
		],
		'register' => [
			'menu:entity' => [
				'\ColdTrick\Questions\Menus\Entity::registerAnswer' => [],
			],
			'menu:filter:questions' => [
				'\Elgg\Menus\Filter::registerFilterTabs' => [],
				'\ColdTrick\Questions\Menus\Filter::registerQuestions' => [],
			],
			'menu:filter:questions/groups' => [
				'\ColdTrick\Questions\Menus\Filter::registerQuestionsGroups' => [],
			],
			'menu:owner_block' => [
				'\ColdTrick\Questions\Menus\OwnerBlock::registerQuestions' => [],
			],
			'menu:site' => [
				'\ColdTrick\Questions\Menus\Site::registerQuestions' => [],
			],
			'menu:social' => [
				'\ColdTrick\Questions\Menus\Social::removeCommentsLinkForAnswers' => ['priority' => 600],
			],
			'menu:title:object:question' => [
				\Elgg\Notifications\RegisterSubscriptionMenuItemsHandler::class => [],
			],
			'menu:user_hover' => [
				'\ColdTrick\Questions\Menus\UserHover::registerToggleExpert' => [],
			],
		],
		'search:config' => [
			'type_subtype_pairs' => [
				'\ColdTrick\Questions\Search::typeSubtypePairsConfig' => [],
			],
		],
		'search:options' => [
			'all' => [
				'\ColdTrick\Questions\Search::optionsAddAnswers' => [],
			],
		],
		'seeds' => [
			'database' => [
				'\ColdTrick\Questions\Seeder::register' => [],
			],
		],
		'supported_types' => [
			'entity_tools' => [
				'\ColdTrick\Questions\Plugins\EntityTools::registerQuestions' => [],
			],
		],
		'update:after' => [
			'object' => [
				'\ColdTrick\Questions\Access::updateAnswerAccessToQuestionAccess' => [],
			],
		],
	],
	'group_tools' => [
		'questions' => [
			'default_on' => false,
		],
	],
	'notifications' => [
		'object' => [
			'answer' => [
				'correct' => CorrectAnswerNotificationEventHandler::class,
				'create' => CreateAnswerNotificationEventHandler::class,
			],
			'question' => [
				'create' => CreateQuestionNotificationEventHandler::class,
				'move' => MoveQuestionNotificationEventHandler::class,
			],
		],
	],
	'routes' => [
		'add:object:question' => [
			'path' => '/questions/add/{guid}',
			'resource' => 'questions/add',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'edit:object:question' => [
			'path' => '/questions/edit/{guid}',
			'resource' => 'questions/edit',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'view:object:question' => [
			'path' => '/questions/view/{guid}/{title?}',
			'resource' => 'questions/view',
		],
		'edit:object:answer' => [
			'path' => '/answers/edit/{guid}',
			'resource' => 'answers/edit',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:object:question:todo' => [
			'path' => '/questions/todo/{group_guid?}',
			'resource' => 'questions/todo',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:object:question:experts' => [
			'path' => '/questions/experts/{group_guid?}',
			'resource' => 'questions/experts',
		],
		'collection:object:question:group' => [
			'path' => '/questions/group/{guid}/{subpage?}',
			'resource' => 'questions/group',
			'defaults' => [
				'subpage' => 'all',
			],
			'middleware' => [
				GroupPageOwnerGatekeeper::class,
			],
		],
		'collection:object:question:owner' => [
			'path' => '/questions/owner/{username}',
			'resource' => 'questions/owner',
			'middleware' => [
				UserPageOwnerGatekeeper::class,
			],
		],
		'collection:object:question:all' => [
			'path' => '/questions/all',
			'resource' => 'questions/all',
		],
		'default:object:question' => [
			'path' => '/questions',
			'resource' => 'questions/all',
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'questions/site.css' => [],
		],
		'groups/edit/settings' => [
			'questions/groups/settings' => [],
		],
	],
	'widgets' => [
		'questions' => [
			'context' => ['index', 'profile', 'dashboard', 'groups'],
			'multiple' => true,
		],
	],
];
