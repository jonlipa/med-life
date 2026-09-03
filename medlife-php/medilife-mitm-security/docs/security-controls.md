# MediLife Security Controls Documentation

**Dokumentim i Kontrolleve të Sigurisë**

---

## Përmbledhje

Ky dokument përshkruan të gjitha kontrollat e sigurisë së implementuara në portalin MediLife. Çdo kontroll është hartëzuar me standardet e industrisë dhe referencat përkatëse.

---

## 1. Kontrollat e Autentikimit

### 1.1 Argon2id Password Hashing

**Implementimi:**
- Algoritmi: Argon2id (winner i Password Hashing Competition)
- Time cost: 3 iteracione
- Memory cost: 64 MB
- Parallelism: 4 threads
- Hash length: 32 bytes

**Referencat:**
- OWASP Password Storage Cheat Sheet
- RFC 9106 (Argon2)

**Pse Argon2id:**
- Rezistent ndaj sulmeve GPU/ASIC
- Memory-hard (vështirëson sulmet paralele)
- Kombinon mbrojtjen e Argon2i dhe Argon2d

### 1.2 Server-Side Sessions

**Implementimi:**
- Session ID: 32 bytes random (cryptographically secure)
- Storage: PostgreSQL database
- Hashing: SHA-256 për session ID në database

**Karakteristikat:**
- Session rotation në login
- Session rotation në privilege change
- Idle timeout: 15 minuta (default)
- Absolute timeout: 8 orë (default)
- Forced invalidation në logout

**Referencat:**
- OWASP Session Management Cheat Sheet
- NIST SP 800-63B

---

## 2. Kontrollat e Autorizimit (RBAC)

### 2.1 Role-Based Access Control

**Rolet e përcaktuara:**

| Roli | Aksesi |
|------|--------|
| Admin | Menaxhimi i përdoruesve, audit logs, caktimi i mjekëve |
| Doctor | Pacientët e caktuar, regjistrime mjekësore, programimet |
| Reception | Demografia e pacientëve, programimet, intake forms |
| Patient | Profili personal, programimet vetjake, regjistrimet |

### 2.2 Field-Level Authorization

**Fushat e kontrolluara:**

| Fusha | Admin | Doctor | Reception | Patient |
|-------|-------|--------|-----------|---------|
| first_name | ✓ | ✗ | ✓ | ✗ |
| last_name | ✓ | ✗ | ✓ | ✗ |
| phone | ✓ | ✗ | ✓ | ✓ (own) |
| email | ✓ | ✗ | ✓ | ✓ (own) |
| diagnosis | ✓ | ✓ | ✗ | ✓ (view own) |
| treatment | ✓ | ✓ | ✗ | ✓ (view own) |

**Referencat:**
- OWASP Access Control Cheat Sheet

---

## 3. Mbrojtja CSRF

### 3.1 Synchronizer Token Pattern

**Implementimi:**
- Token unik për çdo formë
- Token expiration: 1 orë
- SSL strict mode: kërkohet HTTPS
- Double-submit cookie: jo përdorur (token server-side)

**Formularët e mbrojtur:**
- Login form
- Patient intake form
- Appointment form
- User management form
- Medical record form
- Profile edit form

**Referencat:**
- OWASP CSRF Prevention Cheat Sheet
- RFC 6265 (Cookies)

---

## 4. Enkriptimi i të Dhënave

### 4.1 AES-256-GCM për të Dhëna Klinike

**Fushat e enkriptuara:**
- Diagnosis (diagnoza)
- Treatment (trajtimi)
- Notes (shënime mjekësore)

**Karakteristikat:**
- Algoritmi: AES-256-GCM (authenticated encryption)
- Nonce: 96-bit random (përdorim i vetëm)
- Key derivation: SHA-256 nga APP_DATA_KEY
- Auth tag: 128-bit (integritet i garantuar)

**Pse AES-GCM:**
- Siguron konfidencialitet dhe integritet
- Performancë e lartë (hardware acceleration)
- Nuk kërton padding (stream cipher mode)

**Referencat:**
- NIST SP 800-38D (GCM recommendation)
- OWASP Cryptographic Storage Cheat Sheet

### 4.2 TLS 1.3 për Transport

**Konfigurimi:**
- Minimum TLS version: 1.2
- Preferred: TLS 1.3
- Cipher suites të lejuara:
  - TLS_AES_256_GCM_SHA384
  - TLS_CHACHA20_POLY1305_SHA256
  - TLS_AES_128_GCM_SHA256

**HSTS:**
- max-age: 31536000 (1 vit)
- includeSubDomains: enabled
- preload: enabled

**Referencat:**
- RFC 8446 (TLS 1.3)
- RFC 6797 (HSTS)
- OWASP TLS Cheat Sheet

---

## 5. Security Headers

### 5.1 Headerët e Implementuar

| Header | Vlera | Qëllimi |
|--------|-------|---------|
| Strict-Transport-Security | max-age=31536000; includeSubDomains | Detyron HTTPS |
| Content-Security-Policy | default-src 'self' | Parandalon XSS |
| X-Content-Type-Options | nosniff | Parandalon MIME sniffing |
| X-Frame-Options | DENY | Parandalon clickjacking |
| Referrer-Policy | strict-origin-when-cross-origin | Kontrollon referrer |
| Cache-Control | no-store | Parandalon caching të të dhënave |

**Referencat:**
- OWASP Secure Headers Project
- RFC 6797 (HSTS)
- CSP Level 3 Specification

---

## 6. Audit Logging

### 6.1 Eventet e Regjistruara

**Autentikimi:**
- LOGIN (success/failure)
- LOGOUT
- Session expiry

**Aksesi në të dhëna:**
- VIEW_RECORD (patient record access)
- EDIT_RECORD (medical record modification)

**Administrim:**
- CREATE_USER
- EDIT_USER
- DELETE_USER
- ASSIGN_DOCTOR

**CRUD Operations:**
- CREATE_PATIENT
- EDIT_PATIENT
- CREATE_APPOINTMENT
- CANCEL_APPOINTMENT
- CHANGE_PASSWORD

### 6.2 Informacionet e Regjistruara

Për çdo event:
- Actor ID (kush e kryeu)
- Action (çfarë veprimi)
- Target type dhe ID (në çfarë)
- Timestamp (kur)
- Source IP (nga ku)
- Outcome (rezultati)
- Details (JSON me detaje shtesë)

### 6.3 Storage

- PostgreSQL: tabela `audit_events`
- File log: rotating file (10MB max, 10 backups)
- Retention: konfigurueshme (default: 1 vit)

**Referencat:**
- NIST SP 800-92 (Logging Guide)
- OWASP Logging Cheat Sheet

---

## 7. Mbrojtja e Patient Privacy (HIPAA Considerations)

### 7.1 Minimum Necessary Rule

- Reception sheh vetëm demografi (non-clinical)
- Doctor sheh vetëm pacientët e caktuar
- Patient sheh vetëm të dhënat e veta

### 7.2 Access Logging

- Çdo akses në patient record regjistrohet
- Audit log i qasshëm vetëm për admin
- No soft deletes (records are immutable)

### 7.3 Data Encryption

- Sensitive fields encrypted at rest
- TLS për të gjithë komunikimin
- No PHI në URL parameters

**Referencat:**
- HIPAA Privacy Rule (45 CFR 164.502)
- HIPAA Security Rule (45 CFR 164.312)

---

## 8. Mbrojtja ndaj Sulmeve të Përbashkta

### 8.1 SQL Injection

**Mbrojtja:**
- SQLAlchemy ORM (parameterized queries)
- No raw SQL në application code
- Input validation me WTForms

### 8.2 Cross-Site Scripting (XSS)

**Mbrojtja:**
- CSP header (script-src 'self')
- Auto-escaping në Jinja2 templates
- Input sanitization me bleach library

### 8.3 Session Hijacking

**Mbrojtja:**
- Secure cookies (HTTPS only)
- HttpOnly (no JavaScript access)
- SameSite=Strict (no cross-site sending)
- Session rotation në login

### 8.4 Man-in-the-Middle (MitM)

**Mbrojtja:**
- TLS 1.3 me cipher suites të forta
- HSTS (nuk pranon HTTP)
- Certificate validation (CA e brendshme)
- No plaintext communication

**Referencat:**
- OWASP Top 10
- CWE/SANS Top 25

---

## 9. Security Testing Checklist

### 9.1 Automated Tests

- [ ] Password hashing tests (test_auth.py)
- [ ] Session management tests (test_auth.py)
- [ ] CSRF protection tests (test_csrf.py)
- [ ] RBAC tests (test_rbac.py)
- [ ] Encryption tests (test_crypto.py)
- [ ] Security headers tests (test_headers.py)

### 9.2 Manual Verification

- [ ] TLS configuration (openssl s_client)
- [ ] Certificate chain validation
- [ ] Session timeout behavior
- [ ] Access control for each role
- [ ] Audit log completeness
- [ ] Error handling (no information leakage)

---

## 10. Konfigurimi për Production

### 10.1 Environment Variables Required

```bash
# Security keys (generate with: python manage.py genkey)
export FLASK_SECRET_KEY="<random-32-bytes>"
export APP_DATA_KEY="<random-32-bytes>"

# Database
export DATABASE_URL="postgresql://user:password@localhost/medilife_portal"

# Session configuration
export SESSION_IDLE_MINUTES=15
export SESSION_ABSOLUTE_HOURS=8

# Logging
export AUDIT_LOG_FILE="/var/log/medilife/audit.log"
export AUDIT_TO_CONSOLE=false
```

### 10.2 WSGI Server Configuration

**Për production, përdorni Gunicorn:**

```bash
gunicorn --workers 4 \
         --bind 127.0.0.1:8000 \
         --certfile ../certs/server.crt \
         --keyfile ../certs/server.key \
         app:app
```

**Me Nginx reverse proxy:**

```nginx
server {
    listen 443 ssl http2;
    server_name medilife.internal;

    ssl_certificate /path/to/server.crt;
    ssl_certificate_key /path/to/server.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

*Fund i Security Controls Documentation*
