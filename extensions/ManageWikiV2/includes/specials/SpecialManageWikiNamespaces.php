<?php
class SpecialManageWikiNamespaces extends SpecialPage {
	public function __construct() { parent::__construct( 'ManageWikiNamespaces', 'managewiki' ); }
	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->getOutput()->setPageTitle( $this->msg( 'managewiki-namespaces' ) );
		$this->getOutput()->addWikiMsg( 'managewiki-namespaces-text' );
	}
	protected function getGroupName() { return 'wikimanage'; }
}
