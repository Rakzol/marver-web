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
                <p class="card-text mb-1" id="velocidadRepartidor">0.0 Km/h</p>
                <div>
                    <button id="btnBuscarRepartidor" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSelector">Buscar Repartidor</button>
                    <div class="form-check form-switch d-inline-block ms-2 mt-2" >
                        <input class="form-check-input" id="seguirRepartidor" type="checkbox" role="switch" id="flexSwitchCheckChecked">
                        <label class="form-check-label ms-1" for="flexSwitchCheckChecked">Seguir repartidor</label>
                    </div>
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

        let max_frame = 2500;
        let frame = max_frame + 1;

        let velocidadRepartidor;
        let seguirRepartidor;

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Esferica;
        let Codificador;
        let Polilinea;
        let LimitesLatitudLongitud;

        function actualizar() {

            if(frame <= max_frame){

                if( frame == 0 ){

                    json_api['repartidores'].forEach( (repartidor) => {

                        if(repartidores.hasOwnProperty(repartidor['id'])){

                            repartidores[repartidor['id']]['polilinea'] = repartidor['polilinea'];
                            repartidores[repartidor['id']]['distancia'] = repartidor['distancia'];

                        }else{

                            let imagen = document.createElement('img');
                            imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

                            let marcador = new ElementoMarcadorAvanzado({
                                content: imagen,
                                map: mapa,
                                position: { lat: repartidor['polilinea'][0][1], lng: repartidor['polilinea'][0][0] }
                            });

                            let infowindow = new VentanaInformacion({
                                content: '<p style="margin: 0;" ><strong>' + repartidor['id'] + ' </strong> ' + repartidor['nombre'] + '</p>'
                            });

                            marcador.addListener("click", () => {
                                infowindow.open({
                                    anchor: marcador,
                                    map: mapa,
                                });
                            });

                            repartidores[repartidor['id']] = {};
                            repartidores[repartidor['id']]['id'] = repartidor['id'];
                            repartidores[repartidor['id']]['nombre'] = repartidor['nombre'];
                            repartidores[repartidor['id']]['marcador'] = marcador;
                            repartidores[repartidor['id']]['polilinea'] = repartidor['polilinea'];
                            repartidores[repartidor['id']]['distancia'] = repartidor['distancia'];
                        }
                    } );

                }else{

                    Object.keys(repartidores).forEach( (id) => {

                        let metro_recorrer_todo_frame = frame / max_frame * repartidores[id]['distancia'];
                        let metros_recorridos = 0;

                        for(let c = 0; c < repartidores[id]['polilinea'].length - 1; c++ ){

                            let punto_inicial = {lat: repartidores[id]['polilinea'][c][1], lng: repartidores[id]['polilinea'][c][0]};
                            let punto_final = {lat: repartidores[id]['polilinea'][c+1][1], lng: repartidores[id]['polilinea'][c+1][0]};
                            let metros_entre_puntos = calcularDistancia( punto_inicial['lat'], punto_inicial['lng'], punto_final['lat'], punto_final['lng']);

                            metros_recorridos += metros_entre_puntos;

                            if( metros_recorridos >= metro_recorrer_todo_frame){

                                let metros_recorridos_tramo = metros_recorridos - metro_recorrer_todo_frame;

                                if( !isNaN(metros_recorridos_tramo / metros_entre_puntos) ){
                                    let posicion_nueva = calcularPuntoIntermedio( punto_final['lat'], punto_final['lng'], punto_inicial['lat'], punto_inicial['lng'], metros_recorridos_tramo / metros_entre_puntos );
                                    repartidores[id]['marcador'].position = { lat: posicion_nueva[0], lng: posicion_nueva[1] };
                                }

                                break;
                            }
                        }

                    } );

                }

                frame++;
            }

            if(frame > max_frame){

                let datos = {
                    "repartidor": {
                        "id": 0
                    },
                    "repartidores":{}
                };

                Object.keys(repartidores).forEach( (id) => {
                    datos['repartidores'][id] = {
                        "lat": repartidores[id]['marcador'].position['lat'],
                        "lon": repartidores[id]['marcador'].position['lng']
                    };
                } );

                fetch('android/rutas_repartidores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
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
                setTimeout(actualizar, 10);
            }
        }

        function dibujar_polilineas(usuario_encontrado, ruta, polilinea_primera_leg){

            if( fijado == usuario_encontrado['id'] ){

                polilineas.forEach( (polilinea)=>{
                    polilinea.setMap(null);
                });
                polilineas = [];

                marcadores.forEach( (marcador)=>{
                    marcador.setMap(null);
                });
                marcadores = [];

                let latitud_longitud_limite = new LimitesLatitudLongitud();

                for( c = 0 ; c < ruta['legs'].length; c++ ){
                    let leg = ruta['legs'][c];
                    
                    if( c > 0 ){
                        let latitudes_longitudes = Codificador.decodePath(leg['polyline']['encodedPolyline']);
                        let polilinea = new Polilinea({
                            path: latitudes_longitudes,
                            geodesic: true,
                            strokeColor: '#000000',
                            strokeOpacity: 1.0,
                            strokeWeight: 3
                        });
                        polilinea.setMap(mapa);
                        polilineas.push(polilinea);

                        latitudes_longitudes.forEach((latitud_longitud)=>{
                            latitud_longitud_limite.extend({lat: latitud_longitud['lat'](), lng: latitud_longitud['lng']()});
                        });
                    }

                    let imagen = document.createElement('img');
                    if( c == ruta['legs'].length - 1 ){
                        imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_marver.png';
                    }else{
                        imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_cliente_' + (c + 1) +'.png';
                    }

                    let marcador = new ElementoMarcadorAvanzado({
                        content: imagen,
                        map: mapa,
                        position: { lat: leg['endLocation']['latLng']['latitude'], lng: leg['endLocation']['latLng']['longitude'] }
                    });

                    let contenido = '';
                    if( c == ruta['legs'].length - 1 ){
                        contenido = '<p style="margin: 0;" ><strong>' + ruta['duration']  + ' </strong>' + ruta['duration'] + '</p>';
                    }else{
                        let indice = pedidos_consultados.length > 1 ? orden_pedidos[c] : 0;
                        contenido = '<p style="margin: 0;" ><strong>' + pedidos_consultados[indice]['folio']  + ' </strong>' + leg['duration'] + '</p>';
                    }
                    let infowindow = new VentanaInformacion({
                        content: contenido
                    });

                    marcador.addListener("click", () => {
                        infowindow.open({
                            anchor: marcador,
                            map: mapa,
                        });
                    });

                    infowindow.open({
                        anchor: marcador,
                        map: mapa,
                    });

                    marcadores.push(marcador);
                }

                if(polilinea_primera_leg){
                    usuario_encontrado['polilinea'] = new Polilinea({
                        path: usuario_encontrado['latitudes_longitudes'],
                        geodesic: true,
                        strokeColor: '#6495ED',
                        strokeOpacity: 1.0,
                        strokeWeight: 3
                    });
                    usuario_encontrado['polilinea'].setMap(mapa);
                    polilineas.push(usuario_encontrado['polilinea']);

                    usuario_encontrado['latitudes_longitudes'].forEach((latitud_longitud)=>{
                        latitud_longitud_limite.extend({lat: latitud_longitud['lat'](), lng: latitud_longitud['lng']()});
                    });

                    let latitudes_longitudes = Codificador.decodePath(ruta['legs'][0]['polyline']['encodedPolyline']);

                    let polilinea = new Polilinea({
                        path: latitudes_longitudes,
                        geodesic: true,
                        strokeColor: '#6495ED',
                        strokeOpacity: 1.0,
                        strokeWeight: 3
                    });
                    polilinea.setMap(mapa);
                    polilineas.push(polilinea);

                    latitudes_longitudes.forEach((latitud_longitud)=>{
                        latitud_longitud_limite.extend({lat: latitud_longitud['lat'](), lng: latitud_longitud['lng']()});
                    });
                }

                if(consultar_pedidos){
                    if(!latitud_longitud_limite['isEmpty']()){
                        mapa.fitBounds(latitud_longitud_limite,150);
                    }else{
                        mapa.setZoom(18.5);
                        //mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
                        mapa.panTo({lat: usuario_encontrado['latitudes_longitudes'][0]['lat'](), lng: usuario_encontrado['latitudes_longitudes'][0]['lng']()});
                    }
                    consultar_pedidos = false;
                }
            }
        }

        function procesar_logica(respuesta_json) {
            clearTimeout(id_procesar_vista);

            respuesta_json.forEach((usuario) => {

                let usuario_encontrado = usuarios.find((usuario_buscar) => { return usuario_buscar['id'] == usuario['usuario']; });

                if (usuario_encontrado != undefined) {

                    usuario_encontrado['velocidad'] = usuario['velocidad'];

                    if( consultar_pedidos || usuario_encontrado['posicion_final']['lat'] != usuario['latitud'] || usuario_encontrado['posicion_final']['lng'] != usuario['longitud'] ){
                        consultas_polilineas += 1;

                        if( fijado == usuario_encontrado['id'] ){
                            velocidadRepartidor.innerText = (usuario_encontrado['velocidad'] * 3.6).toFixed(1) + ' Km/h';
                        }
                        
                        let datos_envio = new FormData();
                        datos_envio.append('clave',usuario_encontrado['id']);

                        fetch('android/pedidos_en_ruta_test', {
                            method: "POST",
                            body: datos_envio
                        })
                        .then(respuesta => respuesta.json())
                        .then(pedidos => {

                            json_intermedios = [];

                            if( fijado == usuario_encontrado['id'] ){
                                pedidos_consultados = [];
                            }

                            pedidos.forEach( (pedido) =>{
                                if( pedido['latitud'] != null && pedido['longitud'] != null ){
                                    if( fijado == usuario_encontrado['id'] ){
                                        pedidos_consultados.push(pedido);
                                    }
                                    json_intermedios.push({
                                        location:{
                                            latLng:{
                                                latitude: pedido['latitud'],
                                                longitude: pedido['longitud']
                                            }
                                        }
                                    });
                                }
                            });

                            json_envio = {
                                origin: {
                                    location: {
                                        latLng: {
                                            latitude: usuario['latitud'],
                                            longitude: usuario['longitud']
                                        }
                                    }
                                },
                                destination: {
                                    location: {
                                        latLng: {
                                            latitude: 25.7942362,
                                            longitude: -108.9858341
                                        }
                                    }
                                },
                                travelMode: "TWO_WHEELER",
                                routingPreference: "TRAFFIC_AWARE"
                                };

                            if(json_intermedios.length > 0){
                                json_envio['intermediates'] = json_intermedios;
                                json_envio['optimizeWaypointOrder'] = 'true';
                            }

                            fetch("https://routes.googleapis.com/directions/v2:computeRoutes", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-Goog-Api-Key": "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo",
                                    "X-Goog-FieldMask": "routes.duration,routes.distanceMeters,routes.legs.distanceMeters,routes.optimizedIntermediateWaypointIndex,routes.legs.duration,routes.legs.polyline.encodedPolyline,routes.legs.startLocation,routes.legs.endLocation"
                                },
                                body: JSON.stringify(json_envio)
                            })
                            .then(response => response.json())
                            .then(rutas => {

                                if(pedidos_consultados.length > 0 && fijado == usuario_encontrado['id']){
                                    orden_pedidos = rutas['routes'][0]['optimizedIntermediateWaypointIndex'];
                                }

                                let leg = rutas['routes'][0]['legs'][0];
                                let latitudes_longitudes = Codificador.decodePath(leg['polyline']['encodedPolyline']);

                                if( Esferica.computeDistanceBetween( { lat: usuario['latitud'], lng: usuario['longitud'] }, { lat: leg['endLocation']['latLng']['latitude'], lng: leg['endLocation']['latLng']['longitude'] } ) <= 60 ){
                                    clearTimeout(id_procesar_vista);

                                    usuario_encontrado['metros_recorrer'] = 0;
                                    usuario_encontrado['frame'] = 0;
                                    usuario_encontrado['latitudes_longitudes'] = [];
                                    usuario_encontrado['latitudes_longitudes'].push(latitudes_longitudes[latitudes_longitudes.length - 1]);
                                    usuario_encontrado['latitudes_longitudes'].push(latitudes_longitudes[latitudes_longitudes.length - 1]);
                                    usuario_encontrado['posicion_final'] = { lat: usuario['latitud'], lng: usuario['longitud'] };

                                    dibujar_polilineas(usuario_encontrado, rutas['routes'][0], false);

                                    consultas_polilineas -= 1;
                                    id_procesar_vista = setTimeout(procesar_vista, 10);
                                }else{

                                    fetch("https://routes.googleapis.com/directions/v2:computeRoutes", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "X-Goog-Api-Key": "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo",
                                            "X-Goog-FieldMask": "routes.distanceMeters,routes.polyline"
                                        },
                                        body: JSON.stringify({
                                            origin: {
                                                location: {
                                                    latLng: {
                                                        latitude: usuario_encontrado['marcador'].position['lat'],
                                                        longitude: usuario_encontrado['marcador'].position['lng']
                                                    }
                                                }
                                            },
                                            destination: {
                                                location: {
                                                    latLng: {
                                                        latitude: usuario['latitud'],
                                                        longitude: usuario['longitud']
                                                    }
                                                }
                                            },
                                            travelMode: "TWO_WHEELER",
                                            routingPreference: "TRAFFIC_AWARE"
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(ruta => {
                                        clearTimeout(id_procesar_vista);

                                        if(!ruta['routes'][0].hasOwnProperty('distanceMeters')){
                                            ruta['routes'][0]['distanceMeters'] = 0;
                                        }
                                        usuario_encontrado['metros_recorrer'] = ruta['routes'][0]['distanceMeters'];
                                        usuario_encontrado['frame'] = 0;
                                        usuario_encontrado['posicion_final'] = { lat: usuario['latitud'], lng: usuario['longitud'] };
                                        usuario_encontrado['latitudes_longitudes'] = Codificador.decodePath(ruta['routes'][0]['polyline']['encodedPolyline']);

                                        dibujar_polilineas(usuario_encontrado, rutas['routes'][0], true);

                                        consultas_polilineas -= 1;
                                        id_procesar_vista = setTimeout(procesar_vista, 10);
                                    })
                                    .catch(error => {
                                        consultas_polilineas -= 1;
                                        console.error('Error:', error);
                                    });
                                }

                            })
                            .catch(error => {
                                consultas_polilineas -= 1;
                                console.error('Error:', error);
                            });

                        })
                        .catch(error => {
                            consultas_polilineas -= 1;
                            console.error('Error:', error);
                        });

                    }
                } else {

                    let imagen = document.createElement('img');
                    imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

                    let marcador = new ElementoMarcadorAvanzado({
                        content: imagen,
                        map: mapa,
                        position: { lat: usuario['latitud'], lng: usuario['longitud'] }
                    });

                    let usuarioLista = {
                        id: usuario['usuario'],
                        nombre: usuario['Nombre'],
                        marcador: marcador,
                        velocidad: usuario['velocidad'],
                        frame: 0,
                        metros_recorrer: undefined,
                        latitudes_longitudes: undefined,
                        posicion_final: { lat: usuario['latitud'], lng: usuario['longitud'] }
                    };

                    let infowindow = new VentanaInformacion({
                        content: '<p style="margin: 0;" ><strong>' + usuarioLista['id'] + ' </strong>' + usuarioLista['nombre'] + '</p>'
                    });

                    info_windows.push(infowindow);

                    marcador.addListener("click", () => {

                        if(usuarioLista['id'] != fijado){
                            consultar_pedidos = true;
                            fijado = usuarioLista['id'];

                            document.getElementById('txtIdRepartidor').innerText = usuarioLista['id'];
                            document.getElementById('txtNombreRepartidor').innerText = usuarioLista['nombre'];
                        }

                        info_windows.forEach((info)=>{
                            info.close();
                        });

                        infowindow.open({
                            anchor: usuarioLista['marcador'],
                            map: mapa,
                        });
                    });

                    usuarios.push(usuarioLista);

                    let li = document.createElement('li');

                    li.addEventListener('click', () => {
                        document.getElementById('btnCerrarModal').click();

                        if(usuarioLista['id'] != fijado){
                            consultar_pedidos = true;
                            fijado = usuarioLista['id'];

                            document.getElementById('txtIdRepartidor').innerText = usuarioLista['id'];
                            document.getElementById('txtNombreRepartidor').innerText = usuarioLista['nombre'];
                        }

                        info_windows.forEach((info)=>{
                            info.close();
                        });

                        infowindow.open({
                            anchor: usuarioLista['marcador'],
                            map: mapa,
                        });
                    });

                    li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                    li.innerText = usuarioLista['nombre'];

                    let span = document.createElement('span');
                    span.classList.add('badge', 'bg-primary', 'rounded-pill');
                    span.innerText = usuarioLista['id'];

                    li.appendChild(span);
                    listaRepartidores.appendChild(li);

                    /* HACER TODO EL PROCESO DE BUSCAR TUS RUTAS Y SI LLEGATE ALGUN PUNTIO PONERTE EN EL */
                }
            });

            id_procesar_vista = setTimeout(procesar_vista, 10);
        }
</script>

    <script>(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo", v: "weekly" });</script>

    <script type="module">

        async function initMap() {
            const { Map, InfoWindow, Polyline } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            const { spherical, encoding } = await google.maps.importLibrary("geometry");
            const { LatLngBounds } = await google.maps.importLibrary("core");

            ElementoMarcadorAvanzado = AdvancedMarkerElement;
            VentanaInformacion = InfoWindow;
            Esferica = spherical;
            Codificador = encoding;
            Polilinea = Polyline;
            LimitesLatitudLongitud = LatLngBounds;

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: 25.7951169, lng: -108.99698492 },
                zoom: 13.36,
                mapId: '7845e7dffe8cea37'
            });

            setTimeout(actualizar, 10);

            velocidadRepartidor = document.getElementById('velocidadRepartidor');
            seguirRepartidor = document.getElementById('seguirRepartidor');
        }

        initMap();
    </script>

    <script>

        document.getElementById('modalSelector').addEventListener('hidden.bs.modal', function () {
            setTimeout(() => {
                document.getElementById('btnBuscarRepartidor').blur();
            }, 1000);
        });

        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState == 'visible') {
                directo = true;
            }
        });

    </script>
</body>

</html>