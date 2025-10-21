# WikiFarmV2 - CreateWikiV2 & ManageWikiV2

A complete MediaWiki wiki farm solution with extensions for creating and managing multiple wikis from a single installation. Inspired by Miraheze's CreateWiki and ManageWiki extensions, fully compatible with shared hosting environments (no root access required) and VPS/dedicated servers.

## Features

### CreateWikiV2 Extension

- **Easy Wiki Creation**: Create new wikis with a simple form
- **Wiki Request System**: Allow users to request wikis with approval workflow
- **Request Queue Management**: Review, approve, or decline wiki requests
- **Automated Setup**: Automatically creates databases and populates tables
- **Category System**: Organize wikis by category (Community, Education, Gaming, etc.)
- **Private Wikis**: Support for private wiki creation
- **Shared Hosting Compatible**: Works on shared hosting without root access
- **Activity Tracking**: Track wiki creation, closure, and inactivity

### ManageWikiV2 Extension

- **Extension Management**: Enable/disable extensions per wiki
- **Namespace Management**: Create and configure custom namespaces
- **Settings Management**: Configure wiki-specific settings
- **Permission Management**: Manage user groups and permissions per wiki
- **Centralized Control**: Manage all wikis from a single interface

## Requirements

- MediaWiki 1.39.0 or later
- PHP 7.4 or later
- MySQL 5.7+ or MariaDB 10.3+
- Write access to a cache directory (typically `/tmp`)
- SSH access (for installation)
- Database creation privileges (or pre-created databases)

### Shared Hosting Compatibility

This extension is designed to work on shared hosting with the following:
- No root/sudo access required
- Works with standard cPanel/Plesk hosting
- Database creation through hosting control panel
- File permissions managed through FTP/SSH
- No system-level dependencies required

## Installation

### Quick Install

1. Clone or download this repository to your MediaWiki installation:
   ```bash
   cd /path/to/mediawiki
   git clone https://github.com/Xaloria/WikiFarmV2.git temp
   mv temp/extensions/CreateWikiV2 extensions/
   mv temp/extensions/ManageWikiV2 extensions/
   rm -rf temp
   ```

2. Run the installation script:
   ```bash
   bash install.sh
   ```

3. Follow the on-screen instructions

### Manual Installation

See [docs/INSTALL.md](docs/INSTALL.md) for detailed manual installation instructions.

## Configuration

### Basic Configuration in LocalSettings.php

```php
# Load CreateWikiV2
wfLoadExtension( 'CreateWikiV2' );
$wgCreateWikiDatabase = 'createwiki'; // Database for wiki registry
$wgCreateWikiCacheDirectory = '/tmp/createwiki';
$wgCreateWikiSubdomain = 'your-domain.com';
$wgCreateWikiUseCategories = true;
$wgCreateWikiUsePrivateWikis = true;

# Load ManageWikiV2
wfLoadExtension( 'ManageWikiV2' );

# Load CreateWiki setup (must be after extension loading)
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";
```

### Advanced Configuration

See [docs/CONFIGURATION.md](docs/CONFIGURATION.md) for advanced configuration options.

## Usage

### For Administrators

#### Creating a Wiki Directly

1. Navigate to `Special:CreateWiki`
2. Fill in the form:
   - Database name (lowercase, alphanumeric)
   - Site name
   - Language
   - Category
   - Private wiki option (if enabled)
3. Click "Create Wiki"

#### Managing Wiki Requests

1. Navigate to `Special:RequestWikiQueue`
2. View pending requests
3. Click on a request to view details
4. Approve or decline with optional comments

#### Managing Wikis

1. Navigate to `Special:ManageWiki`
2. Choose what to manage:
   - Extensions: Enable/disable extensions
   - Namespaces: Create custom namespaces
   - Settings: Configure wiki settings
   - Permissions: Manage user groups

### For Users

#### Requesting a Wiki

1. Navigate to `Special:RequestWiki`
2. Fill in the request form:
   - Desired database name
   - Site name
   - Language and category
   - Reason for the wiki
3. Submit the request
4. Wait for approval from administrators

## Permissions

### User Rights

- `createwiki` - Allows creating wikis directly
- `requestwiki` - Allows requesting wikis
- `managewiki` - Allows managing wiki settings

### Default Configuration

```php
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;
$wgGroupPermissions['user']['requestwiki'] = true;
```

## File Structure

```
WikiFarmV2/
├── extensions/
│   ├── CreateWikiV2/
│   │   ├── extension.json
│   │   ├── CreateWiki.alias.php
│   │   ├── SetupCreateWiki.php
│   │   ├── includes/
│   │   │   ├── MirahezeFunctions.php
│   │   │   ├── CreateWikiHooks.php
│   │   │   ├── CreateWikiJson.php
│   │   │   ├── RequestWiki.php
│   │   │   └── specials/
│   │   │       ├── SpecialCreateWiki.php
│   │   │       ├── SpecialRequestWiki.php
│   │   │       └── SpecialRequestWikiQueue.php
│   │   ├── i18n/
│   │   └── sql/
│   └── ManageWikiV2/
│       ├── extension.json
│       ├── ManageWiki.alias.php
│       ├── includes/
│       │   ├── ManageWikiHooks.php
│       │   ├── ManageWikiExtensions.php
│       │   ├── ManageWikiNamespaces.php
│       │   ├── ManageWikiSettings.php
│       │   ├── ManageWikiPermissions.php
│       │   └── specials/
│       ├── i18n/
│       └── sql/
├── docs/
│   ├── INSTALL.md
│   ├── CONFIGURATION.md
│   └── USAGE.md
├── install.sh
└── README.md
```

## Database Schema

### CreateWiki Tables

- `cw_wikis` - Wiki registry
- `cw_requests` - Wiki creation requests
- `cw_comments` - Comments on requests

### ManageWiki Tables

- `mw_namespaces` - Custom namespace definitions
- `mw_permissions` - Custom permission configurations

## Shared Hosting Setup

### Requirements

- Access to database management (PHPMyAdmin, etc.)
- SSH or FTP access for file uploads
- PHP CLI access (or cron job capability)

### Setup Steps

1. Upload files via FTP or Git
2. Create database for CreateWiki registry
3. Update LocalSettings.php with configuration
4. Run `php maintenance/update.php` via SSH or scheduled task
5. Set proper file permissions (755 for directories, 644 for files)

## VPS/Dedicated Server Setup

Same as shared hosting, but with additional options:
- Run as system service
- Use cron jobs for maintenance
- Configure nginx/Apache virtual hosts per wiki
- Use separate databases per wiki

## Troubleshooting

### Database Creation Fails

- Ensure user has CREATE DATABASE privilege
- Check database name format (lowercase, alphanumeric)
- Verify connection credentials

### Cache Directory Errors

- Ensure `/tmp/createwiki` is writable
- Change `$wgCreateWikiCacheDirectory` to writable location
- Check file permissions

### Extension Not Loading

- Run `php maintenance/update.php`
- Check for PHP errors in error log
- Verify extension.json syntax

## Support

- GitHub Issues: https://github.com/Xaloria/WikiFarmV2/issues
- Documentation: https://github.com/Xaloria/WikiFarmV2/tree/main/docs

## License

GPL-3.0-or-later

## Credits

Inspired by Miraheze's CreateWiki and ManageWiki extensions.
Developed for WikiFarmV2 project.

## Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Changelog

### Version 2.0.0
- Initial release
- CreateWikiV2 with wiki creation and request system
- ManageWikiV2 with extensions, namespaces, settings, and permissions management
- Shared hosting compatibility
- Full documentation and installation script