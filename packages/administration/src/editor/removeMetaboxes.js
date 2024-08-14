const taxonomies = [
    'brand',
    'editorial_type',
    'industry',
    'podcast-show',
    'region',
    'topic',
    'sc_award',
    'application',
    'ppworks_segment_type',
    'ppworks_show',
    'ppworks_tag',
    'community_region',
    'years'
]

/**
 * Here is an (incomplete) list of panel IDs:
 *
 * taxonomy-panel-category - Category panel.
 * taxonomy-panel-CUSTOM-TAXONOMY-NAME - Custom taxonomy panel. If your taxonomy is topic, then taxonomy-panel-topic works.
 * taxonomy-panel-post_tag - Tags panel
 * featured-image - Featured image panel.
 * post-link - Permalink panel.
 * page-attributes - Page attributes panel.
 * post-excerpt - Post excerpt panel.
 * discussion-panel - Discussions panel.
 *
 * For acf, run jQuery('.postbox') in console and
 * var boxId = 'acf-group_HERE-IS-AN-ID';
 * wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'meta-box-' + boxId );
 */
taxonomies.map(taxonomy => wp.data.dispatch('core/edit-post').removeEditorPanel('taxonomy-panel-' + taxonomy) && console.log('Metabox was removed for taxonomy - ' + taxonomy));
