
    $(document).ready(function(){
        $("#form-group-media").hide();
        $("#form-group-youtube").hide();
        $("#form-group-text").hide();
        $("#type").change(function(){
            var val = $(this).val();

            if (val == 'youtube') {
                $("#form-group-youtube").show();
                $("#form-group-media").hide();
                $("#form-group-text").hide();
            } else {
                $("#form-group-youtube").hide();
                $("#form-group-media").show();
                $("#form-group-text").show();
            }
        });

        $("#type").change();
    })
