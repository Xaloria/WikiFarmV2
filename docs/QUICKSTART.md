# Quick Start Guide

Get WikiFarmV2 up and running in 10 minutes!

## Prerequisites

- MediaWiki 1.39+ installed and working
- SSH or FTP access to your server
- Database creation privileges

## Installation (5 minutes)

### Step 1: Clone Repository

```bash
cd /path/to/mediawiki
git clone https://github.com/Xaloria/WikiFarmV2.git temp
mv temp/extensions/CreateWikiV2 extensions/
mv temp/extensions/ManageWikiV2 extensions/
cp temp/install.sh .
rm -rf temp
```

### Step 2: Run Installation Script

```bash
chmod +x install.sh
bash install.sh
```

The script will:
- Detect your MediaWiki installation
- Create cache directory
- Update LocalSettings.php
- Run database updates

### Step 3: Configure Your Domain

Edit `LocalSettings.php` and change:

```php
$wgCreateWikiSubdomain = 'your-actual-domain.com';
```

## First Wiki Creation (2 minutes)

### Option A: Create Directly (as admin)

1. Go to `Special:CreateWiki`
2. Fill in:
   - Database name: `myfirstwiki`
   - Site name: "My First Wiki"
   - Language: English
   - Category: Community
3. Click "Create Wiki"
4. Done! Wiki is live at `https://myfirstwiki.your-domain.com`

### Option B: Request and Approve

**As a user:**
1. Go to `Special:RequestWiki`
2. Fill in the form with reason
3. Submit request
4. Note your request ID

**As an admin:**
1. Go to `Special:RequestWikiQueue`
2. Click on the request
3. Review and click "Approve"
4. Wiki is created!

## Basic Management (3 minutes)

### Enable Extensions for Your Wiki

1. Go to `Special:ManageWiki`
2. Click "Manage Extensions"
3. Check extensions you want (e.g., VisualEditor, Scribunto)
4. Click "Save"

### Configure Wiki Settings

1. Go to `Special:ManageWiki`
2. Click "Manage Settings"
3. Update:
   - Site name
   - Logo URL
   - Enable uploads
   - Copyright information
4. Click "Save"

### Create Custom Namespace

1. Go to `Special:ManageWiki`
2. Click "Manage Namespaces"
3. Click "Create namespace"
4. Fill in:
   - ID: 3000 (or higher, must be even)
   - Name: "Portal"
   - Check "Searchable" and "Subpages"
5. Click "Create"

## Quick Configuration Examples

### Enable Private Wikis

In `LocalSettings.php`:
```php
$wgCreateWikiUsePrivateWikis = true;
```

### Add Custom Categories

In `LocalSettings.php`:
```php
$wgCreateWikiCategories = [
    'uncategorised' => 'Uncategorised',
    'community' => 'Community',
    'education' => 'Education',
    'gaming' => 'Gaming',
    'mytype' => 'My Custom Type'  // Add your own!
];
```

### Change Permissions

In `LocalSettings.php`:
```php
// Allow all users to request wikis (not just autoconfirmed)
$wgGroupPermissions['user']['requestwiki'] = true;

// Create custom wiki admin group
$wgGroupPermissions['wikiadmin']['createwiki'] = true;
$wgGroupPermissions['wikiadmin']['managewiki'] = true;
```

## Common Tasks

### Grant User Wiki Creation Rights

```bash
php maintenance/createAndPromote.php WikiCreator password123 --bureaucrat
```

Then in MediaWiki:
1. Go to `Special:UserRights`
2. Select the user
3. Check "bureaucrat" group
4. Save

### List All Wikis

```bash
mysql -e "SELECT wiki_dbname, wiki_sitename FROM createwiki.cw_wikis;"
```

### Check Pending Requests

```bash
mysql -e "SELECT * FROM createwiki.cw_requests WHERE cw_status='pending';"
```

### Backup All Wikis

```bash
#!/bin/bash
for wiki in $(mysql createwiki -sN -e "SELECT wiki_dbname FROM cw_wikis"); do
    mysqldump $wiki > backup-$wiki-$(date +%Y%m%d).sql
done
```

## Troubleshooting Quick Fixes

### "Permission denied" accessing special pages

```bash
# Grant yourself createwiki permission
php maintenance/createAndPromote.php YourUsername --bureaucrat
```

### Database tables not created

```bash
cd /path/to/mediawiki
php maintenance/update.php --quick
```

### Cache directory not writable

```bash
mkdir -p /tmp/createwiki
chmod 755 /tmp/createwiki
# Or change in LocalSettings.php:
# $wgCreateWikiCacheDirectory = '/home/username/cache/createwiki';
```

### Wiki creation fails

Check database user has CREATE DATABASE privilege:
```sql
GRANT ALL PRIVILEGES ON *.* TO 'wiki_user'@'localhost';
FLUSH PRIVILEGES;
```

## Next Steps

Now that you're up and running:

1. **Read the full docs**:
   - [Installation Guide](INSTALL.md) - Detailed installation
   - [Configuration Guide](CONFIGURATION.md) - Advanced options
   - [Usage Guide](USAGE.md) - Complete feature guide

2. **Customize your setup**:
   - Configure email notifications
   - Set up subdomain routing
   - Add more manageable extensions
   - Create wiki categories

3. **Set up maintenance**:
   - Schedule database backups
   - Monitor inactive wikis
   - Set up log rotation
   - Configure automated cleanup

4. **Create policies**:
   - Wiki request approval criteria
   - Naming conventions
   - Resource limits
   - Terms of service

## Quick Reference

### Special Pages

| Page | Purpose | Permission |
|------|---------|------------|
| Special:CreateWiki | Create wikis directly | createwiki |
| Special:RequestWiki | Request a wiki | requestwiki |
| Special:RequestWikiQueue | Manage requests | createwiki |
| Special:ManageWiki | Main management hub | managewiki |
| Special:ManageWikiExtensions | Enable/disable extensions | managewiki |
| Special:ManageWikiNamespaces | Custom namespaces | managewiki |
| Special:ManageWikiSettings | Wiki settings | managewiki |
| Special:ManageWikiPermissions | User permissions | managewiki |

### Configuration Variables

| Variable | Description | Default |
|----------|-------------|---------|
| $wgCreateWikiDatabase | Registry database | 'createwiki' |
| $wgCreateWikiCacheDirectory | Cache location | '/tmp/createwiki' |
| $wgCreateWikiSubdomain | Your domain | 'example.com' |
| $wgCreateWikiUseCategories | Enable categories | true |
| $wgCreateWikiUsePrivateWikis | Allow private wikis | true |

### Command Line Tools

```bash
# Run database updates
php maintenance/update.php

# Create admin user
php maintenance/createAndPromote.php AdminName password123 --bureaucrat

# Run jobs
php maintenance/runJobs.php

# Check wiki list
php maintenance/sql.php --wiki=createwiki -e "SELECT * FROM cw_wikis;"

# Export wikis to JSON
php extensions/CreateWikiV2/maintenance/exportWikis.php
```

## Get Help

- **Documentation**: Check docs/ folder
- **Issues**: https://github.com/Xaloria/WikiFarmV2/issues
- **MediaWiki Docs**: https://www.mediawiki.org/

## Tips

1. **Test on staging first**: Don't test wiki creation on production
2. **Backup regularly**: Before any major changes
3. **Monitor logs**: Check MediaWiki debug logs
4. **Read permissions**: Understand who can do what
5. **Document changes**: Keep track of configuration changes

## Example: Complete Setup from Scratch

```bash
# 1. Install MediaWiki (if not already)
wget https://releases.wikimedia.org/mediawiki/1.39/mediawiki-1.39.0.tar.gz
tar -xzf mediawiki-1.39.0.tar.gz
mv mediawiki-1.39.0 /var/www/html/wiki

# 2. Run MediaWiki installer
cd /var/www/html/wiki
php maintenance/install.php \
  --dbname=wikidb \
  --dbserver=localhost \
  --dbuser=root \
  --dbpass=password \
  --pass=admin123 \
  "My Wiki Farm" \
  "Admin"

# 3. Install WikiFarmV2
git clone https://github.com/Xaloria/WikiFarmV2.git temp
mv temp/extensions/CreateWikiV2 extensions/
mv temp/extensions/ManageWikiV2 extensions/
rm -rf temp

# 4. Configure
cat >> LocalSettings.php << 'EOF'
wfLoadExtension( 'CreateWikiV2' );
wfLoadExtension( 'ManageWikiV2' );
$wgCreateWikiDatabase = 'createwiki';
$wgCreateWikiCacheDirectory = '/tmp/createwiki';
$wgCreateWikiSubdomain = 'wikifarm.local';
$wgGroupPermissions['bureaucrat']['createwiki'] = true;
$wgGroupPermissions['bureaucrat']['managewiki'] = true;
$wgGroupPermissions['user']['requestwiki'] = true;
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";
EOF

# 5. Create cache directory
mkdir -p /tmp/createwiki
chmod 755 /tmp/createwiki

# 6. Run updates
php maintenance/update.php

# 7. Test
php maintenance/eval.php
> $db = MirahezeFunctions::getDB();
> echo $db ? "Works!" : "Failed!";

# 8. Access in browser
# http://localhost/wiki/Special:CreateWiki
```

You're done! Start creating wikis! 🎉

## Success Checklist

- [ ] Extensions show in Special:Version
- [ ] Database tables created (check with `SHOW TABLES FROM createwiki;`)
- [ ] Cache directory exists and is writable
- [ ] Can access Special:CreateWiki
- [ ] Can create a test wiki
- [ ] Test wiki accessible (if subdomain configured)
- [ ] Can request a wiki as regular user
- [ ] Can approve request as admin
- [ ] ManageWiki pages work

If all checks pass, you're ready to use WikiFarmV2 in production!
