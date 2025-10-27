<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Models/User.php';
require_once __DIR__ . '/src/Models/Ticket.php';
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Services/TicketService.php';

use App\Services\AuthService;
use App\Services\TicketService;

// Start session
session_start();

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Disable cache for development
    'debug' => true,
]);

// Add debug extension
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Initialize services
$authService = new AuthService();
$ticketService = new TicketService();

// Get current URL path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove trailing slash
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

// Flash message helper
function setFlash($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash() {
    $message = $_SESSION['flash_message'] ?? null;
    $type = $_SESSION['flash_type'] ?? 'success';

    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);

    return ['message' => $message, 'type' => $type];
}

// Get current page name for navbar highlighting
function getCurrentPage($path) {
    if (str_starts_with($path, '/dashboard')) return 'dashboard';
    if (str_starts_with($path, '/tickets')) return 'tickets';
    return 'home';
}

// Routing
$method = $_SERVER['REQUEST_METHOD'];
$flash = getFlash();

try {
    // Common template variables
    $templateVars = [
        'is_authenticated' => $authService->isAuthenticated(),
        'user' => $authService->getCurrentUser(),
        'current_page' => getCurrentPage($path),
        'flash_message' => $flash['message'],
        'flash_type' => $flash['type'],
    ];

    // Routes
    switch (true) {
        // Home page
        case $path === '/' || $path === '/index.php':
            echo $twig->render('pages/home.html.twig', $templateVars);
            break;

        // Login page (GET)
        case $path === '/login' && $method === 'GET':
            if ($authService->isAuthenticated()) {
                header('Location: /dashboard');
                exit;
            }
            echo $twig->render('pages/login.html.twig', $templateVars);
            break;

        // Login action (POST)
        case $path === '/login' && $method === 'POST':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $authService->login($email, $password);

            if ($user) {
                $authService->createSession($user);
                setFlash('Welcome back!', 'success');
                header('Location: /dashboard');
            } else {
                setFlash('Invalid email or password', 'error');
                header('Location: /login');
            }
            exit;

        // Signup page (GET)
        case $path === '/signup' && $method === 'GET':
            if ($authService->isAuthenticated()) {
                header('Location: /dashboard');
                exit;
            }
            echo $twig->render('pages/signup.html.twig', $templateVars);
            break;

        // Signup action (POST)
        case $path === '/signup' && $method === 'POST':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $authService->signup($email, $name, $password);

            if ($user) {
                $authService->createSession($user);
                setFlash('Account created successfully!', 'success');
                header('Location: /dashboard');
            } else {
                setFlash('Email already exists', 'error');
                header('Location: /signup');
            }
            exit;

        // Logout
        case $path === '/logout':
            $authService->logout();
            setFlash('Logged out successfully', 'success');
            header('Location: /');
            exit;

        // Dashboard
        case $path === '/dashboard':
            if (!$authService->isAuthenticated()) {
                header('Location: /login');
                exit;
            }

            $stats = $ticketService->getTicketStats();
            $templateVars['stats'] = $stats;

            echo $twig->render('pages/dashboard.html.twig', $templateVars);
            break;

        // Tickets page
        case $path === '/tickets':
            if (!$authService->isAuthenticated()) {
                header('Location: /login');
                exit;
            }

            $tickets = $ticketService->getAllTickets();
            $templateVars['tickets'] = $tickets;

            echo $twig->render('pages/tickets.html.twig', $templateVars);
            break;

        // Get single ticket (AJAX)
        case preg_match('#^/tickets/get/(.+)$#', $path, $matches):
            if (!$authService->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $ticketId = $matches[1];
            $ticket = $ticketService->getTicketById($ticketId);

            header('Content-Type: application/json');
            echo json_encode($ticket);
            exit;

        // Create ticket
        case $path === '/tickets/create' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                header('Location: /login');
                exit;
            }

            $ticketService->createTicket([
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'],
                'priority' => $_POST['priority'] ?? 'medium',
            ]);

            setFlash('Ticket created successfully', 'success');
            header('Location: /tickets');
            exit;

        // Update ticket
        case $path === '/tickets/update' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                header('Location: /login');
                exit;
            }

            $ticketService->updateTicket($_POST['ticketId'], [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'],
                'priority' => $_POST['priority'] ?? 'medium',
            ]);

            setFlash('Ticket updated successfully', 'success');
            header('Location: /tickets');
            exit;

        // Delete ticket
        case $path === '/tickets/delete' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                header('Location: /login');
                exit;
            }

            $ticketService->deleteTicket($_POST['ticketId']);

            setFlash('Ticket deleted successfully', 'success');
            header('Location: /tickets');
            exit;

        // 404 Not Found
        default:
            http_response_code(404);
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p><a href="/">Go back home</a></p>';
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>500 - Internal Server Error</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    if (true) { // Set to false in production
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}
