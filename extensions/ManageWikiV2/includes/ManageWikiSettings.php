<?php

/**
 * ManageWikiSettings - Manage wiki-specific settings
 */
class ManageWikiSettings {
	
	/**
	 * Get available settings that can be managed
	 *
	 * @return array Array of setting configurations
	 */
	public static function getAvailableSettings() {
		global $wgManageWikiSettings;
		
		// Default settings if not configured
		if ( empty( $wgManageWikiSettings ) ) {
			return self::getDefaultSettings();
		}
		
		return $wgManageWikiSettings;
	}
	
	/**
	 * Get default manageable settings
	 *
	 * @return array
	 */
	private static function getDefaultSettings() {
		return [
			'wgSitename' => [
				'name' => 'Site name',
				'type' => 'text',
				'help' => 'The name of your wiki',
				'section' => 'general'
			],
			'wgMetaNamespace' => [
				'name' => 'Meta namespace',
				'type' => 'text',
				'help' => 'Namespace for project pages',
				'section' => 'general'
			],
			'wgLanguageCode' => [
				'name' => 'Language',
				'type' => 'language',
				'help' => 'Default language for the wiki',
				'section' => 'general'
			],
			'wgDefaultSkin' => [
				'name' => 'Default skin',
				'type' => 'select',
				'options' => [
					'vector' => 'Vector',
					'monobook' => 'MonoBook',
					'timeless' => 'Timeless',
					'minerva' => 'Minerva'
				],
				'help' => 'Default skin for users',
				'section' => 'appearance'
			],
			'wgLogo' => [
				'name' => 'Logo URL',
				'type' => 'text',
				'help' => 'URL to the wiki logo',
				'section' => 'appearance'
			],
			'wgEnableUploads' => [
				'name' => 'Enable file uploads',
				'type' => 'check',
				'help' => 'Allow users to upload files',
				'section' => 'uploads'
			],
			'wgUseImageMagick' => [
				'name' => 'Use ImageMagick',
				'type' => 'check',
				'help' => 'Use ImageMagick for image processing',
				'section' => 'uploads'
			],
			'wgFileExtensions' => [
				'name' => 'Allowed file extensions',
				'type' => 'array',
				'help' => 'Comma-separated list of allowed file extensions',
				'section' => 'uploads'
			],
			'wgMaxUploadSize' => [
				'name' => 'Max upload size',
				'type' => 'text',
				'help' => 'Maximum file upload size in bytes',
				'section' => 'uploads'
			],
			'wgRightsText' => [
				'name' => 'Copyright text',
				'type' => 'text',
				'help' => 'Copyright/license text',
				'section' => 'rights'
			],
			'wgRightsUrl' => [
				'name' => 'Copyright URL',
				'type' => 'text',
				'help' => 'URL to copyright/license',
				'section' => 'rights'
			],
			'wgRightsIcon' => [
				'name' => 'Copyright icon URL',
				'type' => 'text',
				'help' => 'URL to copyright/license icon',
				'section' => 'rights'
			],
			'wgEmergencyContact' => [
				'name' => 'Emergency contact email',
				'type' => 'text',
				'help' => 'Email for emergency contact',
				'section' => 'email'
			],
			'wgPasswordSender' => [
				'name' => 'Password sender email',
				'type' => 'text',
				'help' => 'Email address for password emails',
				'section' => 'email'
			],
		];
	}
	
	/**
	 * Get current settings for a wiki
	 *
	 * @param string $dbname Wiki database name
	 * @return array Current settings
	 */
	public static function getSettings( $dbname ) {
		$wikiInfo = MirahezeFunctions::getWikiInfo( $dbname );
		
		if ( !$wikiInfo ) {
			return [];
		}
		
		return $wikiInfo['settings'] ?? [];
	}
	
	/**
	 * Get a specific setting value
	 *
	 * @param string $dbname Wiki database name
	 * @param string $setting Setting name
	 * @return mixed Setting value or null
	 */
	public static function getSetting( $dbname, $setting ) {
		$settings = self::getSettings( $dbname );
		return $settings[$setting] ?? null;
	}
	
	/**
	 * Update a setting
	 *
	 * @param string $dbname Wiki database name
	 * @param string $setting Setting name
	 * @param mixed $value New value
	 * @return bool Success
	 */
	public static function updateSetting( $dbname, $setting, $value ) {
		$available = self::getAvailableSettings();
		
		// Validate setting exists
		if ( !isset( $available[$setting] ) ) {
			return false;
		}
		
		$config = $available[$setting];
		
		// Validate value based on type
		$value = self::validateValue( $value, $config['type'] );
		
		if ( $value === false ) {
			return false;
		}
		
		// Update setting
		return MirahezeFunctions::updateWikiSettings( $dbname, [ $setting => $value ] );
	}
	
	/**
	 * Update multiple settings
	 *
	 * @param string $dbname Wiki database name
	 * @param array $settings Array of setting => value pairs
	 * @return bool Success
	 */
	public static function updateSettings( $dbname, $settings ) {
		$available = self::getAvailableSettings();
		$validated = [];
		
		foreach ( $settings as $setting => $value ) {
			// Skip unavailable settings
			if ( !isset( $available[$setting] ) ) {
				continue;
			}
			
			$config = $available[$setting];
			$value = self::validateValue( $value, $config['type'] );
			
			if ( $value !== false ) {
				$validated[$setting] = $value;
			}
		}
		
		if ( empty( $validated ) ) {
			return false;
		}
		
		return MirahezeFunctions::updateWikiSettings( $dbname, $validated );
	}
	
	/**
	 * Validate setting value based on type
	 *
	 * @param mixed $value Value to validate
	 * @param string $type Setting type
	 * @return mixed Validated value or false on failure
	 */
	private static function validateValue( $value, $type ) {
		switch ( $type ) {
			case 'text':
				return is_string( $value ) ? $value : false;
			
			case 'check':
				return (bool)$value;
			
			case 'select':
				return is_string( $value ) ? $value : false;
			
			case 'language':
				// Validate language code
				return is_string( $value ) && strlen( $value ) <= 12 ? $value : false;
			
			case 'array':
				if ( is_string( $value ) ) {
					// Convert comma-separated string to array
					return array_map( 'trim', explode( ',', $value ) );
				} elseif ( is_array( $value ) ) {
					return $value;
				}
				return false;
			
			default:
				return $value;
		}
	}
	
	/**
	 * Get settings grouped by section
	 *
	 * @return array Settings grouped by section
	 */
	public static function getSettingsBySection() {
		$available = self::getAvailableSettings();
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
	 * Reset a setting to default
	 *
	 * @param string $dbname Wiki database name
	 * @param string $setting Setting name
	 * @return bool Success
	 */
	public static function resetSetting( $dbname, $setting ) {
		$dbw = MirahezeFunctions::getDB( null, DB_PRIMARY );
		
		$currentSettings = self::getSettings( $dbname );
		unset( $currentSettings[$setting] );
		
		$dbw->update(
			'cw_wikis',
			[ 'wiki_settings' => json_encode( $currentSettings ) ],
			[ 'wiki_dbname' => $dbname ],
			__METHOD__
		);
		
		return true;
	}
	
	/**
	 * Log setting change
	 *
	 * @param string $dbname Wiki database name
	 * @param string $setting Setting name
	 * @param mixed $value New value
	 * @param User $user User making the change
	 */
	public static function logChange( $dbname, $setting, $value, $user ) {
		$logEntry = new ManualLogEntry( 'managewiki', 'setting-change' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( Title::newFromText( "Special:ManageWikiSettings/$dbname" ) );
		$logEntry->setComment( "$setting = " . var_export( $value, true ) );
		$logEntry->setParameters( [
			'4::wiki' => $dbname,
			'5::setting' => $setting
		] );
		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}
}
