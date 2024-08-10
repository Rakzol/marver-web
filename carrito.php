<?php
    require_once('modelo/guardar_estadisticas.php');
    if(!isset($_SESSION['usuario'])){
        header("Location: https://www.marverrefacciones.mx/login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.1/mdb.min.css" rel="stylesheet" />

    <style>
        .gradient-custom,
        html {
            background-color: #e15454;
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
            content: '$ ';
        }

        .is-invalid {
            margin-bottom: 0 !important;
        }

        .form-notch-middle {
            width: 61.6px !important;
        }
    </style>

    <style>
        #mapa{
            margin: auto;
            max-width: 400px;
            aspect-ratio: 1 / 1;
            width: 100%;
        }

        .alert{
            border: 0px;
            padding: 0px;
            height: 0px;
            transition: 1s;
        }
    </style>

</head>

<body>


    <!-- Spinner Start -->
    <div id="spinner" manual='si' 
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border position-relative text-primary" style="width: 6rem; height: 6rem;" role="status">
        </div>
        <img class="text-primary position-absolute top-50 start-50 translate-middle" src="img/logo.png" style="width: 5rem;">
    </div>
    <!-- Spinner End -->

    <!-- Brand & Contact Start -->
    <div class="container-fluid py-3 px-5 wow fadeIn barra-redes" data-wow-delay="0.1s">
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
    </nav>
    <!-- Navbar End -->

    <div class="row d-none plantilla_refaccion">
        <div class="col-lg-3 col-md-12 mb-4 mb-lg-0">
            <!-- Image -->
            <div class="bg-image hover-overlay hover-zoom ripple rounded text-center" data-mdb-ripple-color="light">
                <img src="img/fabricantes/LTH/12N14-3A" class="w-100 d-none" alt="LTH Acumualdor" style="max-width: 300px;" />
                <div style="margin: 23px;" class="spinner-border text-primary spinner_refaccion" role="status"><span class="sr-only">Loading...</span></div>
                <a >
                    <div class="mask" style="background-color: rgba(251, 251, 251, 0.2)"></div>
                </a>
            </div>
            <!-- Image -->
        </div>

        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <!-- Data -->
            <p><strong class="producto">LTH Acumualdor</strong></p>
            <p class="codigo">12N14-3A</p>
            <p class="dinero precio">2000</p>
            <button type="button" class="btn btn-primary btn-sm me-1 mb-2" data-mdb-toggle="tooltip"
                title="Eliminar refacción" onclick='eliminar_refaccion(this.parentNode);'>
                <i class="fas fa-trash"></i>
            </button>
            <!-- Data -->
        </div>

        <div class="col-lg-5 col-md-6 mb-lg-0">
            <!-- Quantity -->
            <div class="d-flex mb-3" style="max-width: 300px">
                <button class="btn btn-primary px-3 me-2"
                    onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.parentNode.querySelector('input[type=number]').onchange();">
                    <i class="fas fa-minus"></i>
                </button>

                <div class="form-outline">
                    <input id="form1" min="1" name="quantity" value="1" type="number" step="1"
                        class="form-control cantidad" onchange="validar_cantidad(this);" />
                    <label class="form-label" for="form1">Cantidad</label>
                    <div class="invalid-feedback" style="white-space: nowrap;">
                        346,785 existencias
                    </div>
                </div>

                <button class="btn btn-primary px-3 ms-2"
                    onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.parentNode.querySelector('input[type=number]').onchange();">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <!-- Quantity -->

            <p class="text-start text-md-center existencias"></p>

            <!-- Price -->
            <p class="text-start text-md-center" style="font-size: 1.25rem;">
                <strong class="dinero total">17.99</strong>
            </p>
            <!-- Price -->
        </div>

        <hr class="my-4" />
    </div>

    <section class="h-100 gradient-custom">
        <div class="container py-5">
            <div class="row d-flex justify-content-center my-4">
                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h5 class="" style="margin-bottom: 0;">Refacciones</h5>
                            <!-- <select class="form-select" aria-label="Default select example" style="max-width: 300px;"
                                id="estado" onchange="consultar_carrito();">
                                <option value="CARRITO" selected>Carrito</option>
                                <option value="SURTIENDO">Surtiendo</option>
                                <option value="SURTIDO">Surtidas</option>
                                <option value="ENTREGANDO">Entregando</option>
                                <option value="ENTREGADO">Entregadas</option>
                            </select> -->
                        </div>
                        <div class="card-body" id="contenedor_refacciones">
                        </div>
                    </div>
                    <div class="card mb-4 mb-xl-0">
                        <div class="card-body">
                            <p><strong>Aceptamos</strong></p>
                            <img class="me-2" width="45px"
                                src="https://mdbcdn.b-cdn.net/wp-content/plugins/woocommerce-gateway-stripe/assets/images/visa.svg"
                                alt="Visa" />
                            <img class="me-2" width="45px"
                                src="https://mdbcdn.b-cdn.net/wp-content/plugins/woocommerce-gateway-stripe/assets/images/mastercard.svg"
                                alt="Mastercard" />
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Información de pedido</h5>
                        </div>
                        <div class="card-body">

                        <div class="card-header py-3">
                                <h5 class="mb-2">Ubicacón</h5>
                                <div id="mapa" ></div>
                                <h6 class="mb-2 mt-2">Dirección</h6>
                                <input type="text" class="form-control mb-2" id="direccion" spellcheck="false" placeholder="calle y numero de casa" >
                                <button type="button" onclick="buscar_direccion();" class="btn btn-primary btn-lg btn-block">
                                Buscar Dirección
                                </button>

                                <div class="alert alert-success mt-3 mb-1" role="alert">
                                
                                </div>

                            </div>

                        <div class="card-header py-3">
                                <h5 class="mb-2">Tipo de compra</h5>
                                <select class="form-select" aria-label="Default select example" id="tipo_de_compra" onchange="cambiar_tipo_decompra();">
                                </select>
                            </div>

                        <div class="card-header py-3">
                                <h5 class="mb-2">Forma de pago</h5>
                                <select class="form-select" aria-label="Default select example" id="forma_de_pago">
                                </select>
                            </div>

                            <div class="card-header py-3">
                                <h5 class="mb-2">Comprobante</h5>
                                <select class="form-select" aria-label="Default select example" id="tipo_de_comprobante">
                                </select>
                            </div>

                            <div class="card-header py-3">
                                <h5 class="mb-2">Entrega</h5>
                                <select class="form-select" aria-label="Default select example" id="MEntrega">
                                    <option value="MOTO" selected>Moto</option>
                                    <option value="CAMIONETA" >Camioneta</option>
                                    <option value="PERSONAL" >Personal</option>
                                </select>
                            </div>

                            <div class="card-header py-3">
                                <h5 class="mb-3">Dirección</h5>

                                <h6 class="mb-1">Estado</h6>
                                <input type="text" class="form-control mb-2" disabled id="estado" >

                                <h6 class="mb-1">Ciudad</h6>
                                <input type="text" class="form-control mb-2" disabled id="ciudad" >

                                <h6 class="mb-1">Municipio</h6>
                                <input type="text" class="form-control mb-2" disabled id="municipio" >

                                <h6 class="mb-1">Colonia</h6>
                                <input type="text" class="form-control mb-2" disabled id="colonia" >

                                <h6 class="mb-1">Código postal</h6>
                                <input type="text" class="form-control mb-2" disabled id="codigo_postal" >

                                <h6 class="mb-1">Número interior</h6>
                                <input type="text" class="form-control mb-2" disabled id="numero_interior" >

                                <h6 class="mb-1">Número exterior</h6>
                                <input type="text" class="form-control mb-2" disabled id="numero_exterior" >

                                <h6 class="mb-1">Domicilio</h6>
                                <input type="text" class="form-control mb-2" disabled id="domicilio" >
                            </div>

                            <div class="card-header py-3">
                                <h5 class="mb-2">Observaciones</h5>
                                <textarea class="form-control" id="observaciones" rows="4"></textarea>
                            </div>

                            <ul class="list-group list-group-flush">
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-0">
                                    Refacciones
                                    <span class="dinero" id="total_refacciones">0.0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    Envio
                                    <span>Gratis</span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
                                    <div>
                                        <strong>Pago total</strong>
                                        <strong>
                                            <p class="mb-0">(IVA Incluido)</p>
                                        </strong>
                                    </div>
                                    <span><strong class="dinero" id="total_iva">0.0</strong></span>
                                </li>
                            </ul>

                            <!-- <div id="smart-button-container">
                            <div style="text-align: center;">
                                <div id="paypal-button-container"></div>
                            </div>
                            </div> -->

                            <button type="button" onclick="finalizar_pedido();" class="btn btn-primary btn-lg btn-block">
                                Finalizar Pedido
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
  <div class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="producto-modal-label">Inconveniente encontrado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
        </div>
      </div>
    </div>

  </div>


    <!-- MDB -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.1/mdb.min.js"></script>

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

    <script>
        let purchase_unit = {
            "description": "Refacciones compradas en Marver",
            "amount": {
              "currency_code": "MXN",
              "value": "946.9",
              "breakdown": { "item_total": {"currency_code": "MXN", "value":"946.9"} }
            },
            "items": [
              { "sku": "gp-10", "name": "refaccion1", "quantity": "10", "unit_amount": { "currency_code": "MXN", "value": "69.69" } },
              { "sku": "gp-69", "name": "refaccion2", "quantity": "5", "unit_amount": { "currency_code": "MXN", "value": "50" } }
            ]
          };

        function eliminar_refaccion(contenedor_refaccion){

            let datos = new FormData();
            datos.append('producto', contenedor_refaccion.querySelector('.codigo').innerText);

            contenedor_refaccion.parentNode.remove();

            actualizar_totales();

            fetch('modelo/eliminar_refaccion', {
                method: 'POST',
                body: datos
            })
            .catch(error => {
                alert('Error al eliminar la refaccion: ' + error);
            });
        }

        function actualizar_totales() {
            let total = 0;
            //purchase_unit["items"] = [];
            document.querySelectorAll('.plantilla_refaccion:not(.d-none)').forEach(refaccion => {
                total += parseFloat(refaccion.querySelector('.total').innerHTML);
                // purchase_unit["items"].push({
                //     "sku": refaccion.querySelector('.codigo').innerHTML,
                //     "name": refaccion.querySelector('.producto').innerHTML,
                //     "quantity": refaccion.querySelector('.cantidad').value,
                //     "unit_amount": {
                //         "currency_code": "MXN",
                //         "value": refaccion.querySelector('.precio').innerHTML }
                // });
            });
            // purchase_unit["amount"]["breakdown"]["item_total"]["value"] = ;
            // purchase_unit["amount"]["value"] = ;
            document.querySelector('#total_refacciones').innerText = total.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
            document.querySelector('#total_iva').innerText = total.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
        }

        function validar_cantidad(input) {
            if (input.valueAsNumber < 1 || isNaN(input.valueAsNumber)) {
                input.value = 1;
            }

            input.value = Math.trunc(input.valueAsNumber);

            input.parentNode.parentNode.parentNode.parentNode.querySelectorAll('.dinero')[1].innerText = (parseFloat(input.parentNode.parentNode.parentNode.parentNode.querySelector('.dinero').innerText) * input.valueAsNumber).toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
            input.classList.add('active');

            actualizar_totales();

            let datos = new FormData();
            datos.append('codigo', input.parentNode.parentNode.parentNode.parentNode.querySelector('.codigo').innerText);
            datos.append('cantidad', input.valueAsNumber);

            fetch('modelo/colocar_cantidad', {
                method: 'POST',
                body: datos
            })
            .catch(error => {
                alert('Error al cambiar el valor de la refaccion: ' + error);
            });
        }

        let preventa = 0;
        function cambiar_tipo_decompra(){

            let tipo_de_compra = document.querySelector("#tipo_de_compra");
            let forma_de_pago = document.querySelector("#forma_de_pago");
            let tipo_de_comprobante = document.querySelector("#tipo_de_comprobante");

            switch(tipo_de_compra.value){
                case '1':
                    forma_de_pago.innerHTML =
                        '<option value="01" selected>Efectivo</option>' +
                        '<option value="02" >Cheque nominativo</option>' +
                        '<option value="03" >Transferencia electrónica</option>' +
                        '<option value="04" >Tarjeta de crédito</option>' +
                        '<option value="28" >Tarjeta de débito</option>';
                    tipo_de_comprobante.innerHTML =
                        '<option value="1" selected>Factura</option>' +
                        '<option value="2">Recibo</option>';
                    break;
                case '2':
                    forma_de_pago.innerHTML =
                        '<option value="99" selected >Crédito</option>';
                    tipo_de_comprobante.innerHTML =
                        '<option value="1" selected>Factura</option>'+
                        ( preventa == 1 ? '<option value="3">Preventa</option>' : '' );
                    break;
            }
        }

        function consultar_carrito() {
            document.querySelector('#contenedor_refacciones').replaceChildren();

            let datos = new FormData();
            datos.append('estado', document.querySelector('#estado').value);

            fetch('modelo/consultar_carrito', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    alert('Error al consultar el carrito: ' + error);
                })
                .then(respuesta_json => {
                    //console.log(respuesta_json);

                    document.querySelector("#estado").value = respuesta_json['cliente']['Estado'];
                    document.querySelector("#ciudad").value = respuesta_json['cliente']['Ciudad'];
                    document.querySelector("#municipio").value = respuesta_json['cliente']['Municipio'];

                    document.querySelector("#colonia").value = respuesta_json['cliente']['Colonia'];

                    document.querySelector("#codigo_postal").value = respuesta_json['cliente']['Codigo_Postal'];

                    document.querySelector("#numero_interior").value = respuesta_json['cliente']['Num_interior'];

                    document.querySelector("#numero_exterior").value = respuesta_json['cliente']['Num_Exterior'];

                    document.querySelector("#domicilio").value = respuesta_json['cliente']['Domicilio'];
                    
                    /* Le mostramos la opcion de pago credito si la tiene habilitada, ademas si es su forma de pago por defecto la seleccionamos */
                    let credito = respuesta_json['cliente']['Credito'];
                    let tipo_de_compra = document.querySelector("#tipo_de_compra");
                    let forma_pago = respuesta_json['cliente']['FormaPago'];
                    switch(credito){
                        case 1:
                            tipo_de_compra.innerHTML = 
                                '<option value="1" ' + ( forma_pago != '99' ? 'selected' : '' ) + ' >Contado</option>'+
                                '<option value="2" ' + ( forma_pago == '99' ? 'selected' : '' ) + ' >Crédito</option>';
                            break;
                        case 0:
                            tipo_de_compra.innerHTML =
                                '<option value="1" selected>Contado</option>';
                            break;
                    }

                    preventa = respuesta_json['cliente']['Preventa'];
                    cambiar_tipo_decompra();

                    /* Seleccionamos la forma de pago default del cliente, si el selectedIndex es -1 significa que tiene una forma de pago por default incorrecta
                    Por lo tanto seleccionamos la primera que tenga la lista */
                    let forma_de_pago = document.querySelector("#forma_de_pago");
                    forma_de_pago.value = forma_pago;
                    if(forma_de_pago.selectedIndex == -1){
                        //console.log('forma de pago invalida');
                        forma_de_pago.selectedIndex = 0;
                    }

                    respuesta_json['productos'].forEach( (producto) =>{

                        let refaccion_html = document.querySelector('.plantilla_refaccion').cloneNode(true);
                        refaccion_html.querySelector('.producto').innerHTML = producto['Producto'];
                        refaccion_html.querySelector('.codigo').innerHTML = producto['Codigo'];

                        refaccion_html.querySelector('.cantidad').value = producto['cantidad'];
                        refaccion_html.querySelector('.cantidad').id = 'cantidad-' + producto['Codigo'];

                        refaccion_html.querySelector('.existencias').innerHTML = '<strong>Existencias:</strong> ' + producto['Existencia'];

                        /* CALCULO PRECIO */

                        let precio = producto['Costo'] * ( 1 + ( ( respuesta_json['cliente']['Utilidad'] > 0 ? respuesta_json['cliente']['Utilidad'] : producto['Utilidades'] ) * 0.01) );

                        /* CALCULO PRECIO */

                        let descuento_esperado = respuesta_json['cliente']['DescuentoUniversal'];

                        for (let i = 0; i < respuesta_json['descuentos'].length; i++) {
                            let descuento = respuesta_json['descuentos'][i];
                            if( descuento['Sistema'] == producto['Sistema'] ){
                                descuento_esperado =
                                    (1-(
                                        (1-descuento_esperado*0.01) *
                                        (1-descuento['DescuentoSistema']*0.01)
                                    ))*100;
                                break;
                            }
                        }

                        for (let i = 0; i < respuesta_json['descuentos'].length; i++) {
                            let descuento = respuesta_json['descuentos'][i];
                            if( descuento['Subsistema'] == producto['Subsistema'] ){
                                descuento_esperado =
                                    (1-(
                                        (1-descuento_esperado*0.01) *
                                        (1-descuento['DescuentoSubsistema']*0.01)
                                    ))*100;
                                break;
                            }
                        }

                        for (let i = 0; i < respuesta_json['descuentos'].length; i++) {
                            let descuento = respuesta_json['descuentos'][i];
                            if( descuento['Producto'] == producto['Producto'] ){
                                descuento_esperado =
                                    (1-(
                                        (1-descuento_esperado*0.01) *
                                        (1-descuento['DescuentoProducto']*0.01)
                                    ))*100;
                                break;
                            }
                        }

                        for (let i = 0; i < respuesta_json['descuentos'].length; i++) {
                            let descuento = respuesta_json['descuentos'][i];
                            if( descuento['Fabricante'] == producto['Fabricante'] ){
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

                        //precio = precio * ( 1 + producto['Iva'] * 0.01 );
                        precio = precio * ( 1 + 16 * 0.01 );

                        /* CALCULO IVA */

                        refaccion_html.querySelector('.precio').innerHTML = precio.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                        refaccion_html.querySelector('.total').innerHTML = (parseFloat(refaccion_html.querySelector('.precio').innerHTML) * producto['cantidad']).toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];

                        document.querySelector('#contenedor_refacciones').appendChild(refaccion_html);

                        refaccion_html.classList.remove('d-none');
                        


                        let datos_codigo_refaccion = new FormData();
                        datos_codigo_refaccion.append('codigo', producto['Codigo']);

                        fetch('modelo/codigo_refaccion', {
                            method: 'POST',
                            body: datos_codigo_refaccion
                        })
                        .then((respuesta) => {
                            return respuesta.json();
                        })
                        .catch(error => {
                            alert('Error al agregar la refaccion: ' + error);
                        })
                        .then(refacciones => {

                            refaccion_html.querySelector('img').onload = () => {
                                refaccion_html.querySelector('.spinner_refaccion').remove();
                                refaccion_html.querySelector('img').classList.remove('d-none');
                            };

                            let refaccion = null;
                            refacciones.forEach( (posible_refaccion) =>{
                                if(posible_refaccion['clave']){
                                    refaccion = posible_refaccion;
                                }
                            } );

                            if( refaccion ){

                                refaccion_html.querySelector('img').onerror = () => {
                                    refaccion_html.querySelector('img').onerror = () => {
                                        refaccion_html.querySelector('img').src = 'img/logo.png';
                                    };
                                    refaccion_html.querySelector('img').src = refaccion['imagen'];
                                };

                                try {
                                    refaccion_html.querySelector('img').src = 'https://marver.mx/img/fabricantes/' + refaccion['fabricante'] + '/' + refaccion['clave'].replace('/', '-').replace('\\', '-');
                                } catch (exc) {
                                    refaccion_html.querySelector('img').src = refaccion['imagen'];
                                }                             

                            }else{
                                refaccion_html.querySelector('img').src = 'img/logo.png';
                            }

                        });  



                    } );

                    actualizar_totales();

                    document.getElementById('spinner').classList.remove('show');
                });
        }

        function finalizar_pedido(){

            // Verificamos si el navegador soporta la Geolocation API
            if (navigator.geolocation) {
                // Solicitar la ubicación del usuario
                navigator.geolocation.getCurrentPosition(
                    // Éxito: se obtuvo la ubicación
                    function (position) {
                        document.querySelectorAll('.cantidad').forEach( (cantidad) => {
                            cantidad.classList.remove('is-invalid');
                        } );

                        let datos = new FormData();
                        datos.append('tipo_de_compra', document.querySelector('#tipo_de_compra').value);
                        datos.append('forma_de_pago', document.querySelector('#forma_de_pago').value);
                        datos.append('tipo_de_comprobante', document.querySelector('#tipo_de_comprobante').value);
                        datos.append('MEntrega', document.querySelector('#MEntrega').value);
                        datos.append('observaciones', document.querySelector('#observaciones').value);
                        datos.append('lat_nav', position.coords.latitude);
                        datos.append('lon_nav', position.coords.longitude);
                        datos.append('precision', position.coords.accuracy);

                        fetch('modelo/finalizar_pedido', {
                            method: 'POST',
                            body: datos
                        })
                        .then((respuesta) => {
                            return respuesta.json();
                        })
                        .catch(error => {
                            console.error('Error al finalizar el pedido: ', error);
                        })
                        .then(respuesta_json => {
                            switch (respuesta_json['codigo']){
                                case 0:
                                    document.location.href = 'https://www.marverrefacciones.mx/pedido?folio=' + respuesta_json['folio'];
                                    break;
                                case 1:
                                    respuesta_json['productos_insuficientes'].forEach( (producto_insuficiente) => {
                                        let cantidad = document.querySelector('#cantidad-' + producto_insuficiente['producto']);
                                        cantidad.classList.add('is-invalid');
                                        cantidad.parentNode.querySelector('div').innerText = producto_insuficiente['existencias'] + ' Existencias';
                                        } );
                                    window.scrollTo({top: 0, left: 0, behavior: 'smooth'});
                                    break;
                                case 2:
                                    let el_modal = document.querySelector('.modal');
                                    el_modal.querySelector('.modal-body').innerHTML = 
                                        'Su limite de credito se ha exedido<br>'+
                                        'favor de llamar a credito y cobranza.';
                                    let miModal = new bootstrap.Modal(el_modal, {
                                        keyboard: false
                                    });
                                    miModal.show();
                                    break;
                                case 3:
                                    let el_modal_2 = document.querySelector('.modal');
                                    el_modal_2.querySelector('.modal-body').innerHTML = 
                                        'Tiene pagos pendientes vencidos<br>'+
                                        'favor de llamar a credito y cobranza.';
                                    let miModal_2 = new bootstrap.Modal(el_modal_2, {
                                        keyboard: false
                                    });
                                    miModal_2.show();
                                    break;
                                default:
                                    let el_modal_3 = document.querySelector('.modal');
                                    el_modal_3.querySelector('.modal-body').innerHTML = 'Agregue refacciones al carrito.';
                                    let miModal_3 = new bootstrap.Modal(el_modal_3, {
                                        keyboard: false
                                    });
                                    miModal_3.show();
                                    break;
                            }
                        });

                    },
                    // Error: manejo de errores
                    function (error) {
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                let el_modal_denegado = document.querySelector('.modal');
                                el_modal_denegado.querySelector('.modal-body').innerHTML = 'Conceda los permisos de ubicació para relizar el pedido.';
                                let miModal_denegado = new bootstrap.Modal(el_modal_denegado, {
                                    keyboard: false
                                });
                                miModal_denegado.show();
                                break;
                            case error.POSITION_UNAVAILABLE:
                                let el_modal_nodis = document.querySelector('.modal');
                                el_modal_nodis.querySelector('.modal-body').innerHTML = 'La información de la posición no está disponible.';
                                let miModal_nodis = new bootstrap.Modal(el_modal_nodis, {
                                    keyboard: false
                                });
                                miModal_nodis.show();
                                break;
                            case error.TIMEOUT:
                                let el_modal_caduca = document.querySelector('.modal');
                                el_modal_caduca.querySelector('.modal-body').innerHTML = 'La solicitud para obtener la ubicación ha caducado.';
                                let miModal_caduca = new bootstrap.Modal(el_modal_caduca, {
                                    keyboard: false
                                });
                                miModal_caduca.show();
                                break;
                            case error.UNKNOWN_ERROR:
                                let el_modal_error = document.querySelector('.modal');
                                el_modal_error.querySelector('.modal-body').innerHTML = 'Ocurrió un error desconocido.';
                                let miModal_error = new bootstrap.Modal(el_modal_error, {
                                    keyboard: false
                                });
                                miModal_error.show();
                                break;
                        }
                    },
                    // Opciones para obtener una posición más precisa
                    {
                        enableHighAccuracy: true, // Prioriza la precisión sobre la velocidad o el consumo de energía
                        timeout: 10000,           // Espera hasta 10 segundos para obtener la posición
                        maximumAge: 0             // No usa posiciones en caché, siempre solicita una nueva
                    }
                );
            } else {
                let el_modal = document.querySelector('.modal');
                el_modal.querySelector('.modal-body').innerHTML = 'Geolocalización no es soportada por este navegador.';
                let miModal = new bootstrap.Modal(el_modal, {
                    keyboard: false
                });
                miModal.show();
            }
        }

        consultar_carrito();
    </script>

<script>(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo", v: "weekly" });</script>

<script>

let marcador = null;
let imagen = document.createElement('img');
let mapa;
imagen.src = 'https://www.marverrefacciones.mx/android/marcador_cliente.png';

let Marcadores;
let ServicioLugares;

function actualizar_posicion(){

    let datos = new FormData();
    datos.append('latitud', marcador.position.lat);
    datos.append('longitud', marcador.position.lng);

    fetch('modelo/actualizar_posicion', {
        method: 'POST',
        body: datos
    })
    .then((respuesta) => {
        return respuesta.json();
    })
    .catch(error => {
        console.error('Error al finalizar el actualizar posicion: ', error);
    })
    .then(respuesta_json => {
        /*anima cuando a un div se le cambia la alturalet alerta = document.querySelector('.alert').cloneNode(true);
                    alerta.querySelector('div').innerText = 'Posicion actualizada correctamente';
                    alerta.classList.add('d-flex');
                    alerta.classList.remove('d-none');
                    document.querySelector('body').appendChild(alerta);
                    setTimeout(() => { new bootstrap.Alert(alerta).close(); }, 2000);*/
        alerta = document.querySelector('.alert');
        alerta.style.border = "1px";
        alerta.style.padding = "20px";
        alerta.style.height = "60px";

        setTimeout(() => {
            document.querySelector('.alert').innerText = "Dirección actualizada.";
        }, 250);

        setTimeout(() => {
            alerta.style.border = "0px";
            alerta.style.padding = "0px";
            alerta.style.height = "0px";

            setTimeout(() => {
                document.querySelector('.alert').innerText = "";
            }, 250);
        }, 4000);

        console.log(respuesta_json);
    });
}

</script>

<script type="module">

    async function initMap() {
        const { Map, InfoWindow } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        Marcadores = AdvancedMarkerElement;
        const { PlacesService } = await google.maps.importLibrary("places");
        ServicioLugares = PlacesService;

        mapa = new Map(document.getElementById("mapa"), {
            center: { lat: 25.7887317, lng: -108.994305 },
            zoom: 12,
            mapId: '7845e7dffe8cea37',
            mapTypeId: google.maps.MapTypeId.HYBRID
        });

        mapa.addListener("click", (e) => {
            if(marcador == null){
                    marcador = new AdvancedMarkerElement({
                    content: imagen,
                    map: mapa,
                    position: e.latLng
                });
            }else{
                marcador.position = e.latLng;
            }
            actualizar_posicion();
        });

        fetch('modelo/obtener_posicion_pedido', {
            method: 'GET'
        })
        .then((respuesta) => {
            return respuesta.json();
        })
        .catch(error => {
            console.error('Error al pedir la posicion de pedido: ', error);
        })
        .then(respuesta_json => {
            if(respuesta_json["latitud"] != "no" && respuesta_json["longitud"] != "no"){
                marcador = new Marcadores({
                        content: imagen,
                        map: mapa,
                        position: { lat: respuesta_json["latitud"], lng: respuesta_json["longitud"] }
                    });
                mapa.setCenter({ lat: respuesta_json["latitud"], lng: respuesta_json["longitud"] });
                mapa.setZoom(18);
            }
        });

    }

    initMap();
</script>

<script>

    function buscar_direccion() {

        let consulta = {
            query: document.getElementById("direccion").value,
            fields: ['name', 'geometry'],
        };

        let service = new ServicioLugares(mapa);
        
        service.findPlaceFromQuery(consulta, function(results, status) {
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                if(results.length > 0){
                    if(marcador == null){
                        marcador = new Marcadores({
                        content: imagen,
                        map: mapa,
                        position: results[0].geometry.location
                    });
                    }else{
                        marcador.position = results[0].geometry.location;
                    }
                    mapa.setCenter(results[0].geometry.location);
                    mapa.setZoom(18);
                    actualizar_posicion();
                }
            }
        });
    }

    window.addEventListener("load", ()=>{

    document.getElementById("direccion").addEventListener("keypress", function(event) {
        if (event.key == "Enter") {
            buscar_direccion();
        }
        });

    });

</script>

<!-- <script src="https://www.paypal.com/sdk/js?&client-id=AWirsRs7Nml-lTS--1gL0ZNDvrBNB9pjHEuLHjlCM-h2DVMFB4LcNum5QdTkKMjAjb4UbV8YNzVK3Svo&currency=MXN"></script>
<script>
  function initPayPalButton() {
    paypal.Buttons({
      style: {
        shape: 'rect',
        color: 'gold',
        layout: 'vertical',
        label: 'paypal',

      },

      createOrder: function (data, actions) {
        console.log(purchase_unit);
        return actions.order.create({
          "purchase_units": [purchase_unit]
        });
      },

      onApprove: function (data, actions) {
        return actions.order.capture().then(function (orderData) {

          // Full available details
          console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

          // Show a success message within this page, e.g.
          const element = document.getElementById('paypal-button-container');
          element.innerHTML = '';
          element.innerHTML = '<h3>¡Pago realizado, Muchas Gracias!</h3>';

          // Or go to another URL:  actions.redirect('thank_you.html');

        });
      },

      onError: function (err) {
        console.log(err);
      }
    }).render('#paypal-button-container');
  }
  initPayPalButton();
</script> -->

</body>

</html>