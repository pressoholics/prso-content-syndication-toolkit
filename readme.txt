=== Content Syndication Toolkit ===
Contributors: ben.moody
Tags: content syndicator,content marketing,content syndication,content aggregator,content aggregation,content publisher,syndication network,aggregator network,seo,content publishing
Requires at least: 3.0
Tested up to: 4.1
Stable tag: 1.0.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Content Syndication Toolkit allows you to syndicate content to multiple client sites automatically. Posts, Categories, Tags, and Images.

== Description ==

= Are you a content creator, freelance author, or SEO content marketer? =

Do yo need to syndicate content to your client's Wordpress sites?

Want to make content marketing to multiple clients a breeze?

Automatically add Canonical links back to original content!

Content Syndication Toolkit allows you to syndicate content to multiple client sites automatically. 

Just create an account for each client, publish your post and all the content, categories, tags and images will be pushed to all registered clients. This is a content marketing wordpress plugin which makes mass content syndication quick and simple.

[youtube http://www.youtube.com/watch?v=IDhMirwfnLc]

= Perfect for Content Marketing, Auto Canonical link generation! =

Plugin automatically adds a canonical link to the 'head' every imported post at the clients end.

It also adds a link back to the original post at the end of the content:

e.g. This article was first published on http://benjaminmoody.com.

= This is NOT an RSS aggregator plugin! =

All requests for content are authenticated with a username and password exactly like a Wordpress login. This means that only clients that you have registered can access the content. 

= Need to stop content going to a client? =

Just remove their account or change the user group. You are in control of who can receive your content.


= Full complete content import =

When a push is made to a client's site, Wordpress will import not only the post content (title, content, excerpt, meta data) it will also import any categories and tags applied to each post. As the images, the client plugin will detect all images used in each post and import those images directly into the client's media library!

= How does it work? =

* Just install this 'master' plugin on in your Wordpress install.
* Now create some client accounts, works just like adding any other Wordpress user. Remember to add their website URL into the website field!
* Provide your client with their account username and password, as well as your website URL
* Direct your clients to download the 'Content Syndication Toolkit Reader' plugin and enter in their account login.
* Create your posts under the new 'Syndication Posts' post type.
* Each time your publish a 'Syndication Post', the plugin will automatically push the post content to all registered users!


= Speed is key =

Content Syndication Toolkit is designed to ensure your master server does as little work as possible when pushing out content. All it does is 'ping' each client with a simple quick http request. The client server then jumps into action, authenticating with the master server which runs a quick query and provides a list of content to import.

The client site then does the heavy lifting, importing the post data and downloading any images from your server without Wordpress getting involved, thus keeping server load to a minimum.


= Problems? Got you covered =

Sometimes web servers go down or take a while to respond. If there is any issue with pushing content to a client, the plugin will send an email to you with the details of the error and it will also send an email to the client admin with instructions on how to manually pull in the content using the client plugin admin options.

== Installation ==
1. Upload to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the post type you wish to syndicate in Settings > Content Syndication menu in Wordpress. By using the default post type you can keep your syndication posts seperate from your default blog posts OR you can choose to syndicate all default Wordpress Posts.
4. Create client user accounts under 'Users' menu in Wordpress. Be sure to select the user role 'Syndication Sub' AND add the client's website URL into the user website field. If you forget this content push to this client will fail.
5. Provide clients with their login info, and your website URL.
6. Have clients download and active the 'Content Syndication Toolkit Reader' plugin from the Wordpress plugin directory.
7. Tell your clients to follow the directions for the 'Content Syndication Toolkit Reader' plugin. That is to say, enter in their username, password, YOUR website URL, add select a default author account.
8. That's it, every time you publish a post in the new 'Syndication Posts' post type. All the post content will automatically be pushed to all registered users in the 'Syndication Sub' role.

== Frequently Asked Questions ==

= How to I display the archive for all my syndication posts? =
In your wordpress Menu add a link to /syndication-post/ like this:

http://www.YourWebsiteURL.com/syndication-post/ (replace www.YourWebsiteURL.com with your websites URL)

= Can i create draft posts without pushing them to clients? =
Yes. only 'Syndication Posts' that are marked as published will be pushed to clients. So be careful when you push the blue publish button, be sure you want the post to go out. If not sure just 'save as draft' and publish later when you are ready.

= Can i correct mistakes in posts =
No, this is a one way process. Think of it as an email campaign, once is goes out mistakes can't be corrected. Be sure before you publish!

= Can people read these posts on the front end like any other post? =
Yes, all 'Syndication Posts' published on your site. All client copies of posts are automatically marked with a Canonical tag pointing back to the original post on your site.

= Do i have to add my posts as 'Syndication Posts' cant i use normal posts? =
In this free version yes, you have to use the custom 'Syndication Posts' post type. In the PRO version, there are many more features, selecting to publish from the default 'posts' post type is just one of them.

= What happens when i delete a post? =
Once deleted a post will no longer be pushed to clients. If a client already has a copy of this post it will remain live on their site along with any images.

= Is the content served from my Wordpress install? =
No, the import process is exactly that. A complete import of posts, categories, tags, and images for each post pushed to a client. So each client has their very own copy of each post inside their Wordpress database.

= What happens if a push goes wrong? =
Sometimes web servers crash or are slow to respond.

If your site cannot reach a client during a push it will send you and email with the account name and error details. It will also send your client and email with instructions on how to manually start a content pull from their wordpress admin area.

== Changelog ==

= 1.0 =
Initial plugin launch.

= 1.0.1 =
Added canonical link generation for client posts.

= 1.0.2 =
Small bugfix for canonical links

= 1.0.3 =
Bugfix for canonical pretty permalinks. Improved push speed on post publish.

= 1.0.4 =
New option added (Settings > Content Syndication menu in Wordpress). You can now choose to syndicate Wordpress Posts instead of custom Sydication Post type.

== Upgrade Notice ==

= 1.0.2 =
Major important bugfix. Also added canonical links at bottom of posts at client end.

= 1.0.3 =
Bugfix for canonical pretty permalinks. Improved push speed on post publish.

= 1.0.4 =
New option added (Settings > Content Syndication menu in Wordpress). You can now choose to syndicate Wordpress Posts instead of custom Sydication Post type.
