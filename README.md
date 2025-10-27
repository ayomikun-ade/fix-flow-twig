# FixFlow - Twig Version

A ticket management system built with PHP and Twig templating engine. This is a conversion of the Next.js version to a traditional PHP application.

## Features

- User authentication (login/signup)
- Dashboard with ticket statistics
- Full CRUD operations for tickets
- Search and filter functionality
- Responsive design matching the Next.js version
- Session-based authentication
- JSON file-based data storage

## Requirements

- PHP 8.0 or higher
- Composer
- A web server (Apache, Nginx, or PHP's built-in server)

## Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure web server:**

   **Option A: Using PHP's built-in server (for development)**
   ```bash
   php -S localhost:8000
   ```
   Then visit `http://localhost:8000` in your browser.

   **Option B: Using Apache**
   - Ensure mod_rewrite is enabled
   - Point your virtual host to the project root directory
   - The `.htaccess` file will handle URL rewriting

   **Option C: Using Nginx**
   - Configure your server block to route all requests to `index.php`

## Default Test Account

Email: `test@example.com`
Password: `test123`

## Project Structure

```
fix-flow-twig/
├── public/              # Public assets (CSS, images, JS)
│   ├── css/
│   │   └── styles.css   # Main stylesheet
│   └── images/          # SVG images and logos
├── src/
│   ├── Models/          # Data models
│   │   ├── User.php
│   │   └── Ticket.php
│   └── Services/        # Business logic
│       ├── AuthService.php
│       └── TicketService.php
├── templates/           # Twig templates
│   ├── layouts/         # Base layouts
│   │   └── base.html.twig
│   ├── components/      # Reusable components
│   │   ├── navbar.html.twig
│   │   ├── hero.html.twig
│   │   ├── footer.html.twig
│   │   └── stats.html.twig
│   └── pages/           # Page templates
│       ├── home.html.twig
│       ├── login.html.twig
│       ├── signup.html.twig
│       ├── dashboard.html.twig
│       └── tickets.html.twig
├── data/                # JSON data files (auto-created)
│   ├── users.json
│   └── tickets.json
├── vendor/              # Composer dependencies
├── index.php            # Main application router
├── composer.json
└── .htaccess           # Apache rewrite rules
```

## Routes

- `/` - Homepage
- `/login` - Login page
- `/signup` - Signup page
- `/dashboard` - User dashboard (requires authentication)
- `/tickets` - Ticket management (requires authentication)
- `/logout` - Logout action

## API Endpoints

- `POST /login` - Login user
- `POST /signup` - Register new user
- `POST /logout` - Logout user
- `GET /tickets/get/{id}` - Get single ticket (AJAX)
- `POST /tickets/create` - Create new ticket
- `POST /tickets/update` - Update existing ticket
- `POST /tickets/delete` - Delete ticket

## Data Storage

The application uses JSON files for data persistence:

- **users.json** - Stores user accounts (with hashed passwords)
- **tickets.json** - Stores all tickets

Data files are created automatically in the `data/` directory on first run.

## Demo Data

The application initializes with:
- A test user account (test@example.com / test123)
- 5 sample tickets with various statuses and priorities

## Development

### Disable Template Caching

Template caching is disabled by default in `index.php`:

```php
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Change to a cache directory path in production
    'debug' => true,
]);
```

### Enable Production Mode

For production deployment:

1. Enable template caching:
   ```php
   'cache' => __DIR__ . '/cache/twig',
   ```

2. Disable debug mode:
   ```php
   'debug' => false,
   ```

3. Remove error details in index.php (line 244):
   ```php
   if (false) { // Set to false in production
   ```

## Styling

The application uses a custom CSS file (`public/css/styles.css`) that recreates the Tailwind-based styling from the Next.js version using:

- CSS custom properties for theming
- Utility classes similar to Tailwind
- Responsive design with media queries
- OKLCH color space for better color management

## Features Comparison with Next.js Version

✅ User authentication
✅ Dashboard with statistics
✅ Ticket CRUD operations
✅ Search functionality
✅ Responsive design
✅ Flash messages (toast notifications)
✅ Same UI/UX design
✅ Modal dialogs for create/edit/delete
✅ Status and priority badges

## Security Notes

- Passwords are hashed using PHP's `password_hash()` with bcrypt
- Sessions are used for authentication
- CSRF protection should be added for production use
- Input validation should be enhanced for production
- Consider using a proper database instead of JSON files for production

## License

This is a demonstration project for learning purposes.
