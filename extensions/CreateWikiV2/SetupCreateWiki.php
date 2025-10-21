<?php
/**
 * SetupCreateWiki - Initialization and configuration loader for wiki farm
 * 
 * This file should be included in LocalSettings.php to set up the wiki farm environment.
 * It handles loading wiki-specific configurations based on the current database name.
 */

// Prevent direct access
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of MediaWiki, it is not a valid entry point.' );
}

/**
 * Initialize CreateWiki and load wiki-specific configuration
 */
function setupCreateWiki() {
	global $wgDBname, $wgConf, $wgLocalDatabases;
	
	// Ensure CreateWiki database is configured
	if ( !isset( $GLOBALS['wgCreateWikiDatabase'] ) ) {
		$GLOBALS['wgCreateWikiDatabase'] = 'createwiki';
	}
	
	// Load wiki list from CreateWiki
	$wgLocalDatabases = [];
	
	try {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		$res = $dbr->select(
			'cw_wikis',
			'wiki_dbname',
			[],
			__METHOD__
		);
		
		foreach ( $res as $row ) {
			$wgLocalDatabases[] = $row->wiki_dbname;
		}
	} catch ( Exception $e ) {
		// Tables might not exist yet during installation
		wfLogWarning( 'SetupCreateWiki: Could not load wiki list: ' . $e->getMessage() );
	}
	
	// Get current wiki info
	if ( in_array( $wgDBname, $wgLocalDatabases ) ) {
		$wikiInfo = MirahezeFunctions::getWikiInfo( $wgDBname );
		
		if ( $wikiInfo ) {
			// Apply wiki-specific settings
			applyWikiSettings( $wikiInfo );
		}
	}
}

/**
 * Apply wiki-specific settings from CreateWiki database
 *
 * @param array $wikiInfo Wiki information array
 */
function applyWikiSettings( $wikiInfo ) {
	global $wgSitename, $wgLanguageCode, $wgServer;
	
	// Basic settings
	if ( isset( $wikiInfo['sitename'] ) ) {
		$wgSitename = $wikiInfo['sitename'];
	}
	
	if ( isset( $wikiInfo['language'] ) ) {
		$wgLanguageCode = $wikiInfo['language'];
	}
	
	if ( isset( $wikiInfo['url'] ) ) {
		$wgServer = $wikiInfo['url'];
	}
	
	// Apply custom settings from wiki_settings JSON
	if ( !empty( $wikiInfo['settings'] ) ) {
		foreach ( $wikiInfo['settings'] as $key => $value ) {
			// Security: Only allow specific whitelisted settings
			$allowedSettings = [
				'wgLogo',
				'wgDefaultSkin',
				'wgMetaNamespace',
				'wgEnableUploads',
				'wgFileExtensions',
				'wgUseImageMagick',
				'wgRightsText',
				'wgRightsUrl',
				'wgRightsIcon',
			];
			
			if ( in_array( $key, $allowedSettings ) ) {
				$GLOBALS[$key] = $value;
			}
		}
	}
	
	// Handle private wikis
	if ( !empty( $wikiInfo['private'] ) ) {
		setupPrivateWiki();
	}
	
	// Handle closed wikis
	if ( !empty( $wikiInfo['closed'] ) ) {
		setupClosedWiki();
	}
}

/**
 * Setup private wiki restrictions
 */
function setupPrivateWiki() {
	global $wgGroupPermissions;
	
	// Disable reading for anonymous users
	$wgGroupPermissions['*']['read'] = false;
	
	// Disable account creation
	$wgGroupPermissions['*']['createaccount'] = false;
}

/**
 * Setup closed wiki (read-only)
 */
function setupClosedWiki() {
	global $wgReadOnly;
	
	$wgReadOnly = 'This wiki has been closed.';
}

/**
 * Get wiki database name from request URL
 * This is useful for subdomain-based wiki farms
 *
 * @return string|null Database name or null if not detected
 */
function getWikiFromSubdomain() {
	global $wgCreateWikiSubdomain;
	
	if ( !isset( $wgCreateWikiSubdomain ) ) {
		return null;
	}
	
	$host = $_SERVER['HTTP_HOST'] ?? '';
	
	// Check if this is a subdomain of our wiki farm
	$pattern = '/^([a-z0-9_]+)\.' . preg_quote( $wgCreateWikiSubdomain, '/' ) . '$/';
	
	if ( preg_match( $pattern, $host, $matches ) ) {
		return $matches[1];
	}
	
	return null;
}

/**
 * Auto-detect and set database based on subdomain
 */
function autoDetectWikiDatabase() {
	global $wgDBname;
	
	$detectedDB = getWikiFromSubdomain();
	
	if ( $detectedDB ) {
		$wgDBname = $detectedDB;
	}
}

// Initialize if tables exist
if ( function_exists( 'MirahezeFunctions::getDB' ) ) {
	setupCreateWiki();
}
