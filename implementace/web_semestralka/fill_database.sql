SET NAMES utf8mb4;

USE web_semestralka;

-- 1. KATEGORIE
INSERT INTO categories (name, slug, description, created_at) VALUES 
    ('Technologie', 'technologie', 'Diskuze o technologiích, programování, hardware a software', NOW()),
    ('Gaming', 'gaming', 'Hry, herní novinky, recenze a herní komunita', NOW()),
    ('Sport', 'sport', 'Sportovní události, týmy, zápasy a sportovní diskuze', NOW()),
    ('Zábava', 'zabava', 'Filmy, seriály, hudba a další zábava', NOW()),
    ('Věda', 'veda', 'Vědecké objevy, výzkum a vzdělávací obsah', NOW()),
    ('Lifestyle', 'lifestyle', 'Životní styl, móda, cestování a osobní rozvoj', NOW()),
    ('Kuchyně', 'kuchyne', 'Recepty, vaření, pečení a kulinářské tipy', NOW()),
    ('Umění', 'umeni', 'Výtvarné umění, fotografie, design a kreativita', NOW()),
    ('Osobní finance', 'osobni-finance', 'Investování, rozpočty, úvěry a spoření.', NOW()),
    ('Zdraví a fitness', 'zdravi-fitness', 'Cvičení, dieta, duševní zdraví a wellness.', NOW()),
    ('Cestování', 'cestovani', 'Tipy na cesty, recenze destinací a cestopisy.', NOW()),
    ('Politika', 'politika', 'Aktuální politické dění, volby a společenské otázky.', NOW()),
    ('DIY/Hobby', 'diy-hobby', 'Návody, kutilství, modely a tvoření.', NOW()),
    ('Auto-moto', 'auto-moto', 'Auta, motorky, opravy a tuning.', NOW()),
    ('Příroda', 'priroda', 'Turistika, ekologie, zvířata a rostliny.', NOW()),
    ('Knihy a literatura', 'knihy', 'Recenze knih, tipy na čtení a literární diskuze.', NOW()),
    ('Historie', 'historie', 'Historické události, archeologie a dokumenty.', NOW()),
    ('Sci-Fi a Fantasy', 'scifi-fantasy', 'Star Wars, LOTR, komiksy a fandom.', NOW())
ON DUPLICATE KEY UPDATE name=name;

-- 2. UŽIVATELÉ
INSERT INTO users (email, username, password_hash, role_id, created_at, avatar) VALUES 
('admin@web.cz', 'AdminKarel', '$2y$10$DummyHashForPassword123xyz...', 2, NOW(), 'admin.png'),
('boss@web.cz', 'SuperBoss', '$2y$10$DummyHashForPassword123xyz...', 3, NOW(), NULL),
('pepa@email.cz', 'PepaZDepa', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'pepa.jpg'),
('jana.novakova@email.cz', 'JanaN', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('gamer@profi.cz', 'xX_Slayer_Xx', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'avatar_game.png'),
('kuchar@recepty.cz', 'GurmánLáďa', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('tech@guru.cz', 'IT_Crowd', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'matrix.jpg'),
('sportovec@fit.cz', 'RunnerCZ', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('cestovatel@svet.cz', 'Wanderlust', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'globe.png'),
('student@vse.cz', 'Ekonom007', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('troll@internet.cz', 'RejpalObecny', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'trollface.png'),
('artist@art.cz', 'PicassoJunior', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('motorkar@auto.cz', 'RychlaKola', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('knihomol@cteni.cz', 'BookWorm', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'book.png'),
('historik@dejiny.cz', 'Dějepisář', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL),
('scifi@fan.cz', 'JediMaster', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), 'yoda.png'),
('zahradnik@priroda.cz', 'ZelenyPalec', '$2y$10$DummyHashForPassword123xyz...', 1, NOW(), NULL);

-- 3. PŘÍSPĚVKY
INSERT INTO posts (user_id, category_id, title, content, created_at) VALUES
-- TECHNOLOGIE (1)
(7, 1, 'Vyplatí se přechod na Windows 11?', 'Ahoj, mám stále desítky a přemýšlím, jestli upgradovat. Jaké máte zkušenosti?', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 1, 'Nový update PHP 8.3 je venku', 'Přináší spoustu novinek včetně typovaných konstant tříd. Už jste to zkoušeli na serveru?', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(7, 1, 'Jaký vybrat notebook na VŠ?', 'Potřebuju něco lehkého s dlouhou výdrží. Rozpočet do 25k. Nějaké tipy?', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(2, 1, 'Budoucnost AI v programování', 'Myslíte si, že nás Copilot a ChatGPT nahradí, nebo je to jen pomůcka?', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(10, 1, 'Stavba PC do 30 000 Kč', 'Sestavil jsem seznam komponent, mrkněte na to prosím.', DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- GAMING (2)
(5, 2, 'Hledám lidi na Minecraft server', 'Rozjíždíme Enigmaticu 9, hledáme pohodové lidi. Máme dedikovaný server.', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(5, 2, 'Recenze: Baldurs Gate 3', 'Podle mě hra roku. Ten příběh je neskutečný a možnosti taky.', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(3, 2, 'Counter-Strike 2 je rozbitý', 'Hitboxy nefungují, tickrate je divný. Valve, opravte to!', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(11, 2, 'League of Legends komunita je toxická', 'Opět jsem dostal ban za chat, přitom jsem jen radil junglerovi.', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(16, 2, 'Starfield - zklamání nebo pecka?', 'Hraju to už 20 hodin a pořád nevím co si myslet.', DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- SPORT (3)
(8, 3, 'MS v hokeji v Praze', 'Lístky jsou hrozně drahé, ale atmosféra bude určitě super. Kdo jdete?', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(8, 3, 'Sparta vs Slavia - derby', 'Tipovačka na výsledek. Já říkám 2:1 pro domácí.', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(3, 3, 'Jak začít s běháním?', 'Jsem úplný začátečník a po 500 metrech nemůžu dýchat. Jak trénovat?', DATE_SUB(NOW(), INTERVAL 13 DAY)),

-- ZÁBAVA (4)
(9, 4, 'Dune: Part Two - diskuze', 'Byl jsem v kině a ten zvuk byl masivní. Co říkáte na konec?', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(12, 4, 'Jaký seriál na Netflixu?', 'Dokoukal jsem Stranger Things a nevím co dál.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 4, 'Letní festivaly 2024', 'Kam se chystáte? Colours nebo Rock for People?', DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- VĚDA (5)
(2, 5, 'James Webb teleskop nové snímky', 'Ty fotky mlhovin jsou dechberoucí.', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(7, 5, 'Fúze jako zdroj energie', 'Kdy se dočkáme komerčního využití? Za 50 let jako vždy?', DATE_SUB(NOW(), INTERVAL 29 DAY)),

-- LIFESTYLE (6)
(9, 6, 'Digitální nomádství na Bali', 'Pracuju odtud už měsíc a je to pecka. Jen ten internet občas vypadává.', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(4, 6, 'Minimalismus v šatníku', 'Vyhodila jsem polovinu věcí a cítím se lépe.', DATE_SUB(NOW(), INTERVAL 24 DAY)),

-- KUCHYNĚ (7)
(6, 7, 'Pravá italská Carbonara', 'Žádná smetana! Jen žloutky, pecorino, guanciale a pepř. Tady je recept.', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 7, 'Kam na nejlepší burger v Brně?', 'Hledám doporučení na páteční večer.', DATE_SUB(NOW(), INTERVAL 19 DAY)),
(6, 7, 'Domácí kváskový chléb - návod', 'Jak založit kvásek a jak se o něj starat.', DATE_SUB(NOW(), INTERVAL 18 DAY)),

-- UMĚNÍ (8)
(12, 8, 'Můj první pokus o olejomalbu', 'Přikládám fotku, buďte kritičtí.', DATE_SUB(NOW(), INTERVAL 40 DAY)),
(12, 8, 'Výstava v Národní galerii', 'Stojí za to vidět Muchu?', DATE_SUB(NOW(), INTERVAL 39 DAY)),

-- Osobní finance (9)
(10, 9, 'Jak začít s investováním do ETF?', 'Mám pár tisíc navíc a chtěl bych začít, ale nevím kde. Broker, platforma?', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 9, 'Inflace a hypotéky', 'Jak se vyrovnat s aktuálními úrokovými sazbami?', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 9, 'Recenze Revolut vs Wise', 'Která platforma je lepší pro mezinárodní platby?', DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Zdraví a fitness (10)
(8, 10, 'Nejlepší cviky na spodní záda', 'Sedavé zaměstnání mi ničí záda, co s tím?', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 10, 'Vegan protein - doporučení', 'Hledám chutný a kvalitní rostlinný protein.', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 10, 'Zlepšení spánku bez léků', 'Jaké jsou vaše tipy a triky pro hluboký spánek?', DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- Cestování (11)
(9, 11, 'Tipy na roadtrip po Skotsku', 'Chystám se na dva týdny. Co nesmím minout?', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(9, 11, 'Jak přežít dlouhý let', 'Tipy na zabavení a pohodlí v ekonomické třídě.', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(11, 11, 'Nejhorší hotel, ve kterém jsem kdy byl', 'Fotky k tomu.', DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- Politika (12)
(1, 12, 'Volební průzkumy k blížícím se volbám', 'Jaké jsou aktuální preference stran?', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(11, 12, 'Měl by být zaveden korespondenční hlas?', 'Diskuze pro a proti.', DATE_SUB(NOW(), INTERVAL 11 DAY)),
(2, 12, 'Evropská unie: Výhody a nevýhody', 'Kde vidíte největší přínos a kde problémy?', DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- DIY/Hobby (13)
(7, 13, 'Stavba police z palet - návod', 'Potřebujete jen vrtačku a pilu.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(12, 13, 'Jak začít s pletením (i pro muže)', 'Není to složité, chce to jen trpělivost.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 13, 'Renovace starého nábytku', 'Před a po fotky křesla.', DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- Auto-moto (14)
(13, 14, 'Jaký olej do Škoda Octavia III?', 'Mám najeto 150k, co doporučujete?', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(13, 14, 'Elektromobilita - slepá ulička?', 'Diskuze o budoucnosti spalovacích motorů.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 14, 'Výměna brzdových destiček doma', 'Zvládne to laik nebo radši servis?', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(13, 14, 'Nejlepší trasy pro motorkáře v ČR', 'Kde jsou pěkné zatáčky a dobrý asfalt?', DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Příroda (15)
(17, 15, 'Kam na výlet v Českém ráji?', 'Hledám trasu pro rodinu s dětmi.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(17, 15, 'Pěstování rajčat na balkoně', 'Jakou odrůdu vybrat pro začátečníka?', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(9, 15, 'Pozorování ptáků - vybavení', 'Jaký dalekohled koupit?', DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- Knihy (16)
(14, 16, 'Nejlepší sci-fi knihy roku 2023', 'Co vás letos nejvíc zaujalo?', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(14, 16, 'Čtečky knih - Kindle nebo PocketBook?', 'Rozhoduji se mezi těmito dvěma.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(4, 16, 'Harry Potter - stále aktuální?', 'Čtete to znovu v dospělosti?', DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Historie (17)
(15, 17, 'Příčiny první světové války', 'Byl atentát v Sarajevu skutečným důvodem?', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(15, 17, 'Starověký Řím - každodenní život', 'Jak se žilo běžným lidem?', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(12, 17, 'Dokumenty o 2. světové válce', 'Doporučte něco na Netflixu.', DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- Sci-Fi a Fantasy (18)
(16, 18, 'Star Wars: Acolyte - očekávání', 'Těšíte se na nový seriál?', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(16, 18, 'Pán Prstenů vs Hra o Trůny', 'Který svět je lépe propracovaný?', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 18, 'Warhammer 40k lore', 'Kde začít s knihami?', DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- FILLER (Doplnění)
(1, 1, 'Klávesnice se zasekla', 'Nemůžu psát mezery, pomoc.', DATE_SUB(NOW(), INTERVAL 50 DAY)),
(3, 3, 'Kolo vs Auto ve městě', 'Věčné téma.', DATE_SUB(NOW(), INTERVAL 49 DAY)),
(5, 2, 'Retro hraní na starém PC', 'DosBox je záchrana.', DATE_SUB(NOW(), INTERVAL 48 DAY)),
(6, 7, 'Připálená rýže', 'Jak vyčistit hrnec?', DATE_SUB(NOW(), INTERVAL 47 DAY)),
(8, 3, 'Jóga pro muže', 'Není to jen pro holky, fakt to pomáhá na záda.', DATE_SUB(NOW(), INTERVAL 46 DAY)),
(11, 4, 'Vtip dne', 'Přijde kůň do baru...', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(2, 5, 'Klimatická změna', 'Data mluví jasně.', DATE_SUB(NOW(), INTERVAL 44 DAY)),
(4, 6, 'Meditace ráno nebo večer?', 'Kdy je to lepší?', DATE_SUB(NOW(), INTERVAL 43 DAY)),
(10, 1, 'Python nebo Java?', 'Co se učit jako první jazyk?', DATE_SUB(NOW(), INTERVAL 42 DAY)),
(5, 2, 'Steam Summer Sale', 'Moje peněženka pláče.', DATE_SUB(NOW(), INTERVAL 41 DAY)),
(1, 9, 'Druhý pilíř penzijního spoření', 'Má to smysl?', DATE_SUB(NOW(), INTERVAL 35 DAY)),
(4, 10, 'Makroživiny', 'Jak si je správně spočítat?', DATE_SUB(NOW(), INTERVAL 34 DAY)),
(9, 11, 'Chorvatsko s dětmi', 'Máte tipy na klidné pláže?', DATE_SUB(NOW(), INTERVAL 33 DAY)),
(2, 12, 'Prezidentské pravomoci', 'Co všechno prezident může a nemůže?', DATE_SUB(NOW(), INTERVAL 32 DAY)),
(7, 13, 'Pájení elektroniky', 'Základy pro začátečníky.', DATE_SUB(NOW(), INTERVAL 31 DAY));

-- 4. KOMENTÁŘE
INSERT INTO comments (post_id, user_id, parent_comment_id, content, created_at) VALUES
-- Komentáře k Minecraftu (Post ID 6)
(6, 10, NULL, 'Já bych šel! Hraju mody už dlouho.', NOW()),
(6, 5, 1, 'Super, pošli mi DM, pošlu ti IP adresu.', NOW()), -- Odpověď
(6, 11, NULL, 'Enigmatica je moc náročná, na to nemám PC.', NOW()),

-- Komentáře k Windows 11 (Post ID 1)
(1, 7, NULL, 'Zůstaň u desítek, 11 jsou zabugované.', NOW()),
(1, 2, NULL, 'Já jsem spokojený, je to hezčí a rychlejší.', NOW()),
(1, 7, 4, 'Rychlejší? To těžko.', NOW()), -- Hádka

-- Komentáře k Carbonara (Post ID 20)
(20, 3, NULL, 'Díky za recept, konečně vím, jak se to dělá správně.', NOW()),
(20, 11, NULL, 'Já tam dávám smetanu a eidam a chutná mi to!', NOW()),
(20, 6, 7, 'To už ale není Carbonara, ale sýrová omáčka...', NOW()),

-- Komentáře k Investování (Post ID 29)
(29, 1, NULL, 'Doporučuji Degiro nebo XTB. Pro začátek jdi jen do globálního ETF.', NOW()),
(29, 10, 9, 'A co Fio? Je to české.', NOW()),
(29, 2, 9, 'Fio je drahé, lepší je zahraniční broker.', NOW()),

-- Různé další komentáře
(7, 3, NULL, 'Taky si myslím, ta hra je masterpiece.', NOW()),
(8, 5, NULL, 'Souhlas, CS2 potřebuje ještě hodně práce.', NOW()),
(11, 8, NULL, 'Sparta do toho!', NOW()),
(12, 3, NULL, 'Slavia je lepší, uvidíš.', NOW()),
(13, 4, NULL, 'Dune byla skvělá, Hans Zimmer nezklamal.', NOW()),
(15, 9, 14, 'Přesně, ten soundtrack dělal 50% filmu.', NOW()),
(42, 12, NULL, 'Moc pěkné! Líbí se mi ty barvy.', NOW()),
(49, 7, NULL, 'Python je jednodušší na začátek.', NOW()),
(48, 1, NULL, 'Java tě naučí lepší OOP návyky.', NOW()),
(34, 9, NULL, 'Chce to hodně repelentu a pozor na opice.', NOW()),
(55, 13, NULL, 'Jedině 5W-30, nic jiného tam nelej.', NOW()),
(60, 17, NULL, 'Prachovské skály jsou klasika, ale bývá tam nával.', NOW()),
(63, 14, NULL, 'Project Hail Mary od Andyho Weira, to musíš přečíst!', NOW());


-- 5. HLASOVÁNÍ (VOTES)
INSERT INTO post_votes (post_id, user_id, vote_type_id, created_at) VALUES
(6, 10, 1, NOW()), (6, 3, 1, NOW()), (6, 2, 1, NOW()), -- Gaming post má lajky
(1, 7, 2, NOW()), -- Windows post má dislike
(20, 3, 1, NOW()), (20, 4, 1, NOW()), (20, 12, 1, NOW()), -- Jídlo má lajky
(9, 1, 2, NOW()), (9, 2, 2, NOW()), -- Toxic LoL post má dislajky
(7, 7, 1, NOW()), (7, 8, 1, NOW()),
(10, 1, 1, NOW()), (10, 2, 1, NOW()), (10, 3, 1, NOW()), (10, 4, 1, NOW()), -- Hokej post má hodně lajků
(29, 5, 1, NOW()), (29, 6, 1, NOW()), (29, 9, 1, NOW()), (29, 10, 1, NOW()), -- Finance lajky
(32, 12, 1, NOW()), (32, 1, 1, NOW()), (32, 2, 1, NOW()), -- Politika lajky
(55, 13, 1, NOW()), (55, 7, 1, NOW()),
(63, 16, 1, NOW()), (63, 14, 1, NOW());

-- 6. BAN PRO TESTOVÁNÍ
INSERT INTO user_bans (user_id, banned_by, reason, banned_at, expires_at) VALUES
(11, 1, 'Opakované urážky v komentářích a trolling.', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 6 DAY));