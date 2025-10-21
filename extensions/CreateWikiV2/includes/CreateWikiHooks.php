<?php

/**
 * CreateWikiHooks - Hook handlers for CreateWiki extension
 */
class CreateWikiHooks {
	
	/**
	 * Handler for LoadExtensionSchemaUpdates hook
	 * Adds CreateWiki database tables
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function fnCreateWikiSchemaUpdates( DatabaseUpdater $updater ) {
		global $wgCreateWikiDatabase;
		
		$dir = __DIR__ . '/../sql';
		
		// Add tables for wiki registry
		$updater->addExtensionTable(
			'cw_wikis',
			"$dir/cw_wikis.sql"
		);
		
		// Add tables for wiki requests
		$updater->addExtensionTable(
			'cw_requests',
			"$dir/cw_requests.sql"
		);
		
		// Add tables for wiki comments
		$updater->addExtensionTable(
			'cw_comments',
			"$dir/cw_comments.sql"
		);
		
		return true;
	}
	
	/**
	 * Handler for wiki creation
	 *
	 * @param string $dbname Database name
	 * @param string $sitename Site name
	 * @param string $language Language code
	 * @param bool $private Private wiki
	 * @param string $category Wiki category
	 * @param User $user Creator
	 */
	public static function onCreateWikiCreation( $dbname, $sitename, $language, $private, $category, $user ) {
		// Log the creation
		$logEntry = new ManualLogEntry( 'farmer', 'createwiki' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:CreateWiki/$dbname" ) );
		$logEntry->setComment( "Created wiki: $sitename" );
		$logEntry->setParameters( [
			'4::dbname' => $dbname,
			'5::sitename' => $sitename,
			'6::language' => $language
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
		
		return true;
	}
	
	/**
	 * Handler for wiki deletion
	 *
	 * @param string $dbname Database name
	 * @param User $user Deleter
	 */
	public static function onCreateWikiDeletion( $dbname, $user ) {
		// Log the deletion
		$logEntry = new ManualLogEntry( 'farmer', 'deletewiki' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:CreateWiki/$dbname" ) );
		$logEntry->setComment( "Deleted wiki: $dbname" );
		$logEntry->setParameters( [
			'4::dbname' => $dbname
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
		
		return true;
	}
}
