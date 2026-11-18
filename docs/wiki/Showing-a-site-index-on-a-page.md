Written against v1.1.0.4.

By the end of this page you will have a published page that lists the sites of your network, each title linking to its site, with each site's tagline under it.

It assumes the plugin is installed and network-activated on a multisite network (see [Installation (README)](https://github.com/UAMS-Web/UAMSWP-multisite-site-index#installation)), and that you can edit pages on one of its sites.

## 1. Open a page for editing

Create a page, or open an existing one, on any site of the network. The list shows every site of the network, whichever site the page is on.

## 2. Add the shortcode

Add a Shortcode block and type:

```text
[site-index]
```

In the classic editor, type the same line into the content.

## 3. Preview the page

Preview the page. It shows a bulleted list with one item per site: the site title in bold, linking to the site's home page, and the tagline under it in italics. The sites are in order of site ID, lowest first, and the primary site is included.

**Note:** the list holds at most 100 sites unless you raise the limit; see `number` in the [Shortcode reference](Shortcode-reference.md#number).

## 4. Narrow the list

Change the shortcode to leave out the primary site and sort by title:

```text
[site-index excluded="1" orderby="name"]
```

Preview again. The primary site is gone and the remaining sites are in alphabetical order of title.

## 5. Publish

Publish the page. The list is built each time the page is shown, so a site added to the network later appears without editing the page.

## Go further

- [Adding the site index widget](Adding-the-site-index-widget.md): the same list in a widget area.
- [Shortcode reference](Shortcode-reference.md): every attribute and the markup to style.
