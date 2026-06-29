# Presentazione Lumen Spirits

Slide Beamer del progetto di Tecnologie Web (UniPD, A.A. 2025/2026).

## Compilazione

```bash
pdflatex lumen-spirits.tex
pdflatex lumen-spirits.tex   # seconda passata per indice/sezioni
```

Richiede una distribuzione LaTeX standard (TeX Live, MikTeX, MacTeX) con i pacchetti `beamer`, `babel-italian`, `booktabs`, `tikz`, `listings`, `hyperref`.

## Cosa va personalizzato prima della consegna

Cerca i commenti `% TODO:` nel file `.tex`:

- nomi, matricole e compiti reali dei membri del gruppo (frontespizio + slide 20)
- logo UniPD in `img/logo-unipd.png`
- diagramma blueprint draw.io in `img/blueprint.png`
- eventuali screenshot della Home, del catalogo o del pannello admin

## Struttura

Documento relazione (article) di ~16 pagine con frontespizio + Sommario + 9 sezioni numerate (Abstract, Analisi del sito, Struttura e Design, Progettazione, Sviluppo, SEO, Accessibilita, Sviluppi futuri, Organizzazione del lavoro). Layout mimato sulla relazione ATDS di riferimento.
