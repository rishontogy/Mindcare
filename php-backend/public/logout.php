<?php

/**
 * Logout - logout.php
 */

require_once __DIR__ . '/../init.php';

Session::destroy();
header('Location: ../../index.php');
exit();
