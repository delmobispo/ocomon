<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <script src="../../includes/components/jquery/jquery.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/Chart.min.js"></script>

	<style>
		canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
		.chart-container {
			width: 45%;
			margin-left: 10px;
			margin-right: 10px;
		}
		.container {
			display: flex;
			flex-direction: row;
			flex-wrap: wrap;
			justify-content: center;
		}
	</style>

</head>
<body>
    
    <div class="container">
        <!-- this DIV will display the chart canvas -->
        
        
        <div class="chart-container">
            <canvas id="bar-chartcanvas"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="pie-chartcanvas"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="line-chartcanvas"></canvas>
        </div>
        
        <div class="chart-container">
            <canvas id="fifth-chart"></canvas>
        </div>
    </div>


    <!-- <script src="ajax/user_x_level_bar.js"></script> -->
    <script src="ajax/area_x_problemas.js"></script>
</body>
</html>
