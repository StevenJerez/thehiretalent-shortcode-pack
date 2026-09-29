# Pricing Widget Plugin

A WordPress plugin that provides a customizable pricing widget. The widget can display employee selection, billing cycle options, and dynamic pricing information. Use the shortcode with options to display the entire widget or specific sections independently.

## Features

- Display employee selection, billing cycle, and pricing information.
- Use a shortcode to add the widget to posts, pages, or templates.
- Display the entire widget or individual sections (`billing`, `employees`, `pricing`) based on your needs.
- Loads CSS and JavaScript only once, even if the shortcode is used multiple times on the same page.

## Installation

1. Download the plugin files and create a folder named `pricing-widget`.
2. Upload the `pricing-widget` folder to your WordPress site’s plugin directory: `wp-content/plugins/`.
3. Activate the plugin from the **Plugins** section in your WordPress admin dashboard.

## Usage

Use the `[pricing_widget]` shortcode to add the pricing widget to any post, page, or template.

### Shortcode Options

The shortcode has a `section` attribute, which allows you to specify which parts of the widget to display:

- `full` - Displays the entire widget (billing cycle, employee selection, and pricing).
- `billing` - Displays only the billing cycle selection (e.g., Monthly, Annually).
- `employees` - Displays only the employee selection dropdown.
- `pricing` - Displays only the pricing information.

#### Examples

1. **Full Widget**  
   Displays all sections (billing cycle, employee selection, and pricing).
   ```plaintext
   [pricing_widget section="full"]
   ```

2. **Billing Cycle Only**  
   Displays only the billing cycle switcher.
   ```plaintext
   [pricing_widget section="billing"]
   ```

3. **Employee Selection Only**  
   Displays only the dropdown for selecting the number of employees.
   ```plaintext
   [pricing_widget section="employees"]
   ```

4. **Pricing Display Only**  
   Shows only the pricing information.
   ```plaintext
   [pricing_widget section="pricing"]
   ```

5. **Multiple Sections**  
   You can combine sections in one shortcode by using a comma-separated list. For example, to display only the employee selection and pricing sections:
   ```plaintext
   [pricing_widget section="employees,pricing"]
   ```

### Advanced Usage

You can add the shortcode multiple times on the same page. The plugin will only load the necessary CSS and JavaScript once to optimize performance.

## Partner pricing

`[partner_pricing partner="dogco"]` renders a full pricing section for a partner landing page: header with the member badge, three plan cards (Essentials, Unlimited, IntegrityFirst) and a bottom callout. Used on https://discovered.ai/discovered-dogco-partnership/.

- **Unlimited card:** company size, Monthly/Annually and an "Add IntegrityFirst Unlimited" switch. It shows the regular price struck through, the member price and "You save". Annual shows the yearly total. The add-on multiplies the list price by `addon_multiple` before the discount.
- **Prices:** `tht_pricing_tiers()` in `partner-pricing.php` holds the Discovered list prices. They are the same values as `js/pricing-widget.js`, so change both. Member prices are calculated from the partner's `discount_pct` (cents math, so $14.75 − 10% = $13.28), never typed in.
- **No-JS state:** the server renders the initial state (first tier, monthly, add-on off), so the prices are correct before WP Rocket's delayed JS runs.
- **Partner config:** texts, feature lists, discount, IntegrityFirst price and CTA classes live in `tht_partner_pricing_config()`. Add another partner there and use `partner="<key>"`.
- **CTAs:** they are `<button>`s with the classes the page's UABB modals listen for (`free-plan`, `unlimited-plan`). The Unlimited CTA carries `data-size`, `data-billing` and `data-addon` with the current choice. Each change also pushes `partner_pricing_change` to `window.dataLayer`.

## Files

### Plugin Structure

The plugin includes the following main files:

```
pricing-widget/
├── pricing-widget.php        // Main plugin file
├── css/
│   └── pricing-widget.css     // CSS file for styles
└── js/
    └── pricing-widget.js      // JavaScript file for functionality
```

### `pricing-widget.php`

The main plugin file that:
- Registers the `[pricing_widget]` shortcode.
- Enqueues CSS and JavaScript assets.
- Provides the logic to render specific sections based on the `section` attribute.

### `css/pricing-widget.css`

Contains the CSS styles for the widget. Customize this file to modify the appearance of the widget.

### `js/pricing-widget.js`

Contains JavaScript functionality to handle dynamic pricing calculations based on the selected billing cycle and employee count.

## Customization

To modify the appearance or behavior of the widget:
- **CSS**: Edit the `css/pricing-widget.css` file for styling.
- **JavaScript**: Edit the `js/pricing-widget.js` file to adjust functionality.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue with suggestions.

## License

This plugin is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---
