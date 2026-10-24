<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Section';
$fields = ['SECTION' => 'Section', 'DESCRIPTION' => 'Description'];
$table = 'tbl_section';
$title = 'Section';
$searchCols = ['SECTION', 'DESCRIPTION'];
include __DIR__ . '/simple_crud.php';
