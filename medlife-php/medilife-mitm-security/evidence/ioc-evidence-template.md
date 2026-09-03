# IOC Evidence Template

**MediLife MitM Security Project - Tabela e të Dhënave të Incidentit**

---

## Informacionet e Incidentit

| Fusha | Vlera |
|-------|-------|
| **Data e Incidentit** | *do të plotësohet me të dhëna reale nga laboratori* |
| **Ora e Zbulimit** | *do të plotësohet me të dhëna reale nga laboratori* |
| **Ora e Përgjigjes** | *do të plotësohet me të dhëna reale nga laboratori* |
| **Klasifikimi** | Man-in-the-Middle (MitM) / ARP Spoofing |
| **Severiteti** | I Lartë / Mesëm / I Ulët (*do të plotësohet me të dhëna reale nga laboratori*) |
| **Sistemi i Prekur** | *do të plotësohet me të dhëna reale nga laboratori* |
| **Vendndodhja** | Laboratori UBT / Rrjeti i Spitalit (*do të plotësohet me të dhëna reale nga laboratori*) |

---

## Tabela e Indikatoreve të Komprometimit (IOC)

| # | Tipi i IOC | Vlera | Burimi | Data e Zbulimit | Severiteti | Statusi |
|---|------------|-------|--------|-----------------|------------|---------|
| 1 | MAC Address | *do të plotësohet me të dhëna reale nga laboratori* | Zeek dhcp.log | *do të plotësohet me të dhëna reale nga laboratori* | Mesëm | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | IP Address | *do të plotësohet me të dhëna reale nga laboratori* | Suricata eve.json | *do të plotësohet me të dhëna reale nga laboratori* | Lartë | *do të plotësohet me të dhëna reale nga laboratori* |
| 3 | ARP Entry | *do të plotësohet me të dhëna reale nga laboratori* | Wireshark | *do të plotësohet me të dhëna reale nga laboratori* | Lartë | *do të plotësohet me të dhëna reale nga laboratori* |
| 4 | Certificate Hash | *do të plotësohet me të dhëna reale nga laboratori* | Zeek x509.log | *do të plotësohet me të dhëna reale nga laboratori* | Mesëm | *do të plotësohet me të dhëna reale nga laboratori* |
| 5 | User Agent | *do të plotësohet me të dhëna reale nga laboratori* | Suricata http.log | *do të plotësohet me të dhëna reale nga laboratori* | Ulët | *do të plotësohet me të dhëna reale nga laboratori* |
| 6 | Filename | *do të plotësohet me të dhëna reale nga laboratori* | Suricata files.log | *do të plotësohet me të dhëna reale nga laboratori* | Mesëm | *do të plotësohet me të dhëna reale nga laboratori* |
| 7 | MD5 Hash | *do të plotësohet me të dhëna reale nga laboratori* | Suricata files.log | *do të plotësohet me të dhëna reale nga laboratori* | Lartë | *do të plotësohet me të dhëna reale nga laboratori* |
| 8 | SHA256 Hash | *do të plotësohet me të dhëna reale nga laboratori* | Analisisi Manual | *do të plotësohet me të dhëna reale nga laboratori* | Lartë | *do të plotësohet me të dhëna reale nga laboratori* |

**Shënim:** Kjo tabelë është një template dhe do të plotësohet me të dhëna reale të mbledhura gjatë sesioneve të laboratorit në UBT.

---

## Tabela e Logjeve të Sigurisë

### Suricata Alerts

| Alert ID | Signature ID | Mesazhi | Source IP | Dest IP | Protokolli | Ora | Aksioni |
|----------|--------------|---------|-----------|---------|------------|-----|---------|
| 1 | *do të plotësohet* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | *do të plotësohet* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

### Zeek Connection Logs

| UID | Origjina | Përgjigja | Protokolli | Kohëzgjatja | Bytes të Dërguara | Bytes të Marra |
|-----|----------|-----------|------------|-------------|-------------------|----------------|
| *do të plotësohet* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

### Audit Events (Aplikacioni)

| Event ID | Actor | Aksioni | Target | Rezultati | IP Burimi | Timestamp |
|----------|-------|---------|--------|-----------|-----------|-----------|
| 1 | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Timeline i Incidentit

```
HAPI KOHË              NGJARJA
----                  ---------
T0    *do të plotësohet*  Fillimi i aktivitetit të dyshimtë (nga logjet)
T1    *do të plotësohet*  ARP spoofing i detektuar nga Suricata
T2    *do të plotësohet*  Traffic interception i konfirmuar në Wireshark
T3    *do të plotësohet*  Alert i gjeneruar në SIEM
T4    *do të plotësohet*  Ekipi i sigurisë njoftohet
T5    *do të plotësohet*  Sistemet e izoluara nga rrjeti
T6    *do të plotësohet*  Evidence e mbledhur (imazhe, logje)
T7    *do të plotësohet*  Analiza forensike e filluar
T8    *do të plotësohet*  Raporti i incidentit i përgatitur
```

---

## Evidence e Mbledhur

| # | Tipi i Evidence | Përshkrimi | Lokacioni | Hash (SHA256) | Ruajtur Nga | Data |
|---|-----------------|------------|-----------|---------------|-------------|------|
| 1 | PCAP File | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | Disk Image | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 3 | Memory Dump | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 4 | Log Files | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 5 | Screenshots | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Analiza e Impactit

| Kategoria | Impacti i Vlerësuar | Justifikimi |
|-----------|---------------------|-------------|
| Konfidencialiteti | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Integriteti | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Disponueshmëria | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Numri i Pacientëve të Prekur | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| Tipi i të Dhënave të Ekspozuara | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Rekomandimet për Remediation

| # | Rekomandimi | Prioriteti | Statusi | Data e Implementimit |
|---|-------------|------------|---------|---------------------|
| 1 | Isolimi i sistemit të komprometuar | Lartë | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | Rishikimi i të gjithë certifikatave | Lartë | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 3 | Rotation i çelësave të sesioneve | Lartë | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 4 | Update i rregullave të Suricata | Mesëm | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |
| 5 | Training i stafit për security awareness | Ulët | *do të plotësohet me të dhëna reale nga laboratori* | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Chain of Custody

| Nr. | Emri | Roli | Data/Orë | Aksioni | Nënshkrimi |
|-----|------|------|----------|---------|------------|
| 1 | *do të plotësohet* | Instruktor | *do të plotësohet me të dhëna reale nga laboratori* | Evidence e marrë në dorëzim | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | *do të plotësohet* | Student | *do të plotësohet me të dhëna reale nga laboratori* | Evidence e analizuar | *do të plotësohet me të dhëna reale nga laboratori* |
| 3 | *do të plotësohet* | Student | *do të plotësohet me të dhëna reale nga laboratori* | Evidence e kthyer | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Shënime Shtesë

*Ky seksion është i lirë për shënime shtesë gjatë analizës:*

*do të plotësohet me të dhëna reale nga laboratori*

---

## Verifikimi

**Përgatitur nga:**

Emri: *do të plotësohet me të dhëna reale nga laboratori*

Data: *do të plotësohet me të dhëna reale nga laboratori*

---

**Verifikuar nga:**

Emri: *do të plotësohet me të dhëna reale nga laboratori*

Data: *do të plotësohet me të dhëna reale nga laboratori*

---

*Shënim: Ky dokument është një template dhe të gjitha fushat do të plotësohen me të dhëna reale të mbledhura gjatë sesioneve të laboratorit në UBT. Asnjë evidence sintetike nuk prezantohet si e vërtetë.*

*Fund i IOC Evidence Template*
