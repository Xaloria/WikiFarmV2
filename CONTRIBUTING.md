# Contributing to WikiFarmV2

Thank you for your interest in contributing to WikiFarmV2! This document provides guidelines for contributing to the project.

## Code of Conduct

- Be respectful and constructive
- Welcome newcomers and help them get started
- Focus on what is best for the community
- Show empathy towards other community members

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/Xaloria/WikiFarmV2/issues)
2. If not, create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - MediaWiki version, PHP version, database type
   - Error messages or logs
   - Screenshots if applicable

### Suggesting Features

1. Check if the feature has been suggested
2. Create a new issue with:
   - Clear description of the feature
   - Use cases and benefits
   - Potential implementation approach
   - Any concerns or drawbacks

### Submitting Code

1. **Fork the repository**
   ```bash
   git clone https://github.com/YOUR-USERNAME/WikiFarmV2.git
   cd WikiFarmV2
   ```

2. **Create a branch**
   ```bash
   git checkout -b feature/my-new-feature
   # or
   git checkout -b fix/bug-description
   ```

3. **Make your changes**
   - Follow the coding style (see below)
   - Add comments for complex logic
   - Update documentation if needed
   - Add tests if applicable

4. **Test your changes**
   - Test on MediaWiki 1.39+
   - Test on both shared hosting and VPS if possible
   - Verify no PHP errors
   - Check database operations work correctly

5. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add feature: description"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/my-new-feature
   ```

7. **Create a Pull Request**
   - Go to the original repository
   - Click "New Pull Request"
   - Select your branch
   - Provide clear description of changes
   - Reference any related issues

## Coding Style

### PHP Style

Follow MediaWiki coding conventions:

```php
<?php

/**
 * Class documentation
 */
class MyClass {
    
    /**
     * Method documentation
     *
     * @param string $param Parameter description
     * @return bool Return value description
     */
    public function myMethod( $param ) {
        if ( $param ) {
            return true;
        }
        
        return false;
    }
}
```

**Key points:**
- Use tabs for indentation
- Space after control structures: `if (`, `for (`
- Space around operators: `$a = $b + $c`
- Braces on same line for control structures
- Braces on new line for functions/classes
- Document all public methods
- Use meaningful variable names

### SQL Style

```sql
-- Table names with prefix placeholder
CREATE TABLE /*_*/cw_tablename (
    field_name VARCHAR(255) NOT NULL,
    field_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
) /*$wgDBTableOptions*/;

-- Indexes
CREATE INDEX /*i*/index_name ON /*_*/cw_tablename (field_name);
```

### JavaScript Style (if adding)

- Use ES6+ syntax
- Use `const` and `let`, not `var`
- Use semicolons
- 4-space indentation

### Commit Messages

Good commit messages:
```
Add feature: wiki request comments

- Add comment system to wiki requests
- Allow admins to discuss requests
- Show comment history on request page
```

Bad commit messages:
```
fix stuff
updated files
changes
```

## Documentation

When adding features:
1. Update README.md if it affects main features
2. Update relevant documentation in docs/
3. Add examples to USAGE.md
4. Update CONFIGURATION.md for new settings

## Testing

### Manual Testing

1. Install on clean MediaWiki installation
2. Test all special pages work
3. Test wiki creation/deletion
4. Test request workflow
5. Test extension management
6. Test namespace management
7. Test settings management
8. Test permissions work correctly

### Database Testing

1. Verify tables are created correctly
2. Test with MySQL and MariaDB
3. Verify data integrity
4. Test rollback scenarios

### Shared Hosting Testing

Test on:
- cPanel hosting
- Plesk hosting
- DirectAdmin hosting
- Verify no root access needed
- Check file permissions
- Test without SSH access

## Pull Request Review Process

1. **Automated checks** (if set up):
   - Syntax validation
   - Coding standards
   - Tests pass

2. **Manual review**:
   - Code quality
   - Security concerns
   - Performance impact
   - Documentation completeness

3. **Testing by maintainers**:
   - Functional testing
   - Integration testing
   - Compatibility testing

4. **Feedback and iteration**:
   - Address review comments
   - Make requested changes
   - Update PR

5. **Merge**:
   - Squash and merge for small changes
   - Regular merge for larger features
   - Update changelog

## Security

### Reporting Security Issues

**Do not** report security issues publicly. Instead:

1. Email security@example.com (replace with actual contact)
2. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

### Security Guidelines

When writing code:
- Sanitize all user input
- Use parameterized database queries
- Validate file uploads
- Check permissions before actions
- Escape output properly
- Don't expose sensitive information

## Project Structure

```
WikiFarmV2/
├── extensions/
│   ├── CreateWikiV2/     # Wiki creation functionality
│   └── ManageWikiV2/     # Wiki management functionality
├── docs/                  # Documentation
├── install.sh            # Installation script
└── README.md             # Main documentation
```

## Areas Needing Contribution

### High Priority
- Unit tests for core functions
- Integration tests for workflows
- Performance optimization
- Security audit
- Accessibility improvements

### Medium Priority
- Additional language translations
- More manageable extensions
- Bulk operations UI
- Statistics and reporting
- API endpoints

### Low Priority
- Additional themes/skins
- More wiki categories
- Enhanced logging
- Monitoring integration
- Mobile optimization

## Getting Help

- **Documentation**: Start with docs/ directory
- **Issues**: Check existing issues for answers
- **Discussions**: Use GitHub Discussions for questions
- **Chat**: (Add chat platform if available)

## Recognition

Contributors will be:
- Listed in README.md
- Mentioned in release notes
- Credited in git history

## License

By contributing, you agree that your contributions will be licensed under the GPL-3.0-or-later license.

## Questions?

If you have questions about contributing:
1. Check existing documentation
2. Search closed issues
3. Open a new discussion
4. Contact maintainers

Thank you for contributing to WikiFarmV2!
