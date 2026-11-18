# WordPress.org plugin directory assets

The icon and banner shown on the plugin's WordPress.org listing. They are not part of the installed plugin.

| File | Purpose |
|---|---|
| `icon.svg` | Vector icon, deployed as-is |
| `icon-256x256.png`, `icon-128x128.png` | Raster icons |
| `banner.svg` | Banner source, not deployed |
| `banner-1544x500.png`, `banner-772x250.png` | Banners |

## Design

One system with the other zirkel.design plugins (`privacy-captcha-for-cap`, `zirkel-iban-for-woocommerce`): the plugin colour as a flat ground, a white form, and a check in a darker shade of the same colour. In the banner the plugin name and one line on the left, and on the right the product itself rather than decoration.

- **Colour:** `#d9481c` ground, check in `#4a1b0d`.
- **Glyph:** the brackets of a character class with the match inside, ticked off.
- **Banner scene:** a "Postcode" field holding `10115`, accepted, with the pattern `^\d{5}$` below it.

## Regenerating the PNGs

```bash
rsvg-convert -w 256 -h 256 icon.svg -o icon-256x256.png
rsvg-convert -w 128 -h 128 icon.svg -o icon-128x128.png
rsvg-convert -w 1544 -h 500 banner.svg -o banner-1544x500.png
rsvg-convert -w 772 -h 250 banner.svg -o banner-772x250.png
```

## Deployment

A push to `main` that changes a file here runs `.github/workflows/deploy-assets.yml`, which commits the icon and banners to the plugin's SVN `assets/` directory. It does not touch `trunk/` or `tags/`, so it needs no release.
