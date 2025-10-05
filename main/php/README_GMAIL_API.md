# Gmail API Integration for SaysonCo

This document provides an overview of the Gmail API integration implemented for SaysonCo.

## Features Implemented

1. **Email Sending**: Replaced the previous email sending mechanism with Gmail API
2. **Email Scanning**: Added functionality to scan and search emails
3. **Email Verification**: Enhanced the verification process with styled pages and welcome emails
4. **Authentication Flow**: Implemented OAuth2 authentication for Gmail API access

## Files Created/Modified

- `email.php`: Core Gmail API functionality (send_email, scan_emails, authentication)
- `gmail_auth.php`: Handles Gmail API authentication
- `gmail_callback.php`: Processes OAuth callbacks
- `email_scanner.php`: Interface for scanning emails
- `verify_account.php`: Updated to use Gmail API for verification
- `signupprocess_users.php`: Updated to use new email functions
- `test_gmail_api.php`: Test script for Gmail API functionality

## How to Use

### Authentication
1. Users need to authenticate with their Gmail account first
2. Visit `/main/php/gmail_auth.php` to start the authentication process
3. After successful authentication, users will be redirected back to the application

### Email Scanning
1. Visit `/main/php/email_scanner.php` to access the email scanning interface
2. Use search queries to find specific emails (e.g., "label:inbox", "from:example@gmail.com")

### Testing
1. Visit `/main/php/test_gmail_api.php` to test all Gmail API functionality
2. The test page allows:
   - Testing authentication
   - Sending test emails
   - Scanning emails with custom queries

## Requirements

- PHP 7.4+
- Google API Client Library (installed via Composer)
- Valid Google Cloud Platform credentials with Gmail API enabled

## Security Notes

- OAuth tokens are stored in the session
- Users must authenticate with their own Gmail account
- Email scanning is limited to the authenticated user's inbox