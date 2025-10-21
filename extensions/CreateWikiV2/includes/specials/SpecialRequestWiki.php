<?php

/**
 * SpecialRequestWiki - Special page for requesting wikis
 */
class SpecialRequestWiki extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'RequestWiki', 'requestwiki' );
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
		$user = $this->getUser();
		
		$out->setPageTitle( $this->msg( 'requestwiki' ) );
		$out->addModuleStyles( 'ext.createwiki.styles' );
		
		// Show form
		$this->showForm();
	}
	
	/**
	 * Show the wiki request form
	 */
	private function showForm() {
		global $wgCreateWikiCategories, $wgCreateWikiUsePrivateWikis;
		
		$formDescriptor = [
			'info' => [
				'type' => 'info',
				'default' => $this->msg( 'requestwiki-info' )->parse(),
				'raw' => true,
			],
			'dbname' => [
				'type' => 'text',
				'label-message' => 'requestwiki-label-dbname',
				'help-message' => 'requestwiki-help-dbname',
				'required' => true,
			],
			'sitename' => [
				'type' => 'text',
				'label-message' => 'requestwiki-label-sitename',
				'help-message' => 'requestwiki-help-sitename',
				'required' => true,
			],
			'language' => [
				'type' => 'language',
				'label-message' => 'requestwiki-label-language',
				'default' => 'en',
				'required' => true,
			],
			'category' => [
				'type' => 'select',
				'label-message' => 'requestwiki-label-category',
				'options' => $wgCreateWikiCategories,
				'default' => 'uncategorised',
				'required' => true,
			],
		];
		
		if ( $wgCreateWikiUsePrivateWikis ) {
			$formDescriptor['private'] = [
				'type' => 'check',
				'label-message' => 'requestwiki-label-private',
				'help-message' => 'requestwiki-help-private',
			];
		}
		
		$formDescriptor['reason'] = [
			'type' => 'textarea',
			'label-message' => 'requestwiki-label-reason',
			'help-message' => 'requestwiki-help-reason',
			'required' => true,
			'rows' => 5,
		];
		
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'requestwiki-submit' )
			->setSubmitCallback( [ $this, 'onSubmit' ] )
			->show();
	}
	
	/**
	 * HTMLForm submit callback
	 *
	 * @param array $data Form data
	 * @return bool|string True on success, error message on failure
	 */
	public function onSubmit( array $data ) {
		$user = $this->getUser();
		
		// Sanitize database name
		$dbname = MirahezeFunctions::sanitizeDBname( $data['dbname'] );
		
		// Check if wiki already exists
		if ( MirahezeFunctions::wikiExists( $dbname ) ) {
			return $this->msg( 'requestwiki-error-exists' )->text();
		}
		
		// Submit request
		$requestData = [
			'dbname' => $dbname,
			'sitename' => $data['sitename'],
			'language' => $data['language'],
			'category' => $data['category'],
			'private' => $data['private'] ?? false,
			'reason' => $data['reason']
		];
		
		$requestId = RequestWiki::submit( $requestData, $user );
		
		if ( !$requestId ) {
			return $this->msg( 'requestwiki-error-failed' )->text();
		}
		
		// Show success message
		$this->getOutput()->addHTML( Html::successBox( 
			$this->msg( 'requestwiki-success' )
				->params( $requestId )
				->parse() 
		) );
		
		return true;
	}
	
	protected function getGroupName() {
		return 'wikimanage';
	}
}
