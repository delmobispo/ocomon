$(document).ready(function () {
    showTotalGraph_04();
});


function showTotalGraph_04(cod) {
    $.ajax({
        url: "../geral/areas_x_problemas_curr_month.php",
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
        var problemasVar = []; // Value and Y Axis basis
        var total = []; 

        //console.log(data.length);

        for (var i in data) {
            areasVar.push(data[i].area);
            
            problemasVar.push(data[i].problema);
            total.push(data[i].quantidade);
        }

        var options = {
            responsive: true,
            title: {
                display: true,
                text: "Chamados Fechados: Áreas x Tipos de Solicitações",
            },
            legend: {
                display: false,
                position: "left",
                align: "start",
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
            labels: areasVar,
            datasets: [
              {
                label: "Áreas",
                
                
                borderColor: [
                  "rgba(255, 99, 132, 1)",
                  "rgba(54, 162, 235, 1)",
                  "rgba(255, 206, 86, 1)",
                  "rgba(75, 192, 192, 1)",
                  "rgba(153, 102, 255, 1)",
                  "rgba(255, 159, 64, 1)",
                ],
                backgroundColor: "rgba(255, 255, 86, 0.8)",
                hoverBackgroundColor: "#CCCCCC",
                hoverBorderColor: "#666666",
                data: areasVar,
              },
              {
                label: "Problemas",
                
                borderColor: [
                  "rgba(55, 99, 132, 1)",
                  "rgba(154, 162, 235, 1)",
                  "rgba(55, 206, 86, 1)",
                  "rgba(200, 192, 192, 1)",
                  "rgba(253, 102, 255, 1)",
                  "rgba(55, 159, 64, 1)",
                ],
                backgroundColor: "rgba(155, 255, 86, 0.8)",
                hoverBackgroundColor: "#CCCCCC",
                hoverBorderColor: "#666666",
                data: problemasVar,
  
              },
              {
                label: "Quantidade",
                borderColor: [
                  "rgba(155, 99, 132, 1)",
                  "rgba(254, 162, 235, 1)",
                  "rgba(155, 206, 86, 1)",
                  "rgba(175, 192, 192, 1)",
                  "rgba(53, 102, 255, 1)",
                  "rgba(155, 159, 64, 1)",
                ],
                backgroundColor: "rgba(75, 255, 192, 0.8)",
                hoverBackgroundColor: "#CCCCCC",
                hoverBorderColor: "#666666",
                data: total,
              },
            ],
          };
  

        //This is the div ID (within the HTML content) where you want to display the chart
        var graphTarget = $("#dashboard-04");
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

