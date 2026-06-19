<?php
echo "Starting session... ";
if (!is_dir(__DIR__ . '/sessions')) {
    mkdir(__DIR__ . '/sessions', 0777, true);
}
session_save_path(__DIR__ . '/sessions');
session_start();
echo "Session started!";
