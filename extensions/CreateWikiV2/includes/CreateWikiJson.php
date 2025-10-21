<?php

/**
 * CreateWikiJson - JSON configuration handler for CreateWiki
 */
class CreateWikiJson {
	
	/** @var string Cache directory */
	private $cacheDir;
	
	/** @var array Loaded wikis */
	private $wikis = [];
	
	/**
	 * Constructor
	 */
	public function __construct() {
		global $wgCreateWikiCacheDirectory;
		$this->cacheDir = $wgCreateWikiCacheDirectory;
		
		// Ensure cache directory exists
		if ( !is_dir( $this->cacheDir ) ) {
			@mkdir( $this->cacheDir, 0755, true );
		}
	}
	
	/**
	 * Load all wikis from database into cache
	 */
	public function load() {
		$dbr = MirahezeFunctions::getDB( null, DB_REPLICA );
		
		$res = $dbr->select(
			'cw_wikis',
			'*',
			[],
			__METHOD__
		);
		
		foreach ( $res as $row ) {
			$this->wikis[$row->wiki_dbname] = [
				'sitename' => $row->wiki_sitename,
				'language' => $row->wiki_language,
				'private' => (bool)$row->wiki_private,
				'closed' => (bool)$row->wiki_closed,
				'inactive' => (bool)$row->wiki_inactive,
				'category' => $row->wiki_category,
				'url' => $row->wiki_url,
				'settings' => json_decode( $row->wiki_settings, true ) ?: []
			];
		}
	}
	
	/**
	 * Export wikis to JSON file
	 *
	 * @param string $filename Output filename
	 * @return bool Success
	 */
	public function exportToFile( $filename = null ) {
		if ( !$filename ) {
			$filename = $this->cacheDir . '/wikis.json';
		}
		
		$this->load();
		
		$json = json_encode( $this->wikis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		return file_put_contents( $filename, $json ) !== false;
	}
	
	/**
	 * Import wikis from JSON file
	 *
	 * @param string $filename Input filename
	 * @return bool Success
	 */
	public function importFromFile( $filename ) {
		if ( !file_exists( $filename ) ) {
			return false;
		}
		
		$json = file_get_contents( $filename );
		$wikis = json_decode( $json, true );
		
		if ( !is_array( $wikis ) ) {
			return false;
		}
		
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		foreach ( $wikis as $dbname => $data ) {
			$dbw->upsert(
				'cw_wikis',
				[
					'wiki_dbname' => $dbname,
					'wiki_sitename' => $data['sitename'],
					'wiki_language' => $data['language'],
					'wiki_private' => $data['private'] ? 1 : 0,
					'wiki_closed' => $data['closed'] ? 1 : 0,
					'wiki_inactive' => $data['inactive'] ? 1 : 0,
					'wiki_category' => $data['category'],
					'wiki_url' => $data['url'],
					'wiki_settings' => json_encode( $data['settings'] )
				],
				[ 'wiki_dbname' ],
				[
					'wiki_sitename' => $data['sitename'],
					'wiki_language' => $data['language'],
					'wiki_private' => $data['private'] ? 1 : 0,
					'wiki_closed' => $data['closed'] ? 1 : 0,
					'wiki_inactive' => $data['inactive'] ? 1 : 0,
					'wiki_category' => $data['category'],
					'wiki_url' => $data['url'],
					'wiki_settings' => json_encode( $data['settings'] )
				],
				__METHOD__
			);
		}
		
		return true;
	}
	
	/**
	 * Get wiki list as array
	 *
	 * @return array
	 */
	public function getWikis() {
		if ( empty( $this->wikis ) ) {
			$this->load();
		}
		
		return $this->wikis;
	}
	
	/**
	 * Get single wiki data
	 *
	 * @param string $dbname Database name
	 * @return array|null
	 */
	public function getWiki( $dbname ) {
		if ( empty( $this->wikis ) ) {
			$this->load();
		}
		
		return $this->wikis[$dbname] ?? null;
	}
}
