<?php

session_start();
include '../db/conexion.php';

// Obtener los datos de sesión
$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nacimiento = $_SESSION['nacimiento'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';
$email = $_SESSION['email'] ?? '';

// Formatear fecha y nombre completo
$nombre_completo = $nombre . ' ' . $apellido; 
$date = date("Y-m"); 
$date = date("Y-m-d", strtotime($date));

// Inicializar variables
$tamaño_grap_2 = 0;
$tamaño_cat = 0;
$tamaño_porce = 0;
$value_cat = [];
$cat_name = [];

/* Grafica de pastel - gráfica 1 */

// Consulta para obtener los valores del plan
$query_category_value = mysqli_query($conexion, "SELECT * FROM plan WHERE id_person = '$usuario' AND month_plan = '$date'");

$cant = 0;
while ($consulta_grap = mysqli_fetch_array($query_category_value)) {
    $cat_id = $consulta_grap['id_category'];
    $value_cat[$cant] = $consulta_grap['value_plan'];

    // Consulta para obtener los nombres de las categorías
    $query_category = mysqli_query($conexion, "SELECT * FROM category_user WHERE id_person = '$usuario' AND id_category = '$cat_id'");
    while ($name_cat = mysqli_fetch_array($query_category)) {
        $cat_name[$cant] = $name_cat['category_name'];
    }

    // Verificación: Si no se encuentra un nombre de categoría, asignar un valor predeterminado
    if (!isset($cat_name[$cant])) {
        $cat_name[$cant] = 'Categoría desconocida';
    }

    $cant++;
}

// Verificar que $value_cat no esté vacío antes de usar array_sum()
if (!empty($value_cat)) {
    $suma_category = array_sum($value_cat);
    $tamaño = count($value_cat);

    // Calcular porcentajes
    $porcetajes = [];
    for ($i = 0; $i < $tamaño; $i++) { 
        $porcetajes[$i] = round(($value_cat[$i] * 100) / $suma_category, 0);
    }

    $datoslabel = json_encode($cat_name);
    $datosvalor = json_encode($porcetajes);

    $tamaño_cat = count($cat_name);
    $tamaño_porce = count($porcetajes);
} else {
    // Manejar el caso en que $value_cat esté vacío
    $datoslabel = json_encode([]);
    $datosvalor = json_encode([]);
    $tamaño_cat = 0;
    $tamaño_porce = 0;
}
    
/* Grafica de lineas - gráfica 2 */

$query_graf_2 = mysqli_query($conexion, "SELECT * FROM expenses WHERE id_person = '$usuario'");

$cant2 = 0;
$array_g2_d_e = []; // Definir como un array vacío
$array_g2_v_e = []; // Definir como un array vacío
$cat_name_2 = []; // Definir como un array vacío

while($consulta_grap_2 = mysqli_fetch_array($query_graf_2)){
    $array_g2_d_e[$cant2] = $consulta_grap_2['date_expenses'];
    $array_g2_v_e[$cant2] = $consulta_grap_2['value_expenses'];
    $array_g2_id_c_e = $consulta_grap_2['id_category'];

    $query_category_2 = mysqli_query($conexion, "SELECT * FROM category_user WHERE id_person = '$usuario' AND id_category = '$array_g2_id_c_e'");
    while($name_cat_2 = mysqli_fetch_array($query_category_2)){
        $cat_name_2[$cant2] = $name_cat_2['category_name'];
    }

    // Verificación: Si no se encuentra un nombre de categoría, asignar un valor predeterminado
    if (!isset($cat_name_2[$cant2])) {
        $cat_name_2[$cant2] = 'Categoría desconocida';
    }

    $cant2++;
}

$tamaño_grap_2 = count($array_g2_d_e);

$datosX = json_encode($cat_name_2);
$datosY = json_encode($array_g2_v_e);

/* Grafica de lineas - gráfica 3 */

$query_graf_income = mysqli_query($conexion, "SELECT * FROM income WHERE id_person = '$usuario'");

$cant_income = 0;
$array_income_date = []; // Definir como un array vacío
$array_income_value = []; // Definir como un array vacío
$cat_name_income = []; // Definir como un array vacío

while ($consulta_income = mysqli_fetch_array($query_graf_income)) {
    $array_income_date[$cant_income] = $consulta_income['date_income'];
    $array_income_value[$cant_income] = $consulta_income['value_income'];
    $array_income_id_category = $consulta_income['id_category'];

    $query_category_income = mysqli_query($conexion, "SELECT * FROM category_user WHERE id_person = '$usuario' AND id_category = '$array_income_id_category'");
    while ($name_cat_income = mysqli_fetch_array($query_category_income)) {
        $cat_name_income[$cant_income] = $name_cat_income['category_name'];
    }

    // Verificación: Si no se encuentra un nombre de categoría, asignar un valor predeterminado
    if (!isset($cat_name_income[$cant_income])) {
        $cat_name_income[$cant_income] = 'Categoría desconocida';
    }

    $cant_income++;
}

$tamaño_graf_income = count($array_income_date);

$datosX_income = json_encode($cat_name_income);
$datosY_income = json_encode($array_income_value);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style_app.css">

    <script src="https://kit.fontawesome.com/27010df775.js" crossorigin="anonymous"></script>
    <script src="https://cdn.lordicon.com/lordicon-1.1.0.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js" charset="utf-8"></script>
    <title>Centavos Sabios</title>
</head>
<body>
    <section id="general_section">
        <div class="left_menu">
            <menu-main></menu-main>
        </div>
        <div class="central">
            <h1>Dashboard de seguimiento globalizado</h1>
            <hr class="sepa">

            <!-- Gráfica de categorías con planeación del mes -->
            <div class="dash_board">
                <h2>Gráfica de categorías con planeación del mes</h2>
                <div class="cont_grap">
                    <div id="grafica1" class="grap">
                    </div>
                    <div class="grap_table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Categoria</th>
                                <th scope="col">Porcentaje</th>
                                <th scope="col">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php
                                    for ($x=0; $x < $tamaño_cat; $x++) { 
                                        $c_n_t = $cat_name[$x];
                                        $p_c_t = $porcetajes[$x];
                                        $v_c_t = $value_cat[$x];

                                        $v_c_t = number_format($v_c_t, 0, ',', '.');
                                        echo '
                                        <tr>
                                            <td>'.$c_n_t.'</td>
                                            <td>'.$p_c_t.' % </td>
                                            <td> $ '.$v_c_t.'</td>
                                        </tr>
                                        ';
                                    }
                                ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Gráfica de Gastos en el mes -->
                <h2>Gráfica de Gastos en el mes</h2>
                <div class="cont_grap">
                    <div class="grap_table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Fecha de cargue</th>
                                    <th scope="col">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    for ($x=0; $x < $tamaño_grap_2; $x++) { 
                                        if (isset($array_g2_d_e[$x]) && isset($array_g2_v_e[$x]) && isset($cat_name_2[$x])) {
                                            $a_d_e = $array_g2_d_e[$x];
                                            $a_v_e = $array_g2_v_e[$x];
                                            $a_c_n = $cat_name_2[$x];
                                            $a_v_e = number_format($a_v_e, 0, ',', '.');
                                            echo '
                                            <tr>
                                                <td>'.$a_c_n.'</td>
                                                <td>'.$a_d_e.'</td>
                                                <td> $'.$a_v_e.'</td>
                                            </tr>
                                            ';
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="grafica2" class="grap"></div>
                </div>

                <!-- Gráfica de Ingresos en el mes -->
                <h2>Gráfica de Ingresos en el mes</h2>
                <div class="cont_grap">
                    <div class="grap_table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Categoría</th>
                                    <th scope="col">Fecha de cargue</th>
                                    <th scope="col">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    for ($x = 0; $x < $tamaño_graf_income; $x++) {
                                        if (isset($array_income_date[$x]) && isset($array_income_value[$x]) && isset($cat_name_income[$x])) {
                                            $a_d_i = $array_income_date[$x];
                                            $a_v_i = $array_income_value[$x];
                                            $a_c_n_i = $cat_name_income[$x];

                                            $a_v_i = number_format($a_v_i, 0, ',', '.');
                                            echo '
                                            <tr>
                                                <td>' . $a_c_n_i . '</td>
                                                <td>' . $a_d_i . '</td>
                                                <td> $' . $a_v_i . '</td>
                                            </tr>
                                            ';
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="grafica3" class="grap"></div>
                </div>
            </div>
        </div>
        
        <div class="right_menu">
            <div class="profile">
                <img src="https://thispersondoesnotexist.com/" alt="">
                <p><?php echo $nombre_completo?></p>
                <span><?php echo $email?></span>
            </div>
            
            <hr class="sepa">
            <div class="last_tras">
                <h2>TODO ACERCA DE TUS FINANZAS</h2>
                <div class="t_i">
                    <span>Centavos Sabios</span>
                    <p> Gestiona tus finanzas de manera eficiente y personalizada. 
                        Simplifica la planificación presupuestaria, el seguimiento de ingresos y gastos, 
                        y toma decisiones financieras inteligentes con nuestro dashboard globalizado.</p>
                    </div>
                </section>
    <script src="../js/arreglo.js"></script>
    <?php include 'dashboard/grafica1.php' ?>
    <?php include 'dashboard/grafica2.php' ?>
    <?php include 'dashboard/grafica3.php' ?>
    <script src="../js/component_menu.js"></script>
</body>
</html>