@extends('admin.layouts.admin-dashboard')

@section('dash_content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="" id="dashboard">
                <div id="menu-links" class="clearfix">
                    <a href="{{ route('admin.users.index') }}" class="dash-icon mb-3">
                        <i class="icon-users"></i>
                        <div class="title">ادمین&zwnj;ها</div>
                        <div class="count" style="height: 24px"></div>
                    </a>
                </div>
                <hr>
                <div id="charts" class="clearfix">
                    <div id="year-month" class="clearfix">
                        <div id="select-year" class="clearfix">
                            <div class="next direction cursor-pointer">
                                <i class="icon-right-dir" id="next-year"></i>
                            </div>
                            <div class="year">
                                <input type="text" id="selected-year" class="text-l ltr" value="{{ $current_year }}">
                            </div>
                            <div class="previous direction cursor-pointer">
                                <i class="icon-left-dir" id="previous-year"></i>
                            </div>
                        </div>
                        <canvas id="year-month-chart"></canvas>
                    </div>
                    <div id="month-day" class="clearfix">
                        <canvas id="month-day-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/chart.js') }}"></script>
    <script>
        $(document).ready(function () {
            let barChartData = {
                labels: [],
                datasets: [{
                    label: 'تعداد نظرات',
                    backgroundColor: 'red',
                    borderColor: 'red',
                    borderWidth: 1,
                    data: []
                },
                ]
            };

            let lineChartData = {
                labels: [],
                datasets: [{
                    label: 'تعداد نظرات',
                    fill: false,
                    borderColor: 'blue',
                    lineTension: 0,
                    pointRadius: 5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: 'green',
                    pointHoverBackgroundColor: 'green',
                    pointBorderColor: 'green',
                    borderWidth: 2,
                    data: [],
                },
                ]
            };

                /* year-month chart */
                let ctxYM = document.getElementById('year-month-chart').getContext('2d');
                let YearMontChart = new Chart(ctxYM, {
                    type: 'bar',
                    data: barChartData,
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1
                                }
                            }]
                        },
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'تعداد نظرات در هر ماه'
                        }
                    }
                });

                let updateYearMonthCart = function (year = null){
                    $.ajax({
                        type: 'post',
                        url: '{{ route("admin.getCommentsInYearByMonth") }}',
                        data: {
                            _token: _token,
                            year: year
                        },
                        success: function (data){
                            let comments_count = data.comments_count,
                                month_names = data.month_names;
                            YearMontChart.data.labels = month_names;
                            YearMontChart.data.datasets[0].data = comments_count;
                            YearMontChart.update();
                        }
                    });
                }

                updateYearMonthCart();

                $('#update-chart').on('click', function (){
                updateYearMonthCart();
            });

                // update year-month chart when change year
                let nextYear = $('#next-year'),
                    previousYear = $('#previous-year'),
                    selectedYear = $('#selected-year'),
                    selected_year = selectedYear.val();
                nextYear.on('click', function (){
                    let next_year = ++selected_year;
                    selectedYear.val(next_year);
                    updateYearMonthCart(next_year);
                });
                previousYear.on('click', function (){
                    let previous_year = --selected_year;
                    selectedYear.val(previous_year);
                    updateYearMonthCart(previous_year);
                });
                selectedYear.on('change paste keyup', function (){
                    $(this).removeClass('text-danger');
                    let selected_year = parseInt($(this).val());
                    $(this).val(selected_year);
                    let value_length = selected_year.toString().length;
                    if (value_length != 4){
                        $(this).addClass('text-danger');
                    }else{
                        updateYearMonthCart(selected_year);
                    }
                })

                /* month-day chart */
                let ctxMD = document.getElementById('month-day-chart').getContext('2d');
                let MontDayChart = new Chart(ctxMD, {
                    type: 'line',
                    data: lineChartData,
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1
                                }
                            }]
                        },
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'تعداد نظرات در روز'
                        }
                    }
                });

                let updateMonthDayChart = function (month = null, year = null){
                    $.ajax({
                        type: 'post',
                        url: '{{ route("admin.getCommentsInMontByDay") }}',
                        data: {
                            _token: _token,
                            month: month,
                            year: year
                        },
                        success: function (data){
                            console.log(data);
                            let comments_count = data.comments_count,
                                days = data.days;
                            console.log(days);
                            console.log(comments_count);
                            MontDayChart.data.labels = days;
                            MontDayChart.data.datasets[0].data = comments_count;
                            MontDayChart.update();
                        }
                    });
                }

                updateMonthDayChart();
        });
    </script>
@endsection
