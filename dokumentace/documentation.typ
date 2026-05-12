#import "template.typ": fasthesis


#show: fasthesis.with(
  title: "Webová sociální síť",
  author: (
    firstname: "Jaroslav",
    surname: "Vaněček - A24B0284P",
    degrees-before: "",
    degrees-after: ""
  ),
  type: "sem",
  department: "kiv",
  language: "cs",
)

= Úvod a účel aplikace \ 
Aplikace "Ziggid" je koncipována jako webová sociální síť a diskusní platforma, funkčně inspirovaná zavedenými komunitními systémy typu Reddit. Hlavním účelem aplikace je umožnit uživatelům sdílet obsah formou textových a obrazových příspěvků, které jsou logicky řazeny do tematických kategorií. Nedílnou součástí systému je následná možnost o tomto obsahu diskutovat prostřednictvím komentářů. Klíčovým prvkem komunitního fungování platformy je uživatelské hodnocení příspěvků (upvote/downvote). Tento mechanismus zajišťuje přirozenou filtraci obsahu, kdy jsou nejkvalitnější a nejoblíbenější příspěvky dynamicky zvýrazňovány pro ostatní uživatele.

Cílem práce bylo navrhnout a vytvořit robustní webový systém na základě striktního objektově orientovaného návrhu. Maximální důraz byl kladen na čistou architekturu, logické oddělení jednotlivých vrstev aplikace a celkovou flexibilitu navrženého řešení, jež zajišťuje dobrou udržovatelnost a budoucí rozšiřitelnost zdrojového kódu.

Kompletní zdrojové kódy projektu, včetně historie vývoje, jsou k dispozici v repozitáři na platformě GitHub: #link("https://github.com/komseb/mns_semestralka")[github.com/komseb/mns_semestralka].

= Použité technologie \ a nástroje
Backendová část aplikace je plně implementována v jazyce *PHP*. Z edukačních důvodů nebyl využit žádný zavedený komplexní framework, ale na základě vlastních UML návrhů byl vytvořen nízkoúrovňový systém postavený na principech architektonického vzoru MVC.

K trvalému uchovávání dat slouží relační databáze *MySQL*, jejíž struktura vychází z navrženého doménového modelu. Komunikace mezi aplikační vrstvou a databází probíhá výhradně prostřednictvím vestavěného rozhraní *PDO*. Hlavním důvodem pro toto technologické rozhodnutí je nativní podpora parametrizovaných dotazů, jež tvoří bezpečnostní standard proti SQL Injection.

Pro prezentační vrstvu (View) byl nasazen šablonovací systém *Twig*. Jeho nasazení zajišťuje striktní oddělení prezentační logiky od aplikační. Systém Twig navíc ze své podstaty automaticky escapuje výstup, čímž preventivně chrání aplikaci před útoky typu XSS. Uživatelské rozhraní je pro zvýšení plynulosti doplněno o asynchronní požadavky zpracovávané na straně klienta pomocí JavaScriptu.

= Architektura aplikace \ (Model-View-Controller)
Celkový návrh systému je založen na prověřeném architektonickém vzoru *MVC*, doplněném o komponentu Front Controller.

Vstupním bodem do aplikace je konfigurační soubor `index.php`. Ten zachytává HTTP požadavky, inicializuje session a deleguje zpracování instanci třídy `Router`. Tato třída analyzuje URL parametry a na základě zjištěných dat dynamicky vytváří instanci odpovídajícího kontroleru.

*Vrstva kontrolerů* plní roli řídící a validační logiky. Přijímá vstupní data, ověřuje uživatelská oprávnění, komunikuje s modely pro čtení nebo zápis dat a sestavuje datový model pro vrstvu View. Veškeré kontrolery v aplikaci dědí z abstraktní třídy `BaseController`, jež poskytuje sdílené servisní metody pro vykreslování šablon.

*Vrstva modelů* zajišťuje přístup k databázi a přímo implementuje bussiness logiku. Třídy jako `Post`, `User` či `Category` dědí z předlohové třídy `BaseModel`. Tato základní třída zapouzdřuje připojení k databázi a definuje základní metody pro spouštění SQL příkazů. 

#figure(
  image("component_diagram.png", width: 95%),
  caption: [Diagram komponent ilustrující architekturu aplikace]
)

= Vybrané aplikační \ mechanismy
Během vývoje a modelování architektury bylo nutné vyřešit několik specifických implementačních problémů. Konkrétní návrh je zřejmý z přiložených UML diagramů tříd.

#figure(
  image("implementation_diagram.png", width: 95%),
  caption: [Částečný implementační diagram tříd]
)

== Zpracování událostí
Pro zachování integrity operací je žádoucí odděleně zaznamenávat klíčové uživatelské aktivity (např. hlasování) do logu. Bylo navrženo řešení, kdy `BaseModel` udržuje seznam závislých objektů, které lze dynamicky připojit přes metodu `attach()`. Při provedení důležité akce se zavolá `notify()`, čímž model notifikuje všechny zájemce. Samotný zápis logu pak obstarává nezávislá třída `ActionLogger`. Primární logika se tak nemusí vůbec starat o vedlejší operace a celý přístup tak přirozeně funguje na principu pozorovatele.

== Algoritmus řazení obsahu
Dalším požadavkem bylo umožnit různé způsoby řazení příspěvků (nejnovější vs. nejoblíbenější). Aby se zamezilo komplikovanému větvení kódu přímo v kontrolerech, byl algoritmus řazení vyčleněn do samostatných tříd pod společné rozhraní. Při vyřizování požadavku aplikace dynamicky vybere vhodný typ řazení a předá ho jako parametr rovnou do databázového modelu. Přístup, kdy je algoritmus předáván zvenčí jako zaměnitelná strategie chování, výrazně zjednodušuje údržbu a případné přidávání nových řazení do budoucna.

= Implementace \ vybraných případů užití
Z navrženého *UML diagramu případů užití* bylo pro podrobný implementační návrh a následné programování vybráno pět klíčových scénářů. Pro nejdůležitější z nich byly detailně zpracovány implementační diagramy tříd a *sekvenční diagramy*, modelující interakce mezi vrstvami MVC.

1. *PU01: Registrace a přihlášení uživatele* -- Modul zpracovává identifikaci autora, využívá algoritmus hesel `password_hash` a modeluje správu klientských relací (session).
2. *PU02: Vytvoření nového příspěvku* -- Jádro systému. Pro tento případ užití byl vytvořen podrobný *implementační diagram tříd* demonstrující komunikaci mezi `PostsController`, doménovým modelem `Post` a zapojení mechanismu událostí s posluchačem (`ActionLogger`) při zápisu do databáze.

#figure(
  image("class_diagram_pu02.png", width: 90%),
  caption: [Implementační diagram tříd pro případ užití PU02]
)

#figure(
  image("seq_pu02.png", width: 90%),
  caption: [Sekvenční diagram interakcí pro vytvoření nového příspěvku (PU02)]
)
3. *PU03: Přidání komentáře k příspěvku* -- Modeluje tvorbu obsahu a závislost na cizích klíčích v relační databázi.
4. *PU04: Hodnocení příspěvku (Upvote/Downvote)* -- Z důvodu optimalizace UX je tento případ řešen pomocí AJAX asynchronních dotazů. I pro tento proces byl vypracován *implementační diagram tříd a chování*, který ukazuje odlišný průchod MVC architekturou, kdy nedochází ke generování View (šablony), ale k navrácení strukturované JSON odpovědi.

#figure(
  image("class_diagram_pu04.png", width: 90%),
  caption: [Implementační diagram tříd pro případ užití PU04]
)

#figure(
  image("seq_pu04.png", width: 90%),
  caption: [Sekvenční diagram interakcí pro hodnocení příspěvku (PU04)]
)

5. *PU05: Zabanování uživatele administrátorem* -- Modelování správy uživatelských oprávnění (Role-Based Access Control). Operace je závislá na stavovém modelu uživatele.

= Závěr
Výsledná práce naplňuje veškeré požadavky předmětu na modelování a návrh softwaru. Zdrojový kód aplikace vychází ze zpracovaných UML návrhů. Flexibilní přístup k řešení dílčích implementačních problémů zajistil dobrou modularitu celého systému a umožnil přirozeně využít v praxi osvědčené architektonické přístupy, aniž by tím utrpěla čitelnost kódu. Softwarová architektura založená na formálních vizuálních modelech tak poskytla stabilní základ pro funkční moderní webovou aplikaci chráněnou proti běžným zranitelnostem.