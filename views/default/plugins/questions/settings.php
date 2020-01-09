<?php
/**
 * All plugin settings can be configured by this view
 *
 */

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

// general settings
$general_settings = elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:general:close'),
	'#help' => elgg_echo('questions:settings:general:close:description'),
	'name' => 'params[close_on_marked_answer]',
	'checked' => $plugin->close_on_marked_answer === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$general_settings .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('questions:settings:general:solution_time'),
	'#help' => elgg_echo('questions:settings:general:solution_time:description'),
	'name' => 'params[site_solution_time]',
	'value' => $plugin->site_solution_time,
	'options' => range(0, 30),
]);

$general_settings .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('questions:settings:general:auto_close_time'),
	'#help' => elgg_echo('questions:settings:general:auto_close_time:description'),
	'name' => 'params[auto_close_time]',
	'value' => $plugin->auto_close_time,
]);

$general_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:general:solution_time_group'),
	'#help' => elgg_echo('questions:settings:general:solution_time_group:description'),
	'name' => 'params[solution_time_group]',
	'checked' => $plugin->solution_time_group === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$general_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:general:limit_to_groups'),
	'#help' => elgg_echo('questions:settings:general:limit_to_groups:description'),
	'name' => 'params[limit_to_groups]',
	'checked' => $plugin->limit_to_groups === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

echo elgg_view_module('info', elgg_echo('questions:settings:general:title'), $general_settings);

// adding expert roles
$expert_settings = elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:experts:enable'),
	'#help' => elgg_echo('questions:settings:experts:enable:description'),
	'name' => 'params[experts_enabled]',
	'checked' => $plugin->experts_enabled === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$expert_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:experts:answer'),
	'name' => 'params[experts_answer]',
	'checked' => $plugin->experts_answer === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$expert_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:experts:mark'),
	'name' => 'params[experts_mark]',
	'checked' => $plugin->experts_mark === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$expert_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('questions:settings:experts:move_to_discussion_allowed'),
	'name' => 'params[move_to_discussion_allowed]',
	'checked' => (bool) $plugin->move_to_discussion_allowed,
	'switch' => true,
	'value' => 1,
]);

echo elgg_view_module('info', elgg_echo('questions:settings:experts:title'), $expert_settings);

// access options
$access_settings = elgg_view_field([
	'#type' => 'access',
	'#label' => elgg_echo('questions:settings:access:personal'),
	'name' => 'params[access_personal]',
	'value' => $plugin->access_personal,
	'options_values' => [
		'user_defined' => elgg_echo('questions:settings:access:options:user'),
		ACCESS_LOGGED_IN => elgg_echo('access:label:logged_in'),
		ACCESS_PUBLIC => elgg_echo('access:label:public'),
	],
]);

$access_settings .= elgg_view_field([
	'#type' => 'access',
	'#label' => elgg_echo('questions:settings:access:group'),
	'name' => 'params[access_group]',
	'value' => $plugin->access_group,
	'options_values' => [
		'user_defined' => elgg_echo('questions:settings:access:options:user'),
		'group_acl' => elgg_echo('questions:settings:access:options:group'),
		ACCESS_LOGGED_IN => elgg_echo('access:label:logged_in'),
		ACCESS_PUBLIC => elgg_echo('access:label:public'),
	],
]);

echo elgg_view_module('info', elgg_echo('questions:settings:access:title'), $access_settings);
