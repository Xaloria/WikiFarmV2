<?php

/**
 * ManageWikiPermissions - Manage user groups and permissions per wiki
 */
class ManageWikiPermissions {
	
	/**
	 * Get user groups for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @return array Array of groups and their permissions
	 */
	public static function getGroups( $dbname ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$res = $dbr->select(
			'mw_permissions',
			'*',
			[ 'perm_dbname' => $dbname ],
			__METHOD__
		);
		
		$groups = [];
		foreach ( $res as $row ) {
			$groups[$row->perm_group] = [
				'group' => $row->perm_group,
				'permissions' => json_decode( $row->perm_permissions, true ) ?: [],
				'addgroups' => json_decode( $row->perm_addgroups, true ) ?: [],
				'removegroups' => json_decode( $row->perm_removegroups, true ) ?: []
			];
		}
		
		return $groups;
	}
	
	/**
	 * Get permissions for a specific group
	 *
	 * @param string $dbname Wiki database name
	 * @param string $group Group name
	 * @return array|false Group data or false
	 */
	public static function getGroup( $dbname, $group ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'mw_permissions',
			'*',
			[
				'perm_dbname' => $dbname,
				'perm_group' => $group
			],
			__METHOD__
		);
		
		if ( !$row ) {
			return false;
		}
		
		return [
			'group' => $row->perm_group,
			'permissions' => json_decode( $row->perm_permissions, true ) ?: [],
			'addgroups' => json_decode( $row->perm_addgroups, true ) ?: [],
			'removegroups' => json_decode( $row->perm_removegroups, true ) ?: []
		];
	}
	
	/**
	 * Update group permissions
	 *
	 * @param string $dbname Wiki database name
	 * @param string $group Group name
	 * @param array $data Group data
	 * @return bool Success
	 */
	public static function updateGroup( $dbname, $group, $data ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$dbw->upsert(
			'mw_permissions',
			[
				'perm_dbname' => $dbname,
				'perm_group' => $group,
				'perm_permissions' => json_encode( $data['permissions'] ?? [] ),
				'perm_addgroups' => json_encode( $data['addgroups'] ?? [] ),
				'perm_removegroups' => json_encode( $data['removegroups'] ?? [] )
			],
			[ [ 'perm_dbname', 'perm_group' ] ],
			[
				'perm_permissions' => json_encode( $data['permissions'] ?? [] ),
				'perm_addgroups' => json_encode( $data['addgroups'] ?? [] ),
				'perm_removegroups' => json_encode( $data['removegroups'] ?? [] )
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Delete a custom group
	 *
	 * @param string $dbname Wiki database name
	 * @param string $group Group name
	 * @return bool Success
	 */
	public static function deleteGroup( $dbname, $group ) {
		global $wgManageWikiPermissionsDefaultGroups;
		
		// Prevent deletion of default groups
		if ( in_array( $group, $wgManageWikiPermissionsDefaultGroups ) ) {
			return false;
		}
		
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$dbw->delete(
			'mw_permissions',
			[
				'perm_dbname' => $dbname,
				'perm_group' => $group
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Apply permissions to wiki configuration
	 *
	 * @param string $dbname Wiki database name
	 */
	public static function applyPermissions( $dbname ) {
		global $wgGroupPermissions, $wgAddGroups, $wgRemoveGroups;
		
		$groups = self::getGroups( $dbname );
		
		foreach ( $groups as $group => $data ) {
			// Apply permissions
			foreach ( $data['permissions'] as $permission => $value ) {
				$wgGroupPermissions[$group][$permission] = $value;
			}
			
			// Apply addgroups
			if ( !empty( $data['addgroups'] ) ) {
				$wgAddGroups[$group] = $data['addgroups'];
			}
			
			// Apply removegroups
			if ( !empty( $data['removegroups'] ) ) {
				$wgRemoveGroups[$group] = $data['removegroups'];
			}
		}
	}
	
	/**
	 * Log permission change
	 *
	 * @param string $dbname Wiki database name
	 * @param string $group Group name
	 * @param User $user User making the change
	 */
	public static function logChange( $dbname, $group, $user ) {
		$logEntry = new ManualLogEntry( 'managewiki', 'permission-change' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:ManageWikiPermissions/$dbname" ) );
		$logEntry->setComment( "Updated permissions for group: $group" );
		$logEntry->setParameters( [
			'4::wiki' => $dbname,
			'5::group' => $group
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}
}
