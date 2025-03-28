# Article Rewriter WordPress Plugin - User Guide

Thank you for choosing the Article Rewriter plugin! This guide will walk you through installing, configuring, and using the plugin to rewrite your WordPress posts and pages using various AI providers.

## Table of Contents

1.  [Introduction](#introduction)
2.  [Requirements](#requirements)
3.  [Installation](#installation)
4.  [License Activation](#license-activation)
5.  [Configuration / Settings](#configuration--settings)
    *   [API Keys](#api-keys)
    *   [API Models](#api-models)
    *   [General Settings](#general-settings)
    *   [Editor Integration](#editor-integration)
6.  [Usage](#usage)
    *   [Block Editor (Gutenberg)](#block-editor-gutenberg)
    *   [Classic Editor (TinyMCE)](#classic-editor-tinymce)
    *   [Rewrite History](#rewrite-history)
7.  [Batch Processing](#batch-processing)
8.  [Troubleshooting](#troubleshooting)
9.  [Support](#support)

---

## 1. Introduction

Article Rewriter integrates powerful AI models (OpenAI, DeepSeek, Anthropic, Google Gemini) directly into your WordPress editor, allowing you to easily rewrite entire articles or selected content with different styles (Standard, Formal, Casual, Creative). It also includes a batch processing feature to rewrite multiple posts/pages automatically.

## 2. Requirements

*   WordPress version 5.0 or higher.
*   PHP version 7.4 or higher.
*   A valid license key/purchase code for this plugin.
*   API key(s) for the AI service(s) you intend to use (OpenAI, DeepSeek, Anthropic, Gemini).
*   Your website must be able to make outgoing HTTP requests (e.g., via `wp_remote_post`) for license verification and AI API calls.

## 3. Installation

1.  **Download:** Download the plugin ZIP file from the marketplace where you purchased it (e.g., CodeCanyon).
2.  **Upload via WordPress Admin:**
    *   Log in to your WordPress admin dashboard.
    *   Navigate to `Plugins` > `Add New`.
    *   Click the `Upload Plugin` button at the top.
    *   Click `Choose File` and select the downloaded plugin ZIP file (e.g., `article-rewriter.zip`).
    *   Click `Install Now`.
3.  **Activate:** Once the installation is complete, click the `Activate Plugin` button.

Alternatively, you can install manually via FTP:

1.  Unzip the downloaded plugin file (`article-rewriter.zip`).
2.  Upload the extracted `article-rewriter` folder to the `/wp-content/plugins/` directory on your web server.
3.  Log in to your WordPress admin dashboard.
4.  Navigate to `Plugins` > `Installed Plugins`.
5.  Locate "Article Rewriter" in the list and click `Activate`.

### Defining the Server API Key (Required for Licensing)

For the plugin to securely communicate with the license server for activation and verification, you **must** define a secret API key as a constant in your WordPress configuration. This key must match the key expected by your license server.

**Recommended Method: Edit `wp-config.php`**

1.  Connect to your server via FTP or use your hosting control panel's File Manager.
2.  Locate the `wp-config.php` file in the root directory of your WordPress installation.
3.  **Carefully** edit the file and add the following line, replacing `'YOUR_SECRET_SERVER_API_KEY'` with the actual secret key you have configured on your license server:

    ```php
    define('ARTICLE_REWRITER_SERVER_API_KEY', 'YOUR_SECRET_SERVER_API_KEY');
    ```
4.  Add this line somewhere **above** the line that says `/* That's all, stop editing! Happy publishing. */`.
5.  Save the `wp-config.php` file.

**Important:** Keep this key secret. Do not share it publicly. If you are unsure about editing `wp-config.php`, consult your web host or a developer.

---

## 4. License Activation

Upon activating the plugin, you will need to activate your license to enable all features, including AI rewriting and batch processing.

1.  Navigate to `Article Rewriter` > `License` in your WordPress admin menu.
2.  If your license is inactive, you will see the "Activate License" section.
3.  Enter your **Purchase Code** (obtained from the marketplace, e.g., CodeCanyon) into the input field.
4.  Click the `Activate License` button.
5.  The plugin will contact the license server to verify your code and activate the license for your domain (`<?php echo home_url(); ?>`).
6.  If successful, the page will reload, showing the "Active" status, your purchase code, domain, and expiration date.
7.  If activation fails, an error message will be displayed. Please double-check your purchase code and ensure your server can make outgoing connections to the license server.

**Deactivating Your License:**

If you need to move your license to a different domain, you must first deactivate it on the current domain.

1.  Navigate to `Article Rewriter` > `License`.
2.  If the license is active, click the `Deactivate License` button.
3.  Confirm the deactivation when prompted.
4.  The page will reload, showing the "Inactive" status.

---

## 5. Configuration / Settings

Configure the plugin's behavior by navigating to `Article Rewriter` > `Settings`.

### API Keys

Enter the API keys for the AI services you want to use. You need at least one key configured for the rewriting features to work. **Treat your API keys like passwords – keep them secure and do not share them.**

*   **OpenAI API Key:** Obtain from your OpenAI account dashboard under API Keys: [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys). You may need to set up billing.
*   **DeepSeek API Key:** Obtain from the DeepSeek Platform dashboard: [https://platform.deepseek.com/](https://platform.deepseek.com/).
*   **Anthropic API Key:** Obtain from the Anthropic Console under API Keys: [https://console.anthropic.com/settings/keys](https://console.anthropic.com/settings/keys).
*   **Google Gemini API Key:** Obtain from Google AI Studio: [https://aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey).

Keys are stored in your WordPress database and used only for making requests from your server to the respective AI services when you initiate a rewrite.

### API Models

For each API provider where you have entered a valid key, you can select the specific model you wish to use for rewriting. The available models are listed in the dropdown menus.

*   Different models have varying capabilities, speeds, context window sizes, and costs.
*   Consult the documentation of each AI provider (OpenAI, DeepSeek, Anthropic, Google) for details on their specific models.
*   Choose the model that best balances your needs for rewriting quality, processing speed, and potential API usage costs.
*   Examples:
    *   **OpenAI:** `gpt-4-turbo` (often faster and cheaper than `gpt-4`), `gpt-3.5-turbo` (very fast, lower cost, good for simpler tasks).
    *   **Anthropic:** `claude-3-opus` (most powerful), `claude-3-sonnet` (balanced), `claude-3-haiku` (fastest, most affordable).
    *   **Gemini:** `gemini-1.5-pro` (large context, advanced reasoning), `gemini-1.5-flash` (faster, optimized for efficiency).
    *   **DeepSeek:** `deepseek-chat` (general chat), `deepseek-coder` (code-focused, might be less ideal for article rewriting).

### General Settings

*   **Default API:** Select the AI provider that will be pre-selected by default in the editor interfaces (Block Editor sidebar, Classic Editor modal). You can always change it before rewriting.
*   **Default Rewrite Style:** Choose the rewriting style that will be pre-selected by default.
    *   **Standard:** A general rewrite aiming for different wording and sentence structure while preserving meaning.
    *   **Formal:** Uses more sophisticated vocabulary and complex sentences, suitable for academic or professional content.
    *   **Casual:** Uses simpler language and a conversational, friendly tone.
    *   **Creative:** Employs more vivid language, metaphors, or storytelling elements while trying to maintain the core meaning.
*   **Batch Size:** Set the maximum number of posts that can be selected for a single batch processing job (Default: 10). Higher numbers may take longer or hit server/API limits.

### Editor Integration

*   **Gutenberg Integration:** Enable or disable the Article Rewriter sidebar in the Block Editor (Gutenberg).
*   **Classic Editor Integration:** Enable or disable the Article Rewriter button in the Classic Editor (TinyMCE) toolbar.

Click `Save Changes` after modifying any settings.

---

## 6. Usage

Once the plugin is installed, activated, licensed, and configured with API keys, you can start rewriting content directly within the WordPress editor.

### Block Editor (Gutenberg)

1.  Open a post or page for editing using the Block Editor.
2.  Ensure the "Article Rewriter" sidebar is enabled (check the `Settings` > `Editor Integration` option if needed).
3.  Click the Article Rewriter icon (pencil icon) in the top-right toolbar to open the sidebar.
4.  **Select Options:** Choose your desired `API Provider` and `Rewriting Style` from the dropdowns in the sidebar.
5.  **Rewrite:** Click the `Rewrite Article` button. The plugin will send the *entire current content* of the editor to the selected AI service.
6.  **Compare:** Once the rewriting is complete, the sidebar will show a "Comparison" tab panel. You can switch between the "Original" and "Rewritten" tabs to see the differences.
7.  **Apply:** If you are satisfied with the rewritten content, click the `Apply Rewritten Content` button at the bottom of the comparison view. This will replace the content in your editor with the rewritten version.

### Classic Editor (TinyMCE)

1.  Open a post or page for editing using the Classic Editor.
2.  Ensure the "Article Rewriter" button is enabled (check the `Settings` > `Editor Integration` option if needed).
3.  Locate the "Rewrite" button (pencil icon) in the TinyMCE toolbar.
4.  Click the "Rewrite" button. This will open a modal dialog.
5.  **Select Options:** Inside the dialog, choose your desired `API Provider` and `Rewriting Style`. The "Original Content" textarea will be pre-filled with the current editor content.
6.  **Rewrite:** Click the `Rewrite` button within the dialog. The plugin will send the content from the "Original Content" textarea to the selected AI service.
7.  **Review:** Once complete, the rewritten text will appear in the "Rewritten Content" textarea below the original.
8.  **Apply:** If satisfied, click the `Apply Rewritten Content` button. This will replace the content in the main WordPress editor with the rewritten version and close the dialog.
9.  **Close:** You can close the dialog without applying changes by clicking the 'X' button in the top-right corner.

### Rewrite History (Both Editors)

*   **Block Editor:** Access the history via the "Rewrite History" panel in the Article Rewriter sidebar. Click the panel header to expand it.
*   **Classic Editor:** Click the "Show History" button within the Article Rewriter modal dialog.

In the history view:
*   You will see a list of previous rewrites for the current post, showing the API used, style, and date.
*   Click the `Apply This Version` button next to any history item to replace the current editor content with that specific historical version.

*(Note: History saving must be enabled in the plugin settings for this feature to work.)*

---

## 7. Batch Processing

The batch processing feature allows you to rewrite multiple posts or pages automatically in the background.

1.  Navigate to `Article Rewriter` > `Batch Processing` in your WordPress admin menu.
2.  **Configure Job:**
    *   Select the `API` provider and `Rewrite Style` to use for this batch.
    *   Review the maximum number of posts allowed per batch (configured in Settings).
3.  **Select Posts:**
    *   Check the boxes next to the posts and/or pages you want to include in the batch. You can use the "Select All" checkbox at the top of the list.
    *   Ensure you do not exceed the maximum batch size limit.
4.  **Create Batch:** Click the `Create Batch` button.
5.  **Processing:** The page will reload, and your new batch job will appear in the "Recent Batch Jobs" table with a "Pending" or "Processing" status. The rewriting happens in the background using WP Cron. You can leave the page.
6.  **Monitor Progress:**
    *   The "Recent Batch Jobs" table shows the status and progress (Processed / Total) of each job. The status will automatically update via AJAX polling if jobs are "in-progress".
    *   Click the `View` action link for a specific job to see the status of individual posts within that batch.
7.  **Actions:**
    *   **View:** See detailed progress for each post in the batch.
    *   **Cancel:** Stop a "Pending" or "Processing" job. Any remaining posts will not be rewritten.
    *   **Delete:** Remove a batch job and its associated item records from the database (this does not revert rewritten posts). Only Administrators can delete jobs.

*(Note: Batch processing relies on WP Cron. Ensure WP Cron is functioning correctly on your site. If posts seem stuck in "processing", check your WP Cron status or trigger it manually.)*

---

## 8. Troubleshooting

*   **License Activation Fails:**
    *   Double-check that you entered the correct Purchase Code.
    *   Ensure your website's server can make outgoing HTTP requests (specifically `wp_remote_post`) to the license server URL (`http://php-license-server.test/` or the production URL). Check firewall settings or contact your host if unsure.
    *   Verify the license server is running and accessible.
    *   Check the PHP error logs on your WordPress site and the license server for more specific error messages.
*   **Rewriting Fails / Error Messages:**
    *   Ensure you have entered a valid API key for the selected provider in the plugin Settings.
    *   Check that the selected API provider's service is operational (visit their status page).
    *   Make sure your website can make outgoing HTTP requests to the AI provider's API endpoint.
    *   Check the specific error message returned. It might indicate an issue with your API key, account limits, or the content being sent.
*   **Batch Processing Stuck:**
    *   Batch processing relies on WP Cron. Ensure WP Cron is enabled and running correctly on your site. You can use plugins like "WP Crontrol" to check scheduled events.
    *   If WP Cron is disabled or unreliable on your host, you may need to set up a real server-side cron job to trigger `wp-cron.php`. See [WordPress Cron documentation](https://developer.wordpress.org/plugins/cron/).
    *   Check the PHP error logs for any fatal errors occurring during the `article_rewriter_process_batch` action.
*   **Buttons/Sidebar Missing in Editor:**
    *   Go to `Article Rewriter` > `Settings` > `Editor Integration` and ensure the relevant editor integration (Gutenberg or Classic Editor) is enabled.
    *   Check for JavaScript errors in your browser's developer console. Conflicts with other plugins or themes can sometimes prevent JavaScript from running correctly.
    *   Ensure your license is active.

---

## 9. Support

If you encounter issues not covered in the Troubleshooting section, or if you have questions or feature requests, please contact support via [Your Support Channel Link/Email Here - e.g., the item comments section on CodeCanyon, your support website].

Please provide as much detail as possible when requesting support, including:

*   Your WordPress version.
*   Your PHP version.
*   The Article Rewriter plugin version.
*   The specific AI provider and model being used (if applicable).
*   A clear description of the issue.
*   Steps to reproduce the issue.
*   Any error messages displayed (copy and paste exact messages).

---
