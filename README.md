# yafig-widget

Yet another featured image gallery widget.

Based on 'Featured Image Gallery Widget' by Nick Halsey.

## Description

A widget which displays an image gallery containing the featured
images of posts in one or more specified post categories.

## Installation

1. Change to your site's `wp-content/plugins/` direcory.
2. Issue the command `git clone https://github.com/pdjr-miscellany/yafig-widget`.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Add the widget to your sidebar or footer.

## Hiding captions

To hide captions, paste this CSS into your theme's `custom.css` file:
```
.gallery .gallery-caption {
	display: none;
}
```