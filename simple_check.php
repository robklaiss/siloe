<?php
print_r('Starting script...');
try {
    print_r('Creating PDO connection...');
    $pdo = new PDO('sqlite:/Users/robinklaiss/Dev/siloe/database/siloe.db');
    print_r('Connection successful');
} catch (Exception $e) {
    print_r('Error: ' . $e->getMessage());
}
?>
