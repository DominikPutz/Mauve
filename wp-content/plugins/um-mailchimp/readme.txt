=== Ultimate Member - MailChimp ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/mailchimp/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link: 
Tags: mailchimp api connect, email, audience, community
Requires at least: 5.0
Tested up to: 5.2
Stable tag: 2.2.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

This extension integrates MailChimp with Ultimate Member and allows users to subscribe to your mailing lists when they register on your site.

== Description ==

This extension integrates MailChimp with Ultimate Member and allows users to subscribe to your mailing lists when they register on your site.

= Key Features: =

* Automatically add users to a MailChimp list when they register.
* Allow users to opt-in to subcribing to a MailChimp list when they register.
* Add multiple lists to register form and allow users to select which lists they subscribe to.
* Show different lists on different register forms.
* Sync user meta with MailChimp list.
* Allow users to subscribe/unsubcribe from their account page.
* Restrict lists to certain roles (Only roles that can subscribe/unsubcribe to a list will see it on their account page).

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > MailChimp to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/article/82-mailchimp-setup) page.

== Changelog ==

= 2.2.1: November 13, 2019 =

* Fixed: Double opt-in: added "filter_subscription_status" method

= 2.2.0: November 11, 2019 =

* New: MailChimp's Groups support
* New: MailChimp's Tags support
* Added: Update Notifications notices
* Added: Requests cache
* Fixed: Requests log
* Fixed: Testing connection tool
* Fixed: Sync Profiles tool Users cache

= 2.1.2: August 3, 2019 =

* Fixed: JS error on Mailchimp widget dashboard

= 2.1.1: December 7, 2018 =

* Fixed: Subscription on change users role

= 2.1.0: November 30, 2018 =

* Added: MailChimp List testing actions

= 2.0.9: November 1, 2018 =

* Fixed: MailChimp API integration
* Fixed: Translation Files

= 2.0.8: November 1, 2018 =

* Fixed: MailChimp API integration

= 2.0.7: August 13, 2018 =

* Fixed: WP native AJAX handlers

= 2.0.6: August 3, 2018 =

* Fixed: MailChimp Queries Queue
* Fixed: Logic for auto subscribe process
* Fixed: Small warnings & bugs

= 2.0.5: July 11, 2018 =

* Deprecated: old Sync&Opt-in process
* Added: New batch opt-in users to MailChimp lists
* Added: New sync users with MailChimp lists

= 2.0.4: June 18, 2018 =

* Added: GDPR compatibility
* Added: Double opt-int settings to MailChimp lists

= 2.0.3: May 18, 2018 =

* Fixed: Statuses after registration
* Fixed: Problem with subscription to list after activation process

= 2.0.2: April 27, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory

= 2.0.1: October 17, 2017 =

* Tweak: UM2.0 compatibility
* Fixed: Users sync on add/edit/update profile actions

= 1.1.8: July 28, 2017 =

* New: MailChimp API v3 Integration
* New: Scan opted-in users but not yet synced
* New: Scan not yet opted-in users
* New: Sync opted-in users
* New: Opt-in profiles to specifc list on behalf of users
* Fixed: resubscribe users in account page
* Fixed: split sync process and show progress
* Fixed: EDD plugin updater
* Fixed: remove notices

= 1.1.7: November 6, 2016 =

* Fixed: empty email lists

= 1.1.6: December 8, 2015 =

* Initial release