/**
 * All of the JS for your classic editor functionality should be
 * included in this file.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/assets/js
 */

(function ($) {
  "use strict";

  /**
   * Initialize the classic editor functionality
   */
  function initClassicEditor() {
    // Add the rewrite button to the TinyMCE editor
    if (typeof tinymce !== "undefined") {
      tinymce.PluginManager.add("article_rewriter", function (editor, url) {
        // Add a button that opens a dialog
        editor.addButton("article_rewriter", {
          text: articleRewriterClassic.i18n.rewrite,
          icon: "edit",
          tooltip: articleRewriterClassic.i18n.rewrite_tooltip,
          onclick: function () {
            openRewriteDialog(editor);
          },
        });
      });
    }

    // Handle dialog form submission
    $(document).on("submit", "#article-rewriter-dialog-form", function (e) {
      e.preventDefault();

      const $form = $(this);
      const $submitButton = $form.find('button[type="submit"]');
      const $spinner = $form.find(".spinner");
      const $message = $form.find(".article-rewriter-message");

      const api = $form.find('select[name="api"]').val();
      const style = $form.find('select[name="style"]').val();
      const content = $form.find('textarea[name="content"]').val();
      const postId = $form.find('input[name="post_id"]').val();

      // Show loading state
      $submitButton.prop("disabled", true);
      $spinner.addClass("is-active");
      $message.empty();

      // Send AJAX request to rewrite the content
      $.ajax({
        url: articleRewriterClassic.rest_url + "article-rewriter/v1/rewrite",
        method: "POST",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", articleRewriterClassic.nonce);
        },
        data: {
          post_id: postId,
          content: content,
          api: api,
          style: style,
        },
        success: function (response) {
          // Reset loading state
          $submitButton.prop("disabled", false);
          $spinner.removeClass("is-active");

          // Show success message
          $message.html(
            '<div class="notice notice-success"><p>' +
              articleRewriterClassic.i18n.success +
              "</p></div>"
          );

          // Update the textarea with the rewritten content
          $form.find('textarea[name="rewritten_content"]').val(response.content);

          // Show the rewritten content and apply button
          $form.find(".article-rewriter-rewritten").show();
        },
        error: function (xhr) {
          // Reset loading state
          $submitButton.prop("disabled", false);
          $spinner.removeClass("is-active");

          // Show error message - Escape potentially unsafe message from server
          let errorMessage = articleRewriterClassic.i18n.error;
          if (xhr.responseJSON && xhr.responseJSON.message) {
            // Basic escaping: replace HTML tags
            errorMessage = $("<div>").text(xhr.responseJSON.message).html();
          }

          $message.html('<div class="notice notice-error"><p>' + errorMessage + "</p></div>");
        },
      });
    });

    // Handle apply button click
    $(document).on("click", "#article-rewriter-apply", function (e) {
      e.preventDefault();

      const rewrittenContent = $(
        '#article-rewriter-dialog-form textarea[name="rewritten_content"]'
      ).val();
      const editor = tinymce.activeEditor;

      // Insert the rewritten content into the editor
      editor.setContent(rewrittenContent);

      // Close the dialog
      closeRewriteDialog();
    });

    // Handle dialog close button click
    $(document).on("click", "#article-rewriter-dialog-close", function (e) {
      e.preventDefault();
      closeRewriteDialog();
    });

    // Handle history button click
    $(document).on("click", "#article-rewriter-history-button", function (e) {
      e.preventDefault();

      const $historyButton = $(this);
      const $historySection = $("#article-rewriter-history");
      const $historyContent = $("#article-rewriter-history-content");
      const postId = $('#article-rewriter-dialog-form input[name="post_id"]').val();

      if ($historySection.is(":visible")) {
        $historySection.hide();
        $historyButton.text(articleRewriterClassic.i18n.show_history);
        return;
      }

      // Show loading state
      $historyContent.html('<div class="spinner is-active"></div>');
      $historySection.show();
      $historyButton.text(articleRewriterClassic.i18n.hide_history);

      // Load history
      $.ajax({
        url: articleRewriterClassic.rest_url + "article-rewriter/v1/history/" + postId,
        method: "GET",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", articleRewriterClassic.nonce);
        },
        success: function (response) {
          if (response.length === 0) {
            $historyContent.html("<p>" + articleRewriterClassic.i18n.no_history + "</p>");
            return;
          }

          let html = '<ul class="article-rewriter-history-list">';

          // Helper function for basic HTML escaping in JS
          const escapeHtml = (unsafe) => {
            return $("<div>").text(unsafe).html();
          };

          response.forEach(function (item) {
            html += '<li class="article-rewriter-history-item">';
            html += '<div class="article-rewriter-history-item-header">';
            html +=
              '<span class="article-rewriter-history-item-api">' + escapeHtml(item.api) + "</span>"; // Escaped
            html +=
              '<span class="article-rewriter-history-item-date">' +
              escapeHtml(item.date) +
              "</span>"; // Escaped date just in case
            html += "</div>";
            html +=
              '<div class="article-rewriter-history-item-style">' +
              articleRewriterClassic.i18n.style + // This is translated, assumed safe
              ": " +
              escapeHtml(item.style) + // Escaped
              "</div>";
            html +=
              '<button type="button" class="button article-rewriter-history-apply" data-content="' +
              encodeURIComponent(item.content) +
              '">' +
              articleRewriterClassic.i18n.apply_version +
              "</button>";
            html += "</li>";
          });

          html += "</ul>";

          $historyContent.html(html);
        },
        error: function () {
          $historyContent.html(
            '<div class="notice notice-error"><p>' +
              articleRewriterClassic.i18n.history_error +
              "</p></div>"
          );
        },
      });
    });

    // Handle history item apply button click
    $(document).on("click", ".article-rewriter-history-apply", function (e) {
      e.preventDefault();

      const content = decodeURIComponent($(this).data("content"));
      const editor = tinymce.activeEditor;

      // Insert the content into the editor
      editor.setContent(content);

      // Close the dialog
      closeRewriteDialog();
    });
  }

  /**
   * Open the rewrite dialog
   */
  function openRewriteDialog(editor) {
    // Get the current content from the editor
    const content = editor.getContent();
    const postId = $("#post_ID").val();

    // Create the dialog if it doesn't exist
    if (!$("#article-rewriter-dialog").length) {
      const dialogHtml = `
                <div id="article-rewriter-dialog" class="article-rewriter-dialog">
                    <div class="article-rewriter-dialog-content">
                        <div class="article-rewriter-dialog-header">
                            <h2>${articleRewriterClassic.i18n.rewrite_article}</h2>
                            <button type="button" id="article-rewriter-dialog-close" class="article-rewriter-dialog-close">&times;</button>
                        </div>
                        <div class="article-rewriter-dialog-body">
                            <form id="article-rewriter-dialog-form">
                                <input type="hidden" name="post_id" value="${postId}">
                                
                                <div class="article-rewriter-options">
                                    <div class="article-rewriter-option">
                                        <label for="article-rewriter-api">${articleRewriterClassic.i18n.api_provider}</label>
                                        <select name="api" id="article-rewriter-api">
                                            <option value="openai">OpenAI</option>
                                            <option value="deepseek">DeepSeek</option>
                                            <option value="anthropic">Anthropic</option>
                                            <option value="gemini">Google Gemini</option>
                                        </select>
                                    </div>
                                    
                                    <div class="article-rewriter-option">
                                        <label for="article-rewriter-style">${articleRewriterClassic.i18n.rewriting_style}</label>
                                        <select name="style" id="article-rewriter-style">
                                            <option value="standard">${articleRewriterClassic.i18n.standard}</option>
                                            <option value="formal">${articleRewriterClassic.i18n.formal}</option>
                                            <option value="casual">${articleRewriterClassic.i18n.casual}</option>
                                            <option value="creative">${articleRewriterClassic.i18n.creative}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="article-rewriter-content">
                                    <label for="article-rewriter-content">${articleRewriterClassic.i18n.original_content}</label>
                                    <textarea name="content" id="article-rewriter-content" rows="10">${content}</textarea>
                                </div>
                                
                                <div class="article-rewriter-message"></div>
                                
                                <div class="article-rewriter-actions">
                                    <button type="submit" class="button button-primary">
                                        ${articleRewriterClassic.i18n.rewrite}
                                    </button>
                                    <span class="spinner"></span>
                                    <button type="button" id="article-rewriter-history-button" class="button">
                                        ${articleRewriterClassic.i18n.show_history}
                                    </button>
                                </div>
                                
                                <div id="article-rewriter-history" class="article-rewriter-history" style="display: none;">
                                    <h3>${articleRewriterClassic.i18n.rewrite_history}</h3>
                                    <div id="article-rewriter-history-content"></div>
                                </div>
                                
                                <div class="article-rewriter-rewritten" style="display: none;">
                                    <h3>${articleRewriterClassic.i18n.rewritten_content}</h3>
                                    <textarea name="rewritten_content" id="article-rewriter-rewritten-content" rows="10"></textarea>
                                    <div class="article-rewriter-actions">
                                        <button type="button" id="article-rewriter-apply" class="button button-primary">
                                            ${articleRewriterClassic.i18n.apply_content}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;

      $("body").append(dialogHtml);
    } else {
      // Update the content and post ID
      $("#article-rewriter-content").val(content);
      $('#article-rewriter-dialog-form input[name="post_id"]').val(postId);

      // Reset the form
      $("#article-rewriter-dialog-form .article-rewriter-message").empty();
      $("#article-rewriter-dialog-form .article-rewriter-rewritten").hide();
      $("#article-rewriter-history").hide();
      $("#article-rewriter-history-button").text(articleRewriterClassic.i18n.show_history);
    }

    // Show the dialog
    $("#article-rewriter-dialog").show();
  }

  /**
   * Close the rewrite dialog
   */
  function closeRewriteDialog() {
    $("#article-rewriter-dialog").hide();
  }

  // Initialize when the DOM is ready
  $(document).ready(initClassicEditor);
})(jQuery);
