<?php

/**
 * SpecialRequestWikiQueue - Special page for managing wiki requests
 */
class SpecialRequestWikiQueue extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'RequestWikiQueue', 'createwiki' );
	}
	
	/**
	 * Show the special page
	 *
	 * @param string|null $par Parameter passed to the page
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();
		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		$out->setPageTitle( $this->msg( 'requestwikiqueue' ) );
		$out->addModuleStyles( 'ext.createwiki.styles' );
		
		// Handle specific request
		if ( $par ) {
			$this->showRequest( intval( $par ) );
			return;
		}
		
		// Show queue list
		$this->showQueue();
	}
	
	/**
	 * Show the queue of pending requests
	 */
	private function showQueue() {
		$out = $this->getOutput();
		
		// Get all pending requests
		$requests = MirahezeFunctions::getWikiRequests( 'pending' );
		
		if ( empty( $requests ) ) {
			$out->addWikiMsg( 'requestwikiqueue-none' );
			return;
		}
		
		// Build table
		$html = Html::openElement( 'table', [ 'class' => 'wikitable sortable' ] );
		$html .= Html::openElement( 'thead' );
		$html .= Html::openElement( 'tr' );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-id' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-dbname' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-sitename' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-requester' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-timestamp' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'requestwikiqueue-header-actions' )->text() );
		$html .= Html::closeElement( 'tr' );
		$html .= Html::closeElement( 'thead' );
		$html .= Html::openElement( 'tbody' );
		
		foreach ( $requests as $req ) {
			$requester = User::newFromId( $req['requester'] );
			$requesterName = $requester->getName();
			
			$html .= Html::openElement( 'tr' );
			$html .= Html::element( 'td', [], $req['id'] );
			$html .= Html::element( 'td', [], $req['dbname'] );
			$html .= Html::element( 'td', [], $req['sitename'] );
			$html .= Html::element( 'td', [], $requesterName );
			$html .= Html::element( 'td', [], 
				$this->getLanguage()->userTimeAndDate( $req['timestamp'], $this->getUser() ) 
			);
			$html .= Html::rawElement( 'td', [], 
				$this->getLinkRenderer()->makeLink(
					$this->getPageTitle( (string)$req['id'] ),
					$this->msg( 'requestwikiqueue-view' )->text()
				)
			);
			$html .= Html::closeElement( 'tr' );
		}
		
		$html .= Html::closeElement( 'tbody' );
		$html .= Html::closeElement( 'table' );
		
		$out->addHTML( $html );
	}
	
	/**
	 * Show a specific request
	 *
	 * @param int $requestId Request ID
	 */
	private function showRequest( $requestId ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		// Get request data
		$requestData = RequestWiki::getRequest( $requestId );
		
		if ( !$requestData ) {
			$out->addHTML( Html::errorBox( $this->msg( 'requestwikiqueue-notfound' )->parse() ) );
			return;
		}
		
		// Handle actions
		if ( $request->wasPosted() && $user->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
			$action = $request->getVal( 'action' );
			
			if ( $action === 'approve' ) {
				$comment = $request->getText( 'comment' );
				if ( RequestWiki::approve( $requestId, $user, $comment ) ) {
					$out->addHTML( Html::successBox( $this->msg( 'requestwikiqueue-approved' )->parse() ) );
				} else {
					$out->addHTML( Html::errorBox( $this->msg( 'requestwikiqueue-error-approve' )->parse() ) );
				}
			} elseif ( $action === 'decline' ) {
				$reason = $request->getText( 'reason' );
				if ( RequestWiki::decline( $requestId, $user, $reason ) ) {
					$out->addHTML( Html::successBox( $this->msg( 'requestwikiqueue-declined' )->parse() ) );
				} else {
					$out->addHTML( Html::errorBox( $this->msg( 'requestwikiqueue-error-decline' )->parse() ) );
				}
			} elseif ( $action === 'comment' ) {
				$comment = $request->getText( 'commenttext' );
				RequestWiki::addComment( $requestId, $user, $comment );
			}
			
			// Reload request data
			$requestData = RequestWiki::getRequest( $requestId );
		}
		
		// Display request details
		$requester = User::newFromId( $requestData['user'] );
		
		$html = Html::openElement( 'div', [ 'class' => 'requestwiki-details' ] );
		
		// Basic info
		$html .= Html::element( 'h2', [], $this->msg( 'requestwikiqueue-details' )->text() );
		$html .= Html::openElement( 'table', [ 'class' => 'wikitable' ] );
		$html .= $this->makeRow( 'requestwikiqueue-label-id', $requestData['id'] );
		$html .= $this->makeRow( 'requestwikiqueue-label-dbname', $requestData['dbname'] );
		$html .= $this->makeRow( 'requestwikiqueue-label-sitename', $requestData['sitename'] );
		$html .= $this->makeRow( 'requestwikiqueue-label-language', $requestData['language'] );
		$html .= $this->makeRow( 'requestwikiqueue-label-category', $requestData['category'] );
		$html .= $this->makeRow( 'requestwikiqueue-label-private', 
			$requestData['private'] ? $this->msg( 'requestwikiqueue-yes' )->text() : $this->msg( 'requestwikiqueue-no' )->text() 
		);
		$html .= $this->makeRow( 'requestwikiqueue-label-requester', $requester->getName() );
		$html .= $this->makeRow( 'requestwikiqueue-label-timestamp', 
			$this->getLanguage()->userTimeAndDate( $requestData['timestamp'], $user ) 
		);
		$html .= $this->makeRow( 'requestwikiqueue-label-status', $requestData['status'] );
		$html .= Html::closeElement( 'table' );
		
		// Reason
		$html .= Html::element( 'h3', [], $this->msg( 'requestwikiqueue-reason' )->text() );
		$html .= Html::element( 'p', [], $requestData['reason'] );
		
		// Comments
		$comments = RequestWiki::getComments( $requestId );
		if ( !empty( $comments ) ) {
			$html .= Html::element( 'h3', [], $this->msg( 'requestwikiqueue-comments' )->text() );
			foreach ( $comments as $comment ) {
				$commentUser = User::newFromId( $comment['user'] );
				$html .= Html::openElement( 'div', [ 'class' => 'requestwiki-comment' ] );
				$html .= Html::element( 'strong', [], $commentUser->getName() );
				$html .= Html::element( 'span', [ 'class' => 'comment-time' ], 
					' (' . $this->getLanguage()->userTimeAndDate( $comment['timestamp'], $user ) . '): '
				);
				$html .= Html::element( 'p', [], $comment['comment'] );
				$html .= Html::closeElement( 'div' );
			}
		}
		
		$html .= Html::closeElement( 'div' );
		
		$out->addHTML( $html );
		
		// Show action forms if still pending
		if ( $requestData['status'] === 'pending' ) {
			$this->showActionForms( $requestId );
		}
	}
	
	/**
	 * Make a table row
	 *
	 * @param string $labelMsg Message key for label
	 * @param string $value Value
	 * @return string HTML
	 */
	private function makeRow( $labelMsg, $value ) {
		return Html::openElement( 'tr' ) .
			Html::element( 'th', [], $this->msg( $labelMsg )->text() ) .
			Html::element( 'td', [], $value ) .
			Html::closeElement( 'tr' );
	}
	
	/**
	 * Show action forms (approve/decline/comment)
	 *
	 * @param int $requestId Request ID
	 */
	private function showActionForms( $requestId ) {
		$out = $this->getOutput();
		
		// Approve form
		$formDescriptor = [
			'comment' => [
				'type' => 'text',
				'label-message' => 'requestwikiqueue-approve-comment',
			],
		];
		
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'requestwikiqueue-approve' )
			->setSubmitName( 'action' )
			->setSubmitID( 'approve' )
			->setSubmitCallback( function() {
				return false; // Handled above
			} )
			->addHiddenField( 'action', 'approve' )
			->prepareForm()
			->displayForm( false );
		
		// Decline form
		$formDescriptor = [
			'reason' => [
				'type' => 'textarea',
				'label-message' => 'requestwikiqueue-decline-reason',
				'required' => true,
			],
		];
		
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'requestwikiqueue-decline' )
			->setSubmitName( 'action' )
			->setSubmitID( 'decline' )
			->setSubmitCallback( function() {
				return false; // Handled above
			} )
			->addHiddenField( 'action', 'decline' )
			->prepareForm()
			->displayForm( false );
		
		// Comment form
		$formDescriptor = [
			'commenttext' => [
				'type' => 'textarea',
				'label-message' => 'requestwikiqueue-comment-text',
				'required' => true,
			],
		];
		
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'requestwikiqueue-comment-submit' )
			->setSubmitName( 'action' )
			->setSubmitID( 'comment' )
			->setSubmitCallback( function() {
				return false; // Handled above
			} )
			->addHiddenField( 'action', 'comment' )
			->prepareForm()
			->displayForm( false );
	}
	
	protected function getGroupName() {
		return 'wikimanage';
	}
}
