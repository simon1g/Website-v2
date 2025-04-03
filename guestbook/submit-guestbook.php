<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
$config = include(get_include_path('/conf/config.php'));
$guestbookFile = get_json_path('guestbook', 'guestbook');
$bannedWords = ['nigger'];
$secretKey = $config['turnstile_secret_key'];

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to censor banned words
function censorMessage($message, $bannedWords) {
    foreach ($bannedWords as $word) {
        $message = str_ireplace($word, str_repeat('*', strlen($word)), $message);
    }
    return $message;
}

// Turnstile secret key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate user input
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $turnstileResponse = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';

    

// Check for name character limit
    if (strlen($name) > 30) {
        die("Name exceeds the character limit of 30.");
    }

    // Check for message character limit

    if (strlen($message) > 50) {
        die("Message exceeds the character limit of 50.");
    }

    // Check for empty fields

    if (empty($name) || empty($message)) {
        die("Name and message are required.");
    }

    // Censor name if it contains banned words
    $name = censorMessage($name, $bannedWords);

    // Verify Turnstile response
    $verifyResponse = file_get_contents("https://challenges.cloudflare.com/turnstile/v0/siteverify", false, stream_context_create([
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query([
                'secret' => $secretKey,
                'response' => $turnstileResponse,
            ]),
        ],
    ]));
    $verificationResult = json_decode($verifyResponse, true);

    if (!$verificationResult['success']) {
        die("Bot verification failed. Please try again.");
    }

    // Censor message
    $message = censorMessage($message, $bannedWords);

    // Load existing guestbook data
    if (file_exists($guestbookFile)) {
        $guestbookData = json_decode(file_get_contents($guestbookFile), true);
    } else {
        $guestbookData = [];
    }

    // Add the new entry
    $entry = [
        'name' => $name,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    $guestbookData[] = $entry;

    // Save updated guestbook data
    if (false === file_put_contents($guestbookFile, json_encode($guestbookData, JSON_PRETTY_PRINT))) {
        die("Error saving guestbook data. Please try again.");
    }

    // Redirect back to the guestbook page
    header('Location: /guestbook/index.php');
    exit;
}
?>
