/* $(document).ready(function () {
    showTotalGraph_06();
}); */

function tickets_open_close_months(canvasId) {
    $.ajax({
        url: "../geral/tickets_open_close_months.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var months = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var tipo = []; //inner labels - Legends
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data.months) {
            months.push(data.months[i]);
        }
        // console.log('Meses: ' + months);

        tipo.push('abertos');
        tipo.push('fechados');

        for (var j in data.totais) {
            total.push(data.totais[j]);
        }
        // console.log('Areas: ' + areas);

        chartTitle.push(data.chart_title);
        
        // console.log('Total: ' + total);
        
        var dataSetValue = [];
        var count = tipo.length; /* Quantidade de tipos recebidos */

        /* Dados */
        for (var k = 0; k < count; k++) {
            // dataSetValueAbertos[k] = {
            dataSetValue[k] = {
                label: tipo[k],
                fill: false,
                data: total[k],
            };
        }

        // var chartdata = {
        //     labels: months,
        //     datasets : dataSetValueAbertos
        // };
        var chartdata = {
            labels: months,
            datasets : dataSetValue
        };

        var options = {
            responsive: true,
            title: {
                display: true,
                // text: "Chamados nos últimos meses",
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
                    display: true,
                    stacked: true,
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


