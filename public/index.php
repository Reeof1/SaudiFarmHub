<?php
declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/core/bootstrap.php';

use Core\Router;

$router = new Router();

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/farms', 'FarmController@index');
$router->get('/farm/view', 'FarmController@show');
$router->post('/search/farms', 'FarmController@search'); // AJAX

// Auth routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Booking routes
$router->get('/booking/availability', 'BookingController@availability');
$router->post('/booking/create', 'BookingController@create');
$router->post('/booking/update-status', 'BookingController@updateStatus');
$router->post('/booking/cancel', 'BookingController@cancel');

// Dashboards
$router->get('/dashboard/owner', 'OwnerDashboardController@index');
$router->get('/dashboard/owner/bookings', 'OwnerDashboardController@bookings');
$router->get('/dashboard/admin', 'AdminDashboardController@index');
$router->get('/dashboard/visitor', 'VisitorDashboardController@index');

// Visitor bookings
$router->get('/visitor/bookings', 'VisitorDashboardController@bookings');

// Farm owner management
$router->get('/owner/farms', 'OwnerFarmController@index');
$router->get('/owner/farm/create', 'OwnerFarmController@create');
$router->post('/owner/farm/store', 'OwnerFarmController@store');
$router->get('/owner/farm/edit', 'OwnerFarmController@edit');
$router->post('/owner/farm/update', 'OwnerFarmController@update');
$router->post('/owner/farm/delete', 'OwnerFarmController@delete');

// Owner activities & schedules
$router->get('/owner/farm/activities', 'OwnerActivityController@index');
$router->get('/owner/farm/activity/create', 'OwnerActivityController@create');
$router->post('/owner/farm/activity/store', 'OwnerActivityController@store');
$router->get('/owner/farm/activity/edit', 'OwnerActivityController@edit');
$router->post('/owner/farm/activity/update', 'OwnerActivityController@update');
$router->post('/owner/farm/activity/delete', 'OwnerActivityController@delete');

$router->get('/owner/farm/schedules', 'OwnerScheduleController@index');
$router->get('/owner/farm/schedule/create', 'OwnerScheduleController@create');
$router->post('/owner/farm/schedule/store', 'OwnerScheduleController@store');
$router->get('/owner/farm/schedule/edit', 'OwnerScheduleController@edit');
$router->post('/owner/farm/schedule/update', 'OwnerScheduleController@update');
$router->post('/owner/farm/schedule/delete', 'OwnerScheduleController@delete');

// Notifications
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/mark-read', 'NotificationController@markRead');
$router->get('/notifications/unread-count', 'NotificationController@unreadCount'); // AJAX

// Settings (profile)
$router->get('/settings', 'SettingsController@index');
$router->post('/settings', 'SettingsController@update');

// Admin farm approvals
$router->get('/admin/farms', 'AdminFarmController@index');
$router->post('/admin/farms/update-status', 'AdminFarmController@updateStatus');

// Admin users / reports / activity / alerts
$router->get('/admin/users', 'AdminUserController@index');
$router->post('/admin/users/update-role', 'AdminUserController@updateRole');
$router->post('/admin/users/toggle-status', 'AdminUserController@toggleStatus');

$router->get('/admin/reports', 'AdminReportController@index');
$router->post('/admin/reports/generate', 'AdminReportController@generate');

$router->get('/admin/activity', 'AdminActivityController@index');
$router->get('/admin/alerts', 'AdminAlertController@index');

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

