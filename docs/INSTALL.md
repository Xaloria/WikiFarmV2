# Installation Guide

## Prerequisites

Before installing WikiFarmV2 extensions, ensure you have:

1. **MediaWiki Installation**: MediaWiki 1.39.0 or later
2. **PHP**: Version 7.4 or later
3. **Database**: MySQL 5.7+ or MariaDB 10.3+
4. **Permissions**:
   - SSH or FTP access to your server
   - Database creation privileges (or ability to create databases via control panel)
   - Write access to MediaWiki directories

## Installation Methods

### Method 1: Automated Installation (Recommended)

1. **Clone the repository** (or download and extract):
   ```bash
   cd /path/to/mediawiki
   git clone https://github.com/Xaloria/WikiFarmV2.git temp
   ```

2. **Move extensions to MediaWiki**:
   ```bash
   mv temp/extensions/CreateWikiV2 extensions/
   mv temp/extensions/ManageWikiV2 extensions/
   cp temp/install.sh .
   rm -rf temp
   ```

3. **Run the installation script**:
   ```bash
   chmod +x install.sh
   bash install.sh
   ```

4. **Follow the on-screen instructions**

The script will:
- Detect your MediaWiki installation
- Create necessary directories
- Add configuration to LocalSettings.php
- Run database updates
- Set proper permissions

### Method 2: Manual Installation

#### Step 1: Download and Extract Extensions

```bash
cd /path/to/mediawiki/extensions
git clone https://github.com/Xaloria/WikiFarmV2.git temp
mv temp/extensions/CreateWikiV2 CreateWikiV2
mv temp/extensions/ManageWikiV2 ManageWikiV2
rm -rf temp
```

#### Step 2: Configure LocalSettings.php

Add to your `LocalSettings.php` (at the end of the file):

```php
# CreateWikiV2 Extension
wfLoadExtension( 'CreateWikiV2' );

# CreateWiki Configuration
$wgCreateWikiDatabase = 'createwiki';
$wgCreateWikiDatabaseClusters = false;
$wgCreateWikiUseCategories = true;
$wgCreateWikiCategories = [
    'uncategorised' => 'Uncategorised',
    'community' => 'Community',
    'education' => 'Education',
    'entertainment' => 'Entertainment',
    'technology' => 'Technology',
    'gaming' => 'Gaming',
    'wiki' => 'Wiki'
];
$wgCreateWikiSQLfiles = "$IP/extensions/CreateWikiV2/sql";
$wgCreateWikiCacheDirectory = '/tmp/createwiki';
$wgCreateWikiSubdomain = 'example.com'; // CHANGE THIS
$wgCreateWikiUsePrivateWikis = true;
$wgCreateWikiUseClosedWikis = true;
$wgCreateWikiUseInactiveWikis = true;

# ManageWikiV2 Extension
wfLoadExtension( 'ManageWikiV2' );

# ManageWiki Configuration
$wgManageWikiPermissionsDefaultGroups = [
    '*',
    'user',
    'autoconfirmed',
    'bot',
    'sysop',
    'bureaucrat'
];

# Load CreateWiki setup (MUST be after extension loading)
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";
```

#### Step 3: Create Cache Directory

```bash
mkdir -p /tmp/createwiki
chmod 755 /tmp/createwiki
```

For shared hosting, you may need to use a directory in your home directory:
```bash
mkdir -p ~/cache/createwiki
chmod 755 ~/cache/createwiki
```

Then update LocalSettings.php:
```php
$wgCreateWikiCacheDirectory = '/home/username/cache/createwiki';
```

#### Step 4: Run Database Updates

```bash
cd /path/to/mediawiki
php maintenance/update.php --quick
```

This creates the required database tables:
- `cw_wikis` - Wiki registry
- `cw_requests` - Wiki creation requests
- `cw_comments` - Comments on requests
- `mw_namespaces` - Custom namespaces
- `mw_permissions` - Custom permissions

#### Step 5: Set Permissions

Grant appropriate permissions to users:

```php
# In LocalSettings.php, add:
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;
$wgGroupPermissions['user']['requestwiki'] = true;
```

Or use Special:UserRights to grant permissions to specific users.

## Shared Hosting Specific Instructions

### Using cPanel

1. **Upload Files**:
   - Use File Manager or FTP client
   - Upload to `/home/username/public_html/extensions/`

2. **Create Database**:
   - Go to MySQL Databases in cPanel
   - Create database named `username_createwiki`
   - Update LocalSettings.php with full database name

3. **Run update.php**:
   - Use Terminal in cPanel, or
   - Create a cron job to run: `php /home/username/public_html/maintenance/update.php`
   - Or use web-based installer if available

4. **Set Directory Permissions**:
   - Use File Manager or FTP client
   - Set directories to 755
   - Set files to 644
   - Set cache directory to 777 (or 755 if using suPHP)

### Using Plesk

1. **Upload Files**: Use File Manager or FTP
2. **Create Database**: Use Databases section
3. **Run Commands**: Use SSH or Scheduled Tasks
4. **Permissions**: Use File Manager permissions tool

### Without SSH Access

If you don't have SSH access:

1. Upload files via FTP
2. Edit LocalSettings.php via FTP or File Manager
3. Create a temporary PHP file to run update.php:

Create `run-update.php` in MediaWiki root:
```php
<?php
define( 'MW_CONFIG_CALLBACK', 'efRunUpdate' );
function efRunUpdate() {
    require __DIR__ . '/maintenance/update.php';
}
require __DIR__ . '/maintenance/doMaintenance.php';
```

Access via browser: `https://yourwiki.com/run-update.php`

**Important**: Delete this file immediately after use!

## Verification

After installation, verify everything works:

1. **Check Extension Loading**:
   - Go to `Special:Version`
   - Look for CreateWikiV2 and ManageWikiV2

2. **Check Database Tables**:
   ```sql
   SHOW TABLES LIKE 'cw_%';
   SHOW TABLES LIKE 'mw_%';
   ```

3. **Check Special Pages**:
   - `Special:CreateWiki`
   - `Special:RequestWiki`
   - `Special:RequestWikiQueue`
   - `Special:ManageWiki`

4. **Check Permissions**:
   - Try accessing each special page
   - Verify appropriate permissions are enforced

## Troubleshooting

### "Extension not found" error

- Verify extension files are in correct location
- Check file permissions
- Ensure `wfLoadExtension` is called correctly

### Database tables not created

- Run `php maintenance/update.php` manually
- Check for SQL errors in MediaWiki debug log
- Verify database user has CREATE TABLE privilege

### Cache directory errors

- Ensure directory exists and is writable
- Check SELinux settings (on some Linux servers)
- Try using a different directory path

### Permission denied errors

- Check file ownership (should match web server user)
- Verify directory permissions (755 for directories)
- Check suPHP/PHP-FPM configuration

## Next Steps

After successful installation:

1. Read [CONFIGURATION.md](CONFIGURATION.md) for advanced configuration
2. Read [USAGE.md](USAGE.md) for usage instructions
3. Create your first wiki or test the request system
4. Configure wiki categories and settings

## Updating

To update the extensions:

1. Backup your LocalSettings.php
2. Backup your database
3. Pull latest changes:
   ```bash
   cd extensions/CreateWikiV2
   git pull
   cd ../ManageWikiV2
   git pull
   ```
4. Run update script:
   ```bash
   php maintenance/update.php
   ```

## Uninstalling

To uninstall:

1. Remove extensions from LocalSettings.php
2. Remove extension directories
3. Optionally drop database tables:
   ```sql
   DROP TABLE cw_wikis, cw_requests, cw_comments, mw_namespaces, mw_permissions;
   ```
4. Remove cache directory
