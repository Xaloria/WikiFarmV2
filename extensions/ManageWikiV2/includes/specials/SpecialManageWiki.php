<?php

/**
 * SpecialManageWiki - Main ManageWiki special page
 */
class SpecialManageWiki extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'ManageWiki', 'managewiki' );
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
		$out->setPageTitle( $this->msg( 'managewiki' ) );
		
		// Show links to management pages
		$html = Html::element( 'h2', [], $this->msg( 'managewiki-header' )->text() );
		$html .= Html::openElement( 'ul' );
		
		$pages = [
			'ManageWikiExtensions' => 'managewiki-link-extensions',
			'ManageWikiNamespaces' => 'managewiki-link-namespaces',
			'ManageWikiSettings' => 'managewiki-link-settings',
			'ManageWikiPermissions' => 'managewiki-link-permissions'
		];
		
		foreach ( $pages as $page => $msgKey ) {
			$html .= Html::rawElement( 'li', [],
				$this->getLinkRenderer()->makeLink(
					Title::newFromText( "Special:$page" ),
					$this->msg( $msgKey )->text()
				) . ' - ' . $this->msg( "$msgKey-desc" )->parse()
			);
		}
		
		$html .= Html::closeElement( 'ul' );
		
		$out->addHTML( $html );
	}
	
	protected function getGroupName() {
		return 'wikimanage';
	}
}
