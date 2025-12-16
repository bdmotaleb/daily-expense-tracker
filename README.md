## Daily Expense Tracker (Laravel 10 + Vue 3 SPA)

A single-page application for tracking daily incomes, expenses, categories, budgets, and reports.  
Backend is built with **Laravel 10** (API + Sanctum), frontend with **Vue 3, Vite, Pinia, Vue Router, Axios, Chart.js**.

---

### 1. Requirements

**Option A: Docker (Recommended)**
- Docker & Docker Compose

**Option B: Local Development**
- PHP 8.1+
- Composer
- Node.js 18+ and npm
- MySQL (or MariaDB)

---

### 2. Docker Setup (Recommended)

1. **Clone the repository:**
```bash
git clone https://github.com/bdmotaleb/daily-expense-tracker.git
cd daily-expense-tracker
```

2. **Copy environment file:**
```bash
cp .env.example .env
```

3. **Update `.env` for Docker:**
```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=daily_expense_tracker
DB_USERNAME=daily_expense
DB_PASSWORD=root
```

4. **Build and start containers:**
```bash
docker-compose up -d --build
```

5. **Run initial setup (install dependencies, generate key, migrate, seed):**
```bash
make setup
```

Or manually:
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

6. **Access the application:**
- Frontend: http://localhost:8000
- Vite Dev Server: http://localhost:5173 (for hot reload)

**Useful Make commands (or use docker-compose directly):**
```bash
make build      # Build Docker containers
make up         # Start containers
make down       # Stop containers
make restart    # Restart containers
make logs       # View logs
make shell      # Access app container shell
make composer   # Install Composer dependencies
make migrate    # Run migrations
make seed       # Seed database
make fresh      # Fresh migration with seeding
make test       # Run tests
make clean      # Stop and remove volumes
```

**Direct docker-compose commands:**
```bash
# View logs
docker-compose logs -f

# Execute artisan commands
docker-compose exec app php artisan [command]

# Access MySQL
docker-compose exec mysql mysql -u daily_expense -proot daily_expense_tracker
```

---

### 3. Local Installation (Without Docker)

```bash
git clone https://github.com/bdmotaleb/daily-expense-tracker.git
cd daily-expense-tracker

composer install
npm install
```

Copy the environment file and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

---

### 4. Environment configuration

Edit `.env` to configure the database and Sanctum SPA URL:

```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daily_expense_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```

Ensure Sanctum stateful domains cover your SPA host (defaults work for local dev).

---

### 5. Database & seeders

Run migrations and seed default expense categories:

```bash
php artisan migrate
php artisan db:seed
```

---

### 6. Running the app (development)

Start the Laravel backend:

```bash
php artisan serve
```

Start the Vite dev server:

```bash
npm run dev
```

Open the app in your browser at `http://127.0.0.1:8000`.

---

### 7. Testing

Feature tests are provided for:

- Authentication (`tests/Feature/AuthTest.php`)
- Income API (`tests/Feature/IncomeTest.php`)
- Expense API (`tests/Feature/ExpenseTest.php`)

Run the test suite:

```bash
php artisan test
```

---

### 8. Production notes

- **API rate limiting**: All API routes use `throttle:api` (60 requests/min per user/IP). Auth endpoints (`/api/login`, `/api/register`) also use a stricter `throttle:auth` limiter (20/min per IP).
- **Authorization policies**: Policies are defined for `User`, `Income`, `Expense`, `Category`, and `Budget` to ensure users only access their own data.
- **Sanctum**: SPA-authenticated using cookies; ensure your production SPA domain is configured in `config/sanctum.php` and `.env`.
- **Build assets**: For production, build the frontend:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Serve the Laravel app with a proper web server (e.g., Nginx or Apache) pointing to the `public` directory.

---

### 9. Main features

- User registration & login (Sanctum)
- Income & expense CRUD with categories
- Category management (global + user-defined)
- Monthly budgets per category with warnings and progress bars
- Financial dashboard with charts (Chart.js)
- Filterable reports with CSV and PDF export

