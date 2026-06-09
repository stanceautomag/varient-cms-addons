<?php

/**
 * MailjetNewsletter Library
 * Varient CMS Addon — Stance Auto Magazine
 *
 * Sends a newsletter notification to all subscribers when a new post is published.
 * Uses the Mailjet API v3.1 with bulk sending (single API call for all subscribers)
 * and full open/click tracking visible in your Mailjet dashboard.
 *
 * INSTALLATION:
 * 1. Drop this file into app/Libraries/MailjetNewsletter.php
 * 2. Make sure your Mailjet API key, secret key, and email address are configured
 *    in your Varient CMS admin under Settings > Email Settings
 * 3. Add the newsletter trigger to your PostController (see Controllers/PostController_newsletter_snippet.php)
 * 4. Add the newsletter checkbox to your publish box view (see Views/_publish_box.php)
 *
 * REQUIREMENTS:
 * - Mailjet account with API key and secret key
 * - PHP curl extension enabled
 * - Varient CMS general_settings table must have: mailjet_api_key, mailjet_secret_key, mailjet_email_address
 */

namespace App\Libraries;

class MailjetNewsletter
{
    public function sendNewPostNotification($post, $postUrl)
    {
        // Load Mailjet credentials from general settings
        $db = \Config\Database::connect();
        $settings = $db->table('general_settings')->get()->getRowObject();

        if (empty($settings->mailjet_api_key) || empty($settings->mailjet_secret_key)) {
            return false;
        }

        // Get all subscribers
        $subscribers = $db->table('subscribers')->get()->getResultObject();
        if (empty($subscribers)) {
            return false;
        }

        // Build recipient list for bulk send (single API call for all subscribers)
        $recipients = [];
        foreach ($subscribers as $subscriber) {
            $recipients[] = ['Email' => $subscriber->email];
        }

        // Build the featured image URL
        $imageUrl = !empty($post->image_big) ? base_url($post->image_big) : '';

        // Build a clean excerpt (strip HTML tags, limit to 200 characters)
        $excerpt = !empty($post->description) ? strip_tags($post->description) : '';
        $excerpt = strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt;

        // Build HTML email
        $html = '
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;">
            <div style="background:#111;padding:20px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;">Stance Auto Magazine</h1>
            </div>';

        if (!empty($imageUrl)) {
            $html .= '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($post->title) . '" style="width:100%;height:auto;display:block;">';
        }

        $html .= '
            <div style="padding:30px;">
                <h2 style="color:#111;font-size:22px;">' . htmlspecialchars($post->title) . '</h2>
                <p style="color:#555;font-size:16px;line-height:1.6;">' . htmlspecialchars($excerpt) . '</p>
                <div style="text-align:center;margin:30px 0;">
                    <a href="' . $postUrl . '" style="background:#e63946;color:#fff;padding:14px 30px;text-decoration:none;font-weight:bold;font-size:16px;border-radius:4px;">Read the Full Article</a>
                </div>
            </div>
            <div style="background:#111;padding:15px;text-align:center;">
                <p style="color:#aaa;font-size:12px;margin:0;">You are receiving this because you subscribed at stanceauto.co.uk</p>
            </div>
        </div>';

        // Send as a single bulk API call to all subscribers
        // CustomCampaign uses a timestamp so each send appears separately in Mailjet dashboard
        $payload = json_encode([
            'Messages' => [[
                'From' => [
                    'Email' => $settings->mailjet_email_address ?? 'your@email.com',
                    'Name'  => 'Stance Auto Magazine'
                ],
                'To' => $recipients,
                'Subject' => 'New Post: ' . $post->title,
                'HTMLPart' => $html,
                'TrackOpens' => 'enabled',
                'TrackClicks' => 'enabled',
                'CustomCampaign' => 'NewPost_' . date('Ymd_His'),
                'DeduplicateCampaign' => false
            ]]
        ]);

        $ch = curl_init('https://api.mailjet.com/v3.1/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, $settings->mailjet_api_key . ':' . $settings->mailjet_secret_key);
        curl_exec($ch);
        curl_close($ch);

        return true;
    }
}
