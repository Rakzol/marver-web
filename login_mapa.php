<?php
    session_start();

    unset($_SESSION['usuario_mapa']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet" />

    <style>
        .gradient-custom-2 {
            /* fallback for old browsers */
            background: #fccb90;

            /* Chrome 10-25, Safari 5.1-6 */
            background: -webkit-linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);

            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);
        }

        @media (min-width: 768px) {
            .gradient-form {
                height: 100vh !important;
            }
        }

        @media (min-width: 769px) {
            .gradient-custom-2 {
                border-top-right-radius: .3rem;
                border-bottom-right-radius: .3rem;
            }
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
        body {
            background-color: rgba(0, 0, 0, 0);
        }

        .barra-redes {
            background-color: #fff;
        }

        html {
            background-image: url('img/login/1.jpg');
            background-size: auto;
        }

        section {
            background-color: rgba(0, 0, 0, 0);
        }

        .alert {
            z-index: 2000;
            position: fixed !important;
            right: 1rem;
            top: 1rem;
        }

        .is-invalid, .is-valid {
            margin-bottom: 0px !important;
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

    <section class="h-100 gradient-form">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <!-- <div class="col-xl-10"> -->
                <div class="col-md-7 col-xxl-5">
                    <div class="card rounded-3 text-black" style="border-width: 0;">
                        <div class="row g-0">
                            <!-- <div class="col-lg-6"> -->
                            <div class="col-lg-12">
                                <div class="card-body p-md-5 mx-md-4">

                                    <div class="text-center">
                                        <img src="img/logo.png" style="width: 185px;" alt="logo">
                                        <h4 class="mt-1 mb-5 pb-1">La mejor calidad</h4>
                                    </div>

                                    <form>
                                        <p>Por favor inicie sesión</p>

                                        <div class="form-outline" style="margin-bottom: 2rem;">
                                            <input type="email" id="correo" class="form-control"
                                                placeholder="Nombre de usuario" spellcheck="false" />
                                            <label class="form-label" for="correo">Usuario</label>
                                            <div class="valid-feedback">
                                                Usuario registrado.
                                              </div>
                                            <div class="invalid-feedback">
                                                Usuario no registrado.
                                            </div>
                                        </div>

                                        <div class="form-outline" style="margin-bottom: 2rem;">
                                            <input type="password" id="contraseña" class="form-control" />
                                            <label class="form-label" for="contraseña">Contraseña</label>
                                            <div class="invalid-feedback">
                                                La contraseña es incorrecta.
                                            </div>
                                        </div>

                                        <div class="form-outline" style="margin-bottom: 2rem;">
                                            <select class="form-select" aria-label="Seleccionar ciudad">
                                                <option value="Mochis">Mochis</option>
                                                <option value="Guasave">Guasave</option>
                                            </select>
                                        </div>

                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button onclick="iniciar_sesion();"
                                                class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3"
                                                style="border-width: 0px;" type="button">Iniciar sesión</button>
                                            <a class="text-muted" href="#!">¿Olvidó su contraseña?</a>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-center pb-4">
                                            <p class="mb-0 me-2">¿No tiene cuenta?</p>
                                            <button type="button" class="btn btn-outline-danger">Crear cuenta</button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                            <!-- <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                    <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                      <h4 class="mb-4">#1 Soluciones Para Refacciones </h4>
                      <p class="small mb-0">Somos especialistas en refacciones y accesorios para la industria automotriz. Contamos con más de 30 años de experiencia y un extenso surtido de piezas y marcas para tu automóvil, brindando el mejor servicio y garantizando calidad y precio en nuestros productos.</p>
                    </div>
                  </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- MDB -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script>

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
        let correo = document.getElementById('correo');
        let contraseña = document.getElementById('contraseña');

        function iniciar_sesion() {
            let datos = new FormData();
            datos.append('usuario', correo.value);
            datos.append('contraseña', contraseña.value);

            fetch('android/iniciar_sesion_web', {
                method: 'POST',
                body: datos
            })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    alert('Error al iniciar sesion: ' + error);
                })
                .then(respuesta_json => {
                    correo.classList.add( respuesta_json.usuario ? 'is-valid' : 'is-invalid' );
                    correo.classList.remove( !respuesta_json.usuario ? 'is-valid' : 'is-invalid' );
                    contraseña.classList.add( respuesta_json.contraseña ? 'is-valid' : 'is-invalid' );
                    contraseña.classList.remove( !respuesta_json.contraseña ? 'is-valid' : 'is-invalid' );

                    if(respuesta_json.usuario && respuesta_json.contraseña){
                        document.location.href = 'https://www.marverrefacciones.mx/mapa.php';
                    }
                });
        }
    </script>

    <script>
        window.addEventListener('load', () => {
            let new_background_image = "url('img/login/" + (Math.round(Math.random() * 14) + 1) + ".jpg')";
            document.querySelector('html').style.backgroundImage = new_background_image;

            document.getElementById("correo").focus();

            document.getElementById("correo").addEventListener("keypress", (e)=>{
                if (event.key == "Enter") {
                    document.getElementById("contraseña").focus();
                }
            });

            document.getElementById("contraseña").addEventListener("keypress", (e)=>{
                if (event.key == "Enter") {
                    iniciar_sesion();
                }
            });

        });
    </script>

</body>

</html>