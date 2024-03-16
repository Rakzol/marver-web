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
                        <input class="form-check-input" id="seguirRepartidor" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
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

        let mapa;
        let usuarios = [];
        let fijado = 0;
        let velocidadRepartidor;
        let seguirRepartidor;
        let id_procesar_vista;

        let ElementoMarcadorAvanzado;
        let VentanaInformacion;
        let Esferica;
        let Codificador;
        let Polilinea;

        let consultas_polilineas = 0;

        function actualizacion_logica() {
            if(consultas_polilineas > 0){
                setTimeout(actualizacion_logica, 1000);
                return;
            }

            let datos = new FormData();

            fetch('android/posiciones_web', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los años: ', error);
                })
                .then(respuesta_json => {
                    procesar_logica(respuesta_json);
                    setTimeout(actualizacion_logica, 1000);
                });
        }

        function procesar_logica(respuesta_json) {
            clearTimeout(id_procesar_vista);

            respuesta_json.forEach((usuario) => {

                let usuario_encontrado = usuarios.find((usuario_buscar) => { return usuario_buscar['id'] == usuario['usuario']; });

                if (usuario_encontrado) {

                    usuario_encontrado['velocidad'] = usuario['velocidad'];
                    if( usuario_encontrado['posicion_final']['lat'] != usuario['latitud'] || usuario_encontrado['posicion_final']['lng'] != usuario['longitud']){
                        consultas_polilineas += 1;

                        fetch("https://routes.googleapis.com/directions/v2:computeRoutes", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-Goog-Api-Key": "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo",
                                "X-Goog-FieldMask": "routes.distanceMeters,routes.polyline.encodedPolyline,routes.legs.steps.distanceMeters,routes.legs.steps.startLocation,routes.legs.steps.endLocation"
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
                        .then(data => {
                            clearTimeout(id_procesar_vista);

                            console.log(data);

                            usuario_encontrado['ruta'] = data;
                            usuario_encontrado['frame'] = 0
                            usuario_encontrado['posicion_inicial'] = { lat: usuario_encontrado['marcador'].position['lat'], lng: usuario_encontrado['marcador'].position['lng'] };
                            usuario_encontrado['posicion_final'] = { lat: usuario['latitud'], lng: usuario['longitud'] };

                            if( usuario_encontrado['polilinea'] != undefined ){
                                usuario_encontrado['polilinea'].setMap(null);
                            }

                            usuario_encontrado['polilinea'] = new Polilinea({
                                path: Codificador.decodePath(usuario_encontrado['ruta']['routes'][0]['polyline']['encodedPolyline']),
                                geodesic: true,
                                strokeColor: '#FF0000',
                                strokeOpacity: 1.0,
                                strokeWeight: 2
                            });

                            usuario_encontrado['polilinea'].setMap(mapa);

                            consultas_polilineas -= 1;
                            id_procesar_vista = setTimeout(procesar_vista, 10);
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
                        ruta: undefined,
                        polilinea: undefined,
                        posicion_inicial: { lat: usuario['latitud'], lng: usuario['longitud'] },
                        posicion_final: { lat: usuario['latitud'], lng: usuario['longitud'] }
                    };

                    let infowindow = new VentanaInformacion({
                        content: '<p style="margin: 0;" ><strong>' + usuarioLista['id'] + ' </strong>' + usuarioLista['nombre'] + '</p>'
                    });

                    marcador.addListener("click", () => {
                        fijado = usuarioLista['id'];

                        mapa.setZoom(18.5);
                        mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
                        mapa.panTo(usuarioLista['marcador'].position);

                        document.getElementById('txtIdRepartidor').innerText = usuarioLista['id'];
                        document.getElementById('txtNombreRepartidor').innerText = usuarioLista['nombre'];

                        infowindow.open({
                            anchor: usuarioLista['marcador'],
                            map: mapa,
                        });
                    });

                    usuarios.push(usuarioLista);

                    let li = document.createElement('li');

                    li.addEventListener('click', () => {
                        document.getElementById('btnCerrarModal').click();
                        fijado = usuarioLista['id'];

                        mapa.setZoom(18.5);
                        mapa.setMapTypeId(google.maps.MapTypeId.HYBRID);
                        mapa.panTo(usuarioLista['marcador'].position);

                        document.getElementById('txtIdRepartidor').innerText = usuarioLista['id'];
                        document.getElementById('txtNombreRepartidor').innerText = usuarioLista['nombre'];

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
                }
            });

            id_procesar_vista = setTimeout(procesar_vista, 10);
        }

        function procesar_vista() {

            usuarios.forEach((usuario) => {

                /*if (fijado == usuario['id'] ) {
                    velocidadRepartidor.innerText = (usuario['velocidad'] * 3.6).toFixed(1) + ' Km/h';
                    if (frame % 50 == 0 && seguirRepartidor.checked) {
                        mapa.panTo(usuario['marcador'].position);
                    }
                }

                if (usuario['posicion_inicial']['lat'] != usuario['posicion_final']['lat'] || usuario['posicion_inicial']['lng'] != usuario['posicion_final']['lng']) {

                    let latitud_dif_abs = Math.abs(usuario['posicion_inicial']['lat'] - usuario['posicion_final']['lat']) * frame / 1800;
                    let longitud_dif_abs = Math.abs(Math.abs(usuario['posicion_inicial']['lng']) + usuario['posicion_final']['lng']) * frame / 1800;

                    let latitud = usuario['posicion_inicial']['lat'] >= usuario['posicion_final']['lat'] ? usuario['posicion_inicial']['lat'] - latitud_dif_abs : usuario['posicion_inicial']['lat'] + latitud_dif_abs;
                    let longitud = usuario['posicion_inicial']['lng'] >= usuario['posicion_final']['lng'] ? usuario['posicion_inicial']['lng'] - longitud_dif_abs : usuario['posicion_inicial']['lng'] + longitud_dif_abs;

                    usuario['marcador'].position = { lat: latitud, lng: longitud };
                }*/

                /*if (frame == 1800) {
                    usuario['posicion_inicial'] = { lat: usuario['posicion_final']['lat'], lng: usuario['posicion_final']['lng'] };
                }

                if (frame == 1800) {
                frame = 1;
                } else {
                    frame++;
                }*/
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

            ElementoMarcadorAvanzado = AdvancedMarkerElement;
            VentanaInformacion = InfoWindow;
            Esferica = spherical;
            Codificador = encoding;
            Polilinea = Polyline;

            mapa = new Map(document.getElementById("mapa"), {
                center: { lat: 25.7951169, lng: -108.99698492 },
                zoom: 13.36,
                mapId: '7845e7dffe8cea37'
            });

            setTimeout(actualizacion_logica, 1000);
            id_procesar_vista = setTimeout(procesar_vista, 10);

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