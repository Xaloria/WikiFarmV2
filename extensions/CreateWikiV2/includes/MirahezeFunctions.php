<?php

/**
 * MirahezeFunctions - Shared utility functions for CreateWiki and ManageWiki
 * Compatible with shared hosting (no root required) and VPS environments
 */
class MirahezeFunctions {
	
	/**
	 * Get database connection
	 *
	 * @param string $dbname Database name
	 * @param int $type DB_PRIMARY or DB_REPLICA
	 * @return IDatabase
	 */
	public static function getDB( $dbname = null, $type = DB_PRIMARY ) {
		global $wgCreateWikiDatabase, $wgCreateWikiDatabaseClusters;
		
		$dbname = $dbname ?? $wgCreateWikiDatabase;
		
		if ( $wgCreateWikiDatabaseClusters ) {
			$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory()
				->getMainLB( $dbname );
			return $lb->getConnection( $type, [], $dbname );
		}
		
		return MediaWikiServices::getInstance()->getDBLoadBalancerFactory()
			->getMainLB()->getConnection( $type );
	}
	
	/**
	 * Get list of all wikis
	 *
	 * @return array Array of wiki database names
	 */
	public static function getWikiList() {
		$dbr = self::getDB( null, DB_REPLICA );
		
		$res = $dbr->select(
			'cw_wikis',
			'wiki_dbname',
			[],
			__METHOD__
		);
		
		$wikis = [];
		foreach ( $res as $row ) {
			$wikis[] = $row->wiki_dbname;
		}
		
		return $wikis;
	}
	
	/**
	 * Check if a wiki exists
	 *
	 * @param string $dbname Wiki database name
	 * @return bool
	 */
	public static function wikiExists( $dbname ) {
		$dbr = self::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'cw_wikis',
			'wiki_dbname',
			[ 'wiki_dbname' => $dbname ],
			__METHOD__
		);
		
		return (bool)$row;
	}
	
	/**
	 * Get wiki information
	 *
	 * @param string $dbname Wiki database name
	 * @return array|false Wiki data or false
	 */
	public static function getWikiInfo( $dbname ) {
		$dbr = self::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'cw_wikis',
			'*',
			[ 'wiki_dbname' => $dbname ],
			__METHOD__
		);
		
		if ( !$row ) {
			return false;
		}
		
		return [
			'dbname' => $row->wiki_dbname,
			'sitename' => $row->wiki_sitename,
			'language' => $row->wiki_language,
			'private' => (bool)$row->wiki_private,
			'closed' => (bool)$row->wiki_closed,
			'inactive' => (bool)$row->wiki_inactive,
			'category' => $row->wiki_category,
			'url' => $row->wiki_url,
			'creation' => $row->wiki_creation,
			'settings' => $row->wiki_settings ? json_decode( $row->wiki_settings, true ) : []
		];
	}
	
	/**
	 * Create wiki database
	 *
	 * @param string $dbname Database name
	 * @return bool Success
	 */
	public static function createWikiDB( $dbname ) {
		global $wgDBname, $wgDBuser, $wgDBpassword, $wgDBserver, $wgDBtype;
		
		try {
			// Connect to MySQL server
			$conn = Database::factory( $wgDBtype, [
				'host' => $wgDBserver,
				'user' => $wgDBuser,
				'password' => $wgDBpassword,
				'dbname' => $wgDBname,
				'flags' => 0,
				'variables' => []
			] );
			
			// Check if database already exists
			$dbs = $conn->query( 'SHOW DATABASES LIKE ' . $conn->addQuotes( $dbname ) );
			if ( $dbs->numRows() > 0 ) {
				return false;
			}
			
			// Create database (compatible with shared hosting - no root needed)
			$conn->query( 'CREATE DATABASE ' . $conn->addIdentifier( $dbname ) . 
				' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci' );
			
			return true;
		} catch ( Exception $e ) {
			wfLogWarning( "Failed to create database $dbname: " . $e->getMessage() );
			return false;
		}
	}
	
	/**
	 * Populate wiki with MediaWiki tables
	 *
	 * @param string $dbname Database name
	 * @return bool Success
	 */
	public static function populateWikiDB( $dbname ) {
		global $IP, $wgCreateWikiSQLfiles;
		
		// Run MediaWiki's tables.sql
		$tablesFile = "$IP/maintenance/tables.sql";
		if ( !file_exists( $tablesFile ) ) {
			// Fallback for older MediaWiki versions
			$tablesFile = "$IP/maintenance/postgres/tables.sql";
			if ( !file_exists( $tablesFile ) ) {
				return false;
			}
		}
		
		try {
			$dbw = Database::factory( 'mysql', [
				'host' => $GLOBALS['wgDBserver'],
				'user' => $GLOBALS['wgDBuser'],
				'password' => $GLOBALS['wgDBpassword'],
				'dbname' => $dbname,
				'flags' => 0,
				'variables' => []
			] );
			
			$sql = file_get_contents( $tablesFile );
			$dbw->sourceFile( $tablesFile );
			
			return true;
		} catch ( Exception $e ) {
			wfLogWarning( "Failed to populate database $dbname: " . $e->getMessage() );
			return false;
		}
	}
	
	/**
	 * Add wiki to CreateWiki registry
	 *
	 * @param array $data Wiki data
	 * @return bool Success
	 */
	public static function addWiki( $data ) {
		$dbw = self::getDB( null, DB_PRIMARY );
		
		$dbw->insert(
			'cw_wikis',
			[
				'wiki_dbname' => $data['dbname'],
				'wiki_sitename' => $data['sitename'],
				'wiki_language' => $data['language'],
				'wiki_private' => $data['private'] ?? 0,
				'wiki_closed' => 0,
				'wiki_inactive' => 0,
				'wiki_category' => $data['category'] ?? 'uncategorised',
				'wiki_url' => $data['url'],
				'wiki_creation' => wfTimestampNow(),
				'wiki_settings' => json_encode( $data['settings'] ?? [] )
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Delete a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @param bool $deleteTables Whether to drop the database
	 * @return bool Success
	 */
	public static function deleteWiki( $dbname, $deleteTables = false ) {
		// Remove from registry
		$dbw = self::getDB( null, DB_PRIMARY );
		$dbw->delete(
			'cw_wikis',
			[ 'wiki_dbname' => $dbname ],
			__METHOD__
		);
		
		// Optionally drop database
		if ( $deleteTables ) {
			try {
				$conn = Database::factory( 'mysql', [
					'host' => $GLOBALS['wgDBserver'],
					'user' => $GLOBALS['wgDBuser'],
					'password' => $GLOBALS['wgDBpassword'],
					'dbname' => $GLOBALS['wgDBname'],
					'flags' => 0,
					'variables' => []
				] );
				
				$conn->query( 'DROP DATABASE IF EXISTS ' . $conn->addIdentifier( $dbname ) );
			} catch ( Exception $e ) {
				wfLogWarning( "Failed to drop database $dbname: " . $e->getMessage() );
			}
		}
		
		return true;
	}
	
	/**
	 * Update wiki settings
	 *
	 * @param string $dbname Wiki database name
	 * @param array $settings Settings to update
	 * @return bool Success
	 */
	public static function updateWikiSettings( $dbname, $settings ) {
		$dbw = self::getDB( null, DB_PRIMARY );
		
		$currentSettings = self::getWikiInfo( $dbname );
		if ( !$currentSettings ) {
			return false;
		}
		
		$newSettings = array_merge( $currentSettings['settings'], $settings );
		
		$dbw->update(
			'cw_wikis',
			[ 'wiki_settings' => json_encode( $newSettings ) ],
			[ 'wiki_dbname' => $dbname ],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Generate wiki URL
	 *
	 * @param string $subdomain Wiki subdomain
	 * @return string Full URL
	 */
	public static function generateWikiURL( $subdomain ) {
		global $wgCreateWikiSubdomain;
		
		if ( isset( $wgCreateWikiSubdomain ) ) {
			return "https://{$subdomain}.{$wgCreateWikiSubdomain}";
		}
		
		return "https://{$subdomain}.wiki.local";
	}
	
	/**
	 * Sanitize database name
	 *
	 * @param string $name Database name to sanitize
	 * @return string Sanitized name
	 */
	public static function sanitizeDBname( $name ) {
		// Remove special characters, keep only alphanumeric and underscore
		$name = preg_replace( '/[^a-z0-9_]/', '', strtolower( $name ) );
		
		// Ensure it doesn't start with a number
		if ( is_numeric( substr( $name, 0, 1 ) ) ) {
			$name = 'wiki_' . $name;
		}
		
		// Limit length to 64 characters (MySQL limit)
		$name = substr( $name, 0, 64 );
		
		return $name;
	}
	
	/**
	 * Check if user can manage wiki
	 *
	 * @param User $user User object
	 * @param string $dbname Wiki database name
	 * @return bool
	 */
	public static function userCanManageWiki( $user, $dbname ) {
		// Check if user has global rights
		if ( $user->isAllowed( 'createwiki' ) ) {
			return true;
		}
		
		// Check if user is wiki bureaucrat (would need to check per-wiki permissions)
		// This is simplified - actual implementation would need cross-wiki checking
		return false;
	}
	
	/**
	 * Get wiki creation requests
	 *
	 * @param string $status Status filter (pending, approved, declined)
	 * @return array Array of requests
	 */
	public static function getWikiRequests( $status = 'pending' ) {
		$dbr = self::getDB( null, DB_REPLICA );
		
		$conds = [];
		if ( $status ) {
			$conds['cw_status'] = $status;
		}
		
		$res = $dbr->select(
			'cw_requests',
			'*',
			$conds,
			__METHOD__,
			[ 'ORDER BY' => 'cw_timestamp DESC' ]
		);
		
		$requests = [];
		foreach ( $res as $row ) {
			$requests[] = [
				'id' => $row->cw_id,
				'dbname' => $row->cw_dbname,
				'sitename' => $row->cw_sitename,
				'language' => $row->cw_language,
				'category' => $row->cw_category,
				'requester' => $row->cw_user,
				'reason' => $row->cw_reason,
				'status' => $row->cw_status,
				'timestamp' => $row->cw_timestamp,
				'comment' => $row->cw_comment
			];
		}
		
		return $requests;
	}
	
	/**
	 * Check if running on shared hosting
	 *
	 * @return bool True if shared hosting detected
	 */
	public static function isSharedHosting() {
		// Check if we have root/sudo access
		$hasRoot = ( posix_getuid() === 0 );
		
		// Check if common VPS/dedicated server indicators exist
		$hasSystemd = file_exists( '/run/systemd/system' );
		
		// Shared hosting typically doesn't have root and no systemd
		return !$hasRoot && !$hasSystemd;
	}
}
