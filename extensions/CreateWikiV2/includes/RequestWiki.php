<?php

/**
 * RequestWiki - Handle wiki creation requests
 */
class RequestWiki {
	
	/**
	 * Submit a new wiki request
	 *
	 * @param array $data Request data
	 * @param User $user Requesting user
	 * @return int|false Request ID or false on failure
	 */
	public static function submit( $data, $user ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		// Validate required fields
		$required = [ 'dbname', 'sitename', 'language', 'category', 'reason' ];
		foreach ( $required as $field ) {
			if ( empty( $data[$field] ) ) {
				return false;
			}
		}
		
		// Sanitize database name
		$dbname = MirahezeFunctions::sanitizeDBname( $data['dbname'] );
		
		// Check if wiki already exists
		if ( MirahezeFunctions::wikiExists( $dbname ) ) {
			return false;
		}
		
		// Check if there's already a pending request for this wiki
		$existing = $dbw->selectRow(
			'cw_requests',
			'cw_id',
			[
				'cw_dbname' => $dbname,
				'cw_status' => 'pending'
			],
			__METHOD__
		);
		
		if ( $existing ) {
			return false;
		}
		
		// Insert request
		$dbw->insert(
			'cw_requests',
			[
				'cw_dbname' => $dbname,
				'cw_sitename' => $data['sitename'],
				'cw_language' => $data['language'],
				'cw_private' => isset( $data['private'] ) ? 1 : 0,
				'cw_category' => $data['category'],
				'cw_url' => MirahezeFunctions::generateWikiURL( $dbname ),
				'cw_user' => $user->getId(),
				'cw_reason' => $data['reason'],
				'cw_status' => 'pending',
				'cw_timestamp' => wfTimestampNow(),
				'cw_comment' => ''
			],
			__METHOD__
		);
		
		return $dbw->insertId();
	}
	
	/**
	 * Approve a wiki request
	 *
	 * @param int $requestId Request ID
	 * @param User $user Approver
	 * @param string $comment Optional comment
	 * @return bool Success
	 */
	public static function approve( $requestId, $user, $comment = '' ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		// Get request details
		$request = $dbw->selectRow(
			'cw_requests',
			'*',
			[
				'cw_id' => $requestId,
				'cw_status' => 'pending'
			],
			__METHOD__
		);
		
		if ( !$request ) {
			return false;
		}
		
		// Create the wiki
		$created = self::createWikiFromRequest( $request );
		
		if ( !$created ) {
			return false;
		}
		
		// Update request status
		$dbw->update(
			'cw_requests',
			[
				'cw_status' => 'approved',
				'cw_comment' => $comment
			],
			[ 'cw_id' => $requestId ],
			__METHOD__
		);
		
		// Log the approval
		$logEntry = new ManualLogEntry( 'farmer', 'requestwikiapprove' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:RequestWikiQueue/$requestId" ) );
		$logEntry->setComment( $comment );
		$logEntry->setParameters( [
			'4::wiki' => $request->cw_dbname
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
		
		return true;
	}
	
	/**
	 * Decline a wiki request
	 *
	 * @param int $requestId Request ID
	 * @param User $user Decliner
	 * @param string $reason Reason for declining
	 * @return bool Success
	 */
	public static function decline( $requestId, $user, $reason ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		// Update request status
		$result = $dbw->update(
			'cw_requests',
			[
				'cw_status' => 'declined',
				'cw_comment' => $reason
			],
			[
				'cw_id' => $requestId,
				'cw_status' => 'pending'
			],
			__METHOD__
		);
		
		if ( !$result ) {
			return false;
		}
		
		// Log the decline
		$logEntry = new ManualLogEntry( 'farmer', 'requestwikidecline' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:RequestWikiQueue/$requestId" ) );
		$logEntry->setComment( $reason );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
		
		return true;
	}
	
	/**
	 * Create wiki from request
	 *
	 * @param object $request Request database row
	 * @return bool Success
	 */
	private static function createWikiFromRequest( $request ) {
		// Create database
		if ( !MirahezeFunctions::createWikiDB( $request->cw_dbname ) ) {
			return false;
		}
		
		// Populate with MediaWiki tables
		if ( !MirahezeFunctions::populateWikiDB( $request->cw_dbname ) ) {
			return false;
		}
		
		// Add to wiki registry
		MirahezeFunctions::addWiki( [
			'dbname' => $request->cw_dbname,
			'sitename' => $request->cw_sitename,
			'language' => $request->cw_language,
			'private' => $request->cw_private,
			'category' => $request->cw_category,
			'url' => $request->cw_url,
			'settings' => []
		] );
		
		return true;
	}
	
	/**
	 * Get request by ID
	 *
	 * @param int $requestId Request ID
	 * @return array|false Request data or false
	 */
	public static function getRequest( $requestId ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$row = $dbr->selectRow(
			'cw_requests',
			'*',
			[ 'cw_id' => $requestId ],
			__METHOD__
		);
		
		if ( !$row ) {
			return false;
		}
		
		return [
			'id' => $row->cw_id,
			'dbname' => $row->cw_dbname,
			'sitename' => $row->cw_sitename,
			'language' => $row->cw_language,
			'private' => (bool)$row->cw_private,
			'category' => $row->cw_category,
			'url' => $row->cw_url,
			'user' => $row->cw_user,
			'reason' => $row->cw_reason,
			'status' => $row->cw_status,
			'timestamp' => $row->cw_timestamp,
			'comment' => $row->cw_comment
		];
	}
	
	/**
	 * Add comment to request
	 *
	 * @param int $requestId Request ID
	 * @param User $user Commenter
	 * @param string $comment Comment text
	 * @return bool Success
	 */
	public static function addComment( $requestId, $user, $comment ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$dbw->insert(
			'cw_comments',
			[
				'cw_id' => $requestId,
				'cw_comment' => $comment,
				'cw_user' => $user->getId(),
				'cw_timestamp' => wfTimestampNow()
			],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Get comments for request
	 *
	 * @param int $requestId Request ID
	 * @return array Array of comments
	 */
	public static function getComments( $requestId ) {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$res = $dbr->select(
			'cw_comments',
			'*',
			[ 'cw_id' => $requestId ],
			__METHOD__,
			[ 'ORDER BY' => 'cw_timestamp ASC' ]
		);
		
		$comments = [];
		foreach ( $res as $row ) {
			$comments[] = [
				'user' => $row->cw_user,
				'comment' => $row->cw_comment,
				'timestamp' => $row->cw_timestamp
			];
		}
		
		return $comments;
	}
}
