<?php

// These filenames match the files in the root Assets directory exactly.
// If you upload a new image to FileZilla, set the matching filename here.
$siteImages = [
    'hero_student' => 'Assets/student_typing_keyboard_text_1.jpg',
    'lecture_clip' => 'Assets/lecture-clip-placeholder.svg',
    'community_student' => 'Assets/student_college_laptop_university.jpg',
    'faculty_portrait' => 'Assets/faculty-portrait-placeholder.svg',
    'support_library' => 'Assets/support-library.jpg',
    'support_staff' => 'Assets/support-staff.jpg',
    'privacy_workspace' => 'Assets/Privacy_image.png',
    'privacy_image' => 'Assets/Privacy_image.png',
    'cookie_workspace' => 'Assets/Cookie_image.png',
];

function site_image_url(string $key): string
{
    global $siteImages;

    $fileName = $siteImages[$key] ?? '';

    if ($fileName === '') {
        return '';
    }

    return site_asset_url($fileName);
}

function site_asset_url(string $path): string
{
    if ($path === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#', $path) === 1) {
        return $path;
    }

    if (strpos($path, 'Assets/') === 0) {
        return encode_asset_path($path);
    }

    return encode_asset_path('Assets/' . ltrim($path, '/'));
}

function encode_asset_path(string $path): string
{
    $segments = explode('/', $path);
    $encodedSegments = array_map('rawurlencode', $segments);

    return implode('/', $encodedSegments);
}
