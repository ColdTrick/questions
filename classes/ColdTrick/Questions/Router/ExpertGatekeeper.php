<?php

namespace ColdTrick\Questions\Router;

use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Exceptions\HttpException;
use Elgg\Router\Middleware\Gatekeeper;

/**
 * Only allow Question experts to view a page
 */
class ExpertGatekeeper extends Gatekeeper {
	
	/**
	 * Validate the page access
	 *
	 * @param \Elgg\Request $request the current request
	 *
	 * @return void
	 * @throws HttpException
	 */
	public function __invoke(\Elgg\Request $request): void {
		parent::__invoke($request);
		
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
