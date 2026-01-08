Design spec — ResepKita

Overview
- Project path: public/ (UI files)
- Purpose: Convert existing UI into a Figma file: pages, components, and tokens.

Design tokens (extracted from public/css/style.css)
- Colors:
  - bg: #f8f9fa
  - card / surface: #ffffff
  - muted: #666666
  - text primary: #222222
  - accent: #3498db
  - accent-dark: #2d82c6
  - success-bg: #d4edda
  - danger-bg: #f8d7da
  - border / subtle: #e6e9ee / #eee / #f0f4f8
- Shadows:
  - shadow: 0 6px 18px rgba(0,0,0,0.06)
  - hover card: 0 10px 30px rgba(16,24,40,0.06)
- Radii & spacing:
  - radius: 8px (global)
  - pad (card/form padding): 16px
  - small radius used: 6px (buttons, inputs)
- Typography:
  - Font family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif
  - Body color: #222, line-height: 1.5
  - H1: 28px, weight 700 (approx)
  - Base input text: 14px
  - Search input: 16px
  - Nav link: 14px, weight 600

Pages to create in Figma
- Home / Search (index.php)
  - Header / nav
  - Hero / title
  - Search bar + health filter
  - Ingredient checklist grid
  - Results list with recipe cards
- Recipe detail (recipe_detail.php)
  - Large image/header
  - Ingredients list + instructions
  - Action buttons
- Add / Edit recipe (add_recipe.php, edit_recipe.php)
  - Form layout, file upload, inputs, multi-select/ingredients
- Nutrition filter / results (nutrition_filter.php, filter_recipes_nutrition.php)
- Manage lists (manage_recipes.php, manage_ingredients.php)
- Partials page: header + footer as components

Component library (variants)
- Header / Nav
  - Variant: default / sticky / compact (mobile)
- Logo (image + CSS-only variant)
- Button
  - Primary / Secondary / Text
  - Sizes: small / medium / large
- Input
  - Text / textarea / select / search
  - States: default / focus (accent border) / disabled / error
- Checkbox item / ingredient chip
  - Variant: selected / unselected
- Recipe Card
  - Variant: with image / without image
  - Elements: title, ingredients line, description, link
- Form Card
  - Fieldset / legend style
- Messages: success / error

Assets (present in repo)
- Logo: public/img/logo.png
- Uploaded images: public/uploads/*.jpeg / *.webp
- Suggestion: replace raster logo with an SVG if available; otherwise export PNG at sizes 64px, 128px, 256px.

Suggested export sizes
- Logo: 32px, 64px, 128px (PNG) + optional 2x versions
- Recipe images: keep original dimensions; export thumbnails at 400×280 and 1200×800 for detail

Next steps
1. Export assets listed in `asset-manifest.json` into `design-assets/` (I can copy them if you want).
2. Create Figma file structure: Pages (Design Tokens, Components, Pages) and build components following spec.
3. Produce a ZIP with assets and these spec files, or provide step-by-step import instructions.

Note: I extracted tokens from `public/css/style.css`. If you want visual changes (brand colors, different spacing), tell me and I will update tokens and regenerate the spec.
