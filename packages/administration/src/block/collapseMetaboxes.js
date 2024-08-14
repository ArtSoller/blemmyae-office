const MetaboxNameToId = {
    Editorial__briefAdvanced: 'acf-group_60d5afc5cabba',
    Editorial__productTestAdvanced: 'acf-group_60d615ef289e7'
}

document.addEventListener('DOMContentLoaded', () => {
    Object.values(MetaboxNameToId).forEach(metaboxId => document.getElementById(metaboxId).element.classList.add('closed'));
});
