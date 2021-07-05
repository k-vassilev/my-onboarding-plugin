console.log('works')

jQuery('input').on('change', (e) => {
    const filter_target = e.target.checked;
    console.log(filter_target)
    //alert('Value changed')

    let data = {
        action: 'add_options',
        ob_filter: filter_target
    }
    if ( data != null ) {
        
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: ajax_object.ajax_url,
            data: data,
            success: function (response) {
                console.log(response)
                alert('works');
            },
            error: function (response) {
                console.log(response)
                alert('error');
            }
        })
    }
});