# Project Brief: Article Rewriter WordPress Plugin

## 1. Project Overview

This project is a WordPress plugin named "Article Rewriter". Based on the file structure, it appears to provide functionality for rewriting article content within the WordPress environment.

## 2. Core Requirements

*   Integrate with the WordPress admin interface.
*   Provide article rewriting capabilities using external APIs (Confirmed: **DeepSeek, OpenAI, and others** mentioned).
*   Support both classic and block editors (suggested by `assets/css` and `assets/js` files).
*   Include batch processing functionality (`class-article-rewriter-batch.php`).
*   Manage licensing (`class-article-rewriter-license.php`).
*   Handle activation/deactivation hooks (`class-article-rewriter-activator.php`, `class-article-rewriter-deactivator.php`).
*   Support internationalization (`class-article-rewriter-i18n.php`, `languages/`).

## 3. Goals

*   Provide users with a tool to easily rewrite WordPress post/page content.
*   Offer settings for configuration and license management.
*   Ensure compatibility with standard WordPress editor experiences.

## 4. Scope

*   **In Scope:** Core rewriting functionality, admin settings, editor integration, batch processing, licensing.
*   **Out of Scope:** (To be determined - initially assuming no external integrations beyond the rewriting API).

## 5. Key Stakeholders

*   Plugin users (WordPress site administrators/editors).
*   Plugin developer(s).

*(This is an initial assessment based on file structure. It will be refined as more context is gathered.)*
