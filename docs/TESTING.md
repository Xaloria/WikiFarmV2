# Testing Guide

This guide explains how to test WikiFarmV2 extensions.

## Prerequisites for Testing

- MediaWiki 1.39.0+ installed
- PHP 7.4+ with required extensions
- MySQL 5.7+ or MariaDB 10.3+
- Write access to file system
- Database creation privileges

## Setting Up Test Environment

### 1. Install MediaWiki

```bash
# Download MediaWiki
wget https://releases.wikimedia.org/mediawiki/1.39/mediawiki-1.39.0.tar.gz
tar -xzf mediawiki-1.39.0.tar.gz
mv mediawiki-1.39.0 /var/www/html/mediawiki

# Complete web installer or use command line:
php maintenance/install.php \
  --dbname=mediawiki \
  --dbserver=localhost \
  --dbuser=root \
  --dbpass=password \
  --pass=admin123 \
  "Test Wiki" \
  "Admin"
```

### 2. Install WikiFarmV2 Extensions

```bash
cd /var/www/html/mediawiki
git clone https://github.com/Xaloria/WikiFarmV2.git temp
mv temp/extensions/CreateWikiV2 extensions/
mv temp/extensions/ManageWikiV2 extensions/
rm -rf temp

# Run installation
bash install.sh
```

### 3. Run Database Updates

```bash
php maintenance/update.php
```

## Test Categories

### Unit Tests (Core Functions)

Test individual functions in isolation.

#### Test MirahezeFunctions

```php
// Test in maintenance/eval.php
php maintenance/eval.php

// Test database connection
$db = MirahezeFunctions::getDB();
echo $db ? "✓ DB connection works\n" : "✗ DB connection failed\n";

// Test sanitization
$result = MirahezeFunctions::sanitizeDBname( "Test-Wiki_123!" );
echo $result === "testwiki_123" ? "✓ Sanitization works\n" : "✗ Sanitization failed\n";

// Test wiki existence check
$exists = MirahezeFunctions::wikiExists( 'nonexistent' );
echo !$exists ? "✓ Wiki existence check works\n" : "✗ Check failed\n";

// Test URL generation
$url = MirahezeFunctions::generateWikiURL( 'testwiki' );
echo strpos($url, 'testwiki') !== false ? "✓ URL generation works\n" : "✗ URL generation failed\n";
```

#### Test CreateWiki Functions

```bash
php maintenance/eval.php
```

```php
// Test wiki creation
$success = MirahezeFunctions::createWikiDB( 'test_wiki_db' );
echo $success ? "✓ Database creation works\n" : "✗ Database creation failed\n";

// Test wiki registration
$data = [
    'dbname' => 'test_wiki_db',
    'sitename' => 'Test Wiki',
    'language' => 'en',
    'category' => 'community',
    'url' => 'https://test.example.com'
];
$result = MirahezeFunctions::addWiki( $data );
echo $result ? "✓ Wiki registration works\n" : "✗ Wiki registration failed\n";

// Test wiki info retrieval
$info = MirahezeFunctions::getWikiInfo( 'test_wiki_db' );
echo $info && $info['sitename'] === 'Test Wiki' ? "✓ Wiki info retrieval works\n" : "✗ Retrieval failed\n";

// Cleanup
MirahezeFunctions::deleteWiki( 'test_wiki_db', true );
```

### Integration Tests (Workflows)

Test complete workflows.

#### Test Wiki Creation Workflow

1. **Access Special:CreateWiki**
   - Go to `http://yourwiki.local/wiki/Special:CreateWiki`
   - Verify form loads
   - Check all fields are present

2. **Fill in form**
   - Database name: `testwiki1`
   - Site name: "Test Wiki 1"
   - Language: English
   - Category: Community
   - Submit

3. **Verify creation**
   - Check for success message
   - Verify database exists:
     ```sql
     SHOW DATABASES LIKE 'testwiki1';
     ```
   - Check wiki registry:
     ```sql
     SELECT * FROM createwiki.cw_wikis WHERE wiki_dbname = 'testwiki1';
     ```
   - Try accessing the wiki (if subdomain configured)

#### Test Wiki Request Workflow

1. **Submit request** (as regular user)
   - Go to `Special:RequestWiki`
   - Fill in form
   - Database name: `requestedwiki`
   - Site name: "Requested Wiki"
   - Reason: "Testing the request system"
   - Submit

2. **View in queue** (as admin)
   - Go to `Special:RequestWikiQueue`
   - Verify request appears in list
   - Click to view details
   - Verify all information is correct

3. **Approve request**
   - Add comment: "Approved for testing"
   - Click "Approve"
   - Verify success message
   - Check wiki was created

4. **Test decline** (create another request first)
   - Submit another request
   - Decline with reason
   - Verify status changes to declined

#### Test Extension Management

1. **Access ManageWiki**
   - Go to `Special:ManageWiki`
   - Click "Manage Extensions"

2. **Enable extension**
   - Select wiki from list
   - Check "VisualEditor"
   - Save

3. **Verify extension enabled**
   - Check wiki settings in database
   - Visit wiki and verify extension works

4. **Disable extension**
   - Uncheck "VisualEditor"
   - Save
   - Verify extension disabled

### UI Tests

Test user interface elements.

#### Test Forms

1. **CreateWiki form**
   - All fields present
   - Required fields marked
   - Help text displays
   - Validation works
   - Error messages clear

2. **RequestWiki form**
   - Information box displays
   - All fields work
   - Textarea for reason
   - Submit button works

3. **Request queue**
   - Table displays correctly
   - Sorting works (if implemented)
   - Links work
   - Forms submit correctly

#### Test Special Pages

Visit each special page and check:
- Page loads without errors
- Title is correct
- Content displays properly
- Forms work (if present)
- Links are valid
- Permissions enforced

Special pages to test:
- Special:CreateWiki
- Special:RequestWiki
- Special:RequestWikiQueue
- Special:ManageWiki
- Special:ManageWikiExtensions
- Special:ManageWikiNamespaces
- Special:ManageWikiSettings
- Special:ManageWikiPermissions

### Permission Tests

Test permission enforcement.

#### Test CreateWiki Permission

```php
// In maintenance/eval.php
$user = User::newFromName( 'TestUser' );
$title = Title::newFromText( 'Special:CreateWiki' );

// Without permission
echo $title->userCan( 'createwiki', $user ) ? "✗ Permission not enforced\n" : "✓ Permission enforced\n";

// Grant permission
$user->addGroup( 'bureaucrat' );
echo $title->userCan( 'createwiki', $user ) ? "✓ Permission granted\n" : "✗ Permission not granted\n";
```

#### Test Request Permission

Test that:
- Anonymous users cannot request wikis (unless configured)
- Regular users can request wikis
- Autoconfirmed users can request wikis

### Database Tests

Test database integrity.

#### Schema Tests

```sql
-- Verify tables exist
SHOW TABLES FROM createwiki LIKE 'cw_%';
-- Expected: cw_wikis, cw_requests, cw_comments

SHOW TABLES FROM createwiki LIKE 'mw_%';
-- Expected: mw_namespaces, mw_permissions

-- Verify table structure
DESCRIBE createwiki.cw_wikis;
DESCRIBE createwiki.cw_requests;
DESCRIBE createwiki.cw_comments;
DESCRIBE createwiki.mw_namespaces;
DESCRIBE createwiki.mw_permissions;
```

#### Data Integrity Tests

```sql
-- Test foreign key relationships (if any)
-- Test cascade deletes
-- Test unique constraints
-- Test default values

-- Example: Test wiki deletion doesn't orphan data
INSERT INTO cw_wikis (wiki_dbname, wiki_sitename, wiki_language, wiki_url, wiki_creation)
VALUES ('test_orphan', 'Test', 'en', 'http://test.com', NOW());

INSERT INTO cw_requests (cw_dbname, cw_sitename, cw_language, cw_user, cw_reason, cw_timestamp)
VALUES ('test_orphan', 'Test', 'en', 1, 'Test', NOW());

DELETE FROM cw_wikis WHERE wiki_dbname = 'test_orphan';

-- Check orphaned requests
SELECT * FROM cw_requests WHERE cw_dbname = 'test_orphan';
-- Cleanup if needed
```

### Shared Hosting Tests

Test on actual shared hosting environment.

#### cPanel Tests

1. **Upload via File Manager**
   - Upload extensions
   - Set permissions (755/644)
   - Verify file structure

2. **Database creation**
   - Create database via MySQL Databases
   - Create user
   - Grant privileges
   - Update LocalSettings.php

3. **Run update.php**
   - Via Terminal (if available)
   - Via cron job
   - Via web interface

4. **Verify functionality**
   - Test all features
   - Check error logs
   - Monitor performance

#### Without SSH Tests

1. **Upload via FTP**
2. **Edit LocalSettings.php via FTP**
3. **Create temporary update script**
4. **Run via web browser**
5. **Delete temporary script**
6. **Test all features**

### Performance Tests

Test performance under load.

#### Load Testing

```bash
# Install Apache Bench
apt-get install apache2-utils

# Test special page performance
ab -n 100 -c 10 http://yourwiki.local/wiki/Special:CreateWiki

# Test wiki list
ab -n 100 -c 10 http://yourwiki.local/wiki/Special:RequestWikiQueue
```

#### Database Performance

```sql
-- Test query performance
EXPLAIN SELECT * FROM cw_wikis WHERE wiki_dbname = 'test';
EXPLAIN SELECT * FROM cw_requests WHERE cw_status = 'pending';

-- Check index usage
SHOW INDEX FROM cw_wikis;
SHOW INDEX FROM cw_requests;
```

### Security Tests

Test security measures.

#### SQL Injection Tests

Try injecting SQL in forms:
- Database name: `'; DROP TABLE cw_wikis; --`
- Site name: `<script>alert('xss')</script>`
- Verify input is sanitized

#### XSS Tests

Try injecting scripts:
- In wiki names
- In comments
- In reasons
- Verify output is escaped

#### Permission Bypass Tests

Try accessing pages without permission:
- Access Special:CreateWiki as anonymous user
- Try to approve requests without permission
- Attempt database manipulation without rights

## Automated Testing

### PHPUnit Tests (if implemented)

```bash
# Run all tests
php tests/phpunit/phpunit.php extensions/CreateWikiV2/tests/
php tests/phpunit/phpunit.php extensions/ManageWikiV2/tests/

# Run specific test
php tests/phpunit/phpunit.php extensions/CreateWikiV2/tests/MirahezeFunctionsTest.php
```

### Continuous Integration

Set up CI pipeline (GitHub Actions example):

```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install MediaWiki
        run: |
          # Install MediaWiki
          # Install extensions
          # Run tests
```

## Test Checklist

Use this checklist for complete testing:

### Pre-deployment
- [ ] Fresh MediaWiki installation
- [ ] Extensions install without errors
- [ ] Database tables created
- [ ] Configuration valid

### Core Functionality
- [ ] Create wiki works
- [ ] Request wiki works
- [ ] Approve request works
- [ ] Decline request works
- [ ] Delete wiki works

### ManageWiki
- [ ] Extension management works
- [ ] Namespace management works
- [ ] Settings management works
- [ ] Permission management works

### Permissions
- [ ] Anonymous users blocked from creating wikis
- [ ] Bureaucrats can create wikis
- [ ] Users can request wikis
- [ ] Permissions enforced correctly

### Database
- [ ] All tables created
- [ ] Indexes exist
- [ ] Data integrity maintained
- [ ] No orphaned records

### UI/UX
- [ ] All pages load
- [ ] Forms work
- [ ] Error messages clear
- [ ] Success messages display
- [ ] Navigation works

### Security
- [ ] Input sanitized
- [ ] Output escaped
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Permissions enforced

### Performance
- [ ] Pages load quickly
- [ ] Database queries optimized
- [ ] No memory leaks
- [ ] Handles concurrent requests

### Compatibility
- [ ] Works on shared hosting
- [ ] Works on VPS
- [ ] Works with MySQL
- [ ] Works with MariaDB
- [ ] Works with PHP 7.4+
- [ ] Works with MediaWiki 1.39+

### Documentation
- [ ] README accurate
- [ ] Installation guide works
- [ ] Configuration examples valid
- [ ] Usage guide clear

## Reporting Test Results

When reporting test results:

1. **Environment details**
   - OS and version
   - PHP version
   - MediaWiki version
   - Database type and version
   - Hosting type

2. **Test results**
   - What was tested
   - Expected result
   - Actual result
   - Screenshots (if applicable)

3. **Issues found**
   - Steps to reproduce
   - Error messages
   - Logs

4. **Performance metrics**
   - Page load times
   - Query execution times
   - Memory usage

## Cleaning Up After Tests

```bash
# Drop test databases
mysql -e "DROP DATABASE IF EXISTS test_wiki_db;"
mysql -e "DROP DATABASE IF EXISTS testwiki1;"
mysql -e "DROP DATABASE IF EXISTS requestedwiki;"

# Clean test data
mysql createwiki -e "DELETE FROM cw_wikis WHERE wiki_dbname LIKE 'test%';"
mysql createwiki -e "DELETE FROM cw_requests WHERE cw_dbname LIKE 'test%';"

# Clear cache
rm -rf /tmp/createwiki/*
```

## Conclusion

Thorough testing ensures WikiFarmV2 works reliably in various environments. Always test before deploying to production.
