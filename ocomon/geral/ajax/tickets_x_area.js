/* $(document).ready(function () {
    showTotalGraph_01();
}); */


function tickets_x_area(canvasId) {
    $.ajax({
        url: "../geral/tickets_x_area.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data) {
            // formStatusVar.push(data[i].area);
            // total.push(data[i].quantidade);

            if (data[i].area !== undefined) {
                formStatusVar.push(data[i].area);
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
                // text: "Chamados x Área de Atendimento",
                text: chartTitle[0],
            },
            legend: {
                display: false,
                position: "left",
                align: "start",
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        try {
                            let label = ' ' + data.labels[tooltipItem.index] || '';

                            if (label) {
                                label += ': ';
                            }

                            const sum = data.datasets[0].data.reduce((accumulator, curValue) => {
                                return parseInt(accumulator) + parseInt(curValue); 
                            });
                            const value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

                            label += Number((value / sum) * 100).toFixed(2) + '%';
                            return label;
                        } catch (error) {
                            console.log(error);
                        }
                    }
                }
            },
            plugins: {
                colorschemes: {
                    scheme: 'brewer.Paired12'
                    // scheme: 'brewer.ocoMon01'
                }
            },
            datalabels: {
                display: function(context) {
                    return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                },
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
        // var graphTarget = $("#dashboard-01");
        var graphTarget = $("#" + canvasId);
        var barGraph = new Chart(graphTarget, {
            type: "bar",
            data: chartdata,
            options: options,
        });
    })
    .fail(function () {
        // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
    });
    
    return false;
}

