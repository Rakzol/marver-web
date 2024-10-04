<?php
    session_start();

    if(!isset($_SESSION['usuario_mapa'])){
        header("Location: https://www.marverrefacciones.mx/login_mapa.php");
        exit();
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
            // Separar la fecha y la hora
            const [fechaParte, horaParte] = fecha.split(" ");
            
            // Separar horas, minutos y segundos
            let [hora, minutos, segundos] = horaParte.split(":");
            
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

        let json_api;
        let repartidores = {};

        let id_actualizar;

        let repartidorSeguido = 0;
        let idRuta = 0;
        let fechaActualizacion = "";

        let max_frame = 250;
        let frame = max_frame + 1;

        let polylineas = [];
        let marcadores = [];

        let infowindowMarver;
        let marcadorMarver;

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Polylinea;

        function actualizar() {

            if(frame <= max_frame){

                if( frame == 0 ){

                    json_api['repartidores'].forEach( (repartidor) => {

                        if(repartidores.hasOwnProperty(repartidor['id'])){

                            repartidores[repartidor['id']]['latitudInicial'] = repartidores[repartidor['id']]['marcador']['position']['lat'];
                            repartidores[repartidor['id']]['longitudInicial'] = repartidores[repartidor['id']]['marcador']['position']['lng'];
                            repartidores[repartidor['id']]['latitudObjetivo'] = repartidor['latitud'];
                            repartidores[repartidor['id']]['longitudObjetivo'] = repartidor['longitud'];
                            repartidores[repartidor['id']]['velocidad'] = repartidor['velocidad'];

                        }else{

                            let imagen = document.createElement('img');
                            imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador.png';

                            let marcador = new ElementoMarcadorAvanzado({
                                content: imagen,
                                map: mapa,
                                position: { lat: repartidor['latitud'], lng: repartidor['longitud'] },
                                zIndex: 1
                            });

                            let infowindow = new VentanaInformacion({
                                disableAutoPan: true,
                                content: '<p class="infoWindow" ><strong>' + repartidor['id'] + ' </strong> ' + repartidor['nombre'] + '</p>',
                                zIndex: 1
                            });

                            marcador.addListener("click", () => {
                                document.getElementById('txtIdRepartidor').innerText = repartidor['id'];
                                document.getElementById('txtNombreRepartidor').innerText = repartidor['nombre'];
                                document.getElementById('velocidadRepartidor').innerText = (repartidores[repartidor['id']]['velocidad'] * 3.6).toFixed(1) + ' Km/h';

                                infowindow.open({
                                    anchor: marcador,
                                    map: mapa,
                                    zIndex: -2
                                });

                                clearTimeout(id_actualizar);
                                frame = max_frame + 1;
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

                            repartidores[repartidor['id']]['latitudInicial'] = repartidor['latitud'];
                            repartidores[repartidor['id']]['longitudInicial'] = repartidor['longitud'];
                            repartidores[repartidor['id']]['latitudObjetivo'] = repartidor['latitud'];
                            repartidores[repartidor['id']]['longitudObjetivo'] = repartidor['longitud'];
                            repartidores[repartidor['id']]['velocidad'] = repartidor['velocidad'];

                            let li = document.createElement('li');

                            li.addEventListener('click', () => {
                                document.getElementById('btnCerrarModal').click();

                                document.getElementById('txtIdRepartidor').innerText = repartidor['id'];
                                document.getElementById('txtNombreRepartidor').innerText = repartidor['nombre'];
                                document.getElementById('velocidadRepartidor').innerText = ( repartidores[repartidor['id']]['velocidad'] * 3.6).toFixed(1) + ' Km/h';

                                infowindow.open({
                                    anchor: marcador,
                                    map: mapa,
                                    zIndex: -2
                                });

                                clearTimeout(id_actualizar);
                                frame = max_frame + 1;
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

                            if(pedido['tipoComprobante'] != 3){

                                let imagen = document.createElement('img');
                                imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/' + tipo + "_" + ( pedido["indice"] + 1 ) + '.png';

                                let marcador = new ElementoMarcadorAvanzado({
                                    content: imagen,
                                    map: mapa,
                                    position: { lat: pedido["latitud"], lng: pedido["longitud"] },
                                    zIndex: 2
                                });

                                let infowindow = new VentanaInformacion({
                                    disableAutoPan: true,
                                    content: '<p class="infoWindow" >' + 
                                    "<strong>Pedido Normal</strong><br>" + 
                                    "<strong>Llegada estimada: </strong>" + pedido["fechaLlegadaEstimada"] + "<br>" +
                                    "<strong>Llegada: </strong>" + pedido["fechaLlegada"] + "<br>" +
                                    "<strong>Eficiencia: </strong>" + pedido["fechaLlegadaEficiencia"] + "<br>" +
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
                                    zIndex: 3
                                });

                                marcador.addListener("click", () => {
                                    infowindow.open({
                                        anchor: marcador,
                                        map: mapa,
                                    });
                                });

                                marcadores.push(marcador);

                            }else{
  
                                let imagen = document.createElement('img');
                                imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/' + tipo + "_" + ( pedido["indice"] + 1 ) + '.png';

                                let marcador = new ElementoMarcadorAvanzado({
                                    content: imagen,
                                    map: mapa,
                                    position: { lat: pedido["latitud"], lng: pedido["longitud"] },
                                    zIndex: 2
                                });

                                let infowindow = new VentanaInformacion({
                                    disableAutoPan: true,
                                    content: '<p class="infoWindow" >' + 
                                    "<strong>Pedido Especial</strong><br>" + 
                                    "<strong>Llegada estimada: </strong>" + pedido["fechaLlegadaEstimada"] + "<br>" +
                                    "<strong>Llegada: </strong>" + pedido["fechaLlegada"] + "<br>" +
                                    "<strong>Eficiencia: </strong>" + pedido["fechaLlegadaEficiencia"] + "<br>" +
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
                                    zIndex: 3
                                });

                                marcador.addListener("click", () => {
                                    infowindow.open({
                                        anchor: marcador,
                                        map: mapa,
                                    });
                                });

                                marcadores.push(marcador);

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

                }else{

                    Object.keys(repartidores).forEach( (id) => {

                        let posicion_nueva = calcularPuntoIntermedio( repartidores[id]['latitudInicial'], repartidores[id]['longitudInicial'], repartidores[id]['latitudObjetivo'], repartidores[id]['longitudObjetivo'], frame / max_frame );
                        repartidores[id]['marcador'].position = { lat: posicion_nueva[0], lng: posicion_nueva[1] };

                    } );

                }

                frame++;
            }

            if(frame > max_frame){
                let datosEnviar = new FormData();
                datosEnviar.append('web', '');
                datosEnviar.append('repartidor', repartidorSeguido);
                datosEnviar.append('id', idRuta);
                datosEnviar.append('fechaActualizacion', fechaActualizacion);

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
                .then(respuesta_json => {
                    frame = 0;
                    json_api = respuesta_json;
                    actualizar();
                });
            }
            else{
                id_actualizar = setTimeout(actualizar, 10);
            }
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
                center: { lat: 25.7951169, lng: -108.99698492 },
                zoom: 13.36,
                mapId: '7845e7dffe8cea37'
            });

            let imagen = document.createElement('img');
            imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_marver.png';

            marcadorMarver = new ElementoMarcadorAvanzado({
                content: imagen,
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

            actualizar();
        }

        initMap();
    </script>
</body>

</html>