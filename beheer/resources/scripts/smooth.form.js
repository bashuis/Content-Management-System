/* this function styles inputs with the type file. It basically replaces browse or choose with a custom button */
(function ($) {
    $.fn.file = function (options) {
        var settings = {
            width: 250
        };

        if (options) {
            $.extend(settings, options);
        }

        this.each(function () {
            var self = this;

            var wrapper = $("<a>").attr("class", "ui-input-file");

            var filename = $('<input type="text" class="file" readonly="readonly">').addClass($(self).attr("class")).css({
                "display": "inline",
                "width": settings.width + "px"
            });

            $(self).before(filename);
            $(self).wrap(wrapper);

            $(self).css({
                "position": "relative",
                "height": settings.image_height + "px",
                "width": settings.width + "px",
                "display": "inline",
                "cursor": "pointer",
                "opacity": "0.0"
            });

            if ($.browser.mozilla) {
                if (/Win/.test(navigator.platform)) {
                    $(self).css("margin-left", "-142px");
                } else {
                    $(self).css("margin-left", "-168px");
                };
            } else {
                $(self).css("margin-left", settings.image_width - settings.width + "px");
            };

            $(self).bind("change", function () {
                filename.val($(self).val());
            });
        });

        return this;
    };
})(jQuery);

$(document).ready(function () {
    $("input.focus, textarea.focus").focus(function () {
        if (this.value == this.defaultValue) {
            this.value = "";
        }
        else {
            this.select();
        }
    });

    $("input.focus, textarea.focus").blur(function () {
        if ($.trim(this.value) == "") {
            this.value = (this.defaultValue ? this.defaultValue : "");
        }
    });

    /* date picker */
    $(".date").datepicker({
        showOn: 'both',
        buttonImage: '/beheer/resources/images/ui/calendar.png',
        dateFormat: 'dd-mm-yy',
        monthNames: ['januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'],
        dayNamesMin: ['Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za'],
        buttonImageOnly: true,
        changeYear: true
    });

    /* select styling */
    $("select").selectmenu({
        style: 'dropdown',
        maxHeight: 150,
        icons: [
		    { find: '.locked', icon: 'ui-icon-locked' },
		    { find: '.unlocked', icon: 'ui-icon-unlocked' },
		    { find: '.folder-open', icon: 'ui-icon-folder-open' }
	    ]
    });

    /* file input styling */
    $("input[type=file]").file({
        image_height: 28,
        image_width: 28,
        width: 250
    });

    /* button styling */
    $("input:submit, input:reset, button").button();

    $('.colorPicker').each(function() {
        var hex = parseInt((($(this).val().indexOf('#') > -1) ? $(this).val().substring(1) : $(this).val()), 16);
        var rgb = {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
        $(this).css('background-color', '#' + $(this).val());
        $(this).css('width', '55px');
        if (rgb['r'] < 110 && rgb['g'] < 110 && rgb['b'] < 110) {
            $(this).css('color', '#FFF');
        } else {
            $(this).css('color', '#000');
        }
    });

    $('.colorPicker').ColorPicker({
        onBeforeShow: function () {
            $(this).ColorPickerSetColor(this.value);
	},
        onChange: function(hsb, hex, rgb) {
            var cal = $(this);
            $(this).ColorPickerSetColor(hex);
            $(cal.data('colorpicker').el).val(hex);
            $(cal.data('colorpicker').el).css('background-color', '#' + hex);
            if (rgb['r'] < 110 && rgb['g'] < 110 && rgb['b'] < 110) {
                $(cal.data('colorpicker').el).css('color', '#FFF');
            } else {
                $(cal.data('colorpicker').el).css('color', '#000');
            }
	},
        onSubmit: function(hsb, hex, rgb, el) {
            $(el).val(hex);
            $(el).css('background-color', '#' + hex);
            if (rgb['r'] < 110 && rgb['g'] < 110 && rgb['b'] < 110) {
                $(el).css('color', '#FFF');
            } else {
                $(el).css('color', '#000');
            }
        }
    })
    .bind('keyup', function(){
	$(this).ColorPickerSetColor(this.value);
    });
});
