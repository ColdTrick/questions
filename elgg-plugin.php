<?php

use ColdTrick\Questions\Bootstrap;
use Elgg\Router\Middleware\Gatekeeper;

define('QUESTIONS_EXPERT_ROLE', 'questions_expert');

require_once(__DIR__ . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'close_on_marked_answer' => 'no',
		'solution_time_group' => 'yes',
		'limit_to_groups' => 'no',
		'experts_enabled' => 'no',
		'experts_answer' => 'no',
		'experts_mark' => 'no',
		'move_to_discussion_allowed' => true,
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'question',
			'class' => ElggQuestion::class,
			'searchable' => true,
		],
		[
			'type' => 'object',
			'subtype' => 'answer',
			'class' => ElggAnswer::class,
			'searchable' => true,
		],
	],
	'routes' => [
		'add:object:question' => [
			'path' => '/questions/add/{guid?}',
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
		'add:object:answer' => [
			'path' => '/answers/add/{guid?}',
			'resource' => 'answers/add',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'edit:object:answer' => [
			'path' => '/answers/edit/{guid}',
			'resource' => 'answers/edit',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'view:object:question' => [
			'path' => '/questions/view/{guid}/{title?}',
			'resource' => 'questions/view',
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
		],
		'collection:object:question:owner' => [
			'path' => '/questions/owner/{username?}',
			'resource' => 'questions/owner',
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
	'actions' => [
		'questions/toggle_expert' => [],
		'questions/group_settings' => [],
		'object/question/move_to_discussions' => [],
		'object/question/save' => [],
		
		'object/answer/edit' => [],

		'answers/toggle_mark' => [],
	],
	'widgets' => [
		'questions' => [
			'context' => ['index', 'profile', 'dashboard', 'groups'],
			'multiple' => true,
		],
	],
];
		