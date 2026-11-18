Written against v1.1.0.4.

The **Site Index** widget prints the same list as the `[site-index]` shortcode, under an optional title. It is registered only on a multisite network.

## Settings

| Setting | Default for a new widget | Effect |
| --- | --- | --- |
| *Title* | empty | Shown above the list in the widget area's title markup. Left out when empty. |
| *Number of Sites to Show* | `100` | The most sites to list. Shown only on a network with more than 100 sites. |
| *Excluded site IDs* | `1` | Site IDs, separated by commas, to leave out. The default leaves out the primary site; clear it to include that site. |
| *Order (ASC or DESC)* | ASC (A - Z) | Ascending or descending. |
| *Order by* | Site ID | Site ID, Site Title, or Last Updated. |
| *Show site description* | checked | Whether each site's tagline is shown under its title. |

Each setting matches a shortcode attribute: *Number of Sites to Show* is `number`, *Excluded site IDs* is `excluded`, *Order* is `order`, *Order by* is `orderby` and *Show site description* is `description`. The [Shortcode reference](Shortcode-reference.md) describes how each one behaves, including how a sort by title works.

**Note:** a widget whose settings were never saved, for example one placed through code, lists every site, the primary site included: the `1` in *Excluded site IDs* is only a default of the settings form.

## Markup

The widget prints the widget area's own wrapper and title markup around the list described in [Shortcode reference](Shortcode-reference.md#markup). The title passes through the `widget_title` filter.
