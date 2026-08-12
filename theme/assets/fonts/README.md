# Fonts

Self-hosted, so the site never contacts `fonts.gstatic.com`. `inc/assets.php`
also strips the preconnect hint core adds for that host.

| File | Family | Licence |
| --- | --- | --- |
| `caprasimo-400.woff2` | Caprasimo 400 | SIL OFL 1.1, `OFL-Caprasimo.txt` |
| `figtree-variable.woff2` | Figtree 300-900 roman | SIL OFL 1.1, `OFL-Figtree.txt` |
| `figtree-variable-italic.woff2` | Figtree 300-900 italic | SIL OFL 1.1, `OFL-Figtree.txt` |

Fetched from Google Fonts 2026-08-12. The licence files travel with the fonts
because OFL 1.1 requires it on redistribution.

**These are the `latin` subset only.** Google serves `latin` and `latin-ext` as
complementary unicode ranges rather than nested ones, and `theme.json` declares a
single `src` per face, so one subset had to be chosen. Basic English text is
covered. Accented characters outside `U+0000-00FF`, which is most of
`latin-ext`, fall back to the next family in the stack. If the site starts
publishing names or loanwords that need them, add the `latin-ext` file as a
second `src` entry with its own `unicode-range` rather than replacing this one.
