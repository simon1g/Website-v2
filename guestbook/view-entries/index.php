<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>simon1g</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/guestbook/view-entries/guestbook_view.css">
    <link rel="icon" href="/icon.ico">
</head>
<body>
    <div class="matrix">
        <?php
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789抖音';
            $screenWidth = 100;
            for ($i = 0; $i < $screenWidth; $i++) {
                $leftPosition = $i * 2;
                $animationDuration = rand(5, 15);
                echo "<span style='left: {$leftPosition}vw; animation-duration: {$animationDuration}s;'>"
                    . mb_substr($characters, rand(0, mb_strlen($characters) - 1), 1)
                    . "</span>";
            }
        ?>
    </div>

    <div class="page-container">
        <div class="title">
            <h1>Guestbook Entries</h1>
        </div>

        <div class="return">
            <button onclick="window.location.href='/guestbook/'">Back to Guestbook</button>
        </div>

        <div class="content">
            <div class="guestbook-entries">
                <?php
                    $guestbookFile = __DIR__ . '/../guestbook.json';

                    if (file_exists($guestbookFile)) {
                        $guestbookData = json_decode(file_get_contents($guestbookFile), true);

                        if ($guestbookData && is_array($guestbookData)) {
                            $guestbookData = array_reverse($guestbookData);
                            
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
