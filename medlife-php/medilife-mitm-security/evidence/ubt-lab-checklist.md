# UBT Laboratori Checklist - Verifikimi i Sigurisë

**MediLife MitM Security Project - Evidence Templates për Laboratorin Real**

---

## Përshkrimi

Ky dokument përmban listën e kontrolleve dhe verifikimeve që duhet të kryhen në laboratorët realë të UBT-së duke përdorur mjete si Wireshark, Suricata, Zeek dhe OpenSSL.

**Shënim i rëndësishëm:** Ky është një template që do të plotësohet me të dhëna reale nga laboratori.

---

## Pjesa 1: Wireshark Analysis

### 1.1 Kapja e Trafikut TLS

**Komanda për të nisur kapjen:**
```bash
# Në Linux
sudo tcpdump -i eth0 -w medilife_tls_capture.pcap port 443

# Ose përdor Wireshark GUI
# Interface: eth0
# Capture filter: port 443
```

**Kontrollat për të kryer:**

- [ ] **TLS Handshake i plotë**
  - Client Hello i pranishëm
  - Server Hello me certifikatën
  - Client Key Exchange
  - Finished messages nga të dyja palët
  
  *Vendi për screenshot:*
  ```
  do të plotësohet me të dhëna reale nga laboratori
  ```

- [ ] **TLS Version i saktë**
  - Versioni duhet të jetë TLS 1.2 ose TLS 1.3
  - Filter në Wireshark: `tls.handshake.version`
  
  *Vendi për screenshot:*
  ```
  do të plotësohet me të dhëna reale nga laboratori
  ```

- [ ] **Cipher Suite i fortë**
  - Duhet të jetë një nga: TLS_AES_256_GCM_SHA384, TLS_CHACHA20_POLY1305_SHA256
  - Filter: `tls.handshake.ciphersuite`
  
  *Vendi për screenshot:*
  ```
  do të plotësohet me të dhëna reale nga laboratori
  ```

- [ ] **Trafiku i aplikacionit është i enkriptuar**
  - Application Data packets nuk shfaqin plaintext
  - Filter: `tls.app_data`
  
  *Vendi për screenshot:*
  ```
  do të plotësohet me të dhëna reale nga laboratori
  ```

### 1.2 Verifikimi i Certifikatës në Wireshark

**Hapat:**
1. Kliko në Server Hello packet
2. Zgjero "Certificate" section
3. Kliko "View Certificate Details"

**Kontrollat:**

- [ ] **Subject:** CN=medilife.internal
- [ ] **Issuer:** CN=MediLife Internal CA
- [ ] **SAN:** Përmban medilife.internal, 10.10.30.10
- [ ] **Validity:** Nuk ka skaduar

*Vendi për screenshot të certifikatës:*
```
do të plotësohet me të dhëna reale nga laboratori
```

---

## Pjesa 2: Zeek Logging

### 2.1 Verifikimi i ssl.log

**Lokacioni:** `/var/log/zeek/ssl.log`

**Komanda për të parë logjet:**
```bash
# Shfaq të gjitha entry-t për medilife.internal
cat /var/log/zeek/ssl.log | grep medilife.internal

# Ose përdor Zeek Reader
zeek-cut ts,uid,host,server_name,version,cipher < /var/log/zeek/ssl.log
```

**Kontrollat:**

- [ ] **Entry e pranishme për serverin**
  - `server_name` = medilife.internal
  - `version` = TLSv13 ose TLSv12
  - `cipher` = TLS_AES_256_GCM_SHA384

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

### 2.2 Verifikimi i x509.log

**Lokacioni:** `/var/log/zeek/x509.log`

**Komanda:**
```bash
# Gjej certifikatën e serverit
cat /var/log/zeek/x509.log | grep "medilife.internal"

# Përdor jq për parsing JSON (nëse është në format JSON)
cat /var/log/zeek/x509.log | jq 'select(.subject | contains("medilife.internal"))'
```

**Kontrollat:**

- [ ] **Subject:** CN=medilife.internal, O=MediLife Hospital
- [ ] **Issuer:** CN=MediLife Internal CA, O=MediLife Hospital
- [ ] **Serial Number:** I pranishëm
- [ ] **Not Valid Before:** Data e kaluar
- [ ] **Not Valid After:** Data në të ardhmen (365 ditë)

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

### 2.3 Verifikimi i dhcp.log

**Lokacioni:** `/var/log/zeek/dhcp.log`

**Komanda:**
```bash
# Shfaq të gjitha lease-t
cat /var/log/zeek/dhcp.log

# Gjej MAC address të klientit
cat /var/log/zeek/dhcp.log | grep -i "<MAC_ADDRESS>"
```

**Kontrollat:**

- [ ] **IP e caktuar për klientin** në rangun e VLAN
- [ ] **MAC Address** e regjistruar
- [ ] **Lease Time** i pranishëm

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

---

## Pjesa 3: Suricata IDS/IPS

### 3.1 Verifikimi i eve.json

**Lokacioni:** `/var/log/suricata/eve.json`

**Komanda:**
```bash
# Shfaq të gjitha alertet
cat /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'

# Gjej alerte specifike për ARP spoofing
cat /var/log/suricata/eve.json | jq 'select(.event_type=="alert" and .alert.signature_id==1000001)'

# Numëro alertet
cat /var/log/suricata/eve.json | jq 'select(.event_type=="alert")' | wc -l
```

**Kontrollat:**

- [ ] **Asnjë alert për trafik të ligjshëm** TLS
- [ ] **Alert për ARP spoofing** (nëse është testuar)
- [ ] **Alert për HTTP traffic** në rrjetin e brendshëm (nëse ka)

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

### 3.2 Verifikimi i fast.log

**Lokacioni:** `/var/log/suricata/fast.log`

**Komanda:**
```bash
# Shfaq alertet në format të lexueshëm
cat /var/log/suricata/fast.log | tail -20
```

**Formati i pritur:**
```
[**] [1:1000001:1] ARP Spoofing Detected [**]
[Classification: Potential Corporate Privacy Violation] [Priority: 1]
11/15-14:30:25.123456 10.10.10.50 -> 10.10.10.1
Protocol: ARP
```

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

---

## Pjesa 4: OpenSSL Verifikimi

### 4.1 Verifikimi i Zinxhirit të Certifikatës

**Komanda:**
```bash
cd /path/to/certs

# Verifiko që certifikata e serverit është e nënshkruar nga CA
openssl verify -CAfile ca.crt server.crt

# Output i pritur: server.crt: OK
```

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

### 4.2 Testimi i Lidhjes TLS

**Komanda:**
```bash
# Testo me TLS 1.3
openssl s_client -connect medilife.internal:443 -tls1_3 -CAfile ca.crt </dev/null 2>&1 | head -30

# Ekstrakto vetëm informacionet e rëndësishme
openssl s_client -connect medilife.internal:443 -tls1_3 -CAfile ca.crt </dev/null 2>&1 | \
    grep -E "(Protocol|Cipher|Verify return)"
```

**Output i pritur:**
```
Protocol  : TLSv1.3
Cipher    : TLS_AES_256_GCM_SHA384
Verify return code: 0 (ok)
```

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

### 4.3 Inspektimi i Certifikatës

**Komanda:**
```bash
# Shfaq të gjitha detajet e certifikatës
openssl x509 -in server.crt -noout -text

# Shfaq vetem subject dhe issuer
openssl x509 -in server.crt -noout -subject -issuer -dates

# Shfaq SAN
openssl x509 -in server.crt -noout -ext subjectAltName
```

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

---

## Pjesa 5: Testimi i Sulmit MitM (Në Mjedis të Kontrolluar)

### 5.1 ARP Spoofing Detection

**Parakushte:**
- Leje nga instruktori
- Mjedis i izoluar nga rrjeti i prodhimit
- Vetëm për qëllime edukative

**Komanda për të nisur ARP spoofing (në mjedis kontrolluar):**
```bash
# Në një terminal tjetër (sulmues i simuluar)
arpspoof -i eth0 -t 10.10.10.50 10.10.30.10

# Në Suricata duhet të shfaqet alerti
tail -f /var/log/suricata/fast.log
```

**Kontrollat:**

- [ ] **Suricata alert për ARP spoofing** u gjenerua
- [ ] **Zeek regjistroi** ndryshimin në ARP table
- [ ] **Wireshark pa** ARP reply të dyshimta

*Vendi për output:*
```
do të plotësohet me të dhëna reale nga laboratori
```

---

## Përmbledhje e Verifikimeve

| Sekcioni | Kontrolli | Statusi | Data |
|----------|-----------|---------|------|
| Wireshark | TLS Handshake | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Wireshark | TLS Version | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Wireshark | Cipher Suite | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Zeek | ssl.log entry | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Zeek | x509.log entry | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Zeek | dhcp.log entry | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Suricata | eve.json (no false alerts) | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Suricata | ARP spoofing detection | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| OpenSSL | Certificate chain verify | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| OpenSSL | TLS connection test | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Nënshkrimet

**Studenti:**

Emri: *do të plotësohet me të dhëna reale nga laboratori*

Nënshkrimi: *do të plotësohet me të dhëna reale nga laboratori*

Data: *do të plotësohet me të dhëna reale nga laboratori*

---

**Instruktori:**

Emri: *do të plotësohet me të dhëna reale nga laboratori*

Verifikimi: *do të plotësohet me të dhëna reale nga laboratori*

Data: *do të plotësohet me të dhëna reale nga laboratori*

---

*Fund i UBT Lab Checklist*
