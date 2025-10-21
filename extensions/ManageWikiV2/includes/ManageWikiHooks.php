<?php

/**
 * ManageWikiHooks - Hook handlers for ManageWiki extension
 */
class ManageWikiHooks {
	
	/**
	 * Handler for LoadExtensionSchemaUpdates hook
	 * Adds ManageWiki database tables
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function fnManageWikiSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__ . '/../sql';
		
		// Add tables for namespace management
		$updater->addExtensionTable(
			'mw_namespaces',
			"$dir/mw_namespaces.sql"
		);
		
		// Add tables for permission management
		$updater->addExtensionTable(
			'mw_permissions',
			"$dir/mw_permissions.sql"
		);
		
		return true;
	}
}
