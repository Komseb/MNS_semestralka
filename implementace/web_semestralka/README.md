# Ziggid - Komunitní diskuzní platforma

Moderní webová aplikace pro sdílení příspěvků, komentáře a diskuze v rámci různých kategorií. Postavená na vlastním PHP MVC frameworku s Twig template enginem a Tailwind CSS v4.

## 📁 Popis adresářové struktury

```
web_semestralka/
│
├── assets/                      # Statické soubory aplikace
│   └── js/                     # JavaScript soubory
│       └── auth.js             # Validace přihlášení a registrace
│
├── config/                      # Konfigurační soubory
│   └── database.php            # Nastavení připojení k MySQL databázi
│
├── controllers/                 # MVC Controllery - logika aplikace
│   ├── AdminController.php     # Správa admin panelu (uživatelé, příspěvky, kategorie, nastavení)
│   ├── AuthController.php      # Registrace, přihlášení, odhlášení uživatelů
│   ├── CategoriesController.php # Zobrazení seznamu kategorií
│   ├── ErrorController.php     # Obsluha chyb (404)
│   ├── HomeController.php      # Hlavní feed s příspěvky, filtrování, stránkování
│   ├── LandingController.php   # Úvodní stránka aplikace
│   ├── PostsController.php     # Správa příspěvků, hlasování, komentáře
│   └── ProfileController.php   # Uživatelské profily a jejich správa
│
├── core/                        # Základní komponenty frameworku
│   ├── BaseController.php      # Rodičovská třída pro všechny controllery
│   ├── Permissions.php         # Systém oprávnění a rolí (user/admin/superadmin)
│   ├── Router.php              # Směrování URL požadavků na controllery
│   └── View.php                # Rendering Twig šablon
│
├── images/                      # Nahrané obrázky
│   ├── avatars/                # Avatary uživatelů
│   └── posts/                  # Obrázky v příspěvcích
│
├── models/                      # MVC Modely - datová vrstva
│   ├── BaseModel.php           # Rodičovská třída s PDO připojením
│   ├── Category.php            # Model pro kategorie (CRUD operace)
│   ├── Comment.php             # Model pro komentáře
│   ├── Post.php                # Model pro příspěvky, hlasování, filtrování
│   ├── User.php                # Model pro autentizaci a správu uživatelů
│   └── UserManagement.php      # Model pro administraci uživatelů (ban, role)
│
├── vendor/                      # Composer závislosti
│   └── twig/                   # Twig template engine
│
├── views/                       # View šablony (Twig)
│   └── templates/
│       ├── base.twig           # Základní layout aplikace
│       ├── admin/              # Šablony admin panelu
│       │   ├── categories.twig # Správa kategorií
│       │   ├── dashboard.twig  # Admin dashboard
│       │   ├── posts.twig      # Správa příspěvků
│       │   ├── settings.twig   # Systémové nastavení a statistiky
│       │   └── users.twig      # Správa uživatelů
│       ├── auth/               # Autentizace
│       │   ├── login.twig      # Přihlášení
│       │   └── register.twig   # Registrace
│       ├── categories/         # Kategorie
│       │   └── index.twig      # Seznam kategorií
│       ├── error/              # Chybové stránky
│       │   └── 404.twig        # Stránka nenalezena
│       ├── home/               # Hlavní feed
│       │   └── index.twig      # Feed s příspěvky, filtry, hlasování
│       ├── landing/            # Úvodní stránka
│       │   └── index.twig      # Landing page
│       ├── partials/           # Opakující se komponenty
│       │   ├── footer.twig     # Patička
│       │   ├── head.twig       # HTML head sekce
│       │   └── header.twig     # Navigace (responsive s hamburger menu)
│       ├── posts/              # Příspěvky
│       │   ├── create.twig     # Vytvoření nového příspěvku
│       │   └── view.twig       # Detail příspěvku s komentáři
│       └── profile/            # Profil
│           └── index.twig      # Uživatelský profil
│
├── composer.json                # Composer konfigurace (Twig závislost)
├── database.sql                 # SQL schéma databáze (struktura tabulek)
├── fill_database.sql            # Testovací data (demo uživatelé, příspěvky)
├── index.php                    # Vstupní bod aplikace (autoload, routing)
├── input.css                    # Tailwind CSS vstupní soubor
├── package.json                 # NPM závislosti (Tailwind CLI)
├── style.css                    # Kompilovaný CSS výstup
└── README.md                    # Tento soubor s dokumentací

```

## 🚀 Návod k instalaci

### Systémové požadavky

- **PHP 8.0+** s rozšířeními:
  - PDO (PHP Data Objects)
  - pdo_mysql
  - mbstring
  - gd (pro zpracování obrázků)
- **MySQL 5.7+** nebo **MariaDB 10.2+**
- **Composer** (pro správu PHP závislostí)
- **Node.js 14+** a **npm** (pro Tailwind CSS)
- **Webový server** (Apache/Nginx) nebo **XAMPP/WAMP**

### Krok 1: Získání projektu

```bash
# Umístěte projekt do www/htdocs složky vašeho webového serveru
# Například pro XAMPP: C:\xampp\htdocs\web_semestralka
```

### Krok 2: Instalace PHP závislostí

```powershell
# Přejděte do složky projektu
cd C:\xampp\htdocs\web_semestralka

# Nainstalujte Composer závislosti (Twig template engine)
composer install
```

### Krok 3: Instalace Node.js závislostí

```powershell
# Nainstalujte NPM závislosti (Tailwind CSS CLI)
npm install
```

### Krok 4: Kompilace CSS stylů

```powershell
# Zkompiluje Tailwind CSS z input.css do style.css
npx @tailwindcss/cli -i ./input.css -o ./style.css

# Případně pro automatickou rekompilaci při změnách (watch mode):
npx @tailwindcss/cli -i ./input.css -o ./style.css --watch
```

### Krok 5: Vytvoření databáze

1. Spusťte MySQL/MariaDB (v XAMPP klikněte na Start u MySQL)
2. Vytvořte novou databázi (např. `ziggid_db`)
3. Importujte schéma databáze:

```sql
# Přes příkazový řádek:
mysql -u root -p ziggid_db < database.sql

# Nebo přes phpMyAdmin:
# - Otevřete http://localhost/phpmyadmin
# - Vytvořte databázi ziggid_db
# - Vyberte databázi a v záložce Import nahrajte database.sql
```

4. (Volitelně) Naplňte databázi testovacími daty:

```sql
mysql -u root -p ziggid_db < fill_database.sql
```

### Krok 6: Konfigurace databázového připojení

Upravte soubor `config/database.php`:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'ziggid_db',  // Název vaší databáze
    'username' => 'root',        // Vaše MySQL uživatelské jméno
    'password' => ''             // Vaše MySQL heslo (XAMPP výchozí: prázdné)
];
```

### Krok 7: Nastavení oprávnění (Linux/Mac)

```bash
# Zajistěte práva zápisu pro upload složky
chmod 755 images/avatars/
chmod 755 images/posts/
```

### Krok 8: Spuštění aplikace

1. Spusťte Apache webový server (v XAMPP klikněte na Start u Apache)
2. Otevřete v prohlížeči:

```
http://localhost/web_semestralka/
```

## 👤 Testovací účty

Po importu `fill_database.sql` budete mít k dispozici testovací účty:

### SuperAdmin (plná správa)
- **Email:** `boss@web.cz`
- **Heslo:** `password123`
- Přístup: všechny admin funkce včetně kategorií a nastavení

### Admin (moderátor)
- **Email:** `admin@web.cz`
- **Heslo:** `password123`
- Přístup: správa uživatelů a příspěvků

### Běžný uživatel
- **Email:** `pepa@email.cz`
- **Heslo:** `password123`
- Přístup: vytváření příspěvků, komentování, hlasování

## ✨ Funkce aplikace

### Základní funkce
- ✅ Registrace a přihlašování uživatelů
- ✅ Vytváření příspěvků s obrázky (max 5 MB)
- ✅ Komentáře k příspěvkům
- ✅ Systém hlasování (upvote/downvote) s okamžitou zpětnou vazbou
- ✅ Kategorie příspěvků s filtrováním
- ✅ Řazení příspěvků (podle data/hlasů)
- ✅ Stránkování (10 příspěvků na stránku)
- ✅ Uživatelské profily s avatary

### Admin panel (role Admin a SuperAdmin)
- ✅ Správa uživatelů (změna role, ban/unban, mazání)
- ✅ Správa příspěvků (mazání)
- ✅ Správa kategorií - pouze SuperAdmin (vytváření, editace, mazání)
- ✅ Systémové nastavení - pouze SuperAdmin (statistiky, informace)

### Responsive design
- ✅ Plně responzivní layout
- ✅ Hamburger menu pro mobily (< 1024px)
- ✅ Optimalizované pro desktop, tablet i mobilní zařízení

## 🎨 Použité technologie

- **Backend:** PHP 8+ (vlastní MVC framework)
- **Database:** MySQL/MariaDB
- **Template Engine:** Twig 3.x
- **Frontend:** Tailwind CSS v4
- **JavaScript:** Vanilla JS (Fetch API pro AJAX)
- **Dependency Management:** Composer, npm

## 🔧 Další konfigurace

### Nastavení BASE_URL

Pokud projekt není v kořenové složce serveru, upravte v `index.php`:

```php
define('BASE_URL', '/web_semestralka/');  // Změňte podle vaší cesty
```

### Upload limitů pro obrázky

Výchozí limit je 5 MB. Pro změnu upravte `php.ini`:

```ini
upload_max_filesize = 5M
post_max_size = 5M
```

### URL Routing

Aplikace používá query parametr `r` pro routing:

- Landing page: `http://localhost/web_semestralka/` nebo `?r=landing`
- Feed: `?r=home`
- Témata/kategorie: `?r=categories`
- Detail příspěvku: `?r=posts/view&id=1`
- Registrace: `?r=auth/register`
- Přihlášení: `?r=auth/login`
- Admin panel: `?r=admin`

## 📝 Poznámky pro vývojáře

- Routing používá formát `?r=controller/action`
- Twig šablony dědí z `base.twig`
- Tailwind CSS v4 používá `@import` syntaxi v `input.css`
- Po změnách v `input.css` je nutné rekompilovat: `npx @tailwindcss/cli -i ./input.css -o ./style.css`
- Všechny controllery dědí z `BaseController` a volají `parent::__construct()`
- Modely dědí z `BaseModel` a mají přístup k PDO připojení přes `$this->db`

## 📄 Licence

Tento projekt je určen pro vzdělávací účely.

## 👨‍💻 Autor

Vytvořeno jako semestrální práce.
