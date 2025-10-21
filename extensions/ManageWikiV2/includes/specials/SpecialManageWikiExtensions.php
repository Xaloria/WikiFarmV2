<?php
class SpecialManageWikiExtensions extends SpecialPage {
	public function __construct() { parent::__construct( 'ManageWikiExtensions', 'managewiki' ); }
	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->getOutput()->setPageTitle( $this->msg( 'managewiki-extensions' ) );
		$this->getOutput()->addWikiMsg( 'managewiki-extensions-text' );
	}
	protected function getGroupName() { return 'wikimanage'; }
}
