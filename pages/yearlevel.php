<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Year Level';
$fields = ['YEARLEVEL' => 'Year Level', 'DESCRIPTION' => 'Description'];
$table = 'tbl_yearlevel';
$title = 'Year Level';
$searchCols = ['YEARLEVEL', 'DESCRIPTION'];
include __DIR__ . '/simple_crud.php';
