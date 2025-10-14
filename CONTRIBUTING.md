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

### Running the Demo App

The package includes a demo Laravel application for testing:

```bash
cd demo-app
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

Visit http://localhost:8000/admin to see the tree view in action.

### Building JavaScript

When making changes to JavaScript:

```bash
# Watch mode for development
npm run dev

# Production build
npm run build
```

**Important:** Always run `npm run build` before committing to ensure the compiled assets are up to date.

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
6. **Build assets** - Run `npm run build` if you changed JavaScript
7. **Write clear commits** - Use descriptive commit messages

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
