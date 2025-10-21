<?php

/**
 * ManageWikiExtensions - Manage MediaWiki extensions per wiki
 */
class ManageWikiExtensions {
	
	/**
	 * Get list of manageable extensions
	 *
	 * @return array Array of extension configurations
	 */
	public static function getAvailableExtensions() {
		global $wgManageWikiExtensions;
		
		// Default extensions if not configured
		if ( empty( $wgManageWikiExtensions ) ) {
			return self::getDefaultExtensions();
		}
		
		return $wgManageWikiExtensions;
	}
	
	/**
	 * Get default list of manageable extensions
	 *
	 * @return array
	 */
	private static function getDefaultExtensions() {
		return [
			'Cite' => [
				'name' => 'Cite',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:Cite',
				'var' => 'wmgUseCite',
				'conflicts' => false,
				'requires' => [],
				'section' => 'parserhooks'
			],
			'CodeEditor' => [
				'name' => 'CodeEditor',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:CodeEditor',
				'var' => 'wmgUseCodeEditor',
				'conflicts' => false,
				'requires' => [],
				'section' => 'editor'
			],
			'Gadgets' => [
				'name' => 'Gadgets',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:Gadgets',
				'var' => 'wmgUseGadgets',
				'conflicts' => false,
				'requires' => [],
				'section' => 'other'
			],
			'ImageMap' => [
				'name' => 'ImageMap',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:ImageMap',
				'var' => 'wmgUseImageMap',
				'conflicts' => false,
				'requires' => [],
				'section' => 'media'
			],
			'InputBox' => [
				'name' => 'InputBox',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:InputBox',
				'var' => 'wmgUseInputBox',
				'conflicts' => false,
				'requires' => [],
				'section' => 'parserhooks'
			],
			'ParserFunctions' => [
				'name' => 'ParserFunctions',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:ParserFunctions',
				'var' => 'wmgUseParserFunctions',
				'conflicts' => false,
				'requires' => [],
				'section' => 'parserhooks'
			],
			'Scribunto' => [
				'name' => 'Scribunto',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:Scribunto',
				'var' => 'wmgUseScribunto',
				'conflicts' => false,
				'requires' => [],
				'section' => 'parserhooks'
			],
			'TemplateData' => [
				'name' => 'TemplateData',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:TemplateData',
				'var' => 'wmgUseTemplateData',
				'conflicts' => false,
				'requires' => [],
				'section' => 'parserhooks'
			],
			'VisualEditor' => [
				'name' => 'VisualEditor',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:VisualEditor',
				'var' => 'wmgUseVisualEditor',
				'conflicts' => false,
				'requires' => [],
				'section' => 'editor'
			],
			'WikiEditor' => [
				'name' => 'WikiEditor',
				'linkPage' => 'https://www.mediawiki.org/wiki/Extension:WikiEditor',
				'var' => 'wmgUseWikiEditor',
				'conflicts' => false,
				'requires' => [],
				'section' => 'editor'
			],
		];
	}
	
	/**
	 * Get enabled extensions for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @return array Array of enabled extension names
	 */
	public static function getEnabledExtensions( $dbname ) {
		$wikiInfo = MirahezeFunctions::getWikiInfo( $dbname );
		
		if ( !$wikiInfo || empty( $wikiInfo['settings'] ) ) {
			return [];
		}
		
		$enabled = [];
		$available = self::getAvailableExtensions();
		
		foreach ( $available as $ext => $config ) {
			$var = $config['var'];
			if ( isset( $wikiInfo['settings'][$var] ) && $wikiInfo['settings'][$var] ) {
				$enabled[] = $ext;
			}
		}
		
		return $enabled;
	}
	
	/**
	 * Enable an extension for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @param string $extension Extension name
	 * @return bool Success
	 */
	public static function enableExtension( $dbname, $extension ) {
		$available = self::getAvailableExtensions();
		
		if ( !isset( $available[$extension] ) ) {
			return false;
		}
		
		$config = $available[$extension];
		$var = $config['var'];
		
		// Check requirements
		if ( !empty( $config['requires'] ) ) {
			$enabled = self::getEnabledExtensions( $dbname );
			foreach ( $config['requires'] as $required ) {
				if ( !in_array( $required, $enabled ) ) {
					return false;
				}
			}
		}
		
		// Check conflicts
		if ( !empty( $config['conflicts'] ) ) {
			$enabled = self::getEnabledExtensions( $dbname );
			foreach ( $config['conflicts'] as $conflict ) {
				if ( in_array( $conflict, $enabled ) ) {
					return false;
				}
			}
		}
		
		// Enable extension
		return MirahezeFunctions::updateWikiSettings( $dbname, [ $var => true ] );
	}
	
	/**
	 * Disable an extension for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @param string $extension Extension name
	 * @return bool Success
	 */
	public static function disableExtension( $dbname, $extension ) {
		$available = self::getAvailableExtensions();
		
		if ( !isset( $available[$extension] ) ) {
			return false;
		}
		
		$config = $available[$extension];
		$var = $config['var'];
		
		// Disable extension
		return MirahezeFunctions::updateWikiSettings( $dbname, [ $var => false ] );
	}
	
	/**
	 * Get extensions grouped by section
	 *
	 * @return array Extensions grouped by section
	 */
	public static function getExtensionsBySection() {
		$available = self::getAvailableExtensions();
		$sections = [];
		
		foreach ( $available as $name => $config ) {
			$section = $config['section'] ?? 'other';
			if ( !isset( $sections[$section] ) ) {
				$sections[$section] = [];
			}
			$sections[$section][$name] = $config;
		}
		
		return $sections;
	}
	
	/**
	 * Log extension change
	 *
	 * @param string $dbname Wiki database name
	 * @param string $extension Extension name
	 * @param bool $enabled Whether enabled or disabled
	 * @param User $user User making the change
	 */
	public static function logChange( $dbname, $extension, $enabled, $user ) {
		$action = $enabled ? 'enable-extension' : 'disable-extension';
		
		$logEntry = new ManualLogEntry( 'managewiki', $action );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:ManageWikiExtensions/$dbname" ) );
		$logEntry->setComment( $extension );
		$logEntry->setParameters( [
			'4::wiki' => $dbname,
			'5::extension' => $extension
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}
}
