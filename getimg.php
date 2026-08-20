<?php

function getImg() {
    if (!isset($_GET['story'], $_GET['image'])) return false;
    $mimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];
    $storyID = basename($_GET['story']);
    $imageName = basename($_GET['image']);

    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    if (!in_array($ext, array_keys($mimes))) {
        http_response_code(404);
        die();
    }

    $filePath = __DIR__.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.$storyID.DIRECTORY_SEPARATOR. '/' . $imageName;
    if (!file_exists($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        die();
    }

    $mime = isset($mimes[$ext])? $mimes[$ext] : 'application/octet-stream';

    while (ob_get_level()) ob_end_clean();
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=86400');
    readfile($filePath);
    exit;
}
