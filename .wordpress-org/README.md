# WordPress.org Plugin Assets

This directory contains assets for the WordPress.org plugin directory.

## Files

### Icon
- `icon.svg` - Vector icon featuring the `^.+$` regex pattern with Gravity Forms brand colors

For WordPress.org, you may also want to provide PNG versions:
- `icon-128x128.png` - Low resolution icon
- `icon-256x256.png` - High resolution icon (recommended)

### Banners
- `banner-772x250.svg` - Low resolution banner (SVG)
- `banner-1544x500.svg` - High resolution banner (SVG - 2x resolution)

For WordPress.org, PNG versions are also supported:
- `banner-772x250.png` - Low resolution banner
- `banner-1544x500.png` - High resolution banner (2x)

## Colors Used

- **Primary (Dark Blue):** `#0f3d6c` - Gravity Forms brand color
- **Secondary (Orange):** `#f15a2b` - Gravity Forms brand color
- **Text:** White with various opacity levels

## Design Elements

- Main feature: `^.+$` regex pattern
- Background: Examples of common regex patterns (email, date, phone number formats)
- Gradient: Transitions from dark blue to orange
- Clean, professional appearance suitable for a developer tool

## Converting to PNG

If you need PNG versions (recommended for broader compatibility), you can convert using:

```bash
# Using ImageMagick (if installed)
convert -background none -resize 256x256 icon.svg icon-256x256.png
convert -background none -resize 128x128 icon.svg icon-128x128.png
convert banner-772x250.svg banner-772x250.png
convert banner-1544x500.svg banner-1544x500.png

# Or using rsvg-convert (if installed)
rsvg-convert -w 256 -h 256 icon.svg -o icon-256x256.png
rsvg-convert -w 128 -h 128 icon.svg -o icon-128x128.png
rsvg-convert -w 772 -h 250 banner-772x250.svg -o banner-772x250.png
rsvg-convert -w 1544 -h 500 banner-1544x500.svg -o banner-1544x500.png
```

## WordPress.org Plugin Directory

These assets will be automatically used by WordPress.org when you:
1. Commit them to your SVN repository in the `assets` folder
2. The WordPress.org plugin directory will detect and display them

For more information, see: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
