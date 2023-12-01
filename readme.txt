=== Media Toolkit ===
Contributors: davidvongries
Tags: Media Toolkit, Media Library, Image Compression, Photo Quality, Image Dimensions
Requires at least: 5.3
Tested up to: 6.4
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Media Toolkit is a powerful utility plugin for WordPress that provides users with the tools they need to manage media files with ease.

With Media Toolkit, users can change the filenames of their uploaded images, set maximum image sizes to control file sizes, and adjust compression levels to optimize image quality.

== Installation ==
1. Download the media-toolkit.zip file to your computer.
2. Unzip the file.
3. Upload the `media-toolkit` folder to your `/wp-content/plugins/` directory.
4. Activate the plugin through the *Plugins* menu in WordPress.

== Frequently Asked Questions ==
= How does compression work? =
Media Toolkit utilizes WordPress' compression system. The default quality set by WordPress is 82%. Media Toolkit allows you to change that value globally.
= big_image_size_threshold vs Media Toolkit =
WordPress' max image size is set to 2560px by default. Though, if an image is uploaded bigger than the threshold, WordPress does not just scale down the original image to the threshold.
Instead, WordPress stores the original image and simply provides the 2560px image as the biggest image available to the users.

Media Toolkit actually replaces the original image with the threshold defined in the plugin.

== Screenshots ==
1. Settings Page

== Changelog ==
= 1.0 November 30, 2023 =
* Overall improvements
= 0.1.0 February 24, 2023 =
* Initial release
