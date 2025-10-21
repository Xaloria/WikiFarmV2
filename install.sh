#!/bin/bash
#
# Installation script for WikiFarmV2 CreateWikiV2 and ManageWikiV2 extensions
# Compatible with shared hosting (no root required) and VPS environments
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "================================================"
echo "WikiFarmV2 Installation Script"
echo "CreateWikiV2 & ManageWikiV2 Extensions"
echo "================================================"
echo

# Check if we're in the right directory
if [ ! -f "LocalSettings.php" ]; then
    echo -e "${RED}Error: LocalSettings.php not found!${NC}"
    echo "Please run this script from your MediaWiki root directory."
    exit 1
fi

MEDIAWIKI_ROOT=$(pwd)
echo -e "${GREEN}MediaWiki root detected: $MEDIAWIKI_ROOT${NC}"
echo

# Check if extensions directory exists
if [ ! -d "extensions" ]; then
    echo -e "${RED}Error: extensions directory not found!${NC}"
    exit 1
fi

# Step 1: Copy extensions
echo "Step 1: Installing extensions..."
if [ -d "extensions/CreateWikiV2" ]; then
    echo -e "${YELLOW}CreateWikiV2 already exists, skipping...${NC}"
else
    echo "CreateWikiV2 will be loaded from repository"
fi

if [ -d "extensions/ManageWikiV2" ]; then
    echo -e "${YELLOW}ManageWikiV2 already exists, skipping...${NC}"
else
    echo "ManageWikiV2 will be loaded from repository"
fi

echo -e "${GREEN}✓ Extensions ready${NC}"
echo

# Step 2: Create cache directory
echo "Step 2: Creating cache directory..."
CACHE_DIR="/tmp/createwiki"
mkdir -p "$CACHE_DIR"
chmod 755 "$CACHE_DIR"
echo -e "${GREEN}✓ Cache directory created: $CACHE_DIR${NC}"
echo

# Step 3: Check database connection
echo "Step 3: Checking database configuration..."
if grep -q "wgDBserver" LocalSettings.php; then
    echo -e "${GREEN}✓ Database configuration found${NC}"
else
    echo -e "${YELLOW}⚠ Database configuration not found in LocalSettings.php${NC}"
fi
echo

# Step 4: Update LocalSettings.php
echo "Step 4: Updating LocalSettings.php..."

BACKUP_FILE="LocalSettings.php.backup.$(date +%Y%m%d_%H%M%S)"
cp LocalSettings.php "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created: $BACKUP_FILE${NC}"

# Check if extensions are already loaded
if grep -q "wfLoadExtension.*CreateWikiV2" LocalSettings.php; then
    echo -e "${YELLOW}⚠ CreateWikiV2 already configured in LocalSettings.php${NC}"
else
    cat >> LocalSettings.php << 'EOF'

# CreateWikiV2 Extension
wfLoadExtension( 'CreateWikiV2' );
$wgCreateWikiDatabase = 'createwiki';
$wgCreateWikiCacheDirectory = '/tmp/createwiki';
$wgCreateWikiSubdomain = 'example.com'; # Change this to your domain
$wgCreateWikiUseCategories = true;
$wgCreateWikiUsePrivateWikis = true;

EOF
    echo -e "${GREEN}✓ CreateWikiV2 configuration added${NC}"
fi

if grep -q "wfLoadExtension.*ManageWikiV2" LocalSettings.php; then
    echo -e "${YELLOW}⚠ ManageWikiV2 already configured in LocalSettings.php${NC}"
else
    cat >> LocalSettings.php << 'EOF'
# ManageWikiV2 Extension
wfLoadExtension( 'ManageWikiV2' );

EOF
    echo -e "${GREEN}✓ ManageWikiV2 configuration added${NC}"
fi

# Add SetupCreateWiki if not present
if grep -q "SetupCreateWiki.php" LocalSettings.php; then
    echo -e "${YELLOW}⚠ SetupCreateWiki already included in LocalSettings.php${NC}"
else
    cat >> LocalSettings.php << 'EOF'
# Load CreateWiki setup
require_once "$IP/extensions/CreateWikiV2/SetupCreateWiki.php";

EOF
    echo -e "${GREEN}✓ SetupCreateWiki.php included${NC}"
fi

echo

# Step 5: Run update.php
echo "Step 5: Running database updates..."
echo "This will create the necessary database tables."
echo

if command -v php > /dev/null 2>&1; then
    php maintenance/update.php --quick
    echo -e "${GREEN}✓ Database tables created${NC}"
else
    echo -e "${RED}Error: PHP not found in PATH${NC}"
    echo "Please run manually: php maintenance/update.php"
fi

echo

# Step 6: Final instructions
echo "================================================"
echo -e "${GREEN}Installation Complete!${NC}"
echo "================================================"
echo
echo "Next steps:"
echo "1. Edit LocalSettings.php and update:"
echo "   - \$wgCreateWikiSubdomain to your actual domain"
echo "   - Database credentials if needed"
echo
echo "2. Grant permissions to users:"
echo "   - 'createwiki' right for wiki creation"
echo "   - 'requestwiki' right for wiki requests"
echo "   - 'managewiki' right for wiki management"
echo
echo "3. Access the special pages:"
echo "   - Special:CreateWiki - Create wikis directly"
echo "   - Special:RequestWiki - Request a new wiki"
echo "   - Special:RequestWikiQueue - Manage wiki requests"
echo "   - Special:ManageWiki - Manage wiki settings"
echo
echo "4. For shared hosting compatibility:"
echo "   - Ensure cache directory is writable: $CACHE_DIR"
echo "   - Check that your hosting allows database creation"
echo "   - Verify file permissions are correct"
echo
echo "For more information, see:"
echo "  - README.md"
echo "  - docs/INSTALL.md"
echo "  - docs/CONFIGURATION.md"
echo
echo "Backup saved to: $BACKUP_FILE"
echo "================================================"
