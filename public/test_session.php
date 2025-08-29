<?php
session_start();
echo "<pre>";
print_r([
    "session_id" => session_id(),
    "user_id" => $_SESSION["user_id"] ?? "Not set",
    "user_role" => $_SESSION["user_role"] ?? "Not set",
    "all_session_data" => $_SESSION
]);
?>
