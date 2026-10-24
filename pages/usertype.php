<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'User Type';
$fields = ['USERTYPE' => 'User Type'];
$table = 'tbl_usertype';
$title = 'User Type';
$searchCols = ['USERTYPE'];
include __DIR__ . '/simple_crud.php';
