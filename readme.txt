=== CBX Rating System ===
Contributors: manchumahara,codeboxr,wpboxr
Donate link: http://wpboxr.com
Tags: widget,shortcodes,rating,comment,reviews, multi criteria,ratingsystem
Requires at least: 3.0
Tested up to: 4.3
Stable tag: 3.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

 CBX Multi Criteria Rating System

== Description ==

 CBX Rating System for wordpress is a versatile and complete rating solution for your wordpress site .
 It helps you to get rating of your articles with additional customs question and comment option.

Features:

*   Unlimited rating forms. You can add,edit ,delete rating forms ,view all forms listing with it's review count ,select one form as default , change default form any time
*   While adding rating forms you will find huge options like form position ,shortcode enable /diaable , show/hide , users who can rate , posts where to show ,users who can view rating reviews and many more
*   Ip and cookie checking system to restrict repeated rating
*   You have 3 custom criteria with 5 custom users .Stars are selectable (show/hide), stars names and criteria names are editable
*   Comment box ,with required options and charecter limit
*   3 custom question of type textbox/radio/checkbox . Can show,hide questions , make it required and edit checkbox or radio numbers
*   User based and form based summary table with delete bulk actions
*   Form intigretion with short code , with meta box under post ,with direct function call in any where or in post loop , find result summary with direct function call
*   Top rated posts widget
*   Language file support and easily customizable


See more details and usages guide here http://wpboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress



Pro Version:

We have a pro version that gives more premium features beside the basic free version(Free version will be always free).

Pro Version Features:

*  Theme & Custom Style for rating presentation
*  Unlimited criteria
*  Unlimited reasons/stars in each criteria
*  Guest email verify



Plugin Backend
[youtube http://www.youtube.com/watch?v=Xa6M2uJnKVw]

== Installation ==

Please note: if you are updating to 3.3 from any other version you need to reset the rating tables. Go to Rating System from
left menu, then tools -> click reset all. It will give a fresh rating system that works. We needed to change the database tables for this plugin and changed a log
and couldn't keep the previous data to move for any better future of this plugin.

How to install the plugin and get it working.


1. Upload `cbratingsystem` folder  to the `/wp-content/plugins/` directory or as you define the wp-content directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Rating System-> Add rating Form  to create a  rating form
4. Explore the setting and create form , set the position ,edit criteria names ,star names and questions
5. View backend pages to view all ratng logs , forms listing
6. Read faqs and detail from our site to get the shortcodes and function

== Frequently Asked Questions ==

= Is it possible to restrict any user group from rating ? =

yes, there is setting in every form to set which user groups can rate .

= If i call direct function in post loop then how to hide default form   ? =

Select default form position setting s  as none ( positions are left /right /none)

= Can I show a different(not default ) form  in a post ? =

yes ,set the form with meta box under post .

= What are the shortcodes ? =

[cbratingsystem form_id = "" post_id = '' theme_key= '' showreview =1 ]
[cbratingavg form_id='' post_ids='' show_title=0 show_form_id=0 show_text=0 show_star=1 show_single=0, text_label]

= What is the function to add rating directly ? =

standalonePostingRatingSystem( $form_id = '', $post_id = '', $theme_key = '', $showreview = true );
standalone_singlePost_rating_summary( $option, $show_title = 1, $show_form_id = 1, $show_text = 1, $show_star = 1, $show_single = 0 , $text_label = '');
Please visit our site for detail tutorial step by step http://codeboxr.com/installation-multi-criteria-flexible-rating-system-for-wordpress.html




== Screenshots ==

1. Plugin listing in plugin manager
2. Rating System menu after activate the plugin
3. Rating form listing with 'add new form button'
4. Rating Form edit view-1
5. Rating Form edit view-2
6. Rating Form edit view-3(permissions)
7. Rating Form edit view-3(Criteria)
8. Rating Form edit view-4(Enable disable comments)
9. Post meta box for enable disable rating for any specific posts
10. Frontend Rating Form-1
11. Frontend Rating Result
12. Frontend Rating Reviews/Comments
13. Frontend Rating Form-2
14. Backend Rating Logs
15. Backend Average Rating
16. Theme Manager for Rating System
17. Rating System Tools to control tables and options values



== Changelog ==
= 3.4.2
* Backend js added as per the menu or screen
* Fixed question edit issues
= 3.4.0
* php5 style widget constructor as per wordpress 4.3 requirement
* Fix "Top Rated Posts" widget, form was not saving properly.
= 3.3.7 =
* Google Rating Schema or Rich Snippet Added
* Bug fix for avg rating when form id is not default(we are sorry, it should not be)
= 3.3.5 =
* While submitting review if comment char limit crossed the allowed size then it was showing comment cut wrong
* No more read more link in ajax comment preview
* Added stripslashes for user rating logs in admin
= 3.3.4 =
* \' type character is taken care in comment
= 3.3.3 =
* Fixed php warning for woocommerce tab replace
= 3.3.2 =
* Review shown login improved for who is allowed and if shown own review
* Ajax security updated
* Bug fix for question display in backend and frontend , store answer based on it on ajax and normal display
= 3.3.1 =
* bug fix for  extra field not array
= 3.3.0 =
* 50% revamp of code, please go to tools of this plugin and reset
= 3.2.26 =
* Bug fix
= 3.2.24 =
* Maintenance Release

