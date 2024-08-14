const $ = jQuery.noConflict();

$(function () {
    wp.domReady(() => {
        /**
         * A listener of post title field
         */
        wp.data.subscribe(() => {
            const gutenbergTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
            const titleBlock = document.getElementsByClassName('wp-block-post-title')[0];
            const counterTitleBlock = document.getElementById('post-title-counter');
            /**
             * Create a block with counter value and recommended number of characters.
             * Need to do it here because post title block is constructed by js and there's
             * no way to filter it and render on backend like we can do with acf fields
             * @todo: redo in a more readable manner
             */
            if (titleBlock && !counterTitleBlock) {
                const recommendedMinValue = 40;
                const recommendedMaxValue = 70;
                const counterOuterWrapper = document.createElement('div');
                const counterWrapper = document.createElement('span');
                counterWrapper.className = 'char-count';
                counterOuterWrapper.appendChild(counterWrapper);
                const recommendedMax = document.createElement('span');
                recommendedMax.textContent = recommendedMaxValue;
                recommendedMax.dataset.recommendedMax = recommendedMaxValue;
                recommendedMax.className = 'recommended-max'
                const recommendedMin = document.createElement('span');
                recommendedMin.textContent = recommendedMinValue;
                recommendedMin.dataset.recommendedMin = recommendedMinValue;
                recommendedMin.className = 'recommended-min';
                const actualCounter = document.createElement('span');
                actualCounter.textContent = 'Number of characters';
                actualCounter.className = 'count';
                counterWrapper.appendChild(actualCounter);
                counterWrapper.append('Recommended number of characters: ');
                counterWrapper.appendChild(recommendedMin);
                counterWrapper.append("-");
                counterWrapper.appendChild(recommendedMax);
                counterOuterWrapper.id = 'post-title-counter';
                counterOuterWrapper.className = 'post-title-counter';
                counterOuterWrapper.style.maxWidth = '840px';
                counterOuterWrapper.style.marginLeft = 'auto';
                counterOuterWrapper.style.marginRight = 'auto';
                titleBlock.insertAdjacentElement('afterend', counterOuterWrapper);
            }
            if (counterTitleBlock) {
                fieldValueChangeCounterHandler($('.post-title-counter'), gutenbergTitle.length);
            }
        });
    });
});

/**
 * A reusable handler for counting characters in elements that have the following structure:
 * <wrapperElement>
 *     <span className='char-count'>
 *         <span className='count'>n</span>
 *         <span className='recommended-min' data-recommended-min='l'>l</span>
 *         <span className='recommended-max' data-recommended-max='k'>k</span>
 *     </span>
 * </wrapperElement>
 *
 * @param wrapperElement
 * @param length
 */
const fieldValueChangeCounterHandler = (wrapperElement, length) => {
    const recommendedMax = wrapperElement?.find('.recommended-max')[0].dataset.recommendedMax ?? 0;
    const recommendedMin = wrapperElement?.find('.recommended-min')[0].dataset.recommendedMin ?? 0;
    wrapperElement?.find('.count').text('Number of characters: ' + length + '. ');

    const charactersCountWrapperBlock = wrapperElement?.find('.char-count')?.[0] ?? null;
    if (!charactersCountWrapperBlock) {
        return;
    }

    if (length >= recommendedMin && length <= recommendedMax) {
        charactersCountWrapperBlock.style.color = 'green';
        return;
    }

    charactersCountWrapperBlock.style.color = 'red';
}

$(function () {
    /**
     * Sets an event listener of updates of acf text field
     */
    acf.fields.text_counter = acf.field.extend({
        type: 'text',
        events: {
            'input input': 'change_count',
        },
        change_count: (e) => fieldValueChangeCounterHandler(e.$el.closest('.acf-input'), e.$el.val().length)
    });

    /**
     * Sets an event listener of updates of acf textarea field
     */
    acf.fields.textarea_counter = acf.field.extend({
        type: 'textarea',
        events: {
            'input textarea': 'change_count',
        },
        change_count: (e) => fieldValueChangeCounterHandler(e.$el.closest('.acf-input'), e.$el.val().length)
    });
});
