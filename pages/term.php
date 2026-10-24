<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Term';
$fields = ['TERM' => 'Term', 'DESCRIPTION' => 'Description'];
$table = 'tbl_term';
$title = 'Term';
$searchCols = ['TERM', 'DESCRIPTION'];
include __DIR__ . '/simple_crud.php';
