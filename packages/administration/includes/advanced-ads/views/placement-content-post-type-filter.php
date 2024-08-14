<?php
/**
 * Render content tag-filter option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var array $_placement information of the current placement.
 * @var array $option_post_types tag option.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

?>

<?php
$post_types = array_merge(
    array_combine(
        array_column(get_option('cptui_post_types', []), 'name'),
        array_column(get_option('cptui_post_types', []), 'label')
    ),
    ['guest-author' => 'Guest Author', 'advanced_ads' => 'Advanced Ads',]
); ?>
<select multiple="multiple" class="advads-placements-content-post-type" name="advads[placements][<?php
echo esc_attr($_placement_slug); ?>][options][post-types][]" style="height:200px;width:50%;">
    <?php
    foreach ($post_types as $_post_type_key => $_post_type) : ?>
        <option value="<?php
        echo esc_attr($_post_type_key); ?>"
            <?php
            echo $option_post_types ? selected(in_array($_post_type_key, $option_post_types)) : '';
            ?>
        ><?php
            echo esc_html($_post_type); ?></option>
        <?php
    endforeach; ?>
</select>
