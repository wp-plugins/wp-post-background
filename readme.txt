=== WP Post Background ===
Contributors: jcchavezs
Tags: plugin, post, background, attachment
Requires at least: 3.0
Tested up to: 4.2.2
Stable tag: trunk

== Description ==

This plugin allows you to add and image as background of posts, pages or custom post type in general (in the same way as a thumbnail).

* This is a rewrite of my old plugin [Post Background](https://wordpress.org/plugins/post-background/) which I do not have access anymore. *

== Installation ==

1. Download the plugin from [here](http://wordpress.org/extend/plugins/wp-post-background/ "Post brackground").
1. Extract all the files.
1. Upload everything (keeping the directory structure) to the `/wp-content/plugins/` directory.
1. There should be a `/wp-content/plugins/wp-post-brackground` directory now with `wp-post-brackground.php` in it.
1. Activate the plugin through the 'Plugins' menu in WordPress. A box will be added to the page edition.

== Frequently Asked Questions ==

= How it works? =

Exactly in the same way that thumbnails works. It includes the `get_the_post_background_src` function which return the url of the background and the `get_the_post_background_id` function which return the id of the attachment.

= Do I have embed the same background if I a page inherits its parent background? =

No. You can pass true as second parameter on `get_the_post_background_src` function.

== Screenshots ==

1. The background box for a post

== Changelog ==
- 1.0.0 Initial release