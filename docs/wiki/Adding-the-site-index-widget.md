Written against v1.1.0.4.

Use the Site Index widget to show the list of the network's sites in a sidebar, a footer, or any other widget area of a site's theme.

1. On the site whose theme should show the list, go to *Appearance*, *Widgets*. The widget is registered only on a multisite network; on a single site it is not offered.
2. Add the **Site Index** widget to a widget area. In the block widget editor, search the block inserter for *Site Index*; it is added as a legacy widget.
3. Fill in the settings. A new widget starts with *Excluded site IDs* set to `1`, so the primary site is left out; clear the field to include it.
4. Save or update the widgets, then view the site. The widget area shows the title you entered, if any, and the list under it.

**Note:** *Number of Sites to Show* appears only on a network with more than 100 sites. On a smaller network the widget lists every site.

## Reference

- [Widget reference](Widget-reference.md): every setting, its default and its effect.
- [Shortcode reference](Shortcode-reference.md#markup): the markup the widget prints, which is the same as the shortcode's.
