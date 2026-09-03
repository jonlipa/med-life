# Udhëzime për PKI dhe Certifikata TLS

**MediLife MitM Security Project - Dokumentim për Certifikatat**

---

## Përmbledhje

Ky dokument përshkruan hap pas hapi procesin e krijimit të një Autoriteti Certifikimi (CA) të brendshëm, gjenerimin e certifikatave për serverin, dhe verifikimin e lidhjeve TLS. Udhëzimet janë të ndara në dy seksione:

1. **Packet Tracer** - Kufizimet dhe mundësitë e simulimit
2. **Laboratori Real UBT** - Validimi me mjete reale (OpenSSL, Wireshark, Zeek)

---

## Arkitektura e PKI

```
MediLife Internal CA (Root)
│
├── CN: MediLife Internal CA
├── O: MediLife Hospital
├── OU: IT Security
├── C: AL
└── Key: RSA 4096-bit, SHA-256
    │
    └── Server Certificate
        ├── CN: medilife.internal
        ├── SAN: medilife.internal, www.medilife.internal, 10.10.30.10
        ├── O: MediLife Hospital
        ├── Validity: 365 ditë
        └── Key: RSA 2048-bit, SHA-256
```

---

## Hapi 1: Krijo Autoritetin e Certifikimit (CA)

### 1.1 Gjenero Çelësin Privat të CA

```bash
cd /path/to/medilife-mitm-security/certs

# Gjenero çelësin privat RSA 4096-bit për CA
openssl genrsa -aes256 -out ca.key 4096

# Vendos të drejtat e duhura (vetëm root mund ta lexojë)
chmod 400 ca.key
```

### 1.2 Krijo Certifikatën Root CA

```bash
# Krijo certifikatën self-signed për CA
openssl req -new -x509 -days 1825 -key ca.key -sha256 -out ca.crt \
    -subj "/C=AL/O=MediLife Hospital/OU=IT Security/CN=MediLife Internal CA"
```

**Parametrat e Certifikatës CA:**

| Fusha | Vlera |
|-------|-------|
| Country (C) | AL |
| Organization (O) | MediLife Hospital |
| Organizational Unit (OU) | IT Security |
| Common Name (CN) | MediLife Internal CA |
| Validity | 5 vite (1825 ditë) |
| Key Size | RSA 4096-bit |
| Hash Algorithm | SHA-256 |

### 1.3 Verifiko Certifikatën CA

```bash
# Shfaq informacionet e certifikatës CA
openssl x509 -in ca.crt -noout -text

# Verifiko vetë-certifikatën
openssl x509 -in ca.crt -noout -modulus
openssl rsa -in ca.key -noout -modulus
# Të dyja duhet të japin të njëjtin output
```

---

## Hapi 2: Krijo Certifikatën e Serverit

### 2.1 Gjenero Çelësin Privat të Serverit

```bash
# Gjenero çelësin privat për serverin (pa passphrase për automatizim)
openssl genrsa -out server.key 2048

# Vendos të drejtat e duhura
chmod 400 server.key
```

### 2.2 Krijo Configuration File për SAN

```bash
# Krijo file konfigurimi për Subject Alternative Names
cat > server_ext.cnf << 'EOF'
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = req_ext
x509_extensions = v3_ext

[dn]
C = AL
O = MediLife Hospital
OU = IT Department
CN = medilife.internal

[req_ext]
subjectAltName = @alt_names

[v3_ext]
authorityKeyIdentifier=keyid,issuer:always
basicConstraints=CA:FALSE
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[alt_names]
DNS.1 = medilife.internal
DNS.2 = www.medilife.internal
DNS.3 = portal.medilife.internal
IP.1 = 10.10.30.10
IP.2 = 127.0.0.1
EOF
```

### 2.3 Gjenero CSR (Certificate Signing Request)

```bash
# Gjenero CSR me SAN
openssl req -new -key server.key -out server.csr -config server_ext.cnf

# Verifiko CSR
openssl req -in server.csr -noout -text
```

### 2.4 Nënshkruaje Certifikatën me CA

```bash
# Krijo file konfigurimi për nënshkrimin e certifikatës
cat > v3_ca_ext.cnf << 'EOF'
authorityKeyIdentifier=keyid,issuer:always
basicConstraints=CA:FALSE
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[alt_names]
DNS.1 = medilife.internal
DNS.2 = www.medilife.internal
DNS.3 = portal.medilife.internal
IP.1 = 10.10.30.10
IP.2 = 127.0.0.1
EOF

# Nënshkruaje certifikatën me CA (365 ditë validitet)
openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key \
    -CAcreateserial -out server.crt -days 365 -sha256 \
    -extfile v3_ca_ext.cnf -extensions v3_ext
```

### 2.5 Verifiko Certifikatën e Serverit

```bash
# Shfaq informacionet e certifikatës
openssl x509 -in server.crt -noout -text

# Verifiko zinxhirin e certifikatës
openssl verify -CAfile ca.crt server.crt

# Kontrollo që SAN janë të pranishme
openssl x509 -in server.crt -noout -ext subjectAltName
```

**Output i pritur për SAN:**
```
X509v3 Subject Alternative Name: 
    DNS:medilife.internal, DNS:www.medilife.internal, DNS:portal.medilife.internal, 
    IP Address:10.10.30.10, IP Address:127.0.0.1
```

---

## Hapi 3: Krijo Certifikatën e Klientit (Opsionale)

Për autentikim mutual TLS (mTLS):

```bash
# Gjenero çelësin e klientit
openssl genrsa -out client.key 2048

# Krijo CSR për klientin
openssl req -new -key client.key -out client.csr \
    -subj "/C=AL/O=MediLife Hospital/OU=IT Department/CN=admin@medilife.internal"

# Nënshkruaje certifikatën e klientit
openssl x509 -req -in client.csr -CA ca.crt -CAkey ca.key \
    -CAcreateserial -out client.crt -days 365 -sha256 \
    -extfile <(echo -e "basicConstraints=CA:FALSE\nkeyUsage=digitalSignature\nextendedKeyUsage=clientAuth")
```

---

## Hapi 4: Verifikimi me openssl s_client

### 4.1 Testo Lidhjen TLS

```bash
# Testo lidhjen me TLS 1.3
openssl s_client -connect medilife.internal:443 -tls1_3 -CAfile ca.crt

# Testo lidhjen me TLS 1.2
openssl s_client -connect medilife.internal:443 -tls1_2 -CAfile ca.crt

# Testo me certifikatë klienti (mTLS)
openssl s_client -connect medilife.internal:443 -tls1_3 \
    -CAfile ca.crt -cert client.crt -key client.key
```

### 4.2 Analizo Output e openssl s_client

**Informacionet e rëndësishme për t'u verifikuar:**

```
Verify return code: 0 (ok)           <-- Certifikata është e vlefshme
Protocol  : TLSv1.3                  <-- TLS 1.3 është aktivizuar
Cipher    : TLS_AES_256_GCM_SHA384   <-- Cipher suite i fortë
```

### 4.3 Ekstrakto dhe Inspekto Certifikatën

```bash
# Ekstrakto certifikatën nga lidhja
openssl s_client -connect medilife.internal:443 2>/dev/null | \
    openssl x509 -noout -text

# Ekstrakto vetëm subject dhe issuer
openssl s_client -connect medilife.internal:443 2>/dev/null | \
    openssl x509 -noout -subject -issuer
```

---

## Hapi 5: Konfigurimi për Aplikacionin Flask

### 5.1 Struktura e File-ave

```
certs/
├── ca.crt              # Certifikata Root CA (publike)
├── ca.key              # Çelësi privat i CA (ruaje me siguri!)
├── ca.srl              # Serial number për nënshkrimin tjetër
├── server.crt          # Certifikata e serverit
├── server.key          # Çelësi privat i serverit
├── server.csr          # Certificate Signing Request (nuk duhet më pas nënshkrimit)
├── client.crt          # Certifikata e klientit (opsionale)
├── client.key          # Çelësi privat i klientit (opsionale)
├── server_ext.cnf      # Konfigurimi për SAN
└── v3_ca_ext.cnf       # Extension për nënshkrim
```

### 5.2 Komanda për Nisjen e Serverit HTTPS

```bash
# Nis Flask app me HTTPS
python manage.py runssl \
    --cert ../certs/server.crt \
    --key ../certs/server.key \
    --port 443
```

### 5.3 Shto CA në Trust Store (për testing)

**Windows:**
```powershell
# Importo CA në trust store të Windows
Import-Certificate -FilePath "ca.crt" -CertStoreLocation Cert:\LocalMachine\Root
```

**Linux (Ubuntu/Debian):**
```bash
# Kopjo CA në folderin e trust
sudo cp ca.crt /usr/local/share/ca-certificates/
sudo update-ca-certificates
```

**macOS:**
```bash
# Shto CA në keychain
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain ca.crt
```

---

## Dallimet: Packet Tracer vs Laboratori Real UBT

### Packet Tracer - Kufizimet

| Karakteristika | Packet Tracer | Shënim |
|----------------|---------------|--------|
| TLS Simulation | E kufizuar | Vetëm simulim vizual, jo kriptografi reale |
| Certifikata | Të thjeshtuara | Nuk suporton SAN të plota |
| Wireshark Integration | Jo | Export i kufizuar i pcap |
| OpenSSL Commands | Jo | Nuk ka terminal të plotë |

**Çfarë mund të dokumentosh në Packet Tracer:**
- Topologjia e rrjetit
- Konfigurimi i VLAN
- Rregullat e ACL/Firewall
- Simulimi i trafikut HTTP vs HTTPS (vizual)

### Laboratori Real UBT - Validimi i Plotë

| Karakteristika | Laboratori UBT | Mjeti |
|----------------|----------------|-------|
| TLS Handshake | Real | Wireshark |
| Certifikata X.509 | Real | OpenSSL |
| Logging SSL/TLS | Real | Zeek ssl.log |
| Alertet IDS | Real | Suricata eve.json |
| ARP Spoofing Detection | Real | Suricata + Zeek |

**Komandat për validim në laboratorin UBT:**

```bash
# 1. Kap traffic në Wireshark
sudo tcpdump -i eth0 -w medilife_capture.pcap port 443

# 2. Analizo TLS handshake në Wireshark
# Filter: tls.handshake

# 3. Ekstrakto logjet e Zeek
cat /var/log/zeek/ssl.log | grep medilife.internal

# 4. Verifiko certifikatën në x509.log
cat /var/log/zeek/x509.log | jq '. | select(.subject=="CN=medilife.internal")'

# 5. Kontrollo alertet e Suricata
cat /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'
```

---

## Checklist për Laboratorin UBT

### Wireshark Verification

- [ ] TLS Client Hello është i pranishëm
- [ ] TLS Server Hello me certifikatën e serverit
- [ ] Certificate Verify dhe Finished messages
- [ ] Application Data është e enkriptuar (nuk shihet plaintext)
- [ ] TLS version është 1.2 ose 1.3

### Zeek Logs Verification

- [ ] `ssl.log` përmban entry për lidhjen
- [ ] `x509.log` tregon subject=CN=medilife.internal
- [ ] `x509.log` tregon issuer=CN=MediLife Internal CA
- [ ] `dhcp.log` tregon IP assignment për klientin

### Suricata Alerts Verification

- [ ] `eve.json` nuk përmban alerte për traffic të ligjshëm
- [ ] Në ka ARP spoofing, alerti gjenerohet

### OpenSSL Verification

- [ ] `openssl verify -CAfile ca.crt server.crt` kthen "OK"
- [ ] `openssl s_client` tregon TLS 1.3
- [ ] Cipher suite është TLS_AES_256_GCM_SHA384

---

## Troubleshooting

### Problem: "unable to get local issuer certificate"

**Zgjidhja:** Sigurohu që CA certifikata është në trust store ose përdor `-CAfile ca.crt`.

### Problem: "certificate has expired"

**Zgjidhja:** Rinovo certifikatën me komandën e nënshkrimit me `-days` të përditësuar.

### Problem: "hostname mismatch"

**Zgjidhja:** Sigurohu që SAN përmban hostname-in që po përdor për të aksesuar serverin.

### Problem: "self signed certificate" warning në browser

**Zgjidhja:** Kjo është e pritur për CA të brendshme. Shto CA në trust store të sistemit.

---

## Referencat

- OpenSSL Documentation: https://www.openssl.org/docs/
- RFC 5280: Internet X.509 Public Key Infrastructure
- OWASP TLS Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Protection_Cheat_Sheet.html
- NIST SP 800-52 Rev. 2: Guidelines for the Selection of Additional Security Controls

---

*Shënim: Të gjitha komandat duhet të ekzekutohen në një mjedis laboratoriku të kontrolluar. Asnjë certifikatë e gjeneruar nga ky proces nuk duhet të përdoret në produksion pa një auditim të plotë të sigurisë.*
