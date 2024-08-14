jQuery(document).ready(function ($) {
    $(".daily-ad-filters table th").css('padding-right', '50px');
    document.getElementById("btn-daily-filter").onclick = function () {
        let dayOfWeekElement = document.getElementById('day-of-week');
        let dayPeriodElement = document.getElementById('day-period');

        let dayOfWeek = dayOfWeekElement ? dayOfWeekElement.value : '';
        let dayPeriod = dayPeriodElement ? dayPeriodElement.value : '';

        let data = {
            'action': 'renderDailyAd',
            'dayOfWeek': dayOfWeek,
            'dayPeriod': dayPeriod,
            'daily-ad-nonce': Daily_Ad_Variables.dailyAdNonce,
        }

        $.post(ajaxurl, data, function (response) {
            $("#weekly-ads").html(response);
        }).fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    };
});
