<?php
require 'koneksi.php';

session_start();
session_destroy();
header("Location:" . $base_url . "login");
exit;
