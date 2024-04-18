## NBA Fixture Simulator

This project will handle NBA Fixture simulation week by week.

## Code Philosophy

In this project, I've focused on readability, cleanliness, and maintainability. The application follows the principles
of **SOLID**, **KISS (Keep It Simple, Stupid)** and **DRY (Don't Repeat Yourself)**. While the project is small, I've opted for
simplicity and a bit over-engineering.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing
purposes.

### Prerequisites

Before you begin, ensure you have the following requirements:

- [Composer](https://getcomposer.org/)
- [PHP 8.2](https://www.php.net/releases/8.2/en.php)
- [Node.js](https://nodejs.org/en/download/current)
- [Redis](https://redis.io/)
- [Homebrew](https://brew.sh/)(optional)
- [Laravel Valet](https://laravel.com/docs/11.x/valet)(optional)

1. Clone Repository from Github:

    ```bash
    git clone https://github.com/bugrasercanseker/nba-fixture.git nba-fixture
    ```

2. Change into the project directory:

    ```bash
    cd nba-fixture
    ```

3. Install PHP:

    ```bash
    composer install

4. Copy the `.env.example` file to `.env`:

    ```bash
    cp .env.example .env

5. Update database keys in `.env`:

    ```bash
    DB_CONNECTION=mysql
    DB_DATABASE=nba_fixture
    DB_USERNAME=<username>
    DB_PASSWORD=<password>
   ```

6. Update queue keys in `.env`:

    ```bash
    REDIS_CLIENT=predis
    REDIS_HOST=<host>
    REDIS_PASSWORD=<password>
    REDIS_PORT=<port>
   ```

7. Update Reverb keys in `.env`:

    ```bash
    REVERB_APP_ID=<app-id>
    REVERB_APP_KEY=<app-key>
    REVERB_APP_SECRET=<app-secret>
    REVERB_HOST="localhost"
    REVERB_PORT=8080
    REVERB_SCHEME=http
    
    VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
    VITE_REVERB_HOST="${REVERB_HOST}"
    VITE_REVERB_PORT="${REVERB_PORT}"
    VITE_REVERB_SCHEME="${REVERB_SCHEME}"
   ```

8. Generate the application key:

    ```bash
    php artisan key:generate
    ```

9. Run the database migrations and seeders:

    ```bash
    php artisan migrate --seed
    ```

10. Install npm dependencies

    ```bash
    npm install
    ```
   
11. Build the app

    ```bash
    npm run build
    ```

12. Start Laravel Horizon for Queue:

    ```bash
    php artisan horizon
    ```

13. Start Laravel Reverb for Websockets:

    ```bash
    php artisan reverb:start
    ```

14. Start the development server with Artisan or Valet:

    ```bash
    php artisan serve
    ```

    or if you are using Valet

    ```bash
    valet link nba-fixture
    ```

15. Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) or [http://nba-fixture.test](http://nba-fixture.test) with
    your browser to make sure everything is up and running.

### Imagine the case
![NCBA](https://cdn.leonardo.ai/users/4087294c-abac-440c-8090-47e1123d5735/generations/14ef94c5-25b6-4968-982f-b7b2fbd84d6d/Default_Stray_cat_with_big_eyes_smiling_playing_basketball_in_0.jpg)
National Cat Basketball Association
