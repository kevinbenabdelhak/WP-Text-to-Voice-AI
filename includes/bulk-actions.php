<?php

if (!defined('ABSPATH')) {
    exit; 
}

// Ajouter un bouton personnalisé pour les actions de publication
add_action('admin_footer-edit.php', 'wp_text_to_voice_ai_bulk_admin_footer_js');
function wp_text_to_voice_ai_bulk_admin_footer_js() {
   ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            if ($('select[name="action"] option[value="transcribe_to_audio"]').length === 0) {
                $('select[name="action"], select[name="action2"]').append('<option value="transcribe_to_audio">Générer une voix en MP3</option>');
            }
            if ($('select[name="action"] option[value="delete_transcriptions_audio"]').length === 0) {
                $('select[name="action"], select[name="action2"]').append('<option value="delete_transcriptions_audio">Supprimer les MP3</option>');
            }

            $(document).on('click', '#doaction, #doaction2', function(e) {
                var action = $('select[name="action"]').val() !== '-1' ? $('select[name="action"]').val() : $('select[name="action2"]').val();
                if (action !== 'transcribe_to_audio' && action !== 'delete_transcriptions_audio') return;
                e.preventDefault();
                var postIDs = [];
                $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                    postIDs.push($(this).val());
                });
                if (postIDs.length === 0) {
                    alert('Aucun post sélectionné');
                    return;
                }

                $('#bulk-action-loader').remove();
                $('#doaction, #doaction2').after("<div id='bulk-action-loader'><span class='spinner is-active' style='margin-left: 10px;'></span> <span id='conversion-progress'>0 / " + postIDs.length + " terminés</span></div>");
                var completedCount = 0;
                var failedCount = 0;

                function processNext(index) {
                    if (index >= postIDs.length) {
                        $('#bulk-action-loader').remove();
                        var message = completedCount + " post(s) traités avec succès.";

                        if (failedCount > 0) {
                            message += " " + failedCount + " échec(s).";
                        }

                        $("<div class='notice notice-success is-dismissible'><p>" + message + "</p></div>").insertAfter(".wp-header-end");
                        location.reload();
                        return;

                    }



                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: action === 'transcribe_to_audio' ? 'wp_text_to_voice_ai_generate_audio_bulk' : 'wp_text_to_voice_ai_delete_audio_bulk',
                            post_id: postIDs[index],
                            security: '<?php echo wp_create_nonce('wp_text_to_voice_ai_nonce'); ?>'
                        },

                        success: function(response) {
                            if (response.success) {
                                completedCount++;
                            } else {
                                failedCount++;
                                console.error("Erreur pour le post ID " + postIDs[index] + ": " + response.data);
                            }

                            $('#conversion-progress').text(completedCount + " / " + postIDs.length + " terminés");
                            processNext(index + 1);
                        },

                        error: function() {
                            failedCount++;
                            console.error("Erreur pour le post ID " + postIDs[index]);
                            $('#conversion-progress').text(completedCount + " / " + postIDs.length + " terminés");
                            processNext(index + 1);

                        }

                    });

                }
                processNext(0);

            });
        });
    </script>
    <?php
}