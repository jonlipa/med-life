# Med Life

Med Life është një portal i sigurt për menaxhimin e proceseve klinike, i ndërtuar
me PHP MVC, MySQL dhe faqe server-rendered. Aplikacioni kryesor ndodhet në
[`medlife-php/`](medlife-php/).

## Veçoritë kryesore

- Role të ndara për administratorin, mjekun, recepsionin dhe pacientin
- Menaxhim i termineve, pacientëve, rezultateve, faturimit dhe njoftimeve
- Autentikim me TOTP ose kod njëpërdorimësh të dërguar me email
- Mbrojtje CSRF, session cookies të sigurta dhe rate limiting
- Mbështetje për HTTPS/HSTS pas reverse proxy ose proxy-t lokal
- Ndërfaqe responsive me përmirësime për aksesueshmërinë
- Migrime MySQL, health check dhe të dhëna demo për zhvillim

## Stack-u

- PHP 8+ me arkitekturë MVC pa framework
- MySQL ose MariaDB
- HTML dhe CSS server-rendered
- JavaScript minimal për progressive enhancement

## Nisja e shpejtë në Windows

1. Klono repository-n dhe hyr në dosjen e projektit:

   ```powershell
   git clone https://github.com/jonlipa/med-life.git
   cd med-life
   ```

2. Krijo konfigurimin lokal:

   ```powershell
   Copy-Item .\medlife-php\.env.example .\medlife-php\.env
   ```

3. Përditëso kredencialet e MySQL në `medlife-php/.env`.

4. Nise aplikacionin:

   ```powershell
   .\start-med-life.cmd
   ```

Launcher-i kontrollon MySQL, ekzekuton health check-un, përgatit databazën dhe
nis aplikacionin me HTTPS lokal kur runtime-i përkatës është i disponueshëm.

## Nisja manuale

```powershell
cd medlife-php
.\start-mysql.cmd
.\php-runtime.cmd scripts\health_check.php
.\migrate.cmd
.\seed-demo.cmd
.\php-runtime.cmd -S 127.0.0.1:8000 -t public public/index.php
```

Aplikacioni do të jetë në `http://127.0.0.1:8000`. Launcher-i kryesor përdor
`https://127.0.0.1:8443` si adresë të parazgjedhur.

## Struktura

```text
med-life/
├── medlife-php/
│   ├── app/                 # MVC: controllers, repositories, views dhe core
│   ├── bootstrap/           # Inicializimi i aplikacionit
│   ├── config/              # Konfigurimi standard
│   ├── database/migrations/ # Skema dhe migrimet MySQL
│   ├── public/              # Entry point dhe asetet publike
│   ├── routes/              # Rrugët HTTP
│   └── scripts/             # Setup, health check dhe mirëmbajtje
├── start-med-life.cmd       # Launcher-i kryesor për Windows
└── README.md
```

## Siguria

Skedarët `.env`, databazat lokale, log-et, certifikatat dhe materialet runtime
nuk ruhen në Git. Për production:

- përdor kredenciale unike dhe ndrysho menjëherë çdo llogari demo;
- aktivizo HTTPS, HSTS dhe `SESSION_COOKIE_SECURE`;
- përfundo TLS në një reverse proxy të besueshëm;
- mos publiko certifikata private ose kopje të databazës;
- ekzekuto migrimet dhe health check-un para deploy-it.

## Kontrolli i sintaksës

```bash
find medlife-php -type f -name '*.php' -not -path '*/vendor/*' -print0 \
  | xargs -0 -n1 php -l
```

Dokumentacioni i zgjeruar gjendet te
[`medlife-php/README.md`](medlife-php/README.md).
