# InsightPulse BI — Sales Analytics Platform

A multi-page PHP + MySQL + JavaScript business intelligence dashboard with
a sidebar-navigated layout, per-account logins, multi-year data, and
SQL-driven analytics (window functions, rankings, YoY growth, market share).
Built to run on XAMPP.

This is a renamed/rebuilt version of an earlier "sales_dashboard" project —
the database name, folder name, and app branding are all independent, so
both versions can be installed side by side in the same XAMPP `htdocs` and
phpMyAdmin instance without conflicting.

## Setup (XAMPP)

1. Copy the whole `insightpulse_bi` folder into your XAMPP `htdocs` directory,
   e.g. `C:\xampp\htdocs\insightpulse_bi` (Windows) or
   `/Applications/XAMPP/htdocs/insightpulse_bi` (Mac).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and import `sql/schema.sql`.
   This creates a new `insightpulse_bi` database (separate from any older
   `sales_dashboard` database), with a `users` table and a `sales` table.
4. Open `config.php` and confirm the DB credentials match your MySQL setup
   (defaults `root` / no password work for a stock XAMPP install).
5. Visit `http://localhost/insightpulse_bi/` — you'll land on the login page.
   Register an account, log in, and you'll be taken to **Home**.
6. From the sidebar, go to **Data Upload** and import a CSV — try
   `sample_sales_data.csv` (2025) and `sample_sales_data_2026.csv` (2026),
   both included and both containing a few intentionally blank cells so you
   can see the missing-value handling in action.

> **SQL window functions used here (`LAG()`, `RANK()`) require MySQL 8.0+ or
> MariaDB 10.2+.** Current XAMPP releases ship a version that supports this;
> if Year-over-Year or Rankings errors out, check your MySQL/MariaDB version
> in phpMyAdmin.

## Pages (sidebar navigation)

| Page | What it shows |
|---|---|
| `login.php` / `register.php` | Account login and creation |
| `hub.php` (**Home**) | Landing page — clickable cards linking to every view |
| `dashboard.php` (**Executive Dashboard**) | 8 KPI cards, monthly trend / category / region charts, filterable + paginated order table, CSV export |
| `yoy.php` (**Year-over-Year**) | Revenue/orders/AOV comparison across years with growth %, plus a month × year revenue matrix |
| `rankings.php` (**Rankings**) | Top 10 & bottom 10 products, top 10 customers — ranked with SQL `RANK()` |
| `performance.php` (**Category & Region**) | Category and region breakdown with revenue, orders, AOV, and market share % |
| `import.php` (**Data Upload**) | CSV upload, column guidelines, and missing-value handling rules |

## Login & data isolation

- Accounts are created on `register.php` (passwords hashed with `password_hash`).
- Every page and API endpoint requires a session; unauthenticated requests
  are redirected to `login.php` (pages) or get a 401 JSON response (API).
- **Data is private per account** — every `sales` row is tagged with the
  uploading user's ID, and every query filters by the logged-in user. If
  User A only uploads 2025 data, User A only ever sees 2025. If User B
  uploads both 2025 and 2026, User B sees both and can filter between them.
  User A never sees User B's data.
- Uploads **accumulate** rather than overwrite — a 2025 file and a 2026 file
  coexist. Re-uploading a CSV with the same `Order ID` for the same account
  updates that row instead of duplicating it, and the import summary tells
  you exactly how many rows were newly added vs. updated vs. unchanged.

## CSV format & missing-value handling

Column headers (order doesn't matter, matching is case-insensitive):

```
Order ID, Order Date, Customer Name, Product, Category, Region, Quantity, Unit Price, Total Amount
```

`Order Date` accepts `YYYY-MM-DD`, `MM/DD/YYYY`, `DD/MM/YYYY`, and a few other common formats.

Handling is tiered rather than "skip the whole row on any blank cell":

- **Required — row is skipped if missing:** Order ID, Order Date.
- **Optional text — filled with a clear placeholder if blank:** Customer Name
  → "Unknown Customer", Product → "Unspecified Product", Category →
  "Uncategorized", Region → "Unspecified".
- **Numeric, cross-calculated:** Quantity, Unit Price, and Total Amount are
  mutually derivable (`Total = Quantity × Unit Price`). If exactly one of
  the three is blank, it's computed from the other two. A row is only
  skipped if two or more of these three are missing.

Every import response reports: new rows added, existing rows updated,
rows with an auto-filled value, and rows skipped (with reasons) — see
`import.php`.

## SQL techniques used

- Aggregation: `SUM`, `AVG`, `COUNT`, `GROUP BY` for every KPI and breakdown
- Window functions: `LAG()` for year-over-year growth %, `RANK()` for
  product/customer leaderboards
- Derived metrics: market share % (`revenue / grand_total`), average
  selling price (`revenue / units_sold`)
- `INSERT ... ON DUPLICATE KEY UPDATE` with `affected_rows` inspection to
  distinguish new inserts from updates during CSV import
- Prepared statements throughout (no raw string interpolation into SQL)

## Deliberately left out (to avoid feature bloat)

PDF/Excel export, revenue forecasting, a separate "Trend Analytics" page
with `LEAD()`/`CTE`/rolling averages, audit logs, and a profile page were
all considered and skipped — they add surface area without changing what
the project demonstrates. CSV export, `LAG()`-based YoY growth, and
`RANK()`-based leaderboards already cover the core SQL-analytics story.

## Project structure

```
insightpulse_bi/
├── index.php                  Redirects to hub.php (logged in) or login.php
├── login.php / register.php / logout.php / auth.php   Login system
├── hub.php                    Home — links to every view
├── dashboard.php              Executive Dashboard
├── yoy.php                    Year-over-Year comparison
├── rankings.php               Top/bottom products, top customers
├── performance.php            Category & region performance + market share
├── import.php                 CSV upload + column guidelines
├── config.php                 DB connection + APP_NAME constant
├── partials/sidebar.php       Shared sidebar nav, included on every page
├── api/
│   ├── get_stats.php          Executive Dashboard KPIs + chart data (JSON)
│   ├── get_data.php           Paginated/sortable/filterable order rows (JSON)
│   ├── get_yoy.php            Year-over-Year data (JSON)
│   ├── get_rankings.php       Ranked product/customer data (JSON)
│   ├── get_performance.php    Category/region breakdown (JSON)
│   └── export_csv.php         Filtered CSV export (direct download)
├── css/style.css              All styling
├── js/
│   ├── common.js              Shared formatting helpers
│   └── dashboard.js / import.js / yoy.js / rankings.js / performance.js
├── sql/schema.sql             Database + users + sales table creation script
├── sample_sales_data.csv      Sample 2025 dataset (includes a few blank cells)
└── sample_sales_data_2026.csv Sample 2026 dataset (includes a few blank cells)
```

## Notes

- Chart.js is loaded from a CDN — an internet connection is needed the first
  time each page loads.
- To rename the app, change the `APP_NAME` constant in `config.php`.
