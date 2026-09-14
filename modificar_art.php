<?php

ob_start();

require('../menu/menu.php');
require('../Conexion.php');


/*
|--------------------------------------------------------------------------
| HELPER: HTML ESCAPING
|--------------------------------------------------------------------------
|
| Prevents XSS when values from the database are inserted into HTML.
|
*/

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| GET / POST MATERIAL ID
|--------------------------------------------------------------------------
*/

$ID_MATERIAL = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ID_MATERIAL = filter_input(
        INPUT_POST,
        'ID_MATERIAL',
        FILTER_VALIDATE_INT
    );

} else {

    $ID_MATERIAL = filter_input(
        INPUT_GET,
        'ID_MATERIAL',
        FILTER_VALIDATE_INT
    );
}


/*
|--------------------------------------------------------------------------
| VALIDATE MATERIAL ID
|--------------------------------------------------------------------------
*/

if (
    $ID_MATERIAL === false ||
    $ID_MATERIAL === null ||
    $ID_MATERIAL <= 0
) {

    die(
        'ID_MATERIAL no especificado o inválido.<br><br>' .
        'Ejemplo:<br>' .
        'modificar_art.php?ID_MATERIAL=1'
    );
}


/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | READ FORM VALUES
    |--------------------------------------------------------------------------
    */

    $NOMBRE_MATERIAL = trim(
        $_POST['NOMBRE_MATERIAL'] ?? ''
    );

    $STOCK = filter_input(
        INPUT_POST,
        'STOCK',
        FILTER_VALIDATE_INT
    );

    $SKU = trim(
        $_POST['SKU'] ?? ''
    );

    $FECHA_CADUCIDAD = trim(
        $_POST['FECHA_CADUCIDAD'] ?? ''
    );

    $TUA = trim(
        $_POST['TUA'] ?? ''
    );

    $PATA = filter_input(
        INPUT_POST,
        'PATA',
        FILTER_VALIDATE_INT
    );

    $FECHA_REGISTRO = trim(
        $_POST['FECHA_REGISTRO'] ?? ''
    );

    $ID_CATEGORIA = filter_input(
        INPUT_POST,
        'ID_CATEGORIA',
        FILTER_VALIDATE_INT
    );

    $ID_PRO = filter_input(
        INPUT_POST,
        'ID_PRO',
        FILTER_VALIDATE_INT
    );

    $ID_UM = filter_input(
        INPUT_POST,
        'ID_UM',
        FILTER_VALIDATE_INT
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $errors = [];


    /*
    | NOMBRE_MATERIAL
    */

    if ($NOMBRE_MATERIAL === '') {

        $errors[] =
            'El nombre del material es obligatorio.';

    } elseif (mb_strlen($NOMBRE_MATERIAL) > 100) {

        $errors[] =
            'El nombre del material no puede superar 100 caracteres.';
    }


    /*
    | STOCK
    */

    if (
        $STOCK === false ||
        $STOCK === null ||
        $STOCK < 0
    ) {

        $errors[] =
            'STOCK debe ser un número entero mayor o igual a 0.';
    }


    /*
    | SKU
    */

    if ($SKU === '') {

        $errors[] =
            'El SKU es obligatorio.';

    } elseif (mb_strlen($SKU) > 30) {

        $errors[] =
            'El SKU no puede superar 30 caracteres.';
    }


    /*
    | FECHA_CADUCIDAD
    |
    | Your database defines this column as TEXT.
    | Therefore we treat it as text.
    */

    if (mb_strlen($FECHA_CADUCIDAD) > 255) {

        $errors[] =
            'FECHA_CADUCIDAD no puede superar 255 caracteres.';
    }


    /*
    | TUA
    */

    if ($TUA !== '') {

        if (!is_numeric($TUA)) {

            $errors[] =
                'TUA debe ser un número válido.';

        } elseif ((float)$TUA < 0) {

            $errors[] =
                'TUA no puede ser negativo.';
        }
    }


    /*
    | PATA
    */

    if (
        $PATA === false ||
        $PATA === null ||
        $PATA < 0
    ) {

        $errors[] =
            'PATA debe ser un número entero mayor o igual a 0.';
    }


    /*
    | FECHA_REGISTRO
    */

    if ($FECHA_REGISTRO !== '') {

        $date = DateTime::createFromFormat(
            'Y-m-d',
            $FECHA_REGISTRO
        );

        if (
            !$date ||
            $date->format('Y-m-d') !== $FECHA_REGISTRO
        ) {

            $errors[] =
                'FECHA_REGISTRO debe tener formato YYYY-MM-DD.';
        }
    }


    /*
    | CATEGORY
    |
    | Category can be NULL because the database allows NULL.
    */

    if (
        $ID_CATEGORIA !== false &&
        $ID_CATEGORIA !== null &&
        $ID_CATEGORIA <= 0
    ) {

        $errors[] =
            'ID_CATEGORIA inválido.';
    }


    /*
    | PROVIDER
    |
    | Provider can also be NULL.
    */

    if (
        $ID_PRO !== false &&
        $ID_PRO !== null &&
        $ID_PRO <= 0
    ) {

        $errors[] =
            'ID_PRO inválido.';
    }


    /*
    | UNIT OF MEASURE
    |
    | ID_UM is NOT NULL in the database.
    */

    if (
        $ID_UM === false ||
        $ID_UM === null ||
        $ID_UM <= 0
    ) {

        $errors[] =
            'Debe seleccionar una unidad de medida.';
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK THAT FOREIGN KEYS ACTUALLY EXIST
    |--------------------------------------------------------------------------
    */


    /*
    | Check CATEGORY
    */

    if (
        $ID_CATEGORIA !== false &&
        $ID_CATEGORIA !== null
    ) {

        $stmt = mysqli_prepare(
            $Conexion,
            'SELECT ID_CATEGORIA
             FROM categoria
             WHERE ID_CATEGORIA = ?'
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $ID_CATEGORIA
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 0) {

            $errors[] =
                'La categoría seleccionada no existe.';
        }

        mysqli_stmt_close($stmt);
    }


    /*
    | Check PROVIDER
    */

    if (
        $ID_PRO !== false &&
        $ID_PRO !== null
    ) {

        $stmt = mysqli_prepare(
            $Conexion,
            'SELECT ID_PRO
             FROM proveedor
             WHERE ID_PRO = ?'
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $ID_PRO
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 0) {

            $errors[] =
                'El proveedor seleccionado no existe.';
        }

        mysqli_stmt_close($stmt);
    }


    /*
    | Check UNIT
    */

    if (
        $ID_UM !== false &&
        $ID_UM !== null
    ) {

        $stmt = mysqli_prepare(
            $Conexion,
            'SELECT ID_UM
             FROM unidad_m
             WHERE ID_UM = ?'
        );

        mysqli_stmt_bind_param(
            $stmt,
            'i',
            $ID_UM
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 0) {

            $errors[] =
                'La unidad de medida seleccionada no existe.';
        }

        mysqli_stmt_close($stmt);
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW VALIDATION ERRORS
    |--------------------------------------------------------------------------
    */

    if (!empty($errors)) {

        echo '<div class="container mt-3">';
        echo '<div class="alert alert-danger">';
        echo '<strong>Se encontraron errores:</strong>';
        echo '<ul>';

        foreach ($errors as $error) {

            echo '<li>' . e($error) . '</li>';
        }

        echo '</ul>';
        echo '</div>';
        echo '</div>';

        /*
         * Do not execute UPDATE.
         */
    } else {


        /*
        |--------------------------------------------------------------------------
        | UPDATE MATERIAL
        |--------------------------------------------------------------------------
        |
        | Prepared statement protects against SQL injection.
        |
        */

        $SQL = "
            UPDATE material
            SET
                NOMBRE_MATERIAL = ?,
                STOCK = ?,
                SKU = ?,
                FECHA_CADUCIDAD = NULLIF(?, ''),
                TUA = NULLIF(?, ''),
                PATA = ?,
                FECHA_REGISTRO = NULLIF(?, ''),
                ID_CATEGORIA = ?,
                ID_PRO = ?,
                ID_UM = ?
            WHERE ID_MATERIAL = ?
        ";


        $stmt = mysqli_prepare(
            $Conexion,
            $SQL
        );


        if (!$stmt) {

            die(
                'Error preparando UPDATE: ' .
                e(mysqli_error($Conexion))
            );
        }


        /*
        | Parameter types:
        |
        | s = string
        | i = integer
        |
        */

        mysqli_stmt_bind_param(
            $stmt,
            'sisssisiiii',
            $NOMBRE_MATERIAL,
            $STOCK,
            $SKU,
            $FECHA_CADUCIDAD,
            $TUA,
            $PATA,
            $FECHA_REGISTRO,
            $ID_CATEGORIA,
            $ID_PRO,
            $ID_UM,
            $ID_MATERIAL
        );


        /*
        | Execute UPDATE
        */

        if (!mysqli_stmt_execute($stmt)) {


            /*
            | MySQL error 1062 = duplicate SKU.
            */

            if (mysqli_stmt_errno($stmt) === 1062) {

                die(
                    'Error: el SKU "' .
                    e($SKU) .
                    '" ya existe.'
                );
            }


            die(
                'Error actualizando material: ' .
                e(mysqli_stmt_error($stmt))
            );
        }


        mysqli_stmt_close($stmt);


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        header('Location: material.php');

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| GET MATERIAL
|--------------------------------------------------------------------------
*/

$SQL = "
    SELECT
        ID_MATERIAL,
        NOMBRE_MATERIAL,
        STOCK,
        SKU,
        FECHA_CADUCIDAD,
        TUA,
        PATA,
        FECHA_REGISTRO,
        FOTO,
        ID_CATEGORIA,
        ID_PRO,
        ID_UM
    FROM material
    WHERE ID_MATERIAL = ?
";


$stmt = mysqli_prepare(
    $Conexion,
    $SQL
);


if (!$stmt) {

    die(
        'Error preparando SELECT: ' .
        e(mysqli_error($Conexion))
    );
}


mysqli_stmt_bind_param(
    $stmt,
    'i',
    $ID_MATERIAL
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$Registro = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| MATERIAL NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$Registro) {

    die(
        'No existe el material con ID_MATERIAL = ' .
        e($ID_MATERIAL)
    );
}


/*
|--------------------------------------------------------------------------
| DATABASE VALUES
|--------------------------------------------------------------------------
*/

$ID_MATERIAL = $Registro['ID_MATERIAL'];

$NOMBRE_MATERIAL =
    $Registro['NOMBRE_MATERIAL'];

$STOCK =
    $Registro['STOCK'];

$SKU =
    $Registro['SKU'];

$FECHA_CADUCIDAD =
    $Registro['FECHA_CADUCIDAD'];

$TUA =
    $Registro['TUA'];

$PATA =
    $Registro['PATA'];

$FECHA_REGISTRO =
    $Registro['FECHA_REGISTRO'];

$FOTO =
    $Registro['FOTO'];

$ID_CATEGORIA =
    $Registro['ID_CATEGORIA'];

$ID_PRO =
    $Registro['ID_PRO'];

$ID_UM =
    $Registro['ID_UM'];

?>

<!doctype html>

<html lang="es">

<head>

```
<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Modificar Material</title>

<link
    rel="stylesheet"
    href="../bootstrap/css/bootstrap.min.css"
>

<style>

    body {
        background-image: url('../Img/pencil.jpg');
        background-size: cover;
        background-attachment: fixed;
    }

    .titulo {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 40px;
        color: #000000;
        text-align: center;
        margin: 30px 0;
    }

    .campo-label {
        font-weight: bold;
        font-size: 18px;
        color: #000000;
        white-space: nowrap;
    }

    .form-container {
        max-width: 950px;
        margin: auto;
        padding: 20px;
    }

</style>
```

</head>

<body>

<div class="container form-container">

```
<h1 class="titulo">
    MODIFICAR MATERIAL
</h1>


<form
    action=""
    method="post"
    name="form1"
    id="form1"
>


    <!--
    --------------------------------------------------------
    MATERIAL ID
    --------------------------------------------------------
    -->

    <input
        type="hidden"
        name="ID_MATERIAL"
        value="<?= e($ID_MATERIAL) ?>"
    >


    <div style="overflow-x:auto;">

        <table
            class="table table-secondary table-striped"
        >


            <!--
            ------------------------------------------------
            NOMBRE MATERIAL
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    NOMBRE MATERIAL
                </td>

                <td>

                    <input
                        name="NOMBRE_MATERIAL"
                        type="text"
                        maxlength="100"
                        class="form-control"
                        id="NOMBRE_MATERIAL"
                        value="<?= e($NOMBRE_MATERIAL) ?>"
                        required
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            STOCK
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    STOCK
                </td>

                <td>

                    <input
                        name="STOCK"
                        type="number"
                        class="form-control"
                        min="0"
                        max="2147483647"
                        id="STOCK"
                        value="<?= e($STOCK) ?>"
                        required
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            SKU
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    SKU
                </td>

                <td>

                    <input
                        name="SKU"
                        type="text"
                        maxlength="30"
                        class="form-control"
                        id="SKU"
                        value="<?= e($SKU) ?>"
                        required
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            FECHA CADUCIDAD
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    FECHA CADUCIDAD
                </td>

                <td>

                    <input
                        name="FECHA_CADUCIDAD"
                        type="text"
                        maxlength="255"
                        class="form-control"
                        id="FECHA_CADUCIDAD"
                        value="<?= e($FECHA_CADUCIDAD) ?>"
                        placeholder="Ej: 2027-12-31"
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            TUA
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    TUA
                </td>

                <td>

                    <input
                        name="TUA"
                        type="number"
                        step="0.01"
                        min="0"
                        max="99999999.99"
                        class="form-control"
                        id="TUA"
                        value="<?= e($TUA) ?>"
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            PATA
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    PATA
                </td>

                <td>

                    <input
                        name="PATA"
                        type="number"
                        min="0"
                        max="2147483647"
                        class="form-control"
                        id="PATA"
                        value="<?= e($PATA) ?>"
                        required
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            FECHA REGISTRO
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    FECHA REGISTRO
                </td>

                <td>

                    <input
                        name="FECHA_REGISTRO"
                        type="date"
                        class="form-control"
                        id="FECHA_REGISTRO"
                        value="<?= e($FECHA_REGISTRO) ?>"
                    >

                </td>

            </tr>


            <!--
            ------------------------------------------------
            FOTO
            ------------------------------------------------
            -->

            <tr>

                <td class="campo-label">
                    FOTO
                </td>

                <td>

                    <input
                        class="form-control"
                        name="FOTO"
                        id="FOTO"
                        value="<?= e($FOTO) ?>"
                        readonly
                    >

                </td>

            </tr>


            <!--
            =================================================
            CATEGORY DROPDOWN
            =================================================
            -->

            <tr>

                <td class="campo-label">
                    CATEGORÍA
                </td>

                <td>

                    <?php

                    /*
                    | Correct column:
                    |
                    | categoria.NOMBRE_CAT
                    */

                    $SQL_CATEGORIA = "
                        SELECT
                            ID_CATEGORIA,
                            NOMBRE_CAT
                        FROM categoria
                        ORDER BY NOMBRE_CAT
                    ";


                    $RKO = mysqli_query(
                        $Conexion,
                        $SQL_CATEGORIA
                    );


                    if (!$RKO) {

                        die(
                            'Error cargando categorías: ' .
                            e(mysqli_error($Conexion))
                        );
                    }

                    ?>


                    <select
                        class="form-control"
                        name="ID_CATEGORIA"
                        id="ID_CATEGORIA"
                    >

                        <option value="">
                            -- Sin categoría --
                        </option>


                        <?php while (
                            $REY =
                            mysqli_fetch_assoc($RKO)
                        ) { ?>


                            <option
                                value="<?= e($REY['ID_CATEGORIA']) ?>"
                                <?=
                                    (
                                        (string)$ID_CATEGORIA ===
                                        (string)$REY['ID_CATEGORIA']
                                    )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e($REY['NOMBRE_CAT']) ?>

                            </option>


                        <?php } ?>


                    </select>

                </td>

            </tr>


            <!--
            =================================================
            PROVIDER DROPDOWN
            =================================================
            -->

            <tr>

                <td class="campo-label">
                    PROVEEDOR
                </td>

                <td>

                    <?php

                    /*
                    | Correct columns:
                    |
                    | proveedor.ID_PRO
                    | proveedor.NOMBRE_PRO
                    */

                    $SQL_PROVEEDOR = "
                        SELECT
                            ID_PRO,
                            NOMBRE_PRO
                        FROM proveedor
                        ORDER BY NOMBRE_PRO
                    ";


                    $RPRO = mysqli_query(
                        $Conexion,
                        $SQL_PROVEEDOR
                    );


                    if (!$RPRO) {

                        die(
                            'Error cargando proveedores: ' .
                            e(mysqli_error($Conexion))
                        );
                    }

                    ?>


                    <select
                        class="form-control"
                        name="ID_PRO"
                        id="ID_PRO"
                    >

                        <option value="">
                            -- Sin proveedor --
                        </option>


                        <?php while (
                            $PRO =
                            mysqli_fetch_assoc($RPRO)
                        ) { ?>


                            <option
                                value="<?= e($PRO['ID_PRO']) ?>"
                                <?=
                                    (
                                        (string)$ID_PRO ===
                                        (string)$PRO['ID_PRO']
                                    )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e($PRO['NOMBRE_PRO']) ?>

                            </option>


                        <?php } ?>


                    </select>

                </td>

            </tr>


            <!--
            =================================================
            UNIT OF MEASURE DROPDOWN
            =================================================
            -->

            <tr>

                <td class="campo-label">
                    UNIDAD DE MEDIDA
                </td>

                <td>

                    <?php

                    /*
                    | Correct columns:
                    |
                    | unidad_m.ID_UM
                    | unidad_m.NOMBRE_UM
                    | unidad_m.ABREVIATURA
                    */

                    $SQL_UM = "
                        SELECT
                            ID_UM,
                            NOMBRE_UM,
                            ABREVIATURA
                        FROM unidad_m
                        ORDER BY NOMBRE_UM
                    ";


                    $RUM = mysqli_query(
                        $Conexion,
                        $SQL_UM
                    );


                    if (!$RUM) {

                        die(
                            'Error cargando unidades de medida: ' .
                            e(mysqli_error($Conexion))
                        );
                    }

                    ?>


                    <select
                        class="form-control"
                        name="ID_UM"
                        id="ID_UM"
                        required
                    >

                        <option value="">
                            -- Seleccione unidad --
                        </option>


                        <?php while (
                            $UM =
                            mysqli_fetch_assoc($RUM)
                        ) { ?>


                            <option
                                value="<?= e($UM['ID_UM']) ?>"
                                <?=
                                    (
                                        (string)$ID_UM ===
                                        (string)$UM['ID_UM']
                                    )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e($UM['NOMBRE_UM']) ?>

                                <?php

                                if (
                                    !empty(
                                        $UM['ABREVIATURA']
                                    )
                                ) {

                                    echo ' (' .
                                        e(
                                            $UM['ABREVIATURA']
                                        ) .
                                        ')';
                                }

                                ?>

                            </option>


                        <?php } ?>


                    </select>

                </td>

            </tr>


            <!--
            =================================================
            BUTTONS
            =================================================
            -->

            <tr>

                <td
                    colspan="2"
                    class="text-center"
                >

                    <button
                        name="Modificar"
                        type="submit"
                        class="btn btn-dark"
                    >
                        Modificar
                    </button>


                    <a
                        href="material.php"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                </td>

            </tr>


        </table>

    </div>

</form>
```

</div>

<!--
|--------------------------------------------------------------------------
| JAVASCRIPT
|--------------------------------------------------------------------------
-->

<script src="../js/jquery-3.4.1.slim.min.js"></script>

<script src="../js/popper.min.js"></script>

<script src="../js/bootstrap.min.js"></script>

<script>

/*
|--------------------------------------------------------------------------
| MATERIAL NAME
|--------------------------------------------------------------------------
|
| Converts:
|
|     TORNILLO DE ACERO
|
| into:
|
|     Tornillo de acero
|
*/

const nombreMaterial =
    document.getElementById('NOMBRE_MATERIAL');


nombreMaterial.addEventListener(
    'blur',
    function () {

        const texto =
            this.value.trim().toLowerCase();


        if (texto.length > 0) {

            this.value =
                texto.charAt(0).toUpperCase() +
                texto.slice(1);
        }

    }
);


/*
|--------------------------------------------------------------------------
| SKU
|--------------------------------------------------------------------------
|
| Always uppercase.
|
*/

const sku =
    document.getElementById('SKU');


sku.addEventListener(
    'input',
    function () {

        this.value =
            this.value.toUpperCase();

    }
);


/*
|--------------------------------------------------------------------------
| BOOTSTRAP DROPDOWNS
|--------------------------------------------------------------------------
*/

$(document).ready(function () {

    $('.dropdown-toggle').dropdown();

});

</script>

<?php

ob_end_flush();

?>

</body>

</html>
