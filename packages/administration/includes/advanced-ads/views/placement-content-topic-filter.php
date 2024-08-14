<?php
/**
 * Render content tag-filter option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var array $_placement information of the current placement.
 * @var array $option_topics tag option.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

?>

<?php
$topics = get_terms(array('taxonomy' => ['topic'], 'fields' => 'id=>name')); ?>
<select multiple="multiple" class="advads-placements-content-topics" name="advads[placements][<?php
echo esc_attr($_placement_slug); ?>][options][topics][]" style="height:200px;width:50%;">
    <?php
    foreach ($topics as $_topic_key => $_topic) : ?>
        <option value="<?php
        echo esc_attr($_topic_key); ?>"
            <?php
            echo $option_topics ? selected(in_array($_topic_key, $option_topics)) : '';
            ?>
        ><?php
            echo esc_html($_topic); ?></option>
        <?php
    endforeach; ?>
</select>
