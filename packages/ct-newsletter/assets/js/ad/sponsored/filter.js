jQuery(document).ready(function ($) {
    document.getElementById("btn-sponsored-filter").onclick = function () {
        let adGroupElement = document.getElementById('ad-group');
        let adGroup = adGroupElement ? adGroupElement.value : '';

        let data = {
            'action': 'renderSponsoredAd',
            'adGroup': adGroup,
            'sponsoredAdNonce': Sponsored_Ad_Variables.sponsoredAdNonce,
        }

        $.post(ajaxurl, data, function (response) {
            $("#sponsored-ad").html(response);
        }).fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    };
});
