# About

SMS Autorespond, a better version of SMS Autoreply plugin for playSMS.

This plugin will scan for each incoming SMS for a match with configured regular expression and reply automatically based on its setup.

The main difference with SMS Autoreply is the incoming SMS scanned based on patterns, not keyword and scenarios.

Info          | Data
--------------|-----------------------------------------
Author        | [Anton Raharja](http://antonraharja.com)
Created       | 151103
Last update   | 151105
Version       | 1.0-master
Compatibility | playSMS 1.3 and above
License       | GPLv3

Issues to solve:

- How to check duplicate regex, 2 entries can have seemingly different regexs but actually matching the same thing
- If we know how to check duplicates then we can let non-admin accounts to add the service themselves, now for admin only
- Issues lay on playSMS it self, incoming SMS handeld by this plugin cannot be viewed anywhere, but they're recorded in tblSMSIncoming
