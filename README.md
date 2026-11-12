# District Interactive Map — WordPress Plugin

A lightweight, standalone, and pixel-perfect WordPress plugin that creates split-screen interactive Google Maps for districts and neighborhoods. Designed to work seamlessly inside **Elementor**, Gutenberg, or any WordPress page builder via a flexible shortcode.

---

## ✨ Key Features

- **Zero External Plugin Dependencies:** Built with native WordPress Custom Post Types (`district_map`) and custom Vanilla JS repeatable meta boxes—no ACF Pro or third-party meta box plugins required.
- **Dual Location Categories:** Manage and display two distinct location groups per map:
  - **GPS Links:** Highlighted with vibrant Red-Orange (`#FF3B1D`) circular numbered markers.
  - **Perk Stops:** Highlighted with sleek Jet-Black (`#111111`) circular numbered markers.
- **Auto-Generated Zero-Padded Numbering:** Automatically indexes locations sequentially (`01`, `02`, `03`...) in both the list and map markers while filtering out incomplete rows to prevent numbering bugs.
- **Bidirectional Hover & Click Sync:**
  - Hovering over a location name highlights the text and scales/elevates (`z-index: 9999`) the corresponding marker on the map.
  - Hovering over a map marker highlights the corresponding list item.
  - Clicking either the list item or the custom marker opens the direct Google Maps URL in a new tab (with automatic coordinate-based URL fallback if no custom link is provided).
- **Custom Silver/Grayscale Map Theme:** Built-in clean grayscale map styling that strips out noisy default Google POI/transit icons so custom markers stand out clearly.
- **RTL/LTR Safe & Fully Responsive:** Strictly scoped CSS (`.dim-*`) that maintains its intended split-screen typography (`Clash Display` & `Open Sans`) and adapts smoothly to tablets and mobile devices (`gestureHandling: 'cooperative'`).

---

## 📂 File Structure

```text
district-interactive-map/
├── district-interactive-map.php       # Main plugin bootstrap & constants
├── includes/
│   ├── class-dim-cpt.php              # Registers 'district_map' CPT & shortcode admin columns
│   ├── class-dim-metaboxes.php        # Native drag-and-drop repeater metaboxes & save handler
│   ├── class-dim-settings.php         # Admin settings page (API Key & Custom Map Style JSON)
│   └── class-dim-shortcode.php        # [district_map] shortcode rendering & conditional asset loading
└── assets/
    ├── css/
    │   ├── dim-admin.css              # Backend repeater & metabox styles
    │   └── dim-frontend.css           # Frontend split-layout, typography & custom marker styles
    └── js/
        ├── dim-admin.js               # Dynamic repeater row add/remove/reorder logic
        └── dim-frontend.js            # Google Maps initialization, custom OverlayView markers & hover sync
```

---

## 🚀 Installation & Setup

1. **Upload the Plugin:**
   - Download or clone this repository into your WordPress plugins directory:
     ```bash
     wp-content/plugins/district-interactive-map
     ```
   - Or compress the `district-interactive-map` folder into a `.zip` file and upload it via **WordPress Admin > Plugins > Add New > Upload Plugin**.

2. **Activate the Plugin:**
   - Activate **District Interactive Map** from the **Plugins** menu in WordPress.

3. **Configure Google Maps API Key:**
   - Navigate to **Settings > District Map**.
   - Enter your **Google Maps JavaScript API Key**.
   - *(Optional)* Paste a custom Google Maps JSON style array, or leave the field empty to use the built-in Silver/Grayscale theme.
   - Click **Save Changes**.

---

## 🗺️ Creating a Map & Using the Shortcode

1. Go to **District Maps > Add New** in the WordPress admin sidebar.
2. Enter a title for your district (e.g., `Heliopolis`).
3. **Map Viewport Config:**
   - Set the **Center Latitude** (e.g., `30.0889`), **Center Longitude** (e.g., `31.3283`), and **Default Zoom** (e.g., `14`).
4. **GPS Links & Perk Stops:**
   - Click **Add Row** to add locations.
   - Fill in the **Title**, **Latitude**, **Longitude**, and **Google Maps URL** *(optional — if left blank, the plugin automatically generates a Google Maps search link using the coordinates)*.
   - Use the drag handle (`⋮⋮`) or up/down arrows (`↑` / `↓`) to reorder locations.
5. Click **Publish** / **Update**.
6. Copy the generated shortcode from the **District Maps** list table and paste it into an **Elementor Shortcode Widget** or any page:

### Shortcode Examples

Render by Post ID:
```shortcode
[district_map id="123"]
```

Render by Post Slug:
```shortcode
[district_map slug="heliopolis"]
```

Show empty section placeholders (optional, defaults to `false`):
```shortcode
[district_map slug="heliopolis" show_empty="true"]
```

---

## 🎨 Typography & Design Specs

- **Headings (`GPS LINKS` / `PERK STOPS`):** `Clash Display` (Split weight: `700 Bold` on first word, `300 Light` on second word).
- **Location Lists & Numbers:** `Open Sans` (`300 Light`), with subtle `#FF3B1D` hover accent on location titles.
- **Marker Colors:**
  - `GPS Links`: `#FF3B1D` (Red-Orange)
  - `Perk Stops`: `#111111` (Jet Black)

---

## 📄 License

This project is licensed under the GPL-2.0+ License.