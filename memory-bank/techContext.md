# Technical Context: Article Rewriter WordPress Plugin

## 1. Core Technologies

*   **Backend:** PHP (version compatibility likely tied to WordPress requirements)
*   **Frontend:** JavaScript (ES version unclear, potentially transpiled if using build tools), HTML, CSS
*   **Platform:** WordPress
*   **WordPress Features Used:** REST API, Settings API, Options API, WP Cron (`wp_schedule_single_event`, custom hooks `article_rewriter_process_batch`, `article_rewriter_license_check`), `admin-post` hooks, AJAX (`wp_ajax_`), Custom Database Tables, `wp_remote_post`, Admin Notices. Transients API (possible, not confirmed).

## 2. Development Environment & Tools

*   **WordPress Installation:** Requires a running WordPress site for activation and use.
*   **Web Server:** Apache or Nginx (typical for WordPress).
*   **Database:** MySQL or MariaDB (standard for WordPress).
*   **Node.js/npm:** Presence of `package.json` and `package-lock.json` indicates Node.js is used for:
    *   Managing frontend dependencies (`@wordpress/scripts`).
    *   **Confirmed:** Running build scripts via `npm run build` (uses `wp-scripts build`). This compiles Block Editor assets from `src/` to `build/`.
    *   Running development server/watcher via `npm start` (uses `wp-scripts start`).
    *   Linting or formatting code (Possible, via `wp-scripts`).
*   **Version Control:** `.gitignore` file present, suggesting Git is used.
*   **Database:** MySQL or MariaDB (standard for WordPress). **Confirmed:** Uses custom tables (`wp_article_rewriter_history`, `wp_article_rewriter_batch`, `wp_article_rewriter_batch_items`) created via `dbDelta` in `Article_Rewriter_Activator`.
*   **Options API:** Used extensively for storing settings (API keys, defaults), license information (key, status, dates), and internal state (DB version). Default options set on activation.

## 3. Key Dependencies

*   **WordPress Core:** The plugin fundamentally depends on the WordPress API (hooks, functions, classes, REST API, Settings API, etc.).
*   **External Rewriting API:** A crucial external dependency, accessed via `wp_remote_post` through the plugin's REST API handler (`Article_Rewriter_API`). Specific providers: **OpenAI, DeepSeek (implemented)**; **Anthropic, Gemini (placeholders)**.
*   **External Licensing Service:** `class-article-rewriter-license.php` confirms communication (via placeholders) with an external service (`https://widigital.com/license-server/`) to validate licenses purchased via **Envato**. Editor integration and REST API access are conditional on active license status (`article_rewriter_license_status` option).

## 4. Technical Constraints & Considerations

*   **WordPress Environment:** Must function correctly within the WordPress ecosystem, respecting its loading process, security measures, and APIs.
*   **PHP Version Compatibility:** Needs to be compatible with PHP versions supported by the target WordPress versions.
*   **Browser Compatibility:** Frontend JavaScript and CSS need to work across common browsers supported by WordPress.
*   **API Rate Limits/Costs:** The external rewriting API might have usage limits or costs associated with it.
*   **Security:** As a WordPress plugin, it must follow security best practices to prevent vulnerabilities (e.g., proper data sanitization, nonce usage in AJAX calls).
*   **Performance:** AJAX calls and API interactions should be optimized to avoid slowing down the WordPress admin or editor experience. Batch processing needs careful implementation to avoid server timeouts or excessive resource usage.

*(This is an initial assessment based on file structure and standard WordPress practices. It will be refined as more context is gathered.)*
