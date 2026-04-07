<?php
// Simple test file - no includes
header('Content-Type: text/plain');
echo "TEST FILE WORKS\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Segments: " . print_r(explode('/', trim($_SERVER['REQUEST_URI'], '/')), true) . "\n";
