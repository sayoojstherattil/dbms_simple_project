<?php
$options = [
    'cost' => 10,
    'prefix' => '$2y$'
];
$hash = password_hash("password", PASSWORD_BCRYPT, $options);
echo $hash;
?>

