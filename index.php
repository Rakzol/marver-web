<?php
    require_once('modelo/guardar_estadisticas.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Marver Refacciones - Inicio</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Refacciones, autos, partes, piezas, vehiculos, automoviles" name="keywords">
    <meta name="description"
        content="Refacciones para autos y vehículos de gran calidad y variedad con ubicaciones en Los Mochis, Guasave e Higuera de Zaragoza" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet" /> -->

    <!-- Favicon -->
    <link href="https://www.marverrefacciones.mx/img/logo.png" rel="icon">

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
                <a href="#" class="navbar-brand m-0 p-0" style="display: block;">
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


        <div class="container-fluid">

            <a href="#" class="navbar-brand ms-3 d-xxl-none">Marver</a>
            <button type="button" class="navbar-toggler me-3" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav p-3 p-xxl-0">
                    <a href="#" class="nav-item nav-link active">INICIO</a>
                    <a href="#nosotros" class="nav-item nav-link">NOSOTROS</a>
                    <a href="https://www.marverrefacciones.mx/catalogo.php" class="nav-item nav-link">REFACCIONES</a>
                    <a href="#proveedores" class="nav-item nav-link">PROVEEDORES</a>
                    <a href="#inicio-tips" class="nav-item nav-link">TIPS</a>
                    <a href="#sucursales" class="nav-item nav-link">SUCURSALES</a>
                    <a href="#contenedor_correo" class="nav-item nav-link">CONTACTO</a>
                </div>
                <div class="navbar-nav ms-auto p-3 p-xxl-0">
                <?php
                    if(isset($_SESSION['usuario'])){
                        echo '<a href="https://www.marverrefacciones.mx/estado_de_cuenta" class="nav-item nav-link"><i class="fa-solid fa-file"></i> ESTADO DE CUENTA</a>';
                        echo '<a href="https://www.marverrefacciones.mx/pedidos.php" class="nav-item nav-link"><i class="fa-solid fa-truck"></i> PEDIDOS</a>';
                        echo '<a href="https://www.marverrefacciones.mx/carrito.php" class="nav-item nav-link"><i class="fa-solid fa-cart-shopping"></i> CARRITO</a>';
                        echo '<a href="https://www.marverrefacciones.mx/modelo/cerrar_sesion.php" class="nav-item nav-link"><i class="fa-solid fa-power-off"></i> CERRAR SESIÓN</a>';
                    }else{
                        echo '<a href="https://www.marverrefacciones.mx/login.php" class="nav-item nav-link"><i class="fa-solid fa-right-to-bracket"></i> INICIAR SESIÓN</a>';
                    }
                ?>
            </div>
            </div>


        </div>


    </nav>
    <!-- Navbar End -->


    <!-- Carousel Start -->

    <div id="carousel-inicio" class="carousel slide wow fadeInUp" data-wow-delay="0.1s" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/carousel-1.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-11.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
                <!-- <div class="carousel-caption d-md-block">
                    <p style="color: white;">Mereces viajar con</p>
                    <h5 style="color: gold;">La mejor seguridad</h5>
                </div> -->
            </div>
            <div class="carousel-item">
                <img src="img/carousel-2.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-22.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
                <!-- <div class="carousel-caption d-md-block">
                    <p style="color: white;">Con marver viaja a</p>
                    <h5 style="color: gold;">La máxima potencia</h5>
                </div> -->
            </div>
            <div class="carousel-item">
                <img src="img/carousel-3.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-33.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
            </div>
            <div class="carousel-item">
                <img src="img/carousel-4.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-44.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
            </div>
            <div class="carousel-item">
                <img src="img/carousel-5.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-55.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
            </div>
            <div class="carousel-item">
                <img src="img/carousel-6.png" class="d-block w-100 carrusel_grande" alt="tips para auto">
                <img src="img/carousel-66.png" class="d-block w-100 carrusel_chico" alt="tips para auto">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-inicio" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel-inicio" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
    </div>

    <!-- Carousel End -->


    <!-- Facts Start -->
    <div id="nosotros" class="container-xxl py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="fact-item bg-light rounded text-center h-100 p-5">
                        <i class="fa fa-certificate fa-4x text-primary mb-4"></i>
                        <h5 class="mb-3">Años de experiencia</h5>
                        <h1 class="display-5 mb-0" data-toggle="counter-up">38</h1>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="fact-item bg-light rounded text-center h-100 p-5">
                        <i class="fa fa-users-cog fa-4x text-primary mb-4"></i>
                        <h5 class="mb-3">Sucursales</h5>
                        <h1 class="display-5 mb-0" data-toggle="counter-up">3</h1>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="fact-item bg-light rounded text-center h-100 p-5">
                        <i class="fa fa-users fa-4x text-primary mb-4"></i>
                        <h5 class="mb-3">Clientes Satisfechos</h5>
                        <h1 class="display-5 mb-0 numero_positivo" data-toggle="counter-up">5000</h1>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="fact-item bg-light rounded text-center h-100 p-5">
                        <i class="fa fa-check fa-4x text-primary mb-4"></i>
                        <h5 class="mb-3">Proveedores de calidad</h5>
                        <h1 class="display-5 mb-0 numero_positivo" data-toggle="counter-up">150</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Facts End -->


    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="contenedor-imagen-alta">
                        <img class="imagen-alta" src="img/about.jpg" alt="">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="h-100">
                        <h6 class="section-title bg-white text-start text-primary pe-3">Sobre Nosotros</h6>
                        <h1 class="display-6 mb-4">#1 Soluciones Para Refacciones <span class="text-primary">38
                                años</span> De Experiencia</h1>
                        <p>Somos especialistas en refacciones y accesorios para la industria automotriz.</p>
                        <p class="mb-4">Contamos con más de 30 años de experiencia y un extenso surtido de piezas y
                            marcas para tu automóvil, brindando el mejor servicio y garantizando calidad y precio en
                            nuestros productos.</p>
                        <div class="d-flex align-items-center mb-4 pb-2">
                            <img class="flex-shrink-0 rounded-circle" src="img/team-8.jpg" alt=""
                                style="width: 50px; height: 50px;">
                            <div class="ps-4">
                                <h6>El mejor servicio</h6>
                                <small>Con la mejor atención</small>
                            </div>
                        </div>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="#nosotros_mas">Leer mas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Feature Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="h-100">
                        <h6 class="section-title bg-white text-start text-primary pe-3">Porque elegirnos</h6>
                        <h1 class="display-6 mb-4">¿Porque las personas confian en nosotros?</h1>
                        <p class="mb-4">Estamos convencidos que el motor que mueve cada día a Marver Refacciones son
                            nuestros clientes es por eso que nos preocupamos porque reciban la mejor atención y calidad
                            de servicio, así como la mejor opción de refacciones para cada necesidad, eso hace que su
                            confianza en nosotros crezca.</p>
                        <div class="d-flex align-items-center mb-4 pb-2">
                            <img class="flex-shrink-0 rounded-circle" src="img/tips/1.jpg" alt=""
                                style="width: 50px; height: 50px;">
                            <div class="ps-4">
                                <h6>Cuidamos de tu auto</h6>
                                <small>Consejos</small>
                            </div>
                        </div>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="#inicio-tips">Leer mas</a>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="contenedor-imagen-alta">
                        <img class="imagen-alta" src="img/feature.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Feature End -->


    <!-- Service Start -->
    <div class="container-xxl py-5" id="refacciones">
        <div class="container" id="contenedor_refacciones">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h6 class="texto-subtitular text-center text-primary px-3">Refacciones</h6>
                <h1 class="display-6 mb-4">Nos enfocamos en ser los mejores en todos los sectores</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/ACCESORIOS.jpg" alt="">
                        <h4 class="mb-0">Accesorios</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/BALEROS Y RETENES.jpg" alt="">
                        <h4 class="mb-0">Baleros</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/COLISION Y CHASIS.jpg" alt="">
                        <h4 class="mb-0">Chasis</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/COMBUSTION.jpg" alt="">
                        <h4 class="mb-0">Combustión</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/DIRECCION.jpg" alt="">
                        <h4 class="mb-0">Dirección</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/ELECTRICO.jpg" alt="">
                        <h4 class="mb-0">Eléctrico</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/EMBRAGUES.jpg" alt="">
                        <h4 class="mb-0">Embragues</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/ENCENDIDO (ARRANQUE).jpg" alt="">
                        <h4 class="mb-0">Arranque</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/ENFRIAMIENTO.jpg" alt="">
                        <h4 class="mb-0">Enfriamiento</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/FERRETERIA.jpg" alt="">
                        <h4 class="mb-0">Ferreteria</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/FRENO Y MAZA.jpg" alt="">
                        <h4 class="mb-0">Frenos</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/ILUMINACION.jpg" alt="">
                        <h4 class="mb-0">Iluminación</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/LUBRICANTES.jpg" alt="">
                        <h4 class="mb-0">Lubricantes</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/MOTOR.jpg" alt="">
                        <h4 class="mb-0">Motor</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/SUSPENSION.jpg" alt="">
                        <h4 class="mb-0">Suspensión</h4>
                    </a>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <a class="service-item d-block rounded text-center h-100 p-4" href="#refacciones">
                        <img class="img-fluid rounded mb-4" src="img/refacciones/TRANSMISION.jpg" alt="">
                        <h4 class="mb-0">Transmisión</h4>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->

    <!-- Project Start -->
    <div class="container-xxl py-5" id="proveedores">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h6 class="section-title bg-white text-center text-primary px-3">Nuestros Proveedores</h6>
                <h1 class="display-6 mb-4">Colaboramos con las mejores marcas para ofrecerte las refacciones que estás
                    buscando.</h1>
            </div>
            <div class="owl-carousel project-carousel wow fadeInUp" data-wow-delay="0.1s">
                <div class="project-item border rounded h-100 p-4" data-dot="01">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_gonher.png" alt="">
                        <a href="img/logo_gonher.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Grupo Gonher</h6>
                    <span>Más de 60 años de protección para tu motor. Filtros, Aceites, Baterías, Aditivos, Bujías,
                        Anticongelantes. ¡Conóce el Catálogo!</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="02">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_injetech_components.png" alt="">
                        <a href="img/logo_injetech_components.png" data-lightbox="project"><i
                                class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Injetech Components</h6>
                    <span>Es una marca orgullosa de sus 18 años de experiencia en México. Durante este tiempo hemos
                        logrado posicionarnos como la mejor.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="03">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_dynamik_carboceramic.png" alt="">
                        <a href="img/logo_dynamik_carboceramic.png" data-lightbox="project"><i
                                class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Dynamik Carboceramic</h6>
                    <span>Los productos Dynamik se fabrican de acuerdo con los más estrictos estándares de calidad y
                        certificaciones europeas.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="04">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_eagle_bhp.png" alt="">
                        <a href="img/logo_eagle_bhp.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Eagle BHP</h6>
                    <span>Empresa líder, en la fabricación y comercialización de autopartes, siendo pioneros en el
                        desarrollo de nuevos productos en soporte de motor.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="05">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_trackone.png" alt="">
                        <a href="img/logo_trackone.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Trackone</h6>
                    <span>Es una empresa 100% Mexicana dedicada a la fabricación de autopartes con altos niveles de
                        calidad y resistencia.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="06">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_shark_wp.png" alt="">
                        <a href="img/logo_shark_wp.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Shark W.P.</h6>
                    <span>En su catálogo de productos cuenta con bombas de agua electricas.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="07">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_bruck_germany.png" alt="">
                        <a href="img/logo_bruck_germany.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Bruck Germany</h6>
                    <span>Nuestras autopartes de colisión e interiores están diseñadas con los mejores materiales y
                        altos estándares de calidad</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="08">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_cartek.png" alt="">
                        <a href="img/logo_cartek.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Cartek</h6>
                    <span>El Grupo Cartek es un fabricante y comercializador de equipos de servicio automotriz compuesto
                        por 10 compañías internacionales propiedad de un grupo privado de capital de riesgo.</span>
                </div>
                <div class="project-item border rounded h-100 p-4" data-dot="09">
                    <div class="position-relative mb-4">
                        <img class="img-fluid rounded" src="img/logo_ecom.png" alt="">
                        <a href="img/logo_ecom.png" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                    <h6>Ecom</h6>
                    <span>Fundada en 1989 por nuestro grupo, con la finalidad de fabricar y comercializar productos
                        químicos automotrices.</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Project End -->


    <!-- Team Start -->
    <div id="nosotros_mas" class="container-xxl py-2">
        <div id="contenedor_videos" class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h6 class="section-title bg-white text-center text-primary px-3">Nuestro equipo</h6>
                <h1 class="display-6 mb-4">Tenemos un equipo ideal para solucionar tu problema</h1>
            </div>
            <div class="row g-4">
                <div class="col-xxl-4 col-lg-6 col-md-12 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item text-center p-2">
                        <!-- <img class="imagen-video img-fluid border w-95 p-2 mb-4" src="img/team-6.jpg" alt=""> -->
                        <video class="img-fluid border w-95 p-2 mb-4" autoplay loop muted playsinline>
                            <source src="img/team-6.mp4" type="video/mp4">
                        </video>
                        <div class="team-text">
                            <h5>El Mejor Servicio</h5>
                            <span>Con la mejor calidad</span>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-6 col-md-12 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item text-center p-2">
                        <!-- <img class="imagen-video img-fluid border w-95 p-2 mb-4" src="img/team-8.jpg" alt=""> -->
                        <video class="img-fluid border w-95 p-2 mb-4" autoplay loop muted playsinline>
                            <source src="img/team-8.mp4" type="video/mp4">
                        </video>
                        <div class="team-text">
                            <h5>La Mejor Atención</h5>
                            <span>Con la mejor actitud</span>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-6 col-md-12 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item text-center p-2">
                        <!-- <img class="imagen-video img-fluid border w-95 p-2 mb-4" src="img/team-10.jpg" alt=""> -->
                        <video class="img-fluid border w-95 p-2 mb-4" autoplay loop muted playsinline>
                            <source src="img/team-10.mp4" type="video/mp4">
                        </video>
                        <div class="team-text">
                            <h5>Estamos Preparados</h5>
                            <span>Para ofrecer lo mejor</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Testimonial Start -->
    <div class="container-xxl py-5" id="inicio-tips">
        <div class="container">
            <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h6 class="section-title bg-white text-center text-primary px-3">Tips</h6>
                <h1 class="display-6 mb-4">Cuidamos tu auto y tu familia!</h1>
            </div>
            <div class="owl-carousel project-carousel wow fadeInUp" data-wow-delay="0.1s">
                <div class="project-item border rounded h-100 p-2" data-dot="01">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/1.jpg" alt="">
                        <a href="img/tips/1.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="02">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/2.jpg" alt="">
                        <a href="img/tips/2.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="03">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/3.jpg" alt="">
                        <a href="img/tips/3.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="04">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/4.jpg" alt="">
                        <a href="img/tips/4.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="05">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/5.jpg" alt="">
                        <a href="img/tips/5.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="06">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/25.jpg" alt="">
                        <a href="img/tips/25.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="07">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/24.jpg" alt="">
                        <a href="img/tips/24.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="08">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/23.jpg" alt="">
                        <a href="img/tips/23.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="09">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/22.jpg" alt="">
                        <a href="img/tips/22.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
                <div class="project-item border rounded h-100 p-2" data-dot="10">
                    <div class="position-relative">
                        <img class="img-fluid rounded" src="img/tips/21.jpg" alt="">
                        <a href="img/tips/21.jpg" data-lightbox="project"><i class="fa fa-eye fa-2x"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial End -->

    <!-- Map Start -->
    <div id="sucursales" class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
        <h6 class="section-title bg-white text-center text-primary px-3">Sucursales</h6>
        <h1 class="display-6">Conoce nuestras sucursales!</h1>
    </div>
    <iframe src="https://www.google.com/maps/d/embed?mid=1kMVlqIuMnQtT5WQJwHJmbf-MgBfnfIc&ehbc=2E312F"
        class="mapa mb-5"></iframe>
    <!-- Map End -->

    <!-- Contact Start -->
    <div id="contenedor_correo" class="container-xxl py-5">
        <div id="contenedor_correo_hijo" class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <h6 class="texto-subtitular text-center text-primary px-3">Contactenos</h6>
                <h1 class="display-6 mb-4">Si tiene alguna pregunta, no dude en escribirnos</h1>
            </div>
            <div class="row g-0 justify-content-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.5s">
                    <p class="text-center mb-4" id="contenedor_correo_hijo_parrafo">Nos pondremos en contacto con usted
                        lo más pronto posible, para una comunicación más instantánea utilice nuestras <a href="#">Redes
                            sociales</a>.</p>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" placeholder="Nombre">
                                    <label for="name">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" placeholder="Correo">
                                    <label for="email">Correo</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" placeholder="Asunto">
                                    <label for="subject">Asunto</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Escriba su mensaje aquí" id="message"
                                        style="height: 200px"></textarea>
                                    <label for="message">Mensaje</label>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button class="btn btn-primary rounded-pill py-3 px-5" type="submit">Enviar
                                    mensaje</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

    <!-- Footer Start -->
    <div id="contenedor_pie" class="container-fluid bg-dark text-body footer pt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5 ps-5 pe-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-2">
                    <img src="img/logo_pie.png" alt="">
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <h5 class="text-light mb-2">Dirección</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Av. Santos Degollado #451. Los Mochis,
                        Sinaloa.</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>668 812 3595</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>ventas@marverrefacciones.mx</p>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <h5 class="text-light mb-2">Enlaces</h5>
                    <a class="btn btn-link" href="#nosotros">Sobre nosotros</a>
                    <a class="btn btn-link" href="#contenedor_correo">Contactanos</a>
                    <a class="btn btn-link" href="#refacciones">Nuestras Refacciones</a>
                    <a class="btn btn-link" href="#">Términos y condiciones</a>
                    <a class="btn btn-link" href="#">Soporte</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-light mb-2">Metodos de pago</h5>
                    <div style="display: block;">
                        <i class="fa fa-money-bill metodo_pago"></i>
                        <i class="fab fa-cc-visa metodo_pago"></i>
                    </div>
                    <div style="display: block;">
                        <i class="fab fa-cc-mastercard metodo_pago"></i>
                        <i class="fa fa-hand-holding-usd metodo_pago"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid copyright ps-5 pe-5">
            <div class="container">
                <div class="row">
                    <div class="text-center text-md-start mb-3 mb-md-0">
                        &copy; <a href="#">marverrefacciones.com</a>,
                        Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i
            class="bi bi-arrow-up"></i></a>

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