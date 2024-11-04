<script>
    // Reemplazar las variables con las correspondientes a los ingresos
    $datosX_income = crearArreglo('<?php echo $datosX_income ?>');
    $datosY_income = crearArreglo('<?php echo $datosY_income ?>');

    var trace1 = {
        x: $datosX_income,
        y: $datosY_income,
        type: 'scatter'
    };

    var data = [trace1];

    Plotly.newPlot('grafica3', data);
</script>