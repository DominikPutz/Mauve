=== Ultimate Member - Social Activity ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/social-activity/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link:
Tags: user wall, activity, community, discussion
Requires at least: 5.0
Tested up to: 5.2
Stable tag: 2.2.0
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

Increase engagement and allow users to interact with each other by adding an activity system to your site where users can create posts on their wall and see what other users are up to on your site.

== Description ==

Increase engagement and allow users to interact with each other by adding an activity system to your site where users can create posts on their wall and see what other users are up to on your site.

= Key Features: =

* Allows users to create public wall posts
* Adds an activity tab to user profile which shows a user’s activity
* Users can post on another user’s profile
* Display global activity wall anywhere on site via shortcode
* Display activity wall for current logged in user anwhere on site via shortcode
* Users can create a post and attach a photo to post
* Users can edit wall posts from front-end
* Embeds YouTube & Vimeo videos directly onto activity stream if user posts a url
* Users can add hashtags to posts & users can click on hashtag to see all posts including that hashtag
* Users can comment & like posts and also reply to comments
* Ajax pagination for wall posts and loading more comments
* Users can report specific wall posts
* Users can make their wall private or viewable by members only via account page
* Admin can view & manage all wall posts in backend via custom cpt
* Allows admin to turn off social wall for specific role(s)
* Prevent a specific user role(s) from creating new posts, uploading a photo or commenting on posts
* Control number of wall posts to appear by default in desktop or mobile view before load more is triggered
* Option to turn on/off option for users to set their activity wall privacy via account page
* Ability for admin to decide which activity types show on activity stream
* Show when a new user registers on site (optional)
* Show when a user creates a new blog post (optional)

= Integrations with followers extension: =

* Show when a user follows another user in activity
* Allow user to make their wall available to his followers only, or people they follow

= Integrations with notifications extension: =

* Notify when user post on user wall
* Notify when user likes their wall post
* Notify when user comments their wall post

= Integrations with WooCommerce: =

* Shows in activity when someone adds a new product (includes item name and price)

= Integrations with bbPress: =

* Show when someone creates a new forum topic

Read about all of the plugin's features at [Ultimate Member - Social Activity](https://ultimatemember.com/extensions/social-activity/)

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](https://docs.ultimatemember.com/article/229-how-to-limit-wall-posts-in-social-activity?) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/article/229-how-to-limit-wall-posts-in-social-activity?) page.

== Changelog ==

= 2.2.0: November 11, 2019 =

* Fixed: Post update activity
* Fixed: 'the_title' filter using
* Fixed: set image data from URL meta
* Fixed: images and hashtags parsing in the post content

= 2.1.9: August 30, 2019 =

* Fixed: Security issues connected with XSS and not sanitized values
* Fixed: First loading of posts via AJAX
* Tweak: Using UM()->get_template() function instead own to make the templates using more complex

= 2.1.8: July 16, 2019 =

* Added: Emoji to activity post content
* Fixed: Profile Tabs
* Fixed: Uninstall process
* Fixed: Mime types filter
* Fixed: Title of the comment

= 2.1.7: May 14, 2019 =

* Tweak: Enqueue scripts in one function and hook
* Fixed: Scroll library enqueue
* Fixed: Login/Register links
* Fixed: Link to user
* Fixed: Edit link at the wall
* Fixed: Hashtag wall

= 2.1.6: March 28, 2019 =

* Added: Friends user suggestions

= 2.1.5: February 8, 2019 =

* Fixed: Upload Path

= 2.1.4: November 27, 2018 =

* Fixed: AJAX vulnerabilities
* Fixed: Edit single activity post
* Fixed: Download links rewrite rules in activity wall

= 2.1.3: November 18, 2018 =

* Fixed: PHP errors in empty functions

= 2.1.2: November 12, 2018 =

* Fixed: async wall posting with links preview
* Fixed: JS/CSS enqueue
* Fixed: Minor CSS fix at activity page

= 2.1.1: October 24, 2018 =

* Fixed: Confirm Box on Activity Page

= 2.1.0: October 24, 2018 =

* Fixed: Like/Unlike post async
* Fixed: Uploading images with active cache
* Fixed: Include templates for not-logged in users
* Optimized: JS/CSS enqueue

= 2.0.11: October 11, 2018 =

* Fixed: Trending Hashtags widget

= 2.0.10: October 8, 2018 =

* Fixed: Activity Wall posts
* Fixed: Some Typo

= 2.0.9: October 2, 2018 =

* Fixed: Optimization + fixed small bugs on User Wall & Activity Wall
* Fixed: async JS handlers
* Fixed: Comments & Replies handlers
* Fixed: Wall privacy

= 2.0.8: August 15, 2018 =

* Fixed: async loading for not logged in users

= 2.0.7: August 13, 2018 =

* Fixed: WP native AJAX handlers
* Fixed: Image sizes and URLs
* Fixed: Typo

= 2.0.6: August 9, 2018 =

* Fixed: Image Uploader
* Fixed: Styles for wall shortcode
* Fixed: Using the 2 or more shortcodes at the same page

= 2.0.5: July 10, 2018 =

* Fixed: Autocomplete via @ symbol

= 2.0.4: July 3, 2018 =

* Added: Max faces setting
* Added: Verify badge in display name of the activity author
* Fixed: Link output with $_GET params

= 2.0.3: July 3, 2018 =

* Fixed: Break words styles

= 2.0.2: December 16, 2017 =

* Added: Loading translation from "wp-content/languages/plugins/" directory

= 2.0.1: October 30, 2017 =

* Added: GravityForms add form/submit form activities
* Fixed: Chinese and Russian Hashtags working
* Fixed: Activity wall privacy
* Tweak: UM2.0 compatibility

= 1.3.3: January 4, 2017 =

* Fixed: plugin updater
* Fixed: activity tab visibility issue
* Added: post comment reply in the notification


= 1.3.2: December 11, 2016 =

* Tweak: Update nl_NL translation files
* Fixed: plugin updater
* Fixed: remove notices
* Fixed: Activity tab's privacy
* Fixed: remove notices from Ajax request
* Fixed: UM Friends integration

= 1.3.1: October 10, 2016 =

* New: UM Friends integration
* New: oEmbed integration
* Tweak: update EDD plugin updater
* Fixed: upload nonce issue
* Fixed: mixed with recent comments widgets
* Fixed: remove notices
* Fixed: load posts on scroll
* Fixed: saving privacy options
* Fixed: update blog post in activity feed
* Fixed: post and comment clickable links in content
* Fixed: links and video embed in posts
* Fixed: only show followed users activity in the social wall option
* Fixed: remove Visual Composer shortcodes from Post excerpts

= 1.3.0: June 20, 2016 =

* Fixed: EDD class for multisite setup

= 1.2.9: June 20, 2016 =

* Fixed: Invalid nonce

= 1.2.8: June 9, 2016 =

* New: display activities in RSS feeds
* New: PressThis tool integration
* Added: new filter to activity post time  'um_activity_human_post_time'
* Added: Dutch / NL translation
* Fixed: delete activity when bbpress topic is removed
* Fixed: profile activity content
* Fixed: title in recent comment widget
* Fixed: tab template
* Fixed: video responsiveness
* Fixed: edit post
* Fixed: post image max width
* Fixed: exclude private walls
* Fixed: license and updater
* Fixed: reply to replies
* Fixed: login string
* Fixed: edit comment reply
* Fixed: comment author
* Fixed: remove notices
* Fixed: translation strings
* Fixed: trailing slashes issue
* Fixed: displaying activities

= 1.2.7: January 25, 2016 =

* Fixed: Remove forum topic when removed from bbpress
* Fixed: Remove activity's hashtag if it's not mentioned anymore

= 1.2.6: January 5, 2016 =

* Fixed: Replace embed media with link when uploaded an image
* Fixed: Add hashtag in comment content after submit
* Fixed: Fixed title encoding to allow quotes characters
* Fixed: Fix hashtag for html special chars

= 1.2.5: December 22, 2015 =

* New: allow hashtags in wall comments
* New: allow soundcloud embeds by track url
* Fixed: remove shortcodes from excerpts
* Fixed: regex for parsing links

= 1.2.4: December 18, 2015 =

* New: user can delete their comments

= 1.2.3: December 11, 2015 =

* Fixed: issue in accessing admin screen
* Fixed: broken social activity avatar

= 1.2.2: December 8, 2015 =

* New: integration/sync with our new Verified extension
* Fixed: bug with wall post permalink

Version 1.2.1

* Fixed: removes post activity when post status is not published

Version 1.2.0

* Fixed: sharing link and photo in one post conflict
* Fixed: css bugs

Version 1.1.9

* Tweak: updated language catalogs
* Fixed: Invalid HTTP requests

Version 1.1.8

* Fixed: comments display on new posts

Version 1.1.7

* Fixed: link sharing for internal links
* Fixed: comments were not showing on some websites

Version 1.1.6

* New: view shared links in the backend and moderate them like normal posts
* Tweak: improved link sharing from the web
* Fixed: video embeds and link sharing conflicts
* Fixed: hashtag feed shows all posts by hashtag

Version 1.1.5

* Tweak: better activity templates when adding new post/product/forum topic

Version 1.1.4

* New: added option to make public wall show activity from followed users only
* New: added link sharing feature from any webpage
* Tweak: updated language files

Version 1.1.3

* New: completely revoked and improved hashtag system
* New: added widget to display trending hashtags
* New: easily access posts by hashtags and see how many posts each hashtag has got

Version 1.1.2

* New: extended integration with followers
* New: you can mention followers/followed users in your wall post
* New: mention users with autocomplete support
* New: mentioned users get a web notification (requires UM Notifications)
* New: improved wall post/comment linking with permalinks
* New: set comment/reply order by newest first or oldest first in social posts
* Tweak: sorting comments by oldest comment first by default (optional)
* Tweak: improved post actions menu
* Fixed: comments count should not include replies
* Fixed: wall comments should not appear in comments tab in profiles

Version 1.1.1

* New: added option to delete wall posts via frontend

Version 1.1.0

* New: added ajax/inline frontend post editing capability

Version 1.0.8

* New: added Swedish language support
* New: added filter to control post submission args um_activity_insert_post_args
* New: added filter to control WP_Query args to display wall posts um_activity_wall_args
* Fixed: Truncated text not working for activity posts

Version 1.0.7

* Fixed: minor bug fixes with shortcodes

Version 1.0.6

* Fixed: issue with wall loading more posts

Version 1.0.5

* New: show when user creates blog post
* New: show when user creates a product
* New: show when user follows someone
* New: show when user creates a forum topic
* New: show when user signs up
* New: integration with notifications add-on

Version 1.0.4

* New: option to turn off wall privacy settings in account page
* Tweak: allow user to get redirected to wall post after login
* Fixed: php issue in comments display

Version 1.0.3

* Fixed: post truncate is resolved

Version 1.0.2

* New: show comment/reply likes through modal
* Tweak: likes modal is customizable via template
* Fixed: hashtag view in main activity

Version 1.0.1

* New: user can post directly from activity page
* New: display user wall or general activity with shortcode anywhere on site
* Tweak: added default max-width css to activity wall