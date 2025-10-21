<?php
class SpecialManageWikiPermissions extends SpecialPage {
	public function __construct() { parent::__construct( 'ManageWikiPermissions', 'managewiki' ); }
	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->getOutput()->setPageTitle( $this->msg( 'managewiki-permissions' ) );
		$this->getOutput()->addWikiMsg( 'managewiki-permissions-text' );
	}
	protected function getGroupName() { return 'wikimanage'; }
}
