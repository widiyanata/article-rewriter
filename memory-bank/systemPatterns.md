# System Patterns: Article Rewriter WordPress Plugin

## 1. Architecture Overview

*   **Standard WordPress Plugin Structure:** The project follows a common WordPress plugin layout with a main plugin file (`article-rewriter.php`), `includes` for PHP classes, `admin` for admin-specific functionality, `assets` for CSS/JS, and `languages` for localization.
*   **Object-Oriented PHP:** The use of PHP classes (e.g., `Article_Rewriter`, `Article_Rewriter_Admin`, `Article_Rewriter_API`) suggests an object-oriented approach.
*   **Hook-Based Integration:** Confirmed. The plugin uses `Article_Rewriter_Loader` to register WordPress actions and filters defined in dedicated classes (`Article_Rewriter_Admin`, `Article_Rewriter_Editor`, etc.).
*   **Client-Server Interaction:**
    *   Confirmed use of AJAX (`wp_ajax_`) and form submissions (`admin_post_`) for admin operations (batch, license).
    *   Confirmed use of REST API (`rest_api_init`) via `Article_Rewriter_API`.
    *   Editor integration (Classic & Block) uses JavaScript to interact with the editor UI and likely makes AJAX calls to the backend for rewriting.
*   **External API Dependency:** The `class-article-rewriter-api.php` strongly indicates interaction with an external service for the actual rewriting process.

## 2. Key Technical Decisions (Inferred)

*   **PHP for Backend Logic:** Standard for WordPress plugins.
*   **JavaScript for Frontend Interactivity:** Used for admin UI enhancements and editor integration.
*   **CSS for Styling:** Separate CSS files for admin, block editor, and classic editor suggest tailored styling.
*   **WordPress Coding Standards:** Likely adherence (or attempted adherence) to WordPress PHP and JS coding standards.
*   **Dependency Management:** `package.json` suggests Node.js/npm might be used for frontend asset building (compiling JS/CSS, possibly from `src/`).

## 3. Design Patterns (Potential)

*   **Singleton or Main Plugin Class:** Confirmed. `Article_Rewriter` is instantiated once in `article-rewriter.php` and orchestrates setup.
*   **Loader Pattern:** Confirmed. `Article_Rewriter` uses `Article_Rewriter_Loader` to collect and register all hooks.
*   **MVC/Separation of Concerns:** Confirmed for admin area. `Article_Rewriter_Admin` acts as a controller, handling logic (menus, settings registration, asset enqueuing) and including partial files (`admin/partials/*.php`) for the view/HTML rendering.
*   **Dependency Injection:** Basic DI observed - `Article_Rewriter` passes `$plugin_name` and `$version` to instantiated classes (e.g., `Article_Rewriter_Admin`). More complex DI not yet confirmed.
*   **Data Localization:** Confirmed use of `wp_localize_script` in `Article_Rewriter_Admin` to pass backend data (settings, nonce, URLs, translations) to frontend JavaScript (`article-rewriter-admin.js`).

## 4. Component Relationships (Initial View)

```mermaid
graph TD
    WP[WordPress Core]
    PluginFile[article-rewriter.php] --> Main[Article_Rewriter]

    subgraph Initialization
        Main -- instantiates --> Loader[Article_Rewriter_Loader]
        Main -- instantiates --> I18n[Article_Rewriter_i18n]
        Main -- instantiates --> Admin[Article_Rewriter_Admin]
        Main -- instantiates --> Editor[Article_Rewriter_Editor]
        Main -- instantiates --> API[Article_Rewriter_API]
        Main -- instantiates --> Batch[Article_Rewriter_Batch]
        Main -- instantiates --> License[Article_Rewriter_License]

        Main -- calls define_*_hooks --> Loader -- adds hooks via add_action/add_filter --> HookDefs{Hook Definitions}

        I18n -- defines hooks for --> Loader
        Admin -- defines hooks for --> Loader
        Editor -- defines hooks for --> Loader
        API -- defines hooks for --> Loader
        Batch -- defines hooks for --> Loader
        License -- defines hooks for --> Loader
    end

    subgraph Runtime
        Main -- calls run() --> Loader -- calls run() --> WPHooks[Registers Hooks w/ WordPress]
        WPHooks -- triggers --> AdminMethods[Admin Methods]
        WPHooks -- triggers --> EditorMethods[Editor Methods]
        WPHooks -- triggers --> APIMethods[API Methods]
        WPHooks -- triggers --> BatchMethods[Batch Methods]
        WPHooks -- triggers --> LicenseMethods[License Methods]

        AdminMethods -- includes --> AdminPartials[admin/partials/*.php]
        AdminMethods -- enqueues --> AdminJS[assets/js/article-rewriter-admin.js]
        AdminMethods -- enqueues --> AdminCSS[assets/css/article-rewriter-admin.css]
        AdminMethods -- wp_localize_script --> AdminJSData(Localized Data: nonce, settings, etc.)
        AdminJSData -- used by --> AdminJS
        AdminJS <-.-> WPAjax(WP admin-ajax.php / admin-post.php)
        AdminMethods <-.-> WPAjax
        AdminMethods -- registers --> WpSettingsAPI[WP Settings API]
        WpSettingsAPI -- stores/retrieves --> WpOptionsDb[(wp_options table)]

        EditorMethods -- enqueues --> BlockEditorJS[build/index.js (React App)]
        EditorMethods -- enqueues --> BlockEditorCSS[assets/css/article-rewriter-block-editor.css]
        EditorMethods -- adds TinyMCE plugin --> ClassicEditorJS[assets/js/article-rewriter-classic-editor.js]
        EditorMethods -- enqueues --> ClassicEditorCSS[assets/css/article-rewriter-classic-editor.css]
        EditorMethods -- wp_localize_script --> EditorJSData(Localized Data: REST endpoint, nonce, settings, etc.)
        EditorJSData -- used by --> BlockEditorJS
        EditorJSData -- used by --> ClassicEditorJS
        BlockEditorJS -- uses --> WpData[(@wordpress/data)]
        BlockEditorJS -- uses --> WpApiFetch[(@wordpress/api-fetch)]
        WpData -- reads --> EditorContentState[(Editor Content)]
        WpData -- dispatches actions --> EditorContentState
        WpApiFetch -- POST --> WpRestApiRewrite(REST POST: /rewrite)
        WpApiFetch -- GET --> WpRestApiHistory(REST GET: /history/{post_id})
        ClassicEditorJS <-.-> WpRestApiRewrite
        ClassicEditorJS <-.-> WpRestApiHistory

        APIMethods -- registers route --> WpRestApiRewrite
        APIMethods -- registers route --> WpRestApiHistory
        APIMethods -- handles /rewrite --> RewriteLogic{rewrite_with_api}
        APIMethods -- handles /history --> HistoryLogic{get_history}
        RewriteLogic -- calls --> Service[Article_Rewriter_Service]
        Service -- calls --> OpenAIMethod[rewrite_with_openai]
        Service -- calls --> DeepSeekMethod[rewrite_with_deepseek]
        Service -- calls --> AnthropicMethod[rewrite_with_anthropic (Placeholder)]
        Service -- calls --> GeminiMethod[rewrite_with_gemini (Placeholder)]
        RewriteLogic -- calls --> ServiceSaveHistory[Service::save_history]
        ServiceSaveHistory -- writes to --> HistoryDB[(wp_article_rewriter_history table)]
        HistoryLogic -- reads from --> HistoryDB[(wp_article_rewriter_history table)]

        BatchMethods -- handles wp_ajax_ --> StartBatch[handle_start_batch]
        BatchMethods -- handles wp_ajax_ --> GetStatus[handle_get_batch_jobs_status]
        BatchMethods -- handles wp_ajax_ --> GetJobDetails[handle_get_batch_job]
        BatchMethods -- handles wp_ajax_ --> CancelJob[handle_cancel_batch_job]
        BatchMethods -- handles wp_ajax_ --> DeleteJob[handle_delete_batch_job]
        BatchMethods -- handles WP Cron --> ProcessBatch[process_batch]
        StartBatch -- creates job --> BatchDB[(wp_article_rewriter_batch table)]
        StartBatch -- creates items --> BatchItemsDB[(wp_article_rewriter_batch_items table)]
        StartBatch -- schedules --> WPCron(article_rewriter_process_batch)
        ProcessBatch -- reads/updates --> BatchDB
        ProcessBatch -- reads/updates --> BatchItemsDB
        ProcessBatch -- calls --> ServiceRewrite[Service::rewrite_content]
        ProcessBatch -- calls --> ServiceSaveHistory
        ProcessBatch -- re-schedules --> WPCron
        GetStatus -- reads from --> BatchDB
        GetStatus -- returns JSON --> AdminJS
        GetJobDetails -- reads from --> BatchDB
        GetJobDetails -- reads from --> BatchItemsDB
        GetJobDetails -- returns JSON --> AdminJS
        CancelJob -- updates --> BatchDB
        CancelJob -- updates --> BatchItemsDB
        CancelJob -- clears schedule --> WPCron
        CancelJob -- returns JSON --> AdminJS
        DeleteJob -- deletes from --> BatchDB
        DeleteJob -- deletes from --> BatchItemsDB
        DeleteJob -- clears schedule --> WPCron
        DeleteJob -- returns JSON --> AdminJS

        LicenseMethods -- handles wp_ajax_ --> ActivateLicense[handle_activate_license]
        LicenseMethods -- handles wp_ajax_ --> DeactivateLicense[handle_deactivate_license]
        LicenseMethods -- handles WP Cron --> CheckLicense[check_license]
        LicenseMethods -- handles admin_notices --> DisplayNotices[admin_notices]
        AdminJS -- AJAX POST --> ActivateLicense
        AdminJS -- AJAX POST --> DeactivateLicense
        ActivateLicense -- calls --> VerifyPurchase[verify_purchase_code (Placeholder)]
        ActivateLicense -- updates --> WpOptionsDb[(license options)]
        ActivateLicense -- schedules --> LicenseCron(article_rewriter_license_check)
        DeactivateLicense -- calls --> DeactivateServer[deactivate_license (Placeholder)]
        DeactivateLicense -- updates --> WpOptionsDb
        DeactivateLicense -- clears schedule --> LicenseCron
        CheckLicense -- calls --> VerifyPurchase
        CheckLicense -- updates --> WpOptionsDb
        DisplayNotices -- reads --> WpOptionsDb
        VerifyPurchase -- wp_remote_post (intended) --> ExternalLicense[(External License Service: widigital.com)]
        DeactivateServer -- wp_remote_post (intended) --> ExternalLicense

        OpenAIMethod -- wp_remote_post --> ExternalAPI[(External Rewriting Service: OpenAI, DeepSeek, Anthropic, Gemini)]
        DeepSeekMethod -- wp_remote_post --> ExternalAPI
    end

    WP --> PluginFile
    WP --> WPHooks
    WP --> HistoryDB

    style ExternalAPI fill:#f9f,stroke:#333,stroke-width:2px
    style ExternalLicense fill:#f9f,stroke:#333,stroke-width:2px
    style HistoryDB fill:#ccf,stroke:#333,stroke-width:2px
    style BatchDB fill:#cfc,stroke:#333,stroke-width:2px
    style BatchItemsDB fill:#cfc,stroke:#333,stroke-width:2px
```

*(Diagram updated based on Batch class analysis. Includes batch DB tables, WP Cron usage, AJAX status check, and notes duplicated logic.)*
