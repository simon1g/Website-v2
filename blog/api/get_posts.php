<?php
header('Content-Type: application/json');

$posts_dir = __DIR__ . '/../posts/';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$start = $page * $per_page;

$files = glob($posts_dir . '*.json');

// Load all posts with their dates for sorting
$posts = [];
foreach ($files as $file) {
    $content = json_decode(file_get_contents($file), true);
    if ($content && isset($content['date']) && isset($content['time'])) {
        $posts[] = $content;
    }
}

// Sort posts by date and time using DateTime objects
usort($posts, function($a, $b) {
    $datetime_a = DateTime::createFromFormat('Y-m-d H:i:s', $a['date'] . ' ' . $a['time']);
    $datetime_b = DateTime::createFromFormat('Y-m-d H:i:s', $b['date'] . ' ' . $b['time']);
    
    if ($datetime_a && $datetime_b) {
        return $datetime_b <=> $datetime_a; // Descending order (newest first)
    }
    return 0;
});

// Return paginated results
$paginated_posts = array_slice($posts, $start, $per_page);

echo json_encode($paginated_posts);
