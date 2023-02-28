<?php

namespace ColdTrick\Questions\Router;

use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Exceptions\HttpException;

/**
 * Only allow Question experts to view a page
 */
class ExpertGatekeeper {
	
	/**
	 * Validate the page access
	 *
	 * @param \Elgg\Request $request the current request
	 *
	 * @return void
	 * @throws HttpException
	 */
	public function __invoke(\Elgg\Request $request) {
		// make sure we're logged in
		$request->elgg()->gatekeeper->assertAuthenticatedUser();
		
		// make sure the current user is an expert
		$page_owner = null;
		$group_guid = (int) $request->getParam('group_guid');
		if (!empty($group_guid)) {
			$page_owner = $request->elgg()->gatekeeper->assertExists($group_guid, 'group');
			$request->elgg()->gatekeeper->assertAccessibleEntity($page_owner);
		}
		
		if (questions_is_expert($page_owner)) {
			return;
		}
		
		// no access
		$e = new EntityPermissionsException();
		$e->setRedirectUrl(elgg_generate_url('collection:object:question:all'));
		throw $e;
	}
}
