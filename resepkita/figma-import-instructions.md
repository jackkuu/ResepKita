Figma import instructions — ResepKita

Goal: Create a Figma file that mirrors the existing `ResepKita` UI using the design tokens and components.

1. Create a new Figma file. Add three pages: `00 Tokens`, `01 Components`, `02 Pages`.

2. Tokens page
- Add Color Styles using values from `design-spec.md`.
- Add Text Styles:
  - Body / Regular: Segoe UI, 14px
  - H1: 28px, Bold
  - Nav link: 14px, SemiBold
- Add Effects for `shadow` and `hover-card`.
- Add Grid/Spacing as Frame components (8px baseline, use 16px for pads).

3. Components page
- Build a `Header` component: place logo left, nav links right. Create variants for `default` and `active` state for links.
- Build `Button` component with `Primary` and `Secondary` variants and `Small/Medium/Large` sizes.
- Build `Input` and `Search` components; add focus and disabled states.
- Build `Recipe Card` component with text placeholders and a media slot.
- Build `Ingredient Checkbox` / `Chip` with selected/unselected states.

4. Pages
- Recreate `Home / Search` layout using the `Header` component and `Recipe Card` instances. Use `Auto Layout` for form sections and ingredient grid.
- Recreate `Recipe Detail` using the `Recipe Card` as a base, expanding to a full detail layout.

5. Assets
- Upload images from `design-assets/` to Figma assets panel. Use thumbnails for cards and full-res for detail pages.

6. Handoff
- Use `Inspect` panel to confirm spacing, font sizes, color tokens. Export code snippets if needed.

If you want, I can copy the repository images into `design-assets/` and generate a ZIP for direct upload to Figma. Tell me to proceed and I will copy the files now.