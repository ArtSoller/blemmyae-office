const MetaboxNameToId = {
    Landing__collectionWidget: 'acf-group_60c1eeb84e108'
}

document.addEventListener('DOMContentLoaded', () => {
    Object.values(MetaboxNameToId).forEach(metaboxId => {
        const metaboxElement = document.getElementById(metaboxId);
        if (!metaboxElement) {
            return;
        }
        metaboxElement.classList.add('closed');
    });
});
