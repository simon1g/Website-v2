<?php
require_once(__DIR__ . '/config.php');

$memesDir = get_site_path('/randomMeme/memes');
$jsonFile = get_site_path('/randomMeme/memes.json');

$files = glob($memesDir . '/*.mp4');

$memeList = [];
foreach ($files as $file) {
    $memeList[] = basename($file);
}

if (file_put_contents($jsonFile, json_encode($memeList, JSON_PRETTY_PRINT))) {
    echo "Successfully updated memes list in " . basename($jsonFile) . "\n";
} else {
    echo "Failed to update memes list\n";
}
