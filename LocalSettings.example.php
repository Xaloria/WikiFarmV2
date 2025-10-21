<?php
/**
 * Example LocalSettings.php configuration for WikiFarmV2
 * 
 * This file shows how to configure MediaWiki with CreateWikiV2 and ManageWikiV2
 * extensions for a wiki farm setup.
 * 
 * Copy the relevant sections to your actual LocalSettings.php file.
 */

// ============================================================================
// BASIC MEDIAWIKI CONFIGURATION (adjust for your installation)
// ============================================================================

if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}

$wgSitename = "WikiFarm Central";
$wgMetaNamespace = "WikiFarm";

## Database settings
$wgDBtype = "mysql";
$wgDBserver = "localhost";
$wgDBname = "central_wiki";
$wgDBuser = "wiki_user";
$wgDBpassword = "wiki_password";

# MySQL specific settings
$wgDBprefix = "";
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

## Shared memory settings
$wgMainCacheType = CACHE_ACCEL;
$wgMemCachedServers = [];

## Language and locale
$wgLanguageCode = "en";
$wgLocaltimezone = "UTC";

## Site URLs
$wgServer = "https://central.example.com";
$wgScriptPath = "";
$wgArticlePath = "/wiki/$1";

## Uploads
$wgEnableUploads = true;
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";

## Email
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmergencyContact = "admin@example.com";
$wgPasswordSender = "noreply@example.com";

## User rights
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['user']['edit'] = true;

// ============================================================================
// CREATEWIKIV2 CONFIGURATION
// ============================================================================

// Load the extension
wfLoadExtension( 'CreateWikiV2' );

// Database for wiki registry (create this database first!)
$wgCreateWikiDatabase = 'createwiki';

// Enable database clusters (set to true for multi-server setups)
$wgCreateWikiDatabaseClusters = false;

// Cache directory (must be writable by web server)
// For shared hosting: use a directory in your home folder
$wgCreateWikiCacheDirectory = '/tmp/createwiki';
// Example for shared hosting:
// $wgCreateWikiCacheDirectory = '/home/username/cache/createwiki';

// Your main domain (used for generating wiki URLs)
// Wiki URLs will be: https://wikiname.YOUR-DOMAIN.com
$wgCreateWikiSubdomain = 'example.com';

// Enable wiki categories
$wgCreateWikiUseCategories = true;

// Define available wiki categories
$wgCreateWikiCategories = [
    'uncategorised' => 'Uncategorised',
    'community' => 'Community',
    'education' => 'Education',
    'entertainment' => 'Entertainment',
    'technology' => 'Technology',
    'gaming' => 'Gaming',
    'wiki' => 'Wiki',
    'personal' => 'Personal',
    'business' => 'Business',
];

// Path to SQL files for wiki creation
$wgCreateWikiSQLfiles = "$IP/extensions/CreateWikiV2/sql";

// Enable private wiki creation
$wgCreateWikiUsePrivateWikis = true;

// Enable closed wiki status
$wgCreateWikiUseClosedWikis = true;

// Track inactive wikis
$wgCreateWikiUseInactiveWikis = true;

// Show biographical option (for privacy)
$wgCreateWikiShowBiographicalOption = true;

// ============================================================================
// MANAGEWIKIV2 CONFIGURATION
// ============================================================================

// Load the extension
wfLoadExtension( 'ManageWikiV2' );

// Define manageable extensions
$wgManageWikiExtensions = [
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

// Extensions enabled by default for new wikis
$wgManageWikiExtensionsDefault = [
    'Cite',
    'ParserFunctions',
    'WikiEditor'
];

// Define manageable settings
$wgManageWikiSettings = [
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
];

// Default user groups
$wgManageWikiPermissionsDefaultGroups = [
    '*',
    'user',
    'autoconfirmed',
    'bot',
    'sysop',
    'bureaucrat'
];

// Additional rights that can be assigned
$wgManageWikiPermissionsAdditionalRights = [
    'createwiki',
    'managewiki',
    'requestwiki'
];

// ============================================================================
// PERMISSIONS CONFIGURATION
// ============================================================================

// Allow bureaucrats to create and manage wikis
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;

// Allow autoconfirmed users to request wikis
// (requires 4 days and 10 edits by default)
$wgGroupPermissions['user']['requestwiki'] = false;
$wgGroupPermissions['autoconfirmed']['requestwiki'] = true;

// Autoconfirmed user settings
$wgAutoConfirmAge = 345600; // 4 days in seconds
$wgAutoConfirmCount = 10; // 10 edits

// Optional: Create a custom "Wiki Creator" group
$wgGroupPermissions['wikicreator']['createwiki'] = true;
$wgGroupPermissions['wikicreator']['managewiki'] = true;
$wgAddGroups['bureaucrat'][] = 'wikicreator';
$wgRemoveGroups['bureaucrat'][] = 'wikicreator';

// ============================================================================
// LOAD CREATEWIKI SETUP (MUST BE LAST)
// ============================================================================

// This file loads wiki-specific configuration based on the current database
// IMPORTANT: Must be loaded after extension configuration
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";

// Optional: Auto-detect wiki database from subdomain
// Uncomment if using subdomain-based wiki URLs
// autoDetectWikiDatabase();

// ============================================================================
// OPTIONAL: ADDITIONAL CONFIGURATION
// ============================================================================

// Logging
$wgDebugLogGroups['CreateWiki'] = '/var/log/mediawiki/createwiki.log';
$wgDebugLogGroups['ManageWiki'] = '/var/log/mediawiki/managewiki.log';

// Performance
$wgJobRunRate = 0; // Run jobs via cron instead of web requests

// Security
$wgShowExceptionDetails = false;
$wgShowSQLErrors = false;

// Caching (if Redis is available)
// $wgObjectCaches['redis'] = [
//     'class' => 'RedisBagOStuff',
//     'servers' => [ '127.0.0.1:6379' ],
// ];
// $wgMainCacheType = 'redis';

// Email configuration (SMTP)
// $wgSMTP = [
//     'host' => 'mail.example.com',
//     'IDHost' => 'example.com',
//     'port' => 587,
//     'auth' => true,
//     'username' => 'noreply@example.com',
//     'password' => 'your-password'
// ];

// ============================================================================
// END OF CONFIGURATION
// ============================================================================
