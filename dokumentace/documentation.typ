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

Cílem práce bylo navrhnout a vytvořit robustní webový systém na základě striktního objektově orientovaného návrhu. Maximální důraz byl kladen na čistou architekturu, logické oddělení jednotlivých vrstev aplikace a smysluplné uplatnění vybraných návrhových vzorů, které zajišťují dobrou udržovatelnost a budoucí rozšiřitelnost zdrojového kódu.

= Použité technologie \ a nástroje
Backendová část aplikace je plně implementována v jazyce *PHP*. Z edukačních důvodů nebyl využit žádný zavedený komplexní framework, ale na základě vlastních UML návrhů byl vytvořen nízkoúrovňový systém postavený na principech architektonického vzoru MVC.

K trvalému uchovávání dat slouží relační databáze *MySQL*, jejíž struktura vychází z navrženého doménového modelu. Komunikace mezi aplikační vrstvou a databází probíhá výhradně prostřednictvím vestavěného rozhraní *PDO*. Hlavním důvodem pro toto technologické rozhodnutí je nativní podpora parametrizovaných dotazů, jež tvoří bezpečnostní standard proti SQL Injection.

Pro prezentační vrstvu (View) byl nasazen šablonovací systém *Twig*. Jeho nasazení zajišťuje striktní oddělení prezentační logiky od aplikační. Systém Twig navíc ze své podstaty automaticky escapuje výstup, čímž preventivně chrání aplikaci před útoky typu XSS. Uživatelské rozhraní je pro zvýšení plynulosti doplněno o asynchronní požadavky zpracovávané na straně klienta pomocí JavaScriptu.

= Architektura aplikace \ (Model-View-Controller)
Celkový návrh systému je založen na prověřeném architektonickém vzoru *MVC*, doplněném o komponentu Front Controller.

Vstupním bodem do aplikace je konfigurační soubor `index.php`. Ten zachytává HTTP požadavky, inicializuje session a deleguje zpracování instanci třídy `Router`. Tato třída analyzuje URL parametry a na základě zjištěných dat dynamicky vytváří instanci odpovídajícího kontroleru.

*Vrstva kontrolerů* plní roli řídící a validační logiky. Přijímá vstupní data, ověřuje uživatelská oprávnění, komunikuje s modely pro čtení nebo zápis dat a sestavuje datový model pro vrstvu View. Veškeré kontrolery v aplikaci dědí z abstraktní třídy `BaseController`, jež poskytuje sdílené servisní metody pro vykreslování šablon.

*Vrstva modelů* zajišťuje přístup k databázi a přímo implementuje bussiness logiku. Třídy jako `Post`, `User` či `Category` dědí z předlohové třídy `BaseModel`. Tato základní třída zapouzdřuje připojení k databázi a definuje základní metody pro spouštění SQL příkazů. 

= Použité návrhové vzory
Architektura aplikace využívá dva pokročilé návrhové vzory, jejichž konkrétní struktura je zřejmá z implementačních UML diagramů tříd: *Observer* a *Strategy*.

== 1. Vzor Observer pro auditní logování
Pro zachování integrity operací bylo v aplikaci žádoucí zaznamenávat klíčové uživatelské aktivity do logu. Tento problém řeší implementace vzoru Observer. Abstraktní třída `BaseModel` plní roli subjektu, ke kterému lze pomocí metody `attach()` dynamicky připojovat posluchače. Po provedení důležité akce model zavolá svou metodu `notify()`. 

Z diagramu tříd je zřejmé, že implementovaným posluchačem je třída `ActionLogger`, realizující rozhraní `Observer`. Tato třída zpracovává data a zapisuje je do připojeného souboru. Výsledkem vzoru je volná vazba.

== 2. Vzor Strategy pro řazení obsahu
Pro umožnění různých typů zobrazení příspěvků (od nejnovějších vs. podle popularity) byl nasazen návrhový vzor Strategy, čímž se předešlo nadměrnému rozvětvení logiky v kontrolerech. Bylo definováno společné rozhraní `SortingStrategy`. Na tomto rozhraní následně vznikly dvě polymorfní implementace: `NewestSortingStrategy` a `TopSortingStrategy`. 

Při obsluze požadavku kontroler instancuje odpovídající strategii a předá ji jako chování do databázového modelu. Tím je zajištěno snadné přidání nového způsobu řazení do budoucna, čímž je do návrhu aplikován princip Open/Closed.

= Implementace \ vybraných případů užití
Z navrženého *UML diagramu případů užití* bylo pro podrobný implementační návrh a následné programování vybráno pět klíčových scénářů. Pro nejdůležitější z nich byly detailně zpracovány implementační diagramy tříd a *sekvenční diagramy*, modelující interakce mezi vrstvami MVC.

1. *PU01: Registrace a přihlášení uživatele* -- Modul zpracovává identifikaci autora, využívá algoritmus hesel `password_hash` a modeluje správu klientských relací (session).
2. *PU02: Vytvoření nového příspěvku* -- Jádro systému. Pro tento případ užití byl vytvořen podrobný *implementační diagram tříd* demonstrující komunikaci mezi `PostsController`, doménovým modelem `Post` a využití vzoru Observer (`ActionLogger`) při zápisu do databáze.
3. *PU03: Přidání komentáře k příspěvku* -- Modeluje tvorbu obsahu a závislost na cizích klíčích v relační databázi.
4. *PU04: Hodnocení příspěvku (Upvote/Downvote)* -- Z důvodu optimalizace UX je tento případ řešen pomocí AJAX asynchronních dotazů. I pro tento proces byl vypracován *implementační diagram tříd a chování*, který ukazuje odlišný průchod MVC architekturou, kdy nedochází ke generování View (šablony), ale k navrácení strukturované JSON odpovědi.
5. *PU05: Zabanování uživatele administrátorem* -- Modelování správy uživatelských oprávnění (Role-Based Access Control). Operace je závislá na stavovém modelu uživatele.

= Závěr
Výsledná práce naplňuje veškeré požadavky předmětu na modelování a návrh softwaru. Veškerý zdrojový kód úzce vychází ze zpracovaného UML návrhu. Účelná integrace návrhových vzorů Observer a Strategy zajistila vysokou modularitu systému. Softwarová architektura a implementační přístup založený na formálních vizuálních modelech tak poskytly stabilní základ pro funkční moderní webovou aplikaci chráněnou proti běžným zranitelnostem.