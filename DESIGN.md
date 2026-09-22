---
version: 1.0
name: Simaan-Water-product-ui
description: >
  Product design system for Simaan Water POS & AI Insight — a light-first commercial
  application UI inspired by Saniti tokens (ink, brand green, mono eyebrows, hairline
  surfaces) but scaled for dense app screens rather than marketing billboards.
  Bootstrap 5.3 + custom SCSS. Primary action color is brand green (#2ea84f).
  IBM Plex Sans for UI; IBM Plex Mono for labels only.

colors:
  primary: "#2ea84f"
  primary-deep: "#0f7a35"
  on-primary: "#ffffff"
  brand: "#2ea84f"
  brand-deep: "#0f7a35"
  ink: "#0b0b0b"
  ink-soft: "#212121"
  graphite: "#353535"
  mute: "#797979"
  ash: "#b9b9b9"
  hairline: "#e6e9e7"
  hairline-soft: "#353535"
  canvas: "#0b0b0b"
  canvas-soft: "#212121"
  canvas-light: "#ffffff"
  canvas-paper: "#f6f7f6"
  page-bg: "#f4f6f5"
  on-canvas-light: "#0b0b0b"
  link-blue: "#0052ef"
  success: "#37cd84"
  error: "#dd0000"
  badge-red-surface: "#ffe3e3"
  badge-red-text: "#b42318"
  badge-amber-surface: #fef7c0
  badge-amber-text: "#93670b"
  badge-green-surface: "#e6fbef"
  badge-green-text: "#057047"
  badge-blue-surface: "#d6efff"
  badge-blue-text: "#0052ef"
  badge-neutral-surface: "#f2f2f2"
  badge-neutral-text: "#353535"
  # Dashboard accent palette (app shell only — never on landing)
  accent-violet: "#6d4aff"
  accent-violet-deep: "#4a2fc7"
  accent-violet-surface: "#efeaff"
  accent-cyan: "#0d9bb5"
  accent-cyan-deep: "#0a7185"
  accent-cyan-surface: "#e0f7fb"
  accent-amber: "#e08600"
  accent-amber-deep: "#a35f00"
  accent-amber-surface: "#fff2d9"
  accent-rose: "#e0447a"
  accent-rose-deep: "#b02659"
  accent-rose-surface: "#ffe7ef"
  accent-indigo: "#2f5fd8"
  accent-indigo-deep: "#1e42a0"
  accent-indigo-surface: "#e6edff"
  accent-teal: "#0f9f7a"
  accent-teal-deep: "#0a7657"
  accent-teal-surface: "#e2f8f1"

typography:
  page-title:
    fontFamily: ibmPlexSans
    fontSize: 22px
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: -0.02em
  page-subtitle:
    fontFamily: ibmPlexSans
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.4
  section-title:
    fontFamily: ibmPlexSans
    fontSize: 15px
    fontWeight: 600
    lineHeight: 1.3
  heading-md:
    fontFamily: ibmPlexSans
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: -0.015em
  heading-sm:
    fontFamily: ibmPlexSans
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.3
  stat-value:
    fontFamily: ibmPlexSans
    fontSize: 26px
    fontWeight: 600
    lineHeight: 1.15
    letterSpacing: -0.03em
  body:
    fontFamily: ibmPlexSans
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.5
  body-sm:
    fontFamily: ibmPlexSans
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.45
  caption:
    fontFamily: ibmPlexSans
    fontSize: 12px
    fontWeight: 400
    lineHeight: 1.4
  mono-eyebrow:
    fontFamily: ibmPlexMono
    fontSize: 10px
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: 0.12em
    textTransform: uppercase
  mono-caps:
    fontFamily: ibmPlexMono
    fontSize: 10px
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: 0.1em
    textTransform: uppercase
  mono-micro:
    fontFamily: ibmPlexMono
    fontSize: 9px
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: 0.1em
    textTransform: uppercase
  button:
    fontFamily: ibmPlexSans
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1
  button-sm:
    fontFamily: ibmPlexSans
    fontSize: 13px
    fontWeight: 500
    lineHeight: 1
  # Landing / marketing scale only (class .landing)
  display-lg:
    fontFamily: ibmPlexSans
    fontSize: clamp(1.75rem, 3.5vw, 2.75rem)
    fontWeight: 400
    letterSpacing: -0.03em
  display-md:
    fontFamily: ibmPlexSans
    fontSize: clamp(1.5rem, 2.5vw, 2.25rem)
    fontWeight: 400

rounded:
  none: 0px
  app-xs: 4px
  app-sm: 6px
  app-md: 8px
  app-lg: 10px
  marketing: 12px
  full: 99999px

spacing:
  xxs: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 20px
  xl: 24px
  xxl: 32px
  page: 20px
  section: 20px

layout:
  sidebar-width: 240px
  topbar-height: 56px
  container-max: 1280px
  page-bg: "{colors.page-bg}"

components:
  button-primary:
    backgroundColor: "{colors.brand}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button}"
    rounded: "{rounded.full}"
    height: 38px
    padding: "0 18px"
  button-brand:
    backgroundColor: "{colors.brand}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button}"
    rounded: "{rounded.full}"
    height: 38px
  button-secondary:
    backgroundColor: "{colors.canvas-light}"
    textColor: "{colors.ink-soft}"
    border: "1px solid {colors.hairline}"
    rounded: "{rounded.full}"
    height: 36px
  button-ghost:
    backgroundColor: transparent
    textColor: "{colors.ink-soft}"
    rounded: "{rounded.full}"
    height: 36px
  button-danger:
    backgroundColor: "{colors.error}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.full}"
    height: 38px
  button-icon:
    size: 32px
    rounded: "{rounded.app-md}"
    border: "1px solid {colors.hairline}"
  button-app-tab:
    backgroundColor: "{colors.canvas-paper}"
    textColor: "{colors.ink-soft}"
    rounded: "{rounded.app-sm}"
    height: 32px
    typography: "{typography.mono-caps}"
  surface-1:
    backgroundColor: "{colors.canvas-light}"
    border: "1px solid {colors.hairline}"
    rounded: "{rounded.app-lg}"
    shadow: "0 1px 2px rgba(11,11,11,0.04), 0 4px 12px -6px rgba(11,11,11,0.08)"
  surface-2:
    backgroundColor: "{colors.canvas-paper}"
    border: "1px solid {colors.hairline}"
    rounded: "{rounded.app-lg}"
  stat:
    extends: surface-1
    padding: "16px 18px"
  stat-value:
    typography: "{typography.stat-value}"
  text-input:
    backgroundColor: "{colors.canvas-light}"
    textColor: "{colors.ink}"
    border: "1px solid #e0e4e2"
    rounded: "{rounded.app-md}"
    height: 40px
    fontSize: 14px
    focusBorder: "{colors.brand}"
    focusRing: "0 0 0 3px rgba(46,168,79,0.12)"
  badge:
    rounded: "{rounded.full}"
    fontSize: 11px
    padding: "3px 8px"
  badge-success:
    backgroundColor: "{colors.badge-green-surface}"
    textColor: "{colors.badge-green-text}"
  badge-error:
    backgroundColor: "{colors.badge-red-surface}"
    textColor: "{colors.badge-red-text}"
  badge-amber:
    backgroundColor: "{colors.badge-amber-surface}"
    textColor: "{colors.badge-amber-text}"
  badge-blue:
    backgroundColor: "{colors.badge-blue-surface}"
    textColor: "{colors.badge-blue-text}"
  badge-filled:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.on-primary}"
  badge-brand:
    backgroundColor: "rgba(46,168,79,0.12)"
    textColor: "{colors.brand-deep}"
  brand-dot:
    backgroundColor: "{colors.brand}"
    size: 8px
    rounded: "{rounded.full}"
  table:
    headerBg: "#f8faf9"
    headerFont: ibmPlexMono
    headerSize: 10px
    cellFontSize: 13px
    cellPadding: "11px 14px"
    rowBorder: "#eef1ef"
  app-sidebar:
    width: "{layout.sidebar-width}"
    backgroundColor: "{colors.canvas-light}"
    border: "1px solid {colors.hairline}"
  nav-item:
    height: 36px
    fontSize: 13px
    rounded: "{rounded.app-md}"
    activeBg: "rgba(46,168,79,0.12)"
    activeColor: "{colors.brand-deep}"
  app-topbar:
    height: "{layout.topbar-height}"
    backgroundColor: "rgba(255,255,255,0.92)"
    border: "1px solid {colors.hairline}"
  app-footer:
    backgroundColor: "{colors.canvas-light}"
    borderTop: "1px solid #eef1ef"
    padding: "14px 0"
  guest-hero:
    background: "linear-gradient(160deg, #0b0b0b 0%, #132a1a 100%)"
    textColor: "{colors.on-primary}"
  alert-success:
    backgroundColor: "rgba(46,168,79,0.08)"
    textColor: "{colors.brand-deep}"
    border: "1px solid rgba(46,168,79,0.2)"
  alert-error:
    backgroundColor: "rgba(221,0,0,0.06)"
    textColor: "{colors.error}"
    border: "1px solid rgba(221,0,0,0.15)"
---

## Overview

Simaan Water is a **light-first product UI** for a depot-air POS and rule-based AI Insight tool. The visual language borrows Saniti’s sober commercial tokens — near-black ink, hairline borders, IBM Plex Mono eyebrows, and a single green brand accent — but **does not** use marketing display sizes (48–112px) inside the authenticated app.

App screens prioritize density, legibility, and green-primary actions. Marketing surfaces (welcome landing, guest hero panel) may use larger editorial type under the `.landing` scope only.

**Stack:** Laravel Blade · Bootstrap 5.3 (layout/utilities) · custom SCSS (`resources/scss/`) · Alpine.js (app state) · IBM Plex Sans + IBM Plex Mono (Bunny CDN).

**Key characteristics**
- Light commercial shell: `{colors.page-bg}` canvas, white cards, soft hairlines
- **Primary = brand green** (`#2ea84f`) for CTAs, focus rings, active nav
- Dense type: page titles 22px, stats 26px, body 14px — not billboard display
- Mono reserved for eyebrows/labels only
- Pill buttons for primary actions; 6–10px radii for app chrome (inputs, icons, cards)
- Sidebar fixed 240px; container max 1280px

## Colors

### Brand & action
| Token | Hex | Role |
|-------|-----|------|
| `{colors.brand}` / primary | `#2ea84f` | Primary buttons, active nav tint, focus, accent bar |
| `{colors.brand-deep}` | `#0f7a35` | Hover primary, active nav text |
| `{colors.ink}` | `#0b0b0b` | Headlines, filled badges, dark guest/landing bands |
| `{colors.ink-soft}` | `#212121` | Body text |

### Surfaces
| Token | Hex | Role |
|-------|-----|------|
| `{colors.page-bg}` | `#f4f6f5` | App shell background |
| `{colors.canvas-light}` | `#ffffff` | Cards, sidebar, topbar |
| `{colors.canvas-paper}` | `#f6f7f6` | Subtle fills, tabs |
| `{colors.hairline}` | `#e6e9e7` | Borders, dividers |
| `{colors.canvas}` | `#0b0b0b` | Guest hero / dark marketing only |

### Semantic
- **Error** `#dd0000` · **Success** `#37cd84` · **Link blue** `#0052ef` (inline links)
- Badge surfaces: red/amber/green/blue/neutral (see front matter)

### Dashboard accents
The authenticated dashboard uses a supporting accent palette (violet, cyan, indigo,
teal, rose, amber) for **data differentiation only** — tinting stat tiles, panel
header bands, table headers, nav icons, and avatars so dense screens don't read as
a wall of white cards.

| Token | Hex | Role |
|-------|-----|------|
| `{colors.accent-indigo}` | `#2f5fd8` | Default table headers, filled badges, zebra tint |
| `{colors.accent-violet}` | `#6d4aff` | Second panel in a row, alternating stat tiles |
| `{colors.accent-cyan}` | `#0d9bb5` | Third stat tile, row hover blend |
| `{colors.accent-teal}` | `#0f9f7a` | Fourth stat tile, sidebar accent ramp |
| `{colors.accent-rose}` | `#e0447a` | Icon tiles, avatar rotation |
| `{colors.accent-amber}` | `#e08600` | Icon tiles, avatar rotation |

Accents are **decorative, never actionable**. They live entirely in
`_colorful.scss` under the `.app-shell` scope.

### Rules
- Green is the **only** action color, everywhere. Accents never appear on buttons,
  active nav items, focus rings, or any interactive affordance.
- Accents are **scoped to `.app-shell`**. The landing page (`body.landing`) and the
  guest/auth shell stay green-minimal — do not add accents there.
- Semantic meaning wins over rotation: `.stat-accent`, `.stat-accent-error`, and
  `.stat-accent-amber` opt out of the hue cycle so red always means "needs
  attention" and green always means "on target".
- Do not add accent hues beyond the six tokens above.

## Typography

### Families
- **IBM Plex Sans** — all UI text (titles, body, buttons)
- **IBM Plex Mono** — eyebrows, table headers, micro labels only

### App hierarchy

| Token | Size | Weight | Use |
|-------|------|--------|-----|
| `{typography.page-title}` | 22px | 600 | Page header titles |
| `{typography.stat-value}` | 26px | 600 | Dashboard / report metrics |
| `{typography.heading-md}` | 18px | 600 | Modal / card titles |
| `{typography.heading-sm}` | 16px | 600 | Feature card titles |
| `{typography.section-title}` | 15px | 600 | Inline section labels |
| `{typography.body}` | 14px | 400 | Default UI |
| `{typography.body-sm}` | 13px | 400 | Tables, helper text |
| `{typography.mono-eyebrow}` | 10px | 500 | Section labels above titles |
| `{typography.mono-caps}` | 10px | 500 | Stat labels, table headers |

### Landing only (`.landing`)
Larger display scales (`display-lg` / `display-md` clamp) for welcome hero. Never use those sizes inside authenticated app pages.

### Principles
- Hierarchy from size + weight, not mixed display fonts
- Mono never for body or page titles
- Prefer `font-weight: 600` for app headings (readable at small sizes)

## Layout

### Shell
```
┌──────────┬────────────────────────────┐
│ Sidebar  │ Topbar (sticky, 56px)      │
│ 240px    ├────────────────────────────┤
│ white    │ Page header (optional)     │
│          ├────────────────────────────┤
│          │ Content (page-bg)          │
│          │ max-width 1280px           │
│          ├────────────────────────────┤
│          │ Footer (light, compact)    │
└──────────┴────────────────────────────┘
```

### Spacing
- Page stack gap: `{spacing.section}` (20px)
- Card padding: 12–16px (dense), not 32px marketing
- Form fields height: 40px; primary buttons: 38px; icon buttons: 32px
- Touch targets ≥ 32px (mobile primary buttons stay ≥ 38px)

### Grid
- Bootstrap grid: `row` / `col-*`
- Stats: 2-up mobile, 4-up desktop
- Forms: max-width ~36rem
- Tables: always inside `.table-responsive`

## Elevation

| Level | Treatment |
|-------|-----------|
| Page | `{colors.page-bg}` flat |
| Card | white + 1px hairline + soft shadow |
| Sticky chrome | topbar/footer white + hairline |
| Active nav | soft green fill, no heavy shadow |
| Modal | scrim 35% ink + elevated white panel |

Depth comes from surface contrast and light shadows — avoid heavy drop shadows and decorative grain on app pages.

## Shapes

| Token | Value | Use |
|-------|-------|-----|
| `{rounded.app-sm}` | 6px | Tabs, qty steppers |
| `{rounded.app-md}` | 8px | Inputs, icon buttons |
| `{rounded.app-lg}` | 10px | Cards, panels |
| `{rounded.marketing}` | 12px | Landing feature cards |
| `{rounded.full}` | pill | Primary/secondary CTAs, badges, brand-dot |

## Components

### Buttons
- **Primary / Brand** (`.btn-primary`, `.btn-brand`): green fill, white text, pill, 38px
- **Secondary** (`.btn-secondary-dark`): white + hairline border
- **Ghost** (`.btn-ghost`): transparent; optional border
- **Danger** (`.btn-danger`): error red
- **Icon** (`.btn-icon`): 32×32, app radius
- **App tab** (`.btn-app-tab`): uppercase compact; selected = green fill

### Navigation
- White sidebar, brand accent line (2px green gradient)
- Active item: green soft background + brand-deep text/icon
- Admin-only CTA “Generate Analisis” uses brand button once at sidebar bottom

### Surfaces
- `.surface-1` — default card
- `.surface-2` — muted panel
- `.stat` — metric tile; optional left accent bar (green / error)
- `.feature-brand` — solid green quick-action card (max one per section)

### Forms
- `.field` / `.select-dark` — 40px, 8px radius, green focus ring
- Labels: `.label-app` (mono 10px uppercase)

### Tables
- `.tbl` — mono uppercase headers, 13px cells, hairline rows, hover `#f8faf9`

### Badges
Semantic chips for stok/role/status (success, error, amber, blue, filled, brand, neutral).

### Feedback
- `.alert-success` / `.alert-error` / `.alert-info` — compact inline banners
- Delete confirms: Alpine modal (`.modal-backdrop-app` + `.modal-panel`)

### Brand mark
- `.brand-dot` — 8px green circle + soft halo next to “Simaan Water”

## SCSS architecture

```
resources/scss/
  app.scss          → variables → bootstrap → tokens → typography → components → layout → utilities → colorful
  _variables.scss   → Bootstrap maps + brand tokens + accent palette
  _tokens.scss      → CSS custom properties
  _typography.scss  → page-title, stat-value, mono-*, landing display
  _components.scss  → buttons, surfaces, forms, badges, tables, modals
  _layout.scss      → shell, sidebar, topbar, guest, container-app
  _utilities.scss   → text/bg/border helpers
  _colorful.scss    → dashboard accent layer, scoped to .app-shell (loads last)
```

`_colorful.scss` is imported **last** so it can override base surfaces. Because it
is nested entirely inside `.app-shell`, the landing and guest pages are unaffected
by design rather than by convention.

Vite entry: `resources/scss/app.scss` + `resources/js/app.js` (imports Bootstrap JS). Alpine remains CDN-only.

## Do's and Don'ts

### Do
- Use green primary for the main action on each app screen
- Pair page titles with `{typography.mono-eyebrow}` when a section needs structure
- Keep app type dense (22/26/14) — reserve large display for `.landing` only
- Use hairline borders + soft shadows for cards
- Keep tables responsive; use semantic badges for status
- Prefer Bootstrap grid + app design classes over ad-hoc utility soup

### Don't
- Don't use 38–112px display headlines inside dashboard/CRUD/POS
- Don't use IBM Plex Mono for body copy or page titles
- Don't reintroduce Tailwind classes or dual CSS frameworks
- Don't put heavy dark footers or grain backgrounds on every app page
- Don't invent accent hues beyond the six dashboard accent tokens
- Don't use accent colors for buttons, active nav, or focus states — green owns actions
- Don't apply dashboard accents to the landing page or auth screens
- Don't hardcode accent colors in Blade; add them to `_colorful.scss` so pages stay consistent
- Don't touch `reports/export-pdf.blade.php` for Bootstrap styling

## Responsive

| Breakpoint | Behavior |
|------------|----------|
| ≥992px | Sidebar fixed; multi-column grids |
| <992px | Sidebar drawer (Alpine); hamburger in topbar |
| ≤768px | Single column forms; stats 2-up |
| ≤480px | Touch-friendly controls ≥38px primary |

## Implementation map

| Concern | Location |
|---------|----------|
| Tokens / SCSS | `resources/scss/**` |
| App shell | `resources/views/layouts/{app,navigation,guest}.blade.php` |
| Blade components | `resources/views/components/**` |
| Pages | `resources/views/{dashboard,sales,products,users,reports,analysis,profile,auth,welcome}/**` |
| Out of scope | `resources/views/reports/export-pdf.blade.php` |

## Iteration guide

1. Change tokens in `_variables.scss` / `_tokens.scss` first, then components.
2. App density changes go in `_typography.scss` and `_layout.scss` — not page-by-page magic numbers.
3. Keep one primary green CTA per viewport when possible; secondary actions use ghost/secondary.
4. After visual changes: `npm run build` + hard refresh.
5. When adding a page: use `container-app page-stack`, `.page-title`, `.surface-1`, `.tbl`, `.btn-brand` / `.btn-primary`.
