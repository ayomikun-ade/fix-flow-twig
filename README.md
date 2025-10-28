# FixFlow - Streamlined Ticket Management System

## Overview

FixFlow is a robust ticket management system developed using Vanilla PHP and Twig, designed to offer a clear and productive way to track, organize, and resolve support tickets. It features a lightweight backend for secure user authentication and essential ticket validation, while leveraging client-side `localStorage` for dynamic ticket data management and an interactive user experience.

## Features

- **User Authentication**: Manages user registration, login, and session validation, persisting user data to JSON files on the server.
- **Client-Side Ticket Management**: Employs browser `localStorage` for responsive creation, retrieval, updating, and deletion of ticket data, enhancing UI performance.
- **Backend Validation**: Ensures data integrity and access control by validating all ticket operations and user authentications through PHP services.
- **Templating with Twig**: Utilizes the Twig templating engine for a structured and maintainable front-end rendering process.

## Getting Started

### Installation

To set up FixFlow locally, follow these steps:

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/ayomikun-ade/fix-flow-twig.git
    cd fix-flow-twig
    ```
2.  **Install Dependencies**:
    ```bash
    composer install
    ```
    This command will install the Twig templating engine. It also creates a `cache` directory and sets its permissions.
3.  **Run with PHP's Built-in Server (Development)**:
    For quick local development and testing, you can use the built-in PHP web server:
    ```bash
    php -S 0.0.0.0:8000 -t .
    ```
    Then, navigate to `http://localhost:8000` in your web browser.

### Environment Variables

This project currently does not utilize external environment variables (e.g., via a `.env` file). Data file paths are hardcoded within the service classes. For production deployments, it is recommended to externalize such configurations.

## Usage

Once the application is installed and running, you can interact with FixFlow via your web browser.

- **Accessing the Application**: Open your web browser and navigate to the configured base URL (e.g., `http://localhost:8000`).
- **Authentication**:
  - **Sign Up**: On the `/auth/signup` page, create a new user account. Your full name, email, and a hashed password are securely stored on the server (`data/users.json`). Upon successful registration, a secure session token is generated and stored in your browser's `localStorage` for automatic login on subsequent visits.
  - **Log In**: Use your registered email and password on the `/auth/login` page. A successful login establishes a server-side session and provides a `localStorage` token, allowing the client-side application to recognize your authenticated state.
  - **Demo Credentials**: For quick testing, use the pre-configured demo account:
    - **Email**: `test@example.com`
    - **Password**: `test123`
- **Dashboard Overview**: After logging in, you will be redirected to the `/dashboard`. This page provides a high-level summary of your tickets, categorized by 'Total', 'Open', 'In Progress', and 'Closed'. These statistics are dynamically calculated in real-time from the ticket data stored in your browser's `localStorage`. An interactive chart visually represents your ticket distribution.
- **Ticket Management**: Navigate to the `/tickets` page to access the full ticket management interface.
  - **Create Ticket**: Click the "Create Ticket" button to open a modal form. Fill in the required details (title, status) and optional information (description, priority). The backend performs critical validation checks, but the actual ticket data is saved directly into your browser's `localStorage`, providing an extremely fast and responsive user experience.
  - **View Tickets**: All your created tickets are displayed in an organized grid. The data is retrieved instantly from `localStorage`, ensuring a smooth browsing experience.
  - **Edit Ticket**: To modify a ticket, click the 'Edit' button associated with it. This re-opens the ticket creation/edit modal pre-populated with the ticket's current details. After making changes, the backend validates the updated information, and the ticket is then updated within `localStorage`.
  - **Delete Ticket**: Select a ticket and confirm its deletion via the 'Delete' button and subsequent confirmation modal. The backend validates the deletion request, and the ticket is permanently removed from your `localStorage`.
  - **Search Tickets**: Utilize the search bar to efficiently filter your tickets by keywords present in their title, description, or status. This search is performed client-side on your `localStorage` data.
- **Logout**: Clicking "Logout" securely terminates your server-side session and removes all `ticketapp_session` data from your browser's `localStorage`, ensuring your account is completely signed out.

## Features

- 🌐 **Secure Authentication**: Robust user registration and login system with password hashing.
- 🎟️ **Intuitive Ticket Workflow**: Create, read, update, and delete support tickets seamlessly.
- 📊 **Interactive Dashboard**: Gain insights into ticket statuses with real-time analytics and a dynamic chart.
- 🔍 **Efficient Search**: Quickly locate tickets using client-side search functionality.
- ⚡ **Responsive Client-Side Operations**: Tickets are managed in `localStorage` for lightning-fast UI interactions.
- 🎨 **Modern UI/UX**: Crafted with Twig, Tailwind CSS, and Franken UI for a clean and engaging design.
- 🛡️ **Session Management**: Secure user session handling across the application.

## Technologies Used

| Technology         | Description                                                    | Link                                                                                                                                 |
| :----------------- | :------------------------------------------------------------- | :----------------------------------------------------------------------------------------------------------------------------------- |
| PHP                | Server-side scripting language for core logic and validation   | [php.net](https://www.php.net/)                                                                                                      |
| Twig               | Flexible, fast, and secure templating engine for PHP           | [twig.symfony.com](https://twig.symfony.com/)                                                                                        |
| Composer           | Dependency Manager for PHP                                     | [getcomposer.org](https://getcomposer.org/)                                                                                          |
| HTML5              | Standard markup language for structuring web content           | [html.spec.whatwg.org](https://html.spec.whatwg.org/multipage/)                                                                      |
| Tailwind CSS       | Utility-first CSS framework for rapid UI development           | [tailwindcss.com](https://tailwindcss.com/)                                                                                          |
| Franken UI         | Lightweight and accessible UI component library                | [franken-ui.dev](https://franken-ui.dev/)                                                                                            |
| JavaScript         | Client-side scripting for dynamic behavior and API interaction | [developer.mozilla.org/en-US/docs/Web/JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)                           |
| `localStorage` API | Browser API used for client-side persistence of ticket data    | [developer.mozilla.org/en-US/docs/Web/API/Window/localStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage) |

## Contributing

We welcome contributions to FixFlow! If you have ideas, suggestions, or want to report a bug, please feel free to contribute.

- 💡 **Suggest Features or Report Bugs**: Open an issue in the repository to discuss new features or point out any problems you encounter.
- 🛠️ **Submit Code Changes**:
  1.  Fork the repository.
  2.  Create your feature branch (`git checkout -b feature/your-feature-name`).
  3.  Commit your changes following conventional commits (`git commit -m 'feat: Add a new dazzling feature'`).
  4.  Push your branch (`git push origin feature/your-feature-name`).
  5.  Open a Pull Request describing your changes.

## License

This project is licensed under the MIT License.

## Author Info

- **Ayomikun Ade**
  - [LinkedIn](https://linkedin.com/in/YOUR_LINKEDIN_USERNAME)
  - [Twitter](https://twitter.com/YOUR_TWITTER_USERNAME)

---

![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg?logo=php&logoColor=white)
![Twig Version](https://img.shields.io/badge/Twig-3.x-green.svg?logo=twig&logoColor=white)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)
![Built With Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-black?style=flat&logo=tailwind-css&logoColor=white)
![UI Kit Franken UI](https://img.shields.io/badge/UI_Kit-FrankenUI-purple?style=flat)
![Data Persistence localStorage](https://img.shields.io/badge/Data%20Persistence-localStorage-orange?style=flat&logo=html5)
[![Readme was generated by Dokugen](https://img.shields.io/badge/Readme%20was%20generated%20by-Dokugen-brightgreen)](https://www.npmjs.com/package/dokugen)
