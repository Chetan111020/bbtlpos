@section('javascript')
    @include('smartcrm::dashboard-custom-chart-js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"
        integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        (g => {
            var h, a, k, p = "The Google Maps JavaScript API",
                c = "google",
                l = "importLibrary",
                q = "__ib__",
                m = document,
                b = window;
            b = b[c] || (b[c] = {});
            var d = b.maps || (b.maps = {}),
                r = new Set,
                e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() =>
                d[l](f, ...n))
        })
        ({
            key: "AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc",
            v: "weekly",
            // sensor: "false",
            libraries: 'marker',
            callback: 'initMap'
        });
    </script>
    <script>
        let map;
        let userMarker;
        let infoWindow;

        async function initMap() {
            $("#GoogleMap").removeClass('hide');
            $("#LoadButton").addClass('hide');
            // Request the necessary libraries from the Google Maps API
            const {
                Map,
                InfoWindow
            } = await google.maps.importLibrary("maps");
            const {
                AdvancedMarkerElement
            } = await google.maps.importLibrary("marker");

            // Initialize the map
            map = new Map(document.getElementById("map"), {
                zoom: 3,
                mapId: 'roadmap',
                mapTypeId: google.maps.MapTypeId.ROADMAP,
            });

            // Initialize the InfoWindow
            infoWindow = new InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            // Get user's location and add the user marker
            getUserLocation();

            // Load markers from the server
            loadMarkers(map, infoWindow, AdvancedMarkerElement);
        }

        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // Get user's latitude and longitude
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    $("#coordinates").val(userLat + ", " + userLng);

                    // Set the center of the map to the user's live location
                    map.setCenter(new google.maps.LatLng(userLat, userLng));
                    if (userMarker) {
                        userMarker.setPosition(new google.maps.LatLng(userLat, userLng));
                    } else {
                        // Create the user marker content as a DOM element
                        const userImage = document.createElement("img");
                        userImage.src = "/img/UserMap.gif";
                        userImage.width = 50;
                        userImage.height = 50;
                        userImage.alt = "User Location";

                        userMarker = new google.maps.marker.AdvancedMarkerElement({
                            position: new google.maps.LatLng(userLat, userLng),
                            content: userImage, // Set content as a DOM node
                            map: map,
                        });

                        userMarker.addListener("click", function() {
                            // Create content for the InfoWindow
                            const infoContent = document.createElement("div");
                            infoContent.className = "infowindow-content";

                            const button = document.createElement("button");
                            button.type = "button";
                            button.className = "btn btn-block btn-primary btn-modal";
                            button.setAttribute("data-href", "{{ route('smartcrm.lead.LeadsCreate') }}");
                            button.setAttribute("data-container", ".contact_modal");
                            button.textContent = "Add Leads";

                            infoContent.appendChild(button);

                            // Set the content of the InfoWindow
                            infoWindow.setContent(infoContent);

                            // Open the info window
                            infoWindow.open(map, userMarker);
                        });
                    }
                }, function(error) {
                    console.error("Error getting user's location:", error.message);
                });
            } else {
                console.error("Geolocation is not supported by this browser.");
            }
        }



        function loadMarkers(map, infoWindow, AdvancedMarkerElement) {
            $.ajax({
                url: "/CashierDashboard/googlemap", // Your backend route to get the markers data
                type: "get",
                dataType: "json",
                success: function(data) {
                    const salesData = data.salesData;
                    const leadsData = data.leadsData;

                    // Define custom icons for sales, leads, and follow-up stores
                    // const salesImage = "/img/map_store.gif";
                    const salesImage = "/img/new_store.gif";

                    const leadsImage = "/img/leads.gif";
                    const greenIcon = "/img/followupstore.png";
                    // Helper function to create content node
                    const createImageNode = (src, width, height, alt) => {
                        const img = document.createElement("img");
                        img.src = src;
                        img.width = width;
                        img.height = height;
                        img.alt = alt;
                        return img;
                    };

                    // Create Sales markers
                    const salesMarkers = salesData.map((location) => {
                        // Create the content node (image) for AdvancedMarkerElement
                        const imageSrc = location[5] ? greenIcon : salesImage;
                        const salesMarkerContent = createImageNode(imageSrc, 50, 50, "Store Icon");
                        const marker = new AdvancedMarkerElement({
                            position: {
                                lat: location[0],
                                lng: location[1],
                            },
                            content: salesMarkerContent, // Set content as a DOM node
                            map: map,
                        });

                        marker.addListener("click", () => {
                            // Create content for the InfoWindow
                            const infoContent = document.createElement("div");
                            infoContent.innerHTML = `
                        <h5><b>Store Name:</b> ${location[2]}</h5>
                        <h5><b>Total Sell (Final Total):</b> ${__currency_trans_from_en(location[3])}</h5>
                        <div class='btn-toolbar' role='toolbar'>
                            <div class='btn-group' role='group'>
                                <a data-toggle='modal' data-target='#followupmodal' class='btn btn-primary follow-up-button' data-contact-id='${location[4]}'>Follow Up</a>
                            </div>
                            <div class='btn-group' role='group'>
                                <a href='/contacts/${location[4]}' target='_blank' class='btn btn-info'>More Details</a>
                            </div>
                            <div class='btn-group' role='group'>
                                <a href='https://www.google.com/maps/search/?api=1&query=${location[0]},${location[1]}' target='_blank' class='btn btn-danger'>Open in Google Map</a>
                            </div>
                        </div>
                    `;

                            infoWindow.setContent(infoContent);
                            infoWindow.open(map, marker);
                        });

                        return marker;
                    });

                    // Create Leads markers
                    const leadsMarkers = leadsData.map((lead) => {
                        // Create the content node (image) for AdvancedMarkerElement
                        const leadsMarkerContent = createImageNode(leadsImage, 60, 60, "Leads Icon");

                        const marker = new AdvancedMarkerElement({
                            position: {
                                lat: lead[0],
                                lng: lead[1],
                            },
                            content: leadsMarkerContent, // Set content as a DOM node
                            map: map,
                        });

                        marker.addListener("click", () => {
                            // Create content for the InfoWindow
                            const infoContent = document.createElement("div");
                            infoContent.innerHTML = `<h5><b>Lead Name:</b> ${lead[2]}</h5>`;

                            infoWindow.setContent(infoContent);
                            infoWindow.open(map, marker);
                        });

                        return marker;
                    });

                    // Combine sales and leads markers into one cluster
                    new markerClusterer.MarkerClusterer({
                        markers: [...salesMarkers, ...leadsMarkers],
                        map: map,
                    });
                },
                error: function() {
                    toastr.error("Data Not Found");
                },
            });
        }


        // Initialize the map on window load
        window.initMap = initMap;

        $(document.body).on('click', '.follow-up-button', function() {
            $('#scheduled_at').datetimepicker().val('{{ date('m/d/Y H:i:s') }}');
            var contactId = $(this).data('contact-id');
            $('#contacts_id').val(contactId).trigger('change');
            var tagInputEle = $('.tags-input');
            tagInputEle.tagsinput();
        });

        function openFollowUpPopup(customerId) {
            // Open a new window with the Follow Up URL
            var followUpUrl = "/smart-crm/follow-up";
            var followUpWindow = window.open(followUpUrl, "_blank");

            // Wait for the new window to load before interacting with its content
            followUpWindow.onload = function() {

                var followUpModal = $(followUpWindow.document.getElementById("followupmodal"));

                // Show the modal
                followUpModal.modal("show");


            };
        }
        $(document).on('shown.bs.modal', '.contact_modal', function(e) {
            $($(this).data('container')).modal('show');


            getUserLocation();
            initAutocomplete();
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            getReportdata();
            $(document).on('change', '.send_mail_checkbox', function() {
                if ($(this).is(":checked")) {
                    $('.send_mail_div').show();
                } else {
                    $('.send_mail_div').hide();
                }
            });

            function getReportdata() {
                // if ($("input#all_date_filter").val()) {
                //     start = $("input#all_date_filter")
                //         .data("daterangepicker")
                //         .startDate.format("YYYY-MM-DD");
                //     end = $("input#all_date_filter")
                //         .data("daterangepicker")
                //         .endDate.format("YYYY-MM-DD");
                // }

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/gettotals",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(data) {

                        //purchase details
                        //   $(".final").html(data.final, true);
                        //   $(".quotation").html(data.quotation, true);
                        $(".draft").html(data.draft.toLocaleString('en'), true);
                        $(".total_transactions").html(data.total_transactions.toLocaleString('en'),
                            true);
                        $(".total_sell").html("$" + __intToString(data.total_sell, true));
                    }
                });

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/getvalue",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(response) {
                        var tableSearch = $('#final_table');
                        var final_head = $('#final_head');
                        final_head.html('');
                        tableSearch.html('');

                        if (!jQuery.isEmptyObject(response)) {
                            final_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'Name' + "</th>" +
                                "<th>" + 'Amount' + "</th></tr>");
                            $.each(response, function(index, value) {
                                var no = index + 1;
                                // tableSearch.append("<p>"no_recent_transactions"</p>");
                                tableSearch.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value.final_total) +
                                    "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            tableSearch.append("<p class='text-center'>" + er +
                                "</p>");
                        }
                    }
                });


                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/quotation",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(data) {
                        var quotation_table = $('#quotation_table');
                        var er_msg = $('#error');
                        var quotation_head = $('#quotation_head');
                        quotation_head.html('');
                        quotation_table.html('');
                        er_msg.html('');

                        if (!jQuery.isEmptyObject(data)) {
                            quotation_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' +
                                "</th>" + "<th>" + 'Amount' + "</th></tr>");
                            $.each(data, function(index, value) {
                                var no = index + 1;
                                // quotation_table.append("<p>"no_recent_transactions"</p>");
                                quotation_table.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value
                                        .final_total) + "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            quotation_table.append("<p class='text-center'>" + er +
                                "</p>");
                        }
                    }
                });

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/draft",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(response) {
                        var draft_table = $('#draft_table');
                        var er_msg = $('#error');
                        var draft_head = $('#draft_head');

                        draft_table.html('');
                        er_msg.html('');
                        draft_head.html('');

                        if (!jQuery.isEmptyObject(response)) {
                            draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                                "<th>" + 'Amount' + "</th></tr>");
                            $.each(response, function(index, value) {
                                var no = index + 1;
                                // tableSearch.append("<p>"no_recent_transactions"</p>");
                                draft_table.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value
                                        .final_total) + "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            draft_table.append("<p class='text-center'>" + er +
                                "</p>");
                        }

                    },

                });

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/due_invoices",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(response) {
                        var draft_table = $('#due_table');
                        var er_msg = $('#error');
                        var draft_head = $('#due_head');

                        draft_table.html('');
                        er_msg.html('');
                        draft_head.html('');

                        if (!jQuery.isEmptyObject(response)) {
                            draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                                "<th>" + 'Amount' + "</th></tr>");
                            $.each(response, function(index, value) {
                                var no = index + 1;
                                // tableSearch.append("<p>"no_recent_transactions"</p>");
                                draft_table.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value
                                        .final_total) + "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            draft_table.append("<p class='text-center'>" + er +
                                "</p>");
                        }

                    },

                });

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/paid_invoices",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(response) {
                        var draft_table = $('#paid_table');
                        var er_msg = $('#error');
                        var draft_head = $('#paid_head');

                        draft_table.html('');
                        er_msg.html('');
                        draft_head.html('');

                        if (!jQuery.isEmptyObject(response)) {
                            draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                                "<th>" + 'Amount' + "</th></tr>");
                            $.each(response, function(index, value) {
                                var no = index + 1;
                                // tableSearch.append("<p>"no_recent_transactions"</p>");
                                draft_table.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value
                                        .final_total) + "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            draft_table.append("<p class='text-center'>" + er +
                                "</p>");
                        }

                    },

                });

                $.ajax({
                    method: "get",
                    url: "/CashierDashboard/partial_invoices",
                    dataType: "json",
                    // data: {
                    //     date_from: start,
                    //     date_to: end,
                    // },
                    success: function(response) {
                        var draft_table = $('#partial_table');
                        var er_msg = $('#error');
                        var draft_head = $('#partial_head');

                        draft_table.html('');
                        er_msg.html('');
                        draft_head.html('');

                        if (!jQuery.isEmptyObject(response)) {
                            draft_head.append("<tr><th>" + 'No' + "</th>" + "<th>" + 'name' + "</th>" +
                                "<th>" + 'Amount' + "</th></tr>");
                            $.each(response, function(index, value) {
                                var no = index + 1;
                                // tableSearch.append("<p>"no_recent_transactions"</p>");
                                draft_table.append("<tr><td style='width: 40px;'>" + no +
                                    "</td>" + "<td>" + value.invoice_no + "(" + value.name +
                                    ")" + "</td><td class='display_currency'>" +
                                    __currency_trans_from_en(value
                                        .final_total) + "</td></tr>");

                            });
                        } else {
                            var er = 'No Recent Transactions';
                            draft_table.append("<p class='text-center'>" + er +
                                "</p>");
                        }

                    },

                });
                var currentDate = new Date();
                var currentYear = currentDate.getFullYear();
                var currentMonth = currentDate.getMonth();

                // Calculate start date as January 1st of the current year
                var startDate = new Date(currentYear, 0, 1);

                // Calculate end date as the last day of the current month
                var endDate = new Date(currentYear, currentMonth + 1, 0);

                var options = {
                    series: [],
                    chart: {
                        height: 350,
                        type: 'area'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    xaxis: {
                        type: 'datetime',
                        categories: [],
                        min: startDate.getTime(),
                        max: endDate.getTime(),
                    },
                    tooltip: {
                        x: {
                            format: 'dd/MM/yy'
                        },
                    },
                    title: {
                        // text: "(Sell = Payable Amount)",
                        rotate: -90,
                        offsetX: 500,
                        offsetY: 0,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            cssClass: 'apexcharts-yaxis-title',
                        },
                    },
                    noData: {
                        text: "Loading...",
                    },
                };

                $("#chart1", function() {
                    var url = "/CashierDashboard/total-sales";
                    axios({
                        method: "GET",
                        url: url,
                    }).then(function(response) {
                        chart.updateOptions({
                            series: [{
                                name: "Total Sell",
                                data: response.data[0],
                            }, ],
                            xaxis: {
                                type: "datetime",
                                categories: response.data[1],
                            },
                            yaxis: [{
                                labels: {
                                    formatter: function(val) {
                                        return (val).toLocaleString();
                                    },
                                },
                            }, ],
                        });
                    });
                });
                var chart = new ApexCharts(document.querySelector("#chart1"), options);
                chart.render();

            }
        });

        function __number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function __intToString(num) {
            num = num.toString().replace(/[^0-9.]/g, "");
            if (num < 1000) {
                return num;
            }
            let si = [{
                    v: 1e3,
                    s: "K"
                },
                {
                    v: 1e6,
                    s: "M"
                },
                {
                    v: 1e9,
                    s: "B"
                },
                {
                    v: 1e12,
                    s: "T"
                },
                {
                    v: 1e15,
                    s: "P"
                },
                {
                    v: 1e18,
                    s: "E"
                },
            ];
            let index;
            for (index = si.length - 1; index > 0; index--) {
                if (num >= si[index].v) {
                    break;
                }
            }
            return (
                (num / si[index].v).toFixed(2).replace(/\.0+$|(\.[0-9]*[1-9])0+$/, "$1") +
                si[index].s
            );
        }
        $(document).ready(function() {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' ~ ' + picker.endDate.format(
                    'MM/DD/YYYY'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            loadDateFillter();
        });


        function setDateAttributesInFillters(data) {
            sertDateRange(data);
            setFilterDataForHeatMap(data);
            reloadBarChart();
            reloadHeatMap();
            reloadLeaderboard();
        }
        let heatmapChart = null;

        function reloadHeatMap() {
            let sendData = {};

            const weekRange = $('#heatmap_week_select').val();
            if (weekRange) {
                const [date_from, date_to] = weekRange.split('|');
                sendData = {
                    date_from,
                    date_to
                };
            } else {
                const date_from = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                sendData = {
                    date_from
                };
            }

            const url = '/smart-crm/fetch-heatmap-data';

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart('sales_rep_heatmap');
            if (existingChart) {
                existingChart.destroy();
            }

            getChartDataBasedOnChart('', '', sendData, url, function(err, result) {
                if (!err) {
                    const {
                        reps,
                        days,
                        matrix_data: heatmapData
                    } = result;

                    const ctx2 = document.getElementById('sales_rep_heatmap').getContext('2d');

                    new Chart(ctx2, {
                        type: 'matrix',
                        data: {
                            datasets: [{
                                label: 'Sales Rep Visits',
                                data: heatmapData,
                                backgroundColor(ctx) {
                                    const value = ctx.raw?.v ?? 0;
                                    const alpha = Math.min(1, value / 10);
                                    return `rgba(0, 123, 255, ${alpha})`;
                                },
                                width: (ctx) => {
                                    const chart = ctx.chart;
                                    return chart.chartArea ? chart.chartArea.width / days
                                        .length - 5 : 40;
                                },
                                height: (ctx) => {
                                    const chart = ctx.chart;
                                    return chart.chartArea ? chart.chartArea.height / reps
                                        .length - 5 : 40;
                                }
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    type: 'linear',
                                    min: -0.5,
                                    max: days.length - 0.5,
                                    ticks: {
                                        callback: (value) => days[value],
                                        autoSkip: false
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    min: -0.5,
                                    max: reps.length - 0.5,
                                    ticks: {
                                        callback: (value) => reps[value],
                                        autoSkip: false
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        title: (items) =>
                                            `${reps[items[0].raw.y]} - ${days[items[0].raw.x]}`,
                                        label: (item) => `Visits: ${item.raw.v}`
                                    }
                                },
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
            });
        }

        function randomColor() {
            const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#1f9bcf'];
            return colors[Math.floor(Math.random() * colors.length)];
        }

        function reloadLeaderboard() {
            var date_from = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var date_to = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            var sendData = {
                date_from: date_from,
                date_to: date_to,
            };

            var url = '/smart-crm/fetch-leaderboard-data';

            getChartDataBasedOnChart(date_from, date_to, sendData, url, function(err, result) {
                if (!err && result.success) {
                    const leaderboardData = result.data; // new grouped structure

                    let html = '';

                    for (const rep in leaderboardData) {
                        html += `<h4 style="margin-top:1em;">${rep}</h4>`;
                        html += `<table style="width:100%;border-collapse:collapse;margin-bottom:1em;">
                    <thead>
                        <tr style="background:#f2f2f2;">
                            <th style="text-align:left;padding:8px;">Client</th>
                            <th style="text-align:right;padding:8px;">Total Orders</th>
                            <th style="text-align:right;padding:8px;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>`;

                        leaderboardData[rep].forEach(client => {
                            html += `<tr>
                        <td style="padding:8px;border-bottom:1px solid #ddd;">${client.client}</td>
                        <td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">${client.total_orders}</td>
                        <td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">${parseFloat(client.total_amount).toFixed(2)}</td>
                    </tr>`;
                        });

                        html += `</tbody></table>`;
                    }

                    document.getElementById('top_clients_leaderboard').innerHTML = html;
                } else {
                    console.error('Failed to load leaderboard:', err);
                }
            });
        }
        $(document).on('click', '#filtterData', function() {
            reloadBarChart();
            reloadLeaderboard();
        });

        $(document).on('click', '#filterHeatMapData', function() {
            reloadHeatMap();
        });

        function reloadBarChart() {
            document.getElementById('ordersBySalesRepChart').innerHTML = '';
            var date_from = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var date_to = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            var sendData = {
                date_from: date_from,
                date_to: date_to,
            }
            var url = '/smart-crm/fetch-bar-chart-data';
            let data = getChartDataBasedOnChart(date_from, date_to, sendData, url, function(err, result) {
                console.log(result);
                console.log(err);
                if (!err && result.success) {
                    const responseData = result.data; // This should be the salesperson array
                    const labels = Object.keys(responseData);
                    const dataPoints = Object.values(responseData);

                    // Optional: Generate colors dynamically
                    const backgroundColors = labels.map(() => randomColor());
                    const borderColors = backgroundColors;

                    // Update Chart
                    ordersBySalesRepChart.data.labels = labels;
                    ordersBySalesRepChart.data.datasets[0].data = dataPoints;
                    ordersBySalesRepChart.data.datasets[0].backgroundColor = backgroundColors;
                    ordersBySalesRepChart.data.datasets[0].borderColor = borderColors;

                    ordersBySalesRepChart.update();
                }
            })

        }

        function getChartDataBasedOnChart(date_from, date_to, data, url, callback) {
            $.ajax({
                method: "get",
                url: url,
                dataType: "json",
                data: data,
                success: function(data) {
                    callback(null, data);
                },
                error: function(data) {
                    alert('something went wrong');
                    callback(err);
                }
            });
        }

        function sertDateRange(data) {
            if (data.date_from && data.date_to) {
                $('#date_range').data('daterangepicker').setStartDate(moment(data.date_from));
                $('#date_range').data('daterangepicker').setEndDate(moment(data.date_to));

                $('#date_range').val(
                    `${moment(data.date_from).format('YYYY-MM-DD')} ~ ${moment(data.date_to).format('YYYY-MM-DD')}`);
            }
        }

        function setFilterDataForHeatMap(data) {
            if (!data.date_from) {
                console.error('date_from not found');
                return;
            }

            // Calculate year, month, and week from date_from
            let dateRef = moment(data.date_from);

            let year = dateRef.year();
            let month = dateRef.month() + 1; // month is 0-indexed
            let startOfWeek = dateRef.clone().startOf('isoWeek'); // Monday
            let endOfWeek = dateRef.clone().endOf('isoWeek'); // Sunday

            let weekValue = `${startOfWeek.format('YYYY-MM-DD')}|${endOfWeek.format('YYYY-MM-DD')}`;

            // 1. Set Year
            $('#heatmap_year_select').val(year).trigger('change');

            // Wait a bit so month dropdown fills (because change event is async)
            setTimeout(function() {
                // 2. Set Month
                $('#heatmap_month_select').val(month).trigger('change');

                // Again, wait for week dropdown to fill
                setTimeout(function() {
                    // 3. Set Week
                    $('#heatmap_week_select').val(weekValue).trigger('change');
                }, 300);
            }, 300);
        }

        function getRowData(date_from, date_to, callback) {
            $.ajax({
                method: "get",
                url: "/smart-crm/fetch-chart-data",
                dataType: "json",
                data: {
                    date_from: date_from,
                    date_to: date_to,
                },
                success: function(data) {
                    callback(null, data.data);
                },
                error: function(data) {
                    alert('something went wrong');
                    callback(err);
                }
            });
        }

        function loadDateFillter() {
            var dateRangePicker = $('#date_range').data('daterangepicker');

            if (!dateRangePicker || !dateRangePicker.startDate || !dateRangePicker.endDate) {
                let today = moment();
                $('#date_range').data('daterangepicker').setStartDate(today);
                $('#date_range').data('daterangepicker').setEndDate(today);
            }

            var date_from = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var date_to = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');

            let data = getRowData(date_from, date_to, function(err, result) {
                if (err) {
                    console.error(err);
                    return;
                }
                setDateAttributesInFillters(result);
            });

        }
    </script>
@endsection
