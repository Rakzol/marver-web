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
                <!-- <p class="card-text mb-1" id="velocidadRepartidor">0.0 Km/h</p> -->
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

        function GeoPolylineToGooglePolyline(geoPolylines){
            let googlePolylines = [];
            geoPolylines.forEach( (geoPolyline)=> {
                googlePolylines.push( { lat: geoPolyline[1], lng: geoPolyline[0] } );
            } );
            return googlePolylines;
        }

        let mapa;

        let json_api;
        let repartidores = {};

        let repartidor_seguido = {
            id: 0,
            marcador: {
                position: {
                    lat: 0,
                    lng: 0
                }
            }
        }

        let id_ruta = 0;
        let pedidos = [];

        let id_actualizar;

        let max_frame = 1050;
        let frame = max_frame + 1;

        let polilineas = [];

        let infowindowMarver;
        let marcadorMarver;
        
        let listaRepartidores;
        let velocidadRepartidor;

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Esferica;
        let Codificador;
        let Polilinea;
        let LimitesLatitudLongitud;

        function actualizar() {

            if(frame <= max_frame){

                if( frame == 0 ){

                    polilineas.forEach( (polilinea)=>{
                        polilinea.setMap(null);
                    });
                    polilineas = [];

                    json_api['repartidores'].forEach( (repartidor) => {

                        if(repartidores.hasOwnProperty(repartidor['id'])){

                            repartidores[repartidor['id']]['polilinea'] = repartidor['polilinea'];
                            repartidores[repartidor['id']]['distancia'] = repartidor['distancia'];
                            repartidores[repartidor['id']]['color'] = repartidor['color'];
                            repartidores[repartidor['id']]['velocidad'] = repartidor['velocidad'];

                        }else{

                            let imagen = document.createElement('img');
                            imagen.src = 'https://www.marverrefacciones.mx/android/marcador.png';

                            let marcador = new ElementoMarcadorAvanzado({
                                content: imagen,
                                map: mapa,
                                position: { lat: repartidor['polilinea'][0][1], lng: repartidor['polilinea'][0][0] },
                                zIndex: 1
                            });

                            let infowindow = new VentanaInformacion({
                                disableAutoPan: true,
                                content: '<p class="infoWindow" ><strong>' + repartidor['id'] + ' </strong> ' + repartidor['nombre'] + '</p>',
                                zIndex: 1
                            });

                            marcador.addListener("click", () => {
                                infowindow.open({
                                    anchor: marcador,
                                    map: mapa,
                                    zIndex: -2
                                });

                                clearTimeout(id_actualizar);
                                frame = max_frame + 1;
                                repartidor_seguido = repartidores[repartidor['id']];
                                actualizar();
                            });

                            repartidores[repartidor['id']] = {};
                            repartidores[repartidor['id']]['id'] = repartidor['id'];
                            repartidores[repartidor['id']]['nombre'] = repartidor['nombre'];
                            repartidores[repartidor['id']]['marcador'] = marcador;
                            repartidores[repartidor['id']]['polilinea'] = repartidor['polilinea'];
                            repartidores[repartidor['id']]['distancia'] = repartidor['distancia'];
                            repartidores[repartidor['id']]['color'] = repartidor['color'];

                            let li = document.createElement('li');

                            li.addEventListener('click', () => {
                                document.getElementById('btnCerrarModal').click();

                                document.getElementById('txtIdRepartidor').innerText = repartidores[repartidor['id']]['id'];
                                document.getElementById('txtNombreRepartidor').innerText = repartidores[repartidor['id']]['nombre'];
                                //velocidadRepartidor.innerText = (usuarioLista['velocidad'] * 3.6).toFixed(1) + ' Km/h';

                                infowindow.open({
                                    anchor: marcador,
                                    map: mapa,
                                    zIndex: -2
                                });

                                clearTimeout(id_actualizar);
                                frame = max_frame + 1;
                                repartidor_seguido = repartidores[repartidor['id']];
                                actualizar();
                            });

                            li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                            li.innerText = repartidores[repartidor['id']]['nombre'];

                            let span = document.createElement('span');
                            span.classList.add('badge', 'bg-primary', 'rounded-pill');
                            span.innerText = repartidores[repartidor['id']]['id'];

                            li.appendChild(span);
                            listaRepartidores.appendChild(li);
                        }

                        if(repartidor['color'] != '#00000000'){

                            let polilinea = new Polilinea({
                                path: GeoPolylineToGooglePolyline(repartidor['polilinea']),
                                geodesic: true,
                                strokeColor: repartidor['color'],
                                strokeOpacity: 1.0,
                                strokeWeight: 3,
                                zIndex: 3
                            });
                            polilinea.setMap(mapa);
                            polilineas.push(polilinea);
                        }
                    } );

                    if( json_api.hasOwnProperty('incorporacion') ){
                        let polilinea = new Polilinea({
                            path: GeoPolylineToGooglePolyline(json_api['incorporacion']['polilinea']),
                            geodesic: true,
                            strokeColor: json_api['incorporacion']['color'],
                            strokeOpacity: 1.0,
                            strokeWeight: 3,
                            zIndex: 4
                        });
                        polilinea.setMap(mapa);
                        polilineas.push(polilinea);
                    }

                    if( json_api.hasOwnProperty('id') ){

                        if( json_api['id'] != id_ruta ){

                            id_ruta = json_api['id'];

                            pedidos.forEach( (pedido)=>{
                                pedido['marcador'].setMap(null);
                            });
                            pedidos = [];

                            for(let c = 0; c < json_api['ruta']['legs'].length - 1; c++){

                                let leg = json_api['ruta']['legs'][c];

                                let imagen = document.createElement('img');
                                imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_cliente_' + (c+1) + ( leg['pedido']['status'] != 4 ? '_verde' : '' ) + '.png';

                                let marcador = new ElementoMarcadorAvanzado({
                                    content: imagen,
                                    map: mapa,
                                    position: { lat: leg['polyline']['polilinea'][leg['polyline']['polilinea'].length-1][1], lng: leg['polyline']['polilinea'][leg['polyline']['polilinea'].length-1][0] },
                                    zIndex: 2
                                });

                                let infowindow = new VentanaInformacion({
                                    disableAutoPan: true,
                                    content: '<p class="infoWindow" >' + 
                                        '<strong>Folio: </strong> ' + leg['pedido']['folio'] + '<br>' +
                                        '<strong>Cliente: </strong> ' + leg['pedido']['cliente_clave'] + ' ' + leg['pedido']['cliente_nombre'] + '<br>' +
                                        '<strong>Pedido: </strong> ' + leg['pedido']['pedido'] + '<br>' +
                                        '<strong>Total: </strong> <span class="dinero" >' + leg['pedido']['total'] + '</span><br>' +
                                        ( leg['pedido']['feria'] != null ? '<strong>Feria: </strong> <span class="dinero" >' + leg['pedido']['feria'] + '</span><br>' : '' ) +
                                        ( leg['pedido']['calle'] != null ? '<strong>Calle: </strong> ' + leg['pedido']['calle'] + '<br>' : '' ) +
                                        ( leg['pedido']['numero_exterior'] != null ? '<strong>Número exterior: </strong> ' + leg['pedido']['numero_exterior'] + '<br>' : '' ) +
                                        ( leg['pedido']['numero_interior'] != null ? '<strong>Número Interior: </strong> ' + leg['pedido']['numero_interior'] + '<br>' : '' ) +
                                        '<strong>Llegada: </strong> ' + leg['llegada'] + '<br>' +
                                        '<strong>Duración: </strong> ' + leg['Totalduration'] + ' Minutos<br>' +
                                        '<strong>Distancia: </strong> ' + leg['Totaldistance'] + ' Km.'
                                    + '</p>',
                                    zIndex: 3
                                });

                                marcador.addListener("click", () => {
                                    infowindow.open({
                                        anchor: marcador,
                                        map: mapa,
                                    });
                                });

                                pedido = {};
                                pedido['marcador'] = marcador;
                                pedido['status'] = leg['pedido']['status'];
                                pedidos.push(pedido);
                            }

                            infowindowMarver.setContent('<p class="infoWindow" >' + 
                                '<strong>Llegada: </strong> ' + json_api['ruta']['llegada'] + '<br>' +
                                '<strong>Duración: </strong> ' + json_api['ruta']['duration'] + ' Minutos<br>' +
                                '<strong>Distancia: </strong> ' + json_api['ruta']['distance'] + ' Km.'
                            + '</p>');
                            infowindowMarver.open({
                                anchor: marcadorMarver,
                                map: mapa,
                            });

                        }else{

                            for(let c = 0; c < json_api['ruta']['legs'].length - 1; c++){

                                let leg = json_api['ruta']['legs'][c];

                                if( leg['pedido']['status'] != pedidos[c]['status'] ){
                                    pedidos[c]['status'] = leg['pedido']['status'];

                                    let imagen = document.createElement('img');
                                    imagen.src = 'https://www.marverrefacciones.mx/android/marcadores_ruta/marcador_cliente_' + (c+1) + ( leg['pedido']['status'] != 4 ? '_verde' : '' ) + '.png';
                                
                                    pedidos[c]['marcador']['content'] = imagen;
                                }

                            }

                        }

                        json_api['ruta']['legs'].forEach( (leg) => {

                            let polilinea = new Polilinea({
                                path: GeoPolylineToGooglePolyline(leg['polyline']['polilinea']),
                                geodesic: true,
                                strokeColor: leg['color'],
                                strokeOpacity: 1.0,
                                strokeWeight: 3,
                                zIndex: ( leg['color'] != "#000000" ? 2 : 1 )
                            });
                            polilinea.setMap(mapa);
                            polilineas.push(polilinea);

                        } );
                    }else{
                        id_ruta = 0;

                        pedidos.forEach( (pedido)=>{
                            pedido['marcador'].setMap(null);
                        });
                        pedidos = [];

                        infowindowMarver.setContent('');
                        infowindowMarver.close();
                    }

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
                        "id": repartidor_seguido['id'],
                        "lat": repartidor_seguido['marcador']['position']['lat'],
                        "lon": repartidor_seguido['marcador']['position']['lng']
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
                id_actualizar = setTimeout(actualizar, 10);
            }
        }

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

    <script>(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo", v: "weekly" });</script>

    <script type="module">

        async function initMap() {
            const { Map, InfoWindow, Polyline } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            const { LatLngBounds } = await google.maps.importLibrary("core");

            ElementoMarcadorAvanzado = AdvancedMarkerElement;
            VentanaInformacion = InfoWindow;
            Polilinea = Polyline;
            LimitesLatitudLongitud = LatLngBounds;

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

            velocidadRepartidor = document.getElementById('velocidadRepartidor');
            listaRepartidores = document.getElementById('listaRepartidores');

            actualizar();
        }

        initMap();
    </script>
</body>

</html>