# Contributing

Thank you for considering contributing to Filament Tree View! We welcome contributions from everyone.

## Ways to Contribute

- **Report bugs** - If you find a bug, please create an issue with a clear description and steps to reproduce
- **Suggest features** - Have an idea? Open an issue to discuss it
- **Submit pull requests** - Code contributions are always welcome
- **Improve documentation** - Help make our docs clearer and more comprehensive
- **Share examples** - Show us how you're using the package

## Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/openplain/filament-tree-view.git
   cd filament-tree-view
   ```

3. Install dependencies:
   ```bash
   composer install
   npm install
   ```

4. Create a branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## Development Workflow

### Testing Your Changes

This package does not include a demo application. To test your changes, you'll need to use the package in a Laravel application with Filament installed.

**Option 1: Use Composer Path Repository (Recommended)**

In your test Laravel application's `composer.json`, add a path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../filament-tree-view"
        }
    ]
}
```

Then require the package:

```bash
composer require openplain/filament-tree-view:@dev
```

**Option 2: Create a Fresh Test Application**

```bash
# Create a new Laravel app
composer create-project laravel/laravel test-app
cd test-app

# Install Filament
composer require filament/filament
php artisan filament:install --panels

# Add path repository and install your local package
# (Edit composer.json as shown in Option 1)
composer require openplain/filament-tree-view:@dev
```

**Publishing Assets**

After making changes to the package, publish the assets to your test application:

```bash
php artisan filament:assets
```

Or if you need to force republish:

```bash
php artisan vendor:publish --tag=filament-tree-view-assets --force
```

### Building JavaScript and CSS

When making changes to JavaScript or CSS files in the package:

```bash
# Watch mode for development (auto-rebuilds on file changes)
npm run dev

# Production build (optimized for distribution)
npm run build
```

**Development Workflow:**
1. Make your changes to source files in `resources/js/` or `resources/css/`
2. Run `npm run build` to compile the assets to `resources/dist/`
3. Republish assets to your test application: `php artisan vendor:publish --tag=filament-tree-view-assets --force`
4. Refresh your browser to see changes

**Important:** Always run `npm run build` before committing to ensure the compiled distribution assets are up to date.

### Code Style

We follow Laravel and Filament coding standards:

```bash
# Fix code style automatically
vendor/bin/pint

# Check for issues
vendor/bin/pint --test
```

All pull requests must pass Pint checks.

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/Feature/TreeTest.php
```

Please add tests for any new features or bug fixes.

## Pull Request Guidelines

1. **Keep changes focused** - One feature or fix per PR
2. **Follow existing patterns** - Match the codebase style and architecture
3. **Update documentation** - Add/update README.md if needed
4. **Add tests** - Cover new functionality with tests
5. **Run code style** - Execute `vendor/bin/pint` before committing
6. **Build assets** - Run `npm run build` if you changed JavaScript or CSS
7. **Test your changes** - Verify functionality in a Laravel application with Filament
8. **Write clear commits** - Use descriptive commit messages

### Commit Message Format

We prefer clear, descriptive commit messages:

```
Add support for custom drag handles

- Allow users to specify a custom element as drag handle
- Update documentation with new dragHandle option
- Add tests for custom drag handle behavior
```

### Pull Request Process

1. **Create an issue first** (for major changes) - Discuss the approach before coding
2. **Update the README** if you're adding features
3. **Ensure all tests pass**
4. **Update CHANGELOG.md** with your changes (under "Unreleased")
5. **Request a review** from maintainers

## Reporting Bugs

When reporting bugs, please include:

- **Clear title** - Summarize the issue
- **PHP/Laravel/Filament versions** - Help us reproduce your environment
- **Steps to reproduce** - Detailed steps to trigger the bug
- **Expected behavior** - What should happen
- **Actual behavior** - What actually happens
- **Code samples** - Minimal code to reproduce the issue
- **Screenshots** - If applicable

### Example Bug Report

```markdown
**Bug:** Drag-and-drop fails on deeply nested items

**Environment:**
- PHP 8.3
- Laravel 12.0
- Filament 4.1.7
- filament-tree-view 1.0.0

**Steps to reproduce:**
1. Create a tree with 7+ levels of nesting
2. Try to drag an item at level 6
3. Drop indicator doesn't appear

**Expected:** Drop indicator shows valid drop targets
**Actual:** No visual feedback, drag fails silently

**Code:** [Link to GitHub Gist with minimal reproduction]
```

## Feature Requests

We love hearing your ideas! When suggesting features:

- **Explain the use case** - Why is this needed?
- **Describe the API** - How should it work?
- **Consider alternatives** - Are there other solutions?
- **Check existing issues** - Has this been suggested before?

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive experience for everyone, regardless of:

- Age, body size, disability, ethnicity, gender identity and expression
- Level of experience, education, socio-economic status
- Nationality, personal appearance, race, religion
- Sexual identity and orientation

### Our Standards

**Positive behavior:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

**Unacceptable behavior:**
- Trolling, insulting/derogatory comments, and personal or political attacks
- Public or private harassment
- Publishing others' private information without permission
- Other conduct which could reasonably be considered inappropriate

### Enforcement

Maintainers have the right to remove, edit, or reject comments, commits, code, issues, and other contributions that don't align with this Code of Conduct.

## Questions?

- **Documentation** - Check the [README.md](README.md) first
- **Issues** - Search existing issues before creating a new one
- **Discussions** - Use GitHub Discussions for general questions
- **Email** - Reach out to support@openplain.com for private inquiries

## Recognition

Contributors will be recognized in:
- Release notes for their contributions
- The README credits section (for significant contributions)

Thank you for helping make Filament Tree View better! 🌳
