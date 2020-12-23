// $(document).ready(function () {
//     showTotalGraph();
// });

function showTotalGraph(cod) {
    $.ajax({
        url: "../geral/user_x_level.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        // console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data) {
            // formStatusVar.push(data[i].area);
            // total.push(data[i].quantidade);

            if (data[i].nivel !== undefined) {
                formStatusVar.push(data[i].nivel);
            }
            if (data[i].quantidade !== undefined) {
                total.push(data[i].quantidade);
            }
            if (data[i].chart_title !== undefined) {
                chartTitle.push(data[i].chart_title);
            }
        }

        var options = {
            responsive: true,
            title: {
                display: true,
                text: chartTitle[0]
            },
            legend: {
                display: false,
                position: "left",
                align: "start",
            },
            scales: {
                xAxes: [
                {
                    display: false,
                },
                ],
                yAxes: [
                {
                    display: false,
                    ticks: {
                    beginAtZero: true,
                    },
                },
                ],
            },
            
        };

        var chartdata = {
            labels: formStatusVar,
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
        var graphTarget = $("#canvasChart1");
        var barGraph = new Chart(graphTarget, {
            type: "doughnut",
            data: chartdata,
            options: options,
        });
    })
    .fail(function () {
        // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
    });
    
    return false;
}
