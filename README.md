# Digital Wallet — PHP Laravel + React

A production-style digital wallet application with a **Laravel** backend, **MySQL** database, and **React** frontend, orchestrated via **Docker Compose**.

## Architecture

| Service    | Technology           | Port  |
|------------|----------------------|-------|
| Backend    | PHP 8.3 / Laravel 11 | 8080  |
| Database   | MySQL 8.0            | 3306  |
| Frontend   | React 18 / Vite      | 3000  |

## Features

- **User Management** — Create users (each gets a wallet automatically)
- **Wallets** — View balances, deposit funds
- **Transfers** — Send money between wallets with pessimistic locking & deadlock prevention
- **Idempotency** — Optional idempotency key to prevent duplicate transfers
- **Transaction History** — View latest transactions with status badges

## Quick Start

```bash
docker-compose up --build
```

Once running:
- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8080/api

## API Endpoints

| Method | Endpoint                     | Description            |
|--------|------------------------------|------------------------|
| GET    | `/api/users`                 | List all users         |
| POST   | `/api/users`                 | Create a user          |
| GET    | `/api/users/{id}`            | Get user by ID         |
| POST   | `/api/wallets`               | Create a wallet        |
| GET    | `/api/wallets/{id}`          | Get wallet by ID       |
| POST   | `/api/wallets/{id}/deposit`  | Deposit funds          |
| POST   | `/api/transfer`              | Transfer between wallets |
| GET    | `/api/transactions`          | Recent transactions    |

## Project Structure

```
digital-wallet-laravel/
├── docker-compose.yml
├── backend/
│   ├── Dockerfile
│   ├── composer.json
│   ├── app/
│   │   ├── Http/Controllers/    # REST controllers
│   │   ├── Http/Requests/       # Form request validation
│   │   ├── Models/              # Eloquent models
│   │   ├── Services/            # Business logic
│   │   └── Exceptions/          # Custom exceptions
│   ├── database/migrations/     # DB schema
│   ├── routes/api.php           # API routes
│   ├── config/                  # Laravel config
│   ├── docker/                  # Nginx, Supervisor, entrypoint
│   └── bootstrap/app.php        # App bootstrap + exception handling
└── frontend/
    ├── Dockerfile
    ├── nginx.conf
    ├── src/
    │   ├── App.jsx
    │   ├── api/                 # Axios API client
    │   └── components/          # React components
    └── package.json
```

## Environment Variables

The `docker-compose.yml` includes all required environment variables. For local development outside Docker, copy `.env.example` to `.env` in the `backend/` directory and adjust as needed.


![Image](https://github.com/user-attachments/assets/92de5a33-7ef5-4145-a1d7-b7e7b86c3fc3)
