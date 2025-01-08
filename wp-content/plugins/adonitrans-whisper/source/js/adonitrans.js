jQuery(document).ready(function($) {
	$(document).on('click', '.adonitrans-plug .formplug label i', function(event) {
        event.preventDefault();
        $ojo = $(this);
        let input = $(this).closest('label').find('input');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
        } else {
            input.attr('type', 'password');
        }
        $(this).toggleClass('icofont-eye icofont-eye-blocked');
    });
});