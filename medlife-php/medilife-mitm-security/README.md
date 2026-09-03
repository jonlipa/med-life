# MediLife MitM Security Project

**Projekt Sigurie për Mbrojtje ndaj Sulmeve Man-in-the-Middle (MitM)**

Institucioni: Universiteti Biznes Tiranë (UBT)  
Lëndë: Siguri Informacioni dhe Rrjetash  
Autor: [Emri Juaj]  
Data: [Data e Dorëzimit]

---

## Përmbajtja e Depozitës (Repository Layout)

```
medilife-mitm-security/
├── README.md                    # Ky dokument - udhëzime për konfigurimin
├── report/
│   └── report.md                # Raporti akademik në shqip
├── certs/
│   └── README.md                # Udhëzime për PKI/TLS dhe certifikata
├── evidence/
│   ├── packet-tracer-checklist.md    # Lista e kontrolleve për Packet Tracer
│   ├── ubt-lab-checklist.md          # Lista e kontrolleve për laboratorin UBT
│   └── ioc-evidence-template.md      # Model për të dhëna IOC
├── medilife-portal/
│   ├── app/
│   │   ├── __init__.py          # Inicializimi i aplikacionit Flask
│   │   ├── config.py            # Konfigurimet e sigurisë
│   │   ├── models.py            # Modelet e bazës së të dhënave
│   │   ├── auth.py              # Autentikimi dhe menaxhimi i sesioneve
│   │   ├── crypto.py            # Ndihmës për enkriptim AES-GCM
│   │   ├── decorators.py        # Dekoratorë për RBAC
│   │   ├── audit.py             # Regjistrim i auditimit të sigurisë
│   │   ├── routes/
│   │   │   ├── __init__.py
│   │   │   ├── admin.py         # Rrugët për administratorët
│   │   │   ├── doctor.py        # Rrugët për mjekët
│   │   │   ├── reception.py     # Rrugët për recepcionin
│   │   │   └── patient.py       # Rrugët për pacientët
│   │   └── templates/
│   │       ├── layouts/
│   │       │   ├── base.html    # Shablloni bazë
│   │       │   ├── admin.html   # Layout për admin
│   │       │   ├── doctor.html  # Layout për mjekë
│   │       │   ├── reception.html # Layout për recepcion
│   │       │   └── patient.html # Layout për pacientë
│   │       ├── components/
│   │       │   ├── navbar.html  # Shablloni i navigimit
│   │       │   ├── sidebar.html # Shablloni i sidebar
│   │       │   └── macros.html  # Makrot e formave
│   │       ├── admin/           # Faqet e administratorit
│   │       ├── doctor/          # Faqet e mjekut
│   │       ├── reception/       # Faqet e recepcionit
│   │       └── patient/         # Faqet e pacientit
│   ├── migrations/
│   │   ├── 001_initial.sql      # Skripti fillestar i bazës së të dhënave
│   │   └── 002_audit_table.sql  # Tabela e auditimit
│   ├── tests/
│   │   ├── __init__.py
│   │   ├── test_auth.py         # Testet e autentikimit
│   │   ├── test_rbac.py         # Testet e kontrollit të aksesit
│   │   ├── test_crypto.py       # Testet e enkriptimit
│   │   ├── test_csrf.py         # Testet e mbrojtjes CSRF
│   │   └── test_headers.py      # Testet e headerëve të sigurisë
│   ├── manage.py                # Komandat CLI (genkey, initdb, bootstrap)
│   ├── requirements.txt         # Varësitë e Python
│   └── run_https.sh             # Skript për nisjen në HTTPS demo
└── docs/
    └── security-controls.md     # Dokumentim i kontrolleve të sigurisë
```

---

## Renditja e Konfigurimit (Setup Order)

### Hapi 1: Konfiguro Certifikatat TLS

Shiko `certs/README.md` për udhëzime të plota:

```bash
cd certs
# Krijo Autoritetin e Certifikimit (CA)
# Krijo Certifikatën e Serverit me SAN
# Nënshkruaje certifikatën
# Verifiko me openssl s_client
```

### Hapi 2: Konfiguro Bazën e të Dhënave PostgreSQL

```bash
# Krijo bazën e të dhënave
createdb medilife_portal

# Ose përdor PostgreSQL me user të dedikuar
psql -U postgres -c "CREATE USER medilife WITH PASSWORD 'secure_password';"
psql -U postgres -c "CREATE DATABASE medilife_portal OWNER medilife;"
```

### Hapi 3: Instaloni Varësitë

```bash
cd medilife-portal
pip install -r requirements.txt
```

### Hapi 4: Inicializo Aplikacionin

```bash
# Gjenero çelësat e sigurisë
python manage.py genkey

# Inicializo bazën e të dhënave
python manage.py initdb

# Krijo user-in admin
python manage.py bootstrap --admin-password Admin123!
```

### Hapi 5: Nis Portalin në Modalitetin HTTPS Demo

```bash
# Nis serverin me certifikata TLS
python manage.py runssl --cert ../certs/server.crt --key ../certs/server.key
```

---

## Harta me Deliverables e Detyrës

| Deliverable | Përshkrim | Lokacioni |
|-------------|-----------|-----------|
| **Raporti Akademik** | Dokumentim i plotë i sigurisë | `report/report.md` |
| **Rrjeti i Rindizajnuar** | Diagrami i topologjisë | `report/report.md` (Mermaid #1) |
| **PKI/TLS Hardening** | Udhëzime për certifikata | `certs/README.md` |
| **IDS/Logging** | Konfigurime Suricata/Zeek | `report/report.md` §5 |
| **NIST IR Mapping** | Hartëzim me NIST SP 800-61 | `report/report.md` §6 |
| **Web App e Sigurt** | Portal Flask + PostgreSQL | `medilife-portal/` |
| **Evidence Templates** | Modele për të dhëna laboratoriku | `evidence/` |

---

## Kontrolli i Fundit para Dorëzimit

- [ ] Raporti renderon saktë në Markdown
- [ ] Diagramet Mermaid shfaqen saktë
- [ ] Të gjitha citimet kanë link të vlefshëm
- [ ] Komandat TLS në `certs/README.md` janë të qëndrueshme
- [ ] Modelet e evidence janë të etiketuara si placeholders
- [ ] Faqet e autentikuara funksionojnë vetëm mbi HTTPS
- [ ] Testet e automatizuara kalojnë me sukses

---

## Shënime për Vlerësimin

Ky projekt plotëson kërkesat e mëposhtme akademike:

1. **Gjuha:** Të gjitha dokumentet dhe UI janë në shqip
2. **Integriteti i Evidence:** Asnjë log/screenshot sintetik nuk prezantohet si real
3. **Citimet:** Vetëm referenca primare (NIST, OWASP, MITRE, RFC)
4. **TLS Baseline:** TLS 1.2/1.3 me HSTS të aktivizuar
5. **Security Controls:** Implementim i plotë i RBAC, CSRF, enkriptimit dhe auditimit
