<?php

/**
 * Meta box template
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="seo-meta-manager-wrapper">
    <style>
        .seo-field {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }

        .seo-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .seo-field input[type="text"],
        .seo-field textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .seo-field textarea {
            height: 80px;
            resize: vertical;
        }

        .character-count {
            font-size: 12px;
            margin-top: 8px;
            font-weight: normal;
        }

        .char-good {
            color: #46b450;
        }

        .char-warning {
            color: #ffb900;
        }

        .char-danger {
            color: #dc3232;
        }

        .seo-help {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }

        .og-image-section {
            margin-top: 10px;
        }

        .og-image-preview {
            max-width: 300px;
            height: auto;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .image-options {
            margin-top: 10px;
        }

        .image-options label {
            font-weight: normal;
            margin-right: 15px;
        }

        .image-options input[type="radio"] {
            margin-right: 5px;
        }

        .custom-image-field {
            margin-top: 10px;
            display: none;
        }

        .media-upload-btn {
            margin-left: 10px;
            vertical-align: top;
        }

        .sitemap-field {
            background: #fff3cd;
            border-color: #ffeaa7;
        }

        .sitemap-field input[type="checkbox"] {
            margin-right: 8px;
        }
    </style>

    <div class="seo-field">
        <label for="seo_meta_title">🔍 Meta Title (used for both SEO and social media)</label>
        <input type="text" id="seo_meta_title" name="seo_meta_title" value="<?php echo esc_attr($meta_title); ?>" maxlength="70">
        <div class="character-count" id="title-count">
            <span id="title-length"><?php echo esc_html(strlen($meta_title)); ?></span>/60 characters
            <div class="seo-help">
                Optimal: 50-60 characters for Google, up to 70 for social media
                <?php if ($post->post_type === 'product'): ?>
                    <br><strong>Product Tip:</strong> Include key product features or benefits
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="seo-field">
        <label for="seo_meta_description">📝 Meta Description (used for both SEO and social media)</label>
        <textarea id="seo_meta_description" name="seo_meta_description" maxlength="170"><?php echo esc_textarea($meta_description); ?></textarea>
        <div class="character-count" id="desc-count">
            <span id="desc-length"><?php echo esc_html(strlen($meta_description)); ?></span>/160 characters
            <div class="seo-help">
                Optimal: 150-160 characters for Google, up to 300 for social media
                <?php if ($post->post_type === 'product'): ?>
                    <br><strong>Product Tip:</strong> Mention key benefits, price range, or unique selling points
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="seo-field">
        <label>🖼️ Social Media Image (Open Graph)</label>
        <div class="seo-help">
            Recommended size: 1200x630px (1.91:1 ratio)
            <?php if ($post->post_type === 'product'): ?>
                <br><strong>Product Tip:</strong> Use high-quality product images or lifestyle shots
            <?php endif; ?>
        </div>

        <div class="image-options">
            <label>
                <input type="radio" name="og_image_option" value="featured" <?php echo (empty($og_image) ? 'checked' : ''); ?>>
                <?php if ($post->post_type === 'product'): ?>
                    Use Product Image
                    <?php if (!$featured_image): ?>
                        <span style="color: #dc3232;">(No product image set)</span>
                    <?php endif; ?>
                <?php else: ?>
                    Use Featured Image
                    <?php if (!$featured_image): ?>
                        <span style="color: #dc3232;">(No featured image set)</span>
                    <?php endif; ?>
                <?php endif; ?>
            </label>
            <label>
                <input type="radio" name="og_image_option" value="custom" <?php echo (!empty($og_image) ? 'checked' : ''); ?>>
                Use Custom Image
            </label>
        </div>

        <div class="custom-image-field" id="custom-image-field" <?php echo (!empty($og_image) ? 'style="display:block;"' : ''); ?>>
            <input type="text" id="seo_og_image" name="seo_og_image" value="<?php echo esc_attr($og_image); ?>" placeholder="Image URL">
            <button type="button" class="button media-upload-btn" id="upload-og-image">Choose Image</button>
        </div>

        <?php
        $display_image = !empty($og_image) ? $og_image : $featured_image;
        if ($display_image):
        ?>
            <img src="<?php echo esc_url($display_image); ?>" class="og-image-preview" alt="Social Media Image Preview" id="image-preview">
        <?php endif; ?>
    </div>

    <?php
    // Check if sitemap is enabled and this post type is included
    $sitemap_enabled = get_option('jolix_seo_enable_sitemap', 1);
    $included_post_types = get_option('jolix_seo_sitemap_post_types', array('post', 'page'));

    if ($sitemap_enabled && in_array($post->post_type, $included_post_types)):
        $exclude_from_sitemap = get_post_meta($post->ID, '_seo_exclude_from_sitemap', true);
    ?>
        <div class="seo-field sitemap-field">
            <label for="seo_exclude_from_sitemap">🗺️ XML Sitemap</label>
            <label style="font-weight: normal;">
                <input type="checkbox" id="seo_exclude_from_sitemap" name="seo_exclude_from_sitemap"
                    value="1" <?php checked($exclude_from_sitemap, 1); ?>>
                Exclude this <?php echo esc_html(strtolower(get_post_type_object($post->post_type)->labels->singular_name)); ?> from the XML sitemap
            </label>
            <div class="seo-help">
                By default, this content will be included in your sitemap at
                <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank"><?php echo esc_url(home_url('/sitemap.xml')); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Character counting function
            function updateCharCount(input, counter, optimal, max) {
                var length = input.val().length;
                var countElement = $('#' + counter + '-length');
                var countContainer = $('#' + counter + '-count');

                countElement.text(length);

                // Color coding based on length
                countContainer.removeClass('char-good char-warning char-danger');
                if (length <= optimal) {
                    countContainer.addClass('char-good');
                } else if (length <= max - 10) {
                    countContainer.addClass('char-warning');
                } else {
                    countContainer.addClass('char-danger');
                }
            }

            // Bind character counting
            $('#seo_meta_title').on('input', function() {
                updateCharCount($(this), 'title', 60, 70);
            });

            $('#seo_meta_description').on('input', function() {
                updateCharCount($(this), 'desc', 160, 170);
            });

            // Initialize counts
            updateCharCount($('#seo_meta_title'), 'title', 60, 70);
            updateCharCount($('#seo_meta_description'), 'desc', 160, 170);

            // Handle image option changes
            $('input[name="og_image_option"]').change(function() {
                if ($(this).val() === 'custom') {
                    $('#custom-image-field').show();
                } else {
                    $('#custom-image-field').hide();
                    $('#seo_og_image').val('');
                    // Show featured image preview if available
                    <?php if ($featured_image): ?>
                        $('#image-preview').attr('src', '<?php echo esc_url($featured_image); ?>').show();
                    <?php endif; ?>
                }
            });

            // Media uploader for OG image
            $('#upload-og-image').click(function(e) {
                e.preventDefault();

                var mediaUploader = wp.media({
                    title: 'Choose Social Media Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#seo_og_image').val(attachment.url);

                    // Update preview
                    var preview = $('#image-preview');
                    if (preview.length) {
                        preview.attr('src', attachment.url).show();
                    } else {
                        $('.og-image-section').append('<img src="' + attachment.url + '" class="og-image-preview" alt="Social Media Image Preview" id="image-preview">');
                    }
                });

                mediaUploader.open();
            });
        });
    </script>
</div>