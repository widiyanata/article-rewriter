/**
 * All of the JS for your Gutenberg block editor functionality should be
 * included in this file.
 *
 * @link       https://widigital.com
 * @since      1.0.0
 *
 * @package    Article_Rewriter
 * @subpackage Article_Rewriter/assets/js
 */

(function (wp) {
  "use strict";

  const { __ } = wp.i18n;
  const { registerPlugin } = wp.plugins;
  const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
  const { PanelBody, Button, SelectControl, Spinner, Notice, TextareaControl, TabPanel } =
    wp.components;
  const { useState, useEffect } = wp.element;
  const { useSelect, useDispatch } = wp.data;
  const { apiFetch } = wp;

  /**
   * Article Rewriter Sidebar Component
   */
  const ArticleRewriterSidebar = () => {
    // State
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [api, setApi] = useState("openai");
    const [style, setStyle] = useState("standard");
    const [originalContent, setOriginalContent] = useState("");
    const [rewrittenContent, setRewrittenContent] = useState("");
    const [history, setHistory] = useState([]);
    const [isLoadingHistory, setIsLoadingHistory] = useState(false);

    // Get post content and ID from the editor
    const { postContent, postId } = useSelect((select) => {
      const { getCurrentPostId, getEditedPostContent } = select("core/editor");
      return {
        postId: getCurrentPostId(),
        postContent: getEditedPostContent(),
      };
    }, []);

    // Get dispatch methods
    const { editPost } = useDispatch("core/editor");

    // Load rewrite history when sidebar opens
    useEffect(() => {
      loadHistory();
    }, []);

    /**
     * Load rewrite history for the current post
     */
    const loadHistory = () => {
      if (!postId) return;

      setIsLoadingHistory(true);

      apiFetch({
        path: "/article-rewriter/v1/history/" + postId,
        method: "GET",
      })
        .then((response) => {
          setHistory(response);
          setIsLoadingHistory(false);
        })
        .catch((error) => {
          console.error("Error loading history:", error);
          setIsLoadingHistory(false);
        });
    };

    /**
     * Handle rewrite button click
     */
    const handleRewrite = () => {
      setIsLoading(true);
      setError(null);
      setSuccess(null);
      setOriginalContent(postContent);

      apiFetch({
        path: "/article-rewriter/v1/rewrite",
        method: "POST",
        data: {
          post_id: postId,
          content: postContent,
          api: api,
          style: style,
        },
      })
        .then((response) => {
          setRewrittenContent(response.content);
          setSuccess(__("Article rewritten successfully!", "article-rewriter"));
          setIsLoading(false);
          loadHistory(); // Refresh history
        })
        .catch((error) => {
          setError(
            error.message ||
              __("An error occurred while rewriting the article.", "article-rewriter")
          );
          setIsLoading(false);
        });
    };

    /**
     * Apply rewritten content to the post
     */
    const applyRewrittenContent = () => {
      editPost({ content: rewrittenContent });
      setSuccess(__("Rewritten content applied to the post!", "article-rewriter"));
    };

    /**
     * Apply a historical version to the post
     */
    const applyHistoricalVersion = (content) => {
      editPost({ content: content });
      setSuccess(__("Historical version applied to the post!", "article-rewriter"));
    };

    /**
     * Render the history list
     */
    const renderHistory = () => {
      if (isLoadingHistory) {
        return (
          <div style={{ display: "flex", alignItems: "center" }}>
            <Spinner />
            <span>{__("Loading history...", "article-rewriter")}</span>
          </div>
        );
      }

      if (!history.length) {
        return (
          <p className="article-rewriter-no-history">
            {__("No rewrite history found for this post.", "article-rewriter")}
          </p>
        );
      }

      return (
        <div className="article-rewriter-history-list">
          {history.map((item, index) => (
            <div key={index} className="article-rewriter-history-item">
              <div className="article-rewriter-history-item-header">
                <span className="article-rewriter-history-item-api">{item.api}</span>
                <span className="article-rewriter-history-item-date">{item.date}</span>
              </div>
              <div className="article-rewriter-history-item-style">
                {__("Style:", "article-rewriter")} {item.style}
              </div>
              <Button
                isSecondary
                isSmall
                onClick={() => applyHistoricalVersion(item.content)}
                style={{ marginTop: "8px" }}
              >
                {__("Apply This Version", "article-rewriter")}
              </Button>
            </div>
          ))}
        </div>
      );
    };

    return (
      <>
        <PluginSidebarMoreMenuItem target="article-rewriter-sidebar" icon="edit">
          {__("Article Rewriter", "article-rewriter")}
        </PluginSidebarMoreMenuItem>
        <PluginSidebar
          name="article-rewriter-sidebar"
          title={__("Article Rewriter", "article-rewriter")}
          icon="edit"
        >
          <div className="article-rewriter-sidebar">
            {error && (
              <Notice status="error" isDismissible={false}>
                {error}
              </Notice>
            )}

            {success && (
              <Notice status="success" isDismissible={false}>
                {success}
              </Notice>
            )}

            <PanelBody title={__("Rewrite Options", "article-rewriter")} initialOpen={true}>
              <div className="article-rewriter-options">
                <SelectControl
                  label={__("API Provider", "article-rewriter")}
                  value={api}
                  options={[
                    { label: "OpenAI", value: "openai" },
                    { label: "DeepSeek", value: "deepseek" },
                    { label: "Anthropic", value: "anthropic" },
                    { label: "Google Gemini", value: "gemini" },
                  ]}
                  onChange={setApi}
                />

                <SelectControl
                  label={__("Rewriting Style", "article-rewriter")}
                  value={style}
                  options={[
                    { label: __("Standard", "article-rewriter"), value: "standard" },
                    { label: __("Formal", "article-rewriter"), value: "formal" },
                    { label: __("Casual", "article-rewriter"), value: "casual" },
                    { label: __("Creative", "article-rewriter"), value: "creative" },
                  ]}
                  onChange={setStyle}
                />
              </div>

              <Button
                isPrimary
                className="article-rewriter-button"
                onClick={handleRewrite}
                disabled={isLoading}
              >
                {isLoading ? (
                  <>
                    <Spinner />
                    {__("Rewriting...", "article-rewriter")}
                  </>
                ) : (
                  __("Rewrite Article", "article-rewriter")
                )}
              </Button>
            </PanelBody>

            {rewrittenContent && (
              <PanelBody title={__("Comparison", "article-rewriter")} initialOpen={true}>
                <div className="article-rewriter-comparison">
                  <TabPanel
                    className="article-rewriter-comparison-tabs"
                    activeClass="is-active"
                    tabs={[
                      {
                        name: "original",
                        title: __("Original", "article-rewriter"),
                        className: "article-rewriter-comparison-tab",
                      },
                      {
                        name: "rewritten",
                        title: __("Rewritten", "article-rewriter"),
                        className: "article-rewriter-comparison-tab",
                      },
                    ]}
                  >
                    {(tab) => (
                      <div className="article-rewriter-comparison-content">
                        <TextareaControl
                          value={tab.name === "original" ? originalContent : rewrittenContent}
                          readOnly
                        />
                      </div>
                    )}
                  </TabPanel>

                  <Button
                    isPrimary
                    className="article-rewriter-button"
                    onClick={applyRewrittenContent}
                  >
                    {__("Apply Rewritten Content", "article-rewriter")}
                  </Button>
                </div>
              </PanelBody>
            )}

            <PanelBody title={__("Rewrite History", "article-rewriter")} initialOpen={false}>
              <div className="article-rewriter-history">{renderHistory()}</div>
            </PanelBody>
          </div>
        </PluginSidebar>
      </>
    );
  };

  // Register the plugin
  registerPlugin("article-rewriter", {
    render: ArticleRewriterSidebar,
    icon: "edit",
  });
})(window.wp);
