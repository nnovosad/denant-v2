=== Darwin Backup ===
Contributors: aguidrevitch
Tags: backup, backups, back up, recover, recovery, restore, restoration, duplicate, duplicator, copy site, clone site, cloner, copier
Requires at least: 3.0.1
Tested up to: 4.7.2
Stable tag: 1.2.25
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

One click recovery from the worst-case scenarios. Simplest automated backup, restoration and cloning tool for non-techies. Backups with built-in UI.

== Description ==

Darwin Backup is the ultimate backup, restoration and cloning tool for non-techies.

There are many backup plugins but restoration has never been this simple, easy, or reliable until now. Darwin Backup is designed to make the restore process as easy as one click and, in most cases, you don't need even know what your FTP, mysql credentials or directory location. Just point your browser to the link Darwin Backup provides you with, authorize yourself using your WordPress administrator credentials and click 'Login' and click 'Restore'.

WARNING: FileZilla is known to break downloaded archives ! Read more on this in the FAQ below

= Features =

* No techincal knowledge required.
* Backup and restore WPEngine.com-hosted sites
* Backup and restore sites on the hostings where exec(), passthrough() and pcntl* functions are disabled
* Backup sites of any size
* Scheduled backups support
* Backup both files and database
* Restore directly to to the server, or over FTP
* Intercepts upgrade and makes backup before upgrade (optionally)

Most of the users will be able to recover w/o any knowledge about their Wordpress setup at all. For the rest, we really try to auto-detect as much as we can, however:

* with some unusual setups, you might need to know your FTP credentials.
* when cloning, you will need to know mysql credentials, and, rarely, FTP credentials.

Please let us know if you have trouble restoring at https://wordpress.org/support/plugin/darwin-backup

== Installation ==

**From your WordPress dashboard**

1. Visit **Plugins > Add New**
2. Type **darwin backup** in the search box and click on search button
3. Find **Darwin Backup** plugin.
4. Then click on Install Now after that activate the plugin.
5. The plugin adds a menu item **Darwin Backup**
6. Visit **Darwin Backup > Add New** and create your first backup. (You can always delete it later.)

**Installing Via FTP**

1. Download the plugin to your hardisk.
2. Unzip.
3. Upload the **darwin-backup** folder into your plugins directory.
4. Log in to your WordPress admin panel and click the Plugins menu.
5. Then activate the plugin.
6. The plugin adds a menu item **Darwin Backup**
7. Visit **Darwin Backup > Add New** and create your first backup. (You can always delete it later.)

== Frequently Asked Questions ==

= WARNING ! FileZilla issues

FileZilla downloads php files in ASCII mode (as text), that means it converts line endings to your system's defaults. For Darwin Backup archives that means binary data inside archive gets corrupted. To download archives safely, make sure you set 'Transfers' -> 'File Types' -> 'Default transfer type' to BINARY in FileZilla preferences !

= Why do I need to mail / copy to clipboard a link after backup =

If something breaks (no matter how severely) and your dashboard is not accessible, you can use this backup link to recover site quickly. **Always email links to archive or copy them to clipboard right after backup** to have instant access to recovery UI, which is built-in into your backup.

= My site is not operable after upgrade or an accident? =

Just point your browser to the mailed / copied to clipboard link and click restore.

= But I've lost the link! =

This will require some extra effort:

1. FTP or SSH to your Wordpress installation, go to wp-content/plugins/darwin-backup/backups/ folder and find the recent backup, which filename looks like YYYYMMDD-WPV.E.R.S.I.O.N-STRING.NUMBER.php
2. Copy its filename and point your browser to http://yoursite.com/wp-content/plugins/darwin-backup/backups/YYYYMMDD-WPV.E.R.S.I.O.N-STRING.NUMBER.php
3. If it works - try to recover. If you fail at this stage for some reason, read on.
4. If it is not - copy STRING.NUMBER part, and point your browser to  http://yoursite.com/wp-content/plugins/darwin-backup/backups/restore.php?sid=STRING.NUMBER
5. Recover.

= Do I need to backup uploads folder? =

Only if you are going to replicate the site. Otherwise - no, uploads folder does not contain files needed to restore your Wordpress to a working state, so feel free to not backup it for the restoration purposes.

= How to duplicate a site? =

Just download a backup (PHP file), upload it to the new site and point your browser to it.

= I'm getting Fatal error: Allowed memory size ... exhausted ... during replication =

**IMPORTANT**: You can hit this problem only when duplicating sites or pointing your browser directly to the archive file. Copied to clipboard or emailed links, provided after backup or from listing screen, are already wrapped with <a href="http://darwinapps.com/restore.php">restore.php</a>

* Increase memory_limit php ini setting to be around 2x archive size. Please consult your hosting provider documentation on how to do this.
* Sometimes archives are just too large to fit memory. In this case, download <a href="http://darwinapps.com/restore.php">restore.php</a> and upload to the same directory where you archive is.

= I'm getting blank page during duplication =

Most probably you are affected by 'out of memory' issue above. Please follow up the steps described in **I'm getting Fatal error: Allowed memory size ... exhausted ...** section

= Restore on a different host stops suddenly during duplication =

An observation shows that some hosting providers decrease memory_limit dynamically (for example GoDaddy), and even though you are able to open restore panel, it might fail with 'out of memory' on the subsequent requests. See **I'm getting Fatal error: Allowed memory size ... exhausted ...** section for possible solutions

= Why authorize during restore? =

The archives produced by Darwin Backup are world-accessible and do not depend on Wordpress code base so you can restore even if your Wordpress renders 500 Internal Server Error. If archives are not protected with username / password, a malicious user who has the link to archive can restore your site to some previous state w/o your permission. On restore, archive will check if it can authorize you against your current Wordpress installation, and if it can - it will prompt you for your Wordpress username / password. If your original Wordpress mysql instance is not accessible (eg when restored on a different host) - you will get directly to the recovery screen, no username/password asked, so do not forget to delete archive

== Screenshots ==

1. Backup before core, theme or plugin upgrade, progress meter

2. Backup before core, theme or plugin upgrade, complete

3. List of available backups

4. Restore panel


== Changelog ==

= 1.2.25, 11 May 2017 =
* better recovery from failures during backup

= 1.2.24, 10 May 2017 =
* better start time detection to avoid timeouts
* stricter error reporting

= 1.2.23, 15 Apr 2017 =
* better debug for gzip-related errors
* lower memory consumption during bootstrap task

= 1.2.22, 13 Apr 2017 =
* FileZilla warning added into description

= 1.2.21, 2 Apr 2017 =
* deadlines lowered to under-30 seconds to get rid of "Unable to lock state", frequent in fastcgi environments due to fastcgi_read_timeout being lower that max_execution_time

= 1.2.20, 1 Apr 2017 =
* better directory selection for state storage

= 1.2.19.1, 31 Mar 2017 =
* more debug information for zero-length blocks
* force little-endian numbers in gzip headers

= 1.2.19, 28 Mar 2017 =
* safer flock handling

= 1.2.18.1, 27 Mar 2017 =
* plugin description updated

= 1.2.18, 25 Mar 2017 =
* better storage dir detection
* better problem detection and user notifications

= 1.2.17, 22 Mar 2017 =
* try to fix backup dir permissions before backing up
* adding uploads dir as an option for backup dir

= 1.2.16.1, 21 Mar 2017 =
* adding wordpress.org formatting to the error report

= 1.2.16, 21 Mar 2017 =
* error handling redone

= 1.2.15.1, 20 Mar 2017 =
* correct original backup site url displayed on the restore panel

= 1.2.15, 20 Mar 2017 =
* client-side error logging improved
* more user-frienly explanations added
* css and javascript embedded into the restore panel for better compatibility with various hosting providers

= 1.2.14.1, 8 Mar 2017 =
* minor improvement to disk_free_space usage

= 1.2.14, 8 Mar 2017 =
* "disk_free_space(): Value too large for defined data type" fixed

= 1.2.13, 26 Feb 2017 =
* security improved: enforced restore authentication
* CloudWays compatibility improved: getting rid of session-based authentication
* https -> http rewrite bug fixed

= 1.2.12, 22 Feb 2017 =
* mysql restore degradation from utf8mb4 to utf8, if utf8mb4 is not available

= 1.2.11, 21 Feb 2017 =
* better FastCGI support
* "cancel" button fixed

= 1.2.10, 27 Jan 2017 =
* better site responsivenes during backup

= 1.2.9, 27 Jan 2017 =
* better domain recognition in emailed links
* error reporting improved

= 1.2.8, 25 Jan 2017 =
* correct bgzf eof support
* symlinks are now resolved
* symlink depth set to 3 to avoid infinite recursion
* restore UI speedups

= 1.2.7, 31 Dec 2016 =
* restoring from non-writeable archives

= 1.2.6, 31 Dec 2016 =
* better WPEngine compatibility

= 1.2.5, 31 Dec 2016 =
* better SQL rewrite during restore
* fix to SQL rewrite during restore

= 1.2.4, 28 Dec 2016 =
* better PHP7 compatibility

= 1.2.3, 27 Dec 2016 =
* Better support for migration from WPEngine, as recommended on https://wpengine.com/support/best-practices-uploading-wp-engine-site-another-environment/

= 1.2.2, 27 Dec 2016 =
* Better error reporting during restore

= 1.2.1, 24 Dec 2016 =
* Changelog moved to a separate file

= 1.2.0, 24 Dec 2016 =
* WPEngine.com support added

= 1.1.32, 22 Dec 2016 =
* major bugfix for compression of files that grow or shrink during compression
* various minor improvements and warnings fixes

= 1.1.31, 20 Dec 2016 =
* making WP Engine PHP Compatibility Checker happy (better php 7.0 compatibility)

= 1.1.30, 19 Dec 2016 =
* error during verify stage fixed correctly

= 1.1.29, 19 Dec 2016 =
* error during verify stage fixed
* CloudWays / DigitalOcean partial workarounds added

= 1.1.28, 11 Dec 2016 =
* minor warning fixed

= 1.1.27, 10 Dec 2016 =
* minor UI improvements

= 1.1.26, 9 Dec 2016 =
* backup list: time zone support added

= 1.1.25, 8 Dec 2016 =
* "restore over FTP" engine rewritten
* restore greatly improved overall, tries to fix permissions where possible

= 1.1.24, 7 Dec 2016 =
* permissions checks relaxed
* multisite information and user level added to stack trace

= 1.1.23, 25 Nov 2016 =
* another try for CloudFlare's breaking ladda.js
* backup restore on servers behind CloudFlare fixed

= 1.1.22, 25 Nov 2016 =
* workaround for CloudFlare's Rocket Roader added

= 1.1.21, 21 Nov 2016 =
* support for reserved keywords in mysql's primary key

= 1.1.20, 17 Nov 2016 =
* automated email notifications when backup is ready

= 1.1.19, 12 Nov 2016 =
* better client-side error logging

= 1.1.18, 12 Nov 2016 =
* mysqld custom port support added
* mysqld socket support added

= 1.1.17, 3 Nov 2016 =
* readme updated, note about plugin issues on wpengine.com

= 1.1.16, 3 Nov 2016 =
* disk_free_space wpengine compatibility fixes again

= 1.1.15, 3 Nov 2016 =
* disk_free_space wpengine compatibility fixes

= 1.1.14, 26 Oct 2016 =
* better error message, link to our support board added

= 1.1.13, 26 Oct 2016 =
* readme.txt updated

= 1.1.12, 26 Oct 2016 =
* a polite, non-intrusive, permanently dismissible notice asking to tell about us to your friends on the 1st, 2nd and every 10th successfull restore added

= 1.1.11, 24 Oct 2016 =
* more improvements to download functionality

= 1.1.10, 24 Oct 2016 =
* downloaded archives potential corruption fixed

= 1.1.9, 23 Oct 2016 =
* fatal error handling improved
* wp-config.php reconfiguration fixed

= 1.1.8, 19 Oct 2016 =
* schedule delete fixed to support schedules for v1.1.0 - v1.1.2

= 1.1.7, 19 Oct 2016 =
* url rewrite fixed for subdomains
* gzdeflate bug fixed

= 1.1.6, 18 Oct 2016 =
* recovery panel auth fixes
* recovery panel field name fixed

= 1.1.5, 16 Oct 2016 =
* available schedules list cleanup

= 1.1.4, 16 Oct 2016 =
* warnings on Schedule page fixed
* minor text updates

= 1.1.3, 15 Oct 2016 =
* stop backup if less than 1M of free space left on disk

= 1.1.2, 09 Oct 2016 =
* bugfix for unlimited number of retained backups
* better url rewrite when cloning

= 1.1.1, 09 Oct 2016 =
* https://wordpress.org/support/topic/backup-failed-14/ fixed
* compatibility with mysql 5.7 improved

= 1.1.0, 05 Oct 2016 =
* scheduled backups support added
* multisite fixes

= 1.0.26, 29 Sep 2016 =
* uploads dir exclusion fixed for multisite

= 1.0.25, 29 Sep 2016 =
* uploads dir exclusion fixed

= 1.0.24, 26 Sep 2016 =
* improving EstimateFs task performance and memory consumption

= 1.0.23, 26 Sep 2016 =
* hard deadline cannot be longer than 25 seconds
* Changelog returned back to tab

= 1.0.22, 22 Sep 2016 =
* improving estimate task for very long sites

= 1.0.21, 25 Aug 2016 =
* readme.txt link to changelog fixed

= 1.0.20, 25 Aug 2016 =
* readme.txt link to changelog fixed

= 1.0.19, 24 Aug 2016 =
* mbstring.func_overload = 2 bugfix

= 1.0.18, 24 Aug 2016 =
* user-side error reporting added

= 1.0.17, 22 Aug 2016 =
* warning in WordpressCleanup task suppressed

= 1.0.16, 22 Aug 2016 =
* customizable email address to send backup links

= 1.0.15, 22 Aug 2016 =
* moving all plugins settings under same option key

= 1.0.14, 21 Aug 2016 =
* javascript / css caching issues addressed

= 1.0.13, 21 Aug 2016 =
* better listing
* better in-app help

= 1.0.12, 17 Aug 2016 =
* bugfix: uploads were always backed up

= 1.0.11, 17 Aug 2016 =
* sslverify disabled when detecting location for backups

= 1.0.10, 17 Aug 2016 =
* .maintenance file removal after restore

= 1.0.9, 17 Aug 2016 =
* Error handling improved
* Better detection of state storage and backup storage directories

= 1.0.8, 16 Aug 2016 =
* new menu icon

= 1.0.7, 16 Aug 2016 =
* readme.txt Features section added
* readme.txt FAQ updated

= 1.0.6, 16 Aug 2016 =
* Support for removal of backups in the site root (happens after duplication)
* FAQ updated
* Installation updated

= 1.0.5, 15 Aug 2016 =
* Minor improvements - size column added to backups list
* Minor bugfixes and improvements

= 1.0.4, 14 Aug 2016 =
* readme.txt fixed

= 1.0.3, 14 Aug 2016 =
* Multisite support - backup available to network admins only
* readme.txt FAQ section and description updated

= 1.0.2, 13 Aug 2016 =
* readme.txt updated

= 1.0.1, 13 Aug 2016 =
* readme.txt formatting fixes

= 1.0, 12 Aug 2016 =
* Initial release


[CHANGELOG](https://plugins.svn.wordpress.org/darwin-backup/trunk/CHANGELOG.txt)