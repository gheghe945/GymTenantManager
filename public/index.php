<?php
/**
 * Main entry point for the application
 * 
 * This file handles all incoming requests and routes them
 * to the appropriate controllers
 */

// Define application root path
define('APP_ROOT', dirname(__DIR__));

// Load bootstrap
require_once APP_ROOT . '/app/bootstrap.php';

// Get the current URI
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Create a router instance
$router = new Router();

// Apply tenant middleware to identify the current tenant
$tenantMiddleware = new TenantMiddleware();
$tenantMiddleware->handle();

// Define routes
// Auth routes
$router->register('login', 'AuthController@login');
$router->register('logout', 'AuthController@logout');

// Dashboard route
$router->register('', 'DashboardController@index');
$router->register('dashboard', 'DashboardController@index');

// User routes
$router->register('users', 'UserController@index');
$router->register('users/create', 'UserController@create');
$router->register('users/store', 'UserController@store');
$router->register('users/edit/([0-9]+)', 'UserController@edit');
$router->register('users/update/([0-9]+)', 'UserController@update');
$router->register('users/delete/([0-9]+)', 'UserController@delete');
$router->register('users/disable/([0-9]+)', 'UserController@disable');
$router->register('users/enable/([0-9]+)', 'UserController@enable');
$router->register('users/resetPassword/([0-9]+)', 'UserController@resetPassword');
$router->register('users/updatePassword/([0-9]+)', 'UserController@updatePassword');
$router->register('users/invite', 'InviteController@index');

// Course routes
$router->register('courses', 'CourseController@index');
$router->register('courses/create', 'CourseController@create');
$router->register('courses/store', 'CourseController@store');
$router->register('courses/edit/([0-9]+)', 'CourseController@edit');
$router->register('courses/update/([0-9]+)', 'CourseController@update');
$router->register('courses/delete/([0-9]+)', 'CourseController@delete');
$router->register('courses/calendar', 'CourseController@calendar');

// Calendar API routes
$router->register('calendar/addCourseAjax', 'CalendarController@addCourseAjax');

// Membership routes
$router->register('memberships', 'MembershipController@index');
$router->register('memberships/create', 'MembershipController@create');
$router->register('memberships/store', 'MembershipController@store');
$router->register('memberships/edit/([0-9]+)', 'MembershipController@edit');
$router->register('memberships/update/([0-9]+)', 'MembershipController@update');
$router->register('memberships/delete/([0-9]+)', 'MembershipController@delete');

// Attendance routes
$router->register('attendance', 'AttendanceController@index');
$router->register('attendance/create', 'AttendanceController@create');
$router->register('attendance/store', 'AttendanceController@store');

// Payment routes
$router->register('payments', 'PaymentController@index');
$router->register('payments/create', 'PaymentController@create');
$router->register('payments/store', 'PaymentController@store');

// Report routes
$router->register('reports', 'ReportController@index');
$router->register('reports/members', 'ReportController@members');
$router->register('reports/attendance', 'ReportController@attendance');
$router->register('reports/revenue', 'ReportController@revenue');

// Settings routes
$router->register('settings', 'SettingController@index');
$router->register('settings/saveSmtp', 'SettingController@saveSmtp');
$router->register('settings/testSmtp', 'SettingController@testSmtp');

// Invite routes
$router->register('invites', 'InviteController@index');
$router->register('invites/send', 'InviteController@send');
$router->register('register/([a-zA-Z0-9]+)', 'InviteController@register');
$router->register('invites/process/([a-zA-Z0-9]+)', 'InviteController@process');

// Gym Settings routes
$router->register('gym-settings', 'GymSettingsController@index');
$router->register('gym-settings/save', 'GymSettingsController@save');

// Invite detail routes
$router->register('invites/details/([a-zA-Z0-9]+)', 'InviteController@details');
$router->register('invites/sendEmail/([a-zA-Z0-9]+)', 'InviteController@sendEmail');

// Password Reset routes
$router->register('password/reset', 'PasswordResetController@index');
$router->register('password/request', 'PasswordResetController@request');
$router->register('password/reset/confirm/([a-zA-Z0-9]+)', 'PasswordResetController@confirm');
$router->register('password/reset', 'PasswordResetController@reset');

// Tenant routes (Super Admin only)
$router->register('tenants', 'TenantController@index');
$router->register('tenants/create', 'TenantController@create');
$router->register('tenants/store', 'TenantController@store');
$router->register('tenants/edit/([0-9]+)', 'TenantController@edit');
$router->register('tenants/update/([0-9]+)', 'TenantController@update');
$router->register('tenants/delete/([0-9]+)', 'TenantController@delete');

// Process the request
$router->dispatch($uri);
