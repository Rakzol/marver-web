<?php
    try{
        session_start();

        if(!isset($_SESSION['usuario_mapa'])){
            header("Location: https://www.marverrefacciones.mx/login_mapa.php");
            exit();
        }

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) AND usuario = :repartidor ORDER BY fecha ASC");
        $preparada->bindValue(':dia_inicial', $_GET['fecha']);
        $preparada->bindValue(':dia_final', $_GET['fecha']);
        $preparada->bindValue(':repartidor', $_GET['id']);
        $preparada->execute();

        echo '<script>';
        echo 'let fechaConsulta = "' . $_GET['fecha'] . ' 00:00:00.000";';
        echo 'let posiciones = ' . json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE) . ';';
        echo '</script>';
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>

<!DOCTYPE html>
<html class="h-100" lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reproductor</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet" />
    <!-- Favicon -->
    <link href="img/logo_solo.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <!-- <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet"> -->

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
</head>

<body class="h-100">

    <div class="d-flex h-100 flex-column">

        <div class="flex-grow-1" id="mapa"></div>

        <div class="card text-center border-0">
            <h5 class="card-header">
                Repartidor : <strong id="txtIdRepartidor"></strong>
            </h5>
            <div class="card-body">

                <div class="d-flex justify-content-center gap-3 mb-3" >
                    <h5 class="card-title mb-0 align-self-center" id="txtNombreRepartidor">Seleccione un Repartidor</h5>
                    <p class="card-text mb-0 align-self-center" id="velocidadRepartidor">0.0 Km/h</p>
                    <!-- <div class="form-check form-switch d-inline-block align-self-center" >
                        <input class="form-check-input" id="seguirRepartidor" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                        <label class="form-check-label ms-1" for="flexSwitchCheckChecked">Seguir repartidor</label>
                    </div> -->
                </div>

                <label for="cursor" class="form-label mb-0 d-block" id="txtFecha" >Fecha</label>
                <input type="range" onchange="actualizar_todo();" style="max-width: 390px;" class="form-range d-block m-auto" id="cursor">

                <div class="d-flex justify-content-center gap-2" >
                    <button onclick="pausar();" class="btn btn-primary"><i class="fa-solid fa-play" id="icono_pausar" ></i></button>
                    <button onclick="retroceder();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-left"></i></button>
                    <button onclick="velocidadMaxima();" class="btn btn-primary"><i class="fa-solid fa-triangle-exclamation"></i></button>
                    <button onclick="adelantar();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-right"></i></button>
                </div>

                <a class="btn btn-primary mt-3" href="https://www.marverrefacciones.mx/mapa" target="_blank" >Rastreo</a>
                <a class="btn btn-primary mt-3" href="https://www.marverrefacciones.mx/repartidores" target="_blank" >Repartidores</a>
                <a class="btn btn-primary mt-3" href="https://www.marverrefacciones.mx/excesos" target="_blank"  >Excesos</a>
            </div>
        </div>

    </div>

    <!-- MDB -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script>

    <!-- JavaScript Libraries -->
    <!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script> -->

    <!-- Template Javascript -->
    <!-- <script src="js/main.js"></script> -->

    <script>(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo", v: "weekly" });</script>

    <script>

        let mapa;
        let velocidadRepartidor;
        let velocidadMaximaTimeStamp;
        let txtFecha;
        let marcador;
        let cursor;
        let pausado = true;

        function pausar(){
            if(pausado){
                pausado = false;
                document.getElementById("icono_pausar").classList.remove('fa-play');
                document.getElementById("icono_pausar").classList.add('fa-pause');
            }else{
                pausado = true
                document.getElementById("icono_pausar").classList.remove('fa-pause');
                document.getElementById("icono_pausar").classList.add('fa-play');
            }
        }

        function adelantar(){
            cursor.valueAsNumber += 60000;
            actualizar_todo();
        }

        function retroceder(){
            cursor.valueAsNumber -= 60000;
            actualizar_todo();
        }

        function actualizar_todo(){
            velocidadRepartidor.innerText = (posicionActual()['velocidad'] * 3.6).toFixed(1) + ' Km/h';
            let fecha = new Date(cursor.valueAsNumber);
            txtFecha.innerText = fecha.getFullYear() + "-" + ( fecha.getMonth() + 1 < 10 ? '0' + ( fecha.getMonth() + 1 ) : fecha.getMonth() + 1 ) + "-" + ( fecha.getDate() < 10 ? '0' + fecha.getDate() : fecha.getDate() ) + ' ' + ( fecha.getHours() % 12 < 10 ? ( fecha.getHours() % 12 == 0 ? '12' : '0' + ( fecha.getHours() % 12 ) ) : fecha.getHours() % 12 ) + ':' + ( fecha.getMinutes() < 10 ? '0' + fecha.getMinutes() : fecha.getMinutes() ) + '.' + ( fecha.getSeconds() < 10 ? '0' + fecha.getSeconds() : fecha.getSeconds() ) + ' ' + ( fecha.getHours() >= 12 ? 'pm' : 'am' );

            marcador.position = { lat: posicionActual()['latitud'], lng: posicionActual()['longitud'] };
            mapa.panTo(marcador.position);
        }

        function velocidadMaxima(){
            cursor.valueAsNumber = velocidadMaximaTimeStamp;
            actualizar_todo();
        }

        function posicionActual(){
            let posicion = posiciones.find(posicion=>new Date(posicion["fecha"])>=new Date(cursor.valueAsNumber));
            if (posicion) {
                return posicion;
            }
            return posiciones.at(-1);
        }

        function procesar_vista() {
            if(pausado){
                return;
            }

            cursor.valueAsNumber += 1000;
            actualizar_todo();
        }
    </script>

    <script type="module">

        async function initMap() {
            const { Map, InfoWindow } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            velocidadRepartidor = document.getElementById('velocidadRepartidor');
            cursor = document.getElementById('cursor');
            txtFecha = document.getElementById('txtFecha');

            document.getElementById('txtIdRepartidor').innerText = <?= $_GET['id']; ?>;
            document.getElementById('txtNombreRepartidor').innerText = '<?= $_GET['nombre']; ?>';

            velocidadMaximaTimeStamp = <?= $_GET['velocidadMaximaTimeStamp']; ?>;

            cursor.min = new Date(fechaConsulta).getTime();
            cursor.max = parseInt(cursor.min) + 86399999;
            cursor.valueAsNumber = velocidadMaximaTimeStamp;

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: posicionActual()['latitud'], lng: posicionActual()['longitud'] },
                zoom: 14,
                mapId: '7845e7dffe8cea37',
                mapTypeId: google.maps.MapTypeId.HYBRID
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

            marcador = new AdvancedMarkerElement({
                content: imagen,
                map: mapa,
                position: { lat: posicionActual()['latitud'], lng: posicionActual()['longitud'] }
            });

            let infowindow = new google.maps.InfoWindow({
                content: '<p style="margin: 0;" ><strong><?= $_GET['id']; ?> </strong><?= $_GET['nombre']; ?></p>'
            });

            marcador.addListener("click", () => {
                mapa.setZoom(14);
                mapa.panTo(marcador.position);

                infowindow.open({
                    anchor: marcador,
                    map: mapa,
                });
            });

            setInterval(procesar_vista, 1000);
            actualizar_todo();
        }

        initMap();
    </script>

</body>

</html>