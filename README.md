
# Yoast to REST API - WordPress plugin

# Install

```
composer require mupic/wp-api-yoast-meta
```

For use with the new [WP REST API](http://v2.wp-api.org/)

Returns Yoast post or page metadata in a normal post or page request. Stores the metadata in the `yoast_meta` field of the returned data.

# Default constants
```
//GET params will take precedence over constants
define('YOAST_REST_META', true); //false - Disable automatic meta seo input
define('YOAST_REST_OG', false); //false - Disable automatic open graph input
define('YOAST_REST_TW', false); //false - Disable automatic meta twitter input
```
```
//wp-json/wp/v2/posts/123?yoast_meta=true&yoast_opengraph=true&yoast_twitter=true
{
	id: 123,
	...
	yoast_meta: {
		article:modified_time: "2019-04-01T16:22:10+00:00"
		article:published_time: "2019-01-10T13:05:30+00:00"
		article:section: "News"
		article:tag: ["Tag1", "Tag2"]
		og:description: "Description"
		og:image: ".../wp-content/uploads/2019/01/6JHYYbvoSuQ95ceGx8Oeg8zzAjg-550x309.jpg"
		og:image:height: "309"
		og:image:width: "550"
		og:locale: "en_US"
		og:site_name: "Site"
		og:title: "Title - site"
		og:type: "article"
		og:updated_time: "2019-04-01T16:22:10+00:00"
		og:url: ".../news/title/"
		twitter:card: "summary"
		twitter:description: "Description"
		twitter:image: ".../wp-content/uploads/2019/01/6JHYYbvoSuQ95ceGx8Oeg8zzAjg.jpg"
		twitter:title: "Title - site"
		yoast_wpseo_canonical: ".../news/title/"
		yoast_wpseo_metadesc: "Description"
		yoast_wpseo_title: "Title - site"
	}
}
```

Supports pages, posts, any *public* custom post types, categories, tags, any *public* custom taxonomies

Currently fetching:

- `yoast_wpseo_title`
- `yoast_wpseo_metadesc`
- `yoast_wpseo_canonical`
- `article:modified_time`
- `article:published_time`
- `article:section`
- `article:tag`
- `og:description`
- `og:image`
- `og:image:height`
- `og:image:width`
- `og:locale`
- `og:site_name`
- `og:title`
- `og:type`
- `og:updated_time`
- `og:url`
- `twitter:card`
- `twitter:description`
- `twitter:image`
- `twitter:title`
- `yoast_wpseo_canonical`
- `yoast_wpseo_metadesc`
- `yoast_wpseo_title`

Currently updating:

- `yoast_wpseo_focuskw`
- `yoast_wpseo_title`
- `yoast_wpseo_metadesc`
- `yoast_wpseo_linkdex`
- `yoast_wpseo_metakeywords`
- `yoast_wpseo_meta-robots-noindex`
- `yoast_wpseo_meta-robots-nofollow`
- `yoast_wpseo_meta-robots-adv`
- `yoast_wpseo_canonical`
- `yoast_wpseo_redirect`
- `yoast_wpseo_opengraph-title`
- `yoast_wpseo_opengraph-description`
- `yoast_wpseo_opengraph-image`
- `yoast_wpseo_twitter-title`
- `yoast_wpseo_twitter-description`
- `yoast_wpseo_twitter-image`

Fork from https://github.com/ChazUK/wp-api-yoast-meta