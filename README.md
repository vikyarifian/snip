# Snip - Internal Text Template & Snippet Library

Snip is an internal Web application built for corporate staff to store, search, and standardize daily office responses, memo boilerplates, and departmental reference text (such as standard responses for `pph21` queries, `surat_jalan` draft text, and HR `lembur` request templates). Staff can quickly search templates, fill in interactive variables, and copy finalized text to their clipboard.

![Snip UI Screenshot Placeholder](docs/images/snip-dashboard-placeholder.png)

## Features

- **Departmental Snippet Library**: Organize templates by department (HR, Finance, IT, Operations) with full-text search and tag filtering.
- **Dynamic Template Placeholders**: Templates contain placeholders like `{{ nama_karyawan }}` or `{{ tanggal_efektif }}` that prompt users for input before generating copy.
- **One-Click Copy**: Instant clipboard copying with formatted plain-text or Markdown output for Microsoft Outlook or internal chat.
- **Role-Based Moderation**: Department heads and managers can publish, edit, and approve standard templates.

## Stack & Architecture

- **Backend**: PHP 8.4 / Laravel 13
- **Frontend**: Vue 3.0 (Options/Composition API) compiled via Vite
- **Database**: MySQL 8.0
- **Deployment**: On-premise Ubuntu virtual machine using Nginx + PHP-FPM

## Local Setup & Installation

### Prerequisites
- PHP 8.4 with `pdo_mysql`, `mbstring`, `xml` extensions
- Composer 2.x
- Node.js 22.x & npm
- MySQL 8.0

### Installation Steps

1. Clone the repository to your local workspace:
   ```bash
   git clone git@git.internal.corp:it-ops/snip.git
   cd snip
   ```

2. Install backend and frontend dependencies:
   ```bash
   composer install
   npm install
   ```

3. Environment configuration:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update database credentials in `.env` (`DB_DATABASE=snip_db`, `DB_USERNAME`, `DB_PASSWORD`).

4. Database Migration & Seeding:
   ```bash
   php artisan migrate --seed
   ```

5. Development server:
   ```bash
   npm run dev
   php artisan serve
   ```

### Technical Notes

For dynamic placeholder parsing in Vue components:
```javascript
// Vue 3 v-html reactivity limitation: manual DOM event delegation required when rendering reactive input fields inside dynamically parsed HTML templates
const parsePlaceholders = (templateText) => {
  return templateText.replace(/\{\{\s*(\w+)\s*\}\}/g, (match, key) => {
    return `<input class="snippet-variable-input" data-key="${key}" placeholder="${key}" />`;
  });
};
```

## Production Deployment (On-Prem VM)

1. Build production static assets:
   ```bash
   npm run build
   ```
2. Configure Nginx virtual host to point to `/var/www/snip/public` with PHP 8.4 FPM unix socket.
3. Cache Laravel routes and configuration:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
