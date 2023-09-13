<?php
    require_once('modelo/guardar_estadisticas.php');
    if(!isset($_SESSION['usuario'])){
        header("Location: https://www.marverrefacciones.mx/login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang='es'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Catalogo</title>


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

        .popover{
            background: #171717;
        }

        .popover-arrow::after{
            border-top-color: #171717 !important;
        }

        .popover-body{
            color: #fff;
        } 

    </style>

</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border position-relative text-primary" style="width: 6rem; height: 6rem;" role="status">
        </div>
        <img class="text-primary position-absolute top-50 start-50 translate-middle" src="img/logo.png" style="width: 5rem;">
    </div>
    <!-- Spinner End -->

    <!-- Brand & Contact Start -->
    <div class="container-fluid py-3 px-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="row align-items-center top-bar">
            <div class="col-lg-8 col-md-12 text-center text-lg-start">
                <a href="https://www.marverrefacciones.mx/" class="navbar-brand m-0 p-0" style="display: block;">
                    <!-- <h1 class="fw-bold text-primary m-0"><i class="fa fa-laptop-code me-3"></i>Marver</h1> -->
                    <img class="logo_principal" style="width: 90px;" src="img/logo.png" alt="Logo">
                </a>
            </div>
            <div id="contenedor_redes" class="col-lg-4 col-md-6 d-lg-block">
                <div class="row">
                    <div class="col-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="https://www.facebook.com/refaccionesparaautos/" target="_blank"
                                class="iconos fondo-facebook flex-shrink-0 btn-lg-square border rounded-circle"><i
                                    class="fab fa-facebook text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="https://www.instagram.com/marverrefacciones/" target="_blank"
                                class="iconos fondo-instagram flex-shrink-0 btn-lg-square border rounded-circle"><i
                                    class="fab fa-instagram text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="mailto:ventas@marverrefacciones.mx" target="_blank"
                                class="iconos flex-shrink-0 btn-lg-square border rounded-circle"><i
                                    class="far fa-envelope text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="https://wa.me/5216681721870" target="_blank"
                                class="iconos fondo-whatsapp flex-shrink-0 btn-lg-square border rounded-circle"><i
                                    class="fab fa-whatsapp text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="https://m.me/refaccionesparaautos" target="_blank"
                                class="iconos fondo-messenger flex-shrink-0 btn-lg-square border rounded-circle"><i
                                    class="fab fa-facebook-messenger text-primary"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Brand & Contact End -->


    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-xxl bg-primary navbar-dark sticky-top py-xxl-0 px-xxl-5 wow fadeIn"
        data-wow-delay="0.1s">
        <a href="https://www.marverrefacciones.mx/" class="navbar-brand ms-3 d-xxl-none">Marver</a>
        <button type="button" class="navbar-toggler me-3" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav p-3 p-xxl-0">
                <a href="https://www.marverrefacciones.mx/" class="nav-item nav-link active">INICIO</a>
                <a href="https://www.marverrefacciones.mx/#nosotros" class="nav-item nav-link">NOSOTROS</a>
                <a href="https://www.marverrefacciones.mx/catalogo.php" class="nav-item nav-link">REFACCIONES</a>
                <a href="https://www.marverrefacciones.mx/#proveedores" class="nav-item nav-link">PROVEEDORES</a>
                <a href="https://www.marverrefacciones.mx/#inicio-tips" class="nav-item nav-link">TIPS</a>
                <a href="https://www.marverrefacciones.mx/#sucursales" class="nav-item nav-link">SUCURSALES</a>
                <a href="https://www.marverrefacciones.mx/#contenedor_correo" class="nav-item nav-link">CONTACTO</a>
            </div>
            <div class="navbar-nav ms-auto p-3 p-xxl-0">
                <?php
                    if(isset($_SESSION['usuario'])){
                        echo '<a href="https://www.marverrefacciones.mx/facturas" class="nav-item nav-link"><i class="fa-solid fa-file"></i> FACTURAS</a>';
                        echo '<a href="https://www.marverrefacciones.mx/pedidos.php" class="nav-item nav-link"><i class="fa-solid fa-truck"></i> PEDIDOS</a>';
                        echo '<a href="https://www.marverrefacciones.mx/carrito.php" class="nav-item nav-link"><i class="fa-solid fa-cart-shopping"></i> CARRITO</a>';
                        echo '<a href="https://www.marverrefacciones.mx/modelo/cerrar_sesion.php" class="nav-item nav-link"><i class="fa-solid fa-power-off"></i> CERRAR SESIÓN</a>';
                    }else{
                        echo '<a href="https://www.marverrefacciones.mx/login.php" class="nav-item nav-link"><i class="fa-solid fa-right-to-bracket"></i> INICIAR SESIÓN</a>';
                    }
                ?>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->


    <svg xmlns="https://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </symbol>
    </svg>


    <div class="alert align-items-center alert-success alert-dismissible fade show d-none" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:">
            <use xlink:href="#check-circle-fill" />
        </svg>
        <div>
            Refaccion agregada al carrito.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="col d-none">
        <div class="card text-center h-100">
            <img src="https://dynamik.mx/archivos/imagenes/DNKE3555SM" class="card-img-top imagen-producto" alt="...">
            <div class="card-header">
                <h5 class="card-title">Producto Motor</h5>
                <h6 class="card-subtitle"></h6>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Codigo-COD</li>
                <li class="list-group-item"><a style='color: #007dcc; text-decoration: underline;'>Detalles</a></li>
                <li class="list-group-item">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </li>
            </ul>
            <div class="card-body">
                <button type="button" class="btn btn-primary"><i class="fa-solid fa-cart-shopping"></i> Agregar</button>
            </div>
        </div>
    </div>

      <!-- Modal -->
  <div class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="producto-modal-label">Detalles del producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="mb-3 col-md-6" style='text-align: center;'>
              <img src="https://via.placeholder.com/300" alt="Producto" class="img-fluid">
            </div>
            <div class="col-md-6">
              <h2>Código: 123456</h2>
              <h3>Nombre del producto</h3>
              <p>Sistema: Nombre del sistema</p>
              <p>Subsistema: Nombre del subsistema</p>
              <p>Fabricante: Nombre del fabricante</p>
              <p>Precio: Nombre del fabricante</p>
              <p>Descripción del producto</p>
              <button type="button" class="mb-3 btn btn-primary"><i class="fa-solid fa-cart-shopping"></i> Agregar</button>
            </div>
          </div>
          <h4>Sustitutos</h4>
          <table class="table">
            <thead>
              <tr>
                <th class='d-none d-sm-table-cell' >Código</th>
                <th>Fabricante</th>
                <th>Precio</th>
                <th>Agregar</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
          <h4>Aplicaciones</h4>
          <table class="table">
            <thead>
              <tr>
                <th>Marca</th>
                <th>Años</th>
                <th>Modelo</th>
                <th>Motor</th>
                <th>Información</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
    
    <div class="container d-flex justify-content-center">

        <form class='container row g-3 m-1' onsubmit="event.preventDefault(); buscar_refaccion_palabras();"
            style="max-width: 1050px;">
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='marcas'>
                    </select>
                    <label for='marcas'>Marca</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='años'>
                    </select>
                    <label for='años'>Año</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='modelos'>
                    </select>
                    <label for='modelos'>Modelo</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='motores'>
                    </select>
                    <label for='motores'>Motor</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='sistemas'>
                    </select>
                    <label for='sistemas'>Sistema</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='subsistemas'>
                    </select>
                    <label for='subsistemas'>Subsistema</label>
                </div>
            </div>
            <div class='col-md-6 col-lg-3'>
                <div class='form-floating'>
                    <select class='form-select' id='productos'>
                    </select>
                    <label for='productos'>Producto</label>
                </div>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="palabras" placeholder="AUDI 2022 A1 1.5L BALATAS">
                <label for="palabras">Refacción</label>
            </div>
        </form>

    </div>

    <div class="container text-center my-3">
        <button type="button" class="btn btn-primary" onclick="buscar_refaccion_palabras();">Buscar Refaccion <i
                class="fa-solid fa-magnifying-glass"></i></button>
    </div>

    <ul class="pagination justify-content-center">
        <li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); pagina_anterior();"
                id="anterior">&#60;</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); primera_pagina();"
                id="primera">0</a></li>
        <li class="page-item"><input type="number" class="page-link" id="pagina" placeholder="0" style="width: 5rem;">
        </li>
        <li class="page-item"></li><button type="button" class="btn btn-primary" style="border-radius: 0px;"
            onclick="ir_pagina();">Ir</button></li>
        <li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); ultima_pagina();"
                id="ultima">0</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); siguiente_pagina();"
                id="siguiente">&#62;</a></li>
    </ul>

    <div class="container mb-5">
        <div class="card-group row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4"
            id="cuerpo_refacciones"></div>
    </div>

    <script>
        let marcas;
        let años;
        let modelos;
        let motores;
        let sistemas;
        let subsistemas;
        let productos;
        let cuerpo_refacciones;
        let palabras;
        let refacciones = [];
        let cliente = {};
        let descuentos = [];
        let primera;
        let ultima;
        let pagina;
        let refacciones_pagina = 10;

        window.addEventListener('load', () => {
            marcas = document.getElementById('marcas');
            años = document.getElementById('años');
            modelos = document.getElementById('modelos');
            motores = document.getElementById('motores');
            sistemas = document.getElementById('sistemas');
            subsistemas = document.getElementById('subsistemas');
            productos = document.getElementById('productos');
            cuerpo_refacciones = document.getElementById('cuerpo_refacciones');
            palabras = document.getElementById('palabras');
            primera = document.getElementById('primera');
            ultima = document.getElementById('ultima');
            pagina = document.getElementById('pagina');

            fetch('modelo/consultar_marcas')
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar las marcas: ', error);
                })
                .then(respuesta_json => {
                    marcas.options.add(new Option('Seleccione. . .', ''));
                    respuesta_json.forEach(marca => {
                        marcas.options.add(new Option(marca['marca'], marca['marca']));
                    });
                });

                marcas.addEventListener('keydown', function(event) {
                if (event.keyCode === 13) {
                    años.focus();
                }
                });

            marcas.addEventListener('change', () => {
                cuerpo_refacciones.replaceChildren();

                let datos = new FormData();
                datos.append('marca', marcas.value);

                fetch('modelo/consultar_años', {
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
                        años.replaceChildren();

                        años.options.add(new Option('Seleccione. . .', ''));
                        respuesta_json.forEach(año => {
                            años.options.add(new Option(año['año'], año['año']));
                        });
                    });

            });

            años.addEventListener('keydown', function(event) {
                if (event.keyCode === 13) {
                    modelos.focus();
                }
                });

            años.addEventListener('change', () => {
                cuerpo_refacciones.replaceChildren();

                let datos = new FormData();
                datos.append('marca', marcas.value);
                datos.append('año', años.value);

                fetch('modelo/consultar_modelos', {
                    method: 'POST',
                    body: datos
                })
                    .then((respuesta) => {
                        return respuesta.json();
                    })
                    .catch(error => {
                        console.error('Error al solicitar los modelos: ', error);
                    })
                    .then(respuesta_json => {
                        modelos.replaceChildren();

                        modelos.options.add(new Option('Seleccione. . . ', ''));
                        respuesta_json.forEach(modelo => {
                            modelos.options.add(new Option(modelo['modelo'], modelo['modelo']));
                        });
                    });

            });


            modelos.addEventListener('keydown', function(event) {
                if (event.keyCode === 13) {
                    motores.focus();
                }
                });

            modelos.addEventListener('change', () => {
                cuerpo_refacciones.replaceChildren();

                let datos = new FormData();
                datos.append('marca', marcas.value);
                datos.append('año', años.value);
                datos.append('modelo', modelos.value);

                fetch('modelo/consultar_motores', {
                    method: 'POST',
                    body: datos
                })
                    .then((respuesta) => {
                        return respuesta.json();
                    })
                    .catch(error => {
                        console.error('Error al solicitar los motores: ', error);
                    })
                    .then(respuesta_json => {
                        motores.replaceChildren();

                        motores.options.add(new Option('Seleccione. . . ', ''));
                        respuesta_json.forEach(motor => {
                            motores.options.add(new Option(motor['motor'], motor['motor']));
                        });
                    });
            });

            motores.addEventListener('keydown', function(event) {
                if (event.keyCode === 13) {
                    productos.focus();
                }
                });

            motores.addEventListener('change', () => {
                consultar_sistemas();
            });

            sistemas.addEventListener('change', () => {
                consultar_subsistemas();
            });

            subsistemas.addEventListener('change', () => {
                consultar_productos();
            });

            productos.addEventListener('change', () => {
                consultar_refacciones();
            });

        });

        function consultar_sistemas() {
            let datos = new FormData();
            datos.append('marca', marcas.value);
            datos.append('año', años.value);
            datos.append('modelo', modelos.value);
            datos.append('motor', motores.value);

            fetch('modelo/consultar_sistemas', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los sistemas: ', error);
                })
                .then(respuesta_json => {
                    sistemas.replaceChildren();

                    sistemas.options.add(new Option('', ''));
                    respuesta_json.forEach(sistema => {
                        sistemas.options.add(new Option(sistema['Sistema'], sistema['Sistema']));
                    });

                    consultar_subsistemas();
                });
        }

        function consultar_subsistemas() {
            let datos = new FormData();
            datos.append('marca', marcas.value);
            datos.append('año', años.value);
            datos.append('modelo', modelos.value);
            datos.append('motor', motores.value);
            datos.append('sistema', sistemas.value);

            fetch('modelo/consultar_subsistemas', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los subsistemas: ', error);
                })
                .then(respuesta_json => {
                    subsistemas.replaceChildren();

                    subsistemas.options.add(new Option('', ''));
                    respuesta_json.forEach(subsistema => {
                        subsistemas.options.add(new Option(subsistema['Subsistema'], subsistema['Subsistema']));
                    });

                    consultar_productos();
                });
        }

        function consultar_productos() {
            let datos = new FormData();
            datos.append('marca', marcas.value);
            datos.append('año', años.value);
            datos.append('modelo', modelos.value);
            datos.append('motor', motores.value);
            datos.append('sistema', sistemas.value);
            datos.append('subsistema', subsistemas.value);

            fetch('modelo/consultar_productos', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los productos: ', error);
                })
                .then(respuesta_json => {
                    productos.replaceChildren();

                    respuesta_json.forEach(producto => {
                        productos.options.add(new Option(producto['Producto'], producto['Producto']));
                    });

                    consultar_refacciones();
                });
        }

        function consultar_refacciones() {
            document.querySelector('#spinner').classList.add('show');

            cuerpo_refacciones.replaceChildren();

            let datos = new FormData();
            datos.append('marca', marcas.value);
            datos.append('año', años.value);
            datos.append('modelo', modelos.value);
            datos.append('motor', motores.value);
            datos.append('sistema', sistemas.value);
            datos.append('subsistema', subsistemas.value);
            datos.append('producto', productos.value);

            fetch('modelo/consultar_refacciones', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar las refacciones: ', error);
                })
                .then(respuesta_json => {
                    actualizar_refacciones(respuesta_json);
                });
        }

        function buscar_refaccion_palabras(event) {
            document.querySelector('#spinner').classList.add('show');

            let datos = new FormData();
            datos.append('palabras', typeof palabras.value === "undefined" ? '' : palabras.value );

            fetch('modelo/consultar_refacciones_palabras', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar las refacciones: ', error);
                })
                .then(respuesta_json => {
                    actualizar_refacciones(respuesta_json);
                });
        }

        function pagina_anterior() {
            if (pagina.valueAsNumber > 1) {
                pagina.valueAsNumber -= 1;
            }
            mostrar_refacciones();
        }

        function primera_pagina() {
            pagina.valueAsNumber = primera.innerText;
            mostrar_refacciones();
        }

        function ir_pagina() {
            mostrar_refacciones();
        }

        function ultima_pagina() {
            pagina.valueAsNumber = ultima.innerText;
            mostrar_refacciones();
        }

        function siguiente_pagina() {
            pagina.valueAsNumber++;
            mostrar_refacciones();
        }

        function actualizar_refacciones(respuesta_json) {

            //Removemos los producto con codigo duplicados que tengan la refacciones null
            let refacciones_limpias = [];
            respuesta_json['refacciones'].forEach( (refaccion) =>{
                let refaccion_encontrada = refacciones_limpias.find( refaccion_buscar => refaccion_buscar.Codigo == refaccion.Codigo );
                if( !refaccion_encontrada ){
                    refacciones_limpias.push(refaccion);
                }else if( refaccion_encontrada.clave == null && refaccion.clave != null ){
                    refacciones_limpias.splice(refacciones_limpias.findIndex( refaccion_buscar => refaccion_buscar.Codigo == refaccion.Codigo ),1);
                    refacciones_limpias.push(refaccion);
                }
            } );
            //console.log("Refacciones procesadas");

            cliente = respuesta_json['cliente'];
            descuentos = respuesta_json['descuentos'];

            refacciones = refacciones_limpias;
            primera.innerText = refacciones.length ? 1 : 0;
            pagina.valueAsNumber = refacciones.length ? 1 : 0;
            ultima.innerText = Math.ceil(refacciones.length / refacciones_pagina);
            mostrar_refacciones();
        }

        function alertar_insercion(codigo) {
            let datos = new FormData();
            datos.append('codigo', codigo);

            fetch('modelo/agregar_refaccion', {
                method: 'POST',
                body: datos
            })
            .then((respuesta) => {
                return respuesta.json();
            })
            .catch(error => {
                alert('Error al agregar la refaccion: ' + error);
            })
            .then(respuesta_json => {
                // if(respuesta_json.loguear){
                //     document.location.href = 'https://www.marverrefacciones.mx/login.php'; 
                // }
                if(respuesta_json.agregada){
                    let alerta = document.querySelector('.alert').cloneNode(true);
                    alerta.querySelector('div').innerText = 'Refacción ' + codigo + ' agregada al carrito';
                    alerta.classList.add('d-flex');
                    alerta.classList.remove('d-none');
                    document.querySelector('body').appendChild(alerta);
                    setTimeout(() => { new bootstrap.Alert(alerta).close(); }, 2000);
                }
            });
        }

        function mostrar_refacciones() {
            cuerpo_refacciones.replaceChildren();
            let inicio = (pagina.valueAsNumber - 1) * refacciones_pagina;
            refacciones.slice(inicio, inicio + refacciones_pagina).forEach(refaccion => {
                let columna = document.querySelector('.col').cloneNode(true);

                columna.querySelector('img').onerror = () => {
                    columna.querySelector('img').onerror = () => {
                        columna.querySelector('img').src = 'img/logo.png';
                    };
                    columna.querySelector('img').src = refaccion['imagen'];
                };

                try{
                    columna.querySelector('img').src = 'https://marver.mx/img/fabricantes/' + refaccion['fabricante_refaccion'] + '/' + refaccion['clave'].replace('/', '-').replace('\\', '-');
                }catch(exc){
                    columna.querySelector('img').src = refaccion['imagen'];
                }

                columna.querySelector('h5').innerText = refaccion['Producto'];

                columna.querySelectorAll('li')[0].innerText = refaccion['Codigo'];
                columna.querySelector('button').onclick = ()=>{alertar_insercion(refaccion['Codigo']);};

                let datos_precio = new FormData();
                datos_precio.append('codigo', refaccion['Codigo']);

                fetch('modelo/precio',{
                        method: 'POST',
                        body: datos_precio
                }).then( (respuesta)=>{
                    return respuesta.json();
                }).catch( error => {
                    alert('Error al consultar los prescios: ' + error);
                }).then( respuesta_json =>{
                    
                    if( !respuesta_json.length ){
                        columna.remove();
                        return;
                    }

                    /* CALCULO PRECIO */

                    let precio = respuesta_json[0]['Costo'] * ( 1 + ( ( cliente['Utilidad'] > 0 ? cliente['Utilidad'] : respuesta_json[0]['Utilidades'] ) * 0.01) );

                    /* CALCULO PRECIO */

                    /* CALCULO DESCUENTOS */

                    let descuento_esperado = cliente['DescuentoUniversal'];

                    for (let i = 0; i < descuentos.length; i++) {
                        let descuento = descuentos[i];
                        if( descuento['Sistema'] == refaccion['Sistema'] ){
                            descuento_esperado =
                                (1-(
                                    (1-descuento_esperado*0.01) *
                                    (1-descuento['DescuentoSistema']*0.01)
                                ))*100;
                            break;
                        }
                    }

                    for (let i = 0; i < descuentos.length; i++) {
                        let descuento = descuentos[i];
                        if( descuento['Subsistema'] == refaccion['Subsistema'] ){
                            descuento_esperado =
                                (1-(
                                    (1-descuento_esperado*0.01) *
                                    (1-descuento['DescuentoSubsistema']*0.01)
                                ))*100;
                            break;
                        }
                    }

                    for (let i = 0; i < descuentos.length; i++) {
                        let descuento = descuentos[i];
                        if( descuento['Producto'] == refaccion['Producto'] ){
                            descuento_esperado =
                                (1-(
                                    (1-descuento_esperado*0.01) *
                                    (1-descuento['DescuentoProducto']*0.01)
                                ))*100;
                            break;
                        }
                    }

                    for (let i = 0; i < descuentos.length; i++) {
                        let descuento = descuentos[i];
                        if( descuento['Fabricante'] == refaccion['Fabricante'] ){
                            descuento_esperado =
                                (1-(
                                    (1-descuento_esperado*0.01) *
                                    (1-descuento['DescuentoFabricante']*0.01)
                                ))*100;
                            break;
                        }
                    }

                    precio = precio * ( 1 - descuento_esperado * 0.01 );

                    /* CALCULO DESCUENTOS */

                    /* CALCULO IVA */

                    //precio = precio * ( 1 + respuesta_json[0]['Iva'] * 0.01 );
                    precio = precio * ( 1 + 16 * 0.01 );

                    /* CALCULO IVA */

                    columna.querySelector('h6').innerText = '$ ' + precio.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];

                    columna.querySelectorAll('li')[2].innerText = respuesta_json[0]['Existencia'] > 0 ? 'Stock' : 'SP';
                    columna.querySelectorAll('li')[2].style.color = respuesta_json[0]['Existencia'] > 0 ? 'green' : 'orange';
                });

                let modal = document.querySelector('.modal').cloneNode(true);
                let modal_real = new bootstrap.Modal(modal);

                columna.querySelectorAll('li')[1].addEventListener("click", function() {
                    modal_real.show();

                    if( modal.querySelectorAll('tbody')[0].children.length == 0 && modal.querySelectorAll('tbody')[1].children.length == 0 ){
                        modal.querySelectorAll('p')[3].innerHTML = '<strong>Precio: </strong>' + columna.querySelector('h6').innerText;

                        let datos_extra = new FormData();
                        datos_extra.append('codigo', refaccion['Codigo']);
                        datos_extra.append('clave', refaccion['clave']);

                        fetch('modelo/sustitutos_aplicaciones', {
                            method: 'POST',
                            body: datos_extra
                        })
                        .then((respuesta) => {
                            return respuesta.json();
                        })
                        .catch(error => {
                            alert('Error al consultar los sustitutos y aplicaciones: ' + error);
                        })
                        .then(respuesta_json => {

                            respuesta_json['sustitutos'].forEach( sustituto => {
                                let tr = document.createElement('tr');

                                let td = document.createElement('td');
                                td.innerText = sustituto['Codigosustituto'];
                                td.classList.add('d-none');
                                td.classList.add('d-sm-table-cell');
                                tr.appendChild(td);

                                td = document.createElement('td');
                                td.innerText = sustituto['Fabricante'];
                                tr.appendChild(td);
                                
                                let datos_precio_sustituto = new FormData();
                                datos_precio_sustituto.append('codigo', sustituto['Codigosustituto']);

                                fetch('modelo/precio',{
                                        method: 'POST',
                                        body: datos_precio_sustituto
                                }).then( (respuesta)=>{
                                    return respuesta.json();
                                }).catch( error => {
                                    alert('Error al consultar los prescios: ' + error);
                                }).then( respuesta_sustituto =>{

                                    /* CALCULO PRECIO */
                                    let precio_sustituto = respuesta_sustituto[0]['Costo'] * ( 1 + ( ( cliente['Utilidad'] > 0 ? cliente['Utilidad'] : respuesta_sustituto[0]['Utilidades'] ) * 0.01) );
                                    /* CALCULO PRECIO */

                                    /* CALCULO DESCUENTOS */

                                    let descuento_esperado_sustituto = cliente['DescuentoUniversal'];
                                    
                                    for (let i = 0; i < descuentos.length; i++) {
                                        let descuento = descuentos[i];
                                        if( descuento['Sistema'] == sustituto['Sistema'] ){
                                            descuento_esperado_sustituto =
                                                (1-(
                                                    (1-descuento_esperado_sustituto*0.01) *
                                                    (1-descuento['DescuentoSistema']*0.01)
                                                ))*100;
                                            break;
                                        }
                                    }

                                    for (let i = 0; i < descuentos.length; i++) {
                                        let descuento = descuentos[i];
                                        if( descuento['Subsistema'] == sustituto['Subsistema'] ){
                                            descuento_esperado_sustituto =
                                                (1-(
                                                    (1-descuento_esperado_sustituto*0.01) *
                                                    (1-descuento['DescuentoSubsistema']*0.01)
                                                ))*100;
                                            break;
                                        }
                                    }

                                    for (let i = 0; i < descuentos.length; i++) {
                                        let descuento = descuentos[i];
                                        if( descuento['Producto'] == sustituto['Producto'] ){
                                            descuento_esperado_sustituto =
                                                (1-(
                                                    (1-descuento_esperado_sustituto*0.01) *
                                                    (1-descuento['DescuentoProducto']*0.01)
                                                ))*100;
                                            break;
                                        }
                                    }

                                    for (let i = 0; i < descuentos.length; i++) {
                                        let descuento = descuentos[i];
                                        if( descuento['Fabricante'] == sustituto['Fabricante'] ){
                                            descuento_esperado_sustituto =
                                                (1-(
                                                    (1-descuento_esperado_sustituto*0.01) *
                                                    (1-descuento['DescuentoFabricante']*0.01)
                                                ))*100;
                                            break;
                                        }
                                    }

                                    precio_sustituto = precio_sustituto * ( 1 - descuento_esperado_sustituto * 0.01 );
                                    /* CALCULO DESCUENTOS */

                                    /* CALCULO IVA */
                                    //precio = precio * ( 1 + respuesta_sustituto[0]['Iva'] * 0.01 );
                                    precio_sustituto = precio_sustituto * ( 1 + 16 * 0.01 );
                                    /* CALCULO IVA */

                                    let td = document.createElement('td');
                                    td.innerText = '$' + precio_sustituto.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                                    td.style.color = 'green';
                                    tr.appendChild(td);

                                    td = document.createElement('td');
                                    button = document.createElement('button');
                                    button.classList.add('btn');
                                    button.innerHTML = '<i class="fa-solid fa-cart-shopping"></i>';

                                    if(respuesta_sustituto[0]['Existencia'] > 0){
                                        button.classList.add('btn-primary');
                                        button.onclick = ()=>{
                                            alertar_insercion(sustituto['Codigosustituto']);
                                        };
                                    }else{
                                        button.classList.add('btn-secondary');
                                        button.classList.add('disabled');
                                    }
                                    
                                    td.appendChild(button);
                                    tr.appendChild(td);

                                    modal.querySelectorAll('tbody')[0].appendChild(tr);
                                });
                            } );

                            // Agrupar elementos por marca, modelo y motor
                            let result = respuesta_json['aplicaciones'].reduce((acc, curr) => {

                                let key = `${curr.marca}_${curr.modelo}_${curr.motor}_${curr.informacion}`;
                                let year = curr.año;

                                // Si ya existe la clave en el objeto, actualizar el rango de años
                                if (acc[key]) {
                                    acc[key].inicio = Math.min(acc[key].inicio, year);
                                    acc[key].fin = Math.max(acc[key].fin, year);
                                } else {
                                    // Si no existe la clave, crear un nuevo elemento
                                    acc[key] = { marca: curr.marca, modelo: curr.modelo, motor: curr.motor, informacion: curr.informacion, inicio: year, fin: year };
                                }

                                return acc;
                            }, {});

                            // Convertir el objeto de nuevo a un array
                            let groupedData = Object.values(result);

                            groupedData.forEach( sustituto => {
                                let tr = document.createElement('tr');

                                let td = document.createElement('td');
                                td.innerText = sustituto['marca'];
                                tr.appendChild(td);

                                td = document.createElement('td');
                                td.innerText = sustituto['inicio']+'-'+sustituto['fin'];
                                tr.appendChild(td);

                                td = document.createElement('td');
                                td.innerText = sustituto['modelo'];
                                tr.appendChild(td);

                                td = document.createElement('td');
                                td.innerText = sustituto['motor'];
                                tr.appendChild(td);

                                if (sustituto['informacion'].length > 0) {
                                    td = document.createElement('td');
                                    td.innerHTML = '<button type="button" class="btn btn-sm btn-primary rounded-circle" data-bs-placement="top" data-bs-content="'+sustituto['informacion']+'"><i class="bi bi-info-circle"></i></button>';
                                    let popover = new bootstrap.Popover(td.querySelector('button'));
                                    td.querySelector('button').addEventListener("blur", function() {
                                        popover.hide();
                                    });
                                    tr.appendChild(td);
                                }else{
                                    td = document.createElement('td');
                                    td.innerHTML = '<button type="button" class="btn btn-sm btn-secondary disabled rounded-circle" data-bs-placement="top" data-bs-content=""><i class="bi bi-info-circle"></i></button>';
                                    tr.appendChild(td);
                                }

                                modal.querySelectorAll('tbody')[1].appendChild(tr);
                            } );

                        });
                    }

                });

                modal.querySelector('h2').innerText = refaccion['Codigo'];
                modal.querySelector('h3').innerText = refaccion['Producto'];
                modal.querySelectorAll('p')[0].innerHTML = '<strong>Fabricante: </strong>' + refaccion['Fabricante'];
                modal.querySelectorAll('p')[1].innerHTML = '<strong>Sistema: </strong>' + refaccion['Sistema'];
                modal.querySelectorAll('p')[2].innerHTML = '<strong>Subsistema: </strong>' + refaccion['Subsistema'];
                modal.querySelectorAll('p')[3].style.color = 'green';
                modal.querySelectorAll('p')[4].innerHTML = '<strong>Descripcion: </strong>' + refaccion['Descripcion'];
                modal.querySelectorAll('button')[1].onclick = ()=>{alertar_insercion(refaccion['Codigo']);};

                modal.querySelector('img').onerror = () => {
                    modal.querySelector('img').onerror = () => {
                        modal.querySelector('img').src = 'img/logo.png';
                    };
                    modal.querySelector('img').src = refaccion['imagen'];
                };

                try{
                    modal.querySelector('img').src = 'https://marver.mx/img/fabricantes/' + refaccion['fabricante_refaccion'] + '/' + refaccion['clave'].replace('/', '-').replace('\\', '-');
                }catch(exc){
                    modal.querySelector('img').src = refaccion['imagen'];
                }

                columna.classList.remove('d-none');
                cuerpo_refacciones.appendChild(columna);

                cuerpo_refacciones.appendChild(modal);

            });

            document.querySelector('#spinner').classList.remove('show');
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