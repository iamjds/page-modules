# Page Modules

This plugin was developed to turn a WordPress admin into a headless, configuration back-end for a website created and hosted on a different stack.
The configuration created and managed by this plugin is accessible via WordPress public API, as well as using built-in WP functions to enable additional public API end points.

This allows for any public-facing website with the ability to make HTTP requests to initially fetch the configuration data from the WordPress API, and display the configuration data on the front-end of the website of application making the request.

## WordPress Admin

The plugin is only used within WP admin, and does not have any public-facing templates or views.

A new page module can be created as either type `simple` or type `list`.

`simple` means that the page module configuration is intended for single use, with the specific pre-defined fields created for the page module.
`list` means that after the pre-defined fields have been created, a collection of repeatable objects with different objects can be created using the same field structure.


## MySQL

Any table or field structure was serialized and stored in the WP options table.

Any new page module created was stored in the WP posts table with the `post_type` of "page_module".

All field values are saved in the WP post_meta table.


## Dependencies

The text area field types used throughout the configuration screens use the TinyMCE rich text editor to allow non-developers to style text that is presentable on the front-end.
https://github.com/tinymce/tinymce

## API End Points

- `[domain]/wp-json` will reveal all of the publicly accessible API end points
- `[domain]/wp-json/wp/v2/page_module` will reveal all page modules created and their unique IDs
- `[domain]/wp-json/wp/v2/page_module/[page module ID]/meta` will reveal all field unique identifying keys and their values
