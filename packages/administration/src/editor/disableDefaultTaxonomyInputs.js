document.addEventListener('DOMContentLoaded', function(event) {
    const fields = ['name', 'slug', 'parent'];
    const deleteLink = document.getElementById('delete-link');

    fields.forEach(fieldId => document.getElementById(fieldId).setAttribute('disabled', 'disabled'));
    deleteLink.remove();
});
