=== WP Simple SEO Meta ===
Contributors: epigrade
Donate link: https://www.epigrade.com/
Tags: seo, meta, page title, meta description, meta keywords, meta robots
Requires at least: 4.4.0
Tested up to: 5.2.1
Requires PHP: 5.4
Stable tag: 1.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Add page title, meta description, keywords and robots to all post types and taxonomies.

== Description ==

Add page title, meta description, keywords and robots to all post types and taxonomies.
Plugin will automatically change page titles for posts, pages, custom posts, categories, tags and custom taxonomies to
whatever you have entered. Also will inject meta fields in template head section. If however you leave fields empty
default WordPress or theme behaviour will occur.
This plugin also removes taxonomy description column from listing table.

You can use it in your theme or other plugin. It creates the following taxonomy and post meta fields:
`_page_title`, `_meta_description`, `_meta_keywords`, `_meta_robots`.

Example usage:

    // Get page title value for taxonomy with ID $term_id:
    $taxonomy_title = get_term_meta( $term_id, '_page_title', true );

    // Get page title value for post with ID $post->ID:
    $post_title = get_post_meta( $post->ID, '_page_title', true );

== Changelog ==

= 1.1.0 =
* Fixed readme.md headings.
* Renamed readme.txt to readme.md in git repo.
* Fixed version number in plugin file.
* Fixed readme.txt stable tag version from trunk to numeric.
* Fixed readme.txt changelog info.
* Input sanitization, override of titles and injection of meta tags in head section.

= 1.0.0 =
* Added readme.txt.
* Full plugin code rewritten using OOP singleton.

