<?php
    session_start();

    if(!isset($_SESSION['usuario_mapa'])){
        header("Location: https://www.marverrefacciones.mx/login_mapa.php");
        exit();
    }

    switch($_SESSION["sucursal_mapa"]){
        case "Mochis":
            $latMarver = 25.794334;
            $lngMarver = -108.985983;
            break;
        case "Guasave":
            $latMarver = 25.571846;
            $lngMarver = -108.466774;
            break;
    }
?>
<!DOCTYPE html>
<html class="h-100" lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa</title>

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
                        <li class="list-group-item d-flex justify-content-between align-items-center" onclick="todosRepartidores();" >Todos</li>
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
                <strong id="txtIdRepartidor"></strong>
            </h5>
            <div class="card-body">
                <h5 class="card-title" id="txtNombreRepartidor"></h5>
                <p class="card-text mb-1" id="velocidadRepartidor"></p>
                <div>
                    <button id="btnBuscarRepartidor" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSelector">Buscar Repartidor</button>
                </div>
                <a class="btn btn-primary mt-2" style="min-width: 142px;" href="https://www.marverrefacciones.mx/excesos" target="_blank" >Excesos</a>
                <a class="btn btn-primary mt-2" style="min-width: 142px;" href="https://www.marverrefacciones.mx/repartidores" target="_blank">Repartidores</a>
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

<script>

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

        function calcularPuntoIntermedio(latitud1, longitud1, latitud2, longitud2, porcentaje) {
            // Convertir grados a radianes
            const lat1Rad = latitud1 * Math.PI / 180;
            const lon1Rad = longitud1 * Math.PI / 180;
            const lat2Rad = latitud2 * Math.PI / 180;
            const lon2Rad = longitud2 * Math.PI / 180;

            // Radio de la Tierra en metros (aproximado)
            const radioTierra = 6371 * 1000; // en metros

            // Calcular la distancia entre los dos puntos
            const distancia = Math.acos(Math.sin(lat1Rad) * Math.sin(lat2Rad) + Math.cos(lat1Rad) * Math.cos(lat2Rad) * Math.cos(lon2Rad - lon1Rad)) * radioTierra;

            // Calcular el punto intermedio
            const puntoIntermedioLatitud = latitud1 + (latitud2 - latitud1) * porcentaje;
            const puntoIntermedioLongitud = longitud1 + (longitud2 - longitud1) * porcentaje;

            return [puntoIntermedioLatitud, puntoIntermedioLongitud];
        }

        function calcularDistancia(lat1, lon1, lat2, lon2) {
            const radioTierraKm = 6371; // Radio de la Tierra en kilómetros
            const dLat = toRadians(lat2 - lat1);
            const dLon = toRadians(lon2 - lon1);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distanciaKm = radioTierraKm * c;
            const distanciaMetros = distanciaKm * 1000;
            return distanciaMetros;
        }

        function toRadians(grados) {
            return grados * Math.PI / 180;
        }

        let mapa;

        let repartidores = {};

        let repartidorSeguido = 0;
        let idRuta = 0;
        let fechaActualizacion = "";

        let polylineas = [];
        let marcadores = [];

        let infowindowMarver;
        let marcadorMarver;

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Polylinea;

        function actualizar() {

            let datosEnviar = new FormData();
            datosEnviar.append('web', '');
            datosEnviar.append('repartidor', repartidorSeguido);
            datosEnviar.append('id', idRuta);
            datosEnviar.append('fechaActualizacion', fechaActualizacion);
            datosEnviar.append('sucursal', '<?= $_SESSION['sucursal_mapa'] ?>');

            fetch('android/rutas_repartidores', {
                method: 'POST',
                body: datosEnviar
            })
            .then((respuesta) => {
                return respuesta.json();
            })
            .catch(error => {
                console.error('Error al solicitar las ruta de los repartidores: ', error);
                actualizar();
            })
            .then(json_api => {

                json_api['repartidores'].forEach( (repartidor) => {

                    if(repartidores.hasOwnProperty(repartidor['id'])){

                        repartidores[repartidor['id']]['marcador'].position = { lat: repartidor['latitud'], lng: repartidor['longitud'] };
                        repartidores[repartidor['id']]['velocidad'] = repartidor['velocidad'];
                    
                        if(repartidorSeguido == repartidor['id']){
                            document.getElementById('velocidadRepartidor').innerText = (repartidores[repartidor['id']]['velocidad'] * 3.6).toFixed(1) + ' Km/h';
                        }
                    }else{

                        let imagen = document.createElement('img');
                        imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador.png';

                        let marcador = new ElementoMarcadorAvanzado({
                            content: imagen,
                            map: mapa,
                            position: { lat: repartidor['latitud'], lng: repartidor['longitud'] },
                            zIndex: 3
                        });

                        let infowindow = new VentanaInformacion({
                            disableAutoPan: true,
                            content: '<p class="infoWindow" ><strong>' + repartidor['id'] + ' </strong> ' + repartidor['nombre'] + '</p>',
                            zIndex: 3
                        });

                        marcador.addListener("click", () => {
                            document.getElementById('txtIdRepartidor').innerText = repartidor['id'];
                            document.getElementById('txtNombreRepartidor').innerText = repartidor['nombre'];

                            infowindow.open({
                                anchor: marcador,
                                map: mapa,
                                zIndex: 3
                            });

                            repartidorSeguido = repartidor['id'];
                            Object.keys(repartidores).forEach( (id) => {
                                if(id != repartidorSeguido){
                                    repartidores[id]['marcador'].setMap(null);
                                }else{
                                    repartidores[id]['marcador'].setMap(mapa);
                                }
                            } );
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
                            idRuta = 0;
                            fechaActualizacion = "";
                            actualizar();
                        });

                        repartidores[repartidor['id']] = {};
                        repartidores[repartidor['id']]['id'] = repartidor['id'];
                        repartidores[repartidor['id']]['nombre'] = repartidor['nombre'];
                        repartidores[repartidor['id']]['marcador'] = marcador;
                        repartidores[repartidor['id']]['velocidad'] = repartidor['velocidad'];

                        let li = document.createElement('li');

                        li.addEventListener('click', () => {
                            document.getElementById('btnCerrarModal').click();

                            document.getElementById('txtIdRepartidor').innerText = repartidor['id'];
                            document.getElementById('txtNombreRepartidor').innerText = repartidor['nombre'];

                            infowindow.open({
                                anchor: marcador,
                                map: mapa,
                                zIndex: 3
                            });

                            repartidorSeguido = repartidor['id'];
                            Object.keys(repartidores).forEach( (id) => {
                                if(id != repartidorSeguido){
                                    repartidores[id]['marcador'].setMap(null);
                                }else{
                                    repartidores[id]['marcador'].setMap(mapa);
                                }
                            } );
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
                            idRuta = 0;
                            fechaActualizacion = "";
                            actualizar();
                        });

                        li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                        li.innerText = repartidor['nombre'];

                        let span = document.createElement('span');
                        span.classList.add('badge', 'bg-primary', 'rounded-pill');
                        span.innerText = repartidor['id'];

                        li.appendChild(span);
                        document.getElementById('listaRepartidores').appendChild(li);
                    }
                    } );

                    if( json_api.hasOwnProperty('marver') ){

                        idRuta = json_api['marver']['id'];
                        fechaActualizacion = json_api['marver']['fechaActualizacion'];

                        polylineas.forEach( (polylineaCiclo)=>{
                            polylineaCiclo.setMap(null);
                        });
                        polylineas = [];
                        marcadores.forEach( (marcadoresCiclo)=>{
                            marcadoresCiclo.setMap(null);
                        });
                        marcadores = [];

                        infowindowMarver.setContent('<p class="infoWindow" >' + 
                                '<strong>Inicio: </strong> ' + convertirFormato(json_api['marver']['fechaInicio']) + '<br>' +
                                '<strong>Llegada Estimada: </strong> ' + convertirFormato(json_api['marver']['fechaLlegadaEstimada'])
                            + '</p>');

                        let indice = 1;
                        let entregaActualEncontrada = false;
                        for(let c = 0; c < json_api['pedidos'].length; c++){

                            let pedido = json_api['pedidos'][c];

                            let tipo = pedido['status'];
                            if(tipo.includes("NO ENTREGADO") || tipo.includes("RECHAZADO")){
                                tipo = "rechazado";
                            }else if(tipo.includes("ENTREGADO")){
                                tipo = "entregado";
                            }else{
                                tipo = "pendiente";
                            }

                            let colorPolylinea = "#FF0000";
                            let indicePolylinea = 0;
                            if(!entregaActualEncontrada && tipo == "pendiente"){
                                colorPolylinea = "#87CEEB";
                                indicePolylinea = 1;
                                entregaActualEncontrada = true;
                            }

                            let infowindow;
                            if(pedido['tipoComprobante'] != 3){
                                infowindow = new VentanaInformacion({
                                    disableAutoPan: true,
                                    content: '<p class="infoWindow" >' + 
                                    "<strong>Pedido Normal</strong><br>" + 
                                    "<strong>Llegada estimada: </strong>" + convertirFormato(pedido["fechaLlegadaEstimada"]) + "<br>" +
                                    "<strong>Llegada: </strong>" + convertirFormato(pedido["fechaLlegada"]) + "<br>" +
                                    "<strong>Eficiencia: </strong>" + formatoTiempo(pedido["fechaLlegadaEficiencia"]) + "<br>" +
                                    "<strong>Status: </strong>" + pedido["status"] + "<br>" +
                                    "<strong>Pedido: </strong>" + pedido["pedido"] + "<br>" +
                                    "<strong>Cliente: </strong>" + pedido["clienteClave"] + " " + pedido["clienteNombre"] + "<br>" +
                                    "<strong>Calle: </strong>" + pedido["calle"] + "<br>" +
                                    "<strong>Colonia: </strong>" + pedido["colonia"] + "<br>" +
                                    "<strong>Codigo postal: </strong>" + pedido["codigoPostal"] + "<br>" +
                                    "<strong>Número exterior: </strong>" + pedido["numeroExterior"] + "<br>" +
                                    "<strong>Número interior: </strong>" + pedido["numeroInterior"] + "<br>" +
                                    "<strong>Observaciones: </strong>" + pedido["observacionesUbicacion"] + "<br>" +

                                    "<strong>Folio: </strong>" + pedido["folioComprobante"] + "<br>" +
                                    "<strong>Comprobante: </strong>" + pedido["tipoComprobante"] + "<br>" +
                                    "<strong>Codigos: </strong>" + pedido["codigos"] + "<br>" +
                                    "<strong>Unidades: </strong>" + pedido["piezas"] + "<br>" +
                                    "<strong>Total: </strong>" + pedido["total"] + "<br>" +
                                    "<strong>Observaciones: </strong>" + pedido["observacionesPedido"]
                                    + '</p>',
                                    zIndex: 4
                                });
                            }else{
                                infowindow = new VentanaInformacion({
                                    disableAutoPan: true,
                                    content: '<p class="infoWindow" >' + 
                                    "<strong>Pedido Especial</strong><br>" + 
                                    "<strong>Llegada estimada: </strong>" + convertirFormato(pedido["fechaLlegadaEstimada"]) + "<br>" +
                                    "<strong>Llegada: </strong>" + convertirFormato(pedido["fechaLlegada"]) + "<br>" +
                                    "<strong>Eficiencia: </strong>" + formatoTiempo(pedido["fechaLlegadaEficiencia"]) + "<br>" +
                                    "<strong>Status: </strong>" + pedido["status"] + "<br>" +
                                    "<strong>Pedido: </strong>" + pedido["pedido"] + "<br>" +
                                    "<strong>Cliente: </strong>" + pedido["clienteClave"] + " " + pedido["clienteNombre"] + "<br>" +
                                    "<strong>Calle: </strong>" + pedido["calle"] + "<br>" +
                                    "<strong>Colonia: </strong>" + pedido["colonia"] + "<br>" +
                                    "<strong>Codigo postal: </strong>" + pedido["codigoPostal"] + "<br>" +
                                    "<strong>Número exterior: </strong>" + pedido["numeroExterior"] + "<br>" +
                                    "<strong>Número interior: </strong>" + pedido["numeroInterior"] + "<br>" +
                                    "<strong>Observaciones: </strong>" + pedido["observacionesUbicacion"] + "<br>" +

                                    "<strong>Observaciones: </strong>" + pedido["observacionesPedido"]
                                    + '</p>',
                                    zIndex: 4
                                });
                            }

                            let marcadorCercano = marcadores.find( m => m.position.lat == parseFloat(pedido["latitud"]) && m.position.lng == parseFloat(pedido["longitud"]) );
                            if( marcadorCercano ){
                                marcadorCercano.infowindow.setContent( marcadorCercano.infowindow.getContent() + '<br>' + infowindow.getContent() );
                            }else{
                                let imagen = document.createElement('img');
                                imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/' + tipo + "_" + indice + '.png';

                                let marcador = new ElementoMarcadorAvanzado({
                                    content: imagen,
                                    map: mapa,
                                    position: { lat: pedido["latitud"], lng: pedido["longitud"] },
                                    zIndex: 4
                                });

                                marcador["infowindow"] = infowindow;

                                marcador.addListener("click", () => {
                                    infowindow.open({
                                        anchor: marcador,
                                        map: mapa,
                                        zIndex: 4
                                    });
                                });

                                marcadores.push(marcador);

                                indice++;
                            }

                            let polylinea = new Polylinea({
                                path: decodePolyline(pedido["polylineaCodificada"]),
                                geodesic: true,
                                strokeColor: colorPolylinea,
                                strokeOpacity: 1.0,
                                strokeWeight: 3,
                                zIndex: indicePolylinea
                            });
                            polylinea.setMap(mapa);
                            polylineas.push(polylinea);

                        }

                        let colorPolylinea = "#FF0000";
                        let indicePolylinea = 0;
                        if(!entregaActualEncontrada){
                            colorPolylinea = "#87CEEB";
                            indicePolylinea = 1;
                            entregaActualEncontrada = true;
                        }

                        let polylinea = new Polylinea({
                            path: decodePolyline(json_api['marver']["polylineaCodificada"]),
                            geodesic: true,
                            strokeColor: colorPolylinea,
                            strokeOpacity: 1.0,
                            strokeWeight: 3,
                            zIndex: indicePolylinea
                        });
                        polylinea.setMap(mapa);
                        polylineas.push(polylinea);
                    }

            });
        }

        function todosRepartidores(){

            document.getElementById('txtIdRepartidor').innerText = "";
            document.getElementById('txtNombreRepartidor').innerText = "";
            document.getElementById('velocidadRepartidor').innerText = "";
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
            Object.keys(repartidores).forEach( (id) => {
                repartidores[id]['marcador'].setMap(mapa);
            } );

            repartidorSeguido = 0;
            idRuta = 0;
            fechaActualizacion = "";

            document.getElementById('btnCerrarModal').click();
        }

        document.getElementById('modalSelector').addEventListener('hidden.bs.modal', function () {
            setTimeout(() => {
                document.getElementById('btnBuscarRepartidor').blur();
            }, 1000);
        });
</script>

    <script>(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo", v: "weekly" });</script>

    <script type="module">

        async function initMap() {
            const { Map, InfoWindow, Polyline } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            const { LatLngBounds } = await google.maps.importLibrary("core");

            ElementoMarcadorAvanzado = AdvancedMarkerElement;
            VentanaInformacion = InfoWindow;
            Polylinea = Polyline;

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: <?= $latMarver ?>, lng: <?= $lngMarver ?> },
                zoom: 13.36,
                mapId: '7845e7dffe8cea37'
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_marver.png';

            marcadorMarver = new ElementoMarcadorAvanzado({
                content: imagen,
                map: mapa,
                position: { lat: <?= $latMarver ?>, lng: <?= $lngMarver ?> },
                zIndex: 2
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
                    zIndex: 2
                });
            });

            actualizar();
            setInterval(()=>{actualizar();},2500);
        }

        initMap();
    </script>
</body>

</html>