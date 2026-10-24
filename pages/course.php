<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Course';
$fields = ['COURSE' => 'Course', 'DESCRIPTION' => 'Description'];
$table = 'tbl_course';
$title = 'Course';
$searchCols = ['COURSE', 'DESCRIPTION'];
include __DIR__ . '/simple_crud.php';
