/**
 * All of the JS for your admin-specific functionality should be
 * included in this file.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/admin/js
 */

(function ($) {
  "use strict";

  /**
   * Initialize the admin functionality
   */
  function initAdmin() {
    initBatchProcessing();
    initLicenseManagement();
  }

  /**
   * Initialize batch processing functionality
   */
  function initBatchProcessing() {
    const $batchForm = $("#article-rewriter-batch-form");
    const $batchTable = $("#article-rewriter-batch-posts-table");
    const $batchSubmit = $("#article-rewriter-batch-submit");
    const $batchJobsTable = $("#article-rewriter-batch-jobs-table");
    const $batchJobDetails = $("#article-rewriter-batch-job-details");

    if (!$batchForm.length) {
      return;
    }

    // Handle batch form submission
    $batchForm.on("submit", function (e) {
      e.preventDefault();

      const formData = $(this).serialize();

      // Disable submit button and show loading state
      $batchSubmit.prop("disabled", true).text(articleRewriterAdmin.i18n.processing);

      // Send AJAX request to start batch processing
      $.ajax({
        url: articleRewriterAdmin.ajax_url,
        type: "POST",
        data: {
          action: "article_rewriter_start_batch",
          nonce: articleRewriterAdmin.nonce,
          ...formData,
        },
        success: function (response) {
          if (response.success) {
            // Reload the page to show the new batch job
            window.location.reload();
          } else {
            alert(response.data || articleRewriterAdmin.i18n.error);
            $batchSubmit.prop("disabled", false).text(articleRewriterAdmin.i18n.submit);
          }
        },
        error: function () {
          alert(articleRewriterAdmin.i18n.error);
          $batchSubmit.prop("disabled", false).text(articleRewriterAdmin.i18n.submit);
        },
      });
    });

    // Handle batch job actions (view, cancel, delete)
    $batchJobsTable.on("click", ".article-rewriter-batch-action", function (e) {
      e.preventDefault();

      const $this = $(this);
      const action = $this.data("action");
      const jobId = $this.data("job-id");

      if (action === "view") {
        loadBatchJobDetails(jobId);
      } else if (action === "cancel" || action === "delete") {
        if (confirm(articleRewriterAdmin.i18n[`confirm_${action}`])) {
          performBatchJobAction(action, jobId);
        }
      }
    });

    // Initialize batch job polling if there are active jobs
    if ($batchJobsTable.find(".article-rewriter-batch-status.in-progress").length) {
      pollActiveBatchJobs();
    }
  }

  /**
   * Load batch job details
   */
  function loadBatchJobDetails(jobId) {
    const $batchJobDetails = $("#article-rewriter-batch-job-details");

    $batchJobDetails.html("<p>" + articleRewriterAdmin.i18n.loading + "</p>");

    $.ajax({
      url: articleRewriterAdmin.ajax_url,
      type: "POST",
      data: {
        action: "article_rewriter_get_batch_job",
        nonce: articleRewriterAdmin.nonce,
        job_id: jobId,
      },
      success: function (response) {
        if (response.success) {
          renderBatchJobDetails(response.data);
        } else {
          $batchJobDetails.html(
            "<p>" + (response.data || articleRewriterAdmin.i18n.error) + "</p>"
          );
        }
      },
      error: function () {
        $batchJobDetails.html("<p>" + articleRewriterAdmin.i18n.error + "</p>");
      },
    });
  }

  /**
   * Render batch job details
   */
  function renderBatchJobDetails(job) {
    const $batchJobDetails = $("#article-rewriter-batch-job-details");

    let html = "<h3>" + articleRewriterAdmin.i18n.job_details + "</h3>";

    html += '<table class="widefat">';
    html += "<tr><th>" + articleRewriterAdmin.i18n.job_id + "</th><td>" + job.id + "</td></tr>";
    html +=
      "<tr><th>" +
      articleRewriterAdmin.i18n.status +
      '</th><td><span class="article-rewriter-batch-status ' +
      job.status +
      '">' +
      job.status_label +
      "</span></td></tr>";
    html +=
      "<tr><th>" + articleRewriterAdmin.i18n.created + "</th><td>" + job.created_at + "</td></tr>";
    html +=
      "<tr><th>" + articleRewriterAdmin.i18n.updated + "</th><td>" + job.updated_at + "</td></tr>";
    html += "<tr><th>" + articleRewriterAdmin.i18n.api + "</th><td>" + job.api + "</td></tr>";
    html += "<tr><th>" + articleRewriterAdmin.i18n.style + "</th><td>" + job.style + "</td></tr>";
    html +=
      "<tr><th>" +
      articleRewriterAdmin.i18n.progress +
      "</th><td>" +
      job.processed +
      " / " +
      job.total +
      "</td></tr>";
    html += "</table>";

    if (job.posts && job.posts.length) {
      html += "<h3>" + articleRewriterAdmin.i18n.posts + "</h3>";
      html += '<table class="widefat">';
      html += "<thead><tr>";
      html += "<th>" + articleRewriterAdmin.i18n.post_title + "</th>";
      html += "<th>" + articleRewriterAdmin.i18n.status + "</th>";
      html += "<th>" + articleRewriterAdmin.i18n.actions + "</th>";
      html += "</tr></thead>";
      html += "<tbody>";

      job.posts.forEach(function (post) {
        html += "<tr>";
        html += '<td><a href="' + post.edit_url + '" target="_blank">' + post.title + "</a></td>";
        html +=
          '<td><span class="article-rewriter-batch-status ' +
          post.status +
          '">' +
          post.status_label +
          "</span></td>";
        html += "<td>";
        if (post.status === "completed") {
          html +=
            '<a href="' +
            post.view_url +
            '" target="_blank">' +
            articleRewriterAdmin.i18n.view +
            "</a>";
        }
        html += "</td>";
        html += "</tr>";
      });

      html += "</tbody></table>";
    }

    $batchJobDetails.html(html);
  }

  /**
   * Perform batch job action (cancel, delete)
   */
  function performBatchJobAction(action, jobId) {
    $.ajax({
      url: articleRewriterAdmin.ajax_url,
      type: "POST",
      data: {
        action: "article_rewriter_" + action + "_batch_job",
        nonce: articleRewriterAdmin.nonce,
        job_id: jobId,
      },
      success: function (response) {
        if (response.success) {
          window.location.reload();
        } else {
          alert(response.data || articleRewriterAdmin.i18n.error);
        }
      },
      error: function () {
        alert(articleRewriterAdmin.i18n.error);
      },
    });
  }

  /**
   * Poll active batch jobs for updates
   */
  function pollActiveBatchJobs() {
    const activeJobIds = [];

    $(".article-rewriter-batch-status.in-progress").each(function () {
      const jobId = $(this).closest("tr").data("job-id");
      if (jobId) {
        activeJobIds.push(jobId);
      }
    });

    if (!activeJobIds.length) {
      return;
    }

    $.ajax({
      url: articleRewriterAdmin.ajax_url,
      type: "POST",
      data: {
        action: "article_rewriter_get_batch_jobs_status",
        nonce: articleRewriterAdmin.nonce,
        job_ids: activeJobIds,
      },
      success: function (response) {
        if (response.success && response.data) {
          updateBatchJobsStatus(response.data);

          // Continue polling if there are still active jobs
          if (response.data.some((job) => job.status === "in-progress")) {
            setTimeout(pollActiveBatchJobs, 5000);
          }
        }
      },
      error: function () {
        // Try again after a delay
        setTimeout(pollActiveBatchJobs, 10000);
      },
    });
  }

  /**
   * Update batch jobs status in the table
   */
  function updateBatchJobsStatus(jobs) {
    jobs.forEach(function (job) {
      const $row = $('tr[data-job-id="' + job.id + '"]');

      if ($row.length) {
        // Update status
        $row
          .find(".article-rewriter-batch-status")
          .removeClass("pending in-progress completed failed")
          .addClass(job.status)
          .text(job.status_label);

        // Update progress
        $row.find(".article-rewriter-batch-progress-bar").css("width", job.progress + "%");
        $row.find(".article-rewriter-batch-progress-text").text(job.processed + " / " + job.total);

        // Update updated_at
        $row.find(".article-rewriter-batch-updated").text(job.updated_at);

        // If job is completed or failed, remove the cancel button
        if (job.status === "completed" || job.status === "failed") {
          $row.find('.article-rewriter-batch-action[data-action="cancel"]').remove();
        }

        // If the job details are currently shown, update them
        const $details = $("#article-rewriter-batch-job-details");
        if ($details.length && $details.data("job-id") === job.id) {
          loadBatchJobDetails(job.id);
        }
      }
    });
  }

  /**
   * Initialize license management functionality
   */
  function initLicenseManagement() {
    const $activateForm = $("#article-rewriter-license-activate-form");
    const $deactivateForm = $("#article-rewriter-license-deactivate-form");

    if (!$activateForm.length && !$deactivateForm.length) {
      return;
    }

    // Handle license activation
    $activateForm.on("submit", function (e) {
      e.preventDefault();

      const $submitButton = $(this).find('input[type="submit"]');
      const purchaseCode = $(this).find('input[name="purchase_code"]').val();

      if (!purchaseCode) {
        alert(articleRewriterAdmin.i18n.enter_purchase_code);
        return;
      }

      $submitButton.prop("disabled", true).val(articleRewriterAdmin.i18n.activating);

      $.ajax({
        url: articleRewriterAdmin.ajax_url,
        type: "POST",
        data: {
          action: "article_rewriter_activate_license",
          nonce: articleRewriterAdmin.nonce,
          purchase_code: purchaseCode,
        },
        success: function (response) {
          if (response.success) {
            window.location.reload();
          } else {
            alert(response.data || articleRewriterAdmin.i18n.activation_error);
            $submitButton.prop("disabled", false).val(articleRewriterAdmin.i18n.activate);
          }
        },
        error: function () {
          alert(articleRewriterAdmin.i18n.activation_error);
          $submitButton.prop("disabled", false).val(articleRewriterAdmin.i18n.activate);
        },
      });
    });

    // Handle license deactivation
    $deactivateForm.on("submit", function (e) {
      e.preventDefault();

      if (!confirm(articleRewriterAdmin.i18n.confirm_deactivate)) {
        return;
      }

      const $submitButton = $(this).find('input[type="submit"]');

      $submitButton.prop("disabled", true).val(articleRewriterAdmin.i18n.deactivating);

      $.ajax({
        url: articleRewriterAdmin.ajax_url,
        type: "POST",
        data: {
          action: "article_rewriter_deactivate_license",
          nonce: articleRewriterAdmin.nonce,
        },
        success: function (response) {
          if (response.success) {
            window.location.reload();
          } else {
            alert(response.data || articleRewriterAdmin.i18n.deactivation_error);
            $submitButton.prop("disabled", false).val(articleRewriterAdmin.i18n.deactivate);
          }
        },
        error: function () {
          alert(articleRewriterAdmin.i18n.deactivation_error);
          $submitButton.prop("disabled", false).val(articleRewriterAdmin.i18n.deactivate);
        },
      });
    });
  }

  // Initialize when the DOM is ready
  $(document).ready(initAdmin);
})(jQuery);
