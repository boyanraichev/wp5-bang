# wp5-bang

This package `boyo\wp5-bang` provides the base functionality of the wp5-bang plugin, included in the `https://github.com/boyanraichev/wp-make` wordpress development boilerplate. 

## Config

Simple file config reader for DB-independent configuration for your website.

## Plugins.lock

A composer-like lock file that saves your current plugins configuration and allows you to easily keep different environments at the same plugins versioning.

## Custom meta fields

Custom meta fields configurator for WordPress 5. An alternative to ACF. This package uses php config files (Laravel-style), instead of saving the configuration into the database, and integrates natively into WordPress. This way it has several advantages over ACF:

- Setup the configuration once and push to every environment
- Access the saved meta data easily with WordPress native functions
- Speed

It should be activated through the main config file in `wp-make`.

You can find the sample `post_meta.php` and `term_meta.php` configuration files in the config folder of `wp-make`

## SEO 

Basic SEO functionality. Most sites do not need an extensive and heavy plugin like Yoast SEO and the functionality included here is more than enough. Can be activate through the main config file and requires custom meta fields to be activated.
