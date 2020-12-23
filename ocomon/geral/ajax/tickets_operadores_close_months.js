/* $(document).ready(function () {
    showTotalGraph_06();
}); */

function tickets_operadores_close_months(canvasId) {
    $.ajax({
        url: "../geral/tickets_operadores_close_months.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        // console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var months = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var operadores = []; //inner labels - Legends
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data.months) {
            months.push(data.months[i]);
        }
        // console.log('Meses: ' + months);

        for (var j in data.operadores) {
            operadores.push(data.operadores[j]);
        }
        // console.log('operadores: ' + operadores);

        for (var l in data.totais) {
            total.push(data.totais[l]);
        }

        chartTitle.push(data.chart_title);
        // console.log('Total: ' + total);
        
        var dataSetValue = [];
        var count = operadores.length; /* Quantidade de operadores recebidas */

        for (var k = 0; k < count; k++) {
            dataSetValue[k] = {
                label: operadores[k],
                fill: false,
                // data: [Math.round(Math.random() * 10), Math.round(Math.random() * 10), Math.round(Math.random() * 10)]
                data: total[k]
            };
        }

        var chartdata = {
            labels: months,
            datasets : dataSetValue
        };

        var options = {
            responsive: true,
            title: {
                display: true,
                // text: "Chamados fechados X operadores nos últimos meses",
                text: chartTitle,
            },
            legend: {
                display: true,
                position: "top",
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
                    stacked: true,
                    display: true,
                },
                ],
                yAxes: [
                {
                    stacked: true,
                    ticks: {
                    beginAtZero: true,
                    },
                },
                ],
            },
        };

        //This is the div ID (within the HTML content) where you want to display the chart
        // var graphTarget = $("#dashboard-06");
        var graphTarget = $('#' + canvasId);
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