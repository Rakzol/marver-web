<?php
    session_start();

    if(!isset($_SESSION['usuario_mapa'])){
        header("Location: https://www.marverrefacciones.mx/login_mapa.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang='es'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Excesos</title>


    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet" /> -->



    <!-- <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet'
        integrity='sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC' crossorigin='anonymous'> -->

    <!-- <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css"> -->

    <style>
        .card-subtitle::before {
            content: "$ ";
        }

        .card-subtitle {
            color: green;
        }

        .alert {
            z-index: 2000;
            position: fixed !important;
            right: 1rem;
            top: 1rem;
        }

        .imagen-producto {
            height: 10rem;
            object-fit: contain;
        }

        .mx-6 {
            margin: 1rem !important;
            margin-left: 4.5rem !important;
            margin-right: 4.5rem !important;
        }
    </style>

    <!-- Favicon -->
    <link href="img/logo_solo.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">


    <style>
        :root {
            --el-color: #E72F2D;
            --velocidad-iconos: 0.2s;
        }

        .iconos>i {
            color: var(--el-color) !important;
            transition: var(--velocidad-iconos);
        }

        .iconos {
            font-size: 1.75rem;
            transition: var(--velocidad-iconos);
            background: linear-gradient(0deg, rgba(231, 47, 45, 1) 100%, rgba(231, 47, 45, 1) 100%);
            background-repeat: no-repeat;
            background-position: 0rem -4rem;
        }

        .fondo-instagram {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
            background-repeat: no-repeat;
            background-position: 0rem -4rem;
        }

        .fondo-facebook {
            background: linear-gradient(0deg, rgba(66, 103, 178, 1) 100%, rgba(66, 103, 178, 1) 100%);
            background-repeat: no-repeat;
            background-position: 0rem -4rem;
        }

        .fondo-whatsapp {
            background: linear-gradient(0deg, rgba(37, 211, 102, 1) 100%, rgba(37, 211, 102, 1) 100%);
            background-repeat: no-repeat;
            background-position: 0rem -4rem;
        }

        .fondo-messenger {
            background: linear-gradient(45deg, rgba(9, 147, 255, 1) 25%, rgba(167, 53, 247, 1) 50%, rgba(255, 109, 100, 1) 75%);
            background-repeat: no-repeat;
            background-position: 0rem -4rem;
        }

        .iconos:hover {
            --el-color: #FFFFFF;
            background-position: 0rem 0rem;
        }

        .numero_positivo::before {
            content: "+";
        }

        .imagen-alta {
            width: 100%;
            top: 2rem;
            left: 2rem;
            position: relative;
            border-radius: 6px;
        }

        .contenedor-imagen-alta {
            border: 6px solid #E72F2D;
            border-radius: 6px;
            width: calc(100% - 3rem);
        }

        #carousel-inicio {
            background: linear-gradient(90deg, #d5201e 0%, rgba(231, 47, 45, 1) 100%);
            background-size: 15rem auto;
        }

        #inicio-tips::before,
        #nosotros::before,
        #refacciones::before,
        #proveedores::before,
        #nosotros_mas::before,
        #sucursales::before,
        #contenedor_correo::before {
            content: '';
            display: block;
            height: 0px;
            margin-top: 25px;
            visibility: hidden;
        }

        #sucursales::before {
            content: '';
            display: block;
            height: 70px;
            margin-top: -70px;
            visibility: hidden;
        }

        .mapa {
            height: 480px;
            width: 100%;
            max-width: 900px;
            display: block;
            margin: auto;
        }

        .col-2 {
            width: 20% !important;
        }

        #contenedor_redes {
            margin: auto;
            margin-top: 1rem;
        }

        #contenedor_videos {
            max-width: 100% !important;
        }

        #nosotros_mas {
            max-width: 1800px;
        }

        video,
        .imagen-video {
            border-radius: 6px;
            max-height: 30rem;
        }

        #contenedor_correo {
            background: url("img/mail.jpg");
            background-repeat: no-repeat;
            background-size: cover;
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            background-position: center;
            background-attachment: fixed;
        }

        #contenedor_correo_hijo {
            background: linear-gradient(180deg, rgb(255 255 255 / 70%) 0%, rgba(0, 0, 0, 0) 100%);
            border-radius: 12px;
            padding: 1rem;
            max-width: 50rem;
        }

        #contenedor_correo_hijo_parrafo {
            color: #272727;
        }

        #contenedor_refacciones {
            background: linear-gradient(180deg, rgb(255 255 255 / 60%) 0%, rgba(0, 0, 0, 0) 100%);
            border-radius: 12px;
            padding: 1rem;
        }

        .texto-subtitular {
            position: relative;
            display: inline-block;
            text-transform: uppercase;
        }

        #refacciones {
            background: url("img/fondo-estatico.jpg");
            background-repeat: no-repeat;
            background-size: cover;
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            background-position: center;
            background-attachment: fixed;
        }

        .service-item {
            background: white;
        }

        @media (max-width: 991px) {
            .logo_principal {
                width: 125px !important;
            }
        }

        @media (max-width: 768px) {
            .carrusel_grande {
                display: none !important;
            }

            .carrusel_chico {
                display: block !important;
            }
        }

        @media (min-width: 769px) {
            .carrusel_grande {
                display: block !important;
            }

            .carrusel_chico {
                display: none !important;
            }
        }

        @media (min-width: 399px) {
            #carousel-inicio h5 {
                font-size: 1.5rem;
            }

            #carousel-inicio p {
                font-size: 1.25rem;
            }
        }

        @media (min-width: 400px) {
            #carousel-inicio h5 {
                font-size: 1.75rem;
            }

            #carousel-inicio p {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 600px) {
            #carousel-inicio h5 {
                font-size: 2.5rem;
            }

            #carousel-inicio p {
                font-size: 2.25rem;
            }
        }

        @media (min-width: 1200px) {
            #carousel-inicio h5 {
                font-size: 3rem;
            }

            #carousel-inicio p {
                font-size: 2.75rem;
            }
        }

        @media (min-width: 1400px) {
            #carousel-inicio h5 {
                font-size: 3.5rem;
            }

            #carousel-inicio p {
                font-size: 3.25rem;
            }
        }

        #carousel-inicio h5,
        #carousel-inicio p {
            margin-bottom: 0;
            margin-top: 0;
            /* line-height: 0; */
            font-weight: 700;
        }

        #carousel-inicio .carousel-caption {
            /* border-radius: 6px;
            background: linear-gradient(180deg, #d5211e6e 0%, rgba(0,0,0,0) 100%); */
        }

        #contenedor_pie {
            background: #E72F2D !important;
            padding-right: 0 !important;
            padding-left: 0 !important;
        }

        #contenedor_pie img {
            height: auto;
            width: 150px;
            margin: auto;
            display: block;
        }

        #contenedor_pie p,
        #contenedor_pie a,
        #contenedor_pie a::before {
            color: black;
        }

        .copyright {
            background: rgb(145 145 145);
            color: black;
        }

        .copyright a {
            color: white !important;
            text-decoration: none;
        }

        .metodo_pago {
            color: white !important;
            font-size: 4rem;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
    </style>

<style>
            .dinero {
            color: green;
        }

        .dinero::before {
            content: '$';
        }

        body{
            background: #e15454;
        }
</style>

</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner" manual = 'si'
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border position-relative text-primary" style="width: 6rem; height: 6rem;" role="status">
        </div>
        <img class="text-primary position-absolute top-50 start-50 translate-middle" src="img/logo.png" style="width: 5rem;">
    </div>
    <!-- Spinner End -->

    <div class="container mt-3 p-3 bg-white rounded">
        <h1 class="text-center" >Excesos</h1>

        <div class="d-flex justify-content-center gap-3 flex-wrap" >

            <div class="form-floating" style="min-width: 210px;" >
                <input type="date" class="form-control" id="fecha">
                <label for="fecha">Fecha</label>
            </div>

            <div class="form-floating" style="min-width: 210px;">
                <input type="number" class="form-control" id="tiempo_limite" value="10">
                <label for="tiempo_limite">Tiempo Limite (Minutos)</label>
            </div>

            <div class="form-floating" style="min-width: 210px;">
                <input type="number" class="form-control" id="velocidad_limite" value="60">
                <label for="velocidad_limite">Velocidad Limite Km/h</label>
            </div> 

            <button id="actualizar" onclick="actualizar_excesos();" type="button" class="btn btn-primary" style="min-width: 210px;">Actualizar</button>

        </div>

        <!-- dia minutos limite de reposo velocidad limite de conduccion -->
        <table class="table mb-0 mt-2">
        <thead>
            <tr>
            <th>Clave</th>
            <th class="d-none d-md-table-cell" >Nombre</th>
            <th>Tipo</th>
            <th>Duración</th>
            <th class="d-none d-md-table-cell" >Velocidad</th>
            <th class="d-none d-md-table-cell" >Fecha</th>
            <th>Mapa</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <!-- <tfoot>
        <tr>
            <th class="d-none d-md-table-cell" ></th>
            <th class="d-none d-md-table-cell" ></th>
            <th>Totales:</th>
            <th class="dinero" id="importe_total" ></th>
            <th class="dinero" id="abono_total" ></th>
            <th class="dinero d-none d-md-table-cell" id="deuda_total" ></th>
            <th></th>
            </tr>
        </tfoot> -->
        </table>
    </div>

    <script>

        let cuerpo_excesos;

        function actualizar_excesos(){
            setTimeout(() => {
                // document.getElementById('actualizar').blur();
            }, 1000);

            document.getElementById('spinner').classList.add('show');

            fetch('modelo/consultar_excesos')
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los excesos: ', error);
                    document.getElementById('spinner').classList.remove('show');
                })
                .then(respuesta_json => {
                    console.log(respuesta_json);
                    document.getElementById('spinner').classList.remove('show');
                });
        }

        window.addEventListener('load', () => {
            document.getElementById('fecha').valueAsDate = new Date();

            cuerpo_excesos = document.querySelector('tbody');

            actualizar_excesos();
        });

        function mostrar_facturas() {
            cuerpo_facturas.replaceChildren();
            let inicio = (pagina.valueAsNumber - 1) * facturas_pagina;
            facturas.slice(inicio, inicio + facturas_pagina).forEach(factura => {
                let tr = document.createElement('tr');

                let td = document.createElement('td');
                td.innerText = factura['FolioComprobante'];
                tr.appendChild(td);

                td = document.createElement('td');
                td.innerText = factura['Fecha'];
                td.classList.add('d-none');
                td.classList.add('d-md-table-cell');
                tr.appendChild(td);

                td = document.createElement('td');
                td.innerText = factura['FechaVencimiento'];
                td.classList.add('d-none');
                td.classList.add('d-md-table-cell');
                tr.appendChild(td);

                td = document.createElement('td');
                td.innerText = factura['Importe'].toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                    });
                td.classList.add('dinero');
                tr.appendChild(td);

                td = document.createElement('td');
                td.innerText = factura['Abono'].toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                    });
                td.classList.add('dinero');
                tr.appendChild(td);

                td = document.createElement('td');
                td.innerText = (factura['Importe'] - factura['Abono']).toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                    });
                td.classList.add('dinero');
                td.classList.add('d-none');
                td.classList.add('d-md-table-cell');
                tr.appendChild(td);

                td = document.createElement('td');
                let button = document.createElement('button');
                button.onclick = ()=>{
                    document.location.href = 'https://www.marverrefacciones.mx/pedido?folio=' + factura['Folio'];
                }
                button.innerText = 'Ver';
                button.classList.add('btn');
                button.classList.add('btn-primary');
                td.appendChild(button);
                tr.appendChild(td);
                
                cuerpo_facturas.appendChild(tr);
            });
        }

    </script>

    <!-- <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js'
        integrity='sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM'
        crossorigin='anonymous'></script> -->

    <!-- <script src="https://kit.fontawesome.com/52bb463edd.js" crossorigin="anonymous"></script> -->

    <!-- MDB -->
    <!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script> -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

</body>

</html>