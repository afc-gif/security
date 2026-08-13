<?php

/*
|--------------------------------------------------------------------------
| ARTSCI Modular Route Architecture
|--------------------------------------------------------------------------
|
| The routes are modularized by domain for clean separation of concerns:
| - auth.php      : Authentication & User Management
| - website.php   : Public Website, Content & Installations Gallery
| - commerce.php  : E-Commerce Shop, POS System, Products & Orders
| - operations.php: Operations, Clients, Job Requests, Inspections, Projects & Tasks
| - field.php     : Field Staff & Field Coordinator Workflows
| - finance.php   : Finance Dashboard, Expenses, Material Costs, Payments & Reports
|
*/

require __DIR__ . '/auth.php';
require __DIR__ . '/website.php';
require __DIR__ . '/commerce.php';
require __DIR__ . '/operations.php';
require __DIR__ . '/field.php';
require __DIR__ . '/finance.php';
