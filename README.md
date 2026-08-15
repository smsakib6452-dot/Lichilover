# Lichi Lover — Static Site (GitHub Pages)

A 100% static, zero-backend version of the Lichi Lover e-commerce store and admin
panel. Everything runs in the browser on `localStorage` (seeded from
`assets/js/data.js`). No PHP, no MySQL, no build step.

The site is a static replacement for the original PHP/MySQL version and needs no
server other than any static file host.

## Demo accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@lichilover.com` | `admin123` |
| Customer | `customer@lichilover.com` | `customer123` |

## Structure

```
├── index.html            # Storefront (all public pages at root)
├── shop.html, product.html, cart.html, checkout.html, pay.html,
│   order-success.html, track-order.html, account.html, orders.html,
│   order-detail.html, login.html, register.html, contact.html,
│   faq.html, about.html, privacy-policy.html, terms.html, refund-policy.html
├── 404.html, robots.txt, sitemap.xml
├── admin/                # Admin panel
│   ├── login.html, index.html, products.html, product-add.html,
│   │   product-edit.html, orders.html, order-view.html, customers.html,
│   │   coupons.html, coupon-add.html, coupon-edit.html, delivery.html,
│   │   reviews.html, messages.html, settings.html
└── assets/
    ├── css/  (style.css, admin.css)
    ├── images/ (logo.svg)
    └── js/
        ├── data.js             # LL_SEED (categories, products, zones, coupons, ...)
        ├── store.js            # Store layer + localStorage + admin CRUD
        ├── components.js       # Public header/footer injection (#site-header/#site-footer)
        ├── main.js             # Storefront page renderers (window.LL_PAGE)
        ├── admin-components.js # Admin shell injection (#admin-header/#admin-footer)
        ├── admin-pages.js      # Admin page renderers (window.LL_ADMIN_PAGE)
        └── admin.js            # Sidebar toggle, charts, variant rows, confirm dialogs
```

Every page sets `window.LL_PAGE` / `window.LL_ADMIN_PAGE` and loads the JS files
in order at the end of `<body>`.

## Test locally

Any static file server works (Pages does not need a server, but localStorage is
shared per origin, so `file://` works too):

```
npx serve .
# or
python -m http.server 8080
```

Then open:

- Storefront: `http://localhost:8080/index.html`
- Admin: `http://localhost:8080/admin/login.html`

## Deploy to GitHub Pages

Option A — project site from the repo root (recommended for `user.github.io/repo`):

1. The site files are already at the repo root — just push them (or publish from a
   `gh-pages` branch).
2. In GitHub → Settings → Pages, set the branch to `main` (root) or `gh-pages`.
3. The site is live at `https://<your-username>.github.io/<repo>/`.

Option B — user site (`user.github.io`): serve the repo root directly with any
Pages-compatible host and it works as-is.

### Subdirectory hosting

The site is already subpath-friendly. If deployed under a path (e.g.
`https://user.github.io/lichi-lover/`), set:

```html
<script>window.LL_ASSET_BASE = './';</script>
```

The default is relative paths (`assets/...`), so it works from any subfolder
without a change.

### Before going live

- Replace `<your-username>` in `robots.txt` and `sitemap.xml`.
- Optionally edit `LL_SEED` in `assets/js/data.js` (products, zones, coupons,
  demo users) and bump `DB_VERSION` there to re-seed every visitor's browser.

## How the data layer works

- First visit: `store.js` hydrates all collections from `window.LL_SEED` into
  `localStorage` (keys `ll_*`, seed version `ll_seeded`).
- Cart, orders, reviews, messages, and admin settings are persisted per browser.
- Bumping `DB_VERSION` in `data.js` re-seeds (dev change, not a reset for users).
- Payments are **simulated** (demo mode) — no real money is charged.

## What's simulated vs real

- Payments: simulated, ~700ms, labelled "demo".
- Login/registration: plaintext demo credentials in `LL_SEED`.
- Delivery fees: read live from the Delivery Zones table in `localStorage`.
- Coupons, reviews moderation, contact messages, newsletter: all persisted in
  the browser and fully manageable from the admin panel.
