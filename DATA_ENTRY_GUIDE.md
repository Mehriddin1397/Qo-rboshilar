# QO'RBOSHILAR.UZ — MA'LUMOTLARNI KIRITISH VA XARITA QO'LLANMASI

Ushbu qo'llanma `public/Turkiston_Qorboshilari_Loyiha claude.docx` hujjati asosida tuzilgan bo'lib, loyihaning ma'lumotlar bazasiga barcha tarixiy faktlar, shaxslar, qo'zg'olonlar, adabiyotlar hamda **hozirgi O'zbekiston xaritasi va koordinatalariga moslangan GeoJSON/markerlarni** to'g'ri ketma-ketlikda kiritish bo'yicha amaliy yo'riqnomadir.

---

## MUNDARIJA
1. [Ma'lumotlar kiritishning qat'iy ketma-ketligi](#1-malumotlar-kiritishning-qatiy-ketma-ketligi)
2. [1-bosqich: Tarixiy davrlar (`periods`)](#2-1-bosqich-tarixiy-davrlar-periods)
3. [2-bosqich: Zamonaviy hududlar (`regions`)](#3-2-bosqich-zamonaviy-hududlar-regions)
4. [3-bosqich: Tarixiy hududlar va GeoJSON poligonlari (`historical_regions`)](#4-3-bosqich-tarixiy-hududlar-va-geojson-poligonlari-historical_regions)
5. [4-bosqich: Adabiyotlar va Ilmiy manbalar (`adabiyotlar`, `source_references`)](#5-4-bosqich-adabiyotlar-va-ilmiy-manbalar-adabiyotlar-source_references)
6. [5-bosqich: Qo'zg'olonlar va Harakatlar (`uzgolonlar`)](#6-5-bosqich-qozgolonlar-va-harakatlar-uzgolonlar)
7. [6-bosqich: Qo'rboshilar va Sarkardalar (`qorboshilar`)](#7-6-bosqich-qorboshilar-va-sarkardalar-qorboshilar)
8. [7-bosqich: Xarita markerlari — O'zbekiston koordinatalari (`map_markers`)](#8-7-bosqich-xarita-markerlari--ozbekiston-koordinatalari-map_markers)
9. [8-bosqich: Xronologiya voqealari (`timeline_events`)](#9-8-bosqich-xronologiya-voqealari-timeline_events)
10. [9-bosqich: Tarixiy xarita raster qatlamlari (`historical_map_layers`)](#10-9-bosqich-tarixiy-xarita-raster-qatlamlari-historical_map_layers)

---

## 1. Ma'lumotlar kiritishning qat'iy ketma-ketligi

Loyiha ma'lumotlar bazasida xorijiy kalitlar (`foreign keys`) va ko'pdan-ko'pga (`many-to-many`) munosabatlar mavjud. Xatoliklarning oldini olish uchun ma'lumotlar quyidagi oqim asosida kiritiladi:

```
[Periods & Regions]
        ↓
[Historical Regions + GeoJSON]
        ↓
[Adabiyotlar & Source References]
        ↓
[Uzgolonlar (Qo'zg'olonlar)]
        ↓
[Qorboshilar] ← (Bog'lanadi: Qo'zg'olonlar, Adabiyotlar, Hududlar)
        ↓
[Map Markers] ← (Bog'lanadi: Qo'zg'olonlar, Koordinatalar)
        ↓
[Timeline Events] ← (Bog'lanadi: Davr, Qo'rboshi, Qo'zg'olon, Koordinatalar)
        ↓
[Historical Map Layers]
```

---

## 2. 1-bosqich: Tarixiy davrlar (`periods`)

Admin paneldan: `/admin/periods` bo'limi orqali kiritiladi.

| Davr nomi (`name`) | Slug | Boshlanish yili | Tugash yili | Tavsif (`description`) |
|---|---|---|---|---|
| **Chor Rossiyasi istilosi davri** | `chor-istilosi-davri` | 1850 | 1876 | Rossiya imperiyasining Qo'qon xonligi va Toshkentni bosib olish davri. |
| **Chorizm mustamlakachiligi va milliy uyg'onish** | `chorizm-mustamlakachiligi` | 1877 | 1916 | Andijon (Dukchi Eshon) qo'zg'oloni va 1916-yilgi mardikorlik g'alayonlari. |
| **Turkiston Muxtoriyati va Istiqlolchilik boshlanishi** | `turkiston-muxtoriyati-davri` | 1917 | 1920 | Qo'qon muxtoriyati e'lon qilinishi, uning ag'darilishi va ommaviy qurolli kurashning boshlanishi. |
| **Ommaviy istiqlolchilik (Qo'rboshilar harakati)** | `ommaviy-istiqlolchilik-harakati` | 1920 | 1924 | Buxoro va Xiva xonliklarining qulashi, Farg'ona, Samarqand, Xorazm va Buxoroda keng ko'lamli milliy kurash. |
| **So'nggi qarshilik va harakat yakunlanishi** | `songgi-qarshilik-va-yakun` | 1924 | 1935 | Milliy-hududiy chegaralanish, kollektivlashtirishga qarshi noroziliklar va Ibrohimbek boshchiligidagi so'nggi to'qnashuvlar. |

---

## 3. 2-bosqich: Zamonaviy hududlar (`regions`)

Admin paneldan: `/admin/regions` bo'limi orqali kiritiladi.
Hozirgi O'zbekiston Respublikasi ma'muriy-hududiy tuzilishi bo'yicha guruhlangan:

1. **Farg'ona vodiysi** (`fargona-vodiysi`)
   * *Qamrovi:* Farg'ona, Andijon va Namangan viloyatlari.
   * *Tavsif:* Madaminbek, Katta va Kichik Ergash, Shermuhammadbek, Shakarxon qo'rboshilar harakat qilgan asosiy markaz.
2. **Toshkent vohasi** (`toshkent-vohasi`)
   * *Qamrovi:* Toshkent shahri va Toshkent viloyati (Chirchiq, Bo'zsuv havzasi).
   * *Tavsif:* 1865-yilgi Mulla Alimqul boshchiligidagi Toshkent mudofaasi va keyingi siyosiy voqealar markazi.
3. **Zarafshon vohasi (Samarqand va Navoiy)** (`zarafshon-vohasi`)
   * *Qamrovi:* Samarqand viloyati, Navoiy viloyati (Karmana, Nurota).
   * *Tavsif:* Bahrombek, Ochilbek, Fuzayl Maxdum, Nodira qiz faoliyat yuritgan hudud.
4. **Buxoro vohasi** (`buxoro-vohasi`)
   * *Qamrovi:* Buxoro shahri, G'ijduvon, Vobkent, Romitan, Shofirkon tumanlari.
   * *Tavsif:* Mulla Abdulqahhor, Jabbor qo'rboshi harakati va 1920-yil Buxoro amirligi tugatilishi voqealari.
5. **Quyi Amudaryo va Xorazm** (`xorazm-va-amudaryo`)
   * *Qamrovi:* Xorazm viloyati (Xiva, Pitnak, Hazorasp, Bog'ot, Xonqa) va Qoraqalpog'iston (To'rtko'l, Chimboy).
   * *Tavsif:* Junaidxon boshchiligidagi istiqlolchilik va 1923-yilgi dehqonlar qo'zg'oloni.
6. **Janubiy O'zbekiston (Surxondaryo va Qashqadaryo)** (`janubiy-ozbekiston`)
   * *Qamrovi:* Surxondaryo (Boysun, Sherobod, Denov) va Qashqadaryo viloyatlari.
   * *Tavsif:* Sharqiy Buxoro bilan chegaradosh hududlar, Ibrohimbek otryadlarining harakat yo'nalishlari.
7. **Jizzax va Sirdaryo** (`jizzax-va-sirdaryo`)
   * *Qamrovi:* Jizzax viloyati va Sirdaryo viloyati.
   * *Tavsif:* 1916-yilgi mardikorlikka qarshi mashhur Jizzax qo'zg'oloni va Zarafshon tog'oldi harakatlari.

---

## 4. 3-bosqich: Tarixiy hududlar va GeoJSON poligonlari (`historical_regions`)

Admin paneldan: `/admin/historical-regions` bo'limi orqali kiritiladi.
Ushbu poligonlar hozirgi O'zbekiston xaritasidagi hududlar ustiga Leaflet orqali chiziladi:

### 1. Farg'ona vodiysi tarixiy-harbiy harakat hududi
* **Nomi:** Farg'ona vodiysi istiqlolchilik o'chog'i
* **Tarixiy nomi:** Farg'ona viloyati (Turkiston general-gubernatorligi) / Qo'qon xonligi
* **Zamonaviy nomi:** Farg'ona, Andijon, Namangan viloyatlari
* **Turi (`region_type`):** `geographical`
* **Davr:** `Ommaviy istiqlolchilik (Qo'rboshilar harakati)`
* **GeoJSON:**
```json
{
  "type": "Feature",
  "geometry": {
    "type": "Polygon",
    "coordinates": [
      [
        [70.50, 40.25],
        [71.50, 40.20],
        [72.50, 40.35],
        [73.10, 40.80],
        [72.80, 41.20],
        [71.80, 41.30],
        [70.80, 41.10],
        [70.40, 40.60],
        [70.50, 40.25]
      ]
    ]
  }
}
```

### 2. Toshkent mudofaasi va Chirchiq havzasi
* **Nomi:** Toshkent mudofaa hududi (1865)
* **Tarixiy nomi:** Toshkent bekligi va Chirchiq istehkomlari
* **Zamonaviy nomi:** Toshkent shahri va Toshkent viloyati
* **Turi (`region_type`):** `political`
* **Davr:** `Chor Rossiyasi istilosi davri`
* **GeoJSON:**
```json
{
  "type": "Feature",
  "geometry": {
    "type": "Polygon",
    "coordinates": [
      [
        [68.90, 41.10],
        [69.50, 41.00],
        [70.10, 41.40],
        [70.00, 41.90],
        [69.20, 41.70],
        [68.90, 41.10]
      ]
    ]
  }
}
```

### 3. Zarafshon vodiysi (Samarqand, Buxoro, Karmana)
* **Nomi:** Zarafshon havzasi istiqlolchilik markazlari
* **Tarixiy nomi:** Buxoro amirligi (Samarqand, Karmana, Nurota, G'ijduvon)
* **Zamonaviy nomi:** Samarqand, Navoiy va Buxoro viloyatlari
* **Turi (`region_type`):** `geographical`
* **Davr:** `Ommaviy istiqlolchilik (Qo'rboshilar harakati)`
* **GeoJSON:**
```json
{
  "type": "Feature",
  "geometry": {
    "type": "Polygon",
    "coordinates": [
      [
        [64.00, 39.50],
        [65.50, 39.30],
        [67.20, 39.40],
        [67.30, 40.10],
        [66.00, 40.60],
        [64.50, 40.30],
        [64.00, 39.50]
      ]
    ]
  }
}
```

### 4. Quyi Amudaryo va Xorazm xalq harakati hududi
* **Nomi:** Quyi Amudaryo istiqlolchilik mintaqasi
* **Tarixiy nomi:** Xiva xonligi (Xorazm Xalq Sovet Respublikasi)
* **Zamonaviy nomi:** Xorazm viloyati va Janubiy Qoraqalpog'iston
* **Turi (`region_type`):** `geographical`
* **Davr:** `Ommaviy istiqlolchilik (Qo'rboshilar harakati)`
* **GeoJSON:**
```json
{
  "type": "Feature",
  "geometry": {
    "type": "Polygon",
    "coordinates": [
      [
        [60.00, 41.20],
        [61.30, 41.10],
        [61.50, 41.80],
        [60.80, 42.10],
        [60.10, 41.70],
        [60.00, 41.20]
      ]
    ]
  }
}
```

---

## 5. 4-bosqich: Adabiyotlar va Ilmiy manbalar (`adabiyotlar`, `source_references`)

Admin paneldan: `/admin/adabiyotlar` va `/admin/source-references` orqali kiritiladi.

1. **Kitob:** *"Shermuhammadbek qo'rboshi"*
   * *Muallif:* Mansurxo'ja Xo'jayev
   * *Turi:* `book` | *Tili:* O'zbekcha
   * *Tavsif:* Farg'ona vodiysidagi 114 qo'rboshi ro'yxati, Shermuhammadbek faoliyati va arxiv ma'lumotlari.
2. **Monografiya:** *"The Russian Conquest of Central Asia: A Study in Imperial Expansion, 1814–1914"*
   * *Muallif:* Aleksandr Morrison
   * *Nashriyot:* Cambridge University Press, 2021
   * *Turi:* `research` | *Tili:* Inglizcha
   * *Tavsif:* Toshkent qamali, Alimqul faoliyati va Rossiya imperiyasi bosqinining ilmiy tahlili.
3. **Ilmiy maqola:** *"Basmachi I & Basmachi II"*
   * *Muallif:* Glenda Fraser
   * *Nashr:* Central Asian Survey, 1987
   * *Turi:* `article`
   * *Tavsif:* Madaminbekning o'limi tafsilotlari, Anvar Posho kampaniyasi va arxiv dalillari.
4. **Tarixiy tadqiqot:** *"Turkiston qo'rboshilari: milliy istiqlol kurashchilari"*
   * *Muallif:* Qahramon Rajabov, B. Eshov
   * *Turi:* `research`
   * *Tavsif:* Sovet davri mafkurasidan holi ravishda istiqlolchilar faoliyatining zamonaviy baholanishi.
5. **Xotiralar / Arxiv:** *"Toshkentning yangi tarixi"*
   * *Muallif:* Muhammad Solih
   * *Turi:* `memoir`
   * *Tavsif:* 1865-yilgi Toshkent mudofaasi guvohi tomonidan yozilgan tarixiy xotiralar.

---

## 6. 5-bosqich: Qo'zg'olonlar va Harakatlar (`uzgolonlar`)

Admin paneldan: `/admin/qozgolonlar` bo'limi orqali kiritiladi.

| Nomi | Yili | Hudud | Bosh yetakchilar | Qisqacha mazmuni va oqibati |
|---|---|---|---|---|
| **Toshkent mudofaasi (1865)** | 1865 | Toshkent vohasi | Mulla Alimqul | Chernyayev qo'shinlariga qarshi 42 kunlik qahramonona mudofaa. Alimqul vafotidan so'ng shahar taslim bo'lgan. |
| **Qo'qon xonligi istilosi va Po'latxon qo'zg'oloni** | 1873–1876 | Farg'ona vodiysi | Po'latxon (Mulla Ishoq) | Og'ir soliqlarga qarshi xalq harakati. Qo'qon xonligi tugatilib, Farg'ona viloyatiga aylantirilgan. |
| **Andijon (Dukchi Eshon) qo'zg'oloni** | 1898 | Farg'ona vodiysi | Dukchi Eshon (Muhammadali xalfa) | Mingtepadan boshlangan mustamlakachilikka qarshi xalq harakati. Mingtepa yakson qilingan, yetakchilar qatl etilgan. |
| **1916-yilgi Milliy-ozodlik (mardikorlik) qo'zg'oloni** | 1916 | Butun O'zbekiston | Xalq yetakchilari, Jadidlar | Nikolay IIning mardikorlik farmoniga qarshi butun Turkistondagi qurolli chiqishlar. |
| **Turkiston Muxtoriyatining bostirilishi** | 1917–1918 | Farg'ona vodiysi (Qo'qon) | Mustafo Cho'qay, Kichik Ergash | Milliy demokratik hukumatning bolsheviklar tomonidan qonga botirilishi; ommaviy qurolli kurashning boshlanishi. |
| **Farg'ona vodiysi qo'rboshilar harakati** | 1918–1922 | Farg'ona vodiysi | Madaminbek, Shermuhammadbek, Katta Ergash | Farg'ona muvaqqat hukumati tuzilishi, Oyimqishloq va G'oyibota qurultoylari. |
| **Buxoro amirligining ag'darilishi** | 1920 | Buxoro vohasi | Said Olimxon | Frunze armiyasi tomonidan Eski Buxoroning bombardimon qilinishi va amirlik tugatilishi. |
| **Zarafshon vodiysi istiqlolchilik harakati** | 1920–1924 | Zarafshon / Buxoro | Mulla Abdulqahhor, Jabbor qo'rboshi | G'ijduvon, Vobkent, Romitan, Nurota va Karmanada o'nlab otryadlarning kurashi. |
| **Xorazm dehqonlar qo'zg'oloni va harakati** | 1918–1924 | Quyi Amudaryo | Junaidxon | Xiva xonining taxtdan voz kechishi, 1923-yil Pitnak, Hazorasp, Bog'otdagi ommaviy dehqonlar g'alayoni. |
| **Ibrohimbekning so'nggi kurashi** | 1930–1932 | Janubiy O'zbekiston / Toshkent | Ibrohimbek Laqay | Kollektivlashtirishga qarshi so'nggi yirik qurolli chiqish. 1932-yil qatl qilingan, 2021-yilda to'liq oqlangan. |

---

## 7. 6-bosqich: Qo'rboshilar va Sarkardalar (`qorboshilar`)

Admin paneldan: `/admin/qorboshilar` bo'limi orqali kiritiladi.

1. **Mulla Alimqul (Amirlashkar)** (1832–1865)
   * *Kelib chiqishi:* Navkatlik qipchoqlar oilasi.
   * *Lavozimi:* Qo'qon xonligi amirlashkari (bosh qo'mondoni).
   * *Jasorati:* Chimkent va Turkiston mudofaasini boshqargan, 1865-yil 9-mayda Toshkent ostonasidagi jangda qahramonlarcha halok bo'lgan.
2. **Po'latxon (soxta Po'latxon, Mulla Ishoq)** (?–1876)
   * *Kelib chiqishi:* Marg'ilon / Oloy tog'lari.
   * *Faoliyati:* 1873–1876-yillardagi xalq qo'zg'oloni yetakchisi, Marg'ilonda dorga osilgan.
3. **Dukchi Eshon (Muhammadali xalfa Sobir o'g'li)** (1846–1898)
   * *Kelib chiqishi:* Marg'ilon yaqinidagi Chimyon qishlog'i.
   * *Yashash joyi:* Andijon uezdi, Mingtepa qishlog'i (hozirgi Marhamat tumani).
   * *Faoliyati:* 1898-yilgi Andijon qo'zg'oloni mafkuraviy va amaliy rahbari.
4. **Madaminbek (Muhammad Aminbek Ahmadbek o'g'li)** (1892–1920)
   * *Kelib chiqishi:* Marg'ilon atrofidagi So'kchilik qishlog'i (hozirgi Toshloq tumani).
   * *Unvoni:* Farg'ona vodiysi qo'rboshilarining Oliy bosh qo'mondoni, "Amir al-muslimin".
   * *Faoliyati:* Oyimqishloq qurultoyida yetakchi etib saylangan, Farg'ona Muvaqqat hukumatini tuzgan. 1920-yil 14-mayda xiyonatkorona o'ldirilgan.
5. **Shermuhammadbek (Ko'rshermat, G'oziy)** (1895–1970)
   * *Kelib chiqishi:* Marg'ilon bekzodalari oilasi.
   * *Faoliyati:* 1920-yil Oltiariq G'oyibota qurultoyida Oliy bosh qo'mondon etib saylangan, Turkiston-turk mustaqil islom jumhuriyati raisi.
6. **Katta Ergash va Kichik Ergash**
   * *Kelib chiqishi:* Qo'qon yaqinidagi Bachqir qishlog'i.
   * *Faoliyati:* Turkiston Muxtoriyati qo'shinini saqlab qolgan va Farg'onada dastlabki ommaviy qurolli qarshilikni boshlagan sardorlar.
7. **Mulla Abdulqahhor va Jabbor qo'rboshi**
   * *Faoliyat hududi:* G'ijduvon, Shofirkon, Vobkent, Romitan, Nurota, Karmana.
   * *Kuchlar soni:* 2000 dan ortiq yigitlar bilan Zarafshon vodiysida kurashgan.
8. **Bahrombek va Ochilbek**
   * *Faoliyat hududi:* Samarqand viloyati va uning tog' etaklari.
9. **Junaidxon (Muhammad Qurbon)** (1857–1938)
   * *Kelib chiqishi:* Xiva / Turkman-yovmut qabilasi sardori.
   * *Faoliyati:* Xiva xonligi amaldagi boshqaruvchisi, Qoraqum va Xorazm istiqlolchilarining bosh qo'mondoni.
10. **Ibrohimbek Laqay (Mulla Muhammad Ibrohimbek)** (1889–1932)
    * *Kelib chiqishi:* Dushanbe Ko'ktosh qishlog'i, o'zbeklarning laqay urug'i.
    * *Unvoni:* "Islom lashkarboshisi", Buxoro istiqlolchilarining bosh qo'mondoni. 2021-yilda O'zbekiston Oliy sudi tomonidan to'liq oqlangan.
11. **Ayol qo'rboshilar:**
    * **Shakarxon qo'rboshi:** Farg'ona vodiysi, Oltiariqning Polason qishlog'idan yetishib chiqqan jasur ayol sarkarda.
    * **Nodira qiz:** Karmanalik; Ibrohimbek qo'shinidagi maxsus xotin-qizlar guruhiga boshchilik qilgan.
    * **Oyshaxon ("Oyim qo'rboshi"):** O'sh va Andijon chegarasida dushman kordonlariga zarba bergan jasur yetakchi.

---

## 8. 7-bosqich: Xarita markerlari — O'zbekiston koordinatalari (`map_markers`)

Admin paneldan: `/admin/map-markers` bo'limi orqali kiritiladi.
Barcha markerlar hozirgi O'zbekiston xaritasi bo'yicha real geolokatsiyalar bilan keltirilgan:

| # | Joy nomi | Kenglik (`latitude`) | Uzunlik (`longitude`) | Turi (`type`) | Bog'liq Qo'zg'olon / Shaxs |
|---|---|---|---|---|---|
| 1 | **Toshkent (O'rda va Darxon)** | `41.3111` | `69.2797` | `battle` | Toshkent mudofaasi (Alimqul jangi) |
| 2 | **Niyozbek qal'asi (Chirchiq)** | `41.4285` | `69.5489` | `battle` | Toshkent mudofaasi (Bo'zsuv to'g'oni) |
| 3 | **Qo'qon xon o'rdasi** | `40.5333` | `70.9333` | `uprising` | Turkiston Muxtoriyati va Qo'qon qirg'ini |
| 4 | **Bachqir qishlog'i (Qo'qon)** | `40.5080` | `70.9650` | `region_center` | Katta va Kichik Ergash qarorgohi |
| 5 | **Marg'ilon shahri** | `40.4722` | `71.7244` | `region_center` | Po'latxon qatli, Shermuhammadbek markazi |
| 6 | **So'kchilik (Toshloq tumani)** | `40.5283` | `71.8139` | `region_center` | Madaminbek tug'ilgan qishloq |
| 7 | **Polason qishlog'i (Oltiariq)** | `40.3844` | `71.4981` | `region_center` | Shakarxon qo'rboshi vatani |
| 8 | **G'oyibota qishlog'i (Oltiariq)** | `40.4120` | `71.4650` | `uprising` | 1920-yil Shermuhammadbek qurultoyi |
| 9 | **Skobelev (Farg'ona shahri)** | `40.3842` | `71.7843` | `other` | 1920-yil 6-mart Madaminbek sulhi |
| 10 | **Yaypan (O'zbekiston tumani)** | `40.3756` | `70.8156` | `region_center` | Islom Polvon markazi |
| 11 | **Mingtepa (Marhamat, Andijon)** | `40.4958` | `72.3314` | `uprising` | 1898-yil Dukchi Eshon qo'zg'oloni markazi |
| 12 | **Andijon qal'asi** | `40.7821` | `72.3442` | `battle` | 1898-yil rus garnizoniga hujum joyi |
| 13 | **Oyimqishloq (Jalaquduq)** | `40.7167` | `72.6333` | `uprising` | 1918-yil Madaminbek qurultoyi |
| 14 | **Baliqchi (Andijon)** | `40.9381` | `71.9056` | `battle` | 1875-yil Skobelev va Po'latxon jangi |
| 15 | **Xonobod (Andijon)** | `40.8067` | `72.9917` | `battle` | 1921-yil Katta Ergash so'nggi jangi |
| 16 | **Eski Buxoro (Ark qal'asi)** | `39.7778` | `64.4103` | `battle` | 1920-yil Frunze hujumi va Buxoro qulashi |
| 17 | **G'ijduvon (Buxoro)** | `40.1000` | `64.6667` | `uprising` | Mulla Abdulqahhor harakati |
| 18 | **Karmana (Navoiy viloyati)** | `40.1389` | `65.3639` | `region_center` | Fuzayl Maxdum va Nodira qiz vatani |
| 19 | **Nurota (Navoiy viloyati)** | `40.5614` | `65.6886` | `battle` | Zarafshon tog'oldi mudofaa markazi |
| 20 | **Samarqand (Registon maydoni)** | `39.6542` | `66.9597` | `region_center` | 1916-yilgi namoyishlar, Bahrombek markazi |
| 21 | **Jizzax shahri** | `40.1158` | `67.8422` | `uprising` | 1916-yilgi mardikorlik qo'zg'oloni epitsentri |
| 22 | **Xiva (Ichan Qal'a)** | `41.3783` | `60.3639` | `battle` | Junaidxon to'ntarishi va Xiva qulashi |
| 23 | **Hazorasp va Pitnak (Xorazm)** | `41.3197` | `61.0742` | `uprising` | 1923-yil dehqonlar qo'zg'oloni boshlangan joy |

---

## 9. 8-bosqich: Xronologiya voqealari (`timeline_events`)

Admin paneldan: `/admin/timeline-events` bo'limi orqali kiritiladi.

1. **1865-yil 9-may:** Mulla Alimqulning Toshkent ostonasidagi so'nggi jangi va vafoti (`battle`, Toshkent).
2. **1876-yil 19-fevral:** Qo'qon xonligining rasman tugatilishi va Farg'ona viloyati tashkil topishi (`other`, Qo'qon).
3. **1876-yil 1-mart:** Po'latxonning Marg'ilonda dorga osilishi (`other`, Marg'ilon).
4. **1898-yil 17–18-may:** Mingtepadan boshlangan Andijon qo'zg'oloni va garnizonga tungi hujum (`uprising`, Mingtepa).
5. **1916-yil 25-iyun:** Nikolay II mardikorlik farmoni e'lon qilinishi va butun o'lkadagi qo'zg'olonlar (`uprising`, Jizzax/Samarqand).
6. **1917-yil 27-noyabr:** Qo'qonda Turkiston Muxtoriyati e'lon qilinishi (`other`, Qo'qon).
7. **1918-yil 22-fevral:** Qo'qon Muxtoriyatining qonga botirilishi va shaharning vayron qilinishi (`battle`, Qo'qon).
8. **1918-yil noyabr:** Oyimqishloq qurultoyida Madaminbekning Oliy bosh qo'mondon etib saylanishi (`other`, Oyimqishloq).
9. **1919-yil 22-oktyabr:** Irkeshtomda Farg'ona Muvaqqat hukumatining tuzilishi (`other`).
10. **1920-yil 6-mart:** Skobelev (Farg'ona) shahrida Madaminbek va qizil armiya o'rtasidagi yarash bitimi (`other`, Farg'ona).
11. **1920-yil 3-may:** Oltiariq G'oyibota qurultoyida Shermuhammadbekning rahbar etib saylanishi (`other`, Oltiariq).
12. **1920-yil 14-may:** Madaminbekning xiyonatkorona otib o'ldirilishi (`other`).
13. **1920-yil 2-sentyabr:** Frunze armiyasi tomonidan Buxoroning zabt etilishi (`battle`, Buxoro).
14. **1923-yil dekabr:** Pitnak, Hazorasp, Bog'ot va Xonqada sovet soliqlariga qarshi dehqonlar qo'zg'oloni (`uprising`, Xorazm).
15. **1932-yil 31-avgust:** Ibrohimbek Laqayning sovet sudi hukmi bilan Toshkentda qatl etilishi (`other`, Toshkent).

---

## 10. 9-bosqich: Tarixiy xarita raster qatlamlari (`historical_map_layers`)

Admin paneldan: `/admin/historical-map-layers` bo'limi orqali kiritiladi.
Agar arxivdan skaner qilingan qadimiy xaritalar tasviri bo'lsa, xaritaga to'g'ri tushishi uchun quyidagi koordinata chegaralari (`bounds`) beriladi:

1. **Farg'ona vodiysi xaritasi:**
   ```json
   {
     "north": 41.50,
     "south": 40.00,
     "west": 70.00,
     "east": 73.20
   }
   ```
2. **Butun Turkiston mintaqasi:**
   ```json
   {
     "north": 45.00,
     "south": 37.00,
     "west": 58.00,
     "east": 74.00
   }
   ```

---

## 11. Xulosa va tavsiyalar

1. Har bir kiritilgan qo'rboshi sahifasida kamida bitta **bog'liq qo'zg'olon**, bitta **zamonaviy hudud** va iloji bo'lsa **adabiyot manbasi** tanlanishi shart. Bu sayt foydalanuvchilari uchun barcha ma'lumotlar o'zaro uzviy bog'langan holda ko'rinishini ta'minlaydi.
2. Har bir xarita markeri avtomatik ravishda `/xarita` interaktiv xaritasida belgilangan rang va piktogramma bilan namoyon bo'ladi.
3. Yangi ma'lumot kiritilganda `status` qiymatini `published` holatiga o'tkazishni unutmang, aks holda ular faqat admin uchun ko'rinadi.
