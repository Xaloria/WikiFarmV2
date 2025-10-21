<?php
class SpecialManageWikiSettings extends SpecialPage {
	public function __construct() { parent::__construct( 'ManageWikiSettings', 'managewiki' ); }
	public function execute( $par ) {
		$this->setHeaders();
		$this->checkPermissions();
		$this->getOutput()->setPageTitle( $this->msg( 'managewiki-settings' ) );
		$this->getOutput()->addWikiMsg( 'managewiki-settings-text' );
	}
	protected function getGroupName() { return 'wikimanage'; }
}
