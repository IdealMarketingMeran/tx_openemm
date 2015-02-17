$(document).ready(function () {
    var zone = $("#field-zone");
    if ($("#field-region").length > 0 && $("#field-zone").length == 0) {
        zone = $("#field-region");
    }
    if(zone.find('option').length < 2)
        zone.attr('disabled', 'disabled');
    
    $("#field-country").bind('change', function () {
        var $this = $(this);
        var ajaxRequestUrl = $this.parents("form").attr('action');
        var para = ajaxRequestUrl.split('&');
        ajaxRequestUrl = "";
        for (var i = 0; i < para.length; i++) {
            var keyValue = para[i].split('=');
            if (keyValue[0] != "cHash") {
                ajaxRequestUrl += para[i];
            }
        }
        ajaxRequestUrl += "&tx_openemm_pi1[action]=getZoneAjax&tx_openemm_pi1[countryIsoA3]=" + $this.val();
        console.log(ajaxRequestUrl);
        $.ajax({
            url: ajaxRequestUrl,
            data: {type: 2415377}
        }).done(function (data) {
            zone.find('option').each(function () {
                if ($(this).attr('value') != '0' && $(this).attr('value') != 'NULL') {
                    $(this).remove();
                    console.log('delete!');
                }
            });
            $.each(data, function () {
                zone.append('<option value="' + this.key + '">' + this.value + '</option>');
            });
            if(zone.find('option').length < 2) {
                zone.attr('disabled', 'disabled');
                zone.find('option').attr('value', 'NULL');
            } else {
                zone.removeAttr('disabled');
                $(zone.find('option').get(0)).attr('value', '0');
            }
            
            console.log(data);
            return false;
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
            console.log(jqXHR);
            console.log(errorThrown);
            return false;
        });
        return false;
    });
});
