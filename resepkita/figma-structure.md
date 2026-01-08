Figma file structure — ResepKita

Pages
- 00 Tokens
  - Color Styles: map to `design-tokens.json` colors
  - Text Styles: Body, H1, Nav, Small
  - Effects: Card, Card Hover
- 01 Components
  - Header
    - Logo (image + fallback rectangle)
    - Nav Link (default + active)
  - Button
    - Primary (medium)
    - Secondary
    - Sizes: small / medium / large
  - Input
    - Text / Search / Textarea / Select
    - States: default / focus / disabled / error
  - Ingredient Chip / Checkbox Item
    - Variant: selected / unselected
  - Recipe Card
    - Variant A: without image
    - Variant B: with left image thumbnail
    - Fields: Title, Ingredients line, Description, Actions
  - Fieldset (Form card) with legend
  - Messages: success / error
- 02 Pages
  - Home / Search
    - Header component instance
    - Search form (search + health filter + ingredient grid)
    - Results list (vertical stack of Recipe Card instances)
  - Recipe Detail
    - Large hero image
    - Two-column layout: ingredients (left) / instructions (right)
  - Add/Edit Recipe
    - Form layout using Fieldset components

Component guidelines
- Use Auto Layout for buttons, input groups, and cards.
- Use component variants for states (hover, active, selected).
- Create named color & text styles to match tokens for quick updates.

Export & Handoff
- Upload all images in `design-assets/` to Figma assets.
- Use the `Export Recommendations` sizes from `design-tokens.json` for PNG/JPG exports.
- Deliver a single Figma file with the three pages above; use component instances on `02 Pages`.

Notes
- Keep spacing increments based on 8px grid, use 16px for main paddings.
- For mobile variants, create separate frames sized 375×812 and 768×1024.
