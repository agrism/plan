# Plānotājs 🚀

Universāls multi-tenant uzdevumu krātuves (Backlog) un dienu plānošanas serviss komandām, uzņēmumiem un personīgai lietošanai.

Built with **Laravel 12**, **PHP 8.3**, **MySQL**, **HTMX**, **Alpine.js**, **Hetzner S3 Object Storage**, and **Tailwind CSS**.

---

## 🌟 Galvenās Iespējas

- **Multi-Tenancy (Darbavietas)**: Jebkurš var izveidot savu uzņēmuma vai projekta darbavietu, uzaicināt kolēģus un viegli pārslēgties.
- **Uzdevumu Krātuve (Backlog)**: Ātra darāmo darbu fiksēšana ar universālām kategorijām (💼 *Projekti*, ⚡ *Steidzami*, 🚀 *Attīstība*, 👥 *Sanāksmes*, 📋 *Ikdienas*, ✨ *Citi*).
- **Hetzner S3 & WebP Apstrāde**: Attēli tiek automātiski konvertēti uz vieglu un ātru **WebP** formātu un saglabāti **Hetzner S3 Object Storage** (`plan-t` lokāli, `plan` produkcijā).
- **Dienu Plānotājs**: Pārcel uzdevumus no krātuves uz konkrētām dienām ar 1 klikšķi.
- **Iesaiste & Reakcijas**: Balsojumi un reakcijas 👍 reāllaikā ar HTMX (bez lapas pārlādes).
- **Autora Fiksēšana**: Skaidri redzams, kurš komandas loceklis uzdevumu ir izveidojis.
- **Mobile-First & PWA**: Optimizēts lietošanai viedtālrunī ar apakšējo navigācijas joslu un izslīdošo ievades formu (Bottom Sheet).
- **Gaišā Tēma (Light Theme)**: Tīrs, moderns un viegli lasāms dizains.

---

## 🛠️ Tehnoloģiju Kopa

- **Backend**: Laravel 12 (PHP 8.3)
- **Database**: MySQL 8.0 / 8.4
- **Object Storage**: Hetzner S3 (`league/flysystem-aws-s3-v3`) + WebP Image Converter (`GD`)
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

## 🚢 Produkcijas Izvietošana (Production Deployment)

Produkcijas vidē datubāzes sagatavošanai un atjaunināšanai **netiek laisti nekādi seederi**:

```bash
# 1. Izpildīt tikai datubāzes migrācijas bez seederiem
php artisan migrate --force

# 2. Kešot konfigurāciju, maršrutus un skatus veiktspējai
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> [!NOTE]
> Produkcijas vidē (`APP_ENV=production`) sistēma automātiski slēpj visus demo lietotājus no pieslēgšanās loga.
