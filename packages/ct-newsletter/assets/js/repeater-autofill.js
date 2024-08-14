acf.add_filter('select2_ajax_data', function (data) {
    var FIELD_REPEATER_POST = 'field_606edda277a9a';
    var FIELD_NEWSLETTER_TYPE = 'field_606edbd026195';

    if (data.field_key === FIELD_REPEATER_POST) {
        var topic = acf.getField(FIELD_NEWSLETTER_TYPE);
        data.s = `${topic.val()}----${data.s}`;
    }

    return data;
});
