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
