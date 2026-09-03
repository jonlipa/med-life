# Med Life

Med Life is a secure healthcare operations portal built with PHP MVC, MySQL,
and server-rendered pages. The canonical application is located in
[`medlife-php/`](medlife-php/).

## Key Features

- Separate roles for administrators, doctors, reception staff, and patients
- Appointment, patient, medical result, billing, and notification management
- Authentication with TOTP or one-time verification codes delivered by email
- CSRF protection, secure session cookies, and rate limiting
- HTTPS and HSTS support behind a reverse proxy or the local HTTPS proxy
- Responsive interface with accessibility improvements
- MySQL migrations, health checks, and development seed data

## Technology Stack

- PHP 8+ with a framework-free MVC architecture
- MySQL or MariaDB
- Server-rendered HTML and CSS
- Minimal JavaScript for progressive enhancement

## Quick Start on Windows

1. Clone the repository and enter the project directory:

   ```powershell
   git clone https://github.com/jonlipa/med-life.git
   cd med-life
   ```

2. Create the local configuration file:

   ```powershell
   Copy-Item .\medlife-php\.env.example .\medlife-php\.env
   ```

3. Update the MySQL credentials in `medlife-php/.env`.

4. Start the application:

   ```powershell
   .\start-med-life.cmd
   ```

The launcher checks MySQL, runs the health check, prepares the database, and
starts the application with local HTTPS when the required runtime is available.

## Manual Startup

```powershell
cd medlife-php
.\start-mysql.cmd
.\php-runtime.cmd scripts\health_check.php
.\migrate.cmd
.\seed-demo.cmd
.\php-runtime.cmd -S 127.0.0.1:8000 -t public public/index.php
```

The application will be available at `http://127.0.0.1:8000`. The main launcher
uses `https://127.0.0.1:8443` by default.

## Project Structure

```text
med-life/
├── medlife-php/
│   ├── app/                 # MVC controllers, repositories, views, and core
│   ├── bootstrap/           # Application initialization
│   ├── config/              # Default configuration
│   ├── database/migrations/ # MySQL schema and migrations
│   ├── public/              # Public entry point and assets
│   ├── routes/              # HTTP routes
│   └── scripts/             # Setup, health checks, and maintenance
├── start-med-life.cmd       # Main Windows launcher
└── README.md
```

## Security

Environment files, local databases, logs, certificates, and runtime artifacts
are excluded from Git. For production deployments:

- use unique credentials and immediately replace every demo account password;
- enable HTTPS, HSTS, and `SESSION_COOKIE_SECURE`;
- terminate TLS through a trusted reverse proxy;
- never publish private certificates or database backups;
- run all migrations and the health check before deployment.

## Syntax Check

```bash
find medlife-php -type f -name '*.php' -not -path '*/vendor/*' -print0 \
  | xargs -0 -n1 php -l
```

Extended documentation is available in
[`medlife-php/README.md`](medlife-php/README.md).
