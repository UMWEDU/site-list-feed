# Multisite Site List Feed #
**Contributors:** cgrymala

**Donate link:** http://giving.umw.edu/

**Tags:** multisite, network, site list, json

**Requires at least:** 3.0.1

**Tested up to:** 4.2.1

**Stable tag:** 0.1

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


Outputs a JSON feed of all sites registered in a WordPress Multisite installation

## Description ##

This plugin retrieves a list of all sites registered in a WordPress Multisite installation and outputs them as a JSON feed to be potentially consumed by other sites.

The list of sites is structured as a flat array of indexed arrays. The output includes the `blog_id`, the `domain`, the `path` and the value of the `public` parameter for each site. The output looks similar to (formatted below for easier readability; in reality, it will all be output on 1 line):

```json
  [
    {
      "blog_id":"1",
      "domain":"example.org",
      "path":"\/",
      "public":"1"
    },{
      "blog_id":"2",
      "domain":"example.org",
      "path":"\/blog\/",
      "public":"1"
    },{
      "blog_id":"3",
      "domain":"example.org",
      "path":"\/code\/",
      "public":"0"
    }
  ]
```

The feed is available at /feed/site-feed.json within your site. For example, if your site is located at http://www.example.org/, the JSON feed will be available at http://www.example.org/feed/site-feed.json.

This plugin will not include any sites that have been marked as "Archived", "Spam", "Mature" or "Deleted" within the multisite installation. If you happen to have a system in place that offers more "Privacy" features for your sites (such as [Network Privacy](https://wordpress.org/plugins/network-privacy/)), only sites that are marked as public or hidden from search engines will be included (in other words, if a site is set to be available only to subscribers, it won't show up in the feed).

The list of sites is cached, by default, for 24 hours.

## Installation ##

1. Upload `site-list-feed.php` to the `/wp-content/mu-plugins/` directory
1. Do not upload any of the other files within this plugin folder to your site
1. Visit /feed/site-feed.json on your site to load up the list of sites

## Frequently Asked Questions ##

### Why don't I see a new site I registered in the feed? ###

There are a few possibilities:

1. The `public` parameter of the site may be set to something less than 0 (which is expected if you have a plugin like "Network Privacy" activated, and have the site set to anything other than "Allow search engines to index this site" or "Discourage search engines from indexing this site")
1. The site has been marked as Archived, Spam, Deleted or Mature
1. The feed may be cached; in this case, the updated list should appear within 24 hours, by default

### How do I adjust the length of time the feed is cached in the database? ###

You can use the `ms-site-feed-list-transient-timeout` filter to change the amount of time the feed is cached. By default, the feed is cached for a `DAY_IN_SECONDS` (24 hours).

### Why is my feed sending a 404 status code? ###

If you attempt to visit your feed at /site-feed.json (e.g. http://example.org/site-feed.json), WordPress will send a 404 status code. Instead, you need to visit the feed at http://example.org/feed/site-feed.json.

## Changelog ##

### 0.1 ###
* Initial version
