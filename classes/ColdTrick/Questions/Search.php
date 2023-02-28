<?php

namespace ColdTrick\Questions;

/**
 * Search changes
 */
class Search {
	
	/**
	 * Remove answers from the searchable types, to prevent a menu item
	 *
	 * @param \Elgg\Event $event 'search:config', 'type_subtype_pairs'
	 *
	 * @return mixed
	 */
	public static function typeSubtypePairsConfig(\Elgg\Event $event) {
		$types = $event->getValue();
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
	 * @param \Elgg\Event $event 'search:options', 'all'
	 *
	 * @return mixed
	 */
	public static function optionsAddAnswers(\Elgg\Event $event) {
		$search_params = $event->getValue();
		
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
