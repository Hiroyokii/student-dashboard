<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'School Year';
$fields = ['SCHOOLYEAR' => 'School Year'];
$table = 'tbl_schoolyear';
$title = 'School Year';
$searchCols = ['SCHOOLYEAR'];
include __DIR__ . '/simple_crud.php';
