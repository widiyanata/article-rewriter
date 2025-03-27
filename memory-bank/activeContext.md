# Active Context: Article Rewriter WordPress Plugin

## 1. Current Focus

*   **Initial Project Analysis:** The immediate focus is on understanding the existing codebase and project structure.
*   **Memory Bank Setup:** Establishing the core Memory Bank documentation based on initial file analysis.

## 2. Recent Changes

*   Created the `memory-bank` directory.
*   Created initial versions of:
    *   `projectbrief.md`
    *   `productContext.md`
    *   `systemPatterns.md`
    *   `techContext.md`

## 3. Next Steps

*   Update `systemPatterns.md` with findings from `class-article-rewriter.php`. (Done)
*   Analyze `includes/class-article-rewriter-loader.php` to understand the mechanism for registering and running hooks. (Done - Standard Loader pattern confirmed)
*   Analyze `admin/class-article-rewriter-admin.php` to understand admin-specific functionality. (Done)
*   Update Memory Bank (`systemPatterns.md`, `progress.md`) with findings from `class-article-rewriter-admin.php`. (Done)
*   Analyze `includes/class-article-rewriter-editor.php` to understand editor integration. (Done)
*   Update Memory Bank (`systemPatterns.md`, `progress.md`, `techContext.md`, `.clinerules`) with findings from `class-article-rewriter-editor.php`. (Done)
*   Analyze `includes/class-article-rewriter-api.php` to understand REST API implementation. (Done)
*   Update Memory Bank (`systemPatterns.md`, `progress.md`, `techContext.md`) with findings from `class-article-rewriter-api.php`. (Done)
*   Analyze `includes/class-article-rewriter-batch.php` to understand batch processing. (Done)
*   Update Memory Bank (`systemPatterns.md`, `progress.md`, `techContext.md`) with findings from `class-article-rewriter-batch.php`. (Done)
*   Analyze `includes/class-article-rewriter-license.php`. (Done)
*   Update Memory Bank (`systemPatterns.md`, `progress.md`, `techContext.md`) with findings from `class-article-rewriter-license.php`. (Done)
*   Analyze `package.json` to understand the build process. (Done - Confirmed `npm run build` using `@wordpress/scripts`)
*   Update Memory Bank (`progress.md`, `techContext.md`, `.clinerules`) with build process details. (Done)
*   Verify the specific external APIs used (DeepSeek, OpenAI implemented; Anthropic, Gemini placeholders). Note code duplication in Batch class.
*   Investigate `note.md` and `docs.html` in the root directory. (Done)
*   Update Memory Bank (`progress.md`, `techContext.md`) with batch UI issue and licensing source. (Done)
*   Analyze `includes/class-article-rewriter-activator.php` to confirm DB table creation. (Done)
*   Update Memory Bank (`progress.md`, `techContext.md`) with activator findings. (Done)
*   Analyze `includes/class-article-rewriter-deactivator.php`. (Done)
*   Update Memory Bank (`progress.md`) with deactivator findings. (Done)
*   Analysis of core PHP structure complete.
*   Analyze JavaScript (`src/index.js`, `assets/js/*`).
    *   `src/index.js` analyzed. (Done)
    *   Update Memory Bank (`progress.md`, `systemPatterns.md`) with Block Editor JS findings. (Done)
    *   Analyze `assets/js/article-rewriter-admin.js`. (Done)
    *   Update Memory Bank (`progress.md`, `systemPatterns.md`) with Admin JS findings and conflicts. (Done)
    *   Analyze `assets/js/article-rewriter-classic-editor.js`. (Done - Confirmed TinyMCE plugin, jQuery modal, REST API interaction for rewrite/history)
*   Update Memory Bank (`progress.md`, `systemPatterns.md`) with Classic Editor JS findings. (Done)
*   Core code analysis complete.
*   Address identified issues:
    *   Resolved AJAX vs `admin-post` conflicts for Batch Start, License Activate/Deactivate. (Done)
    *   Added missing PHP AJAX handlers for Batch Get Details, Cancel, Delete. (Done)
*   Update Memory Bank (`progress.md`, `systemPatterns.md`) with AJAX handler updates. (Done)
*   Refactored duplicated API/history logic into `Article_Rewriter_Service`. (Done)
*   Updated `Article_Rewriter_API` and `Article_Rewriter_Batch` to use the service. (Done)
*   Update Memory Bank (`progress.md`, `systemPatterns.md`, `.clinerules`) with refactoring details. (Done)
*   Verified Batch UI/JS selectors match after HTML partial update. (Done)
*   Update Memory Bank (`progress.md`) to reflect resolved batch UI/JS mismatch.
*   Next: Address remaining issues (placeholder code).

## 4. Active Decisions & Considerations

*   Confirmed the plugin entry point (`article-rewriter.php` instantiating `Article_Rewriter` and calling `run()`).
*   Confirmed `Article_Rewriter` acts as the central orchestrator, loading dependencies and defining hooks via separate classes and a `Loader` class.
*   Confirmed use of REST API, AJAX (`wp_ajax_`), and form submissions (`admin_post_`) for different functionalities.
*   Confirmed specific admin settings registered via Settings API.
*   Confirmed use of `wp_localize_script` to pass data (nonce, URLs, settings, strings) to admin JS and editor JS.
*   Identified potential API providers: DeepSeek, OpenAI, Anthropic, Gemini.
*   Validation of initial assumptions through code review is ongoing.
*   The purpose of `note.md` and `docs.html` in the root directory is now understood.
*   **Confirmed build step required:** Block editor uses `build/index.js`. Build command is `npm run build` using `@wordpress/scripts`.
*   **Resolved:** Batch/License AJAX vs `admin-post` conflicts.
*   **Resolved:** Missing PHP AJAX handlers for batch details/cancel/delete.
*   **Resolved:** Code duplication between API and Batch classes.
*   **Resolved:** Batch processing UI/JS selector mismatch.

*(This context reflects the very beginning of the analysis process.)*
