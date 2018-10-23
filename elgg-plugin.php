<?php

use ColdTrick\Questions\Bootstrap;

define('QUESTIONS_EXPERT_ROLE', 'questions_expert');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
// 		'generate_username_from_email' => 'no',
		
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'question',
			'class' => 'ElggQuestion',
			'searchable' => true,
		],
		[
			'type' => 'object',
			'subtype' => 'answer',
			'class' => 'ElggAnswer',
		],
	],
	'routes' => [
// 		'action:register' => [
// 			'path' => '/action/register',
// 			'file' => Paths::elgg() . 'actions/register.php',
// 			'walled' => false,
// 			'middleware' => [
// 				'\ColdTrick\ProfileManager\Users::validateRegisterAction',
// 			]
// 		],
	],
	'actions' => [
		'questions/toggle_expert' => [],
		'questions/group_settings' => [],
		'questions/delete' => [],
		'object/question/move_to_discussions' => [],
		'object/question/save' => [],
		
		'object/answer/add' => [], // mapped to edit
		'object/answer/edit' => [],

		'answers/delete' => [],
		'answers/toggle_mark' => [],
	],
	'widgets' => [
		'questions' => [
			'context' => ['index', 'profile', 'dashboard', 'groups'],
			'multiple' => true,
		],
	],
];
		