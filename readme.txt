=== Media Toolkit - Image Optimizer, Resize & WebP Converter ===
Contributors: davidvongries
Tags: image optimizer, bulk resize, webp, max image size, rename images
Requires at least: 5.3
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk-optimize your images, convert uploads to WebP, set a max image resolution and change your image filenames.

== Description ==

**Media Toolkit** makes your images smaller, so your website loads faster and your server doesn't fill up.

Optimize all the images you already have in one go, convert new uploads to WebP, stop huge photos from being stored at full size and give your images clean file names.

Everything runs on your own server. No account, no API key, no limits. All settings are on one page under *Media → Media Toolkit*.

== Features ==

= 🧹 Bulk optimize your existing images =
Make existing images in your media library smaller with one click. See the progress for every image and how much disk space you saved.

= 📐 Max image size =
Set a max size in pixels. Large images are downscaled when you upload them, so huge photos don't fill up your server.

= 🖼️ Convert to WebP =
Save new JPEG & PNG uploads as WebP. WebP files are usually much smaller, so your pages load faster.

= 🎚️ Image quality =
Set the quality your images are saved with, from 1 to 100. A lower value makes your files smaller, a higher value keeps more detail. Leave it empty to keep the WordPress default (82).

= ✏️ Rename image files =
Give new uploads clean, SEO-friendly - or simply custom - filenames, built from the original name, the upload date, the author and more.

= 🪶 Lightweight =
No bloat and no external services. Media Toolkit uses WordPress' own image tools.

== How to use it ==

1. Go to *Media → Media Toolkit*.
2. Choose your settings and save them. They apply to every image you upload from now on.
3. To apply them to the images you already have, click *Optimize existing images*. Please back up your uploads folder first.

---------

=== Advanced ===

Change the final filename with the `mediatoolkit_attachment_filename` filter:

`function prefix_media_toolkit_filename( $filename ) {
	return 'my-site-' . $filename;
}
add_filter( 'mediatoolkit_attachment_filename', 'prefix_media_toolkit_filename' );`

Run your own code after an original image was replaced:

`function prefix_after_replace_original_image( $attachment_id, $metadata ) {
	// Your code.
}
add_action( 'mediatoolkit_after_replace_original_image', 'prefix_after_replace_original_image', 10, 2 );`

---------

=== What's next? ===

If you like Media Toolkit, make sure to check out our other products:

* **[Page Builder Framework](https://wp-pagebuilderframework.com/?utm_source=media_toolkit&utm_medium=repository&utm_campaign=wpbf)** - A fast & minimalistic WordPress theme designed for the new WordPress era.
* **[Ultimate Dashboard](https://ultimatedashboard.io/?utm_source=media_toolkit&utm_medium=repository&utm_campaign=udb)** - The #1 WordPress plugin to customize your WordPress dashboard and admin area.
* **[Better Admin Bar](https://betteradminbar.com/?utm_source=media_toolkit&utm_medium=repository&utm_campaign=bab)** - The plugin to make your clients enjoy WordPress. It replaces the default admin bar to provide the best possible user experience when editing & navigating a website.
* **[WP Video Popup](https://wp-video-popup.com/?utm_source=media_toolkit&utm_medium=repository&utm_campaign=wp_video_popup)** - Add a YouTube, Vimeo or Rumble video popup to WordPress. Lightweight, privacy-friendly lightbox that works with any page builder.

== Installation ==
1. In your WordPress admin, go to *Plugins → Add New Plugin* and search for "Media Toolkit".
2. Click *Install Now*, then *Activate*.
3. Go to *Media → Media Toolkit* to change the settings.

== Frequently Asked Questions ==

= Is it safe to optimize my existing images? =
Yes. Your existing images keep their filenames & links, so nothing on your site breaks, and no files are deleted. The images are overwritten though, so please back up your uploads folder first.

= What does "Optimize existing images" do? =
Every image and all its sizes (thumbnails etc.) are saved again with your settings: downscaled if they're larger than the max image size you've selected, and saved with your image quality. An image is never saved with a higher quality (or resolution) than it already has. Existing images are not renamed or converted to WebP, because that would change their links.

= Do I have to keep the page open while it's optimizing? =
Yes. If you close the page, the next time you open it you can resume where it stopped.

= Will a higher quality setting make my existing images look better? =
No. Detail that was lost when an image was uploaded can't be brought back, a higher quality only makes the file bigger. That's why Media Toolkit never saves an image with a higher quality than it already has. The only exception: for WebP & AVIF images uploaded before Media Toolkit was active, the plugin can’t tell their quality, so they’re saved with your quality setting. If that’s higher than before, the new file can be bigger. If that happens, the plugin will let you know during the bulk-optimization.

= Why couldn't an image be optimized? =
The plugin lists every image that couldn't be optimized, with the reason and what you can do about it. For example, the image file may be missing on your server, or your server may have run out of memory due to a very large image.

= What's the difference to WordPress' big image size threshold? =
Since WordPress 5.3, images larger than 2560px are scaled down, but WordPress still keeps the original image on your server. With *Limit image size* enabled, Media Toolkit replaces the original image with a downscaled version instead, so no full-size copy is kept.

= Why can't I enable "Convert to WebP"? =
Your server doesn't support WebP yet. Ask your host to enable WebP support for Imagick or GD.

= I've changed the filename for my images, are other files renamed too? =
No. Only images are renamed. PDFs, videos and other files keep their filename.

= Are images I already uploaded renamed? =
No. Renaming only applies to new uploads, because it would change the links of your existing images.

== Screenshots ==
1. Settings page

== Changelog ==
= 1.1 November 09, 2026 =
* New: Bulk optimize existing images (max image resolution & image quality) with live progress, before/after per image, saved disk space & resume
* New: Convert new JPEG & PNG uploads to WebP
* New: Refreshed settings page design
* Tweak: Security improvements
* Fixed: Image quality is limited to 1-100
* Fixed: Wrong image dimensions & file size after the original image was replaced
* Fixed: Image sizes larger than the max image size were still created
* Fixed: Rotated photos kept a full-size copy when the max image size was set
* Fixed: Animated GIFs lost their animation when the max image size was set
* Fixed: Non-image files were renamed as well
* Tested up to WordPress 7.1
= 1.0 November 30, 2023 =
* Overall improvements
= 0.1.0 February 24, 2023 =
* Initial release
