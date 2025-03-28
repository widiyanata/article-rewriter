# Progress: Article Rewriter WordPress Plugin

## 1. What Works (Confirmed & Assumed)

*   **Confirmed:** Plugin installs, activates (`Article_Rewriter_Activator`), and deactivates (`Article_Rewriter_Deactivator`) via standard WordPress hooks.
*   **Confirmed:** Plugin initializes by instantiating `Article_Rewriter` and calling its `run()` method, which uses `Article_Rewriter_Loader` to register hooks.
*   **Confirmed:** Admin menus created (Main, Settings, Batch, License).
*   **Confirmed:** Admin settings registered via Settings API (API keys, defaults, batch size, editor toggles).
*   **Confirmed:** Admin CSS (`article-rewriter-admin.css`) and JS (`article-rewriter-admin.js`) are enqueued.
*   **Confirmed:** Data (nonce, settings, strings, etc.) passed to admin JS via `wp_localize_script`.
*   **Confirmed:** Block Editor integration via `build/index.js` (requires build step), CSS, and `wp_localize_script`.
*   **Confirmed:** Classic Editor integration via TinyMCE plugin (`article-rewriter-classic-editor.js`), button, CSS, `wp_localize_script`, and inline modal HTML/JS.
*   **Confirmed:** Both editors use REST endpoint `POST article-rewriter/v1/rewrite`. Classic editor also uses `GET article-rewriter/v1/history/{post_id}`.
*   **Confirmed:** Editor integrations and REST API endpoints check for active license status and `edit_posts` capability.
*   **Confirmed:** REST API handler (`Article_Rewriter_API`) routes requests to specific API methods (`rewrite_with_openai`, `rewrite_with_deepseek`).
*   **Confirmed:** OpenAI and DeepSeek integrations use `wp_remote_post` to call external APIs.
*   **Confirmed:** Rewrite history is saved to/retrieved from a custom DB table (`wp_article_rewriter_history`).
*   **Confirmed:** Batch processing initiated via `admin-post` hook, uses WP Cron (`article_rewriter_process_batch`) for background processing, and provides status via AJAX (`wp_ajax_article_rewriter_get_batch_jobs_status`).
*   **Confirmed:** Batch processing uses two custom DB tables (`wp_article_rewriter_batch`, `wp_article_rewriter_batch_items`).
*   **Resolved:** Refactored API logic into `Article_Rewriter_Service` and individual provider classes (`OpenAI_Provider`, `DeepSeek_Provider`, `Anthropic_Provider`, `Gemini_Provider`) extending `Abstract_API_Provider`. History saving remains in `Article_Rewriter_Service`.
*   **Resolved:** License activation and deactivation now both handled via AJAX hooks (`wp_ajax_article_rewriter_activate_license`, `wp_ajax_article_rewriter_deactivate_license`) triggered by button clicks in the admin UI.
*   **Confirmed:** Daily license check scheduled via WP Cron (`article_rewriter_license_check`).
*   **Confirmed:** Admin notices displayed based on license status (`article_rewriter_license_status` option).
*   **Confirmed:** License key, status, domain, activation/expiry dates stored in options.
*   **Identified Issue:** License server communication logic (`verify_purchase_code`, `deactivate_license`) are placeholders and need implementation.
*   **Resolved:** Batch processing UI/JS selector mismatch resolved by updating HTML partial (`admin/partials/article-rewriter-admin-batch.php`).
*   **Confirmed:** Custom DB tables (`wp_article_rewriter_history`, `wp_article_rewriter_batch`, `wp_article_rewriter_batch_items`) are created on activation via `dbDelta`.
*   **Confirmed:** Default options (API keys, license status, etc.) are added on activation.
*   **Confirmed:** Deactivation clears WP Cron hooks (`article_rewriter_license_check`, `article_rewriter_process_batch`) but does **not** remove DB tables or options.
*   **Confirmed:** Block Editor JS (`src/index.js`) uses React and standard WP packages (`@wordpress/data`, `@wordpress/api-fetch`, `@wordpress/components`) to create a sidebar for rewriting the entire post content and viewing/applying history via REST API calls.
*   **Confirmed:** Admin JS (`assets/js/article-rewriter-admin.js`) uses jQuery and AJAX for batch status polling/updates, license activation (via button click), and license deactivation (via button click).
*   **Resolved:** Admin JS AJAX calls for batch start are correctly handled. **Resolved:** License activation and deactivation AJAX calls added/updated and PHP handlers confirmed.
*   **Resolved:** Added missing PHP AJAX handlers for batch job details, cancel, and delete.
*   **Resolved:** Corrected nonce checks in all relevant PHP AJAX handlers to use `article_rewriter_nonce`.
*   **Confirmed:** Classic Editor JS (`assets/js/article-rewriter-classic-editor.js`) uses jQuery to add a TinyMCE button, open a modal, and interact with the `/rewrite` and `/history` REST endpoints (including nonce handling).

## 2. What's Left to Build / Verify

*   **Core Rewriting Logic:** Implementation for OpenAI, DeepSeek, Anthropic, and Gemini completed using the Service/Provider pattern. Need to verify the actual API calls work correctly.
*   **API Integration:** Test all provider integrations (OpenAI, DeepSeek, Anthropic, Gemini).
*   **Editor Integration:** Test the actual rewrite functionality and UI within both Classic and Block editors. Verify Classic Editor modal and history functionality. Verify Block Editor sidebar functionality.
*   **Batch Processing:** Verify the WP Cron job scheduling/execution. Test the batch UI (`admin/partials/article-rewriter-admin-batch.php`) and AJAX functionality (start, status, details, cancel, delete). Test handling of failed items.
*   **Licensing:** **Placeholder functions (`verify_purchase_code`, `deactivate_license`) updated to make actual HTTP requests to assumed server endpoints. `check_license` method (for cron) also updated to handle server responses.** Still requires building the actual license server application. Test the activation/deactivation flow via AJAX once server is ready. Verify the daily cron check correctly updates status based on server response. Test admin notices. Define `ARTICLE_REWRITER_SERVER_API_KEY` constant.
*   **Settings:** Verify that registered settings are correctly saved/retrieved and used by the plugin logic (e.g., API keys, selected models, defaults applied). Check the UI in `admin/partials/article-rewriter-admin-settings.php`. Verify default options set on activation are appropriate. **Model selection settings added and implemented.**
*   **Frontend Build Process:** Confirmed build uses `npm run build` via `@wordpress/scripts`. Verify the build process works and generates `build/index.js` correctly from `src/index.js`.
*   **Error Handling:** Review error handling in `Article_Rewriter_Service`, provider classes, REST handler, AJAX handlers, Block Editor JS, Classic Editor JS, Admin JS, and batch processing cron job.
*   **Security:** Review REST API permission checks, nonce usage (REST, AJAX), data sanitization, and DB interactions. Check `dbDelta` usage.
*   **Database:** Table creation confirmed. Deactivation cleanup only removes cron jobs, not tables/options. Consider if uninstall logic is needed/present.
*   **Code Quality:** API/History duplication addressed via Service/Provider pattern.
*   **Internationalization:** Confirm strings are correctly domain-loaded (`article-rewriter` text domain confirmed) for translation.

## 3. Current Status

*   **Analysis & Refactoring Phase:** Complete. AJAX conflicts resolved, missing handlers added, API logic refactored, providers implemented, model selection added.
*   **Code Functionality:** Core structure refactored and providers implemented. Initial user feedback indicates core API and features are working. License communication logic updated but untested pending server implementation. More thorough testing still recommended.

## 4. Known Issues / Blockers

*   **License Server Application:** The backend license server application needs to be built and deployed.
*   **License Server API Key:** The `ARTICLE_REWRITER_SERVER_API_KEY` constant needs to be defined in the WordPress environment.

*(Refactoring and provider implementation complete. Initial testing positive. License communication logic updated in plugin, but server implementation pending.)*
