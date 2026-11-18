Written against v1.1.0.4.

`[site-index]` prints a list of the sites of the multisite network. On a WordPress install that is not a multisite network it prints nothing.

```text
[site-index excluded="1,4" number="50" orderby="name" order="ASC" description="0"]
```

## Attributes

| Attribute | Type | Default | Effect |
| --- | --- | --- | --- |
| `excluded` | Site IDs, separated by commas | empty | Leaves these sites out of the list. Spaces around the IDs are ignored. |
| `number` | Whole number | `100` | The most sites to list. `0` lists every site. |
| `orderby` | `id`, `name` or `last_updated` | `id` | What the list is sorted by: site ID, site title, or the date each site was last updated. |
| `order` | `ASC` or `DESC` | `ASC` | Ascending or descending. Write it in capitals; see [Sorting by title ignores the order](Troubleshooting.md#sorting-by-title-ignores-the-order). |
| `description` | `1` or `0` | `1` | Whether each site's tagline is shown under its title. Only `0` or an empty value hide it; see [The tagline still shows with description="false"](Troubleshooting.md#the-tagline-still-shows-with-descriptionfalse). |
| `recent` | none | `0` | Reserved and unused; setting it changes nothing. |

### excluded

The primary site is usually site ID `1`. Find a site's ID under *My Sites*, *Network Admin*, *Sites*: it is in the link to each site's edit screen (`site-info.php?id=<id>`).

### number

The limit is applied by WordPress when it fetches the sites, before any sorting by title. Deleted sites are never listed. Archived, spam, mature and private sites are listed unless you exclude them by ID.

### orderby

`id` and `last_updated` are sorted by WordPress when it fetches the sites. `name` is sorted by the plugin after fetching: it compares the titles as text, so titles starting with a capital letter come before titles starting with a lowercase one. When the network has more sites than `number`, which sites are fetched for a `name` sort is not defined; raise `number` above the number of sites to list them all.

## Markup

The list is an unordered list. The `orderby` value is added as a second class, so each sort can be styled on its own.

```html
<ul class="site-index name">
  <li class="site">
    <a href="https://example.com/site-one/" target="_top"><strong class="site-index-site-title">Site One</strong></a>
    <br><em class="site-index-site-tagline">The tagline of site one</em>
  </li>
</ul>
```

| Class | Element |
| --- | --- |
| `site-index` | The list. |
| `id`, `name` or `last_updated` | The list, naming the sort used. |
| `site` | Each item. |
| `site-index-site-title` | The site title, inside the link. |
| `site-index-site-tagline` | The tagline, left out when `description` is `0`. |

When no site is left to list, nothing is printed, not even an empty list.
