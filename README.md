# FamilyTree

FamilyTree is a modern, interactive web application designed to help you visualize and manage your family history. Built with Laravel and Livewire, it offers a seamless experience for creating and exploring family connections.

## Features

-   **Interactive Visualization**: Explore your family tree with a dynamic, zoomable graph powered by D3.js.
-   **Manage Family Members**: Easily add, edit, and remove family members.
-   **Relationship Tracking**: Define relationships including parents, spouses, and children.
-   **Photo Management**: Upload and crop profile photos for each family member.
-   **Multiple Trees**: Create and manage multiple distinct family trees.
-   **Dark Mode**: Fully supported dark mode for a comfortable viewing experience in any lighting.
-   **Responsive Design**: Works great on desktops, tablets, and mobile devices.
-   **Secure Authentication**: User accounts to keep your family data private.

## Tech Stack

-   **Backend**: [Laravel](https://laravel.com)
-   **Frontend**: [Livewire](https://livewire.laravel.com), [Blade](https://laravel.com/docs/blade)
-   **Styling**: [Tailwind CSS](https://tailwindcss.com)
-   **Interactivity**: [Alpine.js](https://alpinejs.dev)
-   **Visualization**: [D3.js](https://d3js.org)
-   **Database**: MySQL / SQLite (configurable)

## Installation

1.  **Clone the repository**
    ```bash
    git clone https://github.com/yourusername/family-tree.git
    cd family-tree
    ```

2.  **Install PHP dependencies**
    ```bash
    composer install
    ```

3.  **Install NPM dependencies**
    ```bash
    npm install
    ```

4.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Configure your database settings in `.env`.

5.  **Run Migrations**
    ```bash
    php artisan migrate
    ```

6.  **Build Assets**
    ```bash
    npm run build
    ```

7.  **Serve the Application**
    ```bash
    php artisan serve
    ```

    Visit `http://localhost:8000` in your browser.

## Usage

1.  **Register/Login**: Create an account to start.
2.  **Create a Tree**: Go to "My Trees" and create a new family tree.
3.  **Add People**: Start adding people to your tree. You can add parents, spouses, and children directly from a person's profile.
4.  **Visualize**: Click "View Tree" to see the interactive graph. Use the "Show Whole Tree" button to see all connections or focus on a specific individual.

## License

This project is open-sourced software licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
