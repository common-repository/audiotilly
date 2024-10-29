(function ($) {
    'use strict';

    function openTab(buttonName,viewName){
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(viewName).style.display = "block";
        document.getElementById(buttonName).classList.add("active");
    }

    $(document).ready(function () {
        let isDashboardTab = false;
        const dashboardTab = document.querySelector('.tab.dashboard-tab section');

        if (dashboardTab && window.getComputedStyle(dashboardTab).display === 'block') {
            document.querySelector('.tab-title[for=setup]').click();
            isDashboardTab = true;
        }

        $('fieldset.sample').jScrollPane();

        if (isDashboardTab) {
            document.querySelector('.tab-title[for=dashboard]').click();
        }

        initAdvancedSettingsView();

        function initAdvancedSettingsView() {
            if (!$('#audiotilly_advanced_gui').length) return;

            $( "#audio_tilly_open_main_settings" ).click(function() {
                openTab('audio_tilly_open_main_settings','audio_tilly_main_settings');
            });
            $( "#audio_tilly_open_advanced_settings" ).click(function() {
                openTab('audio_tilly_open_advanced_settings','audio_tilly_advanced_settings');
            });

            openTab('audio_tilly_open_main_settings','audio_tilly_main_settings');//initialize with main settings open
        }

        const chartOptions = {
            title: 'Audio Playback Stats',
            vAxis: {
                title: '# of listens',
                format: '0',
                minValue: 0,
                viewWindow: {
                    min: 0
                }
            },
            legend: {
                position: 'none'
            },
            pointsVisible: true,
            width: 780,
            height: 300
        };

        const drawChart = stats => {
            let data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Listens');

            data.addRows(stats);

            let chart = new google.visualization.LineChart(document.getElementById('audiotilly-statistic-chart-container'));
            chart.draw(data, google.charts.Line.convertOptions(chartOptions));
        };

        const AUDIOTILLY_URL = document.getElementById('AUDIOTILLY_URL') ? document.getElementById('AUDIOTILLY_URL').value : '';
        const AUDIOTILLY_API_URL = document.getElementById('AUDIOTILLY_API_URL') ? document.getElementById('AUDIOTILLY_API_URL').value : '';
        const AUDIBROW_API_HOST = document.getElementById('ROOT_URL') ? document.getElementById('ROOT_URL').value : '';

        const dateIntervalInput = document.getElementById('report-interval');
        const postsHeader = document.querySelector('.top-heard-articles .info');
        const avgListenTime = document.querySelector('.avg-listen-time > div:last-child');
        const avgListensPerVisit = document.querySelector('.avg-listen-player > div:last-child');
        const refreshes = document.getElementsByClassName('refresh-posts-statistic');

        const fetchStatistic = () =>
            fetch(AUDIOTILLY_API_URL + 'statistic?start_date=' + moment(dateIntervalInput.value.split(' - ')[0], 'MM/DD/YY').format('YYYY-MM-DD') + '&end_date=' + moment(dateIntervalInput.value.split(' - ')[1], 'MM/DD/YY').format('YYYY-MM-DD'))
                .then(response => response.json())
                .then(response => {
                    if (!response.hasOwnProperty('statistic_per_post')) {
                        return;
                    }

                    document.getElementById('audiotilly-plan').innerHTML = response.plan.name;
                    document.getElementById('audiotilly-plan-conversions').innerHTML = response.plan.articles_per_month;

                    Array.prototype.map.call(document.getElementsByClassName('total-converted'), function (span) {
                        span.innerHTML = response.total_converted;
                    });
                    document.getElementById('conversions-left').innerHTML = response.conversions_left;

                    while (postsHeader.nextElementSibling) {
                        postsHeader.nextElementSibling.remove();
                    }
                    let str = '';
                    response.statistic_per_post.map(st => str += `<div class="grey-text"><div>${st.title}</div><div>${st.plays} plays</div></div>`);
                    postsHeader.insertAdjacentHTML('afterend', str);

                    avgListenTime.innerHTML = new Date(response.avg_listen_time * 1000).toJSON().slice(11, 19);
                    avgListensPerVisit.innerHTML = response.avg_listens_per_visit;

                    let endDate = $(dateIntervalInput).data('daterangepicker').endDate.format('DD/MM');
                    drawChart(response.statistic_per_day.length ? response.statistic_per_day : [[endDate, 0]]);
                })
                .catch(console.log);

        $(dateIntervalInput).daterangepicker({
            locale: {
                format: 'MM/DD/YY'
            },
            separator : ' - ',
            maxDate: moment().format('MM/DD/YY'),
            autoApply: true
        });

        $(dateIntervalInput).on('apply.daterangepicker', fetchStatistic);

        if (refreshes.length) {
            google.charts.load('current', {'packages': ['corechart', 'line']});
            google.charts.setOnLoadCallback(fetchStatistic);
            Array.prototype.map.call(refreshes, refresh => {
                refresh.addEventListener('click', fetchStatistic);
                refresh.addEventListener('click', e => e.currentTarget.firstElementChild.classList.toggle('rotate'));
            });
        }

        $('#intro-upload-input, #exit-upload-input').on('change', function () {
            var data = new FormData(),
                input = this,
                clip = this.getAttribute('data-clip'),
                replyWrapper = document.getElementById(clip + '-reply');

            data.append('wp_url', AUDIOTILLY_URL);
            data.append(clip, this.files[0]);

            $.ajax({
                url         : AUDIBROW_API_HOST + '/api/plugin/' + clip,
                type        : 'POST',
                data        : data,
                cache       : false,
                dataType    : 'json',
                processData : false,
                contentType : false,
                success     : function (respond, status, jqXHR) {
                    replyWrapper.innerHTML = '<img src="' + AUDIOTILLY_URL + '/wp-content/plugins/audiotilly/admin/assets/loaded-mark.png" class="clip-uploaded">' + respond.message;
                    document.getElementById(clip + '-controls').innerHTML = '<img id="' + clip + '-play" src="' + AUDIOTILLY_URL + '/wp-content/plugins/audiotilly/admin/assets/play.png" data-clip="' + clip + '"><img id="' + clip + '-trash-button" src="' + AUDIOTILLY_URL + '/wp-content/plugins/audiotilly/admin/assets/trash.png" data-clip="' + clip + '">';
                    var audio = document.getElementById(clip + '-audio');
                    var src = new URL(audio.src);
                    src.searchParams.set('t', Math.floor(Date.now() / 1000));
                    audio.src = src;
                    setTimeout(function () {
                        replyWrapper.innerHTML = '';
                    }, 3000);
                    input.value = '';
                },
                error: function (jqXHR, status, errorThrown) {
                    console.log('Error ajax request: ' + status, jqXHR);

                    for (var error in jqXHR.responseJSON.errors) {
                        replyWrapper.innerHTML += jqXHR.responseJSON.errors[error].join(', ');
                    }
                    setTimeout(function () {
                        replyWrapper.innerHTML = '';
                    }, 3000);
                    input.value = '';
                }
            });
        });

        var clipContainer = $('.clip-container');

        clipContainer.on('click', '#intro-play, #exit-play', function () {
            var audio = document.getElementById(this.getAttribute('data-clip') + '-audio');
            audio.pause();
            audio.currentTime = 0.0;
            audio.play();
        });

        clipContainer.on('click', '#intro-trash-button, #exit-trash-button', function () {
            var clip = this.getAttribute('data-clip'),
                replyWrapper = document.getElementById(clip + '-reply');

            $.ajax({
                url         : AUDIBROW_API_HOST + '/api/plugin/' + clip + '?wp_url=' + encodeURIComponent(AUDIOTILLY_URL),
                type        : 'GET',
                success     : function (respond, status, jqXHR) {
                    replyWrapper.innerHTML = '<img src="' + AUDIOTILLY_URL + '/wp-content/plugins/audiotilly/admin/assets/loaded-mark.png" class="clip-uploaded">' + respond.message;
                    document.getElementById(clip + '-controls').innerHTML = '';
                    setTimeout(function () {
                        replyWrapper.innerHTML = '';
                    }, 3000);
                },
                error: function (jqXHR, status, errorThrown) {
                    console.log('Error ajax request: ' + status, jqXHR);

                    for (var error in jqXHR.responseJSON.errors) {
                        replyWrapper.innerHTML += jqXHR.responseJSON.errors[error].join('. ');
                    }
                    setTimeout(function () {
                        replyWrapper.innerHTML = '';
                    }, 3000);
                }
            });
        });
    });
})(jQuery);
