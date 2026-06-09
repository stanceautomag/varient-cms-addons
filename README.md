# Varient CMS Addons — Stance Auto Magazine

A collection of performance, SEO, and newsletter enhancements for the Varient CMS (CodeIgniter 4).

Built and tested on a live production site running PHP 8.4 on GoDaddy shared hosting.

---

## Addons Included

### 1. Mailjet Newsletter System
Automatically notifies your subscriber list when you publish a new post. Includes a checkbox in the Publish box so you control when newsletters go out — not every post triggers one.

**Files:**
- `Libraries/MailjetNewsletter.php` — drop into `app/Libraries/`
- `Controllers/PostController_newsletter_snippet.php` — code snippet to add to your PostController
- `Views/_publish_box.php` — modified publish box with newsletter checkbox

**Requirements:** Mailjet account with API key and secret key configured in your Varient CMS admin under Email Settings.

---

### 2. Homepage Full-Page HTML Caching
Caches the entire homepage as a static HTML file and serves it to non-logged-in visitors. Dramatically reduces TTFB on shared hosting. Cache is automatically busted when a post is published.

**Files:**
- `Controllers/HomeController_cache_snippet.php` — code snippet to add to your HomeController

**Notes:** Cache bypasses logged-in users automatically. Includes a 60-minute expiry as a fallback safety net.

---

### 3. MySQL Reconnect Filter
Fixes the "Server has gone away" error on GoDaddy and other shared hosts that aggressively close idle database connections. Runs silently on every request.

**Files:**
- `Filters/DBReconnect.php` — drop into `app/Filters/`
- `Config/Filters_snippet.php` — code snippet showing how to register the filter in `app/Config/Filters.php`

---

### 4. SEO Image Filename Renamer
Renames uploaded images using the original filename from the user's device instead of a generic timestamp. Produces filenames like `ford-escort-mk2-870x580-abc123.webp` instead of `img_1234567890.webp`. Helps Google Image Search associate your images with relevant search terms.

**Files:**
- `Models/UploadModel_seo_snippet.php` — modified upload methods for post, quiz, and gallery images

---

### 5. Optimised .htaccess
A production-ready .htaccess for Varient CMS on Apache shared hosting. Includes GZip compression, 1-year browser caching for all static assets, AI crawler whitelist, and security hardening.

**Files:**
- `htaccess-env/.htaccess` — drop into your site root, replacing the existing one
- `htaccess-env/env_snippet.txt` — recommended .env settings

---

## Installation Order

Install in this order to avoid conflicts:

1. DBReconnect filter (fixes DB connection before anything else)
2. .htaccess (server-level performance)
3. Homepage caching (HomeController)
4. SEO image renamer (UploadModel)
5. Newsletter system (MailjetNewsletter library + PostController + publish box view)

---

## Requirements

- Varient CMS (CodeIgniter 4)
- PHP 8.0+ (tested on PHP 8.4)
- Mailjet account (for newsletter addon only)
- Apache with mod_rewrite, mod_deflate, mod_expires (for .htaccess addon)
- PHP fileinfo extension must be enabled (required by Intervention Image)

---

## Credits

Built by Paul Doherty — [Stance Auto Magazine](https://stanceauto.co.uk)

If you use these addons and improve on them, feel free to submit a pull request.

---

## Important Notes

- Always back up your original files before applying any modifications
- The PostController and HomeController snippets show only the code to add — they are not complete file replacements
- Test on a staging environment before applying to production if possible
