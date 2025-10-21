# Configuration Guide

This document covers advanced configuration options for CreateWikiV2 and ManageWikiV2 extensions.

## CreateWikiV2 Configuration

### Basic Settings

```php
# Database for CreateWiki tables
$wgCreateWikiDatabase = 'createwiki';

# Enable database clusters (for large installations)
$wgCreateWikiDatabaseClusters = false;

# Cache directory (must be writable)
$wgCreateWikiCacheDirectory = '/tmp/createwiki';

# Your domain for wiki URLs
$wgCreateWikiSubdomain = 'example.com';
```

### Wiki Categories

Enable and configure wiki categories:

```php
$wgCreateWikiUseCategories = true;

$wgCreateWikiCategories = [
    'uncategorised' => 'Uncategorised',
    'community' => 'Community',
    'education' => 'Education',
    'entertainment' => 'Entertainment',
    'technology' => 'Technology',
    'gaming' => 'Gaming',
    'wiki' => 'Wiki',
    'personal' => 'Personal',
    'business' => 'Business'
];
```

### Private and Closed Wikis

```php
# Allow private wiki creation
$wgCreateWikiUsePrivateWikis = true;

# Enable closed wiki status
$wgCreateWikiUseClosedWikis = true;

# Track inactive wikis
$wgCreateWikiUseInactiveWikis = true;

# Show biographical option (for privacy)
$wgCreateWikiShowBiographicalOption = true;
```

### SQL Files Location

```php
# Path to SQL schema files
$wgCreateWikiSQLfiles = "$IP/extensions/CreateWikiV2/sql";
```

## ManageWikiV2 Configuration

### Extension Management

Configure which extensions can be managed:

```php
$wgManageWikiExtensions = [
    'Cite' => [
        'name' => 'Cite',
        'linkPage' => 'https://www.mediawiki.org/wiki/Extension:Cite',
        'var' => 'wmgUseCite',
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
    // Add more extensions...
];

# Extensions enabled by default for new wikis
$wgManageWikiExtensionsDefault = [
    'Cite',
    'ParserFunctions',
    'WikiEditor'
];
```

### Settings Management

Configure which settings can be managed per wiki:

```php
$wgManageWikiSettings = [
    'wgSitename' => [
        'name' => 'Site name',
        'type' => 'text',
        'help' => 'The name of your wiki',
        'section' => 'general'
    ],
    'wgDefaultSkin' => [
        'name' => 'Default skin',
        'type' => 'select',
        'options' => [
            'vector' => 'Vector',
            'monobook' => 'MonoBook',
            'timeless' => 'Timeless'
        ],
        'help' => 'Default skin for users',
        'section' => 'appearance'
    ],
    'wgEnableUploads' => [
        'name' => 'Enable file uploads',
        'type' => 'check',
        'help' => 'Allow users to upload files',
        'section' => 'uploads'
    ],
    // Add more settings...
];
```

### Permission Management

```php
# Default user groups available on all wikis
$wgManageWikiPermissionsDefaultGroups = [
    '*',
    'user',
    'autoconfirmed',
    'bot',
    'sysop',
    'bureaucrat'
];

# Additional rights that can be assigned
$wgManageWikiPermissionsAdditionalRights = [
    'createwiki',
    'managewiki',
    'requestwiki'
];
```

## Advanced Features

### Subdomain-Based Wiki Detection

Automatically detect which wiki to load based on subdomain:

```php
# In SetupCreateWiki.php or LocalSettings.php
autoDetectWikiDatabase();
```

This enables URLs like:
- `mywiki.example.com` → loads `mywiki` database
- `another.example.com` → loads `another` database

### Wiki Farm Configuration

For managing multiple wikis:

```php
# Use wiki configuration array
$wgConf = new SiteConfiguration;
$wgConf->wikis = MirahezeFunctions::getWikiList();

# Per-wiki settings
$wgConf->settings = [
    'wgSitename' => [
        'default' => 'My Wiki',
        'mywiki' => 'My Wiki',
        'otherwiki' => 'Other Wiki'
    ],
    'wgLanguageCode' => [
        'default' => 'en',
        'mywiki' => 'en',
        'frwiki' => 'fr'
    ]
];

# Extract settings for current wiki
$wgConf->extractAllGlobals( $wgDBname );
```

### Database Cluster Configuration

For large installations with multiple database servers:

```php
$wgCreateWikiDatabaseClusters = true;

$wgLBFactoryConf = [
    'class' => 'LBFactoryMulti',
    'sectionsByDB' => [
        'createwiki' => 'DEFAULT',
        'mywiki' => 'cluster1',
        'otherwiki' => 'cluster1'
    ],
    'sectionLoads' => [
        'DEFAULT' => [
            'db1' => 1
        ],
        'cluster1' => [
            'db2' => 1,
            'db3' => 1
        ]
    ],
    'serverTemplate' => [
        'dbname' => $wgDBname,
        'user' => $wgDBuser,
        'password' => $wgDBpassword,
        'type' => 'mysql',
        'flags' => DBO_DEFAULT
    ],
    'hostsByName' => [
        'db1' => 'db1.example.com',
        'db2' => 'db2.example.com',
        'db3' => 'db3.example.com'
    ]
];
```

## Permission Configuration

### Standard Permissions

```php
# Allow bureaucrats to create wikis
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;

# Allow all users to request wikis
$wgGroupPermissions['user']['requestwiki'] = true;

# Create custom wiki admin group
$wgGroupPermissions['wikiadmin']['createwiki'] = true;
$wgGroupPermissions['wikiadmin']['managewiki'] = true;
$wgAddGroups['bureaucrat'][] = 'wikiadmin';
$wgRemoveGroups['bureaucrat'][] = 'wikiadmin';
```

### Request System Permissions

```php
# Require email confirmation to request wikis
$wgGroupPermissions['user']['requestwiki'] = false;
$wgGroupPermissions['emailconfirmed']['requestwiki'] = true;

# Minimum edits required
$wgGroupPermissions['user']['requestwiki'] = false;
$wgGroupPermissions['autoconfirmed']['requestwiki'] = true;
$wgAutoConfirmAge = 86400 * 4; // 4 days
$wgAutoConfirmCount = 10; // 10 edits
```

## Shared Hosting Specific Configuration

### Cache Directory for Shared Hosting

```php
# Use home directory instead of /tmp
$wgCreateWikiCacheDirectory = '/home/username/cache/createwiki';
```

### Database Prefix (if required)

Some shared hosts require database prefixes:

```php
$wgDBprefix = 'mw_';
```

### Memory Limits

Increase PHP memory limit if needed:

```php
ini_set( 'memory_limit', '256M' );
```

## Production Configuration

### Logging

Enable detailed logging:

```php
$wgDebugLogGroups['CreateWiki'] = '/var/log/mediawiki/createwiki.log';
$wgDebugLogGroups['ManageWiki'] = '/var/log/mediawiki/managewiki.log';
```

### Error Handling

```php
# Hide errors from users
$wgShowExceptionDetails = false;
$wgShowSQLErrors = false;

# Log errors instead
$wgDebugLogFile = '/var/log/mediawiki/debug.log';
```

### Security

```php
# Restrict database creation to specific IPs
$wgCreateWikiAllowedIPs = [ '127.0.0.1', '10.0.0.0/8' ];

# Require approval for all wiki requests
$wgCreateWikiRequireApproval = true;

# Limit wikis per user
$wgCreateWikiMaxWikisPerUser = 5;
```

## Email Configuration

Configure email for notifications:

```php
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmailAuthentication = true;

# SMTP configuration
$wgSMTP = [
    'host' => 'mail.example.com',
    'IDHost' => 'example.com',
    'port' => 587,
    'auth' => true,
    'username' => 'noreply@example.com',
    'password' => 'password'
];

$wgEmergencyContact = 'admin@example.com';
$wgPasswordSender = 'noreply@example.com';
```

## Caching

### File-based Caching

```php
$wgMainCacheType = CACHE_ACCEL;
$wgMemCachedServers = [];
```

### Redis Caching (if available)

```php
$wgObjectCaches['redis'] = [
    'class' => 'RedisBagOStuff',
    'servers' => [ '127.0.0.1:6379' ],
];
$wgMainCacheType = 'redis';
```

## Performance Tuning

### Database Performance

```php
# Connection pooling
$wgDBmwschema = 'mediawiki';
$wgSharedDB = null;

# Query optimization
$wgDBserver = 'localhost';
$wgDBtype = 'mysql';
```

### Job Queue

```php
# Run jobs via cron instead of web requests
$wgJobRunRate = 0;
```

Add to crontab:
```bash
*/5 * * * * php /path/to/mediawiki/maintenance/runJobs.php --wiki=mywiki
```

## Maintenance

### Automated Cleanup

Create maintenance scripts:

```bash
#!/bin/bash
# cleanup-wikis.sh

# Remove inactive wikis (no edits in 180 days)
php maintenance/sql.php --wiki=createwiki <<SQL
UPDATE cw_wikis SET wiki_inactive = 1
WHERE wiki_dbname IN (
    SELECT wiki_dbname FROM (
        SELECT w.wiki_dbname
        FROM cw_wikis w
        WHERE NOT EXISTS (
            SELECT 1 FROM recentchanges
            WHERE rc_timestamp > DATE_SUB(NOW(), INTERVAL 180 DAY)
        )
    ) AS inactive
);
SQL
```

### Backup

```bash
#!/bin/bash
# backup-wikis.sh

# Backup CreateWiki database
mysqldump createwiki > createwiki-$(date +%Y%m%d).sql

# Backup all wikis
for wiki in $(mysql createwiki -e "SELECT wiki_dbname FROM cw_wikis" -sN); do
    mysqldump $wiki > $wiki-$(date +%Y%m%d).sql
done
```

## Example: Complete LocalSettings.php Configuration

```php
<?php
# ... existing MediaWiki configuration ...

# CreateWikiV2 Extension
wfLoadExtension( 'CreateWikiV2' );
$wgCreateWikiDatabase = 'createwiki';
$wgCreateWikiCacheDirectory = '/var/cache/createwiki';
$wgCreateWikiSubdomain = 'wikifarm.com';
$wgCreateWikiUseCategories = true;
$wgCreateWikiUsePrivateWikis = true;

# ManageWikiV2 Extension
wfLoadExtension( 'ManageWikiV2' );

# Permissions
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;
$wgGroupPermissions['autoconfirmed']['requestwiki'] = true;

# Load CreateWiki setup
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";
autoDetectWikiDatabase();
```

## Troubleshooting Configuration

### Debug Configuration Issues

```php
# Enable debug mode
$wgDebugToolbar = true;
$wgShowDebug = true;
$wgDevelopmentWarnings = true;
```

### Test Configuration

Create a test page to verify configuration:

```php
<?php
require_once 'includes/WebStart.php';
echo "CreateWiki Database: " . $wgCreateWikiDatabase . "\n";
echo "Cache Directory: " . $wgCreateWikiCacheDirectory . "\n";
echo "Subdomain: " . $wgCreateWikiSubdomain . "\n";
```

## Further Reading

- MediaWiki Configuration: https://www.mediawiki.org/wiki/Manual:Configuration_settings
- Performance Tuning: https://www.mediawiki.org/wiki/Manual:Performance_tuning
- Security: https://www.mediawiki.org/wiki/Manual:Security
