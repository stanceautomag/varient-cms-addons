<?php

/**
 * HomeController Full-Page HTML Cache — Code Snippet
 * Varient CMS Addon — Stance Auto Magazine
 *
 * This is NOT a complete file replacement.
 * Replace the entire index() method in your HomeController.php
 * (app/Controllers/HomeController.php) with the method below.
 *
 * WHAT IT DOES:
 * Caches the rendered homepage as a static HTML file in writable/cache/
 * Serves the cached file to non-logged-in visitors, bypassing all database
 * queries and PHP processing. Dramatically reduces TTFB on shared hosting.
 *
 * CACHE INVALIDATION:
 * The Varient CMS resetCacheStatic() function (called on publish/save/delete)
 * automatically clears the writable/cache/ folder, which includes this file.
 * A 60-minute expiry is also included as a fallback safety net.
 *
 * SESSION CHECK:
 * Uses vr_ses_id — the Varient CMS session key set on login.
 * Logged-in users always get a fresh uncached page.
 *
 * ALSO ADD to your PostController addPostPost() method after setSuccessMessage():
 *     $cachePath = WRITEPATH . 'cache/custom_home_cache.html';
 *     if (file_exists($cachePath)) {
 *         unlink($cachePath);
 *     }
 */

public function index()
{
    // Cache file location
    $cachePath = WRITEPATH . 'cache/custom_home_cache.html';

    // Cache max age in seconds (3600 = 1 hour)
    $maxCacheAge = 3600;

    // Serve cached version to non-logged-in visitors only
    // Uses vr_ses_id — the Varient CMS login session key
    if (file_exists($cachePath) && (time() - filemtime($cachePath) < $maxCacheAge) && !session()->has('vr_ses_id')) {
        echo file_get_contents($cachePath);
        return;
    }

    // No cache or logged in — run normal database queries
    ob_start();

    $data = [
        'title'       => $this->settings->home_title,
        'description' => $this->settings->site_description,
        'keywords'    => $this->settings->keywords,
        'homeTitle'   => $this->settings->home_title,
        'latestPosts' => $this->postModel->getLatestPosts($this->activeLang->id, POST_NUM_LOAD_MORE, 0)
    ];

    // Slider posts
    $data['sliderPosts'] = $data['latestPosts'];
    if ($this->generalSettings->show_latest_posts_on_slider != 1) {
        $data['sliderPosts'] = getSelectedPostsByType($this->postsSelected, 'slider');
    }

    // Featured posts
    $data['featuredPosts'] = $data['latestPosts'];
    if ($this->generalSettings->show_latest_posts_on_featured != 1) {
        $data['featuredPosts'] = getSelectedPostsByType($this->postsSelected, 'featured');
    }

    // Breaking news
    $data['breakingNews'] = getSelectedPostsByType($this->postsSelected, 'breaking');
    $data['userSession']  = getUserSession();

    echo loadView('partials/_header', $data);
    echo loadView('index', $data);
    echo loadView('partials/_footer', $data);

    // Capture output, save to cache file, and display
    $outputHTML = ob_get_contents();
    ob_end_clean();

    if (!empty($outputHTML)) {
        file_put_contents($cachePath, $outputHTML);
    }

    echo $outputHTML;
}
