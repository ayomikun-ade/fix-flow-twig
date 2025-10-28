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
        case $path === '/auth/login' && $method === 'GET':
            if ($authService->isAuthenticated()) {
                header('Location: /dashboard');
                exit;
            }
            echo $twig->render('pages/login.html.twig', $templateVars);
            break;

        // Login action (POST) - API style response for localStorage
        case $path === '/auth/login' && $method === 'POST':
            // Check if it's an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validation
            if (empty($email) || empty($password)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['error' => 'Email and password are required']);
                    exit;
                } else {
                    setFlash('Email and password are required', 'error');
                    header('Location: /auth/login');
                    exit;
                }
            }

            $user = $authService->login($email, $password);

            if ($user) {
                $sessionData = $authService->createSession($user);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Welcome back!',
                        'session' => $sessionData,
                        'redirect' => '/dashboard'
                    ]);
                    exit;
                } else {
                    setFlash('Welcome back!', 'success');
                    header('Location: /dashboard');
                    exit;
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid email or password']);
                    exit;
                } else {
                    setFlash('Invalid email or password', 'error');
                    header('Location: /auth/login');
                    exit;
                }
            }

        // Signup page (GET)
        case $path === '/auth/signup' && $method === 'GET':
            if ($authService->isAuthenticated()) {
                header('Location: /dashboard');
                exit;
            }
            echo $twig->render('pages/signup.html.twig', $templateVars);
            break;

        // Signup action (POST) - API style response for localStorage
        case $path === '/auth/signup' && $method === 'POST':
            // Check if it's an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validation
            $errors = [];
            if (empty($name)) $errors[] = 'Name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (empty($password)) $errors[] = 'Password is required';
            if (!empty($password) && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';

            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['error' => implode(', ', $errors)]);
                    exit;
                } else {
                    setFlash(implode(', ', $errors), 'error');
                    header('Location: /auth/signup');
                    exit;
                }
            }

            $user = $authService->signup($email, $name, $password);

            if ($user) {
                $sessionData = $authService->createSession($user);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Account created successfully!',
                        'session' => $sessionData,
                        'redirect' => '/dashboard'
                    ]);
                    exit;
                } else {
                    setFlash('Account created successfully!', 'success');
                    header('Location: /dashboard');
                    exit;
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(409);
                    echo json_encode(['error' => 'Email already exists']);
                    exit;
                } else {
                    setFlash('Email already exists', 'error');
                    header('Location: /auth/signup');
                    exit;
                }
            }

        // Logout
        case $path === '/logout':
            $authService->logout();
            echo '<script>
                localStorage.removeItem("ticketapp_session");
                window.location.href = "/";
            </script>';
            exit;

        // Dashboard
        case $path === '/dashboard':
            if (!$authService->isAuthenticated()) {
                setFlash('Your session has expired — please log in again.', 'error');
                header('Location: /auth/login');
                exit;
            }

            $currentUser = $authService->getCurrentUser();
            // Stats will be computed from localStorage on client-side
            $templateVars['stats'] = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'closed' => 0];

            echo $twig->render('pages/dashboard.html.twig', $templateVars);
            break;

        // Tickets page
        case $path === '/tickets':
            if (!$authService->isAuthenticated()) {
                setFlash('Unauthorized access — please log in.', 'error');
                header('Location: /auth/login');
                exit;
            }

            $currentUser = $authService->getCurrentUser();
            // Tickets will be loaded from localStorage on client-side
            $templateVars['tickets'] = [];

            echo $twig->render('pages/tickets.html.twig', $templateVars);
            break;

        // Get single ticket (AJAX) - Now handled client-side via localStorage
        case preg_match('#^/tickets/get/(.+)$#', $path, $matches):
            if (!$authService->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized access — please log in']);
                exit;
            }

            // Return success - actual data comes from localStorage
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Use localStorage']);
            exit;

        // Create ticket - Validation only, storage in localStorage
        case $path === '/tickets/create' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized access — please log in']);
                exit;
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            $currentUser = $authService->getCurrentUser();

            // Validate required fields
            $title = trim($_POST['title'] ?? '');
            $status = $_POST['status'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';

            $errors = [];

            // Validate title (mandatory)
            if (empty($title)) {
                $errors[] = 'Title is required';
            }

            // Validate status (mandatory and must be valid value)
            $validStatuses = ['open', 'in_progress', 'closed'];
            if (empty($status)) {
                $errors[] = 'Status is required';
            } elseif (!in_array($status, $validStatuses)) {
                $errors[] = 'Status must be one of: open, in_progress, closed';
            }

            // Validate description length (optional but with constraints)
            if (!empty($description) && strlen($description) > 500) {
                $errors[] = 'Description must not exceed 500 characters';
            }

            if (!empty($errors)) {
                if ($isAjax) {
                    http_response_code(400);
                    echo json_encode(['error' => implode('. ', $errors)]);
                } else {
                    setFlash(implode('. ', $errors), 'error');
                    header('Location: /tickets');
                }
                exit;
            }

            // Validation passed - client will handle localStorage storage
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Ticket created successfully']);
            } else {
                setFlash('Ticket created successfully', 'success');
                header('Location: /tickets');
            }
            exit;

        // Update ticket - Validation only, storage in localStorage
        case $path === '/tickets/update' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized access — please log in']);
                exit;
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            $currentUser = $authService->getCurrentUser();

            // Validate required fields
            $ticketId = $_POST['ticketId'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $status = $_POST['status'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';

            $errors = [];

            // Validate title (mandatory)
            if (empty($title)) {
                $errors[] = 'Title is required';
            }

            // Validate status (mandatory and must be valid value)
            $validStatuses = ['open', 'in_progress', 'closed'];
            if (empty($status)) {
                $errors[] = 'Status is required';
            } elseif (!in_array($status, $validStatuses)) {
                $errors[] = 'Status must be one of: open, in_progress, closed';
            }

            // Validate description length (optional but with constraints)
            if (!empty($description) && strlen($description) > 500) {
                $errors[] = 'Description must not exceed 500 characters';
            }

            if (!empty($errors)) {
                if ($isAjax) {
                    http_response_code(400);
                    echo json_encode(['error' => implode('. ', $errors)]);
                } else {
                    setFlash(implode('. ', $errors), 'error');
                    header('Location: /tickets');
                }
                exit;
            }

            // Validation passed - client will handle localStorage storage
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Ticket updated successfully']);
            } else {
                setFlash('Ticket updated successfully', 'success');
                header('Location: /tickets');
            }
            exit;

        // Delete ticket - Handled in localStorage
        case $path === '/tickets/delete' && $method === 'POST':
            if (!$authService->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized access — please log in']);
                exit;
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            // Validation passed - client will handle localStorage storage
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Ticket deleted successfully']);
            } else {
                setFlash('Ticket deleted successfully', 'success');
                header('Location: /tickets');
            }
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
