# Frame: A photography-centric WordPress theme Framework

Frame is a lightweight framework for building photography-centric WordPress themes.


### Installation

Use the following command from your preferred command line utility to install the package.

```bash
composer require photopress/frame
```

If you are bundling Frame with in your theme, you'll need to add the following to your `functions.php` to autoload the framework:

```php
if ( file_exists( get_parent_theme_file_path( 'vendor/autoload.php' ) ) ) {
	
	require_once( get_parent_theme_file_path( 'vendor/autoload.php' ) );
}
```

## Purchase or donate

Frame is free.  However, we ask that you purchase a support membership at [PhotoPress](http://photopressdev.com).  Even if you don't need the support, this purchase helps fund the development of this project.

[Donations to the project](http://paypal.me/padams) are also appreciated.

## Documentation

Documentation for Frame is maintained on the [wiki](https://github.com/photopress-dev/frame/wiki).  Please feel free to add to the wiki if you use the framework.

## Copyright and License

This project is licensed under the [GNU GPL](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

2011&thinsp;&ndash;&thinsp;2020 &copy; [Peter Adams](http://peteradams.org).
