/**
 * CubeWP Keyword Suggestions
 * Handles AJAX keyword suggestions based on taxonomy settings
 * Loader-in-field version
 * 
 * REVISED: Only trigger AJAX when text/keyword changes (keyup/input), otherwise re-use previously loaded suggestions.
 */
(function ($) {
    'use strict';

    var KeywordSuggestions = {
        init: function () {
            this.bindEvents();
        },

        // Store previous values per input field
        _inputCache: {},

        bindEvents: function () {
            var self = this;

            // Prevent double loader/suggestion invocation for default suggestions
            var focusDefaultSuggestionsRunning = false;
 
        
            $(document).on('click', '.cwp-search-form input[name="select"], .cubewp-filter-builder-input[name="select"]', function () {
                var $input = $(this);
                var keyword = $input.val().trim();
                var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container').find('.cubewp-keyword-suggestions-container');
                var inputId = $input[0] && $input[0].id ? $input[0].id : $input.data('cache-key') || '';

                // If there are suggestions already shown (from cache), just show them, do NOT re-AJAX
                if ($container.length && $container.html() && !$container.is(':visible')) {
                    $container.show();
                    return;
                }

                // If container is empty and keyword is empty, load via AJAX (unless already running)
                if (keyword.length === 0 && !focusDefaultSuggestionsRunning) {
                    focusDefaultSuggestionsRunning = true;
                    var postType = self.getPostTypeFromForm($input);
                    if (postType) {
                        var taxonomies = self.getTaxonomies($input, postType);
                        if (taxonomies && taxonomies.length > 0) {
                            // Save the fact we fetched for blank
                            self.fetchDefaultSuggestions($input, postType, taxonomies, function () {
                                focusDefaultSuggestionsRunning = false;
                            });
                        } else {
                            focusDefaultSuggestionsRunning = false;
                        }
                    } else {
                        focusDefaultSuggestionsRunning = false;
                    }
                }
            });

            $(document).on('click', '.cwp-search-form input[name="select"], .cubewp-filter-builder-input[name="select"]', function () {
                var $input = $(this);
                var keyword = $input.val().trim();
                var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container').find('.cubewp-keyword-suggestions-container');

                var inputId = $input[0] && $input[0].id ? $input[0].id : $input.data('cache-key') || '';
                if (!inputId) {
                    inputId = Math.random().toString(36).substr(2, 9); // unique key if none exists
                    $input.data('cache-key', inputId);
                }

                // If suggestions container already has content (cached), and is hidden, just re-show it (no AJAX!)
                if ($container.length && $container.html() && $container.html().trim() !== '' && $container.is(':hidden')) {
                    $container.show();
                    return;
                }

                // Otherwise only run AJAX if input is empty and not running AND nothing cached yet
                if (keyword.length === 0 && !focusDefaultSuggestionsRunning) {
                    focusDefaultSuggestionsRunning = true;
                    var postType = self.getPostTypeFromForm($input);
                    if (postType) {
                        var taxonomies = self.getTaxonomies($input, postType);
                        if (taxonomies && taxonomies.length > 0) {
                            self.fetchDefaultSuggestions($input, postType, taxonomies, function () {
                                focusDefaultSuggestionsRunning = false;
                            });
                        } else {
                            focusDefaultSuggestionsRunning = false;
                        }
                    } else {
                        focusDefaultSuggestionsRunning = false;
                    }
                } else if (keyword.length > 0) {
                    // If user has typed, trigger input event for up-to-date suggestions.
                    $input.trigger('input');
                }
            });

            // Only trigger AJAX when keyword changes from last input value (debounced)
            $(document).on('input keyup', '.cwp-search-form input[name="select"], .cubewp-filter-builder-input[name="select"]', function () {
                var $input = $(this);
                var keyword = $input.val().trim();

                var inputId = $input[0] && $input[0].id ? $input[0].id : $input.data('cache-key') || '';
                if (!inputId) {
                    inputId = Math.random().toString(36).substr(2, 9);
                    $input.data('cache-key', inputId);
                }

                var cache = self._inputCache[inputId] || {};
                var $form = $input.closest('form');
                $form.find('input[type="hidden"][data-taxonomy]').val('');

                var $hiddenS = $form.find('input[name="s"][type="hidden"]');
                if ($hiddenS.length) {
                    $hiddenS.val(keyword);
                }

                // Clear any selected taxonomy terms when user starts typing
                $form.find('input[name^="tax_"][type="hidden"]').remove();
                var $postIdField = $form.find('input[name="post_id"][type="hidden"]');
                if ($postIdField.length) {
                    $postIdField.remove();
                }

                // Get post type from form
                var postType = self.getPostTypeFromForm($input);
                if (!postType) {
                    return;
                }

                // Check if suggestions are enabled for this input
                if (!$input.data('enable-suggestions')) {
                    return;
                }

                // Get taxonomies from settings
                var taxonomies = self.getTaxonomiesFromSettings(postType);
                if (!taxonomies || taxonomies.length === 0) {
                    return;
                }

                // Prevent repeat ajax call for same keyword (unless input changed)
                clearTimeout($input.data('suggestions-timeout'));
                var lastKeyword = cache.lastKeyword || '';

                var timeout = setTimeout(function () {
                    if (keyword.length >= 1) {
                        if (lastKeyword !== keyword) {
                            self.fetchSuggestions($input, keyword, postType, taxonomies, function (suggestions, html, $container) {
                                self._inputCache[inputId] = {
                                    lastKeyword: keyword,
                                    suggestions: suggestions || [],
                                    html: html || '',
                                    containerHtml: html || ''
                                };
                            });
                        } else {
                            // If already cached, just show last suggestions
                            var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container').find('.cubewp-keyword-suggestions-container');
                            if (self._inputCache[inputId] && self._inputCache[inputId].containerHtml) {
                                $container.html(self._inputCache[inputId].containerHtml).show();
                            }
                        }
                    } else if (keyword.length === 0) {
                        // Only re-AJAX if last keyword wasn't empty (so typing back to empty reverts to default)
                        if (lastKeyword !== '') {
                            self.fetchDefaultSuggestions($input, postType, taxonomies, function (suggestions, html, $container) {
                                self._inputCache[inputId] = {
                                    lastKeyword: '',
                                    suggestions: suggestions || [],
                                    html: html || '',
                                    containerHtml: html || ''
                                };
                            });
                        } else {
                            // Just show cached default suggestions if exist
                            var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container').find('.cubewp-keyword-suggestions-container');
                            if (self._inputCache[inputId] && self._inputCache[inputId].containerHtml) {
                                $container.html(self._inputCache[inputId].containerHtml).show();
                            }
                        }
                    } else {
                        self.hideSuggestions($input);
                    }
                }, 500);
                $input.data('suggestions-timeout', timeout);
            });

            // Handle click on suggestion
            $(document).on('click', '.cubewp-keyword-suggestion-item', function (e) {

                var $item = $(this);
                var type = $item.data('type');
                var $input = $item.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                    .find('input[name="select"]');
                var $form = $input.closest('form');
                $form.find('input[type="hidden"][data-taxonomy]').val('');
                $form.find('input[name="s"]').val('');
                if (type === 'post') {
                    // For posts, navigate to detail page
                    var postUrl = $item.data('post-url');
                    if (postUrl) {
                        window.location.href = postUrl;
                    }
                } else {
                    // For terms, add to search form and clear text search
                    e.preventDefault();

                    var termId = $item.data('term-id');
                    var termName = $item.data('term-name');
                    var termSlug = $item.data('term-slug');
                    var taxonomy = $item.data('taxonomy');

                    // Set the visible input value to the selected term name
                    $input.val(termName);

                    // Clear the hidden "s" field (no text search when term is selected)
                    var $hiddenS = $form.find('input[name="s"][type="hidden"]');
                    if ($hiddenS.length) {
                        $hiddenS.val('');
                    }

                    // Clear any existing taxonomy fields
                    $form.find('input[name^="tax_"][type="hidden"]').remove();

                    // Add the selected term to taxonomy field
                    self.addHiddenInput($input, {
                        id: termId,
                        name: termName,
                        slug: termSlug,
                        taxonomy: taxonomy
                    });

                    // Hide suggestions after selection
                    self.hideSuggestions($input);
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.cubewp-keyword-suggestions-container, input[name="select"]').length) {
                    $('.cubewp-keyword-suggestions-container').hide();
                }
            });

        },

        getPostTypeFromForm: function ($input) {
            // Try to get from data attribute
            var postType = $input.data('post-type');
            if (postType) {
                return postType;
            }

            // Try to get from hidden input in form
            var $form = $input.closest('form');
            var $postTypeInput = $form.find('input[name="post_type"]');
            if ($postTypeInput.length) {
                return $postTypeInput.val();
            }

            // Try to get from URL parameter
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('post_type')) {
                return urlParams.get('post_type');
            }

            return null;
        },

        getTaxonomies: function ($input, postType) {
            // Try to get from data attribute
            var taxonomies = $input.data('taxonomies');
            if (taxonomies) {
                if (typeof taxonomies === 'string') {
                    try {
                        taxonomies = JSON.parse(taxonomies);
                    } catch (e) {
                        return [];
                    }
                }
                return Array.isArray(taxonomies) ? taxonomies : [taxonomies];
            }
 
            return this.getTaxonomiesFromSettings(postType);
        },

        getTaxonomiesFromSettings: function (postType) { 
            if (typeof cubewpKeywordSuggestions !== 'undefined' &&
                cubewpKeywordSuggestions.settings &&
                cubewpKeywordSuggestions.settings[postType]) {
                return cubewpKeywordSuggestions.settings[postType];
            }
            return null;
        },

        checkSuggestionsEnabled: function (postType) { 
            return true;
        },

        showInputLoader: function ($input) {
            this.removeInputLoader($input); // Remove any existing loaders
            var loader = $('<span class="keyword-suggest-input-loader" style="position: absolute;right: 10px;transform: translate(-2px, 14px);"><span class="loading-spinner" style="width:16px;height:16px;display:inline-block;vertical-align:middle;"></span></span>');
            $input.after(loader);
        },

        removeInputLoader: function ($input) {
            $input.siblings('.keyword-suggest-input-loader').remove();
        },

        // Accepts doneCallback(suggestions, renderedHtml, $container)
        fetchSuggestions: function ($input, keyword, postType, taxonomies, doneCallback) {
            var self = this;
            var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                .find('.cubewp-keyword-suggestions-container');

            if (!$container.length) {
                $container = $('<div class="cubewp-keyword-suggestions-container"></div>');
                $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                    .append($container);
            }

            this.showInputLoader($input);
            $container.hide();

            $.ajax({
                url: cubewpKeywordSuggestions.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_keyword_suggestions',
                    nonce: cubewpKeywordSuggestions.nonce,
                    keyword: keyword,
                    post_type: postType,
                    taxonomies: taxonomies
                },
                success: function (response) {
                    self.removeInputLoader($input);
                    var htmlStr;
                    if (response.success && response.data.suggestions) {
                        htmlStr = self.renderSuggestionsHtml($input, response.data.suggestions, $container);
                        $container.html(htmlStr).show();
                    } else {
                        htmlStr = self.renderSuggestionsHtml($input, [], $container);
                        $container.html(htmlStr).show();
                    }
                    if (typeof doneCallback === "function") doneCallback(response.data && response.data.suggestions, htmlStr, $container);
                },
                error: function () {
                    self.removeInputLoader($input);
                    $container.hide();
                    if (typeof doneCallback === "function") doneCallback();
                }
            });
        },

        // Accepts optional callback(suggestions, renderedHtml, $container)
        fetchDefaultSuggestions: function ($input, postType, taxonomies, doneCallback) {
            var self = this;
            var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                .find('.cubewp-keyword-suggestions-container');

            if (!$container.length) {
                $container = $('<div class="cubewp-keyword-suggestions-container"></div>');
                $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                    .append($container);
            }

            this.showInputLoader($input);
            $container.hide();

            $.ajax({
                url: cubewpKeywordSuggestions.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_default_keyword_suggestions',
                    nonce: cubewpKeywordSuggestions.nonce,
                    post_type: postType,
                    taxonomies: taxonomies
                },
                success: function (response) {
                    self.removeInputLoader($input);
                    var htmlStr;
                    if (response.success && response.data.suggestions) {
                        htmlStr = self.renderSuggestionsHtml($input, response.data.suggestions, $container);
                        $container.html(htmlStr).show();
                    } else {
                        htmlStr = self.renderSuggestionsHtml($input, [], $container);
                        $container.html(htmlStr).show();
                    }
                    if (typeof doneCallback === "function") doneCallback(response.data && response.data.suggestions, htmlStr, $container);
                },
                error: function () {
                    self.removeInputLoader($input);
                    $container.hide();
                    if (typeof doneCallback === "function") doneCallback();
                }
            });
        },

        displaySuggestions: function ($input, suggestions, $container) {
            // Deprecated in favor of renderSuggestionsHtml to allow for caching output as string
            var htmlStr = this.renderSuggestionsHtml($input, suggestions, $container);
            $container.html(htmlStr).show();
            // Don't cache here, cache is set in AJAX callback
        },

        // Generates suggestions html
        renderSuggestionsHtml: function ($input, suggestions, $container) {
            // If already have loader, remove it
            this.removeInputLoader($input);

            var html = '<ul class="cubewp-keyword-suggestions-list">';
            if (!suggestions || suggestions.length === 0) {
                html += '<li class="cubewp-keyword-suggestion-item no-results-found" style="cursor:default;color:#aaa;text-align:center;pointer-events:none;">No results found</li>';
                html += '</ul>';
                return html;
            }
            suggestions.forEach(function (suggestion) {
                var typeClass = suggestion.type === 'post' ? 'suggestion-post' : 'suggestion-term';
                var taxonomyInfo = suggestion.taxonomy_label;
                var extraAttrs = '';

                if (suggestion.type === 'post' && suggestion.url) {
                    extraAttrs = ' data-post-url="' + suggestion.url + '"';
                    extraAttrs += ' data-thumbnail="' + suggestion.thumbnail + '"';
                }

                html += '<li class="cubewp-keyword-suggestion-item ' + typeClass + '" ' +
                    'data-term-id="' + suggestion.id + '" ' +
                    'data-term-name="' + suggestion.name + '" ' +
                    'data-term-slug="' + suggestion.slug + '" ' +
                    'data-taxonomy="' + suggestion.taxonomy + '" ' +
                    'data-type="' + suggestion.type + '"' +
                    extraAttrs + '>';

                if (suggestion.type === 'post' && suggestion.thumbnail) {
                    html += '<span class="suggestion-thumbnail"><img src="' + suggestion.thumbnail + '" alt="' + suggestion.name + '" /></span>';
                }

                html += '<span class="suggestion-name">' + suggestion.name + '</span>' +
                    '<span class="suggestion-type">' + taxonomyInfo + '</span>' +
                    '</li>';
            });
            html += '</ul>';
            return html;
        },

        hideSuggestions: function ($input) {
            var $container = $input.closest('.cwp-field-container, .cubewp-filter-builder-field-container')
                .find('.cubewp-keyword-suggestions-container');
            $container.hide();
            this.removeInputLoader($input);
        },

        addHiddenInput: function ($input, term) {
            var $form = $input.closest('form');
            var taxonomy = term.taxonomy;
            // Check if there's already an input for this taxonomy
            var $existingInput = $form.find('input[name="' + taxonomy + '"][type="hidden"]');
            if ($existingInput.length) {
                var currentValue = $existingInput.val();
                var termIds = currentValue ? currentValue.split(',').map(function (id) { return id.trim(); }) : [];
                if (termIds.indexOf(term.id.toString()) === -1) {
                    termIds.push(term.id.toString());
                    $existingInput.val(termIds.join(','));
                }
            } else {
                var $hiddenInput = $('<input type="hidden" name="_ST_' + taxonomy + '" value="' + term.id +
                    '" data-taxonomy="' + escapeHtml(taxonomy) + '">');
                $form.append($hiddenInput);
            }
        }
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Initialize on document ready
    $(document).ready(function () {
        KeywordSuggestions.init();
    });

})(jQuery);
