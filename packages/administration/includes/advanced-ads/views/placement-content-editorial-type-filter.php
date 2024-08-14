<?php
/**
 * Render content tag-filter option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var array $_placement information of the current placement.
 * @var array $option_editorial_types tag option.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

?>

<?php
$editorial_types = get_terms(array('taxonomy' => ['editorial_type'], 'fields' => 'id=>name')); ?>
<select multiple="multiple" class="advads-placements-content-editorial-type" name="advads[placements][<?php
echo esc_attr($_placement_slug); ?>][options][editorial-types][]" style="height:200px;width:50%;">
    <?php
    foreach ($editorial_types as $_editorial_type_key => $_editorial_type) : ?>
        <option value="<?php
        echo esc_attr($_editorial_type_key); ?>"
            <?php
            echo $option_editorial_types ? selected(in_array($_editorial_type_key, $option_editorial_types)) : '';
            ?>
        ><?php
            echo esc_html($_editorial_type); ?></option>
        <?php
    endforeach; ?>
</select>
