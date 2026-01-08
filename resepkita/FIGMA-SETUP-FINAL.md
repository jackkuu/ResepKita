# ResepKita Figma Design Package — Setup Final

## File yang sudah disiapkan

✓ **Paket utama**: `resepkita-design-package.zip`
  - Location: `c:\xampp\htdocs\project\tugasentre\resepkita\resepkita-design-package.zip`
  - Berisi: spesifikasi, token desain, aset, dan artboard SVG

✓ **File sudah diekstrak ke**: `C:\temp\resepkita-design\`
  - `home.svg` — Artboard halaman utama/pencarian
  - `recipe-detail.svg` — Artboard detail resep
  - `design-spec.md` — Spesifikasi desain lengkap
  - `design-tokens.json` — Token warna, typography, spacing
  - `figma-import-instructions.md` — Panduan impor ke Figma
  - `logo.png` — Logo ResepKita
  - `*.jpeg` / `*.webp` — Gambar resep upload

## Langkah-langkah selanjutnya

### 1. Konversi SVG ke PNG (Opsional untuk preview)

**Pilihan A: Gunakan ImageMagick** (Rekomendasi)
```powershell
# Instal dulu dari: https://imagemagick.org/script/download.php

# Jalankan konversi:
cd "c:\xampp\htdocs\project\tugasentre\resepkita"
.\convert-svgs.ps1
```

Hasil PNG akan ada di: `C:\temp\resepkita-design\png_output\`

**Pilihan B: Gunakan Inkscape**
```powershell
# Instal dulu dari: https://inkscape.org/release/

# Edit convert-svgs.ps1 atau jalankan manual:
inkscape "C:\temp\resepkita-design\home.svg" --export-type=png --export-filename="C:\temp\resepkita-design\png_output\home.png"
inkscape "C:\temp\resepkita-design\recipe-detail.svg" --export-type=png --export-filename="C:\temp\resepkita-design\png_output\recipe-detail.png"
```

### 2. Impor ke Figma (Web)

1. Buka https://figma.com → Buat file baru
2. Buat tiga pages:
   - `00 Tokens` — Letakkan color & text styles
   - `01 Components` — Komponen reusable (Header, Button, Card, Input, dll)
   - `02 Pages` — Layar produk (Home, Recipe Detail, Add/Edit Recipe)
3. **Impor aset & artboard:**
   - Buka folder: `C:\temp\resepkita-design\`
   - Drag & drop `home.svg` dan `recipe-detail.svg` ke page `02 Pages`
   - Drag & drop `logo.png` dan gambar resep ke asset panel Figma
4. **Buat Color Styles** di page `00 Tokens` menggunakan nilai dari `design-tokens.json`:
   - Primary: `#3498db`
   - Background: `#f8f9fa`
   - Text: `#222222`
   - (... lihat file JSON lengkapnya)
5. **Buat Text Styles:**
   - Body: Segoe UI, 14px, Regular
   - H1: Segoe UI, 28px, Bold
   - Nav: Segoe UI, 14px, SemiBold
6. **Build components** di page `01 Components` dengan Auto Layout & apply styles dari `00 Tokens`

### 3. Dokumentasi

- [design-spec.md](../design-spec.md) — Detail lengkap warna, spacing, typography
- [figma-structure.md](../figma-structure.md) — Struktur halaman & komponen Figma
- [figma-import-instructions.md](../figma-import-instructions.md) — Panduan step-by-step impor

## File lokasi di PC

| File | Path |
|------|------|
| Paket ZIP | `c:\xampp\htdocs\project\tugasentre\resepkita\resepkita-design-package.zip` |
| Skrip konversi | `c:\xampp\htdocs\project\tugasentre\resepkita\convert-svgs.ps1` |
| Ekstrak folder | `C:\temp\resepkita-design\` |
| Token JSON | `C:\temp\resepkita-design\design-tokens.json` |
| Artboards SVG | `C:\temp\resepkita-design\home.svg`, `recipe-detail.svg` |

## Status

✅ Inventaris UI files — SELESAI
✅ Definisi pages & components — SELESAI
✅ Extract assets — SELESAI
✅ Create design tokens — SELESAI
✅ Produce Figma file structure — SELESAI
✅ Generate deliverables (ZIP) — SELESAI
⏳ Review & final tweaks — MENUNGGU feedback Anda

---

**Next:** Buka `C:\temp\resepkita-design\` dan mulai impor ke Figma, atau beri tahu jika mau saya sesuaikan desain (warna, spacing, komponen tambahan).
