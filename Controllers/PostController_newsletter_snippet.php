<?php

/**
 * PostController Newsletter Trigger — Code Snippet
 * Varient CMS Addon — Stance Auto Magazine
 *
 * This is NOT a complete file replacement.
 * Add the code below to your existing PostController.php (app/Controllers/PostController.php)
 *
 * WHERE TO ADD IT:
 * In the addPostPost() method, find the cache busting block:
 *
 *     $cachePath = WRITEPATH . 'cache/custom_home_cache.html';
 *     if (file_exists($cachePath)) {
 *         unlink($cachePath);
 *     }
 *
 * Add the newsletter block IMMEDIATELY AFTER the cache bust closing brace,
 * and BEFORE the return redirect() line.
 *
 * The send_newsletter checkbox value comes from the publish box view.
 * Only sends if: post is published AND the "Send Newsletter Notification" checkbox is ticked.
 */

// ============================================================
// ADD THIS BLOCK after your cache busting code:
// ============================================================

// Send newsletter notification to subscribers (only if checkbox ticked)
if (isPostPublished($post) && inputPost('send_newsletter') == '1') {
    $newsletter = new \App\Libraries\MailjetNewsletter();
    $newsletter->sendNewPostNotification($post, $postUrl);
}

// ============================================================
// The return redirect() line should follow immediately after:
// return redirect()->to(adminUrl('add-post?type=' . cleanStr($postType)));
// ============================================================
