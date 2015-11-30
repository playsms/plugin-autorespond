# About

SMS Autorespond, a better version of SMS Autoreply plugin for playSMS.

This plugin will scan for each incoming SMS for a match with configured regular expression and reply automatically based on its setup.

The main difference with SMS Autoreply is the incoming SMS scanned based on patterns, not keyword and scenarios.

Info          | Data
--------------|-----------------------------------------
Author        | [Anton Raharja](http://antonraharja.com)
Created       | 151103
Last update   | 151129
Version       | 2
Compatibility | playSMS 1.1 and above
License       | GPLv3

# Changelog

## Version 2

   - change to hook recvsms_intercept_after() to fix missing feature and status
   - change name from sms_autorespond to autorespond

## Version 1

   - changelog started

# Todo

- How to check duplicate regex, 2 entries can have seemingly different regexs but actually matching the same thing
- If we know how to check duplicates then we can let non-admin accounts to add the service themselves, now for admin only

# Upgrade

If you have installed previous version then you need to remove it before installing this version.

1. Remove `sms_autorespond` files and folder, but do not remove the DB table
2. Follow below installation steps EXCEPT point 3, the SQL file don't need to be re-inserted

# Installation

Current version of this plugin should work with playSMS 1.1 and above.

Here is how to install it on a working playSMS:

1. Stop `playsmsd`, just make sure to stop it
2. Copy `web/plugin/feature/autorespond` to the playSMS `plugin/feature` folder
3. Insert `web/plugin/feature/autorespond/db/install.sql` to your playSMS database
4. Re-start `playsmsd`
5. Login to web with admin accounts and visit **Features -> Manage autorespond**

# Usage

## Example 1

Here is the configuration example:

![Example 1](https://raw.githubusercontent.com/antonraharja/playsms-autorespond/master/web/plugin/feature/autorespond/docs/screenshots/example1.png)

Configuration above will scan incoming SMS and match with incoming SMS containing the word `code` (case-insensitive) followed by a number from 1 to 3 digits length.

SMS like these will match: `code 123` `Code 45` `CODE     6` `Code895`

SMS like these will not match: `code1234` `Code 876543` `CODE`
