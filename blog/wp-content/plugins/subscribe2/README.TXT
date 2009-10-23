== subscribe2 ==

Tags: subscribe, email
copyright (c) 2004 Scott Merrill
skippy@skippy.net | http://skippy.net/
Released under the terms of the GNU General Public License.
Distributed with no warranty.  Use it at your own risk.

This plugin provides a comprehensive subscription management system for WordPress blogs.  Visitors can subscribe and unsubscribe through a convenient web-based form.  All requests require confirmation by email.
Administators can configure the email template used for new post notifications, as well the messages used for (un)subscription requests.  Admins can also subscribe or unsubscribe users, as well as send out an email to all confirmed subscribers.
New in version 2.2, registered users of the blog can subscribe to individual categories!

== INSTALLATION ==
1) Copy the entire /subscribe2/ directory into your /wp-content/plugins/ directory.
2) Activate the plugin.
3) Click the "Options" admin menu link, and select "Subscribe2".
4) Configure the options to taste, including the email template and any categories which should be excluded from notification
5) Click the "Manage" admin menu link, and select "Subscribers".
6) Manually subscribe people as you see fit.
7) Create a WordPress Page (http://codex.wordpress.org/Pages) to display the subscription form.  When creating the page, you may click the "S2" button on the QuickBar to automatically insert the subscribe2 token.  Or, if you prefer, you may manually insert the subscribe2 token:
     <!--subscribe2-->
This token will automatically be replaced by the subscription form, and will display all messages as necessary.

== UPGRADING ==
If you are upgrading from any 2.1.x version of subscribe2, please delete both the /subscribe.php and /wp-content/plugins/subscribe2.php files before copying the new files into place.  Your subscriber list will remain intact, and your options should be preserved.

Create the WordPress Page as described above.

== USAGE ==
Subscribe2 supports two classes of subscribers: the general public, and registered users of the blog.  The general public may subscribe and unsubscribe.  They will receive an email notification whenever a new post is made (unless that post is assigned to one of the excluded categories you defined).  The general public will receive a plaintext email with an excerpt of the post: either the excerpt you created when making the post, the portion of text before a <!--more--> tag (if present), or the first 55 words of the post.

Registered users of the blog can elect to receive email notifications for specific categories.  The Users->Subscription menu item will allow them to select the delivery format (plaintext or HTML), amount of message (excerpt or full post), and the categories to which they want to subscribe.  You, the blog owner, have the option (Options->Subscribe2) to allow registered users to subscribe to your excluded categories or not.

When you post a new item, subscribe2 will generate (at least) one email for each of the following combinations of subscribers:
  * plaintext excerpt
  * plaintext full post
  * HTML full post
So for each new post you make, you may receive up to three emails.

***********************
** AN IMPORTANT NOTE **
***********************
Some hosting providers place a restriction on the maximum number of recipients in any one email message.  For example, the venerable Dreamhost (http://www.dreamhost.com/) does not allow any message to contain more than 30 recipients.

Subscribe2 provides a facility to work around this restriction by sending batches of emails.  To enable this feature, edit subscribe2.php in a text editor and go to line 36:
     define('DREAMHOST', FALSE);
Change the "FALSE" to "TRUE" (without the quotation marks).

Reminder: because subscribe2 places all recipients in BCC fields, and places the blog admin in the TO field, the blog admin will receive one email per batched delivery.  So if you have 90 subscribers, the blog admin should receive three post notification emails, one for eah set of 30 BCC recipients.

Batches will occur for each group of message as described above.  A site on Dreamhost with many public and registered subscribers could conceivably generate a lot of email for your own inbox.

(Many thanks to Wade Emmert and Joe Messanini for their patience and their assistance in testing this functionality!)

== TRANSLATION ==
Translations are available at
   http://www.skippy.net/trac/browser/trunk/subscribe2/i18n/

If you create a translation for subscribe2, please email me (skippy@skippy.net) so that I can add it to the collection!!
The POT file can be found at the URL above.

See here for additional localization information:
  http://codex.wordpress.org/Localizing_WordPress

== Frequently Asked Questions ==
Q. No emails are sent!
A. In the overwhelming majority of cases so far, this is caused by spam filters at the recipient end incorrectly flagging messages as spam.  I can almost never help resolve these problems.  First, check with your hosting provider to determine if the mail was received by their server, and sent out.  Then check with the hosting provider(s) of the recipient(s) to determine if the message was received.

Q. I posted an item dated for tomorrow, but no notification was sent!
A. Future-dated posts do not generate notification emails.

Q. Can I place the subscription form in my sidebar?
A. You can place the HTML for the form manually, but you cannot place the <!--subscribe2--> token in your sidebar.  Make sure your form POSTs to a WordPress Page containing the <!--subscribe2--> token.
