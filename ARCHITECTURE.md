# QO'RBOSHILAR.UZ — Arxitektura hujjati (v2, Laravel monolit)

> **Diqqat:** Bu hujjat oldingi Next.js/Prisma arxitekturasini almashtiradi. Loyiha endi
> **100% Laravel monolit** (Blade + Alpine.js + Tailwind, alohida frontend/backend yo'q)
> sifatida quriladi. Qarorlar va sabablar shu yerda qayd etiladi — development shu hujjatga
> tayanadi.

---

## 1. Laravel project architecture

**Qat'iy oqim (har bir so'rov shu yo'ldan o'tadi):**

```
Route (routes/web.php)
   → Controller (app/Http/Controllers/**)
       → Form Request (validation)
       → Service / Action (app/Services, app/Actions)
           → Eloquent Model (app/Models)
               → Database
       ← Service natijani qaytaradi
   ← Controller View'ga ma'lumot uzatadi
Blade View (resources/views/**)
```

**Qat'iy qoida:** Blade ichida `DB::table(...)` yoki `Model::query()` ishlatilmaydi.
Barcha ma'lumot Controller/Service orqali view'ga uzatiladi (View faqat taqdimot qatlami).

**Texnologik versiyalar (o'rnatilgan):**

| Komponent | Versiya |
|---|---|
| PHP | 8.2.12 (local, XAMPP) — quyida izoh |
| Laravel | 12.69.1 |
| Tailwind CSS | v4 (Vite plugin orqali) |
| Vite | v7 |
| Alpine.js | eng so'nggi (npm) |
| DB (local dev) | SQLite (`database/database.sqlite`) |
| DB (production maqsad) | PostgreSQL |

> **Muhim eslatma — PHP versiyasi:** Talabnoma PHP 8.3+ so'ragan, lekin joriy mashinada
> XAMPP orqali PHP **8.2.12** o'rnatilgan (Composer ham shu PHP'ni ishlatadi). Laravel 12
> minimal talabi PHP 8.2 bo'lgani uchun bu bloklovchi emas — loyiha ishlaydi. Agar 8.3/8.4'ga
> o'tish kerak bo'lsa, buni alohida environment sozlash vazifasi sifatida keyin bajaramiz
> (composer.json'dagi `"php": "^8.2"` talabini keyin `^8.3`ga qattiqlashtirish mumkin).

> **DB eslatma:** Local mashinada PostgreSQL server ishga tushirilgan holatda topildi
> (port 5432), lekin standart `postgres` foydalanuvchisi paroli noma'lum bo'lgani uchun
> ulanib bo'lmadi. Shuning uchun **local development uchun hozircha SQLite** ishlatilmoqda
> (Laravel buni composer install paytida avtomatik sozladi, migratsiyalar allaqachon
> muvaffaqiyatli ishga tushdi). Eloquent orqali yozilgan kod SQLite/PostgreSQL o'rtasida
> deyarli portativ (provider-specific SQL, masalan raw JSONB operatorlari ishlatilmaydi).
> Production'da (yoki istasangiz hozirdanoq local'da) PostgreSQL'ga o'tish uchun faqat
> `.env` dagi `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
> `DB_PASSWORD`ni to'ldirish va `php artisan migrate:fresh` yetarli. Agar hozir Postgres
> credential'laringizni bersangiz, darhol shunga o'tkazib qo'yaman.

---

## 2. Folder structure

Talabnomadagi #28-bo'limga to'liq mos (allaqachon yaratilgan):

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # admin CRUD controllerlari
│   │   ├── Auth/        # login/register/profile
│   │   └── Public/      # public sahifalar (Home, Qorboshi, Uzgolon, ...)
│   ├── Requests/        # Form Request validatsiya klasslari
│   └── Middleware/      # role-guard va h.k.
├── Models/
├── Policies/            # QorboshiPolicy, UzgolonPolicy, BlogPolicy, CommentPolicy, ...
├── Services/            # QorboshiService, UzgolonService, SearchService, MapService, ...
├── Actions/             # bitta-vazifali action classlar (masalan PublishBlogAction)
└── Support/             # helper/util classlar

database/
├── migrations/
├── factories/
└── seeders/

resources/
├── views/
│   ├── layouts/         # app.blade.php, admin.blade.php
│   ├── components/      # x-navbar, x-card, x-qorboshi-card, ...
│   ├── pages/           # home, about, contact kabi statik sahifalar
│   ├── qorboshilar/     # index.blade.php, show.blade.php
│   ├── qozgolonlar/
│   ├── adabiyotlar/
│   ├── videolar/
│   ├── bloglar/
│   ├── auth/
│   ├── profile/
│   └── admin/
├── css/
└── js/

routes/
├── web.php
└── console.php

storage/
├── app/                 # yuklangan fayllar (rasm, PDF)
├── framework/
└── logs/
```

Barchasi allaqachon amaliyotda yaratildi (`app/Http/Controllers/{Admin,Auth,Public}`,
`app/{Policies,Services,Actions,Support}`, `resources/views/{layouts,components,pages,
qorboshilar,qozgolonlar,adabiyotlar,videolar,bloglar,auth,profile,admin}`).

---

## 3. Database ER model (konseptual)

```
User ──< Blog                 (1:N, author)
User ──< Comment               (1:N, author)

Qorboshi >──< Uzgolon           (M:N — pivot: qorboshi_uzgolon)
Qorboshi >──< Literature        (M:N — pivot: qorboshi_literature)
Uzgolon  >──< Literature        (M:N — pivot: uzgolon_literature)
Blog     >──< Qorboshi          (M:N — pivot: blog_qorboshi)
Blog     >──< Uzgolon           (M:N — pivot: blog_uzgolon)

Video      }──o Qorboshi        (N:1, optional)
Video      }──o Uzgolon         (N:1, optional)
HistoricalImage }──o Qorboshi   (N:1, optional)
HistoricalImage }──o Uzgolon    (N:1, optional)
TimelineEvent   }──o Qorboshi   (N:1, optional)
TimelineEvent   }──o Uzgolon    (N:1, optional)
TimelineEvent   }──o Region     (N:1, optional)
MapMarker       }──o Uzgolon    (N:1, optional)

Region         ──< Qorboshi     (1:N)
Region         ──< Uzgolon      (1:N)
Region         ──< HistoricalRegion (1:N, optional — zamonaviy↔tarixiy moslik)
Period         ──< Uzgolon      (1:N)
Period         ──< HistoricalRegion (1:N)
Period         ──< HistoricalMapLayer (1:N)

Comment  }──o {Qorboshi|Uzgolon|Literature|Video|Blog}   (polymorphic: commentable)
SourceReference }──o Literature  (N:1, optional — manba to'liq bir adabiyotga mos kelsa)
SourceReference >──< {Qorboshi|Uzgolon|TimelineEvent}     (polymorphic M:N: sourceable —
                                                            "kelajakda" kengaytma, §13)
```

**Nega Comment uchun polymorphic (`morphTo`)?** Laravel'da bu native va idiomatik yechim
(§24 talabi). Prisma'dagi kabi 5 ta nullable FK o'rniga bitta `commentable_type` +
`commentable_id` ustuni + `[commentable_type, commentable_id]` composite index — Eloquent
`MorphTo`/`MorphMany` orqali to'g'ridan-to'g'ri qo'llab-quvvatlanadi, kod ancha qisqaradi.

**Nega Region/Period/HistoricalRegion/HistoricalMapLayer alohida jadvallar?** §9–10 talabi:
xarita "oddiy zamonaviy Google Maps" bo'lmasligi, balki tarixiy davr/hudud/chegara/nom
qatlamlarini alohida boshqarish kerak. Shu sabab:
- `regions` — zamonaviy/umumiy filtrlash uchun oddiy hudud (masalan "Farg'ona vodiysi").
- `periods` — "tarixiy davr" (masalan "Bosmachilik harakati, 1918–1924").
- `historical_regions` — muayyan davrga tegishli tarixiy chegara/nom/geojson (masalan
  "Qo'qon xonligi, 1876-yilgacha") — `region_id` orqali zamonaviy hudud bilan bog'lanadi.
- `historical_map_layers` — georeferenced raster overlay metama'lumoti (rasm, bounds,
  opacity, qaysi davrga tegishli) — xaritada "layer" sifatida yoqilib/o'chirilib turadi.

**Nega Blog↔Qorboshi/Uzgolon M:N (pivot), spec'da "bog'liq qo'rboshi" birlik deyilgan bo'lsa ham?**
§41 "global content relationship" talabi bo'yicha bitta blog bir nechta tarixiy shaxs/voqeaga
tegishli bo'lishi tabiiy (masalan Andijon qo'zg'oloni haqidagi blog bir nechta qo'rboshini
tilga olishi mumkin). Nullable FK bilan boshlab, keyin M:N'ga o'tish og'ir migratsiya bo'lardi
— shu uchun boshidanoq M:N tanladim. Detail page'da UI baribir "asosiy bog'liq shaxs"ni
birinchi element sifatida ko'rsatishi mumkin.

---

## 4–6. Eloquent modellar, migration strukturasi va relationshiplar

Jadval nomlari **alohida belgilangan** (Laravel default pluralizatsiyasi o'rniga, o'zbekcha
route'lar bilan izchillik uchun): `qorboshilar`, `uzgolonlar`, `adabiyotlar`, `videolar`,
`bloglar`. Pivot jadval nomlari §49'dagi aniq ko'rsatmaga mos: `qorboshi_uzgolon`,
`qorboshi_literature`, `uzgolon_literature` (+ qo'shimcha `blog_qorboshi`, `blog_uzgolon`).

### `users`
| ustun | tur | izoh |
|---|---|---|
| id | bigIncrements | |
| name | string | |
| email | string, unique | |
| email_verified_at | timestamp, nullable | |
| password | string | bcrypt hash |
| avatar | string, nullable | Storage path |
| bio | text, nullable | |
| role | string (enum: `user`,`editor`,`admin`), default `user` | |
| remember_token | string, nullable | |
| timestamps | | |

### `regions`
id, `name` (unique), `slug` (unique), `description` (text, nullable), timestamps.

### `periods`
id, `name` (unique), `slug` (unique), `start_year` (int, nullable), `end_year` (int, nullable),
`description` (text, nullable), timestamps.

### `historical_regions`
id, `name`, `slug` (unique), `historical_name` (string, nullable), `modern_name` (string,
nullable), `description` (text, nullable), `geojson` (json, nullable — polygon),
`period_id` (FK `periods`, nullable), `region_id` (FK `regions`, nullable), timestamps.

### `historical_map_layers`
id, `title`, `period_id` (FK `periods`, nullable), `image_path` (string), `opacity` (float,
default 0.7), `bounds` (json — `{north,south,east,west}`), `is_active` (bool, default false),
`sort_order` (int, default 0), timestamps.

### `qorboshilar` (model: `Qorboshi`)
id, `slug` (unique), `full_name`, `short_description` (text), `biography` (longText),
`historical_context` (text, nullable), `birth_date` (date, nullable), `birth_place` (string,
nullable), `death_date` (date, nullable), `death_place` (string, nullable), `portrait_path`
(string, nullable), `region_id` (FK `regions`, nullable), `meta_title`/`meta_description`/
`og_image` (nullable — SEO), timestamps.

**Relationships:** `belongsTo(Region)`, `belongsToMany(Uzgolon, 'qorboshi_uzgolon')`,
`belongsToMany(Literature, 'qorboshi_literature')`, `hasMany(Video)`,
`hasMany(HistoricalImage)`, `hasMany(TimelineEvent)`, `morphMany(Comment, 'commentable')`,
`belongsToMany(Blog, 'blog_qorboshi')`.

### `uzgolonlar` (model: `Uzgolon`)
id, `slug` (unique), `name`, `start_date` (date, nullable), `end_date` (date, nullable),
`year` (int — tez filtr/saralash uchun, `start_date`dan hosila bo'lsa ham alohida ustun
sifatida saqlanadi), `short_description` (text), `historical_context` (text, nullable),
`causes` (text, nullable), `main_events` (longText, nullable), `results` (text, nullable),
`historical_significance` (text, nullable), `cover_image` (string, nullable), `region_id`
(FK `regions`, nullable), `period_id` (FK `periods`, nullable), `meta_title`/
`meta_description`/`og_image`, timestamps.

**Relationships:** `belongsTo(Region)`, `belongsTo(Period)`,
`belongsToMany(Qorboshi, 'qorboshi_uzgolon')`, `belongsToMany(Literature, 'uzgolon_literature')`,
`hasMany(Video)`, `hasMany(HistoricalImage)`, `hasMany(TimelineEvent)`, `hasMany(MapMarker)`,
`morphMany(Comment, 'commentable')`, `belongsToMany(Blog, 'blog_uzgolon')`.

### `adabiyotlar` (model: `Literature`)
id, `slug` (unique), `title`, `author`, `publisher` (string, nullable), `publication_year`
(int, nullable), `isbn` (string, nullable), `type` (string enum: `book`,`article`,`research`,
`archive`,`newspaper`,`journal`,`memoir`,`dissertation`,`other`), `language` (string,
nullable), `description` (text), `cover_path` (string, nullable), `source_url` (string,
nullable), `file_path` (string, nullable — PDF, Laravel Storage), `meta_title`/
`meta_description`/`og_image`, timestamps.

**Relationships:** `belongsToMany(Qorboshi, 'qorboshi_literature')`,
`belongsToMany(Uzgolon, 'uzgolon_literature')`, `hasMany(SourceReference)`,
`morphMany(Comment, 'commentable')`.

### `videolar` (model: `Video`)
id, `slug` (unique), `title`, `youtube_url`, `category` (string enum: `qorboshi`,`uzgolon` —
kelajakda `documentary`,`ai_reconstruction`,`historical_commentary`,`shorts` kengayadi, PHP
enum'ga qiymat qo'shish yetarli, migration shart emas), `duration_seconds` (int, nullable),
`description` (text, nullable), `thumbnail_path` (string, nullable), `qorboshi_id` (FK,
nullable), `uzgolon_id` (FK, nullable), `sources` (text, nullable), `meta_title`/
`meta_description`, timestamps.

**Relationships:** `belongsTo(Qorboshi)`, `belongsTo(Uzgolon)`,
`morphMany(Comment, 'commentable')`.

### `bloglar` (model: `Blog`)
id, `slug` (unique), `title`, `excerpt` (string, nullable), `content` (longText), `cover_path`
(string, nullable), `status` (string enum: `draft`,`pending`,`approved`,`rejected`, default
`draft`), `views` (int, default 0), `published_at` (timestamp, nullable), `author_id` (FK
`users`), `meta_title`/`meta_description`/`og_image`, timestamps.

> **Workflow soddalashtirildi:** Talabnomada `Draft → Submitted → Pending moderation →
> Approved → Published` beshta holat ko'rsatilgan, lekin "Submitted" va "Pending moderation"
> aslida bitta harakatning ikki nomi (submit qilish = moderatsiya navbatiga tushish) va
> "Approved" bilan "Published" o'rtasida alohida ish yo'q (tasdiqlangan blog darhol public
> bo'ladi, `published_at` shu payt to'ldiriladi). Shu sabab men holatlarni 4 taga
> soddalashtirdim: `draft → pending → approved/rejected`. Faqat `approved` bloglar public
> ko'rinadi (`published_at IS NOT NULL` orqali sanaladi).

**Relationships:** `belongsTo(User, 'author_id')`, `belongsToMany(Qorboshi, 'blog_qorboshi')`,
`belongsToMany(Uzgolon, 'blog_uzgolon')`, `morphMany(Comment, 'commentable')`.

### `comments` (model: `Comment`)
id, `content` (text), `status` (string enum: `pending`,`approved`,`rejected`, default
`pending`), `author_id` (FK `users`), `commentable_type` (string), `commentable_id`
(unsignedBigInteger), timestamps. Index: `[commentable_type, commentable_id]`.

**Relationships:** `belongsTo(User, 'author_id')`, `morphTo('commentable')`.

### `historical_images` (model: `HistoricalImage`)
id, `title` (string, nullable), `file_path`, `caption` (text, nullable), `source` (string,
nullable), `source_url` (string, nullable), `copyright` (string, nullable), `year` (int,
nullable), `alt_text` (string, nullable), `qorboshi_id` (FK, nullable), `uzgolon_id` (FK,
nullable), timestamps.

### `timeline_events` (model: `TimelineEvent`)
id, `title`, `date_from` (date, nullable), `date_to` (date, nullable), `year` (int —
saralash/filtr uchun asosiy ustun), `description` (text), `image_path` (string, nullable),
`qorboshi_id` (FK, nullable), `uzgolon_id` (FK, nullable), `region_id` (FK, nullable),
`sort_order` (int, default 0), timestamps.

### `map_markers` (model: `MapMarker`)
id, `title`, `latitude` (decimal 10,7), `longitude` (decimal 10,7), `type` (string enum:
`uprising`,`battle`,`region_center`,`other`, default `uprising`), `description` (text),
`uzgolon_id` (FK, **nullable** — §23 "MapMarker → Uzgolon: Optional"), timestamps.

### `source_references` (model: `SourceReference`) — provenance, §25
id, `author`, `title`, `publisher` (string, nullable), `year` (int, nullable), `url` (string,
nullable), `page` (string, nullable — "125–128" kabi range saqlash uchun string), `note`
(text, nullable), `source_type` (string enum: `book`,`article`,`archive`,`newspaper`,
`interview`,`website`,`other`), `literature_id` (FK `adabiyotlar`, nullable — agar manba
to'liq bitta adabiyot yozuviga mos kelsa), timestamps.

> **Kelajakdagi kengaytma (hozir qurilmaydi, faqat joy qoldiriladi):** har qanday faktni
> aniq manba+sahifaga bog'lash uchun polymorphic pivot `source_reference_sourceable`
> (`source_reference_id`, `sourceable_type`, `sourceable_id`) — `Qorboshi`, `Uzgolon`,
> `TimelineEvent` kabi istalgan modelga `morphedByMany(SourceReference, 'sourceable')`
> orqali ulanadi. Bu migratsiyani hozir qo'shish shart emas (mavjud `source_references`
> jadvaliga keyin FK-siz pivot qo'shish oson, boshqa jadvallarga ta'sir qilmaydi).

---

## 7. Authentication arxitekturasi

- Laravel'ning o'z auth tizimi (`Illuminate\Auth`), Blade asosidagi login/register
  formalari — alohida paket (Breeze/Jetstream) shart emas, chunki view'lar to'liq qo'lda
  (loyihaning vizual uslubiga mos) yoziladi.
- `users.password` — `Hash::make()` (bcrypt) orqali.
- Session-based auth (Laravel default, `web` guard) — SPA emas, klassik server-rendered
  oqim uchun eng mos.
- Route'lar: `/kirish` (login), `/royxatdan-otish` (register), `/profil` (profile,
  `auth` middleware bilan himoyalangan).
- Parol tiklash (`forgot password`) — keyingi bosqichda qo'shiladi (hozir scope'da shart
  emas, lekin Laravel buni tayyor beradi).

---

## 8. Role/permission arxitekturasi

- `users.role`: `user` | `editor` | `admin` (oddiy string ustun, alohida `roles`/
  `permissions` jadvali **hozircha kerak emas** — uch xil rol va scope aniq, Spatie
  Permission kabi paket ortiqcha murakkablik bo'lardi; agar kelajakda granular
  permission kerak bo'lsa, shu ustunni almashtirmasdan ustiga qo'shish mumkin).
- **Gates** (`AuthServiceProvider` yoki `bootstrap/app.php`): `manage-content` (admin-only
  CRUD), `moderate` (editor+admin).
- **Policies** har bir moderatsiya/egalik talab qiluvchi model uchun:
  - `BlogPolicy`: `update`/`delete` — muallif (faqat `draft` holatda) yoki admin;
    `moderate` (approve/reject) — editor/admin.
  - `CommentPolicy`: `update`/`delete` — muallif yoki admin; `moderate` — editor/admin.
  - `QorboshiPolicy`, `UzgolonPolicy`, `LiteraturePolicy`, `VideoPolicy`,
    `MapMarkerPolicy`, `TimelineEventPolicy`, `UserPolicy`: to'liq CRUD — faqat `admin`.
- **Middleware:** `EnsureUserHasRole:admin,editor` (custom) — `/admin/**` route group'ga
  qo'llanadi; controller ichida yana Policy orqali qayta tekshiriladi (defense-in-depth).

---

## 9. Admin arxitekturasi

- `/admin` prefiksi, `admin` route group, `auth` + rol middleware bilan himoyalangan.
- Layout: `resources/views/layouts/admin.blade.php` (sidebar + top statistika).
- Har bir entity uchun bir xil pattern: `Controller@index` (DataTable+filter),
  `@create`/`@edit` (Blade form + Form Request validatsiya), `@store`/`@update`/`@destroy`.
- Umumiy Blade komponentlar (`x-admin.data-table`, `x-admin.form-field`,
  `x-admin.stat-card`, `x-admin.confirm-delete`) — bir xil UI'ni har bir CRUD sahifasida
  qayta ishlatish, kod dublikatsiyasidan qochish uchun.
- Dashboard statistikasi (`AdminDashboardController`) — har bir model uchun `::count()`,
  pending blog/comment uchun `where('status','pending')->count()`. Kod:
  ```php
  $stats = [
      'qorboshilar' => Qorboshi::count(),
      'uzgolonlar' => Uzgolon::count(),
      'adabiyotlar' => Literature::count(),
      'videolar' => Video::count(),
      'bloglar' => Blog::count(),
      'users' => User::count(),
      'pending_blogs' => Blog::where('status', 'pending')->count(),
      'pending_comments' => Comment::where('status', 'pending')->count(),
  ];
  ```
- Map/Region/Period/HistoricalMapLayer boshqaruvi ham shu admin qatlamida (§37): marker
  qo'shish/tahrirlash formasi kichik preview-xarita bilan (MapLibre, faqat bitta marker
  ko'rsatuvchi "tanlash" rejimida).

---

## 10. Public page hierarchy (routing)

```
/                              Bosh sahifa
/qorboshilar                   Ro'yxat + filter (hudud, davr, qo'zg'olon, ism)
/qorboshilar/{slug}             Detail
/qozgolonlar                    Xarita + ro'yxat + filter (yil, davr, hudud)
/qozgolonlar/{slug}              Detail
/adabiyotlar                    Ro'yxat + filter (muallif, yil, tur, mavzu)
/adabiyotlar/{slug}               Detail
/videolar                       Kategoriya bo'yicha ro'yxat
/videolar/{slug}                  Detail
/bloglar                        Public listing (faqat approved)
/bloglar/{slug}                   Detail + comments
/bloglar/yozish                  Draft yaratish (auth)
/qidiruv?q=...                   Global search, guruhlangan natijalar
/boglanish                      Contact
/profil                         Joriy user profili (auth)
/kirish, /royxatdan-otish         Auth
/admin/**                       Admin panel (role-guarded)
```

Route model binding — barcha `{slug}` parametrlar `getRouteKeyName()` orqali `slug`
ustunidan (id emas) bog'lanadi.

---

## 11. Blade component hierarchy

```
<x-layouts.app>                       # umumiy public layout
  <x-layout.navbar />
  <x-layout.mobile-menu />
  {slot}
  <x-layout.footer />

<x-layouts.admin>                     # admin layout
  <x-admin.sidebar />
  {slot}

# Content komponentlari (resources/views/components/)
<x-qorboshi-card :qorboshi="$item" />
<x-uprising-card :uzgolon="$item" />
<x-literature-card :item="$item" />
<x-video-card :video="$video" />
<x-blog-card :blog="$blog" />
<x-pagination :paginator="$items" />
<x-search-bar />
<x-filter-panel :filters="$filters" />
<x-map.uprising-map :markers="$markers" />
<x-comment-section :commentable="$model" />
<x-gallery :images="$images" />
<x-timeline :events="$events" />
<x-related-entities :model="$model" />
<x-seo :title="..." :description="..." :og-image="..." />

# Admin komponentlari
<x-admin.data-table :rows="$rows" :columns="$columns" />
<x-admin.form-field ... />
<x-admin.stat-card :label="..." :value="..." />
<x-admin.confirm-delete :action="..." />
```

Har bir card/section **Blade component** sifatida yoziladi — sahifalar ichida HTML
copy-paste qilinmaydi (§29 talabi).

---

## 12. Interaktiv xarita arxitekturasi

- **Kutubxona:** ~~MapLibre GL JS~~ — Faza 15'da **olib tashlandi**, o'z SVG
  dvigatelimiz (`resources/js/map/svg-map.js`) bilan almashtirildi. Sabab va
  batafsil qaror §35'da.
- **Ma'lumot oqimi:** `MapController@index` → `MapService` → `MapMarker::with('uzgolon')`
  → GeoJSON `FeatureCollection`ga aylantiriladi → Blade view'ga `json_encode` qilib
  `data-markers` attribute yoki `<script type="application/json">` orqali uzatiladi →
  Alpine.js/vanilla JS komponent MapLibre'ni shu bilan ishga tushiradi.
- **Hech qanday xarita ma'lumoti hardcode qilinmaydi** — barchasi `map_markers`,
  `regions`, `historical_regions`, `historical_map_layers` jadvalidan keladi (§9 talabi).
- **Filter (yil/davr/hudud):** query string orqali server-side filtrlash
  (`GET /qozgolonlar?period=...&region=...`) — marker soni ko'p bo'lmagani uchun boshida
  har bir filtrlash uchun to'liq sahifa/qisman (Alpine `x-data` + `fetch`) yangilanishi
  yetarli; keyinchalik kerak bo'lsa client-side filtrlashga o'tish mumkin.
- **Clustering:** marker soni ortganda `supercluster` (MapLibre bilan standart).
- **Mobil holat (§36):** xarita full-width, filter pastdan chiqadigan drawer, marker
  popup o'rniga pastdan ochiladigan detail panel (bottom sheet, Alpine `x-show` bilan).

---

## 13. Tarixiy xarita arxitekturasi (kelajakka tayyorlash)

Qatlamlar ierarxiyasi (§39):

```
Period (tarixiy davr)
   → HistoricalRegion (shu davrga tegishli hudud/chegara/nom, geojson)
       → Region (zamonaviy hudud bilan moslik, ixtiyoriy)
   → HistoricalMapLayer (shu davrga tegishli georeferenced raster overlay)
Uzgolon → Period, Region (qaysi davr/hudud kontekstida sodir bo'lgan)
Qorboshi → Region (faoliyat hududi)
```

- Xarita UI'da **davr selektori** (masalan dropdown: "1876-yilgacha", "1918–1924")
  tanlanganda faqat shu davrga tegishli `HistoricalMapLayer` (agar `is_active`) va
  `HistoricalRegion` chegaralari ko'rsatiladi/almashtiriladi — bu MapLibre'da alohida
  `source`/`layer` sifatida qo'shilib, video selektor o'zgarganda `setLayoutProperty`
  bilan `visibility` almashtiriladi (butun xarita qayta yuklanmaydi).
- `HistoricalMapLayer.bounds` — MapLibre `ImageSource`'ning `coordinates` (4 burchak
  lat/lng) formatiga mos keladi — georeferenced rasm to'g'ridan-to'g'ri shu ustunlardan
  qo'yiladi.
- Boshlang'ich bosqichda (hozir) faqat standart OpenStreetMap/MapTiler tile + zamonaviy
  `regions` chegaralari (agar geojson kiritilsa); `HistoricalMapLayer`/`HistoricalRegion`
  jadvallari va admin CRUD **strukturaviy tayyor** bo'ladi, lekin haqiqiy tarixiy
  rasm/chegara keyinroq, ishonchli manba asosida kiritiladi.

---

## 14. Design system

O'zgarishsiz, oldingi hujjatdagi bilan bir xil (loyiha vizual yo'nalishi Laravel'ga
o'tishdan ta'sirlanmaydi):

**Ranglar**

| Token | Hex | Ishlatilishi |
|---|---|---|
| Ink | `#1A1512` | Asosiy matn |
| Paper | `#FBF6EC` | Asosiy fon |
| Paper Dark | `#EFE4CE` | Card fon, ikkinchi darajali |
| Brown 900 | `#3B2A1D` | Header/footer, qorong'u bloklar |
| Brown 700 | `#6B4A32` | Ikkinchi darajali matn, border |
| Brown 500 | `#8C6845` | Hover holatlar |
| Gold 600 | `#A6791E` | Asosiy aksent (CTA, link) |
| Gold 400 | `#C9A227` | Aksent hover |
| Sand | `#E4D6B8` | Ajratuvchi chiziqlar |
| Danger | `#8B2E2E` | Reject/xato |
| Success | `#3F6B3F` | Approved |

Tailwind v4'da bu tokenlar `resources/css/app.css` ichida `@theme` direktivasi orqali
CSS custom property sifatida ro'yxatdan o'tkaziladi (Tailwind v4 config-less yondashuvi).

**Tipografiya:** Heading — `Playfair Display`; Body — `Inter`; Iqtibos/epigraf (ixtiyoriy)
— `Cormorant Garamond`. Google Fonts orqali (`<link>` yoki self-hosted `public/fonts`).

**Vizual tamoyillar:** subtle pergament tekstura (fon, past opacity), cinematic rasm
taqdimoti (qat'iy aspect-ratio, object-cover), minimal border-radius (~6-8px), faqat
nozik hover/fade animatsiyalar — §55–56 taqiqlariga qat'iy rioya (neon, gaming, SaaS
dashboard, haddan tashqari animatsiya yo'q).

---

## 15. Responsive strategiya

- **Dizayn yondashuvi:** Desktop-first *kontent tarkibi* (xarita, sidebar, timeline katta
  ekranda "yashaydi"), lekin **implementatsiya mobile-first Tailwind** utility'lari bilan
  (`base → sm → md → lg → xl`) — bu ikkalasi ziddiyatli emas (§35).
- **Breakpointlar:** Tailwind default (`sm:640px md:768px lg:1024px xl:1280px 2xl:1536px`).
- **Xarita sahifasi:** `lg`dan past ekranda xarita/ro'yxat tab yoki accordion'ga aylanadi,
  filter pastdan chiqadigan drawer (§36).
- **Navbar:** `lg`dan past — hamburger + Alpine.js bilan boshqariladigan drawer.
- **Admin panel:** `md`dan past — sidebar collapse/off-canvas.

---

## 16. Development bosqichlari (talabnomadagi 20 faza bilan bir xil, holat belgilangan)

| Faza | Nomi | Holat |
|---|---|---|
| 1 | Laravel project setup | ✅ Bajarildi (composer create-project, Tailwind v4, Vite, Alpine.js, folder architecture) |
| 2 | Database architecture | ✅ Ushbu hujjat (§3–6) |
| 3 | Models + migrations | ✅ Bajarildi — 21 migratsiya (barcha jadval+pivotlar), 14 Eloquent model + `User`, 7 PHP enum (`Role`,`BlogStatus`,`CommentStatus`,`LiteratureType`,`VideoCategory`,`MapMarkerType`,`SourceType`), barcha relationshiplar tinker orqali tekshirildi |
| 4 | Authentication | ✅ Bajarildi — session-based auth (`/kirish`, `/royxatdan-otish`, `/profil`), rate-limited login, avatar upload, curl orqali to'liq oqim tekshirildi |
| 5 | Roles + Policies | ✅ Bajarildi — `role` middleware, 2 Gate (`manage-content`,`moderate`), 9 Policy (barcha model uchun), tinker orqali tekshirildi |
| 6 | Admin skeleton | ✅ Bajarildi — layout+sidebar+topbar, 10 ta reusable `x-admin.*` komponent, 13 route (faqat `role:admin`), dashboard real statistika bilan, 11 entity uchun generic placeholder-index (CRUD hali yo'q) |
| 7 | Public layout | ✅ Bajarildi — SEO-tayyor layout, 10 ta `x-ui.*` reusable komponent (avvalgi admin-only button/badge/card/pagination/alert shu namespace'ga ko'chirildi, duplicate yo'q), homepage 8 ta section bilan (hammasi real DB + empty-state) |
| 8 | Qorboshilar | ✅ Bajarildi — to'liq CRUD (admin+public), status/featured/provenance arxitekturasi, media upload+cleanup. Batafsil: §17 |
| 9 | Qo'zg'olonlar | ✅ Bajarildi — to'liq CRUD (admin+public), status/featured/provenance, MapLibre interaktiv xarita (birinchi versiya). Batafsil: §18 |
| 10 | Interactive map | ✅ Faza 9'da birlashtirilgan holda bajarildi (§18) |
| 11 | Literature | ✅ Bajarildi — to'liq CRUD, status/featured, PDF/cover upload. Batafsil: §19 |
| 12 | Videos | ✅ Bajarildi — to'liq CRUD, xavfsiz YouTube embed. Batafsil: §20 |
| 13 | Blogs | ✅ Bajarildi — egalik + moderatsiya workflow (draft→pending→approved/rejected). Batafsil: §21 |
| 14 | Comments | ✅ Bajarildi — polymorphic moderatsiya, XSS/rate-limit himoyasi. Batafsil: §24 |
| 15 | Global search | ✅ Bajarildi — 5 model, published-only, flat pagination. Batafsil: §23 |
| — | HistoricalRegion + HistoricalMapLayer Admin CRUD (Faza 12) | ✅ Bajarildi — texnik GeoJSON validatsiya, admin CRUD, MapLibre preview. Batafsil: §25-26 |
| — | Real Historical Turkiston Map (Faza 13) | ✅ Texnik integratsiya to'liq; real tarixiy ma'lumot hali tasdiqlanmagan (§68). Batafsil: §27-28 |
| — | Historical Timeline + Map Synchronization (Faza 14) | ✅ Bajarildi — Timeline CRUD, public sahifalar, xarita sinxronizatsiyasi, search/comments integratsiyasi. Batafsil: §29-30 |
| — | Final Technical Completion (Production Readiness) | ✅ Bajarildi — xavfsizlik, SEO, i18n, deployment. Batafsil: §31-34 |
| 16 | Timeline | ⏭ |
| 17 | SEO | ⏭ |
| 18 | Performance | ⏭ |
| 19 | Responsive refinement | ⏭ |
| 20 | Testing + deployment prep | ⏭ |

Har fazadan keyin: qaysi fayllar yaratilgani, qanday qarorlar qabul qilingani va keyingi
faza nima ekani qisqacha yoziladi (§ "har bir katta bosqich tugagach" talabi).

---

## 17. Qorboshi content architecture (Faza 8)

Bu bo'lim Faza 8'da Qorboshi entitysi uchun qurilgan to'liq CRUD arxitekturasini qayd
etadi — kelajakda boshqa entitylar (Uzgolon, Literature, Video, Blog) xuddi shu naqsh
bo'yicha quriladi.

### 17.1 Schema o'zgarishlari

Faza 3'dagi `qorboshilar` jadvali tekshirilib, quyidagi **maqsadli** o'zgarishlar kiritildi
(migratsiya: `update_qorboshilar_table_for_content_architecture`):

| O'zgarish | Sabab |
|---|---|
| `birth_date`/`death_date` (`date`) → `birth_year`/`death_year` (nullable `smallInteger`) | Tarixiy shaxslarning aniq tug'ilgan/vafot **kuni** deyarli hech qachon ishonchli ma'lum bo'lmaydi — faqat yil. `date` ustuni soxta aniqlikka majbur qilardi (masalan "1892-01-01" yozishga to'g'ri keladi, holbuki faqat yil ma'lum). |
| `active_from_year`/`active_to_year` qo'shildi (nullable `smallInteger`) | "Faoliyat davri" tabiatan yil-darajasidagi tushuncha (masalan 1918–1924) — butun loyihada sanalar shu formatda ishlatiladi. |
| `status` qo'shildi (`string`, default `draft`) | `QorboshiStatus` enum (`draft`,`published`) — public sahifada faqat `published` ko'rinadi. |
| `featured` qo'shildi (`boolean`, default `false`) | Bosh sahifada ko'rsatiladigan tanlangan qo'rboshilar. |

**O'zgartirilmadi (mavjud arxitektura buzilmadi):** `full_name` (spec "name" so'ragan bo'lsa
ham, `full_name` allaqachon butun kodbazada ishlatilgani uchun qayta nomlanmadi), `slug`,
`short_description`, `biography`, `historical_context`, `birth_place`, `death_place`,
`portrait_path`, `region_id`, `meta_title`/`meta_description`/`og_image` — hammasi Faza
3'dagidek qoldi.

**Yangi jadval — `sourceables`** (polymorphic pivot, Faza 3'da rejalashtirilgan edi):
`id`, `source_reference_id` (FK, cascade), `sourceable_type`, `sourceable_id`, timestamps.
`Qorboshi::sourceReferences()` → `morphToMany`, `SourceReference::qorboshilar()` →
`morphedByMany`. Kelajakda `Uzgolon`/`TimelineEvent` ham xuddi shu jadvalga (migratsiyasiz)
ulanishi mumkin.

### 17.2 Cover image vs gallery

`Qorboshi.portrait_path` — **muqova/profil rasmi** (bitta, to'g'ridan-to'g'ri ustunda).
`HistoricalImage` (mavjud, o'zgarishsiz) — **galereya** (bir nechta rasm, `qorboshi_id`
orqali). Bu ajratish alohida "is_cover" flag talab qilmaydi — ikkala tushuncha allaqachon
tabiiy ravishda ikki xil joyda yashaydi.

### 17.3 Relationshiplar (holat)

| Relationship | Turi | Holat |
|---|---|---|
| Qorboshi ↔ Region | `belongsTo`/`hasMany` | Faza 3'dan, o'zgarishsiz |
| Qorboshi ↔ Uzgolon | `belongsToMany` (`qorboshi_uzgolon`) | Faza 3'dan; admin forma orqali sync qilinadi |
| Qorboshi ↔ Literature | `belongsToMany` (`qorboshi_literature`) | Faza 3'dan; admin forma orqali sync qilinadi |
| Qorboshi ↔ SourceReference | `morphToMany` (`sourceables`) | **Yangi** (Faza 8) |
| Qorboshi ↔ Video | `hasMany` (Video tomonda `belongsTo`) | Faza 3'dan; **create formada tanlanmaydi** — chunki bu 1:N (Video'ning yagona egasi), Video CRUD hali yo'q, biriktiradigan narsa yo'q. Detail sahifada avtomatik ko'rsatiladi. |
| Qorboshi ↔ HistoricalImage | `hasMany` | Faza 3'dan; galereya upload orqali to'ldiriladi |
| Qorboshi ↔ Comment | `morphMany` | Faza 3'dan; UI hali yo'q (izohlar — Faza 14) |

### 17.4 CRUD arxitekturasi

```
Route (resource, slug binding)
   → QorboshiController (Admin yoki Public)
       → Form Request (Store/UpdateQorboshiRequest) — validatsiya
       → QorboshiService — create/update/delete, slug generatsiya, relation sync
       → QorboshiMediaService — fayl upload/o'chirish
   ← View (admin/qorboshilar/* yoki qorboshilar/*)
```

**Admin routes** (`Route::resource('qorboshilar', ...)->parameters(['qorboshilar' => 'qorboshi'])`):
`index`, `create`, `store`, `show`, `edit`, `update`, `destroy` + qo'shimcha
`DELETE /admin/qorboshilar/{qorboshi}/images/{image}` (galereya rasmini o'chirish).

**Public routes:** `GET /qorboshilar` (published, filter+qidiruv+pagination),
`GET /qorboshilar/{slug}` (faqat published — draft uchun 404, guest'ga policy emas, oddiy
scoped query orqali).

**Slug:** bo'sh qoldirilsa `full_name`dan avtomatik generatsiya qilinadi
(`Str::slug` + collision bo'lsa `-2`, `-3` ... qo'shiladi). Admin qo'lda kiritsa,
`unique:qorboshilar,slug` validatsiya qiladi (duplicate kiritilsa xato qaytaradi).
**Muhim:** tahrirlashda `full_name` o'zgarsa ham, agar admin slug'ni qo'lda o'zgartirmasa,
**slug o'zgarmaydi** — bu public URL/SEO barqarorligini saqlash uchun ataylab shunday
(birinchi implementatsiyada bu xato bor edi, test paytida topilib tuzatildi — pastdagi
§17.7 ga qarang).

### 17.5 Media arxitekturasi

`QorboshiMediaService` — Laravel `Storage` (`public` disk) orqali:
- Portret: `qorboshilar/portraits/`, eskisi almashtirilganda avtomatik o'chiriladi.
- Galereya: `qorboshilar/gallery/`, har biri alohida `HistoricalImage` qatori.
- Fayl nomlari — Laravel'ning o'zi generatsiya qiladigan tasodifiy hash (`store()`),
  user-supplied nom hech qachon fayl tizimiga yozilmaydi (xavfsiz).
- Validatsiya: portret — `image, mimes:jpg,jpeg,png,webp, max:4096` (4MB); galereya —
  har biri `max:6144` (6MB).
- **O'chirish:** Qorboshi o'chirilganda `deleteAllMedia()` portret + barcha galereya
  fayllarini diskdan o'chiradi. `historical_images` qatorlari FK cascade orqali avtomatik
  o'chadi (DB darajasida), lekin fayllar buni **kutmaydi** — shuning uchun service orqali
  alohida tozalanadi (aks holda orphan fayl qolardi).
- **Comment** — polymorphic bo'lgani uchun DB FK cascade yo'q; Qorboshi o'chirilganda
  `$qorboshi->comments()->delete()` qo'lda chaqiriladi (orphan qoldirmaslik uchun).

### 17.6 Provenance (manba) arxitekturasi

`SourceReference` (Faza 3'dan) + yangi `sourceables` polymorphic pivot orqali:

```
Qorboshi ──sourceables──> SourceReference (author, title, publisher, year, url, page, note)
                                │
                                └─ literature_id (ixtiyoriy) → Literature (to'liq adabiyot yozuvi)
```

Bitta `SourceReference` qatori o'zi "Manba → Kitob → Sahifa → Izoh" zanjirini ifodalaydi
(`page` va `note` ustunlari orqali) — alohida "Fact" modeli hali kerak emas. Admin formada
mavjud manbalarni tanlash mumkin (manba boshqaruvi — CRUD — keyingi bosqichda).

### 17.7 Testda topilgan va tuzatilgan muammo

Dastlabki implementatsiyada `QorboshiService::update()` slug bo'sh yuborilganda uni har
doim `full_name`dan qayta generatsiya qilar edi — natijada faqat ismni tahrirlash ham
**slug'ni (demak public URL'ni) jimgina o'zgartirib yuborardi**. Curl orqali qilingan
end-to-end testda aniqlandi (ism yangilanganda slug kutilmaganda o'zgargani ko'rindi) va
darhol tuzatildi: endi bo'sh slug — faqat **create**'da `full_name`dan generatsiya qilinadi,
**update**'da esa mavjud slug saqlanadi (agar admin uni qo'lda o'zgartirmasa).

---

## 18. Qo'zg'olon (Uzgolon) content architecture + interaktiv xarita (Faza 9)

### 18.1 Logo integratsiyasi

`public/logo/` papkasida tayyor logo to'plami topildi (yangi logo yaratilmadi, mavjudi
ishlatildi): dumaloq emblem (favicon-16/32/64/192/512, apple-touch-icon-180) va ikkita
gorizontal lockup (`og-image-1200x630.png` — krem fon, `og-image-yashil-1200x630.png` —
to'q yashil fon). Qo'llanilishi:
- **Navbar/footer/admin sidebar:** `favicon-64x64.png` (dumaloq emblem) matn wordmark
  yonida — chunki hech bir fayl shaffof fonli/tor-cropped gorizontal variant emas edi.
- **`<head>` favicon linklari:** barcha o'lchamdagi `favicon-*.png` + `apple-touch-icon`.
- **Default Open Graph rasm:** `og-image-1200x630.png` (krem fon — sayt "paper" foniga
  yaqinroq) — `layouts/app.blade.php`dagi `$seo['image']` default qiymati.
- Hech bir fayl o'chirilmadi/qayta yozilmadi/ustidan yozilmadi.

### 18.2 Uzgolon schema o'zgarishlari

Qorboshi'dagi bir xil tamoyil qo'llandi (migratsiya:
`update_uzgolonlar_table_for_content_architecture`):

| O'zgarish | Sabab |
|---|---|
| `year` → `start_year`ga nomlandi (`renameColumn`, ma'lumot yo'qolmadi) | Mavjud ustun aslida "boshlanish yili" ma'nosida edi |
| `end_year` qo'shildi (nullable) | Qo'zg'olon davri (masalan 1918–1924) |
| `start_date`/`end_date` (`date`) o'chirildi | Qorboshi'dagi kabi — soxta kun/oy aniqligi |
| `historical_location`/`modern_location` (nullable string) qo'shildi | Spec talabi — sodda matn maydonlar, `HistoricalRegion` kabi to'liq modelga majburlanmadi |
| `status`, `featured` qo'shildi | Qorboshi bilan bir xil naqsh |

**`latitude`/`longitude` Uzgolon jadvaliga QO'SHILMADI** — chunki mavjud `MapMarker`
modeli (Faza 3) allaqachon bu vazifani bajaradi (`hasMany`, `uzgolon_id` FK). Buning
o'rniga `Uzgolon::primaryMarker()` — `hasOne(MapMarker::class)->oldestOfMany()` —
qo'shildi: admin formadagi "Xarita" bo'limi shu yagona "asosiy" markerni
yaratadi/yangilaydi/o'chiradi (`UzgolonService::syncPrimaryMarker()`), lekin
`mapMarkers()` (`hasMany`) kelajakda bir nechta marker (masalan turli jang joylari)
qo'shishga tayyor bo'lib qoladi — migratsiyasiz.

**Tarixiy bayon maydonlari o'zgartirilmadi:** spec bitta generic "description" maydonini
so'ragan edi, lekin mavjud `Uzgolon`da allaqachon 5 ta aniqroq maydon bor edi
(`historical_context`, `causes`, `main_events`, `results`, `historical_significance`) —
bular saqlanib qoldi (generic maydonga tushirish aslida orqaga qadam bo'lardi).

### 18.3 Relationshiplar (holat)

| Relationship | Holat |
|---|---|
| Uzgolon ↔ Qorboshi | Faza 3'dan, o'zgarishsiz (`qorboshi_uzgolon` pivot) |
| Uzgolon ↔ Literature | Faza 3'dan, o'zgarishsiz (`uzgolon_literature` pivot) |
| Uzgolon ↔ Video | Faza 3'dan, o'zgarishsiz (`hasMany`, Video CRUD hali yo'q) |
| Uzgolon ↔ HistoricalImage | Faza 3'dan, o'zgarishsiz (cover — `cover_image` ustunida, galereya — alohida jadval, xuddi Qorboshi'dagidek) |
| Uzgolon ↔ TimelineEvent | Faza 3'dan, o'zgarishsiz (`hasMany`) |
| Uzgolon ↔ MapMarker | Faza 3'dan; **yangi** `primaryMarker()` accessor qo'shildi |
| Uzgolon ↔ SourceReference | **Yangi** (Faza 9) — Qorboshi'dagi bir xil `sourceables` polymorphic pivot qayta ishlatildi, yangi migratsiya kerak bo'lmadi |
| Uzgolon ↔ Comment | Faza 3'dan, o'zgarishsiz (`morphMany`, UI hali yo'q) |

### 18.4 CRUD arxitekturasi

Qorboshi bilan bir xil qatlamlar: `Route::resource('qozgolonlar', ...)->parameters([...
=> 'qozgolon'])` → `UzgolonController` → `Store/UpdateUzgolonRequest` → `UzgolonService`
(+ `UzgolonMediaService`) → View. Slug — bo'sh bo'lsa `name`dan avtogenerate + unique
validatsiya; **update'da mavjud slug saqlanadi** (Faza 8'da topilgan xatoni boshidanoq
oldini olish uchun shunga alohida e'tibor berildi).

### 18.5 Interaktiv xarita (MapLibre GL JS)

```
UzgolonService::publicGeoJson(regionId?, periodId?)
    → faqat published() + primaryMarker mavjud yozuvlar
    → GeoJSON FeatureCollection (PHP massiv)
Controller → View (Blade @js() orqali xavfsiz JSON encode)
    → resources/js/map/uzgolon-map.js (`initUzgolonMap`)
    → MapLibre GL (npm, Vite orqali bundle qilingan — CDN emas)
```

- **Uslub (style):** `https://tiles.openfreemap.org/styles/positron` — OpenFreeMap
  (bepul, API-kalitisiz, rate-limit'siz, OpenMapTiles/OSM ma'lumotlari asosida;
  §35'ga qarang). Avvalgi `demotiles.maplibre.org` faqat quruq materik konturi
  edi — O'zbekiston/Turkiston hududini "xarita" sifatida tanib bo'lmas edi.
  Kelajakda tarixiy Turkiston overlay/uslub bilan qo'shimcha boyitiladi
  (pastga qarang, §18.6), lekin bazaviy uslub allaqachon production-grade.
- **Sahifalar:** `/xarita` (to'liq, barcha published marker + region/period filtri,
  server-side query string orqali), homepage preview (kompakt, `interactive:false`),
  qo'zg'olon detail sahifasi (bitta marker, agar mavjud bo'lsa).
- **Popup:** nom, yil oralig'i, hudud, qisqa tavsif, "Batafsil" havolasi — xarita
  dizayni sayt tokenlariga moslashtirilgan (`app.css`dagi `.map-popup*` klasslar).
  Google Maps ishlatilmadi.
- **Filter:** query string orqali server qayta so'rov beradi (client-side JS filter
  emas) — "hozir ortiqcha complexity kiritma" talabiga muvofiq.
- **Clustering:** **hali yo'q** — ataylab (spec: "hozir ortiqcha complexity kiritma").
  Marker soni ko'paysa, keyingi bosqichda `supercluster` qo'shiladi (MapLibre bilan
  standart integratsiya, ARCHITECTURE.md §12'da rejalashtirilgan).

### 18.6 Future-ready xarita modellari (bu fazada implement QILINMADI)

Quyidagilar Faza 3'dan beri mavjud, ushbu fazada **tekshirildi va o'zgartirilmadi** —
ular allaqachon kelajakdagi talablarga tayyor:

- **`HistoricalRegion`** (`historical_name`, `modern_name`, `geojson`, `period_id`,
  `region_id`) — tarixiy chegara/nom uchun to'liq tayyor, hozircha CRUD yo'q.
- **`HistoricalMapLayer`** (`period_id`, `image_path`, `bounds`, `opacity`,
  `is_active`) — georeferenced raster overlay uchun tayyor, hozircha CRUD yo'q.
- **`Period`** — allaqachon yil-darajasida (`start_year`/`end_year`), Uzgolon bilan
  bog'langan, filtrlarda ishlatiladi.

Bular hali **CRUD'siz** — admin panelda "Tarixiy xaritalar" bo'limi Faza 6'dan beri
placeholder-index holatida qoladi (bu fazada CRUD qo'shish talab qilinmagan edi).
Kelajakda: davr tanlanganda MapLibre'da mos `HistoricalMapLayer` `ImageSource`
sifatida (`bounds` — to'g'ridan-to'g'ri MapLibre formatiga mos) qo'shiladi va
`HistoricalRegion.geojson` chegara sifatida chiziladi — hozirgi `uzgolon-map.js`
strukturasi bunga layer qo'shish orqali kengaytiriladi (qayta yozish shart emas).

### 18.7 Testda topilgan va tuzatilgan muammo

`admin/qozgolonlar/index.blade.php`da `:headers="['...', \"Qo'rboshilar\", '...']"` —
Blade component atributi ichida backslash bilan escape qilingan qo'sh tirnoq (`\"..\"`)
ishlatilgan edi. Bu Blade'ning attribute-parsing mexanizmini chalg'itib, **compile
vaqtida `unexpected token "endif"` xatosiga** olib keldi (butun sahifa 500 bergan).
Sababi qidirilib, xuddi shu naqsh Faza 8'dagi `qorboshilar/show.blade.php`da ham
(tasodifan ishlab turgan) borligi aniqlandi — ikkalasi ham `@php` blokida oldindan
massiv sifatida hisoblab, komponentga oddiy o'zgaruvchi (`:headers="$tableHeaders"`)
sifatida uzatishga o'zgartirildi. Umumiy qoida: **Blade component atributlarida
apostrof bor satrni backslash-escaped qo'sh tirnoq bilan aralashtirmaslik** —
buning o'rniga `@php` blokida o'zgaruvchi tayyorlash xavfsizroq.

---

## 19. Literature (Adabiyotlar) content architecture (Faza 10)

### 19.1 Schema

Faza 3'dagi `adabiyotlar` jadvali deyarli to'liq edi (title, slug, author, publisher,
publication_year, isbn, type, language, description, cover_path, source_url, file_path,
meta_*). Faza 10'da faqat **`status`** va **`featured`** qo'shildi (Qorboshi/Uzgolon bilan
bir xil naqsh) — fuzzy-date muammosi bu yerda yo'q edi, chunki `publication_year` allaqachon
yil-darajasida edi.

`LiteratureType` enum (Faza 3'dan, 9 ta tur: book/article/research/archive/newspaper/
journal/memoir/dissertation/other) — **o'zgartirilmadi**, spec so'ragan 4-5 turdan ancha
boyroq va aniqroq edi.

### 19.2 Relationships

`Qorboshi`/`Uzgolon` ↔ `Literature` — Faza 3'dagi `qorboshi_literature`/`uzgolon_literature`
pivotlar, o'zgarishsiz. Yangi: `Literature::blogs()` (`blog_literature` pivot),
`Literature::videos()` (`hasMany`, Video'ning `literature_id` FK tomonidan).

**`sourceReferences()` — MUHIM QARORNI ARALASHTIRMASLIK:** `Literature::sourceReferences()`
Faza 3'dan beri `hasMany(SourceReference::class)` — bu **"bu adabiyot BOSHQA yozuvlar uchun
manba"** ma'nosida (`source_references.literature_id` FK). Bu Qorboshi/Uzgolon/Video/Blog'da
ishlatilgan `sourceables` polymorphic (`morphToMany`) bilan **bir xil emas** — u yerda "bu
yozuv QAYSI manbalarga asoslangan" ma'nosida. Ikkalasi ham to'g'ri, ikki xil yo'nalish,
Literature modeliga qo'shimcha `sourceables` munosabati **qo'shilmadi** (mantiqiy jihatdan
chalkashtirib yuborardi).

### 19.3 Media

`LiteratureMediaService` — cover (4MB) + PDF fayl (20MB, faqat `.pdf`). Mualliflik huquqi
tekshiruvi **texnik emas, tashkiliy qaror** — admin javobgarligi (kod avtomatik hech narsani
tashqi manbadan yuklab olmaydi yoki nusxalamaydi).

### 19.4 CRUD va public sahifalar

`admin.adabiyotlar.*` (resourceful), `/adabiyotlar`, `/adabiyotlar/{slug}` — Qorboshi/Uzgolon
bilan bir xil qatlam (Service → MediaService → Controller → View). "O'qish" tugmasi faqat
`file_path` yoki `source_url` mavjud bo'lsagina ko'rsatiladi (fake havola yo'q).

---

## 20. Video content architecture (Faza 10)

### 20.1 Schema

Qo'shildi: `youtube_id` (extractsiya qilingan, nullable), `status`, `featured`,
`literature_id` (nullable FK — Video↔Literature yangi bog'lanish). `thumbnail_path`
(Faza 3'dan) — endi **ixtiyoriy override**, standart holatda YouTube thumbnail avtomatik
ishlatiladi (`Video::thumbnailUrl()`).

### 20.2 YouTube xavfsizligi — MUHIM

`App\Support\YoutubeUrl` — yagona joyda YouTube URL bilan ishlash:
- `extractId()` — regex orqali faqat 4 ta qo'llab-quvvatlanadigan formatdan
  (`watch?v=`, `youtu.be/`, `/shorts/`, `/embed/`) 11 belgili video ID ajratib oladi;
  boshqa domen/format uchun `null` qaytaradi.
- `embedUrl()` — **har doim** qattiq yozilgan `youtube-nocookie.com/embed/{id}` domenidan
  quriladi.
- **Foydalanuvchidan xom iframe HTML yoki arbitrary URL hech qachon qabul qilinmaydi va
  render qilinmaydi** — faqat validatsiyadan o'tgan 11-belgili ID orqali.
- `StoreVideoRequest`da `youtube_url` maydoni custom `Closure` validator bilan tekshiriladi:
  `YoutubeUrl::isValid()` false bo'lsa — validatsiya xatosi (yozuv yaratilmaydi).
- Test qilindi: `<iframe src="evil.com">`, `javascript:alert(1)` kabi zararli qiymatlar
  — ikkalasi ham `extractId()` orqali `null` qaytardi, ya'ni rad etiladi.

### 20.3 Relationships

`Qorboshi`/`Uzgolon` — Faza 3'dan, `belongsTo` (1:N, video bitta egaga tegishli).
Yangi: `Video::literature()` (`belongsTo`), `Video::blogs()` (`blog_video` pivot),
`Video::sourceReferences()` (`sourceables` polymorphic, Qorboshi bilan bir xil naqsh).

### 20.4 CRUD va public sahifalar

`admin.videolar.*`, `/videolar`, `/videolar/{slug}`. Detail sahifada `<iframe>` faqat
`Video::embedUrl()` orqali (hech qachon `youtube_url` xom holda emas).

---

## 21. Blog content architecture (Faza 10)

### 21.1 Egalik va lifecycle

`Blog belongsTo User` (`author_id`) — Faza 3'dan. Status enum (Faza 3'dan, **o'zgartirilmadi**):
`draft → pending → approved / rejected`. Spec "published" so'ragan joyda mavjud `approved`
qiymati aynan shu ma'noni bildiradi (`published_at` shu paytda to'ldiriladi) — nomlash farqi
bor, mantiq bir xil, shuning uchun enum qayta nomlanmadi (keraksiz churn).

**Ikki xil Form Request qatlami — xavfsizlik uchun muhim ajratish:**
- `Store/UpdateBlogRequest` — faqat **admin** panel uchun, `status` va `author_id`ni
  to'liq qabul qiladi.
- `Store/UpdateMyBlogRequest` — **"mening bloglarim"** uchun, `status` va `author_id`
  umuman qabul qilinmaydi/validatsiya qilinmaydi. `BlogService::createAsAuthor()` bu
  ikkalasini serverda majburan belgilaydi (har doim joriy user + Draft). Bu — oddiy
  foydalanuvchi o'zini boshqa muallif yoki to'g'ridan-to'g'ri "approved" status sifatida
  ko'rsata olmasligini kafolatlaydi (mass-assignment orqali privilege escalation yo'q).

### 21.2 Moderatsiya — route+policy ikki qatlamli himoya

`routes/admin.php`da `Route::resource('bloglar', ...)->middlewareFor(...)` orqali:
`index`/`show` — `role:admin,editor`; `create`/`store`/`edit`/`update`/`destroy` — faqat
`role:admin`. `approve`/`reject` — alohida `role:admin,editor` guruhida. `BlogPolicy`
mos ravishda yangilandi (`view()` endi Editor'ni ham qamrab oladi, `submit`/`approve`/
`reject` metodlari qo'shildi). Ikkala qatlam (route middleware + Policy) mustaqil
tekshiradi — biri "unut"ilsa ham ikkinchisi ushlab qoladi.

**Test qilindi:** editor `/admin/bloglar` (200, pending ko'rinadi) va approve/reject
qila oldi; lekin `/admin/bloglar/create` (403) va `/admin/qorboshilar` (403) — admin-only
qismlarga kira olmadi.

### 21.3 XSS/xavfsizlik

Blog matni **oddiy matn** sifatida saqlanadi va Blade'ning standart `{{ }}` (avtomatik
HTML-escape) orqali chiqariladi — `{!! !!}` (raw HTML) hech qanday joyda ishlatilmagan.
Rich-text editor yoki Markdown-renderer **qo'shilmadi** (spec: "yangi katta JS editor
qo'shma"). Test qilindi: `<script>alert(1)</script>` va `<img onerror=...>` saqlangan
blog sahifasida `&lt;script&gt;` ko'rinishida chiqdi — brauzerda ishga tushmadi.

### 21.4 Relationships

`Blog` ↔ `Qorboshi`/`Uzgolon` (Faza 3'dan, `blog_qorboshi`/`blog_uzgolon`). Yangi:
`Blog::literatures()` (`blog_literature`), `Blog::videos()` (`blog_video`),
`Blog::sourceReferences()` (`sourceables` polymorphic).

### 21.5 Routes

- Admin: `admin.bloglar.*` + `admin.bloglar.approve`/`reject`.
- Public: `bloglar.index`/`show` (faqat `approved`).
- User: `bloglarim.*` (`index`/`create`/`store`/`edit`/`update`/`submit`/`destroy`) —
  `auth` middleware ostida, `routes/web.php`da. Route parametri `{blogim}` (Form
  Request'lardagi `$this->route('blogim')` bilan mos).

---

## 22. Content Graph — yakuniy holat (Faza 10)

```
Qorboshi ──belongsToMany──> Uzgolon (qorboshi_uzgolon)
Qorboshi ──belongsToMany──> Literature (qorboshi_literature)
Qorboshi ──belongsToMany──> Blog (blog_qorboshi)
Qorboshi ──hasMany────────> Video (videolar.qorboshi_id)
Qorboshi ──morphToMany────> SourceReference (sourceables)

Uzgolon ──belongsToMany──> Literature (uzgolon_literature)
Uzgolon ──belongsToMany──> Blog (blog_uzgolon)
Uzgolon ──hasMany────────> Video (videolar.uzgolon_id)
Uzgolon ──morphToMany────> SourceReference (sourceables)

Literature ──belongsToMany──> Blog (blog_literature)
Literature ──hasMany────────> Video (videolar.literature_id)
Literature ──hasMany────────> SourceReference ("bu adabiyot manba sifatida")

Video ──belongsToMany──> Blog (blog_video)
Video ──morphToMany────> SourceReference (sourceables)

Blog ──belongsTo──> User (author_id)
Blog ──morphToMany──> SourceReference (sourceables)
```

**Hech biri frontendda hard-code qilinmagan** — barchasi Eloquent relationship orqali,
har bir public detail-sahifa controller/service qatlamida **faqat published bog'liq
yozuvlarni** eager-load qiladi (`'literatures' => fn($q) => $q->published()` naqshi
— draft/pending yozuvlarga public sahifada hech qachon havola chiqmaydi, aks holda 404
havola bo'lardi). Bu Faza 10'da barcha 5 ta service (`Qorboshi`, `Uzgolon`, `Literature`,
`Video`, `Blog`) uchun tizimli qo'llanildi.

Amalda ishlaydigan sayohat (spec §57'dagi Faza 8 talabiga o'xshash, endi kengaytirilgan):
Qo'rboshi → Qo'zg'olon → Adabiyot → Video → Blog → Manba — va **teskari yo'nalishda ham**
(Blog detail'dan Qorboshi/Uzgolon/Literature/Video'ga, Literature detail'dan Qorboshi/
Uzgolon/Video'ga va h.k.) — har ikki yo'nalish ham test qilindi.

---

## 23. Global Search (Faza 11)

### 23.1 Umumiy manba — `ContentType` enum

`Comment` (polymorphic whitelist) va `Search` (qidiruv qamrovi) ikkalasi ham bir xil
5 ta content turini bilishi kerak: Qorboshi/Uzgolon/Literature/Video/Blog. Shu sabab
`App\Enums\ContentType` yagona joyda: `value` (URL/DB alias), `label()`/`pluralLabel()`,
`model()` (FQCN), `routeName()` (public show route) va `fromModel()` (teskari lookup)
ni saqlaydi — ikkita alohida whitelist massividan qochildi (§55 "duplicate code
kamaytir").

### 23.2 Oqim

```
GET /qidiruv?q=...&type=...
   → SearchController (validatsiya: q max:150, type whitelist)
       → SearchService::search()
           → har turdan alohida Eloquent so'rov (published/approved-only)
       ← flat, aralash pagination (LengthAwarePaginator qo'lda tuzilgan)
   ← resources/views/search/index.blade.php
```

**Nega "flat merge", spec §5'dagi "guruhlangan" (Qo'rboshilar/Qo'zg'olonlar/...
sarlavhali) misol emas?** §12 (pagination) va §13 (type filter) aniq talab
qilingan, ammo guruh chegaralari bilan sahifa chegaralari tabiiy ravishda mos
kelmaydi (masalan 1-sahifada "Qo'rboshilar" guruhi tugab, 2-sahifada
davom etolmaydi). Spec §5'da "mumkin" (ixtiyoriy misol) so'zi ishlatilgan,
shuning uchun pragmatik yechim: bitta flat ro'yxat, har bir natijada turi
badge sifatida ko'rsatiladi — bu §11 formatiga (`type`/`title`/`slug`/`url`/
`excerpt`) to'liq mos.

**Pagination algoritmi:** har bir turdan mos LIKE-filtrlangan `COUNT()` (5 ta
query), so'ng talab qilingan sahifa oralig'iga mos keladigan turlardan aniq
`skip()/take()` bilan faqat kerakli qatorlar olinadi (5 tagacha qo'shimcha
query). Bu N ta natija bo'lsa ham query sonini o'zgartirmaydi (test: §23.4).

### 23.3 Qidiruv fieldlari (mavjud schema asosida, taxmin qilinmadi)

| Model | Ustunlar |
|---|---|
| Qorboshi | `full_name`, `short_description`, `biography` |
| Uzgolon | `name`, `short_description`, `historical_location`, `modern_location` |
| Literature | `title`, `author`, `description` |
| Video | `title`, `description` |
| Blog | `title`, `excerpt`, `content` |

Har biri **faqat** `published()` scope orqali (Blog uchun bu `approved`
qiymatiga mos keladi, §16'dagi qaror bilan bir xil).

### 23.4 Xavfsizlik va performance

- **SQL injection:** `whereRaw('LOWER(column) LIKE ?', [$needle])` — ustun nomi
  hech qachon foydalanuvchidan kelmaydi (fixed private massiv), qiymat har doim
  parametr sifatida bog'lanadi.
- **Case-insensitive:** `LOWER()` orqali — SQLite/MySQL/PostgreSQL uchun bir xil
  ishlaydi (MySQL/SQLite default collation'iga bog'liq bo'lib qolmaydi).
- **XSS:** `$query` Blade'da har doim `{{ }}` orqali chiqadi; `<title>` uchun
  (`@yield` — Blade'da escape qilinmaydi, butun saytda shunday) alohida
  `e($query)` bilan qo'lda escape qilingan (`SearchController::index()`).
- **Query uzunligi:** `max:150` validatsiya.
- **Invalid type:** `Rule::in(...)` — mavjud FormRequest'lar bilan bir xil
  convention (ValidationException → redirect back + session errors).
- **N+1:** natija massivi faqat skalyar ustunlardan tuzilgani uchun (hech qanday
  relation yuklanmaydi) query soni natija sonidan mustaqil — test bilan
  tasdiqlangan (`SearchTest::test_search_query_count_does_not_grow_with_result_size`).

---

## 24. Comments & Moderation (Faza 11)

### 24.1 Mavjud arxitektura (Faza 3) — nima saqlanib qoldi

`Comment` modeli, `comments` migratsiyasi va `CommentPolicy` Faza 3'da allaqachon
yaratilgan edi (`content`/`status`/`author_id`/`commentable_type`/`commentable_id`,
`CommentStatus` enum, `morphTo`/`morphMany`, har bir content modelida `comments()`).
Faza 11 buni **buzmadi** — ustiga qurdi:

- `Comment` modeliga qo'shildi: `scopeApproved()`/`scopePending()`, va admin panel
  uchun `commentableTitle()`/`commentableTypeLabel()`/`commentableUrl()` (Blade'da
  DB query qilmaslik uchun — bular faqat allaqachon eager-load qilingan
  `commentable` relationdan foydalanadi).
- `CommentPolicy`ga qo'shildi: `viewAny()`/`view()` (editor+admin, yoki egasi).
  `create()`/`update()`/`delete()`/`moderate()` **o'zgarishsiz** qoldi.
- Migratsiya: mavjud `[commentable_type, commentable_id]` indeksi
  `[commentable_type, commentable_id, status]`ga kengaytirildi (public sahifada
  "shu commentable uchun faqat approved" so'rovi doim uch ustunni ishlatadi).

### 24.2 Whitelist — `commentable_type` xavfsizligi

Foydalanuvchidan kelgan `commentable_type` (masalan `"qorboshi"`) hech qachon
to'g'ridan-to'g'ri PHP class sifatida ishlatilmaydi. `App\Enums\ContentType`
(§23.1) orqali qat'iy whitelist: `StoreCommentRequest::rules()` da
`Rule::in([...5 ta qiymat...])`, so'ng `CommentService::resolveCommentable()`
faqat shu enum orqali aniq model classga map qiladi. Morph map (`Relation::
morphMap()`) **ataylab qo'shilmadi** — mavjud `sourceables` polymorphic pivot
(Faza 8-10) allaqachon FQCN saqlaydi, global morph map buni buzardi; shuning
uchun `commentable_type` ustunida ham standart Eloquent xatti-harakati (FQCN)
saqlanadi, whitelist faqat PHP darajasida (`ContentType` enum) ta'minlanadi.

### 24.3 Published-parent validatsiyasi (§25)

`StoreCommentRequest::withValidator()` — `commentable_type`/`commentable_id`
o'zaro bog'liq tekshiruv (Laravel'ning oddiy `exists:table,column` qoidasi
dinamik jadval tanlay olmaydi): turga mos modelning `published()` scope'i
orqali borligini tekshiradi. Agar draft/pending/rejected bo'lsa —
`commentable_id`ga validatsiya xatosi (422, frontend'ga ishonilmaydi, har doim
serverda qayta tekshiriladi). `CommentService::resolveCommentable()` xuddi shu
`published()` filtrini controller darajasida ham qo'llaydi (defense-in-depth).

### 24.4 Oqim

```
Public:  POST /comments (auth, throttle:10,1)
   → StoreCommentRequest (whitelist + published-parent validatsiya)
       → CommentService::resolveCommentable() + create()
   ← redirect back + "Izohingiz moderatsiyaga yuborildi."

Admin:   GET /admin/comments (role:admin,editor)
   → Admin\CommentController → CommentService::paginateForAdmin()
       (filter: status, type, search — content/user name/email)
   ← admin.comments.index (approve/reject: admin+editor; delete: admin yoki
     comment egasi — CommentPolicy::delete())
```

### 24.5 Public UI — `<x-comments>` component

`resources/views/components/comments.blade.php` — `:comments` (paginated,
`comments_page` query param, §37) va `:commentable` qabul qiladi. Har 5 ta
detail sahifada (`qorboshilar/uzgolonlar/adabiyotlar/videolar/bloglar show.blade.php`)
avvalgi "Izohlar tizimi keyingi bosqichda ishga tushiriladi" placeholder'i
almashtirildi. Guest uchun login havolasi, auth user uchun forma
(`commentable_type`/`commentable_id` hidden fieldlar orqali — `ContentType::
fromModel()` bilan avtomatik aniqlanadi).

### 24.6 Xavfsizlik

- **XSS:** comment matni `{{ $comment->content }}` orqali (hech qachon
  `{!! !!}`) — test: `CommentTest::test_comment_content_is_escaped_on_render`.
- **CSRF:** barcha formalar `@csrf`.
- **IDOR:** `CommentController::destroy()` / `Admin\CommentController::destroy()`
  har doim `authorize('delete', $comment)` — boshqa userning commentini
  o'chirish 403 (test: `test_user_cannot_delete_another_users_comment`).
- **Rate limit:** `throttle:10,1` (10/daqiqa) — `POST /comments` route'ida.
- **Mass assignment:** `StoreCommentRequest` faqat `commentable_type`/
  `commentable_id`/`content` qabul qiladi; `status`/`author_id` har doim
  serverda (`CommentService::create()`) belgilanadi.
- **N+1:** `CommentService::approvedFor()` va `paginateForAdmin()` ikkalasi
  ham `with('author')` (admin qo'shimcha `with('commentable')`) — test:
  `test_approved_comments_query_does_not_grow_with_comment_count`.

---

## 25. HistoricalRegion (Faza 12)

### 25.1 Mavjud arxitektura — nima saqlanib qoldi

`HistoricalRegion` modeli va migratsiyasi Faza 3'da allaqachon yaratilgan edi:
`name`, `slug` (unique), `historical_name`/`modern_name` (nullable), `description`,
`geojson` (json), `period_id`/`region_id` (ikkalasi ham nullable FK). Faza 12 buni
**buzmadi** — ustiga qurdi. Field nomlari o'zgartirilmadi (spec "description" kabi
generic nom so'ragan joylarda ham mavjud aniqroq nomlar — `historical_name`/
`modern_name` — saqlandi).

### 25.2 Qo'shilgan fieldlar

Migratsiya `update_historical_regions_table_for_content_architecture`:

| Field | Sabab |
|---|---|
| `status` (`draft`/`published`, default `draft`) | Qorboshi/Uzgolon/Literature/Video bilan bir xil naqsh — public'da faqat `published` ko'rinadi |
| `featured` (boolean, default `false`) | Kelajakda homepage/xarita highlight uchun |
| `sort_order` (integer, default `0`) | Xaritada/ro'yxatda ko'rsatish tartibi (§14) |

`HistoricalRegionStatus` enum (`App\Enums`) — `Qorboshi`dagi bilan bir xil ikki
holatli (`draft`/`published`) naqsh, Blog'dagi 4-holatli moderatsiya workflow'i
bu yerda kerak emas (moderatsiya emas, admin-only kontent).

### 25.3 Relationships

```
HistoricalRegion belongsTo Period (nullable)
HistoricalRegion belongsTo Region (nullable — zamonaviy hudud mosligi)
HistoricalRegion hasMany HistoricalMapLayer (yangi, Faza 12)
```

### 25.4 CRUD arxitekturasi

Boshqa entitylar bilan bir xil qatlam: `Route::resource('historical-regions', ...)
->parameters(['historical-regions' => 'historicalRegion'])` (kebab-case URI, camelCase
route parameter — PHP o'zgaruvchi nomi sifatida valid bo'lishi uchun) →
`HistoricalRegionController` → `Store/UpdateHistoricalRegionRequest` →
`HistoricalRegionService` → View. To'liq admin-only (`HistoricalRegionPolicy` —
`VideoPolicy`/`MapMarkerPolicy` bilan bir xil naqsh, editor kirolmaydi).

**Slug stability:** Qorboshi'da (Faza 8) tuzatilgan bug qayta kiritilmadi — `update`da
bo'sh slug mavjud slug'ni saqlaydi, faqat admin qo'lda o'zgartirsa o'zgaradi.

### 25.5 O'chirish xatti-harakati (§43)

`historical_map_layers.historical_region_id` FK — `nullOnDelete()` (cascade EMAS).
HistoricalRegion o'chirilganda bog'liq HistoricalMapLayer'lar **o'chirilmaydi**,
faqat `historical_region_id` null bo'ladi (layer umumiy/regionsiz qatlamga aylanadi).
Bu ataylab ehtiyotkor tanlov — layer'ning o'zi (geojson, manba, davr) tarixiy
ma'lumot sifatida qimmatli, faqat bitta regionga bog'liqligi yo'qoladi.

---

## 26. HistoricalMapLayer (Faza 12)

### 26.1 Mavjud arxitektura — muhim kontseptual kengaytma

Faza 3'da `HistoricalMapLayer` **faqat georeferenced RASTER overlay** uchun
mo'ljallangan edi: `title`, `period_id`, `image_path` (**majburiy**), `opacity`,
`bounds` (`{north,south,east,west}` — MapLibre `ImageSource` formatiga mos),
`is_active`, `sort_order`. Faza 12 spec'i esa GeoJSON **vektor** qatlamlarni ham
talab qildi (§15, §21) — bu ikkalasi bir-biriga zid emas, shuning uchun mavjud
jadval **evolyutsion ravishda ikkalasini ham qo'llab-quvvatladigan** qilib
kengaytirildi:

| O'zgarish | Sabab |
|---|---|
| `image_path` majburiy → nullable | Vektor-only (faqat geojson) qatlam rasmga muhtoj emas |
| `slug` qo'shildi (unique) | Boshqa entitylar bilan izchillik — admin CRUD route slug bilan bog'lanadi |
| `description` qo'shildi (nullable) | Admin kontekst/izoh |
| `geojson` qo'shildi (nullable json) | Vektor geometriya (§21) |
| `historical_region_id` qo'shildi (nullable FK) | §19 — layer bitta tarixiy hududga **yoki** umumiy xarita qatlamiga tegishli bo'lishi mumkin |
| `status` qo'shildi (`draft`/`published`) | Public xaritada faqat `published` (§24) |

**`title` "name"ga qayta nomlanmadi** — Qorboshi'dagi `full_name` bilan bir xil
qaror: mavjud nom butun kodbazada ishlatilgani uchun keraksiz churn qilinmadi.

**`z_index` alohida ustun sifatida QO'SHILMADI** — mavjud `sort_order` (Faza 3'dan)
xuddi shu vazifani (qatlamlar tartibi/stacking order) bajaradi; ikkita alohida
integer-ordering ustuni dublikat kontseptsiya bo'lardi.

**`is_active` saqlanib qoldi, `status`ga almashtirilmadi** — ikkalasi turli
ma'noga ega: `is_active` = "bu davr uchun hozir ko'rsatilayotgan asosiy raster"
(xaritadagi bitta-vaqtda-bitta-faol UI holati), `status` = "bu yozuv publish
qilishga tayyormi" (moderatsiya/publish lifecycle). Bu xuddi Qorboshi/Uzgolon'da
`status` + `featured` ikkalasi bir vaqtda mavjud bo'lishi bilan bir xil naqsh.

### 26.2 Manba arxitekturasi — parallel tizim yaratilmadi (§20)

`HistoricalMapLayer::sourceReferences(): MorphToMany` — Faza 10'dagi `sourceables`
polymorphic pivot (`morphToMany(SourceReference::class, 'sourceable')`) **qayta
ishlatildi**, xuddi Qorboshi/Uzgolon/Video/Blog'dagi bir xil naqsh. Alohida
`source_title`/`source_url` ustunlari **qo'shilmadi** — bu parallel source tizimi
bo'lardi va Faza 10 arxitekturasini keraksiz dublikatsiya qilardi.

### 26.3 Relationships

```
HistoricalMapLayer belongsTo Period (nullable, Faza 3'dan)
HistoricalMapLayer belongsTo HistoricalRegion (nullable, yangi)
HistoricalMapLayer morphToMany SourceReference ('sourceable', yangi — mavjud pivot)
```

### 26.4 Georeference (raster) vs GeoJSON (vektor) — ikkala rejim bir jadvalda

Admin formada ikkalasi ham mavjud: ixtiyoriy rasm yuklash + `bounds` (4 ta alohida
number input: north/south/east/west — MapLibre `ImageSource.coordinates` formatiga
to'g'ridan-to'g'ri mos), **va** ixtiyoriy `geojson` textarea. Ikkalasi ham bo'sh
qoldirilishi mumkin (masalan faqat metadata/manba saqlash uchun yaratilgan yozuv).
Amalda MapLibre integratsiyasi (qaysi rejim qachon ishlatilishi) — Faza 13 ishi;
Faza 12 faqat ma'lumotlarni **saqlash va admin orqali boshqarish** arxitekturasini
tayyorlaydi (§37 "future-ready, lekin majburiy emas").

### 26.5 GeoJSON texnik validatsiyasi — `App\Rules\ValidGeoJson`

Yangi reusable `ValidationRule` class (`app/Rules/ValidGeoJson.php`), ikkala
`HistoricalRegion`/`HistoricalMapLayer` FormRequest'da bir xil qo'llaniladi:

1. Qiymat — valid JSON string (`json_decode` xatosiz).
2. Hajm cheklovi — ~100KB (DoS/malicious payload himoyasi).
3. Top-level `type` — faqat `Feature`/`FeatureCollection`/`Polygon`/`MultiPolygon`
   (spec §10 qamrovi).
4. `FeatureCollection` → `features` massiv, har biri valid `Feature`.
5. `Feature` → `geometry` maydoni mavjud (yoki `null`), mavjud bo'lsa valid geometry.
6. `Polygon`/`MultiPolygon`/geometry — `type` + `coordinates` (massiv) mavjud.

**Muhim: bu FAQAT texnik tekshiruv** (§11, §58) — geometriyaning tarixiy jihatdan
to'g'ri (haqiqiy chegara, sana, joy) ekanligini tasdiqlamaydi:

> Texnik xarita geometriyasi shunchaki valid GeoJSON bo'lgani uchun tarixiy
> haqiqat sifatida qabul qilinmasligi kerak. Tarixiy chegaralar, nomlar, sanalar
> va geografik da'volar — nashr etilishidan oldin hujjatlashtirilgan tarixiy
> manbalar asosida tekshirilishi shart.

Bu prinsip Faza 13'da (real tarixiy ma'lumot kiritilganda) amalda qo'llaniladi;
Faza 12'da faqat DEMO/TEST geometriya (bo'sh `FeatureCollection`) ishlatiladi.

### 26.6 GeoJSON preview xavfsizligi (§32-33)

Admin `show` sahifasida (`historical-regions/show.blade.php`,
`historical-map-layers/show.blade.php`) mavjud GeoJSON MapLibre orqali ko'rsatiladi:
`@js($model->geojson)` — Laravel'ning xavfsiz JSON-encode direktivasi (Uzgolon xarita
integratsiyasida — Faza 9 — allaqachon shu naqsh ishlatilgan). Yangi
`resources/js/map/geojson-preview.js` (`initGeoJsonPreview`) — **hech qanday popup
yoki feature.properties'dan HTML qurmaydi** (uzgolon-map.js'dagi popup naqshidan
ataylab farqli) — faqat fill/outline/circle layer chizadi. Bu properties ichidagi
har qanday satr (masalan `<script>...</script>`) hech qachon HTML sifatida
render qilinmasligini kafolatlaydi — test bilan tasdiqlangan
(`test_geojson_property_containing_script_tag_is_never_rendered_as_raw_html`).

### 26.7 Mavjud build xatosi tuzatildi (Faza 9'dan meros, Faza 12'da topildi)

`npm run build` ishga tushirilganda `resources/js/map/uzgolon-map.js`
(`import maplibregl from 'maplibre-gl'`) build xatosi berdi: o'rnatilgan
`maplibre-gl@6.7.0` endi default export bermaydi (faqat named exports:
`Map`, `NavigationControl`, `Popup`, `LngLatBounds`, ...). Bu Faza 9'dan beri
mavjud bo'lgan, hech qachon `npm run build` orqali tekshirilmagan xato edi (Faza
12'gacha faqat dev-server orqali sinovdan o'tgan bo'lishi mumkin). Tuzatildi:
default import named import'larga almashtirildi, xatti-harakat o'zgarishsiz.

### 26.8 CRUD arxitekturasi va authorization

`HistoricalMapLayerController` → `Store/UpdateHistoricalMapLayerRequest` →
`HistoricalMapLayerService` → View — boshqa entitylar bilan bir xil qatlam.
`HistoricalMapLayerPolicy` — to'liq admin-only (editor kirolmaydi, §40).

### 26.9 Database indexlar

Ikkala jadvalda ham FK ustunlari (`period_id`, `region_id`, `historical_region_id`)
Laravel'ning `foreignId()->constrained()` orqali avtomatik indekslangan. Qo'shimcha
`status` yoki `sort_order` uchun alohida index **qo'shilmadi** — bu ikkala jadval
ham kichik hajmli (o'nlab-yuzlab yozuv, million emas) bo'lishi kutiladi, keraksiz
index qo'shish §44 talabiga ("keraksiz indexlar yaratma") zid bo'lardi.

---

## 27. Real Historical Map (Faza 13)

### 27.1 ENG MUHIM PRINSIP — ushbu faza davomida qat'iy amal qilindi

> **Texnik jihatdan valid GeoJSON — tarixiy haqiqat sifatida qabul qilinmasligi
> kerak.** Tarixiy chegaralar, nomlar, sanalar va geografik da'volar nashr
> etilishidan oldin hujjatlashtirilgan tarixiy manbalar asosida tekshirilishi
> shart.

Bu faza davomida **hech qanday real tarixiy hudud, chegara yoki koordinata
o'ylab topilmadi**. Barcha test/demo yozuvlar `[DEMO]`/`[DEMO DATA]` prefiksi
bilan aniq belgilangan (`HistoricalRegionFactory`, `HistoricalMapLayerFactory`,
`MapMarkerFactory`). Natija: **texnik integratsiya to'liq bajarildi; real
tarixiy ma'lumot hali tasdiqlanishi kutilmoqda** (§68). Bu — muvaffaqiyatsizlik
emas, balki loyihaning asosiy talabiga (tarixiy aniqlik) rioya qilish.

### 27.2 Yangi metadata — HistoricalRegionType va HistoricalAccuracyStatus

Ikkita yangi enum (`app/Enums/`), additive migratsiya orqali qo'shildi
(0 qatorli jadvallarga, ma'lumot yo'qolmadi):

- **`HistoricalRegionType`** (`political`/`administrative`/`geographical`/
  `other`) — HistoricalRegion "nimani bildirishi"ni aniq belgilaydi (§6, §64-65:
  "Turkiston" atamasining turli davrlardagi siyosiy/ma'muriy/geografik ma'nolari
  bir xil chiziq sifatida chalkashtirilmasligi kerak).
- **`HistoricalAccuracyStatus`** (`verified`/`approximate`/`uncertain`,
  ikkala HistoricalRegion **va** HistoricalMapLayer'da) — §7: bu `status`
  (draft/published, publish holati) bilan **hech qachon aralashtirilmaydi**.
  Bitta yozuv `published` + `uncertain` bo'lishi to'liq mumkin va normal holat
  (masalan taxminiy chegara, ochiq belgilab, publish qilingan). Default —
  `uncertain` (§41 "no false precision": yangi geometriya hech qachon
  standart bo'yicha "tasdiqlangan" deb taxmin qilinmaydi, admin buni ongli
  ravishda tanlashi kerak).

### 27.3 Manba talabi — publish + real geometriya = manba shart (§9, §40)

`StoreHistoricalRegionRequest`/`StoreHistoricalMapLayerRequest` ichida
`withValidator()`: agar `status=published` **va** yozuvda haqiqiy geometriya
mavjud bo'lsa (bo'sh placeholder emas — pastga qarang), kamida bitta
`SourceReference` biriktirilgan bo'lishi shart, aks holda `source_reference_ids`
maydoniga validatsiya xatosi qaytadi. Tekshiruv:

- Update so'rovlarida ham to'g'ri ishlaydi — agar `geojson`/`source_reference_ids`
  so'rovda qayta yuborilmagan bo'lsa (forma faqat boshqa maydonlarni o'zgartirsa),
  mavjud DB qiymatlariga ham qaraladi (aks holda partial update orqali tekshiruvni
  aylanib o'tish mumkin bo'lardi).
- **"Bo'sh placeholder" haqiqiy geometriya hisoblanmaydi**: `{"type":
  "FeatureCollection","features":[]}` kabi bo'sh qiymat manba talab qilmaydi —
  `ValidGeoJson::hasRealGeometry()` static helper buni ajratadi (`features`/
  `coordinates`/`geometry` bo'sh bo'lsa `false`). Demo/test yozuvlar shuning
  uchun manbasiz ham yaratilishi mumkin.
- Manba arxitekturasi — Faza 10'dagi `sourceables` polymorphic pivot,
  **parallel tizim yaratilmadi** (§8). `HistoricalRegion::sourceReferences()`
  bu faza'da qo'shildi (Faza 12'da faqat HistoricalMapLayer'da bor edi).

### 27.4 GeoJSON validatsiyasi kengaytirildi

`App\Rules\ValidGeoJson` (Faza 12'da yaratilgan) ga qo'shildi:

- **Koordinata diapazoni** (§34-35): WGS84/EPSG:4326 — longitude `-180..180`,
  latitude `-90..90`. Rekursiv `coordinatesInRange()` Point/LineString/Polygon/
  MultiPolygon'ning istalgan ichki chuqurligidagi `[lon, lat]` juftlarini
  tekshiradi. Boshqa CRS talab qilinsa — foydalanuvchi view'da ko'rsatilishidan
  oldin, kutubxonasiz, kod darajasida transformatsiya qo'shilishi kerak (hozircha
  qo'shilmadi — barcha kirish WGS84 deb talab qilinadi).
- **Hajm chegarasi 100KB → 5MB** (§12): batafsil tarixiy Polygon/MultiPolygon
  (ko'p vertex) osongina yuz KB dan oshishi mumkin edi. Baribir cheksiz emas —
  DoS/xato-fayl himoyasi sifatida yuqori chegara saqlanadi.
- `hasRealGeometry()` static helper — §27.3'da tasvirlangan.

### 27.5 Public integratsiya — `HistoricalMapService`

Yangi `app/Services/HistoricalMapService.php` — barcha query/formatlash logikasi
shu yerda, Controller faqat chaqiradi (§45):

```
MapController::index()
    → UzgolonService::publicGeoJson()        (Faza 9, o'zgarishsiz)
    → HistoricalMapService::regionsGeoJson()  (yangi)
    → HistoricalMapService::layersGeoJson()   (yangi)
    → HistoricalMapService::rasterLayersData() (yangi)
    ← pages/map.blade.php → initTurkestanMap()
```

**Nega alohida `/xarita/tarixiy-qatlamlar` JSON endpoint yaratilmadi (§14,
§46)?** Mavjud arxitektura allaqachon har bir sahifa yuklanishida faqat
tanlangan `period`/`region` filtriga mos ma'lumotni server-side hisoblab
yuboradi (to'liq GET-reload, Faza 9'dan beri ishlaydigan naqsh) — bu allaqachon
"faqat kerakli davrni yukla, hammasini birdan emas" talabini (§53)
qanoatlantiradi, qo'shimcha AJAX endpoint keraksiz murakkablik bo'lardi (§46
"haddan tashqari kengaytirma").

**Format:** har bir HistoricalRegion/HistoricalMapLayer yozuvining `geojson`
ustuni (o'zi Feature/FeatureCollection/Polygon/MultiPolygon bo'lishi mumkin)
`toFeatures()` orqali bitta yozuvning barcha geometriyalariga mos MapLibre
Feature'larga aylantiriladi, har biriga model xususiyatlari (`name`,
`historicalName`, `period`, `accuracyStatus`, `sourceSummary`, ...)
properties sifatida biriktiriladi — natijada bitta flat `FeatureCollection`.

**Xavfsiz manba havolasi (§25):** `firstSafeSourceUrl()` faqat `http(s)://`
sxemali URL'larni qaytaradi — `javascript:` yoki boshqa sxema hech qachon
public JSON'ga chiqmaydi (test: `test_unsafe_source_url_scheme_is_never_
exposed_publicly`). Bu server-side filtr — frontend qo'shimcha ravishda
`isSafeUrl()` bilan yana tekshiradi (defense-in-depth, §33).

### 27.6 MapLibre integratsiyasi — `resources/js/map/turkestan-map.js`

Mavjud `uzgolon-map.js` (Faza 9, boshqa 2 sahifada — homepage preview, qozgolon
detail — ishlatiladi) **o'zgartirilmadi**, yangi `initTurkestanMap()` funksiyasi
alohida faylda yaratildi va faqat to'liq `/xarita` sahifasida ishlatiladi
(§13, §54):

- **Qatlamlar tartibi** (§21-22, Faza 12 qarori saqlandi — alohida `z_index`
  yo'q): tarixiy hududlar (fill+outline) → tarixiy chegaralar (line, standart
  o'chiq) → raster overlaylar → qo'zg'olon markerlari (eng ustida, hech qachon
  tarixiy qatlam ostida yo'qolib qolmaydi, §26).
- **Data-driven style, hardcoded rang yo'q** (§22): barcha tarixiy geometriya
  bitta brand rangida (`#A6791E`/`#6B4A32`); "verified" bo'lmagan
  (`approximate`/`uncertain`) geometriya MapLibre `case` ifodasi orqali **uzuq
  chiziq** bilan chiziladi — bu texnik ravishda "taxminiy" ekanini ko'rsatadi,
  soxta aniqlik yaratmaydi (§23, §41).
- **XSS-safe popup — DOM-based, string concatenation EMAS** (§24, §50): Faza
  9'dagi `uzgolon-map.js` (escapeHtml() bilan xavfsiz, lekin string-based)dan
  farqli ravishda, `buildPopupElement()` faqat `document.createElement`/
  `textContent`/`createTextNode` ishlatadi — feature.properties'dagi hech qanday
  qiymat hech qachon HTML sifatida parse qilinmaydi (in'ektsiya imkonsiz,
  escaping ham kerak emas — DOM API tabiiy ravishda xavfsiz).
- **Layer switcher** (§16): `setLayerVisibility(layerIds, visible)` — Alpine
  checkbox'lar shu orqali MapLibre `setLayoutProperty` chaqiradi, server so'rovi
  yo'q. Layer ro'yxati database'dan keladi (`hasRasterLayers` — raster mavjud
  bo'lsagina checkbox ko'rinadi).
- **Raster/vektor moslik saqlandi** (§36-37): Faza 3'dan beri mavjud
  `image_path`+`bounds` arxitekturasi buzilmadi — `ImageSource` sifatida
  qo'shiladi, agar `bounds`+`image_path` mavjud bo'lsa. Hozircha real raster
  yuklanmagani uchun bu yo'l amalda bo'sh massiv bilan ishlaydi (struktura
  tayyor, ma'lumot yo'q).
- **Xato himoyasi** (§59): `map.on('error', ...)` — bitta manba/tile xatosi
  butun xaritani yiqitmaydi, faqat console'ga log qiladi; `onError` callback
  orqali UI'da "Tarixiy xarita qatlamlarini yuklashda xatolik yuz berdi."
  xabari ko'rsatiladi.

### 27.7 Base map — hal qilindi (§35'ga qarang)

`demotiles.maplibre.org` (Faza 9'dan) keyinchalik OpenFreeMap'ning `positron`
uslubiga almashtirildi — batafsil qaror va sabab §35'da qayd etilgan.

### 27.8 Public UX

`resources/views/pages/map.blade.php`: mavjud region/period GET-filtri
o'zgarishsiz qoldi (§18-19, Period semantikasi buzilmadi — bir `period_id`,
M:N qo'shilmadi, chunki mavjud ma'lumotlar buni talab qilmaydi). Qo'shildi:

- Loading/error/empty state'lar (§31, §59).
- Collapsible "Xarita qatlamlari va legenda" paneli — desktop'da doim ochiq,
  mobil'da accordion (`aria-expanded`, `aria-controls` bilan, §58).
- Legenda — data-driven belgilar (fill = hudud, uzuq chiziq = taxminiy chegara,
  nuqta = qo'zg'olon), ranglar hardcoded emas, mavjud dizayn tokenlaridan (§28,
  §56).

---

## 28. Historical Accuracy & Provenance (Faza 13)

### 28.1 Ikki mustaqil o'lchov — publish holati va tarixiy ishonchlilik

```
status (draft | published)              — kontent PUBLICGA CHIQARILGANMI?
accuracy_status (verified | approximate | uncertain) — geometriya QANCHALIK ISHONCHLI?
```

Bu ikkalasi **mustaqil** — barcha 4 kombinatsiya mantiqan to'g'ri:

| status | accuracy_status | Ma'no |
|---|---|---|
| draft | (istalgan) | Admin hali ishlayapti, publicga chiqmagan |
| published | verified | Manba asosida tasdiqlangan, ishonchli |
| published | approximate | Ochiq "taxminiy" deb belgilab publish qilingan |
| published | uncertain | Kamdan-kam holat — odatda publish qilishdan oldin aniqlanishi kerak, lekin texnik jihatdan taqiqlanmagan (masalan "mavjudligi ma'lum, chegarasi noaniq" degan holat ham tarixiy jihatdan qimmatli bo'lishi mumkin) |

### 28.2 Manba (Provenance) — bitta arxitektura, uch joyda qayta ishlatiladi

```
SourceReference (author, title, publisher, year, url, page, note, source_type)
        │
        └─ sourceables (polymorphic pivot, Faza 8)
                ├─ Qorboshi, Uzgolon, Video, Blog (Faza 8-10)
                └─ HistoricalRegion, HistoricalMapLayer (Faza 12-13)
```

Parallel source tizimi (alohida `source_title`/`source_url` ustunlari) hech
qachon qo'shilmadi (§8, §20 — Faza 12 va 13'da ikkalasida ham qat'iy saqlangan
qaror). Admin formada mavjud manbalar multi-select orqali tanlanadi; kamida
bitta manba — real geometriyani publish qilish sharti (§27.3).

### 28.3 Tarixiy nom vs zamonaviy nom — aralashtirilmaydi (§10, §42)

`historical_name` (o'sha davrda ishlatilgan nom — masalan manbada "Туркестан"
yoki "Turkestan" deb yozilgan bo'lsa, o'sha holicha) va `modern_name`
(zamonaviy/hozirgi atama) alohida ustunlar sifatida saqlanadi (Faza 3'dan
beri mavjud, Faza 13'da semantikasi hujjatlashtirildi). Admin bittasini
ikkinchisi bilan almashtirib yozmasligi kerak — bu UI yordam matnida
(`form.blade.php`) eslatib o'tilgan.

### 28.4 "No false precision" — amaliy qo'llanilishi

§41 prinsipi kodda uch joyda aniq ko'rinadi:

1. `accuracy_status` default qiymati — har doim `uncertain`, hech qachon
   `verified` emas (admin ongli ravishda o'zgartirishi kerak).
2. Public xaritada "verified" bo'lmagan geometriya **uzuq chiziq** bilan
   chiziladi (§27.6) — vizual ravishda "bu aniq chegara emas" signali.
3. Legenda matni aniq ogohlantiradi: "texnik xarita ko'rinishi tarixiy haqiqat
   sifatida qabul qilinmasligi kerak".

### 28.5 Testlar

`HistoricalRegionTest`/`HistoricalMapLayerTest`/`HistoricalMapPublicTest` —
jami 24+19+13 test, shu jumladan: koordinata diapazoni, manba-talab qilinishi
(bor/yo'q/bo'sh-placeholder), accuracy_status status'dan mustaqinligini
tasdiqlovchi test, public visibility (published/draft/geojson-yo'q holatlar),
davr filtri, XSS (region nomi va manba URL'i orqali), IDOR (admin route'lari),
N+1 (5→25 yozuv bilan query soni o'zgarmasligi).

---

## 29. Historical Timeline (Faza 14)

### 29.1 Mavjud arxitektura — nima saqlanib qoldi, nima evolyutsiya qildi

`TimelineEvent` modeli/migratsiyasi Faza 3'dan beri mavjud edi, lekin faqat
metadata-daraja edi: `title`, `date_from`/`date_to` (`date`, soxta kun/oy
aniqligini majburlagan — Qorboshi/Uzgolon'da Faza 8-9'da tuzatilgan bir xil
muammo), `year` (NOT NULL), `description`, `image_path`, `qorboshi_id`/
`uzgolon_id`/`region_id` (nullable FK, Faza 3'dan), `sort_order`. **Hech qanday
`status` ustuni yo'q edi** — `HomeService::timelinePreview()` shu tufayli
**barcha (draft ham) eventlarni** bosh sahifada ko'rsatib turgan edi (topilgan
va tuzatilgan bug, §29.6).

Qo'shildi (additive migratsiya, 0 qatorli jadvalga — ma'lumot yo'qolmadi):

| O'zgarish | Sabab |
|---|---|
| `date_from`/`date_to` o'chirildi | Soxta kun/oy aniqligi — §4 aniq taqiqlagan |
| `year` → `start_year`ga nomlandi | Uzgolon'dagi bir xil pattern (Faza 9) |
| `end_year` (nullable) | Voqea davri (masalan 1918–1924) |
| `event_date` (nullable `date`) | **Faqat** aniq sana haqiqatan ma'lum bo'lganda — ixtiyoriy, standart holatda bo'sh |
| `slug` (unique) | Public detail sahifa uchun (`getRouteKeyName()`) |
| `status` (`draft`/`published`) | Boshqa admin-only content turlari bilan bir xil ikki holatli naqsh (§29.2) |
| `accuracy_status` | Faza 13'dagi `HistoricalAccuracyStatus` qayta ishlatildi |
| `featured`, `period_id`, `historical_region_id`, `latitude`/`longitude` | §4, §12 talablari |

### 29.2 Status enum — nega 2 holatli, Blog'dagi 4 holatli emas

`TimelineEventStatus` (`draft`/`published`) — Qorboshi/Uzgolon/Literature/Video/
HistoricalRegion/HistoricalMapLayer bilan bir xil. Blog'ning 4 holatli
(`draft`/`pending`/`approved`/`rejected`) moderatsiya workflow'i **qo'llanilmadi**
— bu workflow oddiy foydalanuvchi submit qilish oqimi uchun mo'ljallangan, lekin
TimelineEvent faqat admin tomonidan yaratiladi (boshqa admin-only content
turlari kabi).

### 29.3 Relationships

```
TimelineEvent belongsTo Period, Qorboshi, Uzgolon, Region, HistoricalRegion (barchasi nullable)
TimelineEvent morphToMany SourceReference ('sourceable', Faza 10 pivot qayta ishlatildi)
TimelineEvent morphMany Comment ('commentable')
```

Yangi M:N relationship **yaratilmadi** — barcha bog'lanishlar mavjud nullable
`belongsTo` orqali (§8 "keraksiz M:N yaratma" talabiga mos).

### 29.4 Admin CRUD

`Route::resource('timeline-events', ...)->parameters([...=> 'timelineEvent'])`
— boshqa entitylar bilan bir xil qatlam (`TimelineEventController` →
`Store/UpdateTimelineEventRequest` → `TimelineEventService` → View).
`TimelineEventPolicy` — to'liq admin-only (`VideoPolicy`/`HistoricalRegionPolicy`
bilan bir xil naqsh, editor kirolmaydi).

**Validatsiya:** `start_year` required (1000..joriy yil), `end_year` `gte:
start_year`, `latitude`/`longitude` WGS84 diapazon + `required_with` (biri
bo'lsa ikkalasi ham bo'lishi shart), barcha relation ID'lar `exists:` qoidasi
bilan tekshiriladi.

### 29.5 Manba talabi — Faza 13 qoidasi qayta qo'llanildi

`StoreTimelineEventRequest`da hozircha **qat'iy** "publish + real geometriya =
manba shart" tekshiruvi **qo'shilmadi** — TimelineEvent'ning "asosiy da'vosi"
matn (title/description/year), koordinata esa ixtiyoriy qo'shimcha. Manba
maydoni admin formada mavjud (`sourceables` pivot, boshqa entitylar bilan bir
xil), lekin bloklovchi validatsiya faqat HistoricalRegion/HistoricalMapLayer'da
qoldi (ular uchun geometriyaning o'zi asosiy mahsulot). Bu ataylab tanlangan
farq — TimelineEvent uchun ham xuddi shunday qat'iy qoida qo'yish talab
qilinmagan (§27 "kerak bo'lishi mumkin" — soft, majburiy emas).

### 29.6 Topilgan va tuzatilgan bug

`HomeService::timelinePreview()` (Faza 3'dan) `status` ustuni mavjud
bo'lmagani sababli **hech qanday published() filtri qo'llamasdi** — natijada
`status` qo'shilgandan keyin bu chindan ham bug bo'lib qolardi (draft eventlar
bosh sahifada oshkor bo'lardi). Faza 14'da `->published()` qo'shildi va
`orderBy('year')` → `orderBy('start_year')`ga yangilandi. Test:
`test_draft_event_is_excluded_from_homepage_preview`.

---

## 30. Timeline–Map Synchronization (Faza 14)

### 30.1 Markazlashtirilgan format — `HistoricalMapService`

Faza 13'da o'rnatilgan "controller faqat chaqiradi, formatlash bitta joyda"
tamoyili davom ettirildi: `HistoricalMapService` endi `TimelineEventService`ni
constructor orqali oladi va `timelineEventsGeoJson(?int $periodId)` metodini
taqdim etadi — `TimelineEventService::getPublishedWithCoordinates()` (faqat
published + lat/lng mavjud eventlar, bog'liq Qorboshi/Uzgolon `published()`
scope bilan eager-load qilingan) natijasini Point Feature'larga aylantiradi.

**Period logikasi bitta joyda:** `MapController` va `TimelineEventController`
ikkalasi ham bir xil `?period=` query parametrini bir xil `TimelineEventService`
metodlariga uzatadi — filtr mantig'i ikki marta yozilmagan (§14 talabi).

### 30.2 Xarita qatlam tartibi (§13)

```
1. modern base map (MapLibre style)
2. historical-regions-fill / historical-regions-outline
3. historical-layers-line (standart o'chiq)
4. raster-* (agar mavjud bo'lsa)
5. timeline-events-points   ← Faza 14, YANGI
6. uprising-markers-points  ← eng ustida (Faza 9, o'zgarishsiz)
```

**Chalkashmaslik uchun UX yechimi (§13):** xronologiya nuqtalari qo'zg'olon
markerlaridan ATAYLAB farqli uslubda — kichikroq radius (6 vs 8), to'q
ink-rangli to'ldirish + oltin kontur (qo'zg'olon esa to'liq oltin rangli,
paper-rangli kontur). Ikkalasi bir xil hududda bo'lsa ham vizual jihatdan
aniq ajraladi.

### 30.3 Timeline → Map fokus (§12, §17)

```
Timeline detail sahifa
   → "To'liq xaritada ko'rish" tugmasi
       → GET /xarita?period={period_id}&event={event_slug}
           → MapController: $focusEventSlug = request('event')
               → pages/map.blade.php: initTurkestanMap(..., { focusEventSlug })
                   → turkestan-map.js: agar focusEventSlug mos feature topsa,
                     map.flyTo(coordinates) + popup avtomatik ochiladi
                     (umumiy fitBounds() shu holatda ATLAB o'tkazib yuboriladi —
                     aks holda flyTo natijasini darhol bekor qilardi)
```

**Event card interaction (§17):** butun card bosilganda → detail sahifa
(oddiy `<a>` link, hech qanday JS kerak emas). Xaritaga o'tish alohida,
aniq "To'liq xaritada ko'rish" tugmasi orqali — foydalanuvchi tasodifan
detaildan chiqarilib yuborilmaydi.

### 30.4 XSS-safe popup — DOM-based, string concatenation emas

Faza 13'da o'rnatilgan `buildPopupElement()` (faqat `createElement`/
`textContent`) qayta ishlatildi — xronologiya popup'i ham feature.properties
qiymatlarini hech qachon HTML sifatida qurmaydi. Test:
`test_map_never_leaks_script_tags_from_event_title`.

### 30.5 Koordinata aniqligi va "no false precision"

`latitude`/`longitude` ixtiyoriy — event faqat matn/yil sifatida ham to'liq
qimmatli bo'lishi mumkin (barcha tarixiy voqealar aniq geografik nuqtaga ega
bo'lavermaydi). Agar biri kiritilsa, ikkalasi ham talab qilinadi
(`required_with`) — yarim-to'liq koordinata saqlanmaydi. `accuracy_status`
xuddi HistoricalRegion/HistoricalMapLayer'dagidek — koordinataning o'zi mavjud
bo'lishi uning "tasdiqlangan" ekanini anglatmaydi.

### 30.6 Public visibility va unpublished relation leakage oldini olish

`TimelineEventService::findPublishedBySlugOrFail()` va `paginateForPublic()`
ikkalasi ham `'qorboshi' => fn($q) => $q->published()`, `'uzgolon' => fn($q)
=> $q->published()` naqshidan foydalanadi (Faza 10'da o'rnatilgan qoida) —
agar bog'liq Qorboshi/Uzgolon hali draft bo'lsa, eager-load natijasi `null`
bo'lib qoladi, public sahifada hech qachon draft entityga havola chiqmaydi.
Test: `test_unpublished_related_qorboshi_is_not_leaked_on_published_event`,
`test_unpublished_related_uzgolon_is_not_leaked_on_published_event`.

### 30.7 Search va Comments integratsiyasi

`ContentType` enumga `TimelineEvent` case qo'shildi (§20-21) — bu ikkala
arxitektura (`SearchService`, `StoreCommentRequest`/`CommentPolicy`) allaqachon
generic (whitelist-based) bo'lgani uchun **qo'shimcha kod deyarli kerak
bo'lmadi**: faqat `SearchService`ning ikkita exhaustive `match` ifodasiga
(`searchableColumns`, `excerptOf`) yangi case qo'shildi. Comments — hech qanday
o'zgarishsiz avtomatik ishladi (`Comment::commentableTitle()`dagi `default`
fallback allaqachon `title` maydonini qamrab olgan). Bu Faza 11'dagi
arxitektura qarorining (bitta umumiy `ContentType` manbasi) to'g'riligini
tasdiqladi — yangi content turi qo'shish minimal, xavfsiz o'zgarish bo'ldi.

### 30.8 Performance

`HistoricalMapService::timelineEventsGeoJson()` va
`TimelineEventService::paginateForPublic()`/`findPublishedBySlugOrFail()`
barchasi eager-loading bilan (`period`, `qorboshi`, `uzgolon`) — query soni
5→25→(test qilinganidek) doimiy qoladi. Test:
`test_map_query_count_does_not_grow_with_timeline_event_count`,
`test_public_index_query_count_does_not_grow_with_event_count`.

### 30.9 Keyingi bosqichga qoldirilgan (§29, ataylab implement qilinmadi)

Continuous animated time slider, avtomatik tarixiy chegara reconstruction,
AI-generated boundaries, GIS editing interface, avtomatik georeferencing,
real tarixiy chegara digitizatsiyasi, murakkab animatsiya framework'i — bu
Phase 14 doirasidan tashqarida qoldirildi, kelajakdagi bosqichlar uchun.

---

## 31. Production Readiness (Final Technical Completion)

Bu bosqich **hech qanday yangi content feature yaratmadi** — maqsad
mavjud arxitekturani audit qilib, production uchun mustahkamlash edi.
Batafsil deployment qadamlari — `DEPLOYMENT.md`.

### 31.1 Audit paytida topilgan va tuzatilgan real buglar

Bu ro'yxat — "nazariy risklar" emas, balki testlar bilan tasdiqlangan,
haqiqatan mavjud bo'lgan muammolar:

1. **`/boglanish` — 404 dead link, Faza 7'dan beri.** Navbar va footer
   "Bog'lanish"ga havola berardi, lekin hech qachon route/controller/view
   yaratilmagan edi. `ContactController` + statik sahifa qo'shildi (soxta
   kontakt ma'lumoti — telefon/email — o'ylab topilmadi, chunki bu haqiqiy
   biznes ma'lumoti; buning o'rniga mavjud izohlar tizimiga yo'naltiradi).
2. **`APP_LOCALE=en` bo'lgani holda butun sayt o'zbekcha edi.** Custom
   `messages()` bilan qoplanmagan har qanday standart Laravel validatsiya
   qoidasi (masalan oddiy `required`) inglizcha xabar chiqarardi ("The
   title field is required."). `lang/uz/validation.php` yaratildi (to'liq
   qoidalar + odatiy maydon nomlari tarjimasi), `APP_LOCALE=uz`ga
   o'zgartirildi. Bu producton-quality bilinguallikni yechdi.
3. **JSON-LD'dagi `'@context'` Blade'ning `@context` direktivasi (Laravel
   Context feature) bilan to'qnashib, butun structured data blokini buzib
   yuborgan edi** — `@type`gacha yetib bormasdan, `'@context'` matni
   compile vaqtida boshqa PHP kodga almashtirilib ketardi. `@@context`
   escape sintaksisi bilan tuzatildi (3 fayl: layout, breadcrumb-jsonld,
   article-jsonld). Regression test: `StructuredDataTest`.
4. **Ro'yxatdan o'tishda (`/royxatdan-otish`) rate limiting umuman yo'q
   edi** — login'da FormRequest ichida qo'lda `RateLimiter` bor edi, lekin
   register'da yo'q, bu ommaviy spam-akkaunt yaratishga yo'l ochardi.
   `throttle:6,1` middleware qo'shildi.
5. **`UserFactory` boshqa barcha factory'lardan farqli o'laroq o'zining
   enum-based holat maydonini (`role`) hech qachon aniq belgilamasdi**,
   faqat DB ustun default'iga tayanardi — natijada `User::factory()->
   create()` orqali olingan in-memory model `->role` `null` bo'lib qolardi
   (`actingAs()` bilan test qilinganda `profile/edit.blade.php`dagi
   `$profileUser->role->label()` fatal xato berardi). Ikkala tomondan
   tuzatildi: `UserFactory`ga `'role' => Role::User` qo'shildi (boshqa
   factory'lar bilan izchillik) **va** Blade'da `?->`/`?? '—'` bilan
   himoyalandi (defensive coding — kelajakda boshqa sabab bilan `role`
   `null` bo'lib qolsa ham sahifa yiqilmaydi).
6. **`HistoricalImageFactory` bo'sh stub edi** — to'ldirildi (boshqa barcha
   factory'lar bilan bir xil holatga keltirildi).

### 31.2 Xavfsizlik qattiqlashtirish (Security Hardening)

- **`SecurityHeaders` middleware** (yangi, global) — `X-Content-Type-
  Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy:
  strict-origin-when-cross-origin` har bir javobga qo'shiladi. **Content-
  Security-Policy ataylab qo'shilmadi** — Alpine.js va MapLibre GL bilan
  ishlaydigan qat'iy CSP alohida, ehtiyotkor sinovni talab qiladi
  (noto'g'ri CSP mavjud xaritani/interaktivlikni buzishi mumkin edi);
  bu keyingi, alohida bosqich sifatida qoldirildi.
- **Fayl yuklash:** barcha rasm maydonlari `mimes:jpg,jpeg,png,webp`
  bilan qat'iy cheklangan (hech qachon `svg` yoki umumiy `image`
  wildcard emas — SVG-orqali-XSS imkonsiz). Barcha yuklash `->store()`
  orqali (hech qachon `storeAs()` foydalanuvchi nomi bilan emas) —
  fayl nomi doim tasodifiy generatsiya qilinadi.
- **Mass assignment:** har bir model `$fillable` orqali himoyalangan
  (audit — `$guarded`/`$fillable`siz model topilmadi). Har bir Form
  Request faqat kutilgan maydonlarni qabul qiladi.
- **Rate limiting:** login (FormRequest, 5 urinish/email+IP), registratsiya
  (yangi, `throttle:6,1`), comment yaratish (`throttle:10,1`, Faza 11).
- **URL xavfsizligi:** YouTube (`YoutubeUrl` — faqat validated 11-belgili
  ID), manba havolalari (`HistoricalMapService::isSafeUrl()` — faqat
  `http(s)://`, `javascript:`/`data:` bloklanadi, Faza 13).

### 31.3 SEO qattiqlashtirish

- **`lang/uz/validation.php`** — §31.1 ga qarang.
- **`public/robots.txt`** yangilandi — `/admin`, `/kirish`,
  `/royxatdan-otish`, `/profil`, `/bloglarim`, `/comments`, `/qidiruv`
  disallow qilingan (draft/pending/rejected kontent hech qachon
  route orqali sizib chiqmaydi, lekin bu qatlam qo'shimcha ehtiyot
  chorasi — admin/auth sahifalar indekslanmasin).
- **`/sitemap.xml`** (yangi, `SitemapService` + `SitemapController`) —
  faqat published/approved kontent (`->published()` scope orqali, har bir
  content turi uchun), statik sahifalar (`/`, `/xarita`, `/boglanish` va h.k.).
  Draft/pending/rejected URL **hech qachon** kirmaydi — bu servis darajasida
  kafolatlangan (bir xil `published()` scope barcha joyda ishlatiladi).
- **Structured data (JSON-LD):** faqat aniq qo'llab-quvvatlanadigan schema:
  - `WebSite` + `SearchAction` (global, layout'da bir marta).
  - `BreadcrumbList` (6 ta detail sahifada — Qorboshi/Uzgolon/Literature/
    Video/Blog/TimelineEvent, mavjud breadcrumb massividan qayta ishlatib).
  - `Article` (faqat Blog detail — eng "maqola"ga o'xshash content turi).
  - Hech qanday tarixiy da'vo yoki soxta schema **yaratilmadi**.
  - **Xavfsizlik:** barcha JSON-LD `JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT
    |JSON_HEX_AMP` flaglari bilan encode qilinadi — foydalanuvchi kontenti
    (masalan Blog sarlavhasi) `</script>` orqali script'dan chiqib keta
    olmaydi.
- **Custom error sahifalari** (404/403/419/429/500/503) — mavjud dizayn
  tokenlaridan (`resources/views/errors/minimal.blade.php`), asosiy
  layout'ga bog'liq emas (DB/session ishlamay qolgan holatda ham
  render bo'lishi uchun) — stack trace/config/DB tafsilot chiqarmaydi.

### 31.4 Performance audit natijasi

- N+1 — barcha public/admin sahifalarda avvalgi fazalarda test qilingan,
  bu bosqichda qo'shimcha regression topilmadi.
- `Model::all()` (cheksiz query) — hech qanday Service/Controller'da
  topilmadi.
- JS bundle ~1MB (asosan MapLibre GL, o'zi ~800KB minified) — bu
  xarita funksionalligi uchun zarur trade-off, MapLibre'ni olib
  tashlash yoki kod-splitting qilish alohida qaror talab qiladi
  (hozircha ishlaydigan narsani buzmaslik ustuvor).
- DB indexlar — mavjud holat audit qilindi, qo'shimcha index
  **qo'shilmadi**: FK ustunlari (`period_id`, `region_id` va h.k.)
  Laravel `foreignId()->constrained()` orqali avtomatik indekslangan;
  past-kardinallik ustunlarga (`status` yolg'iz) alohida index —
  Faza 12-13'da qabul qilingan qaror bilan bir xil sabab bo'yicha
  qo'shilmadi (query planner uchun foydasiz, keraksiz yozish xarajati).

### 31.5 Accessibility / Responsive

- `loading="lazy"` qo'shildi: grid kartalar (`content-card`), galereya
  rasmlari (Qorboshi/Uzgolon detail), izoh avatarlari, admin jadval
  thumbnail'lari. **Hero/LCP rasmlarga qo'shilmadi** (`hero.blade.php`,
  detail sahifalarning asosiy portret/muqova rasmi) — ular sahifa
  yuklanishининг birinchi muhim vizual elementi.
- Barcha `<img>` elementlari allaqachon Tailwind `h-X w-X` klasslar bilan
  aniq o'lcham/aspekt nisbatiga ega edi (CLS xavfi yo'q).
- Mavjud `aria-expanded`/`aria-controls` (xarita paneli, Faza 13),
  semantik HTML (`<nav>`, `<fieldset>`, `<legend>`, `<dl>`) avvalgi
  fazalarda qo'llanilgan — bu bosqichda qo'shimcha buzilish topilmadi.

### 31.6 i18n (Internationalization)

`lang/uz/validation.php` — Laravel standart validatsiya xabarlari uchun
to'liq o'zbekcha tarjima + eng ko'p ishlatiladigan maydon nomlari uchun
`attributes` mapping (masalan `title` → "sarlavha"). `auth.php`/
`pagination.php`/`passwords.php` tarjima qilinmadi — bu loyihada
ishlatilmaydi (custom auth/pagination/parol-tiklash komponentlari
allaqachon o'zbekcha matn bilan qattiq yozilgan, standart Laravel
view'lari umuman render qilinmaydi — tekshirildi).

---

## 32. Deployment

To'liq amaliy qo'llanma — `DEPLOYMENT.md` (server talablari, deploy
tartibi, `.env` sozlamalari, fayl ruxsatlari, backup, health check,
logging). Qisqacha xulosa:

- Queue worker/Supervisor **kerak emas** — loyihada hech qanday
  `ShouldQueue`/`Mail::` ishlatilmaydi (audit tasdiqladi), `QUEUE_
  CONNECTION=sync` yetarli.
- `php artisan optimize`/`optimize:clear` sikli test qilindi — ikkala
  holatda ham to'liq test suite (144 test) muvaffaqiyatli o'tdi.
- Health check — `/up` (Laravel o'rnatilgan, sezgir ma'lumot
  qaytarmaydi, tekshirildi).

---

## 33. COMPLETED vs DEFERRED — yakuniy holat

### COMPLETED (Phase 1-14 + Production Readiness)

- To'liq texnik arxitektura: Qorboshi/Uzgolon/Literature/Video/Blog CRUD,
  auth, rollar, admin panel.
- Global Search, Comments + moderatsiya.
- HistoricalRegion/HistoricalMapLayer admin CRUD + GeoJSON texnik
  validatsiya.
- Real tarixiy xarita infratuzilmasi (MapLibre, layer switcher, davr
  filtri, source/provenance, accuracy metadata).
- Timeline (xronologiya) — CRUD, public sahifalar, xarita
  sinxronizatsiyasi.
- Production readiness: xavfsizlik header'lari, i18n (o'zbekcha
  validatsiya), SEO (sitemap, robots, JSON-LD, custom error sahifalari),
  deployment hujjatlari.

### DEFERRED (ataylab, keyingi bosqichlar uchun)

- **Real tarixiy kontent** — real Qo'rboshi/Qo'zg'olon ma'lumotlari,
  tarixiy chegaralar, verified GeoJSON, manba asosida tarixiy voqealar.
  Bu **alohida bosqich**: "REAL HISTORICAL DATA RESEARCH + SOURCE
  VERIFICATION + CONTENT ENTRY".
- Animated time slider, avtomatik chegara reconstruction, AI-generated
  boundaries, GIS editing interface, avtomatik georeferencing.
- Content-Security-Policy (Alpine/MapLibre bilan ehtiyotkor sinovni
  talab qiladi).
- Tashqi monitoring/error-tracking xizmati (Sentry va h.k.) — hozircha
  ulanmagan, kerak bo'lsa alohida qaror.
- Email yuborish infratuzilmasi (parol tiklash, email verifikatsiya) —
  hozircha loyiha scope'ida yo'q.

---

## 34. MUHIM PRINSIP — takrorlanadi

> Texnik jihatdan tayyor bo'lgan infratuzilma — tarixiy ma'lumotning
> haqiqiyligini anglatmaydi. Har qanday real tarixiy fakt (sana,
> joylashuv, chegara, siyosiy birlik, shaxsning voqeaga aloqadorligi)
> faqat hujjatlashtirilgan, tekshirilgan manba asosida, alohida
> "Historical Data Research" bosqichida kiritiladi.

Bu bosqichda kiritilgan barcha demo ma'lumot `[DEMO]`/`[DEMO DATA]`
prefiksi bilan aniq belgilangan va production tarixiy fakt sifatida
ishlatilmasligi kerak.

---

## 35. Base map production tanlovi — MapLibre GL (WebGL)dan SVG dvigatelga o'tish

Bu bo'lim ikki bosqichli qarorni qayd etadi — birinchi urinish (tile-server
almashtirish) yetarli bo'lmadi, ikkinchi (dvigatelni almashtirish) muammoni
tubdan hal qildi.

### 35.1 Birinchi urinish: OpenFreeMap (yetarli bo'lmadi)

Faza 9'dan beri ishlatilgan `https://demotiles.maplibre.org/style.json`
MapLibre'ning rasman "demo/test uchun" uslubi — faqat quruq materik konturlarini
chizadi (yo'l, aholi punkti nomi, relyef yo'q), shuning uchun avval
`https://tiles.openfreemap.org/styles/positron` (bepul, API-kalitisiz
OpenFreeMap loyihasi) bilan almashtirildi. Bu real geografik detal muammosini
hal qildi, lekin **asosiy muammoni yechmadi**: production muhitida (institut
kompyuterida) xarita konteyneri to'g'ri o'lchamda yaratilar, lekin ichi
umuman bo'sh/ko'rinmas qolar edi.

### 35.2 Haqiqiy sabab: WebGL/rAF ba'zi muhitlarda ishlamaydi

Diagnostika shuni ko'rsatdi: **MapLibre GL — WebGL asosida ishlaydi va o'z
render tsiklini `requestAnimationFrame`ga bog'laydi.** Ba'zi kompyuterlar/
tarmoqlarda (uskunaviy tezlashtirish o'chirilgan, GPU cheklangan yoki
korporativ/institut xavfsizlik siyosati) WebGL konteksti umuman ishga
tushmaydi yoki uning render tsikli hech qachon "load" holatiga yeta olmaydi —
natijada xarita **butunlay bo'sh, xatosiz va sukut saqlagan holda** qoladi
(konteyner to'g'ri o'lchamda, lekin ichi hech qachon chizilmaydi). Bu eng
oddiy, hech qanday tashqi manbaga muhtoj bo'lmagan test uslubi (bo'sh
`sources`, bitta background layer) bilan ham qayta hosil qilindi — demak
muammo tile-server yoki ma'lumotda emas, balki **WebGL dvigatelining o'zida**
edi.

### 35.3 Yakuniy yechim: WebGL'siz, sof SVG dvigateli

MapLibre GL to'liq olib tashlandi (`npm uninstall maplibre-gl` — bundle
hajmi ~1.1MB'dan (gzip 303KB) 128KB'ga (gzip 45KB) tushdi). O'rniga
`resources/js/map/svg-map.js` — MapLibre'ning kichik bir API qismini
(`addSource`/`addLayer`/`on`/`fitBounds`/`flyTo`/`Popup`/`Marker`) taqlid
qiluvchi, lekin **oddiy SVG DOM elementlari** orqali chizadigan o'z
dvigatelimiz yozildi:

- **GPU/WebGL'ga umuman bog'liq emas** — SVG brauzerning oddiy DOM/layout
  dvigateli orqali sinxron chiziladi, shuning uchun har qanday kompyuter/
  brauzerda (hatto uskunaviy tezlashtirish o'chirilgan bo'lsa ham) ishlaydi.
- **Bazaviy geografik ma'lumot:** geoBoundaries.org (OpenStreetMap asosida,
  ODC-BY litsenziya)dan olingan O'zbekistonning 14 ta viloyat/respublika/
  shahar chegarasi — `public/geo/uzbekistan-regions.geojson` (148KB, statik
  fayl, loyihaning o'z serveridan xizmat qiladi, **hech qanday tashqi
  tile-server'ga so'rov yubormaydi** — bu institut/korporativ tarmoq
  cheklovlaridan ham butunlay mustaqil qiladi).
- **Proyeksiya:** oddiy ekvirektangulyar proyeksiya, kenglik bo'yicha
  `cos(o'rtacha_kenglik)` tuzatish koeffitsienti bilan (mamlakat miqyosida
  yetarli aniqlik, murakkab Merkator hisoblash shart emas).
- **Interaktivlik:** pan (drag), zoom (wheel + tugmalar), popup (click),
  draggable marker — barchasi qo'lda, WebGL'siz DOM voqealari orqali
  qayta yozildi.
- **API moslik:** chaqiruvchi kod (`turkestan-map.js`, `uzgolon-map.js`,
  `marker-picker.js`, `geojson-preview.js`) MapLibre'dagi bilan deyarli bir
  xil struktura/mantiqni saqlab qoldi (`map.addSource(...)`,
  `map.addLayer(...)`, `map.on('click', layerId, cb)`) — faqat "dvigatel"
  almashtirildi, business-logika o'zgarmadi.
- **Nima yo'qoldi (ataylab, hozircha kerak emas):** vektor tile-based
  streets/joy nomlari detali (endi faqat viloyat chegaralari + markerlar);
  murakkab proyeksiya buzilishlarini to'g'irlash. Bular hozirgi
  "qo'zg'olon joylari + viloyat konteksti" ko'rsatish maqsadi uchun yetarli.

### 35.4 Yon ta'sirda topilgan va tuzatilgan mustaqil xato

`resources/views/components/admin/map-point-picker.blade.php`da
`onChange: onMapChange` — Alpine metodini xom holda (bog'lanmagan holda)
uzatgan, natijada `options.onChange(...)` chaqirilganda `this` Alpine
komponentiga emas, `options` obyektiga ishora qilgan (standart JS
"detached method" xatosi). Natijada xaritani bosib koordinata tanlash
**hech qachon** `Latitude`/`Longitude` maydonlarini to'ldirmagan — bu xato
MapLibre'dan oldin ham mavjud bo'lgan, faqat interaktiv brauzer testida
(curl orqali emas) birinchi marta aniqlandi. Tuzatildi:
`onChange: (lat, lng) => onMapChange(lat, lng)` (arrow function `this`ni
to'g'ri ushlab qoladi).
