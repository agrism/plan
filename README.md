# Plānotājs 🚀

Universāls multi-tenant uzdevumu krātuves (Backlog) un dienu plānošanas serviss komandām, uzņēmumiem un personīgai lietošanai.

Built with **Laravel 12**, **PHP 8.3**, **MySQL**, **HTMX**, **Alpine.js**, and **Tailwind CSS**.

---

## 🌟 Galvenās Iespējas

- **Multi-Tenancy (Darbavietas)**: Jebkurš var izveidot savu uzņēmuma vai projekta darbavietu, uzaicināt kolēģus un viegli pārslēgties.
- **Uzdevumu Krātuve (Backlog)**: Ātra darāmo darbu fiksēšana ar universālām kategorijām (💼 *Projekti*, ⚡ *Steidzami*, 🚀 *Attīstība*, 👥 *Sanāksmes*, 📋 *Ikdienas*, ✨ *Citi*).
- **Dienu Plānotājs**: Pārcel uzdevumus no krātuves uz konkrētām dienām ar 1 klikšķi.
- **Iesaiste & Reakcijas**: Balsojumi un reakcijas 👍 reāllaikā ar HTMX (bez lapas pārlādes).
- **Autora Fiksēšana**: Skaidri redzams, kurš komandas loceklis uzdevumu ir izveidojis.
- **Mobile-First & PWA**: Optimizēts lietošanai viedtālrunī ar apakšējo navigācijas joslu un izslīdošo ievades formu (Bottom Sheet).
- **Gaišā Tēma (Light Theme)**: Tīrs, moderns un viegli lasāms dizains.

---

## 🛠️ Tehnoloģiju Kopa

- **Backend**: Laravel 12 (PHP 8.3)
- **Database**: MySQL 8.0 / 8.4
- **Frontend Interactivity**: HTMX 2.0 & Alpine.js
- **Styling**: Tailwind CSS
- **Dev Environment**: Laravel Sail (Docker)

---

## 🚀 Lokālā Palaišana (Laravel Sail)

1. **Klonēt repozitoriju**:
   ```bash
   git clone https://github.com/agrism/plan.git
   cd plan
   ```

2. **Kopēt vides failu**:
   ```bash
   cp .env.example .env
   ```

3. **Palaist Docker konteinerus ar Sail**:
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Ģenerēt atslēgu un izpildīt migrācijas ar demo datiem**:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

5. **Atvērt pārlūkā**:
   - Lietotne: **[http://localhost:8085](http://localhost:8085)**
   - E-pasti (Mailpit): **[http://localhost:8035](http://localhost:8035)**

---

## 👥 Demo Lietotāji Testēšanai

Pieslēdzoties [http://localhost:8085/login](http://localhost:8085/login), pieejamas 1-klikšķa ātrās pieslēgšanās pogas:
- 👨‍💼 **Jānis Bērziņš (Vadītājs)** — `janis@komanda.lv` (parole: `password`)
- 👩‍🎨 **Anna Ozola (Dizainere)** — `anna@komanda.lv`
- 👨‍💻 **Kārlis Kalniņš (Izstrādātājs)** — `karlis@komanda.lv`
- 👩‍💼 **Laura Liepiņa (Mārketings)** — `laura@komanda.lv`
