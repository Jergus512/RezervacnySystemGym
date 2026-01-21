# SUPER GYM — Rezervačný systém

Tento repozitár obsahuje Laravel aplikáciu pre rezervačný systém (klienti, tréneri, admin, recepcia) s kalendárom tréningov, kreditmi a správou rezervácií.

Tento README poskytuje rýchly návod na lokálnu inštaláciu, Docker variantu, testovanie a riešenie bežných problémov.

---

## Požiadavky

- PHP 8.1+ (alebo verzia používaná v projektových súboroch)
- Composer
- Node.js + npm / pnpm
- Git
- (voliteľne) Docker & Docker Compose

V repozitári sú súbory `Dockerfile` a `docker-compose.yml` — ak chcete spustiť aplikáciu v kontajneroch, nepotrebujete lokálnu inštaláciu PHP/Node.

---

## Rýchla lokálna inštalácia

1. Klonujte repozitár:

```bash
git clone <repo-url> super-gym
cd super-gym
```

2. Skopírujte `.env` (ak nemáte pripravené):

```bash
cp .env.example .env
```

3. Ak chcete použiť SQLite (v repozitári je `database/database.sqlite`), uistite sa, že súbor existuje a má správne práva:

```bash
mkdir -p database
touch database/database.sqlite
chmod -R 755 database
```

V `.env` nastavte:

```
DB_CONNECTION=sqlite
DB_DATABASE=${PWD}/database/database.sqlite
```

(alebo použite relatívnu cestu `database/database.sqlite` podľa konfigurácie `config/database.php`)

4. Inštalujte PHP závislosti:

```bash
composer install --no-interaction --prefer-dist
```

5. Inštalujte JavaScript / frontend závislosti a zostavte assets:

```bash
npm install
# pre development
npm run dev
# alebo pre produkciu
npm run build
```

6. Vygenerujte aplikačný kľúč a spustite migrácie + seedery:

```bash
php artisan key:generate
php artisan migrate --seed
```

7. Vytvorte symbolický link pre storage (ak potrebné):

```bash
php artisan storage:link
```

8. Spustite lokálny server (alebo použite váš webserver / Valet):

```bash
php artisan serve
# predvolene http://127.0.0.1:8000
```

Po otvorení stránky by mala byť dostupná homepage a funkčné rozhranie.

---

## Spustenie pomocou Docker Compose

Ak chcete aplikáciu spustiť cez Docker (odhaliť závislosti v `docker-compose.yml`):

1. Build a spustenie:

```bash
docker compose up -d --build
```

2. Získajte shell do PHP kontajnera (príklad názvu kontajnera `app` — upravte podľa `docker-compose.yml`):

```bash
docker compose exec app bash
# alebo
docker exec -it <container_name> bash
```

3. Vnútri kontajnera spustite migrácie a seedery (alebo spravte to z hosta cez artisan volanie vo vnútri kontajnera):

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

4. Aplikácia bude dostupná na porte mapovanom v `docker-compose.yml` (štandardne 80 alebo 8080).

---

## Testovanie

Spustenie PHPUnit testov:

```bash
./vendor/bin/phpunit
# alebo
php artisan test
```

---

## Debug & riešenie problémov

- Dropdown/menu je prekrytý: ak sa na homepage objavuje overlay alebo element, ktorý menu prekrýva, skontrolujte `z-index` v DevTools a pokúste sa nájsť rodičovské elementy, ktoré vytvárajú stacking context (napr. `transform`, `opacity`, `filter`, `position` a pod.). V tomto repozitári som pridal do homepage CSS fix (veľký `z-index` pre bežné navigačné selektory). Ak sa problém nevyrieši, zvážte:
  - presunutie dropdown elementu do `body` pri otvorení cez JS,
  - alebo `position: fixed` pre menu a explicitné umiestnenie.

- Chyby pri migráciách (permissions): uistite sa, že `database/` a `storage/` majú správne práva (na unix systémoch):

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

- Ak používate SQLite a migrácie hlásia, že databáza nenájdená, skontrolujte cestu v `.env` a existenciu súboru `database/database.sqlite`.

- Ak blade zmeny nevidíte, vyčistite cache:

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

---

## Štruktúra projektu (stručne)

- `app/` — aplikácia (Controllers, Models, Middleware)
- `resources/views/` — Blade šablóny
- `public/` — verejné assets
- `database/` — sqlite databáza, migrácie, seedery
- `docker/` — nginx konfigurácia a ďalšie Docker artefakty
- `routes/` — definície rout (web.php, console.php)

---
