$(document).ready(function () {
  showTotalGraph();
});

function showTotalGraph() {
  {
    // This is the database.php file we created earlier, its JSON output will be processed in this function
    $.post(
      "../geral/user_x_level.php",
      function (data) {
        console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis

        //console.log(data.length);

        for (var i in data) {
          // formStatus is taken from JSON output (see above)
          formStatusVar.push(data[i].nivel);
          total.push(data[i].quantidade);
        }

        var options = {
          title: {
              display: true,
              text: "Nível dos Usuários Cadastrados",
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
    );
  }
}
