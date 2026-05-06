jQuery(document).on('click', 'button.cwp-add-new-business-hour', function (event) {
    
    event.preventDefault();
    var $this = jQuery(this);
    var $this = jQuery(this);
    var field_name = $this.data('name');
    var field_id = $this.data('id');
    var $fieldContainer = $this.closest('.cwp-field-business_hours');
    // HTML for the remove icon
    var remove = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"></path></svg>',
        error = !1,
        fullday = '',
        fullhoursclass = '';
    var $dash = ' - ';
    // Get selected weekday
    var weekday = $fieldContainer.find('[name="' + field_name + '_day"]').val();
    // Check if a weekday is selected
    if (weekday === "") {

        if ( typeof cwp_notification_ui == 'function' ) {
            cwp_notification_ui('error','Please Select Day');
        }else{
            alert("Please Select Day");
        }
       
        error = !0; // Set error to true if no weekday is selected
        return;
    }
    
    // Check if the "24 hours" checkbox is checked
    if ($this.closest('.cwp-field-business_hours').find(".yb_fulldayopen").is(":checked")) {
        $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').attr('checked', !1);
        // Disable time picker fields if 24 hours is checked
        jQuery('#' + field_id + '_open_time').prop("disabled", !1);
        jQuery('#' + field_id + '_close_time').prop("disabled", !1);
        var open_time = '';
        var close_time = '';
        var $start_time = '';
        var $end_time = '';
        fullday = $this.data('fullday');
        fullhoursclass = 'fullhours';
        $dash = ""
    } else {
        // Get selected open and close times
        var open_time = $fieldContainer.find('[name="' + field_name + '_open_time"]').val();
        var close_time = $fieldContainer.find('[name="' + field_name + '_close_time"]').val();
        // Convert times to AM/PM format
        $start_time = convertTimeToAMPM(open_time);
        $end_time = convertTimeToAMPM(close_time);
        var temp_open_time = timeStringToSeconds(open_time),
            temp_close_time = timeStringToSeconds(close_time);
        if(temp_open_time >= temp_close_time){

            if ( typeof cwp_notification_ui == 'function' ) {
                cwp_notification_ui('error','Your Closing Time Should be More Than Open Time.');
            }else{
                alert("Your Closing Time Should be More Than Open Time.");
            }
            
            error = !0
            return;
        }
        if (open_time === "" || close_time === "") {

            if ( typeof cwp_notification_ui == 'function' ) {
                cwp_notification_ui('error','Please Select Open/Close Time');
            }else{
                alert("Please Select Open/Close Time.");
            }
           
            error = !0
            return;
        } 
        
    }
    var weakdayClass = '.' + weekday + '-' + field_name,
        weekdayAdded = $fieldContainer.find(weakdayClass),
        WDclose_time = $fieldContainer.find('.' + weekday + '-close').last().val();

    // Allow only one slot per day.
    if (weekdayAdded.hasClass(weekday)) {
        if ( typeof cwp_notification_ui == 'function' ) {
            cwp_notification_ui('error', 'Sorry, you have already added this day.');
        } else {
            alert('Sorry, you have already added this day.');
        }
        error = !0;
        return;
    }

    // Multiple slots for same day are intentionally blocked above.
    if (error != !0) {
        var business_hoursDisplay = $this.closest('.cwp-field-business_hours').find('.yb-business-hours-display'),
            emptyTimingsInput = business_hoursDisplay.find('.cwp-empty-business-hours');
            
        // Remove empty business-hours input if present
        if (emptyTimingsInput.length > 0) {
            emptyTimingsInput.remove();
        }
        let args = {
            field_name: field_name,
            weekday: weekday,
            fullday: fullday,
            start_time: $start_time,
            dash: $dash,
            end_time: $end_time,
            remove: remove,
            open_time: open_time,
            close_time: close_time,
            day_name: formatBusinessDayLabel(weekday),
        };
        $this.closest('.cwp-field-business_hours').find('.yb-business-hours-display').append(
            `<div class='business-hours ${weekday} ${weekday}-${field_name} ${fullhoursclass}'>
                ${BusinessHourHTML(args)}
            </div>`
            );
                
        // Reset select and input fields
        var nextWeekday = getNextBusinessWeekday(weekday);
        $fieldContainer.find('[name="' + field_name + '_day"]').val(nextWeekday).trigger('change');
        $fieldContainer.find('[name="' + field_name + '_open_time"]').val('09:00');
        $fieldContainer.find('#' + field_id + '_open_time').val('09:00');
        $fieldContainer.find('[name="' + field_name + '_close_time"]').val('17:00');
        $fieldContainer.find('#' + field_id + '_close_time').val('17:00');
        $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').prop('checked', false); // Uncheck the full day open checkbox
    }
});

// Handle change event for 24 hour checkbox
jQuery(document).on('change', '.yb_fulldayopen', function (e) {
    var $this = jQuery(this);
    if (this.checked) {
        $this.closest('.cwp-field-business_hours').find('.cwp-field-time_picker .hasDatepicker').prop("disabled", !0); // Disable time picker inputs when checkbox is checked

    } else {
        $this.closest('.cwp-field-business_hours').find('.cwp-field-time_picker .hasDatepicker').prop("disabled", !1); // Enable time picker inputs when checkbox is unchecked
    }
});

jQuery(document).on('change', '.business-days', function (e) {
    var $this = jQuery(this),
        value = $this.val(),
        name  = $this.attr('name'),
        businessClass = '.'+value+'-'+name.split('_day').join(''),
        fullhoursChecked = false,
        weekdayAdded = jQuery(businessClass);

        if ($this.closest('.cwp-field-business_hours').find(".yb_fulldayopen").is(":checked")) {
            fullhoursChecked = true;
        }
        if(weekdayAdded.hasClass(value) && weekdayAdded.hasClass('fullhours')){

            $this.closest('.cwp-field-business_hours').find('.cwp-field-time_picker .hasDatepicker').prop("disabled", !0);
            $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').removeAttr('checked');
            $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').prop("disabled", !0);
            if ( typeof cwp_notification_ui == 'function' ) {
                cwp_notification_ui('error',value+ ' is 24 Hour Open, You cannot add 2nd slot.');
            }else{
                alert(value+ ' is 24 Hour Open, You cannot add 2nd slot.');
            }
            
            error = !0
            return;

        }else if(weekdayAdded.hasClass(value)){
            $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').prop("disabled", !0);
            $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').removeAttr('checked');
            $this.closest('.cwp-field-business_hours').find('.cwp-field-time_picker .hasDatepicker').prop("disabled", !1);
        }else{
            if(fullhoursChecked == false){
                $this.closest('.cwp-field-business_hours').find('.yb_fulldayopen').prop("disabled", !1);
                $this.closest('.cwp-field-business_hours').find('.cwp-field-time_picker .hasDatepicker').prop("disabled", !1);
            }
            
        }
});

// Handle click event for 24 hours label to checked the 24 hours
jQuery(document).on('click', '.yb_business_hour_fulldayopen label', function (e) {
         var $this = jQuery(this);
        $this.closest('.yb_business_hour_fulldayopen').find('#yb_fulldayopen').trigger('click'); // Trigger click event on the associated checkbox when label is clicked
});

// Handle click event for Remove timing link
jQuery(document).on('click', 'a.remove-business-hours', function (event) {
    event.preventDefault();
    var $this = jQuery(this);
    var field_name = $this.data('field_name'),
        weekday = $this.data('weekday'),
        business_hours = jQuery('.'+weekday+'-'+field_name);
        
    business_hours.remove(); // Remove the timing entry from the DOM
});

function timeStringToSeconds(timeString) {
    if (!timeString) {
        return NaN;
    }

    var raw = String(timeString).trim();
    if (!raw) {
        return NaN;
    }

    if (raw === '24-hours-open') {
        return 86400;
    }

    // Supports:
    // - 24h: "17:00", "17:00:00"
    // - 12h: "07:00 AM", "07:00:00 PM", "4:30 pm"
    var match = raw.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*([AaPp][Mm])?$/);
    if (!match) {
        return NaN;
    }

    var hours = parseInt(match[1], 10);
    var minutes = parseInt(match[2], 10);
    var seconds = match[3] ? parseInt(match[3], 10) : 0;
    var meridiem = match[4] ? match[4].toLowerCase() : '';

    if (meridiem) {
        if (hours === 12) {
            hours = 0;
        }
        if (meridiem === 'pm') {
            hours += 12;
        }
    }

    return hours * 3600 + minutes * 60 + seconds;
}

function getNextBusinessWeekday(currentWeekday) {
    var weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    var index = weekdays.indexOf((currentWeekday || '').toLowerCase());
    if (index === -1) {
        return 'monday';
    }
    return weekdays[(index + 1) % weekdays.length];
}

function BusinessHourHTML(object){

    var fullday = object['fullday'],
        $start_time = object['start_time'],
        $end_time = object['end_time'],
        $dash = object['dash'],
        field_name = object['field_name'],
        remove = object['remove'],
        weekday = object['weekday'],
        open_time = object['open_time'],
        close_time = object['close_time'],
        DayName = object['day_name'],
        $meta = 'cwp_user_form[cwp_meta]['+field_name+']['+weekday+']',
        $metaOpen = '[open][]',
        $metaClose = '[close][]';

    if(jQuery('body').hasClass('post-php') || jQuery('body').hasClass('post-new-php')){
        $meta = 'cwp_meta['+field_name+']['+weekday+']';
    }
    if(jQuery('body').hasClass('cubewp_page_cubewp-settings')){
        $meta = field_name+'['+weekday+']';
    }
    if(fullday !== ''){
        $metaClose = $metaOpen = '';
        close_time = open_time = '24-hours-open';
    }
    return `<div class="day-hours">
        <span class='weekday'>${DayName}</span>
        <span class='start-end fullday'>${fullday}</span>
        <span class='open'>${$start_time}</span>
        <span class='dash'>${$dash}</span>
        <span class='close'>${$end_time}</span>
        <a class='remove-business-hours' href='#' data-field_name ='${field_name }' data-weekday ='${weekday }'>${remove}</a>
        <input class="${weekday}-open" name='${$meta}${$metaOpen}' value='${open_time}' type='hidden'>
        <input class="${weekday}-close" name='${$meta}${$metaClose}' value='${close_time}' type='hidden'>
    </div>`;
}
// Function to convert time to AM/PM format
function convertTimeToAMPM(timeString) {
    if (!timeString) {
        return '';
    }

    var raw = String(timeString).trim();
    if (raw === '24-hours-open') {
        return '';
    }

    if (/[AaPp][Mm]$/.test(raw)) {
        // Already in 12h format (e.g. 07:00:00 AM), normalize to hh:mm AM/PM.
        var twelveHourMatch = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])$/);
        if (twelveHourMatch) {
            var h = parseInt(twelveHourMatch[1], 10);
            var m = twelveHourMatch[2];
            var ap = twelveHourMatch[3].toUpperCase();
            var paddedHour = (h < 10 ? '0' : '') + h;
            return paddedHour + ':' + m + ' ' + ap;
        }
    }

    var timeComponents = raw.split(":");
    var hours = parseInt(timeComponents[0], 10);
    var minutes = parseInt(timeComponents[1], 10);
    var period = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    var formattedHour = hours < 10 ? "0" + hours : String(hours);
    var formattedTime = formattedHour + ":" + (minutes < 10 ? "0" + minutes : minutes) + " " + period;
    return formattedTime;
}

function formatBusinessDayLabel(dayValue) {
    if (!dayValue) {
        return '';
    }
    var day = String(dayValue).toLowerCase();
    return day.charAt(0).toUpperCase() + day.slice(1);
}

jQuery(function () {
    jQuery('.cwp-field-business_hours').each(function () {
        var $fieldContainer = jQuery(this);
        var $day = $fieldContainer.find('.business-days');
        var $open = $fieldContainer.find('.business-open-time');
        var $close = $fieldContainer.find('.business-close-time');

        if ($day.length && !$day.val()) {
            $day.val('monday').trigger('change');
        }
        if ($open.length && !$open.val()) {
            $open.val('09:00');
        }
        if ($close.length && !$close.val()) {
            $close.val('17:00');
        }
    });
});