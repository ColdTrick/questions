<?php

namespace ColdTrick\Questions;

class Search {
	
	/**
	 * Remove answers from the searchable types, to prevent an menu item
	 *
	 * @param \Elgg\Hook $hook 'search:config', 'type_subtype_pairs'
	 *
	 * @return void|mixed
	 */
	public static function typeSubtypePairsConfig(\Elgg\Hook $hook) {
		
		$types = $hook->getValue();
		$objects = elgg_extract('object', $types);
		if (empty($objects)) {
			return;
		}
		
		$key = array_search(\ElggAnswer::SUBTYPE, $objects);
		if ($key === false) {
			return;
		}
		
		unset($objects[$key]);
		$types['object'] = $objects;
		
		return $types;
	}
	
	/**
	 * Add answers to the question searches
	 *
	 * @param \Elgg\Hook $hook 'search:options', 'all'
	 *
	 * @return void|mixed
	 */
	public static function optionsAddAnswers(\Elgg\Hook $hook) {
		
		$search_params = $hook->getValue();
		
		$type_subtype_pairs = false;
		$subtypes = (array) elgg_extract('subtypes', $search_params, elgg_extract('subtype', $search_params));
		if (empty($subtypes)) {
			$type_subtype_pairs = (array) elgg_extract('type_subtype_pairs', $search_params);
			$subtypes = (array) elgg_extract('object', $type_subtype_pairs);
		}
		
		if (empty($subtypes) || !in_array(\ElggQuestion::SUBTYPE, $subtypes) || in_array(\ElggAnswer::SUBTYPE, $subtypes)) {
			return;
		}
		
		$subtypes[] = \ElggAnswer::SUBTYPE;
		
		if ($type_subtype_pairs !== false) {
			$type_subtype_pairs['object'] = $subtypes;
			
			$search_params['type_subtype_pairs'] = $type_subtype_pairs;
		} else {
			$search_params['subtypes'] = $subtypes;
			unset($search_params['subtype']);
		}
		
		return $search_params;
	}
}
