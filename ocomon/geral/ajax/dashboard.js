$(document).ready(function () {
  showTotalGraph();
});

function showTotalGraph() {
  {
    // This is the database.php file we created earlier, its JSON output will be processed in this function
    $.post(
      "../geral/tickets_x_area.php",
      // {area: 1},
      function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis

        //console.log(data.length);

        for (var i in data) {
          // formStatus is taken from JSON output (see above)
          formStatusVar.push(data[i].area);
          total.push(data[i].quantidade);
        }

        var options = {
          title: {
              display: true,
              text: "Chamados x Área de Atendimento",
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
        var graphTarget = $("#bar-chartcanvas");
        var barGraph = new Chart(graphTarget, {
          type: "bar",
          data: chartdata,
          options: options,
        });
      },
      "json"
    ),

    $.post(
      "../geral/tickets_x_status.php",
      function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis

        //console.log(data.length);

        for (var i in data) {
          // formStatus is taken from JSON output (see above)
          formStatusVar.push(data[i].status);
          total.push(data[i].quantidade);
        }

        var options = {
          title: {
              display: true,
              text: "Ocorrências x Status",
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
        var graphTarget = $("#pie-chartcanvas");
        var barGraph = new Chart(graphTarget, {
          type: "pie",
          data: chartdata,
          options: options,
        });
      },
      "json"
    ),

    $.post(
      "../geral/tickets_x_area_curr_month.php",
      function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var areasVar = []; // X Axis Label
        var abertosVar = []; // Value and Y Axis basis
        var fechadosVar = []; // Value and Y Axis basis
        var canceladosVar = []; // Value and Y Axis basis

        // console.log(data.length);

        for (var i in data) {
          areasVar.push(data[i].area);
          abertosVar.push(data[i].abertos);
          fechadosVar.push(data[i].fechados);
          canceladosVar.push(data[i].cancelados);
        }
        // for (var i in data) {
        //   areasVar.push(data.area);
        //   abertosVar.push(data.abertos);
        //   fechadosVar.push(data.fechados);
        //   canceladosVar.push(data.cancelados);
        // }

        console.log(areasVar);

        var options = {
          title: {
              display: true,
              text: "Quadro Geral do Mês Atual",
          },
          legend: {
            display: true,
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
              label: "Abertos",
              
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
              data: abertosVar,
            },
            {
              label: "Fechados",
              
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
              data: fechadosVar,
            },
            {
              label: "Cancelados",
              
              borderColor: [
                "rgba(55, 99, 132, 1)",
                "rgba(154, 162, 235, 1)",
                "rgba(55, 206, 86, 1)",
                "rgba(25, 192, 192, 1)",
                "rgba(253, 102, 255, 1)",
                "rgba(55, 159, 64, 1)",
              ],
              backgroundColor: "rgba(255, 55, 192, 0.8)",
              hoverBackgroundColor: "#CCCCCC",
              hoverBorderColor: "#666666",
              data: canceladosVar,
            },
          ],
        };

        //This is the div ID (within the HTML content) where you want to display the chart
        var graphTarget = $("#line-chartcanvas");
        var barGraph = new Chart(graphTarget, {
          type: "bar",
          data: chartdata,
          options: options,
        });
      },
      "json"
    ),

    $.post(
      "../geral/areas_x_problemas_curr_month.php",
      function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var areasVar = []; // X Axis Label
        var problemasVar = []; // Value and Y Axis basis
        var total = []; 

        var tmp; 

        // console.log(data.length);

        for (var i in data) {
          
          if (!areasVar.includes(data[i].area)) {
            areasVar.push(data[i].area);
            problemasVar.push(data[i].problema);
            total.push(data[i].quantidade);
          } else {
            
          }
          
          // problemasVar.push(data[i].problema);
          
        }


        var options = {
          title: {
              display: true,
              text: "Chamados Fechados: Áreas x Tipos de Solicitações",
          },
          legend: {
            display: true,
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
        var graphTarget = $("#fifth-chart");
        var barGraph = new Chart(graphTarget, {
          type: "bar",
          data: chartdata,
          options: options,
        });
      },
      "json"
    );


  }
}
