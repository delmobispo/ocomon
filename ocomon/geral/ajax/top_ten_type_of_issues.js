/* $(document).ready(function () {
    showTotalGraph_05();
}); */


function top_ten_type_of_issues(canvasId) {
    $.ajax({
        url: "../geral/top_ten_type_of_issues.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var problemasVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data) {

            if (data[i].problema !== undefined) {
                problemasVar.push(data[i].problema);
            }
            if (data[i].total !== undefined) {
                total.push(data[i].total);
            }
            if (data[i].chart_title !== undefined) {
                chartTitle.push(data[i].chart_title);
            }
        }


        var options = {
            responsive: true,
            title: {
                display: true,
                // text: "Top-10 tipos de Problemas",
                text: chartTitle[0],
            },
            legend: {
                display: false,
                position: "left",
                align: "start",
            },
            plugins: {
                colorschemes: {
                    scheme: 'brewer.Paired12'
                    // scheme: 'brewer.ocoMon01'
                },
                datalabels: {
                    display: function(context) {
                        return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                    },
                    // color: '#36A2EB',
                    // font: {
                    //     size: "20"
                    // }
                }
            },
            scales: {
                xAxes: [
                {
                    display: true,
                },
                ],
                yAxes: [
                {
                    ticks: {
                    beginAtZero: true,
                    },
                },
                ],
            },
        };

        var chartdata = {
            labels: problemasVar,
            datasets: [
                {
                    label: "Total",
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.8)",
                        "rgba(54, 162, 235, 0.8)",
                        "rgba(255, 206, 86, 0.8)",
                        "rgba(75, 192, 192, 0.8)",
                        "rgba(153, 102, 255, 0.8)",
                        "rgba(255, 159, 64, 0.8)",
                    ],
                    borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                    ],
                    hoverBackgroundColor: "#CCCCCC",
                    hoverBorderColor: "#666666",
                    data: total,
                },
            ],
        };

        //This is the div ID (within the HTML content) where you want to display the chart
        // var graphTarget = $("#dashboard-05");
        var graphTarget = $("#" + canvasId);
        var barGraph = new Chart(graphTarget, {
            type: "horizontalBar",
            data: chartdata,
            options: options,
        });
    })
    .fail(function () {
        // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
    });
    
    return false;
}

