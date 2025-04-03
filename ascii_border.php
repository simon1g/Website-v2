<?php
function ascii_border($text) {
    $lines = explode("\n", $text);
    $maxLength = max(array_map('strlen', $lines));
    
    // Add extra spaces to pad the content for a better fit
    $padding = 6; // Adjust this padding as needed for the button size
    $topBorder = '+' . str_repeat('-', $maxLength + $padding) . "+\n";
    $asciiBox = $topBorder;
    
    foreach ($lines as $line) {
        $linePadding = ($maxLength - strlen($line)) / 2;
        $asciiBox .= "|   " . str_repeat(' ', $linePadding) . $line . str_repeat(' ', $linePadding) . "   |\n";
    }
    
    $asciiBox .= $topBorder;
    
    return '<pre style="display: inline-block;">' . htmlspecialchars($asciiBox) . '</pre>';
}
?>
