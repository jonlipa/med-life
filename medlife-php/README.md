# Med Life PHP

`medlife-php/` eshte aplikacioni kanonik aktiv i workspace-it.

Stack-u aktiv:

- frontend server-rendered HTML/CSS
- backend `vanilla PHP MVC`
- databaze `MySQL`

## Nisja lokale

Entry point-i i rekomanduar eshte nga root:

```bat
..\start-med-life.cmd
```

Ky workflow perdor:

- MySQL lokal sipas kredencialeve ne `.env`
- `start-mysql.cmd` per te kontrolluar ose nisur sherbimin lokal MySQL kur ekziston ne Windows
- `php-runtime.cmd` per te ngarkuar `pdo_mysql`
- HTTPS proxy lokal me certifikatat ne `..\certs\` kur niset nga `..\start-med-life.cmd`
- `scripts/health_check.php` per diagnostikim para nisjes

Nese do komandat manuale:

```bat
start-mysql.cmd
php-runtime.cmd scripts\health_check.php
migrate.cmd
seed-demo.cmd
php-runtime.cmd -S 127.0.0.1:8000 -t public public/index.php
```

Ne PowerShell, thirri komandat lokale me `.\`:

```powershell
.\start-mysql.cmd
.\php-runtime.cmd scripts\health_check.php
.\migrate.cmd
.\seed-demo.cmd
.\php-runtime.cmd -S 127.0.0.1:8000 -t public public/index.php
```

URL:

```text
https://127.0.0.1:8443  # default kur niset me ..\start-med-life.cmd
http://127.0.0.1:8000   # backend lokal; ridrejtohet ne HTTPS kur niset nga launcher-i
http://127.0.0.1:8001   # backend fallback kur 8000 eshte i zene
```

## SSL / HTTPS

Aplikacioni ka mbeshtetje per HTTPS pas reverse proxy ose hosting-ut qe terminon SSL-in
(p.sh. Nginx, IIS, Caddy, Cloudflare Tunnel ose load balancer). PHP built-in server
perdoret vetem per zhvillim lokal dhe nuk sherben TLS direkt.

Per testim lokal me certifikatat ekzistuese ne `..\certs\`:

```powershell
.\start-https.cmd
```

Pastaj hape:

```text
https://127.0.0.1:8443
```

`..\start-med-life.cmd` dhe `start-https.cmd` e nisin projektin me SSL si default.
Gjate nisjes ato vendosin runtime config:

```env
APP_URL=https://127.0.0.1:8443
APP_FORCE_HTTPS=true
APP_HSTS_ENABLED=true
SESSION_COOKIE_SECURE=true
```

Prandaj request-et direkte ne backend-in HTTP ridrejtohen te URL-ja HTTPS dhe session
cookie dergohet vetem permes lidhjes secure. Nese 8443 eshte i zene, launcher-i zgjedh
portin tjeter te lire dhe perditeson `APP_URL` per ate proces.

Certifikata lokale mund te shfaqe browser warning nese CA nuk eshte trusted ne Windows.
Launcher-i kryesor `..\start-med-life.cmd` therrret `trust-local-cert.cmd` dhe e shton
certifikaten lokale te `CurrentUser\Trusted Root` kur mungon. Nese Chrome ka qene i hapur
para importimit, mbylle dhe hape perseri qe te rifreskohet trust store.

Per production vendos:

```env
APP_URL=https://domaini-yt.com
APP_FORCE_HTTPS=true
APP_HSTS_ENABLED=true
APP_HSTS_MAX_AGE=31536000
APP_HSTS_INCLUDE_SUBDOMAINS=true
APP_HSTS_PRELOAD=false
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_SAMESITE=Lax
```

Reverse proxy duhet te dergoje header-in:

```text
X-Forwarded-Proto: https
```

Me kete konfigurim app-i ben redirect nga HTTP ne HTTPS, perdor `Secure` per session
cookie, dhe dergon security headers duke perfshire HSTS vetem kur request-i eshte HTTPS.
Per zhvillim manual pa proxy mund t'i mbash keto vlera `false`/`auto` ne `.env`; launcher-i
i SSL i mbishkruan vetem gjate procesit te nisjes secure.

## Email OTP ne MySQL

Ky app ruan dhe verifikon kodet e login-it me email vetem ne MySQL.

Sigurohu qe migrimet jane ekzekutuar:

```bat
migrate.cmd
```

Kodet OTP ruhen ne tabelen `email_verification_codes`.

## Sjellja ne setup mode

Kur databaza nuk eshte gati:

- faqet publike dhe auth forms renderohen me fallback data te kontrolluara
- dashboards dhe POST flows kthejne setup/unavailable response te qarte
- `scripts/health_check.php` raporton statusin e runtime-it dhe databazes

## Struktura

```text
medlife-php/
|-- app/
|-- bootstrap/
|-- config/
|-- database/migrations/
|-- public/
|-- routes/
|-- scripts/
|-- .env.example
|-- php-runtime.cmd
|-- migrate.cmd
`-- seed-demo.cmd
```

## Kredenciale demo

- `admin / ChangeMe#2026`
- `dr_arben / Doctor#2026!`
- `reception / Reception#2026!`
- `patient_aurora / Patient#2026!`

## Med Life visual restoration

Faqja publike dhe panelet kryesore perdorin nje teme klinike te rindertuar nga referencat "Med Life":

- Paleta kryesore: `#EEF4F8` per sfond klinik, `#FFFFFF` per siperfaqe, `#0F8EA6` dhe `#137C97` per veprime, `#1B3446` per tekst, `#6E8394` per tekst dytesor, `#DCE7EF` per border.
- Hierarkia vizuale: hero i madh me marken Med Life, doktor vizual si anchor, cards kompakte per statistika, grid sherbimesh, profile doktorash dhe CTA rezervimi.
- Dashboard-et ruajne logjiken ekzistuese, por marrin sidebar teal, cards me radius 8px, tabela kompakte, charts dhe panele me kontrast me te qarte.

### Responsive strategy

- Mobile: nen `640px`, layout-et kalojne ne nje kolone, menuja behet toggle, imazhet perdorin aspect-ratio stabile.
- Tablet: `640px-1023px`, hero dhe seksionet kryesore kalojne ne nje kolone me cards te plota.
- Desktop: `1024px+`, hero perdor dy kolona dhe doctors/services perdorin grid.
- Wide: `1280px+`, permbajtja kufizohet me `.container` per lexueshmeri dhe ruan spacing te qendrueshem.

### Accessibility

- Layout publik ka skip link drejt `#main-content`, landmark `main`, nav me `aria-label`, dhe mobile toggle me `aria-expanded`.
- Imazhet kane `alt` pershkrues; imazhet jo kritike perdorin `loading="lazy"`.
- Input-et ruajne label-et eksplicite dhe focus states te dukshme me kontrast te larte.
- `prefers-reduced-motion` redukton animacionet per perdoruesit qe e kerkojne.

### Performance and compatibility

- CSS/JS jane lokale, pa font ose script te jashtem.
- JavaScript eshte `defer` dhe perdoret vetem per progressive enhancement te menuse mobile.
- Hero image ka dimensione dhe `fetchpriority="high"` per LCP; imazhet e doktorave kane dimensione/aspect-ratio per te shmangur CLS.
- Synohet kompatibilitet me Chrome, Firefox, Safari dhe Edge moderne.

## Shenime

- `medilife-mitm-security/` eshte subtree autoritativ reference per security demo.
- `medlife-mitm-security/` eshte vetem pointer legacy dhe nuk duhet trajtuar si projekt paralel.
- `_archive/` ne root mbetet reference-only.
