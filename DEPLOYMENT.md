# Qo'rboshilar.uz — Deployment qo'llanmasi

Bu hujjat production serverga joylashtirish (deploy), backup va monitoring
bo'yicha amaliy ko'rsatmalarni beradi. Arxitektura qarorlari uchun
`ARCHITECTURE.md` ga qarang.

---

## 1. Server talablari

| Komponent | Talab |
|---|---|
| PHP | 8.3+ (composer.json: `^8.2`, lekin loyiha 8.3+ bilan sinovdan o'tgan) |
| PHP kengaytmalari | `pdo`, `pdo_sqlite`/`pdo_pgsql`/`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` yoki `imagick` (rasm yuklash uchun) |
| Composer | 2.x |
| Node/npm | Faqat **build vaqtida** kerak (`npm run build`) — production serverda doimiy ishlab turishi shart emas, faqat compiled `public/build/` natijasi kerak |
| Baza (production) | PostgreSQL yoki MySQL 8+ (local dev'da SQLite ishlatiladi — ARCHITECTURE.md §1) |
| Veb-server | Nginx yoki Apache, `public/` katalogini document root sifatida |

---

## 2. Deployment tartibi

```bash
git pull

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan storage:link   # faqat birinchi deploy'da kerak (idempotent, xato bermaydi)

php artisan optimize
```

**Muhim izohlar:**

- `composer install --no-dev` — test/dev-only paketlar (masalan `phpunit`,
  `laravel/pail`) production'ga o'rnatilmaydi.
- `npm ci` (`npm install` emas) — `package-lock.json`dagi aniq versiyalarni
  qat'iy o'rnatadi, deploy'lar orasida versiya siljishini oldini oladi.
- `php artisan migrate --force` — production'da `APP_ENV != local` bo'lgani
  uchun Laravel interaktiv tasdiqlash so'raydi; `--force` buni chetlab o'tadi.
  **Migratsiyalarni ishga tushirishdan oldin baza backup qiling** (§6).
- `php artisan optimize` — config/route/view cache'larni yaratadi
  (`config:cache`, `route:cache`, `view:cache`, `event:cache`). Bu **faqat
  production'da** ishlatiladi — local development'da **ishlatilmasin**
  (`.env` o'zgarishlari cache tufayli ko'rinmay qolishi mumkin). Agar
  keyinchalik `.env` yoki route/config o'zgarsa, albatta qayta ishga tushiring:
  ```bash
  php artisan optimize:clear
  php artisan optimize
  ```
- Deploy oqimi test qilindi: `optimize` → `optimize:clear` → to'liq test suite
  (140 test) muvaffaqiyatli o'tdi, cache holatidan qat'i nazar ilova bir xil
  ishladi.

---

## 3. Fayl ruxsatlari (permissions)

Web-server foydalanuvchisi (`www-data`, `nginx` va h.k.) quyidagi papkalarga
yozish huquqiga ega bo'lishi kerak:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

(Foydalanuvchi/guruh nomini o'z serveringiz konfiguratsiyasiga moslang.)

---

## 4. Environment (.env) sozlamalari

`.env.example` production-safe default qiymatlar bilan yangilangan
(`APP_ENV=production`, `APP_DEBUG=false`). Production `.env` faylida:

### Majburiy

```env
APP_NAME="Qo'rboshilar.uz"
APP_ENV=production
APP_KEY=                    # php artisan key:generate orqali generatsiya qiling
APP_DEBUG=false              # HECH QACHON true bo'lmasin — stack trace/config sizib chiqadi
APP_URL=https://qorboshilar.uz   # haqiqiy domen

APP_LOCALE=uz
APP_FALLBACK_LOCALE=en
```

> **Muhim topilma (audit paytida tuzatildi):** `APP_LOCALE` avval `en` edi,
> holbuki butun sayt o'zbek tilida — bu standart Laravel validatsiya
> xabarlarini (masalan "The title field is required.") inglizcha qilib
> qo'yayotgan edi. `lang/uz/validation.php` qo'shildi va `APP_LOCALE=uz`ga
> o'zgartirildi (ARCHITECTURE.md §31.3).

### Baza

```env
DB_CONNECTION=pgsql   # yoki mysql
DB_HOST=127.0.0.1
DB_PORT=5432          # yoki 3306 (MySQL)
DB_DATABASE=qorboshilar
DB_USERNAME=...
DB_PASSWORD=...
```

### Sessiya/Cache/Queue

```env
SESSION_DRIVER=database      # mavjud arxitektura (local bilan bir xil)
SESSION_SECURE_COOKIE=true   # HTTPS orqali xizmat qilinsa — MAJBURIY true
CACHE_STORE=database         # kichik/o'rta trafik uchun yetarli, Redis shart emas
QUEUE_CONNECTION=sync        # hech qanday Job/Mailable ishlatilmaydi (audit tasdiqladi) — worker/process manager KERAK EMAS
```

> Loyihada hozircha **hech qanday queue job yoki mail yuborish** yo'q (audit
> paytida tekshirildi — `grep -r "ShouldQueue\|Mail::"` bo'sh natija berdi).
> Shuning uchun alohida queue worker infratuzilmasi (Supervisor, Horizon va
> h.k.) **hozircha kerak emas** — `QUEUE_CONNECTION=sync` yetarli. Agar
> kelajakda email yuborish (masalan parol tiklash) qo'shilsa, shu bo'lim
> yangilanadi.

### Mail (hozircha ishlatilmaydi, lekin kelajak uchun tayyor)

```env
MAIL_MAILER=log   # yoki real SMTP, email funksiyasi qo'shilganda
```

### Sekretlar haqida

- `.env` fayli **hech qachon** git repositoriyga qo'shilmasin (`.gitignore`da
  allaqachon bor — tekshirildi).
- `.env.example`da hech qanday real parol/kalit/token yo'q (tekshirildi —
  barcha sezgir maydonlar bo'sh yoki placeholder).

---

## 5. Web-server konfiguratsiyasi

Document root — **majburiy** `public/` katalogi, loyiha ildizi emas:

**Nginx misoli:**

```nginx
root /var/www/qorboshilar/public;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location ~ /\.(?!well-known).* {
    deny all;
}
```

`storage/`, `bootstrap/cache/`, `.env`, `composer.json` kabi fayllar
`public/` tashqarisida turgani uchun web-server orqali to'g'ridan-to'g'ri
so'ralinmaydi (Laravel'ning standart papka strukturasi allaqachon xavfsiz).

---

## 6. Health check

`GET /up` — Laravel'ning o'rnatilgan health-check marshruti
(`bootstrap/app.php`da `health: '/up'`). Ilovaning asosiy boot jarayoni
ishlab turganini tekshiradi, **sezgir ma'lumot qaytarmaydi** (tekshirildi).
Load balancer/uptime monitoring shu endpoint'ga murojaat qilishi mumkin.

```bash
curl -f https://qorboshilar.uz/up || echo "DOWN"
```

---

## 7. Backup

### Baza

Kunlik avtomatik dump tavsiya etiladi (cron orqali, server darajasida):

```bash
# PostgreSQL misoli
pg_dump -Fc qorboshilar > /backups/qorboshilar-$(date +%Y%m%d).dump

# MySQL misoli
mysqldump qorboshilar > /backups/qorboshilar-$(date +%Y%m%d).sql
```

### Fayllar (storage)

`storage/app/public/` — foydalanuvchi yuklagan barcha rasm/fayllar (portret,
muqova, PDF, tarixiy rasm va h.k.). Bazadan alohida backup qilinishi shart:

```bash
tar -czf /backups/storage-$(date +%Y%m%d).tar.gz storage/app/public
```

### Retention (saqlash muddati)

Tavsiya: kunlik backup 14 kun, haftalik backup 3 oy saqlanadi (server
diskiga qarab moslashtiring).

### Restore test

Backup'ning ishlashini davriy ravishda (masalan oyiga bir marta) alohida
staging muhitda tiklab ko'rish tavsiya etiladi — backup fayli mavjudligi
uning **to'g'ri ishlashini** kafolatlamaydi.

Bu hujjat avtomatik external backup xizmatini ulamaydi — faqat aniq
jarayonni belgilaydi (spec talabiga mos).

---

## 8. Logging / Monitoring

- Laravel standart `storage/logs/laravel.log` (LOG_CHANNEL=stack) orqali
  xatoliklarni yozadi.
- **Sezgir ma'lumot logga tushmasligi tekshirildi:** parol maydonlari
  `User` modelida `$hidden` orqali yashirilgan, FormRequest validatsiya
  xatolari faqat maydon nomlarini o'z ichiga oladi (qiymatlarni emas).
- Production'da `LOG_LEVEL=error` yoki `warning` tavsiya etiladi (`debug`
  emas — ortiqcha shovqin va potentsial sezgir ma'lumot uchun).
- Health endpoint (`/up`) uptime monitoring uchun ishlatilishi mumkin.
- Bu bosqichda tashqi monitoring xizmati (Sentry, Bugsnag va h.k.) **ataylab
  ulanmadi** — kerak bo'lsa, alohida qaror sifatida keyinroq qo'shiladi.

---

## 9. Analytics (kelajak uchun joy qoldirilgan)

Hozircha hech qanday analytics provider ulanmagan va privacy-sensitive
tracking yaratilmagan. Agar kelajakda analytics kerak bo'lsa:

```env
ANALYTICS_PROVIDER=   # masalan "plausible", "matomo" — hali tanlanmagan
```

kabi konfiguratsiya o'zgaruvchisi qo'shilishi mumkin — bu ATAYLAB hozir
amalga oshirilmadi (spec §33 talabiga mos).

---

## 10. Deploy'dan keyingi tekshiruv (smoke test)

```bash
curl -f https://qorboshilar.uz/up
curl -sI https://qorboshilar.uz/ | grep -i "x-frame\|x-content-type"
```

Va qo'lda:

- `/` — bosh sahifa ochiladimi
- `/kirish`, `/royxatdan-otish` — auth formalari
- `/admin` — login qilib, admin panel ochiladimi
- `/sitemap.xml`, `/robots.txt` — to'g'ri javob qaytaradimi
- Statik asset'lar (`/build/assets/*.css`, `*.js`) 200 qaytaradimi
