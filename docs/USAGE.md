# Usage Guide

This guide covers how to use CreateWikiV2 and ManageWikiV2 extensions.

## For Wiki Administrators

### Creating a Wiki (Special:CreateWiki)

1. **Navigate** to `Special:CreateWiki`
2. **Fill in the form**:
   - **Database name**: Internal identifier (e.g., `mywiki`)
     - Must be lowercase
     - Only alphanumeric and underscores
     - Will be sanitized automatically
   - **Site name**: Display name (e.g., "My Wiki")
   - **Language**: Wiki language (default: English)
   - **Category**: Wiki category (Community, Education, etc.)
   - **Private**: Check if wiki should be private
3. **Click** "Create Wiki"
4. **Result**: Wiki is created immediately with:
   - New database with MediaWiki tables
   - Entry in wiki registry
   - Generated URL (e.g., `https://mywiki.example.com`)

**Example**:
- Database name: `gamewiki`
- Site name: "Gaming Wiki"
- Language: English
- Category: Gaming
- Result: `https://gamewiki.example.com`

### Managing Wiki Requests (Special:RequestWikiQueue)

#### Viewing Pending Requests

1. **Navigate** to `Special:RequestWikiQueue`
2. **View** list of pending requests with:
   - Request ID
   - Database name
   - Site name
   - Requester
   - Date submitted
3. **Click** "View" to see details

#### Reviewing a Request

1. **Click** on a request to view full details:
   - Basic information
   - Requester's reason
   - Any comments from other admins
2. **Read** the reason carefully
3. **Check**:
   - Is the wiki needed?
   - Is the requester trustworthy?
   - Does it violate any policies?

#### Approving a Request

1. **View** the request details
2. **Optional**: Add a comment for approval
3. **Click** "Approve"
4. **Result**:
   - Wiki is created automatically
   - Requester is notified (if email configured)
   - Request status changes to "approved"

#### Declining a Request

1. **View** the request details
2. **Enter** reason for declining (required)
3. **Click** "Decline"
4. **Result**:
   - Request status changes to "declined"
   - Requester can see the reason

#### Adding Comments

1. **View** the request details
2. **Enter** comment in the comment field
3. **Click** "Add Comment"
4. **Use for**:
   - Asking for more information
   - Discussing with other admins
   - Providing feedback

### Managing Wiki Extensions (Special:ManageWikiExtensions)

1. **Navigate** to `Special:ManageWikiExtensions`
2. **View** available extensions grouped by section:
   - Parser hooks
   - Editor tools
   - Media handling
   - Other
3. **Enable** extensions:
   - Check box next to extension
   - Click "Save"
4. **Disable** extensions:
   - Uncheck box
   - Click "Save"

**Available Extensions** (default configuration):
- Cite - Citation tools
- CodeEditor - Syntax highlighting for code
- Gadgets - User gadgets
- ImageMap - Clickable images
- InputBox - Input forms
- ParserFunctions - Parser functions
- Scribunto - Lua scripting
- TemplateData - Template documentation
- VisualEditor - Visual editing
- WikiEditor - Enhanced wiki editor

### Managing Namespaces (Special:ManageWikiNamespaces)

#### Creating a Custom Namespace

1. **Navigate** to `Special:ManageWikiNamespaces`
2. **Click** "Create namespace"
3. **Fill in**:
   - **Namespace ID**: Must be >= 3000 and even
   - **Name**: Namespace name (e.g., "Portal")
   - **Searchable**: Include in search results
   - **Subpages**: Enable subpages (recommended)
   - **Content**: Mark as content namespace
   - **Protection**: Required right (optional)
   - **Aliases**: Alternative names (optional)
4. **Click** "Create"

**Example**:
- ID: 3000
- Name: Portal
- Searchable: Yes
- Subpages: Yes
- Result: Can create pages like `Portal:Main`

#### Editing a Namespace

1. **View** namespace list
2. **Click** "Edit" on namespace
3. **Modify** settings
4. **Click** "Save"

#### Deleting a Namespace

1. **View** namespace list
2. **Click** "Delete" on custom namespace
3. **Confirm** deletion
4. **Note**: Cannot delete core namespaces (< 3000)

### Managing Wiki Settings (Special:ManageWikiSettings)

1. **Navigate** to `Special:ManageWikiSettings`
2. **View** settings grouped by section:
   - General
   - Appearance
   - Uploads
   - Rights
   - Email
3. **Modify** settings:
   - Change values
   - Click "Save"

**Common Settings**:
- **Site name**: Wiki display name
- **Language**: Default language
- **Default skin**: User interface theme
- **Logo URL**: Wiki logo
- **Enable uploads**: Allow file uploads
- **Copyright text**: License information

### Managing Permissions (Special:ManageWikiPermissions)

#### Viewing Groups

1. **Navigate** to `Special:ManageWikiPermissions`
2. **View** list of user groups
3. **Click** on a group to edit

#### Editing Group Permissions

1. **Select** a group (e.g., "sysop")
2. **View** current permissions
3. **Check/uncheck** permissions:
   - Page permissions (edit, delete, protect)
   - User permissions (block, rights)
   - Special permissions
4. **Set** group assignment rights:
   - Groups this group can add
   - Groups this group can remove
5. **Click** "Save"

#### Creating Custom Groups

1. **Click** "Create group"
2. **Enter** group name
3. **Select** permissions
4. **Configure** group management
5. **Click** "Create"

## For Regular Users

### Requesting a Wiki (Special:RequestWiki)

1. **Navigate** to `Special:RequestWiki`
2. **Read** the information about wiki requests
3. **Fill in the form**:
   - **Database name**: Desired wiki identifier
   - **Site name**: What your wiki will be called
   - **Language**: Wiki language
   - **Category**: Best category for your wiki
   - **Private**: Check if you need a private wiki
   - **Reason**: Explain why you need this wiki (important!)
4. **Click** "Submit Request"
5. **Note** your request ID
6. **Wait** for administrator review

**Tips for Good Requests**:
- Be specific about the purpose
- Explain how you'll use the wiki
- Mention if you have experience with wikis
- Include links to relevant information
- Be patient - approval may take time

**Example Good Reason**:
> "I would like to create a wiki for documenting the history of my local town. I plan to include historical photos, articles about local landmarks, and interviews with longtime residents. I have experience editing Wikipedia and have already gathered material from the local library."

**Example Bad Reason**:
> "I want a wiki."

### Checking Request Status

1. **Go** to `Special:RequestWikiQueue/[RequestID]`
   - Use the ID from your request
2. **View** current status:
   - Pending: Still under review
   - Approved: Wiki created
   - Declined: Request rejected
3. **Read** any comments from administrators

## Common Workflows

### Workflow 1: Quick Wiki Creation

For administrators who want to create wikis quickly:

```
Special:CreateWiki
→ Fill form
→ Create Wiki
→ Done (wiki accessible immediately)
```

### Workflow 2: Request Approval Process

For user-requested wikis:

```
User: Special:RequestWiki
→ Submit request

Admin: Special:RequestWikiQueue
→ View request
→ Review reason
→ Approve/Decline

User: Receives notification
→ Access new wiki (if approved)
```

### Workflow 3: Wiki Customization

For customizing a new wiki:

```
Special:ManageWiki
→ Manage Extensions (enable needed extensions)
→ Manage Settings (configure appearance, uploads)
→ Manage Namespaces (create custom namespaces)
→ Manage Permissions (adjust user rights)
```

## Best Practices

### For Administrators

1. **Review requests promptly**: Don't leave users waiting
2. **Be clear in decline reasons**: Help users understand
3. **Use comments**: Discuss with other admins
4. **Monitor new wikis**: Check for abuse/spam
5. **Document policies**: Create clear wiki request policies
6. **Regular cleanup**: Mark inactive wikis
7. **Backup regularly**: Protect wiki data

### For Users

1. **Provide detailed reasons**: Increases approval chances
2. **Choose appropriate names**: Professional, clear
3. **Select correct category**: Helps organization
4. **Be patient**: Approval takes time
5. **Follow up**: Check request status
6. **Respect decisions**: Accept if declined
7. **Report issues**: Help improve the system

## Special Page Reference

### CreateWikiV2 Pages

- **Special:CreateWiki**
  - Purpose: Create wikis directly
  - Permission: `createwiki`
  - Users: Bureaucrats by default

- **Special:RequestWiki**
  - Purpose: Request a new wiki
  - Permission: `requestwiki`
  - Users: All users by default

- **Special:RequestWikiQueue**
  - Purpose: Manage wiki requests
  - Permission: `createwiki`
  - Users: Bureaucrats by default

### ManageWikiV2 Pages

- **Special:ManageWiki**
  - Purpose: Main management hub
  - Permission: `managewiki`
  - Users: Bureaucrats by default

- **Special:ManageWikiExtensions**
  - Purpose: Enable/disable extensions
  - Permission: `managewiki`

- **Special:ManageWikiNamespaces**
  - Purpose: Create custom namespaces
  - Permission: `managewiki`

- **Special:ManageWikiSettings**
  - Purpose: Configure wiki settings
  - Permission: `managewiki`

- **Special:ManageWikiPermissions**
  - Purpose: Manage user permissions
  - Permission: `managewiki`

## Troubleshooting

### "Permission denied" errors

**Problem**: Cannot access special pages
**Solution**:
- Check you have the required permission
- Ask an administrator to grant you rights
- See `Special:ListGroupRights` for permission info

### Request not appearing in queue

**Problem**: Submitted request not showing
**Solution**:
- Check request was successful
- Look for error messages
- Verify database connection
- Check `cw_requests` table directly

### Wiki creation fails

**Problem**: Wiki creation errors
**Solution**:
- Check database user has CREATE DATABASE permission
- Verify database name format
- Check for existing wiki with same name
- Review error logs

### Extension not enabling

**Problem**: Extension enabled but not working
**Solution**:
- Verify extension is installed in MediaWiki
- Check extension dependencies
- Run `php maintenance/update.php`
- Check for extension conflicts

### Namespace not appearing

**Problem**: Created namespace not visible
**Solution**:
- Check namespace ID is valid (>= 3000, even)
- Verify namespace name is unique
- Clear MediaWiki cache
- Check for configuration conflicts

## API Usage (For Developers)

### Programmatic Wiki Creation

```php
// Create wiki programmatically
$data = [
    'dbname' => 'testwiki',
    'sitename' => 'Test Wiki',
    'language' => 'en',
    'category' => 'community',
    'private' => false
];

$success = MirahezeFunctions::createWikiDB( $data['dbname'] );
if ( $success ) {
    MirahezeFunctions::populateWikiDB( $data['dbname'] );
    MirahezeFunctions::addWiki( $data );
}
```

### Checking Wiki Status

```php
// Check if wiki exists
$exists = MirahezeFunctions::wikiExists( 'mywiki' );

// Get wiki info
$info = MirahezeFunctions::getWikiInfo( 'mywiki' );
print_r( $info );
```

### Managing Extensions Programmatically

```php
// Enable extension
ManageWikiExtensions::enableExtension( 'mywiki', 'VisualEditor' );

// Disable extension
ManageWikiExtensions::disableExtension( 'mywiki', 'Gadgets' );

// Get enabled extensions
$extensions = ManageWikiExtensions::getEnabledExtensions( 'mywiki' );
```

## Tips and Tricks

### Bulk Operations

Create maintenance script for bulk operations:

```bash
#!/bin/bash
# Enable VisualEditor on all wikis

for wiki in $(php maintenance/sql.php --wiki=createwiki -e "SELECT wiki_dbname FROM cw_wikis" -sN); do
    php extensions/ManageWikiV2/maintenance/setExtension.php --wiki=$wiki --extension=VisualEditor --enable
done
```

### Custom Categories

Add custom categories for specific use cases:

```php
$wgCreateWikiCategories['research'] = 'Research';
$wgCreateWikiCategories['nonprofit'] = 'Non-Profit';
```

### Automated Approval

For trusted users, auto-approve requests:

```php
// In your own extension/hook
if ( $user->getEditCount() > 100 ) {
    RequestWiki::approve( $requestId, $autoApproveUser, 'Auto-approved: trusted user' );
}
```

## Further Help

- Documentation: https://github.com/Xaloria/WikiFarmV2/tree/main/docs
- Issue Tracker: https://github.com/Xaloria/WikiFarmV2/issues
- MediaWiki Help: https://www.mediawiki.org/wiki/Help:Contents
