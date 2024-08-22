# Custom Functionality Plugin

[![WordPress Version](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![License](https://img.shields.io/badge/license-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/github/v/release/locke85/webgefaehrte)](https://github.com/locke85/webgefaehrte/releases)

## Description

The **Custom Functionality Plugin** is designed to extend your WordPress site with custom features tailored to your needs. Whether you want to create custom post types, add new shortcodes, or manage custom widgets, this plugin provides a flexible and extendable platform.

### Key Features

- Create and manage custom post types and taxonomies.
- Add dynamic content using shortcodes.
- Develop custom widgets for your theme.
- Easily configurable and extendable through hooks and filters.
- Centralized management of custom functionalities across multiple sites.

## Installation

1. **Download the Plugin**: Clone the repository or download the ZIP file from the [releases page](https://github.com/locke85/webgefaehrte/releases).
2. **Upload to WordPress**: Upload the `custom-functionality-deployment` folder to the `/wp-content/plugins/` directory.
3. **Activate**: Activate the plugin through the 'Plugins' menu in WordPress.
4. **Configure**: Go to `Settings > Custom Functionality` to configure the plugin.

## Configuration

To configure the plugin, you can edit the settings directly within WordPress or extend its functionality via the `functions.php` file in your theme or a custom functionality plugin.

### Custom Post Types and Taxonomies

To add custom post types and taxonomies, use the plugin's settings page or define them in your theme's `functions.php` file.

### Shortcodes

Create custom shortcodes by defining them in the plugin settings or within your theme.

### Widgets

Develop custom widgets by extending the base widget class provided by the plugin.

## Changelog

### 1.0.0
- Initial release with core functionality for post types, shortcodes, and widgets.

## Contributing

Contributions are welcome! Please read our [contributing guidelines](CONTRIBUTING.md) for more details.

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html). See the `LICENSE` file for more details.

## Support

For support, please open an issue on the [GitHub issues page](https://github.com/locke85/webgefaehrte/issues).

## Acknowledgements

This plugin was developed using the [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library by Yahnis Elsts.
