/* $(document).ready(function () {
    showTotalGraph_03();
}); */


function tickets_x_area_curr_month(canvasId) {
    $.ajax({
        url: "../geral/tickets_x_area_curr_month.php",
        method: "POST",
        // data: {
        //     'cod': cod
        // },
        dataType: "json",
    })
    .done(function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var areasVar = []; // X Axis Label
        var abertosVar = []; // Value and Y Axis basis
        var fechadosVar = []; // Value and Y Axis basis
        var canceladosVar = []; // Value and Y Axis basis
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data) {

            if (data[i].area !== undefined) {
                areasVar.push(data[i].area);
            }
            if (data[i].abertos !== undefined) {
                abertosVar.push(data[i].abertos);
            }
            if (data[i].fechados !== undefined) {
                fechadosVar.push(data[i].fechados);
            }
            if (data[i].cancelados !== undefined) {
                canceladosVar.push(data[i].cancelados);
            }
            if (data[i].chart_title !== undefined) {
                chartTitle.push(data[i].chart_title);
            }
        }

        var options = {
            responsive: true,
            title: {
                display: true,
                // text: "Quadro Geral do Mês Atual",
                text: chartTitle[0],
            },
            legend: {
                display: true,
                position: "left",
                align: "start",
            },
            plugins: {
              colorschemes: {
                scheme: 'tableau.Tableau20'
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

        var chartdata = {
            labels: areasVar,
            datasets: [
              {
                label: "Abertos",
                data: abertosVar,
              },
              {
                label: "Fechados",
                data: fechadosVar,
              },
              // {
              //   label: "Cancelados",
              //   data: canceladosVar,
              // },
            ],
          };

        //This is the div ID (within the HTML content) where you want to display the chart
        // var graphTarget = $("#dashboard-03");
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

