<?php
header('Content-Type: application/json');

// Simple response for testing
echo json_encode([
    'success' => true,
    'test' => 'working'
]);
?>