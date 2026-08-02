<div align="center">

# InsightPulse BI

**A sales analytics platform built with PHP, MySQL & JavaScript**

Multi-page BI dashboard with account-based data isolation, year-over-year growth analysis, product/customer rankings, and category performance breakdowns — all powered by SQL window functions.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=flat&logo=chart.js&logoColor=white)

</div>

---

## Overview

InsightPulse BI turns a raw sales CSV into a full analytics workspace: an executive dashboard, year-over-year growth comparisons, product and customer rankings, and category/region performance breakdowns — each backed by SQL rather than client-side math. Every account's data is fully isolated, so multiple users can upload their own datasets independently on the same install.


## Features

- **Executive Dashboard** — revenue, orders, AOV, best category/region, and highest/lowest revenue months at a glance
- **Year-over-Year Comparison** — growth % calculated with SQL's `LAG()` window function, plus a month-by-month matrix across years
- **Rankings** — top & bottom 10 products and top 10 customers, ranked with SQL's `RANK()`
- **Category & Region Performance** — revenue, orders, and market share % per category and region
- **CSV Import** — validates structure, cross-calculates missing numeric fields, and reports exactly what was inserted, updated, defaulted, or skipped
- **Accounts & Data Isolation** — each login only ever sees the data it uploaded
- **CSV Export** — download the current filtered view

## Tech Stack

| Layer | Tech |
|---|---|
| Backend | PHP (procedural, mysqli, prepared statements) |
| Database | MySQL / MariaDB |
| Frontend | Vanilla JavaScript, Chart.js |
| Environment | XAMPP (Apache + MySQL) |

## Getting Started

**Prerequisites:** XAMPP (or any Apache + MySQL 8.0+/MariaDB 10.2+ stack — needed for window function support)

```bash
# 1. Clone into your XAMPP htdocs folder
git clone https://github.com/sanjiaust/insightpulse-bi.git

# 2. Start Apache & MySQL from the XAMPP control panel

# 3. Import the schema
#    phpMyAdmin → Import → select sql/schema.sql
#    (creates the insightpulse_bi database, users table, and sales table)

# 4. Check config.php matches your MySQL credentials
#    (defaults: root / no password — standard XAMPP setup)

# 5. Visit the app
http://localhost/insightpulse_bi/
```

Register an account, then upload `sample_sales_data.csv` and `sample_sales_data_2026.csv` (included) to explore the dashboard with sample data spanning two years.

## Project Structure

<details>
<summary>Click to expand</summary>

```
insightpulse_bi/
├── login.php / register.php / logout.php / auth.php   # Auth
├── hub.php              # Landing page
├── dashboard.php        # Executive Dashboard
├── yoy.php              # Year-over-Year comparison
├── rankings.php         # Product & customer rankings
├── performance.php      # Category & region performance
├── import.php           # CSV upload
├── api/                 # JSON endpoints consumed by the frontend
├── css/ · js/            # Styling & frontend logic
├── sql/schema.sql        # Database schema
└── sample_sales_data*.csv
```

</details>

## CSV Format

```
Order ID, Order Date, Customer Name, Product, Category, Region, Quantity, Unit Price, Total Amount
```

Only `Order ID` and `Order Date` are required — everything else is defaulted or cross-calculated where possible. Full column-by-column rules are shown in-app on the **Data Upload** page.

## License

MIT — free to use, modify, and learn from.

---

<div align="center">
Built by <a href="https://github.com/sanjiaust">Ridwanul Islam (Sanji)</a>
</div>
