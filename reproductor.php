<?php
    try{
        session_start();

        if(!isset($_SESSION['usuario_mapa'])){
            header("Location: https://www.marverrefacciones.mx/login_mapa.php");
            exit();
        }

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) AND usuario = :repartidor");
        $preparada->bindValue(':dia_inicial', $_GET['fecha']);
        $preparada->bindValue(':dia_final', $_GET['fecha']);
        $preparada->bindValue(':repartidor', $_GET['id']);
        $preparada->execute();

        echo '<script>';
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
                <h5 class="card-title" id="txtNombreRepartidor">Seleccione un Repartidor</h5>
                <p class="card-text" id="velocidadRepartidor">0.0 Km/h</p>
                <div class="form-check form-switch d-inline-block ms-2 mt-2" >
                <button onclick="pausar();" class="btn btn-primary"><i class="fa-solid fa-play" id="icono_pausar" ></i></button>
                <button onclick="adelantar();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-right"></i></button>
                <button onclick="retroceder();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-left"></i></button>
                <a class="btn btn-primary" >Rastreo</a>
                <a class="btn btn-primary" >Excesos</a>
                <a class="btn btn-primary" >Repartidores</a>
                <a class="btn btn-primary" >Infraccion</a>
                <button onclick="reproduccion();" class="btn btn-primary"><i class="fa-solid fa-forward"></i>  <i class="fa-solid fa-1" id="icono_velocidad" ></i></button>
                    <label for="cursor" class="form-label" id="txtPosicion" >Posicion</label>
                    <input type="range" onchange="actualizar_todo();" class="form-range" min="0" max="1" value="0" id="cursor">
                    <input class="form-check-input" id="seguirRepartidor" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                    <label class="form-check-label ms-1" for="flexSwitchCheckChecked">Seguir repartidor</label>
                </div>
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
        let seguirRepartidor;
        let velocidadRepartidor;
        let txtPosicion;
        let marcador;
        let posicion_inicial;
        let posicion_final;
        let cursor;
        let frame = 1;
        let pausado = true;
        let velocidad = 100;
        let actualizaciones = 0;

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

        function reproduccion(){
            if( velocidad == 100 ){
                velocidad = 50;
                document.getElementById("icono_velocidad").classList.remove('fa-1');
                document.getElementById("icono_velocidad").classList.add('fa-2');
            }else if(velocidad == 50){
                velocidad = 25;
                document.getElementById("icono_velocidad").classList.remove('fa-2');
                document.getElementById("icono_velocidad").classList.add('fa-4');
            }else if(velocidad == 25){
                velocidad = 12;
                document.getElementById("icono_velocidad").classList.remove('fa-4');
                document.getElementById("icono_velocidad").classList.add('fa-8');
            }else if(velocidad == 12){
                velocidad = 100;
                document.getElementById("icono_velocidad").classList.remove('fa-8');
                document.getElementById("icono_velocidad").classList.add('fa-1');
            }
        }

        function adelantar(){
            cursor.valueAsNumber += 60;
            actualizar_todo();
        }

        function retroceder(){
            cursor.valueAsNumber -= 60;
            actualizar_todo();
        }

        function actualizar_todo(){
            frame = 1;
            velocidadRepartidor.innerText = (posiciones[cursor.valueAsNumber]['velocidad'] * 3.6).toFixed(1) + ' Km/h';
            let fecha = new Date(posiciones[cursor.valueAsNumber]['fecha']);
            txtPosicion.innerText = fecha.getFullYear() + "-" + ( fecha.getMonth() + 1 < 10 ? '0' + ( fecha.getMonth() + 1 ) : fecha.getMonth() + 1 ) + "-" + ( fecha.getDay() < 10 ? '0' + fecha.getDay() : fecha.getDay() ) + ' ' + ( fecha.getHours() % 12 < 10 ? ( fecha.getHours() % 12 == 0 ? '12' : '0' + ( fecha.getHours() % 12 ) ) : fecha.getHours() % 12 ) + ':' + ( fecha.getMinutes() < 10 ? '0' + fecha.getMinutes() : fecha.getMinutes() ) + '.' + ( fecha.getSeconds() < 10 ? '0' + fecha.getSeconds() : fecha.getSeconds() ) + ' ' + ( fecha.getHours() >= 12 ? 'pm' : 'am' );
            marcador.position = { lat: posiciones[cursor.valueAsNumber]['latitud'], lng: posiciones[cursor.valueAsNumber]['longitud'] };
            mapa.panTo(marcador.position);
        }
    </script>

    <script type="module">

        async function procesar_vista() {
            if(pausado){
                setTimeout(procesar_vista, 10);
                return;
            }

            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            actualizaciones++;
            if (actualizaciones % 50 == 0 && seguirRepartidor.checked) {
                mapa.panTo(marcador.position);
                actualizaciones = 0;
            }

            if (frame == 1) {
                posicion_inicial = { lat: marcador.position['lat'], lng: marcador.position['lng'] };
                posicion_final = { lat: posiciones[cursor.valueAsNumber]['latitud'], lng: posiciones[cursor.valueAsNumber]['longitud'] };
                velocidadRepartidor.innerText = (posiciones[cursor.valueAsNumber]['velocidad'] * 3.6).toFixed(1) + ' Km/h';
                let fecha = new Date(posiciones[cursor.valueAsNumber]['fecha']);
                txtPosicion.innerText = fecha.getFullYear() + "-" + ( fecha.getMonth() + 1 < 10 ? '0' + ( fecha.getMonth() + 1 ) : fecha.getMonth() + 1 ) + "-" + ( fecha.getDay() < 10 ? '0' + fecha.getDay() : fecha.getDay() ) + ' ' + ( fecha.getHours() % 12 < 10 ? ( fecha.getHours() % 12 == 0 ? '12' : '0' + ( fecha.getHours() % 12 ) ) : fecha.getHours() % 12 ) + ':' + ( fecha.getMinutes() < 10 ? '0' + fecha.getMinutes() : fecha.getMinutes() ) + '.' + ( fecha.getSeconds() < 10 ? '0' + fecha.getSeconds() : fecha.getSeconds() ) + ' ' + ( fecha.getHours() >= 12 ? 'pm' : 'am' );
            }

            if (posicion_inicial['lat'] != posicion_final['lat'] || posicion_inicial['lng'] != posicion_final['lng']) {

                let latitud_dif_abs = Math.abs(posicion_inicial['lat'] - posicion_final['lat']) * frame / velocidad;
                let longitud_dif_abs = Math.abs(Math.abs(posicion_inicial['lng']) + posicion_final['lng']) * frame / velocidad;

                let latitud = posicion_inicial['lat'] >= posicion_final['lat'] ? posicion_inicial['lat'] - latitud_dif_abs : posicion_inicial['lat'] + latitud_dif_abs;
                let longitud = posicion_inicial['lng'] >= posicion_final['lng'] ? posicion_inicial['lng'] - longitud_dif_abs : posicion_inicial['lng'] + longitud_dif_abs;

                marcador.position = { lat: latitud, lng: longitud };
            }

            if (frame >= velocidad) {
                frame = 1;
                cursor.valueAsNumber += 1;

                if(cursor.valueAsNumber == posiciones.length - 1){
                    cursor.valueAsNumber = 0;
                }

                marcador.position = { lat: posicion_final['lat'], lng: posicion_final['lng'] };
            } else {
                frame++;
            }

            setTimeout(procesar_vista, 10);
        }

        async function initMap() {
            const { Map, InfoWindow } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            velocidadRepartidor = document.getElementById('velocidadRepartidor');
            seguirRepartidor = document.getElementById('seguirRepartidor');
            cursor = document.getElementById('cursor');
            txtPosicion = document.getElementById('txtPosicion');

            cursor.max = posiciones.length - 1;
            cursor.value = posiciones.findIndex( posicion => posicion.id == <?php echo $_GET['posicion']; ?> );
            velocidadRepartidor.innerText = (posiciones[cursor.valueAsNumber]['velocidad'] * 3.6).toFixed(1) + ' Km/h';
            let fecha = new Date(posiciones[cursor.valueAsNumber]['fecha']);
            txtPosicion.innerText = fecha.getFullYear() + "-" + ( fecha.getMonth() + 1 < 10 ? '0' + ( fecha.getMonth() + 1 ) : fecha.getMonth() + 1 ) + "-" + ( fecha.getDay() < 10 ? '0' + fecha.getDay() : fecha.getDay() ) + ' ' + ( fecha.getHours() % 12 < 10 ? ( fecha.getHours() % 12 == 0 ? '12' : '0' + ( fecha.getHours() % 12 ) ) : fecha.getHours() % 12 ) + ':' + ( fecha.getMinutes() < 10 ? '0' + fecha.getMinutes() : fecha.getMinutes() ) + '.' + ( fecha.getSeconds() < 10 ? '0' + fecha.getSeconds() : fecha.getSeconds() ) + ' ' + ( fecha.getHours() >= 12 ? 'pm' : 'am' );

            document.getElementById('txtIdRepartidor').innerText = <?php echo $_GET['id']; ?>;
            document.getElementById('txtNombreRepartidor').innerText = '<?php echo $_GET['nombre']; ?>';

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: posiciones[cursor.valueAsNumber]['latitud'], lng: posiciones[cursor.valueAsNumber]['longitud'] },
                zoom: 18.5,
                mapId: '7845e7dffe8cea37',
                mapTypeId: google.maps.MapTypeId.HYBRID
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

            marcador = new AdvancedMarkerElement({
                content: imagen,
                map: mapa,
                position: { lat: posiciones[cursor.valueAsNumber]['latitud'], lng: posiciones[cursor.valueAsNumber]['longitud'] }
            });

            let infowindow = new google.maps.InfoWindow({
                content: '<p style="margin: 0;" ><strong><?php echo $_GET['id']; ?> </strong><?php echo $_GET['nombre']; ?></p>'
            });

            marcador.addListener("click", () => {
                mapa.setZoom(18.5);
                mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
                mapa.panTo(marcador.position);

                infowindow.open({
                    anchor: marcador,
                    map: mapa,
                });
            });



            setTimeout(procesar_vista, 10);
        }

        initMap();
    </script>

</body>

</html>