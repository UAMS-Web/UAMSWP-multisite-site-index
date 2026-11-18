Written against v1.1.0.4.

- [The shortcode prints nothing](#the-shortcode-prints-nothing)
- [The Site Index widget is not offered](#the-site-index-widget-is-not-offered)
- [Some sites are missing from the list](#some-sites-are-missing-from-the-list)
- [An archived or private site is listed](#an-archived-or-private-site-is-listed)
- [The tagline still shows with description="false"](#the-tagline-still-shows-with-descriptionfalse)
- [Sorting by title ignores the order](#sorting-by-title-ignores-the-order)
- [Titles in lowercase sort after the others](#titles-in-lowercase-sort-after-the-others)

## The shortcode prints nothing

**Cause:** the install is not a multisite network, or every site is excluded. The shortcode prints nothing in both cases, with no message.

**Fix:** confirm the install is a multisite network, then remove IDs from `excluded` until a site is left.

## The Site Index widget is not offered

**Cause:** the widget is registered only on a multisite network.

**Fix:** use it on a site of a multisite network, with the plugin active.

## Some sites are missing from the list

**Cause:** the list holds at most `number` sites, 100 by default; the site is excluded by ID; or the site is deleted, and deleted sites are never listed.

**Fix:** raise `number` (on the widget, *Number of Sites to Show*, which appears only on a network with more than 100 sites), and check `excluded`. A new widget excludes site `1` by default.

## An archived or private site is listed

**Cause:** only deleted sites are left out. Archived, spam, mature and private sites are listed.

**Fix:** add the site's ID to `excluded`.

## The tagline still shows with description="false"

**Cause:** `description` is read as text, and any value other than `0` or an empty one, `false` included, shows the tagline.

**Fix:** write `description="0"`.

## Sorting by title ignores the order

**Example:** `[site-index orderby="name" order="desc"]` lists the sites unsorted.

**Cause:** the plugin sorts by title only when `order` is exactly `ASC` or `DESC`, in capitals.

**Fix:** write `order="DESC"`.

## Titles in lowercase sort after the others

**Cause:** a sort by title compares the titles as text, so every title starting with a capital letter comes before every title starting with a lowercase one.

**Fix:** start the site titles consistently, or sort by `id` and set the site IDs' order instead.
