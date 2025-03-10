# WP Learn Plugin Security

A practice plugin for learning WordPress security concepts and implementation.

## Description

This plugin was created as a learning tool for WordPress security best practices. It demonstrates various security techniques and approaches that can be implemented in WordPress plugins.

## Features

- Login security measures
- Input sanitization examples
  - Using `sanitize_text_field()` for text inputs
  - Using `sanitize_email()` for email addresses
  - Using `sanitize_key()` for nonce values
- Data validation techniques
  - Type casting with `(int)` for ID values
  - Input validation before database operations
- Nonce implementation
  - Added nonce verification for delete operations
  - Protection against CSRF attacks in admin actions
  - Using `wp_create_nonce()` on the server side
  - Passing nonce via AJAX requests
  - Verifying with `wp_verify_nonce()` before processing actions
- Safe database operations
  - Using prepared statements with `$wpdb->prepare()`
  - Using WordPress built-in methods like `$wpdb->insert()` and `$wpdb->delete()`
  - Proper escape functions for database queries
- XSS prevention methods
  - Using `esc_html()` for displaying text
  - Using `esc_attr()` for HTML attributes
- CSRF protection
  - Form nonce fields with `wp_nonce_field()`
  - Nonce verification for all form submissions

## Installation

1. Download or clone this repository
2. Upload to your WordPress plugins directory
3. Activate the plugin from the WordPress admin panel

## Usage

This plugin serves as educational material for learning WordPress security. Review the code to understand different security implementations and best practices that can be applied to WordPress development.

### Nonce Implementation for Delete Actions

To verify proper nonce implementation for delete operations:
1. Check for `wp_create_nonce()` when generating delete links or forms
2. Verify the nonce is being passed with delete requests (via JavaScript)
3. Confirm `wp_verify_nonce()` is being called before processing any delete operation
4. Test delete operations with invalid nonces to ensure they fail properly

### Form Processing Security

The plugin demonstrates secure form processing with:
1. Nonce creation and verification
2. Input sanitization
3. Safe redirection using `wp_safe_redirect()`
4. Proper database operations with prepared statements

## Requirements

- WordPress 5.0+
- PHP 7.0+

## Note

This is a learning tool and not intended for production environments without thorough review and customization.

## Recent Updates

- Fixed form submission processing issues
- Improved nonce implementation in AJAX requests
- Enhanced documentation for each function
- Updated database operations to use safer WordPress methods
