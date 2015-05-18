$(document).ready(function () {
    var openemmFieldZone = $(".tx-openemm #field-zone");
    if ($(".tx-openemm #field-region").length > 0 && $(".tx-openemm #field-zone").length == 0) {
        openemmFieldZone = $(".tx-openemm #field-region");
    }
    if(openemmFieldZone.find('option').length < 2)
        openemmFieldZone.attr('disabled', 'disabled');
    
    $(".tx-openemm #field-country").bind('change', function () {
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
            openemmFieldZone.find('option').each(function () {
                if ($(this).attr('value') != '0' && $(this).attr('value') != 'NULL') {
                    $(this).remove();
                    console.log('delete!');
                }
            });
            $.each(data, function () {
                openemmFieldZone.append('<option value="' + this.key + '">' + this.value + '</option>');
            });
            if(openemmFieldZone.find('option').length < 2) {
                openemmFieldZone.attr('disabled', 'disabled');
                openemmFieldZone.find('option').attr('value', 'NULL');
            } else {
                openemmFieldZone.removeAttr('disabled');
                $(openemmFieldZone.find('option').get(0)).attr('value', '0');
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
    setTimeout(function() {$(".tx-openemm #field-country").trigger('change')}, 100);
});
