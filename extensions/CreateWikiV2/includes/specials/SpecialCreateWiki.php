<?php

/**
 * SpecialCreateWiki - Special page for creating wikis
 */
class SpecialCreateWiki extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'CreateWiki', 'createwiki' );
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
		
		$out->setPageTitle( $this->msg( 'createwiki' ) );
		$out->addModuleStyles( 'ext.createwiki.styles' );
		
		// Handle form submission
		if ( $request->wasPosted() && $user->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
			$this->processForm( $request, $user );
			return;
		}
		
		// Show form
		$this->showForm();
	}
	
	/**
	 * Show the wiki creation form
	 */
	private function showForm() {
		global $wgCreateWikiCategories, $wgCreateWikiUsePrivateWikis;
		
		$out = $this->getOutput();
		
		$formDescriptor = [
			'dbname' => [
				'type' => 'text',
				'label-message' => 'createwiki-label-dbname',
				'help-message' => 'createwiki-help-dbname',
				'required' => true,
			],
			'sitename' => [
				'type' => 'text',
				'label-message' => 'createwiki-label-sitename',
				'help-message' => 'createwiki-help-sitename',
				'required' => true,
			],
			'language' => [
				'type' => 'language',
				'label-message' => 'createwiki-label-language',
				'default' => 'en',
				'required' => true,
			],
			'category' => [
				'type' => 'select',
				'label-message' => 'createwiki-label-category',
				'options' => $wgCreateWikiCategories,
				'default' => 'uncategorised',
				'required' => true,
			],
		];
		
		if ( $wgCreateWikiUsePrivateWikis ) {
			$formDescriptor['private'] = [
				'type' => 'check',
				'label-message' => 'createwiki-label-private',
				'help-message' => 'createwiki-help-private',
			];
		}
		
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'createwiki-submit' )
			->setSubmitCallback( [ $this, 'onSubmit' ] )
			->show();
	}
	
	/**
	 * Process form submission
	 *
	 * @param WebRequest $request
	 * @param User $user
	 */
	private function processForm( $request, $user ) {
		$out = $this->getOutput();
		
		// Get form data
		$dbname = $request->getText( 'wpdbname' );
		$sitename = $request->getText( 'wpsitename' );
		$language = $request->getText( 'wplanguage' );
		$category = $request->getText( 'wpcategory' );
		$private = $request->getBool( 'wpprivate' );
		
		// Sanitize database name
		$dbname = MirahezeFunctions::sanitizeDBname( $dbname );
		
		// Validate
		if ( empty( $dbname ) || empty( $sitename ) || empty( $language ) ) {
			$out->addHTML( Html::errorBox( $this->msg( 'createwiki-error-required' )->parse() ) );
			$this->showForm();
			return;
		}
		
		// Check if wiki already exists
		if ( MirahezeFunctions::wikiExists( $dbname ) ) {
			$out->addHTML( Html::errorBox( $this->msg( 'createwiki-error-exists' )->parse() ) );
			$this->showForm();
			return;
		}
		
		// Create database
		if ( !MirahezeFunctions::createWikiDB( $dbname ) ) {
			$out->addHTML( Html::errorBox( $this->msg( 'createwiki-error-database' )->parse() ) );
			$this->showForm();
			return;
		}
		
		// Populate database
		if ( !MirahezeFunctions::populateWikiDB( $dbname ) ) {
			$out->addHTML( Html::errorBox( $this->msg( 'createwiki-error-populate' )->parse() ) );
			$this->showForm();
			return;
		}
		
		// Add to registry
		$url = MirahezeFunctions::generateWikiURL( $dbname );
		MirahezeFunctions::addWiki( [
			'dbname' => $dbname,
			'sitename' => $sitename,
			'language' => $language,
			'private' => $private,
			'category' => $category,
			'url' => $url,
			'settings' => []
		] );
		
		// Log creation
		CreateWikiHooks::onCreateWikiCreation( $dbname, $sitename, $language, $private, $category, $user );
		
		// Show success message
		$out->addHTML( Html::successBox( 
			$this->msg( 'createwiki-success' )
				->params( $sitename, $url )
				->parse() 
		) );
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
			return $this->msg( 'createwiki-error-exists' )->text();
		}
		
		// Create database
		if ( !MirahezeFunctions::createWikiDB( $dbname ) ) {
			return $this->msg( 'createwiki-error-database' )->text();
		}
		
		// Populate database
		if ( !MirahezeFunctions::populateWikiDB( $dbname ) ) {
			return $this->msg( 'createwiki-error-populate' )->text();
		}
		
		// Add to registry
		$url = MirahezeFunctions::generateWikiURL( $dbname );
		MirahezeFunctions::addWiki( [
			'dbname' => $dbname,
			'sitename' => $data['sitename'],
			'language' => $data['language'],
			'private' => $data['private'] ?? false,
			'category' => $data['category'],
			'url' => $url,
			'settings' => []
		] );
		
		// Log creation
		CreateWikiHooks::onCreateWikiCreation( 
			$dbname, 
			$data['sitename'], 
			$data['language'], 
			$data['private'] ?? false, 
			$data['category'], 
			$user 
		);
		
		return true;
	}
	
	protected function getGroupName() {
		return 'wikimanage';
	}
}
