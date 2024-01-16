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

    <!-- Modal -->
    <div class="modal fade" id="modalSelector" tabindex="-1" aria-labelledby="modalSelectorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalSelectorLabel">Repartidores</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ol class="list-group" id="listaRepartidores">
                    </ol>
                </div>
                <div class="modal-footer">
                    <button id="btnCerrarModal" type="button" class="btn btn-primary"
                        data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex h-100 flex-column">

        <div class="flex-grow-1" id="mapa"></div>

        <div class="card text-center border-0">
            <h5 class="card-header">
                Repartidor : <strong id="txtIdRepartidor"></strong>
            </h5>
            <div class="card-body">
                <h5 class="card-title" id="txtNombreRepartidor">Seleccione un Repartidor</h5>
                <p class="card-text" id="velocidadRepartidor">0.0 Km/h</p>
                <a href="#" id="btnBuscarRepartidor" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalSelector">Buscar Repartidor</a>
                <div class="form-check form-switch d-inline-block ms-2 mt-2" >
                    <label for="cursor" class="form-label">Posicion</label>
                    <input type="range" class="form-range" min="0" max="1" value="0" id="cursor">
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

    <script type="module">

        let mapa;
        let seguirRepartidor;
        let velocidadRepartidor;
        let marcador;
        let posicion_inicial;
        let posicion_final;
        let cursor;
        let frame = 1;

        async function procesar_vista() {
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            if (frame % 50 == 0 && seguirRepartidor.checked) {
                mapa.panTo(marcador.position);
            }

            if (frame == 1) {
                posicion_inicial = { lat: marcador.position['lat'], lng: marcador.position['lng'] };
                posicion_final = { lat: posiciones[cursor.value]['lat'], lng: posiciones[cursor.value]['lng'] };
                velocidadRepartidor.innerText = (posiciones[cursor.value]['velocidad'] * 3.6).toFixed(1) + ' Km/h';
            }

            if (posicion_inicial['lat'] != posicion_final['lat'] || posicion_inicial['lng'] != posicion_final['lng']) {

                let latitud_dif_abs = Math.abs(posicion_inicial['lat'] - posicion_final['lat']) * frame / 150;
                let longitud_dif_abs = Math.abs(Math.abs(posicion_inicial['lng']) + posicion_final['lng']) * frame / 150;

                let latitud = posicion_inicial['lat'] >= posicion_final['lat'] ? posicion_inicial['lat'] - latitud_dif_abs : posicion_inicial['lat'] + latitud_dif_abs;
                let longitud = posicion_inicial['lng'] >= posicion_final['lng'] ? posicion_inicial['lng'] - longitud_dif_abs : posicion_inicial['lng'] + longitud_dif_abs;

                marcador.position = { lat: latitud, lng: longitud };
            }

            if (frame == 100) {
                frame = 1;
                cursor.value += 1;
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

            cursor.max = posiciones.length;

            document.getElementById('txtIdRepartidor').innerText = <?php echo $_GET['id']; ?>;
            document.getElementById('txtNombreRepartidor').innerText = <?php echo $_GET['nombre']; ?>;

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: 25.7951169, lng: -108.99698492 },
                zoom: 18.5,
                mapId: '7845e7dffe8cea37',
                mapTypeId: google.maps.MapTypeId.HYBRID
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

            marcador = new AdvancedMarkerElement({
                content: imagen,
                map: mapa,
                position: { lat: 25.7951169, lng: -108.99698492 }
            });

            let infowindow = new google.maps.InfoWindow({
                content: '<p style="margin: 0;" ><strong>' + <?php $_GET['id']; ?> + ' </strong>' + <?php $_GET['nombre']; ?> + '</p>'
            });

            marcador.addListener("click", () => {
                mapa.setZoom(18.5);
                mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
                mapa.panTo(usuarioLista['marcador'].position);

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