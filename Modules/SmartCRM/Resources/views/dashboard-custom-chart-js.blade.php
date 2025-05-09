<script>
    let ordersBySalesRepChart;
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. New Orders by Sales Rep Chart ---
        const ctx1 = document.getElementById('ordersBySalesRepChart').getContext('2d');

        ordersBySalesRepChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: [], // Empty initially
                datasets: [{
                    label: 'New Orders ($)',
                    data: [],
                    backgroundColor: [],
                    borderColor: [],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Order Value ($)'
                        }
                    }
                }
            }
        });

        // --- 2. Sales Rep Heatmap Chart ---
        // const ctx2 = document.getElementById('sales_rep_heatmap').getContext('2d');
        // const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        // const reps = ['John', 'Alice', 'Sam', 'Emma', 'Chris'];

        // const heatmapData = [];
        // for (let y = 0; y < reps.length; y++) {
        //     for (let x = 0; x < days.length; x++) {
        //         heatmapData.push({
        //             x: x,
        //             y: y,
        //             v: Math.floor(Math.random() * 10) // Random visit count
        //         });
        //     }
        // }

        // new Chart(ctx2, {
        //     type: 'matrix',
        //     data: {
        //         datasets: [{
        //             label: 'Sales Rep Visits',
        //             data: heatmapData,
        //             backgroundColor(ctx) {
        //                 const value = ctx.dataset.data[ctx.dataIndex].v;
        //                 const alpha = Math.min(1, value / 10);
        //                 return `rgba(0, 123, 255, ${alpha})`;
        //             },
        //             width: (ctx) => {
        //                 const chart = ctx.chart;
        //                 if (!chart.chartArea) {
        //                     return 40;
        //                 }
        //                 return chart.chartArea.width / days.length - 5;
        //             },
        //             height: (ctx) => {
        //                 const chart = ctx.chart;
        //                 if (!chart.chartArea) {
        //                     return 40;
        //                 }
        //                 return chart.chartArea.height / reps.length - 5;
        //             }
        //         }]
        //     },
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         scales: {
        //             x: {
        //                 type: 'linear',
        //                 min: -0.5,
        //                 max: days.length - 0.5,
        //                 ticks: {
        //                     callback: (value) => days[value],
        //                     autoSkip: false
        //                 },
        //                 grid: {
        //                     display: false
        //                 }
        //             },
        //             y: {
        //                 type: 'linear',
        //                 min: -0.5,
        //                 max: reps.length - 0.5,
        //                 ticks: {
        //                     callback: (value) => reps[value],
        //                     autoSkip: false
        //                 },
        //                 grid: {
        //                     display: false
        //                 }
        //             }
        //         },
        //         plugins: {
        //             tooltip: {
        //                 callbacks: {
        //                     title: (items) => `${reps[items[0].raw.y]} - ${days[items[0].raw.x]}`,
        //                     label: (item) => `Visits: ${item.raw.v}`
        //                 }
        //             },
        //             legend: {
        //                 display: false
        //             }
        //         }
        //     }
        // });

        // --- 3. Top Clients Leaderboard ---
        const leaderboardData = [{
                rep: 'John Doe',
                client: 'Store A',
                total_orders: 120
            },
            {
                rep: 'John Doe',
                client: 'Store B',
                total_orders: 90
            },
            {
                rep: 'Jane Smith',
                client: 'Distributor X',
                total_orders: 150
            },
            {
                rep: 'Jane Smith',
                client: 'Distributor Y',
                total_orders: 80
            },
            {
                rep: 'Alan Walker',
                client: 'Store C',
                total_orders: 70
            }
        ];

        // Group data by Rep
        const groupedData = {};
        leaderboardData.forEach(item => {
            if (!groupedData[item.rep]) {
                groupedData[item.rep] = [];
            }
            groupedData[item.rep].push(item);
        });

        // Build HTML
        let html = '';
        for (const rep in groupedData) {
            html += `<h4 style="margin-top:1em;">${rep}</h4>`;
            html += `<table style="width:100%;border-collapse:collapse;margin-bottom:1em;">
                <thead>
                    <tr style="background:#f2f2f2;">
                        <th style="text-align:left;padding:8px;">Client</th>
                        <th style="text-align:right;padding:8px;">Total Orders</th>
                    </tr>
                </thead>
                <tbody>`;
            groupedData[rep].forEach(client => {
                html += `<tr>
                    <td style="padding:8px;border-bottom:1px solid #ddd;">${client.client}</td>
                    <td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">${client.total_orders}</td>
                 </tr>`;
            });
            html += `</tbody></table>`;
        }

        // Insert leaderboard HTML into the div
        document.getElementById('top_clients_leaderboard').innerHTML = html;
    });
</script>
