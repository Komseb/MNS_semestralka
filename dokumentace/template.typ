// FAV FASThesis Template for Typst
// Replicated from the official LaTeX template (fasthesis.cls)
// Author: Antigravity

#let zcu-blue = rgb("#C99700")
#let fav-yellow = rgb("#C99700")
#let uwb-gray = rgb("#7E8287")

#let fasthesis(
  title: "",
  author: (
    firstname: "",
    surname: "",
    degrees-before: "",
    degrees-after: "",
  ),
  supervisor: "",
  type: "ba", // ba, ma, phd, sem, oth
  department: "kiv", // kiv, kky, kma, kme, kfy, kgm
  language: "cs",
  abstract-cs: none,
  abstract-en: none,
  keywords: (),
  acknowledgement: none,
  declaration: none,
  assignment-pdf: none,
  stag-work-id: "",
  body
) = {
  let lang-cs = (language == "cs")
  
  let dept-name = (
    kiv: if lang-cs { "Katedra informatiky a výpočetní techniky" } else { "Department of Computer Science and Engineering" },
    kky: if lang-cs { "Katedra kybernetiky" } else { "Department of Cybernetics" },
    kma: if lang-cs { "Katedra matematiky" } else { "Department of Mathematics" },
    kme: if lang-cs { "Katedra mechaniky" } else { "Department of Mechanics" },
    kfy: if lang-cs { "Katedra fyziky" } else { "Department of Physics" },
    kgm: if lang-cs { "Katedra geomatiky" } else { "Department of Geomatics" },
  ).at(department, default: "Katedra")

  let type-text = (
    ba: if lang-cs { "Bakalářská práce" } else { "Bachelor's Thesis" },
    ma: if lang-cs { "Diplomová práce" } else { "Master's Thesis" },
    phd: if lang-cs { "Disertační práce" } else { "Doctoral Dissertation" },
    sem: if lang-cs { "Semestrální práce" } else { "Seminar Work" },
    oth: if lang-cs { "Práce" } else { "Thesis" },
  ).at(type, default: "Práce")

  let university = if lang-cs { "ZÁPADOČESKÁ UNIVERZITA V PLZNI" } else { "UNIVERSITY OF WEST BOHEMIA" }
  let faculty = if lang-cs { "FAKULTA APLIKOVANÝCH VĚD" } else { "FACULTY OF APPLIED SCIENCES" }

  set document(title: title, author: author.firstname + " " + author.surname)
  
  // Page setup
  set page(
    paper: "a4",
    margin: (left: 37mm, right: 37mm, top: 33mm, bottom: 40mm),
    header: context {
      if counter(page).get().first() > 1 {
        set text(size: 9pt, fill: uwb-gray)
        grid(
          columns: (1fr, 1fr),
          [#type-text],
          align(right)[#title]
        )
        v(-0.6em)
        line(length: 100%, stroke: 0.5pt + uwb-gray)
      }
    },
    footer: context {
      if counter(page).get().first() > 1 {
        align(center)[#counter(page).display()]
      }
    }
  )

  // Fonts
  let title-font = ("Titillium Web", "Inter", "Arial", "sans-serif")
  let body-font = ("Cochineal", "Georgia", "Libertinus Serif", "serif")
  
  set text(font: body-font, size: 12pt, lang: language)
  set par(justify: true, leading: 0.65em)
  set heading(numbering: "1.1.1")
  
  // Headings
  show heading: it => {
    set text(font: title-font, weight: "bold", fill: zcu-blue)
    if it.level == 1 {
      pagebreak(weak: true)
      v(3em)
      if it.numbering != none {
        grid(
          columns: (auto, 1fr, auto),
          align: (left, horizon, right),
          gutter: 1.5em,
          text(size: 24pt)[#it.body],
          line(length: 100%, stroke: 1.5pt + zcu-blue),
          rect(fill: zcu-blue, inset: (x: 18pt, y: 12pt))[
            #text(size: 38pt, fill: white, weight: "bold")[#counter(heading).display(it.numbering)]
          ]
        )
      } else {
        text(size: 28pt)[#it.body]
      }
      v(1.5em)
    } else if it.level == 2 {
      v(1.5em)
      text(size: 21pt)[#it]
      v(0.8em)
    } else if it.level == 3 {
      v(1.2em)
      text(size: 17pt)[#it]
      v(0.6em)
    } else {
      v(1em)
      it
    }
  }

  // --- 1. Titulní strana ---
  page(margin: 0pt, header: none, footer: none)[
    // Background image
    #place(top + left)[
      #image("img/background-" + department + ".pdf", width: 100%, height: 100%, fit: "cover")
    ]
    
    // Header
    #let logo-lang = if language == "cs" { "cz" } else { "en" }
    #place(top + left, dx: 37mm, dy: 33mm, block(width: 210mm - 74mm)[
      #image("img/" + department + "-cmyk-" + logo-lang + ".pdf", width: 100%)
    ])
    
    // Middle section
    #place(center + top, dy: 125mm, block(width: 100%)[
      #align(left)[
        #h(8.8em)
        #set text(font: title-font, size: 22pt, weight: "bold", fill: fav-yellow)
        #type-text
      ]
      
      #v(0.8em)
      
      #rect(width: 100%, fill: fav-yellow, stroke: none, inset: (top: 3em, bottom: 3em))[
        #align(center)[
          #set text(font: title-font, fill: white)
          #text(size: 28pt, weight: "bold")[#upper(title)] \
          #v(1.5em)
          #text(size: 18pt, weight: "semibold")[#author.firstname #author.surname]
        ]
      ]
    ])
    
    // Bottom section
    #place(bottom + left, dx: 37mm, dy: -20mm, block(width: 210mm - 74mm)[
      #grid(
        columns: (1fr, 1fr),
        align(left)[
          #set text(font: title-font, size: 11pt, weight: "bold", fill: fav-yellow)
          #if lang-cs { "PLZEŇ" } else { "PILSEN" }
        ],
        align(right)[
          #set text(font: title-font, size: 11pt, weight: "bold", fill: fav-yellow)
          #datetime.today().year()
        ]
      )
    ])
  ]

  // --- 2. Prohlášení a copyright ---
  if declaration != none {
    pagebreak()
    v(0fr)
    [
      == #if lang-cs { "Prohlášení" } else { "Declaration" }
      #declaration
      #v(2em)
      #grid(
        columns: (1fr, 1fr),
        [#if lang-cs { "V Plzni dne:" } else { "In Pilsen, date:" } #datetime.today().display("[day]. [month]. [year]")],
        align(right)[..................................\ #if lang-cs { "podpis" } else { "signature" }]
      )
    ]
  }
  
  

  // --- 4. Abstrakt a klíčová slova ---
  if abstract-cs != none or abstract-en != none {
    pagebreak()
    [
      == #if lang-cs { "Abstrakt" } else { "Abstract" }
      #if abstract-cs != none [
        #abstract-cs
      ]
      
      #if keywords.len() > 0 [
        #v(1em)
        *#if lang-cs { "Klíčová slova" } else { "Keywords" }:* #keywords.join(", ")
      ]

      #if abstract-en != none [
        #v(2em)
        == Abstract
        #abstract-en
      ]
    ]
  }

  // --- 3. Poděkování ---
  if acknowledgement != none {
    pagebreak()
    v(0fr)
    [
      == #if lang-cs { "Poděkování" } else { "Acknowledgement" }
      #acknowledgement
    ]
  }

  // --- 5. Obsah ---
  pagebreak()
  outline(title: if lang-cs { "Obsah" } else { "Contents" }, indent: auto)
  
  // --- 6. Body ---
  pagebreak()
  counter(page).update(1)
  body
}
