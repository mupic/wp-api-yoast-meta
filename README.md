
# Yoast to REST API v2

# Install

```
composer require mupic/wp-api-yoast-meta
```

For use with the new [WP REST API](http://v2.wp-api.org/)

Returns Yoast post or page metadata in a normal post or page request. Stores the metadata in the `yoast_meta` field of the returned data.

# Default constants
```
//GET params will take precedence over constants
define('YOAST_REST_META', false); //false - Disable automatic meta seo input
define('YOAST_REST_OG', false); //false - Disable automatic open graph input
define('YOAST_REST_TW', false); //false - Disable automatic meta twitter input
define('YOAST_REST_BC', false); //true - Return json breadcrumbs. "html" - Return html generated breadcrumbs.
define('YOAST_REST_SCHEMA', false); //false - Disable automatic microdata input.
```

# Examples
```
//wp-json/wp/v2/posts/123?yoast_meta=true&opengraph=true&twitter=true&breadcrumbs=true&schema=true
{
	id: 123,
	...
	yoast:{
		breadcrumbs: {
			links: [
				0: {
					allow_html: true
					text: "Home"
					url: "http://example.com/"
				},
				1: {
					text: "News"
					url: "http://example.com/category/news/"
				},
				2: {
					text: "Title"
					url: "http://example.com/news/title/"
				}
			],
			separator: "»"
		},
		meta: {
			canonical: "http://example.com/news/title/"
			description: "Description"
			article:modified_time: "2019-04-01T16:22:10+00:00"
			article:published_time: "2019-01-10T13:05:30+00:00"
			article:section: "News"
			article:tag: ["Tag1", "Tag2"]
			og:description: "Description"
			og:image: "http://example.com/wp-content/uploads/2019/01/6JHYYbvoSuQ95ceGx8Oeg8zzAjg-550x309.jpg"
			og:image:height: "309"
			og:image:width: "550"
			og:locale: "en_US"
			og:site_name: "Site"
			og:title: "Title - site"
			og:type: "article"
			og:updated_time: "2019-04-01T16:22:10+00:00"
			og:url: "http://example.com/news/title/"
			title: "Title - site"
			twitter:card: "summary"
			twitter:description: "Description"
			twitter:image: "http://example.com/wp-content/uploads/2019/01/6JHYYbvoSuQ95ceGx8Oeg8zzAjg.jpg"
			twitter:title: "Title - site"
		},
		schema: {
			breadcrumbs: "{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"item":{"@id":"http://example.com/","name":"Home"}},{"@type":"ListItem","position":2,"item":{"@id":"http://example.com/category/news/","name":"News"}},{"@type":"ListItem","position":3,"item":{"@id":"http://example.com/news/title/","name":"Title"}}]}",
			organization: "{"@context":"https://schema.org","@type":"Organization","url":"http://example.com/","sameAs":[],"@id":"http://example.com/#organization","name":"My super company","logo":"http://example.com/wp-content/uploads/2019/02/7e55b905c43b67479065761d49f0dcb8-2.png"}"
		}
	}
}
```

```
//yoast_api/v1/home?yoast_meta=true&opengraph=true&twitter=true&breadcrumbs=true&schema=true
{
	breadcrumbs: {
		links: [
			0: {
				allow_html: true
				text: "Home"
				url: "http://example.com/"
			}
		],
		separator: "»"
	},
	meta: {
		canonical: "http://example.com/"
		description: "Description"
		og:description: "Description"
		og:locale: "en_US"
		og:site_name: "Site"
		og:title: "Title - site"
		og:type: "website"
		og:url: "http://example.com/"
		title: "Title - site"
		twitter:card: "summary"
		twitter:description: "Description"
		twitter:title: "Title - site"
	},
	schema: {
		organization: "{"@context":"https://schema.org","@type":"Organization","url":"http://example.com/","sameAs":[],"@id":"http://example.com/#organization","name":"My super company","logo":"http://example.com/wp-content/uploads/2019/02/7e55b905c43b67479065761d49f0dcb8-2.png"}",
		website: "{"@context":"https://schema.org","@type":"WebSite","@id":"http://example.com/#website","url":"http://example.com/","name":"SiteName","potentialAction":{"@type":"SearchAction","target":"http://example.com/?s={search_term_string}","query-input":"required name=search_term_string"}}"
	}
}
```

Supports pages, posts, any *public* custom post types, categories, tags, any *show_in_rest* custom taxonomies

Currently fetching:

- `canonical`
- `description`
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
- `title`
- `twitter:card`
- `twitter:description`
- `twitter:image`
- `twitter:title`

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