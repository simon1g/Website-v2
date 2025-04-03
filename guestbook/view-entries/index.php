<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>simon1g</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <!--<link rel="stylesheet" href="/styles.css"> -->
    <link rel="stylesheet" href="/guestbook/view-entries/guestbook_view.css">
    <link rel="icon" href="/icon.ico">
</head>
<body>
    <!-- Matrix effect behind the content -->
    <div class="matrix">
        <?php
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789抖音';
            $screenWidth = 100; // Number of characters across the width
            for ($i = 0; $i < $screenWidth; $i++) {
                $leftPosition = $i * 2; // Spacing between columns
                $animationDuration = rand(5, 15); // Randomize duration for variety
                echo "<span style='left: {$leftPosition}vw; animation-duration: {$animationDuration}s;'>"
                    . mb_substr($characters, rand(0, mb_strlen($characters) - 1), 1)
                    . "</span>";
            }
        ?>
    </div>

    <div class="page-container">
        <!-- Title Section -->
        <div class="title">
            <h1>Guestbook Entries</h1>
        </div>

        <!-- Return Section -->
        <div class="return">
            <button onclick="window.location.href='/guestbook/'">Back to Guestbook</button>
        </div>

        <!-- Content Section -->
        <div class="content">
            <div class="guestbook-entries">
                <?php
                    // Path to the guestbook JSON file
                    $guestbookFile = __DIR__ . '/../guestbook.json';

                    // Check if the file exists
                    if (file_exists($guestbookFile)) {
                        // Load and decode the JSON data
                        $guestbookData = json_decode(file_get_contents($guestbookFile), true);

                        // Check if the JSON data was decoded properly
                        if ($guestbookData && is_array($guestbookData)) {
                            // Reverse the order of the entries so the newest ones appear first
                            $guestbookData = array_reverse($guestbookData);

                            // Display each entry
                            foreach ($guestbookData as $entry) {
                                echo "<div class='guestbook-entry'>";
                                echo "<p>" . htmlspecialchars($entry['name']) . " said:</p>";
                                echo "<p>" . nl2br(htmlspecialchars($entry['message'])) . "</p>";
                                echo "<small>Posted on " . htmlspecialchars($entry['timestamp']) . "</small>";
                                echo "</div><hr>";
                            }
                        } else {
                            echo "<p>No valid entries found in the guestbook.</p>";
                        }
                    } else {
                        echo "<p>No entries yet.</p>";
                    }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
