#set page(paper: "a4", margin: (x: 2.5cm, y: 2.5cm))
#set page(numbering: "1")
#set text(lang: "cs")

#show heading: set text(size: 16pt, weight: "bold")


#v(2em)
#align(center)[
  #text("Legenda datových typů použitých v doménovém modelu:")
  #table(
    columns: (100pt, 200pt),
    [*String*], [textový řetězec],
    [*int*], [celé číslo],
    [*boolean*], [pravdivostní hodnota],
    [*datetime*], [datum a čas]
  )
]


#pagebreak()

= Specifikace případů užití

#align(center)[
  #table(
    columns : (130pt, 1fr),
    align: (left, left),
    stroke: 0.5pt + black,
    fill: (col, row) => if col == 0 {luma(245)} else {none},
    [*Identifikátor a název*], [*PU01: Registrace a přihlášení uživatele*],
    [*Primární aktér*], [Neregistrovaný uživatel (host)],
    [*Vstupní podmínky*], [Uživatel se nachází na webu aplikace a není přihlášen],
    [*Výstupní podmínky*], [Uživatel má vytvořený účet, je autentizován systémem a přesměrován na hlavní stránku aplikace],
    [*Hlavní tok*], [
      + Aktér klikne na tlačítko "Registrovat"
      + Systém zobrazí formulář pro registraci
      + Aktér vyplní registrační údaje a odešle je
      + Systém zvaliduje údaje a ověří dostupnost uživatelského jména/e-mailu
      + Systém vytvoří nový uživatelský účet
      + Systém přihlásí uživatele do aplikace
      + Systém přesměruje uživatele na hlavní přehled příspěvků
    ],
    [*Alternativní toky*], [
      - 4a. Nevalidní vstupní data: Systém zobrazí chybovou hlášku a vrací se ke kroku 2
      - 5a. Duplicitní údaje: Systém upozorní, že e-mail/jméno je již obsazeno, vrací se ke kroku 2
      - 5b. Neshoda hesel: Systém zobrazí chybovou hlášku a vrací se ke kroku 2
    ]
  )
]

#pagebreak()

#align(center)[
  #table(
    columns : (130pt, 1fr),
    align: (left, left),
    stroke: 0.5pt + black,
    fill: (col, row) => if col == 0 {luma(245)} else {none},
    [*Identifikátor a název*], [*PU02: Vytvoření nového příspěvku*],
    [*Primární aktér*], [Registrovaný uživatel],
    [*Vstupní podmínky*], [Uživatel je přihlášen, nemá omezení přístupu (ban)],
    [*Výstupní podmínky*], [Příspěvek je vytvořen, asociován s kategorií a autorem a publikován v systému],
    [*Hlavní tok*], [
      + Zahrnuje případ užití "Ověření přihlášení"
      + Uživatel zvolí možnost vytvoření nového příspěvku
      + Systém zobrazí formulář (výběr kategorie, titulek, obsah)
      + Uživatel vyplní data a odešle je k publikaci
      + Systém provede validaci povinných polí
      + Systém uloží nový příspěvek a přiřadí mu aktuální čas a autora
      + Systém zobrazí detail nově vytvořeného příspěvku
    ],
    [*Alternativní toky*], [
      - 5a. Neúplná data: Systém detekuje prázdná pole, zobrazí varování a vrací se ke kroku 3
    ]
  )
]

#pagebreak()

#align(center)[
  #table(
    columns : (130pt, 1fr),
    align: (left, left),
    stroke: 0.5pt + black,
    fill: (col, row) => if col == 0 {luma(245)} else {none},
    [*Identifikátor a název*], [*PU03: Přidání komentáře k příspěvku*],
    [*Primární aktér*], [Registrovaný uživatel],
    [*Vstupní podmínky*], [Uživatel je přihlášen a nachází se na detailu konkrétního příspěvku],
    [*Výstupní podmínky*], [Komentář je uložen v systému a zobrazen v diskusním vlákně pod příspěvkem],
    [*Hlavní tok*], [
      + Zahrnuje případ užití "Ověření přihlášení"
      + Uživatel zadá text komentáře do příslušného pole
      + Uživatel potvrdí odeslání komentáře
      + Systém zvaliduje délku a obsah komentáře
      + Systém uloží komentář a propojí jej s příspěvkem a autorem
      + Systém aktualizuje zobrazení diskuse u příspěvku
    ],
    [*Alternativní toky*], [
      - 4a. Neplatný obsah: Systém upozorní uživatele na chybu (např. prázdný text) a vrací se ke kroku 2
    ]
  )
]

#pagebreak()

#align(center)[
  #table(
    columns : (130pt, 1fr),
    align: (left, left),
    stroke: 0.5pt + black,
    fill: (col, row) => if col == 0 {luma(245)} else {none},
    [*Identifikátor a název*], [*PU04: Hodnocení příspěvku*],
    [*Primární aktér*], [Registrovaný uživatel],
    [*Vstupní podmínky*], [Uživatel je přihlášen a má zobrazen příspěvek k hodnocení],
    [*Výstupní podmínky*], [Hlas uživatele je započítán do celkového hodnocení příspěvku],
    [*Hlavní tok*], [
      + Zahrnuje případ užití "Ověření přihlášení"
      + Uživatel zvolí typ hlasu (kladný/záporný) u příspěvku
      + Systém ověří, zda uživatel již daný příspěvek hodnotil
      + Systém zaznamená aktuální hlas uživatele
      + Systém přepočítá a vizuálně aktualizuje celkové skóre příspěvku
    ],
    [*Alternativní toky*], [
      - 3a. Existující identický hlas: Systém předchozí hlas zruší (odebere hodnocení)
      - 3b. Existující opačný hlas: Systém změní předchozí hodnocení na aktuálně zvolené
    ]
  )
]

#pagebreak()

#align(center)[
  #table(
    columns : (130pt, 1fr),
    align: (left, left),
    stroke: 0.5pt + black,
    fill: (col, row) => if col == 0 {luma(245)} else {none},
    [*Identifikátor a název*], [*PU05: Zabanování uživatele administrátorem*],
    [*Primární aktér*], [Administrátor],
    [*Vstupní podmínky*], [Administrátor je autentizován a disponuje příslušnými oprávněními],
    [*Výstupní podmínky*], [Cílový uživatel má omezený přístup k interaktivním prvkům aplikace],
    [*Hlavní tok*], [
      + Zahrnuje případ užití "Ověření přihlášení"
      + Administrátor vybere v rozhraní správy konkrétního uživatele
      + Administrátor zvolí možnost udělení banu
      + Systém zobrazí formulář pro specifikaci důvodu a trvání banu
      + Administrátor potvrdí zadané parametry
      + Systém uloží záznam o omezení účtu uživatele
      + Systém zneplatní aktuální přístup zabanovaného uživatele (pokud je aktivní)
    ],
    [*Alternativní toky*], [
      - 5a. Neúplná specifikace: Chybí důvod nebo délka banu, systém se vrací ke kroku 4
    ]
  )
]