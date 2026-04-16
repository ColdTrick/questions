<?php

namespace ColdTrick\Questions\Controllers;

use Elgg\Controllers\GenericContentListing;
use Elgg\Database\EntityTable;
use Elgg\Database\QueryBuilder;

/**
 * List questions
 */
class QuestionsListing extends GenericContentListing {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getGroupToolOption(): ?string {
		return 'questions';
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getListingOptions(string $page, array $options): array {
		$defaults = [
			'no_results' => true,
			'wheres' => [],
		];
		
		$options = array_merge($defaults, $options);
		
		if ($page === 'todo') {
			if ($this->page_owner instanceof \ElggGroup) {
				$options['container_guid'] = $this->page_owner->guid;
			} else {
				$options['wheres'][] = questions_get_expert_where_sql();
			}
			
			$options['wheres'][] = [
				function (QueryBuilder $qb, $main_alias) {
					$sub = $qb->subquery(EntityTable::TABLE_NAME, 'a');
					$sub->joinMetadataTable($sub->getTableAlias(), 'guid', 'correct_answer');
					$sub->select("{$sub->getTableAlias()}.container_guid")
						->andWhere($qb->compare("{$sub->getTableAlias()}.type", '=', 'object', ELGG_VALUE_STRING))
						->andWhere($qb->compare("{$sub->getTableAlias()}.subtype", '=', \ElggAnswer::SUBTYPE, ELGG_VALUE_STRING));
					
					return $qb->compare("{$main_alias}.guid", 'NOT IN', $sub->getSQL());
				},
			];
			
			$options['sort_by'] = [
				'property' => 'solution_time',
				'signed' => true,
			];
			$options['no_results'] = elgg_echo('questions:todo:none');
		}
		
		// support tag filtering
		$tags = $this->request->getParam('tags');
		if (empty($tags)) {
			return $options;
		}
		
		if (is_string($tags)) {
			$tags = elgg_string_to_array($tags);
		}
		
		$options['wheres'][] = function(QueryBuilder $qb, $main_alias) use ($tags) {
			$ands = [];
			foreach ($tags as $index => $tag) {
				$md = $qb->joinMetadataTable($main_alias, 'guid', 'tags', 'inner', "md{$index}");
				
				$ands[] = $qb->compare("{$md}.value", '=', $tag, ELGG_VALUE_STRING);
			}
			
			return $qb->merge($ands);
		};
		
		return $options;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getPageOptions(string $page, array $options): array {
		$options = parent::getPageOptions($page, $options);
		
		$options['filter_id'] = ($this->page_owner instanceof \ElggGroup) ? 'questions/groups' : 'questions';
		
		return $options;
	}
	
	/**
	 * List question experts
	 *
	 * @param array $options listing options
	 *
	 * @return string
	 */
	protected function listExperts(array $options): string {
		$container = $this->page_owner;
		
		elgg_unregister_menu_item('title', 'add');
		
		if ($container instanceof \ElggGroup) {
			elgg_group_tool_gatekeeper($this->getGroupToolOption());
			
			elgg_push_collection_breadcrumbs($options['type'], $options['subtype'], $container);
		} else {
			elgg_push_collection_breadcrumbs($options['type'], $options['subtype']);
			
			$container = elgg_get_site_entity();
		}
		
		if ($container instanceof \ElggGroup) {
			$content = elgg_view('output/longtext', [
				'value' => elgg_echo('questions:experts:description:group', [$container->getDisplayName()]),
			]);
		} else {
			$content = elgg_view('output/longtext', [
				'value' => elgg_echo('questions:experts:description:site'),
			]);
		}
		
		$content .= elgg_list_entities([
			'type' => 'user',
			'relationship' => QUESTIONS_EXPERT_ROLE,
			'relationship_guid' => $container->guid,
			'inverse_relationship' => true,
			'no_results' => elgg_echo('questions:experts:none', [$container->getDisplayName()]),
		]);
		
		return elgg_view_page('', $this->getPageOptions('experts', [
			'title' => elgg_echo('questions:experts:title'),
			'content' => $content,
			'filter_value' => 'experts',
		]));
	}
	
	/**
	 * List questions that need an answer
	 *
	 * @param array $options listing options
	 *
	 * @return string
	 */
	protected function listTodo(array $options): string {
		$container = $this->page_owner;
		if ($container instanceof \ElggGroup) {
			elgg_group_tool_gatekeeper($this->getGroupToolOption());
		} else {
			$container = elgg_get_logged_in_user_entity();
		}
		
		elgg_push_collection_breadcrumbs($options['type'], $options['subtype'], $container);
		
		$this->page_owner = $container;
		elgg_set_page_owner_guid($container->guid);
		
		return elgg_view_page('', $this->getPageOptions('todo', [
			'title' => elgg_echo('questions:todo'),
			'content' => elgg_view('page/list/all', [
				'entity' => $container,
				'options' => $options,
				'page' => 'group',
			]),
			'filter_value' => 'todo',
		]));
	}
}
