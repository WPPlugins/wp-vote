(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(document).ready(function () {

        /************************************************
         * Utility Functions
         ************************************************/

        /**
         * Update or Add query string variable to provided URL
         */
        function updateQueryStringParameter(uri, key, value) {
            var re = new RegExp("([?|&])" + key + "=.*?(&|#|$)", "i");
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            } else {
                var hash = '';
                if (uri.indexOf('#') !== -1) {
                    hash = uri.replace(/.*#/, '#');
                    uri = uri.replace(/#.*/, '');
                }
                var separator = uri.indexOf('?') !== -1 ? "&" : "?";
                return uri + separator + key + "=" + value + hash;
            }
        }

        /**
         * Get a query string value from provided URL
         */
        function getQueryStringParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }

        /**
         * Display a notification message at the top of the edit screen
         */
        function add_edit_post_message(message, success) {

            var html = '',
                classes = (success) ? 'notice notice-success' : 'notice notice-error';

            html += '<div class="' + classes + ' is-dismissible">';
            html += '<p>' + message + '</p>';
            html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

            $('#wpbody-content>.wrap>h1').after(html);
        }


        /************************************************
         * CMB2 Field Customizations
         ************************************************/

        /**
         * Handlers for CMB2 Attached Fields 'Add All' / 'Remove All' buttons
         */
        if ($('.cmb-type-custom-attached-posts .add-remove-all')[0]) {
            $('.cmb-type-custom-attached-posts .add-all').click(function () {
                $('.attached-posts-wrap .retrieved .add-remove').each(function () {
                    $(this).click();
                });
                return false;
            });
            $('.cmb-type-custom-attached-posts .remove-all').click(function () {
                $('.attached-posts-wrap .attached .add-remove').each(function () {
                    $(this).click();
                });
                return false;
            });
        }

        /************************************************
         * Voter Behaviours
         ************************************************/

        // Wrap all behaviours in conditional testing for post-new.php or edit.php and post-type wp_vote_voter
        if ($('body').hasClass('post-type-wp-vote-voter') && ($('body').hasClass('post-php') || $('body').hasClass('post-new-php'))) {


            /**
             * Reload Voter edit on Voter Type change
             */
            $('#wp-vote-voter_voter_type').change(function () {
                var url = updateQueryStringParameter(window.location.href, 'voter_type', $(this).val());
                window.location.href = updateQueryStringParameter(url, 'voter_title', $('#title').val());
            });

            /**
             * Set Voter title after reload (if set originally)
             */
            var voter_title = getQueryStringParameterByName('voter_title', window.location.href);
            if (voter_title) {
                $('#title').val(voter_title);
            }

            /**
             * Disable Publish button if Voter title is left blank
             * @TODO IS THIS NECESSARY?
             */
            var $voter_title = $('#title');
            if ($voter_title[0]) {
                if (0 === $voter_title.val().length) {
                    $('#publish').prop('disabled', true);
                }
            }

            /**
             * Activate Publish button once the Voter title length is > 0
             * @TODO SEE ABOVE
             */
            $voter_title.on('input change', function () {
                var disabled = ( 0 === $voter_title.val().length );
                $('body.post-type-wp-vote-voter #publish').prop('disabled', disabled);
            });

            /**
             * Activate a Spinner when changing Voter types while waiting for page to refresh
             */
            $('#wp-vote-voter_voter_type').change(function () {
                $('#wp-vote-voter_voter_type_spinner').addClass('is-active');
                $(this).attr('disabled', true);
            });


        } // End Voter

        /************************************************
         * Ballot Behaviours
         ************************************************/

        // Wrap all behaviours in conditional testing for post-new.php or edit.php and post-type wp_vote_voter
        if ($('body').hasClass('post-type-wp-vote-ballot') && ($('body').hasClass('post-php') || $('body').hasClass('post-new-php'))) {

            /**
             * Cycle through Questions and hide the answer field, then look for a custom question and show that answer field
             */
            $('.question-type').each(function (index, type) {

                //questions_fields.possible.each( function (index, type) {
                //    $('.' + type + '_wrap').hide();
                //});

                $('.answers_wrap').hide();

                if ('custom' === $(type).val()) {
                    $(type).closest('.cmb-field-list').find('.answers_wrap').show();
                }
                //TODO loop questions_fields

            });

            /**
             * Check if the Question Type has been changed and show or hide the answer box accordingly
             */
            $('#wp-vote-ballot_questions_repeat').on('change', '.question-type', function () {
                // get the posable from script localisation in questions_fields
                if ('custom' === $(this).val()) {
                    $(this).closest('.cmb-field-list').find('.answers_wrap').show();
                } else {
                    $(this).closest('.cmb-field-list').find('.answers_wrap').hide();
                }
            });


            /**
             * Disable Publish button if Ballot title is left blank
             * @TODO THIS IS GOING TO CONFLICT WITH OUR CHECKBOX, MAYBE DISABLE CHECKBOX INSTEAD??
             */
            var $ballot_title = $('#title');
            if ($ballot_title[0]) {
                if (0 === $ballot_title.val().length) {
                    $('#wp-vote_open_ballot').prop('disabled', true);
                }
            }

            /**
             * Activate Publish button once the Ballot title length is > 0
             * @TODO SEE ABOVE
             */
            $ballot_title.on('input change', function () {
                if ($('#wp-vote_open_ballot_checkbox').checked) {
                    var disabled = ( 0 === $ballot_title.val().length );
                    $('#wp-vote_open_ballot').prop('disabled', disabled);
                }
            });

            /**
             * Set proxy checkbox behaviours
             * @TODO: Check that the title is set, combine functions?
             */
            $('#wp-vote_open_ballot').prop('disabled', true);
            $('#wp-vote_open_ballot_checkbox').change(function () {

                if (this.checked) {
                    $('#wp-vote_open_ballot').prop('disabled', false);
                } else {
                    $('#wp-vote_open_ballot').prop('disabled', true);
                }
            });


            $('.wp-vote-open-ballot').hover( function () {
                if ( ! $('#wp-vote_open_ballot_checkbox').prop( "checked" ) ) {
                    $('.wp-vote-open-ballot-confirmation label').css( 'color','red' );
                 }
            }, function () {
                $('.wp-vote-open-ballot-confirmation label').css( 'color','black' );
            })


            /**
             * Disable ballot submissions with Enter key
             */
            $('body.post-type-wp-vote-ballot form input, body.post-type-wp-vote-ballot form select').on('keyup keypress', function (e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            $('form#post').on('submit', function (e) {
                $voter_title = $('form#post #title');
                if (0 === $voter_title.val().length) {
                    add_edit_post_message('Title is required', false)
                    e.preventDefault();
                    $voter_title.focus();
                    return false;
                }

            });


            /**
             * TODO ASK PAUL WHATS GOING OIN HERE AND WHAT NEEDS TO BE KEPT AND WHAT CAN BE REMOVED
             */
            if ($('body.post-type-wp-vote-ballot #save-action #save-post')) {

                var new_button = $('body.post-type-wp-vote-ballot #save-action #save-post')
                    .clone().prop('id', 'save-post-etc').addClass('save-post-etc button-primary').val('Save Ballot');
                $('body.post-type-wp-vote-ballot #save-action').prepend(new_button);
                $('body.post-type-wp-vote-ballot #save-action #save-post').hide().addClass('hide-if-js');

                $('body.post-type-wp-vote-ballot #wp-vote_open_ballot').on('click', function (e) {
                    $(this).append("<input type='hidden' name='publish' value='Publish'>");
                });


                ///**
                // * do the save by clicking the real button to stop alert
                // */
                $('body.post-type-wp-vote-ballot form .save-post-etc').click(function (e) {
                    $('#save-action #save-post').click();
                    e.preventDefault();
                });

            }


            /**
             * AJAX Handler - Email All Users
             */
            $('#wp-vote-send-ballot-to-all-voters').click(function (e) {

                $(this).closest('tr').find('.spinner').addClass('is-active');

                var data = {
                    'action': 'email_ballot_to_all_voters',
                    'ballot_id': $(this).data('ballot-id')
                };

                $.post(ajaxurl, data, function (response) {
                    console.debug(response);
                    var res = wpAjax.parseAjaxResponse(response, 'result');
                    $.each(res.responses, function () {

                        if ("0" !== this.id) {
                            add_edit_post_message(this.data, true);
                        } else {
                            $.each(this.errors, function () {
                                console.debug(this);
                                add_edit_post_message(this.message, false)
                            });

                        }
                        $('.spinner').removeClass('is-active');
                    });
                });

                return false;

            });

            /**
             * AJAX Handler - Email Individual Voter
             */
            $('.wp-vote-send-ballot-to-individual').click(function (e) {
                $(this).closest('tr').find('.spinner').addClass('is-active');
                var data = {
                    'action': 'email_ballot_to_individual',
                    'voter_id': $(this).data('voter-id')
                };

                $.post(ajaxurl, data, function (response) {

                    console.debug(response);
                    var res = wpAjax.parseAjaxResponse(response, 'result');
                    $.each(res.responses, function () {

                        if ("0" !== this.id) {
                            add_edit_post_message(this.data, true);
                        } else {
                            $.each(this.errors, function () {
                                console.debug(this);
                                add_edit_post_message(this.message, false)
                            });
                        }
                        $('.spinner').removeClass('is-active');
                    });
                });

                return false;

            });


            /**
             * AJAX Handler - Show Individual Votes Popup
             */
            $('.wp-vote-show-individual-votes').click(function () {
                var data = {
                    'action': 'show-individual-votes',
                    'voter_id': $(this).data('voter-id'),
                    'ballot_id': $(this).data('ballot-id')
                };
                // show a spinner or something via css
                var dialog = $('<div style="display:none" class="wp-vote-dialog spinner is-active"></div>').appendTo('body');
                // open the dialog
                dialog.dialog({
                    // add a close listener to prevent adding multiple divs to the document
                    close: function (event, ui) {
                        // remove div with all data and events
                        dialog.remove();
                    },
                    modal: true,
                    width: "80%",
                    maxWidth: "768px",
                    height: 600
                });
                // load remote content
                dialog.load(
                    ajaxurl,
                    data, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
                    function (responseText, textStatus, XMLHttpRequest) {
                        dialog.removeClass('spinner is-active');
                    }
                );
                //prevent the browser to follow the link
                return false;
            });


            /**
             * AJAX Handler - Export Results to CSV
             */
            $('#wp-vote_export_results').click(function (e) {

                var data = {
                    'action': 'export_results_to_csv'
                };

                $.post(ajaxurl, data, function (response) {
                    console.debug(response);
                    var res = wpAjax.parseAjaxResponse(response, 'result');
                    $.each(res.responses, function () {

                        if ("0" !== this.id) {
                            add_edit_post_message('<a href="' + this.data + '" target="_blank">' + this.data + '</a>', true);
                            window.location = this.data;
                        } else {
                            $.each(this.errors, function () {
                                console.debug(this);
                                add_edit_post_message(this.message, false)
                            });

                        }
                    });
                });

                return false;

            });

        } // END Ballot Behaviours

        /************************************************
         * Voter Import/Export Behaviours
         ************************************************/

        if ($('body').hasClass('wp-vote_page_import')) {

            /**
             * AJAX Handler - Export Results to CSV
             */
            $('#wp-vote_export_voters').click(function (e) {

                var data = {
                    'action': 'export_voters_to_csv'
                };

                $.post(ajaxurl, data, function (response) {
                    console.debug(response);
                    var res = wpAjax.parseAjaxResponse(response, 'result');
                    $.each(res.responses, function () {

                        if ("0" !== this.id) {
                            add_edit_post_message('<a href="' + this.data + '" target="_blank">' + this.data + '</a>', true);
                            window.location = this.data;
                        } else {
                            $.each(this.errors, function () {
                                console.debug(this);
                                add_edit_post_message(this.message, false)
                            });

                        }
                    });
                });

                return false;

            });
        } // END Voter Import/Export Behaviours

    });
})(jQuery);
