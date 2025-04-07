<?php
function ascii_border($text) {
    $lines = explode("\n", $text);
    $maxLength = max(array_map('strlen', $lines));
    
    $padding = 6; 
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
