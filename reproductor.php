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

        $preparada = $conexion->prepare("SELECT
               id AS id,
               fecha_actualizacion AS fechaActualizacion,
               fecha_inicio AS fechaInicio,
               fecha_llegada_estimada AS fechaLlegadaEstimada,
               fecha_fin AS fechaFin,
               fecha_llegada_eficiencia AS fechaLlegadaEficiencia,
               polylinea_codificada AS polylineaCodificada,
               segundos_estimados_sumatoria AS segundosEstimadosSumatoria,
               metros_estimados_sumatoria AS metrosEstimadosSumatoria,
               25.7941814 AS latitud,
               -108.9858957 AS longitud
           FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio >= :fecha_inicial AND fecha_inicio < DATEADD(DAY, 1, :fecha_final) AND fecha_fin IS NOT NULL
           ORDER BY fecha_inicio ASC;
        ");
        $preparada->bindValue(':fecha_inicial', $_GET['fecha']);
        $preparada->bindValue(':fecha_final', $_GET['fecha']);
        $preparada->bindValue(':repartidor', $_GET['id']);
        $preparada->execute();

        $pedidos = $preparada->fetchAll(PDO::FETCH_ASSOC);

        for($c = 0; $c < count($pedidos); $c++){
                    $preparada = $conexion->prepare("SELECT
                        pr.indice AS indice,
                        pr.polylinea_codificada AS polylineaCodificada,
                        pr.fecha_llegada_estimada AS fechaLlegadaEstimada,
                        pr.fecha_llegada AS fechaLlegada,
                        pr.fecha_llegada_eficiencia AS fechaLlegadaEficiencia,
                        en.Extra2 AS status,
                        REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, en.Fecha) , ' ', en.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fechaAsignacion,
                        pc.folio AS pedido,
                        pc.FolioComprobante AS folioComprobante,
                        pc.Tipocomprobante AS tipoComprobante,
                        pc.Observacion AS observacionesPedido,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN cl.Clave
                            ELSE ue.clave
                        END AS clienteClave,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN cl.Razon_Social
                            ELSE ue.nombre
                        END AS clienteNombre,
                        en.Responsable AS repartidor,
                        pc.CodigosFacturado AS codigos,
                        pc.UnidadesFacturado AS piezas,
                        pc.TotalFacturado AS total,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.latitud
                            ELSE ue.latitud
                        END AS latitud,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.longitud
                            ELSE ue.longitud
                        END AS longitud,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.calle
                            ELSE ue.calle
                        END AS calle,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.colonia
                            ELSE ue.colonia
                        END AS colonia,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.codigo_postal
                            ELSE ue.codigo_postal
                        END AS codigoPostal,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.numero_exterior
                            ELSE ue.numero_exterior
                        END AS numeroExterior,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.numero_interior
                            ELSE ue.numero_interior
                        END AS numeroInterior,
                        CASE WHEN pc.Tipocomprobante != 3
                            THEN un.observaciones
                            ELSE ue.observaciones
                        END AS observacionesUbicacion,
                        CASE
                            WHEN en.Extra2 = 'PENDIENTE' THEN NULL
                            WHEN en.Extra2 = 'EN RUTA' THEN mv.Importe * -1
                            ELSE mv.Feria + (mv.Importe * -1)
                        END AS feria
                    FROM pedidos_repartidores pr
                    INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio
                    INNER JOIN EnvioPedidoCliente en ON en.Extra1 = pr.id
                    LEFT JOIN Clientes cl ON cl.Clave = pc.Cliente
                    LEFT JOIN clientes_posiciones un ON un.clave = pc.Cliente
                    LEFT JOIN ubicaciones_especiales ue ON ue.clave = pc.Cliente
                    LEFT JOIN MoviemientosVenta mv ON mv.Folio = pc.FolioComprobante AND mv.TipoComprobante = 11 AND mv.Importe < 0
                    WHERE pr.ruta_repartidor = :ruta_repartidor
                    ORDER BY pr.indice ASC;
            ");
            $preparada->bindValue(':ruta_repartidor', $pedidos[$c]["id"]);
            $preparada->execute();

            $pedidos[$c]["entregas"] = $preparada->fetchAll(PDO::FETCH_ASSOC);
        }

        echo '<script>';
        echo 'let pedidos = ' . json_encode($pedidos, JSON_UNESCAPED_UNICODE) . ';';
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

    <style>
        .infoWindow{
            margin: 0;
            font-size: 15px;
            color: black;
            font-weight: 400;
        }

        .dinero{
            margin: 0;
            color: green;
        }

        .dinero::before{
            content: '$ ';
        }
    </style>

    <style>
        .barraRutas{
            position: fixed;
            width: 250px;
            height: 100%;
            background: blue;
            z-index: 1000;
        }
    </style>
</head>

<body class="h-100">

        <div class="barraRutas" >

        </div>

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

                <button onclick="verRutas();" class="btn btn-primary mb-3 mt-2"><i class="fa-solid fa-eye" id="icono_ver_rutas" ></i> Ver Rutas</button>

                <div class="d-flex justify-content-center gap-2" >
                    <button onclick="pausar();" class="btn btn-primary"><i class="fa-solid fa-play" id="icono_pausar" ></i></button>
                    <button onclick="retroceder();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-left"></i></button>
                    <button onclick="velocidadMaxima();" class="btn btn-primary"><i class="fa-solid fa-triangle-exclamation"></i></button>
                    <button onclick="adelantar();" class="btn btn-primary"><i class="fa-solid fa-arrow-rotate-right"></i></button>
                </div>

                <a class="btn btn-primary mt-3" href="https://www.marverrefacciones.mx/mapa" target="_blank" >Rastreo</a>
                <a class="btn btn-primary mt-3 ms-1 me-1" href="https://www.marverrefacciones.mx/repartidores" target="_blank" >Repartidores</a>
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

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Polylinea;

        let mapa;
        let velocidadRepartidor;
        let velocidadMaximaTimeStamp;
        let txtFecha;
        let marcadorRepartidor;
        let marcadorMarver;
        let cursor;
        let pausado = true;
        let infowindowMarver;

        let polylineas = [];
        let marcadores = [];

        let idPedido = 0;

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

            marcadorRepartidor.position = { lat: posicionActual()['latitud'], lng: posicionActual()['longitud'] };
            mapa.panTo(marcadorRepartidor.position);

            let pedido = pedidoActual();
            if(pedido){

                if(pedido["id"] != idPedido){
                    idPedido = pedido["id"];

                    infowindowMarver.setContent('<p class="infoWindow" >' + 
                                '<strong>Inicio: </strong> ' + convertirFormato(pedido['fechaInicio']) + '<br>' +
                                '<strong>Llegada Estimada: </strong> ' + convertirFormato(pedido['fechaLlegadaEstimada']) + '<br>' +
                                '<strong>LLegada: </strong> ' + convertirFormato(pedido['fechaFin']) + '<br>' +
                                '<strong>Eficiencia: </strong> ' + formatoTiempo(pedido["fechaLlegadaEficiencia"])
                            + '</p>');

                    let polylinea = new Polylinea({
                        path: decodePolyline(pedido["polylineaCodificada"]),
                        geodesic: true,
                        strokeColor: "#FF0000",
                        strokeOpacity: 1.0,
                        strokeWeight: 3,
                        zIndex: 1
                    });
                    polylinea.setMap(mapa);
                    polylineas.push(polylinea);

                    let indice = 1;
                    pedido["entregas"].forEach(entrega => {
                        polylinea = new Polylinea({
                            path: decodePolyline(entrega["polylineaCodificada"]),
                            geodesic: true,
                            strokeColor: "#FF0000",
                            strokeOpacity: 1.0,
                            strokeWeight: 3,
                            zIndex: 1
                        });
                        polylinea.setMap(mapa);
                        polylineas.push(polylinea);

                        let infowindow;
                        if(entrega["tipoComprobante"] != 3){
                            infowindow = new VentanaInformacion({
                                disableAutoPan: true,
                                content: '<p class="infoWindow" >' + 
                                "<strong>Pedido Normal</strong><br>" + 
                                "<strong>Llegada estimada: </strong>" + convertirFormato(entrega["fechaLlegadaEstimada"]) + "<br>" +
                                "<strong>Llegada: </strong>" + convertirFormato(entrega["fechaLlegada"]) + "<br>" +
                                "<strong>Eficiencia: </strong>" + formatoTiempo(entrega["fechaLlegadaEficiencia"]) + "<br>" +
                                "<strong>Status: </strong>" + entrega["status"] + "<br>" +
                                "<strong>Pedido: </strong>" + entrega["pedido"] + "<br>" +
                                "<strong>Cliente: </strong>" + entrega["clienteClave"] + " " + entrega["clienteNombre"] + "<br>" +
                                "<strong>Calle: </strong>" + entrega["calle"] + "<br>" +
                                "<strong>Colonia: </strong>" + entrega["colonia"] + "<br>" +
                                "<strong>Codigo postal: </strong>" + entrega["codigoPostal"] + "<br>" +
                                "<strong>Número exterior: </strong>" + entrega["numeroExterior"] + "<br>" +
                                "<strong>Número interior: </strong>" + entrega["numeroInterior"] + "<br>" +
                                "<strong>Observaciones: </strong>" + entrega["observacionesUbicacion"] + "<br>" +

                                "<strong>Folio: </strong>" + entrega["folioComprobante"] + "<br>" +
                                "<strong>Comprobante: </strong>" + entrega["tipoComprobante"] + "<br>" +
                                "<strong>Codigos: </strong>" + entrega["codigos"] + "<br>" +
                                "<strong>Unidades: </strong>" + entrega["piezas"] + "<br>" +
                                "<strong>Total: </strong>" + entrega["total"] + "<br>" +
                                "<strong>Observaciones: </strong>" + entrega["observacionesPedido"]
                                + '</p>',
                                zIndex: 3
                            });
                        }else{
                            let infowindow = new VentanaInformacion({
                                disableAutoPan: true,
                                content: '<p class="infoWindow" >' + 
                                "<strong>Pedido Especial</strong><br>" + 
                                "<strong>Llegada estimada: </strong>" + convertirFormato(entrega["fechaLlegadaEstimada"]) + "<br>" +
                                "<strong>Llegada: </strong>" + convertirFormato(entrega["fechaLlegada"]) + "<br>" +
                                "<strong>Eficiencia: </strong>" + formatoTiempo(entrega["fechaLlegadaEficiencia"]) + "<br>" +
                                "<strong>Status: </strong>" + entrega["status"] + "<br>" +
                                "<strong>Pedido: </strong>" + entrega["pedido"] + "<br>" +
                                "<strong>Cliente: </strong>" + entrega["clienteClave"] + " " + entrega["clienteNombre"] + "<br>" +
                                "<strong>Calle: </strong>" + entrega["calle"] + "<br>" +
                                "<strong>Colonia: </strong>" + entrega["colonia"] + "<br>" +
                                "<strong>Codigo postal: </strong>" + entrega["codigoPostal"] + "<br>" +
                                "<strong>Número exterior: </strong>" + entrega["numeroExterior"] + "<br>" +
                                "<strong>Número interior: </strong>" + entrega["numeroInterior"] + "<br>" +
                                "<strong>Observaciones: </strong>" + entrega["observacionesUbicacion"] + "<br>" +

                                "<strong>Observaciones: </strong>" + entrega["observacionesPedido"]
                                + '</p>',
                                zIndex: 3
                            });
                        }

                        let imagen = document.createElement('img');
                        imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/pendiente_' + indice + '.png';

                        let marcadorEntrega = new ElementoMarcadorAvanzado({
                            content: imagen,
                            map: mapa,
                            position: { lat: parseFloat(entrega["latitud"]), lng: parseFloat(entrega["longitud"]) },
                            zIndex: 3
                        });
                        marcadorEntrega["idPedido"] = entrega["pedido"];

                        marcadorEntrega.addListener("click", () => {
                            infowindow.open({
                                anchor: marcadorEntrega,
                                map: mapa,
                            });
                        });

                        marcadores.push(marcadorEntrega);

                        indice++;
                    });

                    actualizar_todo();
                }else{
                    let indice = 1;
                    pedido["entregas"].forEach(entrega => {

                        if(entrega["fechaLlegada"]){
                            let marcadorEntrega = marcadores.find(marcadorEntrega=>marcadorEntrega["idPedido"] == entrega["pedido"]);
                            if( new Date(cursor.valueAsNumber) >= new Date(entrega["fechaLlegada"]) ){
                                let src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/entregado_' + indice + '.png';

                                if(marcadorEntrega.content.src != src){
                                    marcadorEntrega.content.src = src;
                                }
                            }else{
                                let src ='https://www.marverrefacciones.mx/android/marcadores_ruta/pendiente_' + indice + '.png';

                                if(marcadorEntrega.content.src != src){
                                    marcadorEntrega.content.src = src;
                                }
                            }
                        }
                        indice++;
                    });
                }

            }else{
                idPedido = 0;

                polylineas.forEach( (polylineaCiclo)=>{
                    polylineaCiclo.setMap(null);
                });
                polylineas = [];
                marcadores.forEach( (marcadoresCiclo)=>{
                    marcadoresCiclo.setMap(null);
                });
                marcadores = [];
            
                infowindowMarver.setContent('<p class="infoWindow" ></p>');
                infowindowMarver.close();
            }
        }

        function velocidadMaxima(){
            cursor.valueAsNumber = velocidadMaximaTimeStamp;
            idPedido = 0;

            polylineas.forEach( (polylineaCiclo)=>{
                polylineaCiclo.setMap(null);
            });
            polylineas = [];
            marcadores.forEach( (marcadoresCiclo)=>{
                marcadoresCiclo.setMap(null);
            });
            marcadores = [];

            infowindowMarver.setContent('<p class="infoWindow" ></p>');
            infowindowMarver.close();
            actualizar_todo();
        }

        function posicionActual(){
            let posicion = posiciones.find(posicion=>new Date(posicion["fecha"])>=new Date(cursor.valueAsNumber));
            if (posicion) {
                return posicion;
            }
            return posiciones.at(-1);
        }

        function pedidoActual(){
            return pedidos.find(pedido=>new Date(pedido["fechaFin"])>=new Date(cursor.valueAsNumber) && new Date(pedido["fechaInicio"])<=new Date(cursor.valueAsNumber));
            // let pedido = pedidos.find(pedido=>new Date(pedido["fechaInicio"])<=new Date(cursor.valueAsNumber));
            // if (pedido) {
            //     return pedido;
            // }
            // return pedidos.at(-1);
        }

        function procesar_vista() {
            if(pausado){
                return;
            }

            cursor.valueAsNumber += 1000;
            actualizar_todo();
        }

        function decodePolyline(encoded) {
            let poly = [];
            let index = 0, len = encoded.length;
            let lat = 0, lng = 0;

            while (index < len) {
                let b, shift = 0, result = 0;
                do {
                    b = encoded.charCodeAt(index++) - 63;
                    result |= (b & 0x1f) << shift;
                    shift += 5;
                } while (b >= 0x20);
                let dlat = ((result >> 1) ^ -(result & 1));
                lat += dlat;

                shift = 0;
                result = 0;
                do {
                    b = encoded.charCodeAt(index++) - 63;
                    result |= (b & 0x1f) << shift;
                    shift += 5;
                } while (b >= 0x20);
                let dlng = ((result >> 1) ^ -(result & 1));
                lng += dlng;

                poly.push({ lat: lat / 1E5, lng: lng / 1E5 });
            }
            return poly;
        }

        function convertirFormato(fecha) {
            if(!fecha){
                return "";
            }
            // Separar la fecha y la hora
            const [fechaParte, horaParte] = fecha.split(" ");
            
            // Separar horas, minutos y segundos (ignoramos milisegundos si existen)
            let [hora, minutos, segundos] = horaParte.split(":");

            // Eliminar milisegundos si vienen en el string (caso: "HH:mm:ss.fff")
            if (segundos.includes(".")) {
                segundos = segundos.split(".")[0]; // Tomamos solo la parte antes de los milisegundos
            }
            
            // Convertir la hora de string a número
            hora = parseInt(hora);
            
            // Determinar si es AM o PM
            const periodo = hora >= 12 ? "PM" : "AM";
            
            // Convertir la hora al formato de 12 horas
            hora = hora % 12 || 12; // Si es 0, se cambia a 12 (caso especial de la medianoche)
            
            // Formatear la nueva hora
            const nuevaHora = `${hora.toString().padStart(2, '0')}:${minutos}:${segundos} ${periodo}`;
            
            // Retornar el nuevo formato con la fecha original
            return `${fechaParte} ${nuevaHora}`;
        }

        function formatoTiempo(segundos) {
            // Verificar si el número es negativo
            const negativo = segundos < 0;
            // Convertir a positivo para hacer los cálculos
            segundos = Math.abs(segundos);

            // Calcular horas, minutos y segundos
            const horas = Math.floor(segundos / 3600);
            const minutos = Math.floor((segundos % 3600) / 60);
            const seg = segundos % 60;

            // Formatear horas, minutos y segundos con ceros a la izquierda si es necesario
            const formatoHoras = horas.toString().padStart(2, '0');
            const formatoMinutos = minutos.toString().padStart(2, '0');
            const formatoSegundos = seg.toString().padStart(2, '0');

            // Armar el string en formato "horas:minutos:segundos"
            const resultado = `${formatoHoras}:${formatoMinutos}:${formatoSegundos}`;

            // Devolver el resultado con signo negativo si es necesario
            return negativo ? `-${resultado}` : resultado;
        }
    </script>

    <script type="module">

        async function initMap() {
            const { Map, InfoWindow, Polyline } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            ElementoMarcadorAvanzado = AdvancedMarkerElement;
            VentanaInformacion = InfoWindow;
            Polylinea = Polyline;

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

            let imagenMarver = document.createElement('img');
            imagenMarver.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_marver.png';

            marcadorMarver = new ElementoMarcadorAvanzado({
                content: imagenMarver,
                map: mapa,
                position: { lat: 25.7943047, lng: -108.9859510 },
                zIndex: 3
            });

            infowindowMarver = new VentanaInformacion({
                disableAutoPan: true,
                content: '<p class="infoWindow" ></p>',
                zIndex: 2
            });

            marcadorMarver.addListener("click", () => {
                infowindowMarver.open({
                    anchor: marcadorMarver,
                    map: mapa,
                });
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador.png';

            marcadorRepartidor = new ElementoMarcadorAvanzado({
                content: imagen,
                map: mapa,
                position: { lat: posicionActual()['latitud'], lng: posicionActual()['longitud'] }
            });

            let infowindow = new VentanaInformacion({
                content: '<p style="margin: 0;" ><strong><?= $_GET['id']; ?> </strong><?= $_GET['nombre']; ?></p>'
            });

            marcadorRepartidor.addListener("click", () => {
                mapa.setZoom(14);
                mapa.panTo(marcadorRepartidor.position);

                infowindow.open({
                    anchor: marcadorRepartidor,
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