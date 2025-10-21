<?php

/**
 * ManageWikiNamespaces - Manage custom namespaces per wiki
 */
class ManageWikiNamespaces {
	
	/**
	 * Get custom namespaces for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @return array Array of namespaces
	 */
	public static function getNamespaces( $dbname ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$res = $dbr->select(
			'mw_namespaces',
			'*',
			[ 'ns_dbname' => $dbname ],
			__METHOD__,
			[ 'ORDER BY' => 'ns_namespace_id ASC' ]
		);
		
		$namespaces = [];
		foreach ( $res as $row ) {
			$namespaces[$row->ns_namespace_id] = [
				'id' => $row->ns_namespace_id,
				'name' => $row->ns_namespace_name,
				'searchable' => (bool)$row->ns_searchable,
				'subpages' => (bool)$row->ns_subpages,
				'content' => (bool)$row->ns_content,
				'protection' => $row->ns_protection,
				'aliases' => json_decode( $row->ns_aliases, true ) ?: []
			];
		}
		
		return $namespaces;
	}
	
	/**
	 * Get a specific namespace
	 *
	 * @param string $dbname Wiki database name
	 * @param int $namespaceId Namespace ID
	 * @return array|false Namespace data or false
	 */
	public static function getNamespace( $dbname, $namespaceId ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'mw_namespaces',
			'*',
			[
				'ns_dbname' => $dbname,
				'ns_namespace_id' => $namespaceId
			],
			__METHOD__
		);
		
		if ( !$row ) {
			return false;
		}
		
		return [
			'id' => $row->ns_namespace_id,
			'name' => $row->ns_namespace_name,
			'searchable' => (bool)$row->ns_searchable,
			'subpages' => (bool)$row->ns_subpages,
			'content' => (bool)$row->ns_content,
			'protection' => $row->ns_protection,
			'aliases' => json_decode( $row->ns_aliases, true ) ?: []
		];
	}
	
	/**
	 * Add a custom namespace
	 *
	 * @param string $dbname Wiki database name
	 * @param int $namespaceId Namespace ID (must be >= 3000 for custom)
	 * @param array $data Namespace data
	 * @return bool Success
	 */
	public static function addNamespace( $dbname, $namespaceId, $data ) {
		// Validate namespace ID (custom namespaces should start at 3000)
		if ( $namespaceId < 3000 ) {
			return false;
		}
		
		// Namespace ID must be even (odd is reserved for talk pages)
		if ( $namespaceId % 2 !== 0 ) {
			return false;
		}
		
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		// Check if namespace already exists
		$exists = $dbw->selectRow(
			'mw_namespaces',
			'ns_namespace_id',
			[
				'ns_dbname' => $dbname,
				'ns_namespace_id' => $namespaceId
			],
			__METHOD__
		);
		
		if ( $exists ) {
			return false;
		}
		
		$dbw->insert(
			'mw_namespaces',
			[
				'ns_dbname' => $dbname,
				'ns_namespace_id' => $namespaceId,
				'ns_namespace_name' => $data['name'],
				'ns_searchable' => $data['searchable'] ?? 1,
				'ns_subpages' => $data['subpages'] ?? 1,
				'ns_content' => $data['content'] ?? 0,
				'ns_protection' => $data['protection'] ?? '',
				'ns_aliases' => json_encode( $data['aliases'] ?? [] )
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Update a namespace
	 *
	 * @param string $dbname Wiki database name
	 * @param int $namespaceId Namespace ID
	 * @param array $data Namespace data
	 * @return bool Success
	 */
	public static function updateNamespace( $dbname, $namespaceId, $data ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$updateData = [];
		
		if ( isset( $data['name'] ) ) {
			$updateData['ns_namespace_name'] = $data['name'];
		}
		if ( isset( $data['searchable'] ) ) {
			$updateData['ns_searchable'] = $data['searchable'] ? 1 : 0;
		}
		if ( isset( $data['subpages'] ) ) {
			$updateData['ns_subpages'] = $data['subpages'] ? 1 : 0;
		}
		if ( isset( $data['content'] ) ) {
			$updateData['ns_content'] = $data['content'] ? 1 : 0;
		}
		if ( isset( $data['protection'] ) ) {
			$updateData['ns_protection'] = $data['protection'];
		}
		if ( isset( $data['aliases'] ) ) {
			$updateData['ns_aliases'] = json_encode( $data['aliases'] );
		}
		
		if ( empty( $updateData ) ) {
			return false;
		}
		
		$dbw->update(
			'mw_namespaces',
			$updateData,
			[
				'ns_dbname' => $dbname,
				'ns_namespace_id' => $namespaceId
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Delete a namespace
	 *
	 * @param string $dbname Wiki database name
	 * @param int $namespaceId Namespace ID
	 * @return bool Success
	 */
	public static function deleteNamespace( $dbname, $namespaceId ) {
		// Prevent deletion of core namespaces
		if ( $namespaceId < 3000 ) {
			return false;
		}
		
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$dbw->delete(
			'mw_namespaces',
			[
				'ns_dbname' => $dbname,
				'ns_namespace_id' => $namespaceId
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Get next available namespace ID
	 *
	 * @param string $dbname Wiki database name
	 * @return int Next available namespace ID
	 */
	public static function getNextNamespaceId( $dbname ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'mw_namespaces',
			'MAX(ns_namespace_id) as max_id',
			[ 'ns_dbname' => $dbname ],
			__METHOD__
		);
		
		$maxId = $row ? intval( $row->max_id ) : 2999;
		
		// Ensure it's >= 3000 and even
		$nextId = max( 3000, $maxId + 1 );
		if ( $nextId % 2 !== 0 ) {
			$nextId++;
		}
		
		return $nextId;
	}
	
	/**
	 * Apply namespaces to wiki configuration
	 *
	 * @param string $dbname Wiki database name
	 */
	public static function applyNamespaces( $dbname ) {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages, 
		       $wgContentNamespaces, $wgNamespaceProtection, $wgNamespaceAliases;
		
		$namespaces = self::getNamespaces( $dbname );
		
		foreach ( $namespaces as $id => $ns ) {
			// Add to extra namespaces
			$wgExtraNamespaces[$id] = $ns['name'];
			$wgExtraNamespaces[$id + 1] = $ns['name'] . '_talk';
			
			// Enable subpages if needed
			if ( $ns['subpages'] ) {
				$wgNamespacesWithSubpages[$id] = true;
				$wgNamespacesWithSubpages[$id + 1] = true;
			}
			
			// Mark as content namespace if needed
			if ( $ns['content'] ) {
				$wgContentNamespaces[] = $id;
			}
			
			// Add protection if specified
			if ( !empty( $ns['protection'] ) ) {
				$wgNamespaceProtection[$id] = [ $ns['protection'] ];
			}
			
			// Add aliases
			if ( !empty( $ns['aliases'] ) ) {
				foreach ( $ns['aliases'] as $alias ) {
					$wgNamespaceAliases[$alias] = $id;
				}
			}
		}
	}
	
	/**
	 * Log namespace change
	 *
	 * @param string $dbname Wiki database name
	 * @param string $action Action (add, update, delete)
	 * @param int $namespaceId Namespace ID
	 * @param User $user User making the change
	 */
	public static function logChange( $dbname, $action, $namespaceId, $user ) {
		$logEntry = new ManualLogEntry( 'managewiki', "namespace-$action" );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:ManageWikiNamespaces/$dbname" ) );
		$logEntry->setParameters( [
			'4::wiki' => $dbname,
			'5::namespace' => $namespaceId
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}
}
