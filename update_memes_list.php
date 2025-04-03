<?php
require_once(__DIR__ . '/config.php');

// scan for mp4 files
$memesDir = get_site_path('/randomMeme/memes');
$jsonFile = get_site_path('/randomMeme/memes.json');

// get all mp4 files
$files = glob($memesDir . '/*.mp4');

// create a relative list of paths
$memeList = [];
foreach ($files as $file) {
    $memeList[] = basename($file);
}

// save to json
if (file_put_contents($jsonFile, json_encode($memeList, JSON_PRETTY_PRINT))) {
    echo "Successfully updated memes list in " . basename($jsonFile) . "\n";
} else {
    echo "Failed to update memes list\n";
}
