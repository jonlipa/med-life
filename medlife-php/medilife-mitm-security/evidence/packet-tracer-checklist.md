# Packet Tracer Screenshot Checklist

**MediLife MitM Security Project - Evidence Templates**

---

## Udhëzime të Përgjithshme

Ky dokument përmban listën e screenshot-eve që duhet të mblidhen nga Cisco Packet Tracer për të demonstruar konfigurimin e rrjetit dhe masat e sigurisë.

**Shënim i rëndësishëm:** Ky është një template që do të plotësohet me të dhëna reale nga laboratori.

---

## Screenshot #1: Topologjia e Plotë e Rrjetit

**Qëllimi:** Demonstron strukturën e përgjithshme të rrjetit me të gjitha VLAN-et dhe pajisjet.

**Komandat përpara screenshot:**
```
N/A (pamja grafike e Packet Tracer)
```

**Çfarë duhet të duket:**
- Të gjitha VLAN-et e etiketuara (10, 20, 30, 40, 99)
- Switch-at e konfiguruar me trunk ports
- Router-i me subinterface për inter-VLAN routing
- Serverët e vendosur në VLAN_30
- IDS/IPS i vendosur në VLAN_99

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #2: Konfigurimi i VLAN në Switch

**Qëllimi:** Verifikon që VLAN-et janë krijuar dhe portat janë caktuar saktë.

**Komandat për të ekzekutuar:**
```bash
Switch# show vlan brief
Switch# show interfaces trunk
```

**Çfarë duhet të duket në output:**
```
VLAN Name                             Status    Ports
---- -------------------------------- --------- -------------------------------
1    default                          active    Fa0/1, Fa0/2, Fa0/3, Fa0/4
10   VLAN_RECEPTION                   active    Fa0/5, Fa0/6, Fa0/7
20   VLAN_DOCTORS                     active    Fa0/8, Fa0/9, Fa0/10
30   VLAN_SERVERS                     active    Fa0/11, Fa0/12
40   VLAN_MEDICAL                     active    Fa0/13, Fa0/14
99   VLAN_MGMT                        active    Fa0/15
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #3: Konfigurimi i Inter-VLAN Routing

**Qëllimi:** Demonstron që router-i është konfiguruar për routing ndërmjet VLAN-eve.

**Komandat për të ekzekutuar:**
```bash
Router# show ip interface brief
Router# show running-config | section interface
```

**Çfarë duhet të duket:**
```
Interface              IP-Address      OK? Method Status                Protocol
GigabitEthernet0/0.10  10.10.10.1      YES manual up                    up
GigabitEthernet0/0.20  10.10.20.1      YES manual up                    up
GigabitEthernet0/0.30  10.10.30.1      YES manual up                    up
GigabitEthernet0/0.40  10.10.40.1      YES manual up                    up
GigabitEthernet0/0.99  10.10.99.1      YES manual up                    up
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #4: Rregullat e Firewall/ACL

**Qëllimi:** Verifikon që rregullat e aksesit janë konfiguruar për të kufizuar trafikun.

**Komandat për të ekzekutuar:**
```bash
Router# show access-lists
Router# show ip interface | include ACL
```

**Çfarë duhet të duket:**
```
Standard IP access list 10
    10 permit 10.10.10.0 0.0.0.255
    20 permit 10.10.20.0 0.0.0.255
    30 deny any

Extended IP access list 110
    10 permit tcp 10.10.10.0 0.0.0.255 10.10.30.0 0.0.0.255 eq 443
    20 permit tcp 10.10.20.0 0.0.0.255 10.10.30.0 0.0.0.255 eq 443
    30 permit tcp 10.10.30.0 0.0.0.255 10.10.30.0 0.0.0.255 eq 5432
    40 deny ip any any
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #5: Konfigurimi i SPAN Port për IDS

**Qëllimi:** Demonstron që traffic është duke u mirroruar në portin e IDS.

**Komandat për të ekzekutuar:**
```bash
Switch# show monitor session 1
```

**Çfarë duhet të duket:**
```
Session 1
---------
Type              : Local Session
Source Ports      :
    Rx Only       : Fa0/11
    Tx Only       : Fa0/12
    Both          : Fa0/5-10
Destination Ports : Fa0/15
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #6: Simulimi i Trafikut HTTP vs HTTPS

**Qëllimi:** Demonstron ndryshimin midis trafikut të pakriptuar dhe atij të enkriptuar.

**Komandat për të ekzekutuar:**
```
N/A (përdor Simulation mode në Packet Tracer)
```

**Çfarë duhet të duket:**
- HTTP packet i hapet (plaintext i dukshëm)
- HTTPS packet mbetet i enkriptuar

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #7: Testing i Konektivitetit

**Qëllimi:** Verifikon që stacionet e punës mund të komunikojnë me serverët.

**Komandat për të ekzekutuar:**
```bash
PC> ping 10.10.30.10
PC> tracert 10.10.30.10
```

**Çfarë duhet të duket:**
```
Pinging 10.10.30.10 with 32 bytes of data:
Reply from 10.10.30.10: bytes=32 time=1ms TTL=127
Reply from 10.10.30.10: bytes=32 time=1ms TTL=127
Reply from 10.10.30.10: bytes=32 time=1ms TTL=127
Reply from 10.10.30.10: bytes=32 time=1ms TTL=127

Ping statistics for 10.10.30.10:
    Packets: Sent = 4, Received = 4, Lost = 0 (0% loss)
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Screenshot #8: Konfigurimi i Port Security

**Qëllimi:** Demonstron masat e sigurisë në nivelin e portit të switch-it.

**Komandat për të ekzekutuar:**
```bash
Switch# show port-security interface Fa0/5
Switch# show port-security address
```

**Çfarë duhet të duket:**
```
Port Security              : Enabled
Port Status                : Secure-up
Violation Mode             : Shutdown
Aging Time                 : 0 mins
Aging Type                 : Absolute
SecureStatic Address Aging : Disabled
Maximum MAC Addresses      : 1
Total MAC Addresses        : 1
Configured MAC Addresses   : 0
Sticky MAC Addresses       : 0
Last Source Address        : 0000.1111.2222
Security Violation Count   : 0
```

**Vendi për screenshot:**

---
*do të plotësohet me të dhëna reale nga laboratori*
---

---

## Përmbledhje e Screenshot-eve të Kërkuara

| # | Përshkrimi | VLAN | Pajisja | Statusi |
|---|------------|------|---------|---------|
| 1 | Topologjia e plotë | Të gjitha | E gjithë | *do të plotësohet me të dhëna reale nga laboratori* |
| 2 | VLAN brief | Të gjitha | Switch | *do të plotësohet me të dhëna reale nga laboratori* |
| 3 | Inter-VLAN routing | Të gjitha | Router | *do të plotësohet me të dhëna reale nga laboratori* |
| 4 | ACL/Firewall rules | Të gjitha | Router | *do të plotësohet me të dhëna reale nga laboratori* |
| 5 | SPAN session | 99 | Switch | *do të plotësohet me të dhëna reale nga laboratori* |
| 6 | HTTP vs HTTPS | - | Simulation | *do të plotësohet me të dhëna reale nga laboratori* |
| 7 | Ping/Tracert | 10→30 | PC | *do të plotësohet me të dhëna reale nga laboratori* |
| 8 | Port security | 10,20 | Switch | *do të plotësohet me të dhëna reale nga laboratori* |

---

## Shënime Shtesë

**Data e sesionit të laboratorikut:** *do të plotësohet me të dhëna reale nga laboratori*

**Emri i studentit:** *do të plotësohet me të dhëna reale nga laboratori*

**Grupi:** *do të plotësohet me të dhëna reale nga laboratori*

**Verifikuar nga instruktori:** *do të plotësohet me të dhëna reale nga laboratori*

---

*Fund i Packet Tracer Screenshot Checklist*
