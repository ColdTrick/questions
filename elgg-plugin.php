<?php

use ColdTrick\Questions\Controllers\QuestionsListing;
use ColdTrick\Questions\Notifications\AutoCloseQuestionHandler;
use ColdTrick\Questions\Notifications\CorrectAnswerHandler;
use ColdTrick\Questions\Notifications\CreateAnswerHandler;
use ColdTrick\Questions\Notifications\CreateQuestionHandler;
use ColdTrick\Questions\Notifications\ExpertWorkloadHandler;
use ColdTrick\Questions\Notifications\MoveQuestionHandler;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerGatekeeper;

if (!defined('QUESTIONS_EXPERT_ROLE')) {
	define('QUESTIONS_EXPERT_ROLE', 'questions_expert');
}

require_once(__DIR__ . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '16.0',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'question',
			'class' => \ElggQuestion::class,
			'capabilities' => [
				'commentable' => true,
				'likable' => true,
				'restorable' => true,
				'river_emittable' => true,
				'searchable' => true,
				'subscribable' => true,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'answer',
			'class' => \ElggAnswer::class,
			'capabilities' => [
				'commentable' => true,
				'likable' => true,
				'river_emittable' => true,
				'searchable' => true,
			],
		],
	],
	'settings' => [
		'close_on_marked_answer' => 'no',
		'solution_time_group' => 'yes',
		'limit_to_groups' => 'no',
		'experts_enabled' => 'no',
		'experts_answer' => 'no',
		'experts_mark' => 'no',
	],
	'group_tools' => [
		'questions' => [
			'default_on' => false,
		],
	],
	'actions' => [
		'answers/toggle_mark' => [],
		'object/answer/edit' => [],
		'object/question/save' => [],
		'questions/toggle_expert' => [],
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
			'path' => '/questions/todo',
			'controller' => QuestionsListing::class,
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:object:question:todo:group' => [
			'path' => '/questions/todo/{guid}',
			'controller' => QuestionsListing::class,
			'middleware' => [
				Gatekeeper::class,
			],
			'options' => [
				'group_tool' => 'questions',
			],
		],
		'collection:object:question:experts' => [
			'path' => '/questions/experts',
			'controller' => QuestionsListing::class,
		],
		'collection:object:question:experts:group' => [
			'path' => '/questions/experts/{guid?}',
			'controller' => QuestionsListing::class,
			'options' => [
				'group_tool' => 'questions',
			],
		],
		'collection:object:question:group' => [
			'path' => '/questions/group/{guid}',
			'controller' => QuestionsListing::class,
			'middleware' => [
				GroupPageOwnerGatekeeper::class,
			],
			'options' => [
				'group_tool' => 'questions',
			],
			'required_plugins' => [
				'groups',
			],
		],
		'collection:object:question:owner' => [
			'path' => '/questions/owner/{username}',
			'controller' => QuestionsListing::class,
			'middleware' => [
				UserPageOwnerGatekeeper::class,
			],
		],
		'collection:object:question:all' => [
			'path' => '/questions/all',
			'controller' => QuestionsListing::class,
		],
		'default:object:question' => [
			'path' => '/questions',
			'controller' => QuestionsListing::class,
		],
	],
	'events' => [
		'container_logic_check' => [
			'object' => [
				'\ColdTrick\Questions\Permissions::preventAnswerCommentsWhenDisabled' => [],
				'\ColdTrick\Questions\Permissions::preventQuestionCommentsWhenDisabled' => [],
			],
		],
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
				'\Elgg\Menus\Filter::registerAllFilterTab' => [],
				'\Elgg\Menus\Filter::registerMineFilterTab' => [],
				'\ColdTrick\Questions\Menus\Filter::registerQuestions' => [],
			],
			'menu:filter:questions/groups' => [
				'\ColdTrick\Questions\Menus\Filter::registerQuestionsGroups' => [],
			],
			'menu:relationship' => [
				'\ColdTrick\Questions\Menus\Relationship::toggleGroupExpert' => [],
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
	'notifications' => [
		'object' => [
			'answer' => [
				'correct' => [
					CorrectAnswerHandler::class => [],
				],
				'create' => [
					CreateAnswerHandler::class => [],
				],
			],
			'question' => [
				'close' => [
					AutoCloseQuestionHandler::class => [],
				],
				'create' => [
					CreateQuestionHandler::class => [],
				],
				'move' => [
					MoveQuestionHandler::class => [],
				],
			],
		],
		'user' => [
			'user' => [
				'questions_expert_workload' => [
					ExpertWorkloadHandler::class => [],
				],
			],
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'questions/site.css' => [],
		],
		'groups/edit/settings' => [
			'questions/groups/settings' => [],
		],
		'page/list/all' => [
			'questions/filter' => ['priority' => 400],
		],
	],
	'widgets' => [
		'questions' => [
			'context' => ['index', 'profile', 'dashboard', 'groups'],
			'multiple' => true,
		],
	],
];
