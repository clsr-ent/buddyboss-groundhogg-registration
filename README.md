# buddyboss-groundhogg-registration
# BuddyBoss + Groundhogg Registration Integration

Last updated: 2025-01-03 22:08:44 UTC
Author: clsr-ent

## Description
This WordPress plugin integrates BuddyBoss registration with Groundhogg CRM, enhancing the standard registration process by adding newsletter subscription options and automating contact management in Groundhogg.

## Features

### 1. Registration Form Enhancements
- Adds a newsletter subscription checkbox to the BuddyBoss registration form
- Utilizes the native BuddyBoss Terms of Service checkbox
- Maintains a clean, integrated look with BuddyBoss styling

### 2. Groundhogg CRM Integration
The plugin automatically:
- Creates or updates contacts in Groundhogg when users register
- Maps user registration data to Groundhogg contact fields:
  - First Name
  - Last Name
  - Email Address
  - Terms Agreement Status
  - GDPR Consent Status

### 3. Automated Contact Management
When a user registers:
- If they agree to the Terms of Service:
  - Marks their terms agreement status in Groundhogg
- If they subscribe to the newsletter:
  - Applies the 'newsletter-subscriber' tag
  - Confirms their email in Groundhogg
  - Sets appropriate GDPR consent flags

## Technical Requirements
- WordPress 6.0 or higher
- BuddyBoss Platform (latest version)
- Groundhogg CRM (latest version)
- PHP 8.0 - 8.4
- MySQL 8.0 or higher

## PHP Compatibility
- Compatible with PHP 8.0 through 8.4
- Uses basic type hints compatible with PHP 8.0+
- Implements proper error handling
- Follows WordPress coding standards

## Installation
1. Upload the plugin to your WordPress plugins directory
2. Activate the plugin through the WordPress admin panel
3. No additional configuration needed - works automatically with BuddyBoss registration

## Developer Notes
- Code follows WordPress coding standards
- Implements proper error checking
- Uses type declarations where appropriate
- Maintains compatibility across PHP 8.0 - 8.4

## Version History
- 1.1.0 (2025-01-03)
  - Added PHP 8.0-8.4 compatibility
  - Improved error handling
  - Added proper type hints
- 1.0.0 - Initial release

## Support
For support inquiries, please visit: https://closrr.com/

## License
Copyright © 2025 Closrr.com. All rights reserved.
