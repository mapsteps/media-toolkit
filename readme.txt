=== Media Toolkit - Bulk Image Optimizer, WebP, Max Image Size & Rename Image Files ===
Contributors: davidvongries
Tags: image optimizer, bulk resize, webp, max image size, rename images
Requires at least: 5.3
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk optimize your images, convert them to WebP, set a max image size & image quality and rename image files.

== Description ==

**Media Toolkit** is a lightweight image optimizer for WordPress. Shrink the images you already have with one click, convert new uploads to WebP, stop huge photos from filling up your server and give your image files clean, SEO-friendly names.

No external services, no API keys, no limits. Everything runs on your own server, and all settings live on one simple page under *Media → Media Toolkit*.

== Features ==

= 🧹 Bulk optimize your existing images =
Shrink the images you already uploaded with one click. Watch every image get smaller, see how much disk space you saved, and pick up where you left off if you close the page. Your images keep their filenames & links, so nothing on your site breaks.

= 📐 Max image size =
Stop huge photos from filling up your server. Images larger than your limit are downscaled automatically when you upload them.

= 🖼️ Convert to WebP =
Save new uploads as WebP, which is usually 25-35% smaller than JPEG and makes your pages load faster.

= 🎚️ Image quality =
Decide how much WordPress compresses your images, from smaller files to sharper images.

= ✏️ Rename image files =
Give your image files clean, SEO-friendly names, built from the original name, the upload date, the author and more.

= 🪶 Lightweight =
No bloat and no external services. Media Toolkit uses WordPress' own image editor.

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
Yes. Your images keep their filenames & links, so nothing on your site breaks, and no files are deleted. The images are overwritten though, so please back up your uploads folder first.

= What does "Optimize existing images" do? =
Images larger than the max image size are downscaled, and all image sizes (thumbnails etc.) are created again with your image quality. Images are not renamed or converted to WebP, because that would change their links.

= Do I have to keep the page open while it's optimizing? =
Yes. If you close the page, the next time you open it you can resume where it stopped.

= Will a higher quality setting make my existing images look better? =
No. Detail that was lost when an image was saved can't be brought back, a higher quality only makes the file bigger. That's why Media Toolkit never saves an image with a higher quality than it already has. The exception are WebP & AVIF images uploaded before Media Toolkit was active, because these files don't tell their quality. If they get bigger, the results tell you.

= Why couldn't an image be optimized? =
The results list every image that couldn't be optimized, with the reason and what you can do about it. For example, the image file may be missing on your server, or your server may have run out of memory with a very large image.

= Why are larger image files still on my server after optimizing? =
Image sizes larger than the max image size are no longer used, but their files aren't deleted. Your posts might still link to them, and deleting them would break those images.

= What's the difference to WordPress' big image size threshold? =
Since WordPress 5.3, images larger than 2560px are scaled down, but WordPress still keeps the original image on your server. With *Limit image size* enabled, Media Toolkit replaces the original image with a downscaled version instead, so no full-size copy is kept.

= What happens to the original when an image is converted to WebP? =
The uploaded JPEG or PNG is kept as the original, just like WordPress does with big images. Your website uses the WebP version.

= Why can't I enable "Convert to WebP"? =
Your server doesn't support WebP yet. Ask your host to enable WebP support for Imagick or GD.

= Which template tags can I use for the filename? =
`{original_file_name}`, `{upload_date}`, `{upload_timestamp}`, `{random_id}`, `{author_first_name}`, `{author_last_name}` and `{author_full_name}`. Click a tag on the settings page to copy it.

= Are other files renamed too? =
No. Only images are renamed. PDFs, videos and other files keep their filename.

= Are images I already uploaded renamed? =
No. Renaming only applies to new uploads, because it would change the links of your existing images.

= Does it work with animated GIFs? =
GIFs are not resized, so they keep their animation.

= Is the EXIF data kept? =
No. When an image is downscaled, its EXIF data (e.g. camera details & location) is removed.

== Screenshots ==
1. Settings page

== Changelog ==
= 2.0 November 08, 2026 =
* New: Bulk optimize existing images (max image size & image quality) with live progress, before/after per image, saved disk space & resume
* New: Images are never saved with a higher quality than they already have, so optimizing never makes them worse or bigger
* New: Images that can't be optimized are listed with the reason and what to do about it
* New: Convert new JPEG & PNG uploads to WebP
* New: Refreshed settings page design
* Fixed: Security improvements (settings are sanitized before they're saved)
* Fixed: Image quality is limited to 1-100
* Fixed: Wrong image dimensions & file size after the original image was replaced
* Fixed: Image sizes larger than the max image size were still created (also for portrait photos)
* Fixed: Rotated photos kept a full-size copy when the max image size was set
* Fixed: Animated GIFs lost their animation when the max image size was set
* Fixed: Non-image files were renamed as well
* Fixed: Images got an empty filename if nothing was left of the filename structure
* Tweak: Template tags can be copied with the keyboard
* Tweak: Settings page is more accessible (labels & descriptions)
* Tweak: Smaller plugin (removed development files)
* Tested up to WordPress 7.1
= 1.0 November 30, 2023 =
* Overall improvements
= 0.1.0 February 24, 2023 =
* Initial release
