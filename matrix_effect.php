<!-- matrix effect -->
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
