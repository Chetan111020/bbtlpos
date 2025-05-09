<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<div class="form-group pull-right">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('heatmap_year_select', 'Year:') !!}
                {!! Form::select('heatmap_year_select', [], null, [
                    'class' => 'form-control',
                    'id' => 'heatmap_year_select',
                    'placeholder' => 'Select Year',
                ]) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('heatmap_month_select', 'Month:') !!}
                {!! Form::select('heatmap_month_select', [], null, [
                    'class' => 'form-control',
                    'id' => 'heatmap_month_select',
                    'placeholder' => 'Select Month',
                    'disabled' => true,
                ]) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('heatmap_week_select', 'Week:') !!}
                {!! Form::select('heatmap_week_select', [], null, [
                    'class' => 'form-control',
                    'id' => 'heatmap_week_select',
                    'placeholder' => 'Select Week',
                    'disabled' => true,
                ]) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group" style="margin-top: 25px;">
                <button class="btn btn-primary" name="filter" id="filterHeatMapData">Filter</button>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        // Fill year dropdown (last 10 years)
        let currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 10; i--) {
            $('#heatmap_year_select').append(`<option value="${i}">${i}</option>`);
        }

        // On Year select → Enable and fill month
        $('#heatmap_year_select').on('change', function() {
            let year = $(this).val();
            $('#heatmap_month_select').empty().append('<option value="">Select Month</option>');
            $('#heatmap_month_select').prop('disabled', false);
            $('#heatmap_week_select').empty().append('<option value="">Select Week</option>').prop(
                'disabled',
                true);

            for (let m = 0; m < 12; m++) {
                if (year == currentYear && m > new Date().getMonth()) continue;
                const monthName = moment().month(m).format('MMMM');
                $('#heatmap_month_select').append(`<option value="${m+1}">${monthName}</option>`);
            }
        });

        // On Month select → Enable and fill weeks
        $('#heatmap_month_select').on('change', function() {
            let year = $('#heatmap_year_select').val();
            let month = $(this).val().padStart(2,
                '0'); // Ensure month is zero-padded (e.g., 01, 02, etc.)
            $('#heatmap_week_select').empty().append('<option value="">Select Week</option>');
            $('#heatmap_week_select').prop('disabled', false);

            if (year && month) {
                let firstDay = moment(`${year}-${month}-01`);
                let lastDay = moment(firstDay).endOf('month');

                let week = 1;
                let startOfWeek = firstDay.clone().startOf('isoWeek'); // Monday
                let endOfWeek = startOfWeek.clone().endOf('isoWeek'); // Sunday

                // Loop through weeks and add them to the dropdown
                while (startOfWeek.isBefore(lastDay) || (endOfWeek.month() === moment(
                        `${year}-${month}-01`).month())) {
                    let optionText = `${startOfWeek.format('MMM D')} - ${endOfWeek.format('MMM D')}`;
                    $('#heatmap_week_select').append(
                        `<option value="${startOfWeek.format('YYYY-MM-DD')}|${endOfWeek.format('YYYY-MM-DD')}">Week ${week}: ${optionText}</option>`
                    );

                    // Move to next week
                    startOfWeek.add(1, 'week');
                    endOfWeek = startOfWeek.clone().endOf('isoWeek');
                    week++;
                }

                // If the last week of the month doesn't complete a full week, we need to include the next month's week.
                if (endOfWeek.isBefore(lastDay)) {
                    let nextMonthStart = moment(`${year}-${month}-01`).add(1, 'month').startOf('month');
                    let nextMonthEnd = nextMonthStart.clone().endOf('isoWeek');

                    let optionText =
                        `${nextMonthStart.format('MMM D')} - ${nextMonthEnd.format('MMM D')}`;
                    $('#heatmap_week_select').append(
                        `<option value="${nextMonthStart.format('YYYY-MM-DD')}|${nextMonthEnd.format('YYYY-MM-DD')}">Week ${week}: ${optionText}</option>`
                    );
                }
            }
        });
    });
</script>
