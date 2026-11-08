=== Media Toolkit - Bulk Image Optimizer, WebP, Max Image Size & Rename Images ===
Contributors: davidvongries
Tags: image optimizer, bulk resize, webp, max image size, rename images
Requires at least: 5.3
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk optimize your existing images, convert uploads to WebP, set a max image size & image quality and rename uploaded images.

== Description ==

**Media Toolkit** is a lightweight image optimizer for the WordPress media library. Shrink the images you already have with one click, convert new uploads to WebP, stop huge photos from filling up your server and give your images clean, SEO-friendly filenames.

No external services, no API keys, no limits. Everything runs on your own server with WordPress' image editor, and all settings live on one simple page under *Media → Media Toolkit*.

== Features ==

= 🧹 Bulk optimize your existing images =
Apply the max image size and image quality to every image you already uploaded, with one click. Watch each image shrink with its dimensions & file size before and after, scroll through the list of optimized images and see how much disk space you saved. Closed the page? Resume where it stopped.

* **Safe:** filenames & URLs stay the same, so nothing on your site breaks. No files are deleted.
* **Never worse:** images are never saved with a higher quality than they already have (that would only make them bigger), and image sizes are never replaced with bigger files.
* **Honest:** images that can't be optimized are listed with the reason and what you can do about it.

= ✏️ Rename uploaded images =
Create your own filename structure with template tags like `{original_file_name}`, `{upload_date}` or `{author_full_name}`. Filenames are lowercased and cleaned up automatically.

= 🎚️ Change the image quality =
WordPress saves generated images with a quality of 82%. Set your own value from 1 to 100 to make images smaller or sharper.

= 🖼️ Convert to WebP =
Convert new JPEG & PNG uploads to WebP. WebP images are usually 25-35% smaller at the same quality, which makes your pages load faster.

= 📐 Max image size =
Set a max width or height for new uploads. Larger images are downscaled on upload, so a 6000px photo straight from your camera doesn't stay on your server and no image is ever larger than your limit.

= 🪶 Lightweight =
No bloat, no external services. Media Toolkit uses WordPress' own image editor.

== How to use it ==

Go to *Media → Media Toolkit*.

**Filename structure:** enter a structure like `{original_file_name}-{upload_date}`. Click a tag in the sidebar to copy it. Leave the field empty to keep the original filename. Only images are renamed.

**Available template tags:**

* `{original_file_name}` – the name of the uploaded file
* `{upload_date}` – the upload date (e.g. 2026-11-08)
* `{upload_timestamp}` – the Unix timestamp of the upload
* `{random_id}` – a random, unique ID
* `{author_first_name}`, `{author_last_name}`, `{author_full_name}` – the name of the user who uploads the image

**Quality:** enter a value from 1 to 100. Leave it empty to use the WordPress default (82).

**Convert to WebP:** new JPEG & PNG uploads are saved as WebP, including all image sizes. The uploaded file is kept as the original, just like WordPress does with big images. Your server needs WebP support (Imagick or GD), the setting tells you if it's missing.

**Max image size:** enable *Limit image size* and enter the max width or height in pixels. New uploads that are larger are downscaled on upload, and WordPress doesn't create any image sizes larger than that. The full-size original is replaced, not kept as a backup.

**Optimize existing images:** save your settings first, then click *Optimize existing images*. Images larger than the max image size are downscaled and all image sizes are re-created with your image quality. Files are overwritten in place, so please back up your uploads folder first. Keep the page open while it runs. If you leave, you can resume where it stopped. Existing images are not renamed or converted to WebP, because that would change their URLs.

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

= How do I rename images on upload in WordPress? =
Go to *Media → Media Toolkit* and enter a filename structure, e.g. `{original_file_name}-{upload_date}`. Every image you upload from then on is renamed. Existing images are not renamed.

= Are other files renamed too? =
No. Only images are renamed. PDFs, videos and other files keep their filename.

= Will a higher quality setting make my existing images look better? =
No. Detail that was lost when an image was saved can't be brought back, a higher quality only makes the file bigger. That's why Media Toolkit never saves an image with a higher quality than it already has. Images uploaded before Media Toolkit was active are the exception if they're WebP or AVIF, because those files don't tell their quality. If they get bigger, the results tell you.

= How does the image quality setting work? =
Media Toolkit uses WordPress' own image editor. WordPress saves generated images (thumbnails and other sizes) with a quality of 82%. Media Toolkit lets you change that value for your whole site.

= What's the difference to WordPress' big image size threshold? =
Since WordPress 5.3, images larger than 2560px are scaled down, but WordPress still keeps the original image on your server. With *Limit image size* enabled, Media Toolkit replaces the original image with a downscaled version instead.

= Does it work with images I already uploaded? =
Yes. Use *Optimize existing images* to apply the max image size & image quality to your existing images. Renaming and WebP conversion only apply to new uploads, because they would change the URLs of your existing images.

= Why are larger image files still on my server after optimizing? =
Image sizes larger than the max image size are no longer used, but their files aren't deleted. Your posts might still link to them, and deleting them would break those images.

= Does it work with animated GIFs? =
GIFs are not resized, so they keep their animation.

= Is the EXIF data of the original image kept? =
No. When the original image is replaced, its EXIF data is removed.

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
