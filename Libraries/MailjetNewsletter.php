<?php

namespace App\Libraries;

class MailjetNewsletter
{
    public function sendNewPostNotification($post, $postUrl)
    {
        // Load Mailjet credentials from general settings
        $db = \Config\Database::connect();
        $settings = $db->table('general_settings')->get()->getRowObject();

        if (empty($settings->mailjet_api_key) || empty($settings->mailjet_secret_key)) {
            log_message('error', 'MailjetNewsletter: Missing API credentials');
            return false;
        }

        // Get all subscribers
        $subscribers = $db->table('subscribers')->get()->getResultObject();
        if (empty($subscribers)) {
            log_message('error', 'MailjetNewsletter: No subscribers found');
            return false;
        }

        // Build the featured image URL
        $imageUrl = !empty($post->image_big) ? base_url($post->image_big) : '';

        // Build a clean excerpt
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

        $subject = 'New Post: ' . $post->title;
        $fromEmail = $settings->mailjet_email_address ?? 'stanceautomagmedia@gmail.com';
        $campaignName = 'NewPost_' . date('Ymd_His');

        // Build one message per recipient (required for per-recipient tracking in v3.1)
        $messages = [];
        foreach ($subscribers as $subscriber) {
            $messages[] = [
                'From' => [
                    'Email' => $fromEmail,
                    'Name'  => 'Stance Auto Magazine'
                ],
                'To' => [['Email' => $subscriber->email]],
                'Subject' => $subject,
                'HTMLPart' => $html,
                'TrackOpens' => 'enabled',
                'TrackClicks' => 'enabled',
                'CustomCampaign' => $campaignName,
                'DeduplicateCampaign' => false
            ];
        }

        // Mailjet v3.1 accepts up to 50 messages per API call — chunk accordingly
        $chunks = array_chunk($messages, 50);
        $totalSent = 0;
        $errors = [];

        foreach ($chunks as $chunk) {
            $payload = json_encode(['Messages' => $chunk]);

            $ch = curl_init('https://api.mailjet.com/v3.1/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERPWD, $settings->mailjet_api_key . ':' . $settings->mailjet_secret_key);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                log_message('error', 'MailjetNewsletter: cURL error - ' . $curlError);
                $errors[] = $curlError;
                continue;
            }

            $decoded = json_decode($response, true);

            if ($httpCode !== 200) {
                log_message('error', 'MailjetNewsletter: HTTP ' . $httpCode . ' - ' . $response);
                $errors[] = 'HTTP ' . $httpCode;
                continue;
            }

            $sent = isset($decoded['Messages']) ? count($decoded['Messages']) : 0;
            $totalSent += $sent;
            log_message('info', 'MailjetNewsletter: Chunk sent - ' . $sent . ' messages, HTTP ' . $httpCode . ' - ' . $response);
        }

        log_message('info', 'MailjetNewsletter: Complete - ' . $totalSent . ' sent, ' . count($errors) . ' errors');

        return empty($errors);
    }
}
