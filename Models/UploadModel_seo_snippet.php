<?php

/**
 * UploadModel SEO Filename Renamer — Code Snippet
 * Varient CMS Addon — Stance Auto Magazine
 *
 * This is NOT a complete file replacement.
 * Replace the uploadPostImage(), uploadQuizImage(), and uploadGalleryImage()
 * methods in your existing app/Models/UploadModel.php with the methods below.
 *
 * WHAT IT DOES:
 * Instead of giving uploaded images a generic timestamp name, this uses the
 * original filename from the user's device and converts it into a clean SEO slug.
 *
 * Example: "Ford Escort MK2.jpg" becomes "ford-escort-mk2-870x580-abc123.webp"
 *
 * This gives Google Image Search meaningful filename signals, helping your
 * images appear in relevant searches.
 *
 * WHY THIS MATTERS:
 * Google uses image filenames as one of many signals when indexing images.
 * A filename of "ford-escort-mk2-870x580.webp" tells Google exactly what the
 * image shows. A filename of "img_1234567890.webp" tells Google nothing.
 */

// ============================================================
// REPLACE uploadPostImage() with this:
// ============================================================

public function uploadPostImage($tempPath, $type)
{
    $img = Image::make($tempPath);

    // Grab the original filename from the uploaded file
    $uploadedFile = $this->request->getFile('file');
    $originalFilename = (!empty($uploadedFile) && method_exists($uploadedFile, 'getClientName')) ? $uploadedFile->getClientName() : '';

    // Strip extension and convert to clean SEO slug
    $rawFileName = !empty($originalFilename) ? pathinfo($originalFilename, PATHINFO_FILENAME) : 'car-photo';
    $cleanSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawFileName), '-'));
    if (empty($cleanSlug)) { $cleanSlug = 'car-photo'; }

    $name = '';
    if ($type == 'big') {
        $name = $cleanSlug . '-870x580-';
        $img->fit(870, 580);
    } elseif ($type == 'default') {
        $name = $cleanSlug . '-870x-';
        $img->resize(870, null, function ($constraint) {
            $constraint->aspectRatio();
        });
    } elseif ($type == 'slider') {
        $name = $cleanSlug . '-694x532-';
        $img->fit(694, 532);
    } elseif ($type == 'mid') {
        $name = $cleanSlug . '-430x256-';
        $img->fit(450, 280);
    } elseif ($type == 'small') {
        $name = $cleanSlug . '-140x98-';
        $img->fit(140, 98);
    }

    if ($this->getFileExt($tempPath) == 'webp') {
        $this->jpgQuality = 100;
    }
    $newPath = 'uploads/images/' . $this->createUploadDirectory('images') . $name . uniqid();
    $img->save(FCPATH . $newPath . $this->imgExt, $this->jpgQuality);
    return $this->convertImageFormat($newPath);
}

// ============================================================
// REPLACE uploadQuizImage() with this:
// ============================================================

public function uploadQuizImage($tempPath, $type)
{
    $img = Image::make($tempPath);

    $uploadedFile = $this->request->getFile('file');
    $originalFilename = (!empty($uploadedFile) && method_exists($uploadedFile, 'getClientName')) ? $uploadedFile->getClientName() : '';
    $rawFileName = !empty($originalFilename) ? pathinfo($originalFilename, PATHINFO_FILENAME) : 'quiz-photo';
    $cleanSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawFileName), '-'));
    if (empty($cleanSlug)) { $cleanSlug = 'quiz-photo'; }

    $name = '';
    if ($type == 'default') {
        $name = $cleanSlug . '-quiz-870x580-';
        $img->fit(870, 580);
    } elseif ($type == 'small') {
        $name = $cleanSlug . '-quiz-420x420-';
        $img->fit(420, 420);
    }
    if ($this->getFileExt($tempPath) == 'webp') {
        $this->jpgQuality = 100;
    }
    $newPath = 'uploads/quiz/' . $this->createUploadDirectory('quiz') . $name . uniqid();
    $img->save(FCPATH . $newPath . $this->imgExt, $this->jpgQuality);
    return $this->convertImageFormat($newPath);
}

// ============================================================
// REPLACE uploadGalleryImage() with this:
// ============================================================

public function uploadGalleryImage($tempPath, $width)
{
    $uploadedFile = $this->request->getFile('file');
    $originalFilename = (!empty($uploadedFile) && method_exists($uploadedFile, 'getClientName')) ? $uploadedFile->getClientName() : '';
    $rawFileName = !empty($originalFilename) ? pathinfo($originalFilename, PATHINFO_FILENAME) : 'gallery-photo';
    $cleanSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawFileName), '-'));
    if (empty($cleanSlug)) { $cleanSlug = 'gallery-photo'; }

    $name = $cleanSlug . '-gallery-' . $width . 'x-';
    $newPath = 'uploads/gallery/' . $this->createUploadDirectory('gallery') . $name . uniqid();

    $img = Image::make($tempPath);
    $img->resize($width, null, function ($constraint) {
        $constraint->aspectRatio();
    });
    $img->save(FCPATH . $newPath . $this->imgExt, 90);
    return $this->convertImageFormat($newPath);
}
