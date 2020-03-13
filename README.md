<p align="center">
  <a href="https://wordpress.org/plugins/block-editor-colors/">
    <img src="https://ps.w.org/block-editor-colors/assets/icon.svg" alt="Block Editor Colors" width="128" height="128">
  </a>
</p>


<h1 align="center">Block Editor Colors</h1>

![](https://img.shields.io/wordpress/plugin/v/block-editor-colors)
![](https://img.shields.io/wordpress/plugin/wp-version/block-editor-colors)
![](https://img.shields.io/wordpress/plugin/dd/block-editor-colors)
![](https://img.shields.io/wordpress/plugin/installs/block-editor-colors)
![](https://img.shields.io/wordpress/plugin/rating/block-editor-colors)
![](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg?style=flat)

This WordPress plugin allows you to change the WordPress block editor colors that are registered with a theme - you can update default colors or add your own ones. They appear in the color palette of the block editor.

If you are short of the core Gutenberg blocks, feel free to try [Getwid – Gutenberg Blocks](https://wordpress.org/plugins/getwid/) - a collection of 40+ comprehensive WordPress blocks.

## Getting Started
1. Upload or clone the plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Settings > Editor Colors.

## Custom colors
In the settings, you can add your colors via the “Custom Colors” section. You need three components to add your colors: Name, Slug and Color.
* Name is what you see when hovering your mouse over the block color palette.
* Slug is used for generating CSS classes.
* Color is a Hex color code.
     
> **Important!** Slug is permanent and can’t be changed later.

Custom colors can be disabled.
* Disabled colors won’t be displayed in the block editor color picker; CSS classes for these colors won’t be generated as well.
* These colors will appear at the bottom of the plugin settings - they can be either deleted or restored.
* Disabled colors can’t be edited.
    
## Default Colors
In the “Default Colors” section, you can edit colors of your active WordPress theme, or, if absent, colors of the block editor. You can only edit values(Hex color code) of default colors, they can’t be deleted. Colors change when you switch a theme.

**CSS class prefix** is responsible for selectors that will be used for generating CSS classes. Classes for colors will be descendant for a selector/selectors in the configuration.

## Support
This is a developer's portal for the Block Editor Colors plugin and should not be used for support. Please visit the support page if you need to submit a support request.

## License
Block Editor Colors WordPress Plugin, Copyright (C) 2020, MotoPress.
Block Editor Colors is distributed under the terms of the GNU GPL.

## Contributions
Anyone is welcome to contribute.