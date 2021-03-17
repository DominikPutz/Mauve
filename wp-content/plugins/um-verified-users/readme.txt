=== Ultimate Member - Verified Users ===
Author URI: https://ultimatemember.com
Plugin URI: https://ultimatemember.com/extensions/user-tags/
Contributors: nsinelnikov
Tags: verification, user-profile, user-registration
Requires at least: 5.0
Tested up to: 5.2
Stable tag: 2.0.5
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

Add a user verification system to your site so users can request verification and be manually verified by site admin.

== Description ==

Add a user verification system to your site so users can request verification and be manually verified by site admin.

= Key Features: =

* Users can submit request to have their account verified
* Users can cancel verification request
* Display global activity wall anywhere on site via shortcode
* Provides a verification url so admin can add verification request link anywhere on site
* Admin can manually verify any user from admin without user request for verification
* Adds sort option to directories to allow admin to display verified users first
* Verified users will have blue verification badge appear on their profile and on member directories
* User will receive an email once their account has been verified
* Admin will receive an email notification when a user requests verification
* Adds a filter to users page in admin so admin can filter users to show all users awaiting verification

= Integrations with notifications extension: =

* Notifies a user when their account has been verified

= Integrations with Social Activity: =

* Shows activity when a user is verified

Read about all of the plugin's features at [Ultimate Member - Verified Users](https://ultimatemember.com/extensions/verified-users/)

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/article/184-verified-users-setup) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Verified Users to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/article/184-verified-users-setup) page.

== Changelog ==

= 2.0.5: November 11, 2019 =

* Tweak: Integration with Ultimate Member 2.1.0
* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Added: ability to change templates in theme via universal method UM()->get_template()
* Fixed: uninstall process
* Fixed: replace placeholders

= 2.0.4: November 12, 2018 =

* Fixed: verify account template
* Fixed: verify process after profile completeness
* Optimized: CSS enqueue

= 2.0.3: July 3, 2018 =

* Optimized: CSS loading
* Fixed: some CSS styles

= 2.0.2: April 27, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory

= 2.0.1: April 2, 2018 =

* Fixed: Autoverify users after registration
* Tweak: UM2.0 compatibility

= 1.0.8: January 3, 2016 =

* New: allow bulk verify/unverify for user accounts in backend
* New: added option to prevent specific user role from requesting verification

= 1.0.7: December 15, 2015 =

* Fixed: missing email template argument

= 1.0.6: December 11, 2015 =

* Tweak: compatibility with WP 4.4

= 1.0.5: December 8, 2015 =

* Initial release