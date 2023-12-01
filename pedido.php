<?php
    require_once('modelo/guardar_estadisticas.php');
    if(!isset($_SESSION['usuario'])){
        header("Location: https://www.marverrefacciones.mx/login.php");
        exit();
    }
    echo '<script> let folio = ' . $_GET['folio'] . '; </script>';
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

        .progress{
            height: 2rem;
            font-size: 1rem;
        }
</style>

</head>

<body>
<br><br><br><br><br><br>
    <!-- Spinner Start -->
    <div id="spinner" manual='si' 
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border position-relative text-primary" style="width: 6rem; height: 6rem;" role="status">
        </div>
        <img class="text-primary position-absolute top-50 start-50 translate-middle" src="img/logo.png" style="width: 5rem;">
    </div>
    <!-- Spinner End -->

    <!-- Brand & Contact Start -->
    <div class="container-fluid py-3 px-5 wow fadeIn" data-wow-delay="0.1s" style="background: white;">
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

    <div class="container mt-3 mb-3 p-5 pt-3 pb-3 bg-white rounded">
    <div class="row">
      <div class="col">
        <h1 class="mb-3">Detalles de Pedido</h1>
        <!-- Barra de progreso -->
		<div class="progress mb-3">
			<div id="progreso_1" class="progress-bar align-items-center bg-primary" role="progressbar" style="width: 33.33%;">
				<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Surtido</span>
			</div>
			<div id="progreso_2" class="progress-bar align-items-center bg-primary" role="progressbar" style="width: 0%;">
				<span class="d-flex align-items-center">
					<i class="fas fa-truck me-2"></i>
					Enviado
				</span>
			</div>
			<div id="progreso_3" class="progress-bar align-items-center bg-primary" role="progressbar" style="width: 0%;">
				<span class="d-flex align-items-center">
					<i class="fas fa-check-circle me-2"></i>
					Entregado
				</span>
			</div>
		</div>
	</div>
        <!-- Datos de la compra -->
        <div class="row">
          <div class="col-md-6">
            <p id='folio' ></p>
            <p id='fecha' ></p>
            <p id='hora' ></p>
            <p id='entrega' ></p>
            <p id='observacion' ></p>
            <p id='total' ></p>
          </div>
        </div>
        <button id="descargar" style='width: 200px; margin-bottom: 1rem;' type="button" class="btn btn-primary me-3"><i class="fas fa-download me-2"></i>Descargar</button>
        <!-- Tabla de productos -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Imagen</th>
              <th>Código</th>
              <th class="d-none d-md-table-cell" >Cantidad</th>
              <th class="d-none d-md-table-cell" >Precio</th>
              <th class="d-none d-md-table-cell" >Importe</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>

    <script>

        window.addEventListener('load', () => {

            let datos = new FormData();
            datos.append('folio', folio);

            fetch('modelo/consultar_pedido',{
                    method: 'POST',
                    body: datos
                })
                .then((respuesta) => {
                    return respuesta.json();
                })
                .catch(error => {
                    console.error('Error al solicitar los datos: ', error);
                })
                .then(respuesta_json => {

                            // document.querySelector('#descargar').innerText = '<i class="fas fa-download me-2"></i>Descargar Factura';

                            // let datos_descarga = new FormData();
                            // datos_descarga.append('folio', folio);

                            // document.querySelector('#descargar').addEventListener('click', () => {

                            //     let nombre_xml;
                            //     let nombre_pdf;

                            //     fetch('modelo/descargar_xml',{
                            //         method: 'POST',
                            //         body: datos_descarga
                            //     })
                            //     .then(response =>{
                            //         nombre_xml = response.headers.get('Content-Disposition').split('filename=')[1];
                            //         return response.blob();
                            //     })
                            //     .then(blob => {
                            //         let url = window.URL.createObjectURL(new Blob([blob]));
                            //         let a = document.createElement('a');
                            //         a.href = url;
                            //         a.download = nombre_xml;
                            //         document.body.appendChild(a);
                            //         a.click();
                            //         a.remove();
                            //         window.URL.revokeObjectURL(url);
                            //     });

                            //     fetch('modelo/descargar_pdf',{
                            //         method: 'POST',
                            //         body: datos_descarga
                            //     })
                            //     .then(response =>{
                            //         nombre_pdf = response.headers.get('Content-Disposition').split('filename=')[1];
                            //         return response.blob();
                            //     })
                            //     .then(blob => {
                            //         let url = window.URL.createObjectURL(new Blob([blob]));
                            //         let a = document.createElement('a');
                            //         a.href = url;
                            //         a.download = nombre_pdf;
                            //         document.body.appendChild(a);
                            //         a.click();
                            //         a.remove();
                            //         window.URL.revokeObjectURL(url);
                            //     });

                            // });

                    if( respuesta_json['pedido']['Tipocomprobante'] == 1 ){
                        document.querySelector('#descargar').innerHTML = '<i class="fas fa-download me-2"></i>Descargar Factura';

                        let datos_descarga = new FormData();
                        datos_descarga.append('folio', folio);

                        document.querySelector('#descargar').addEventListener('click', () => {

                            let nombre_xml;
                            let nombre_pdf;

                            fetch('modelo/descargar_xml',{
                                method: 'POST',
                                body: datos_descarga
                            })
                            .then(response =>{
                                nombre_xml = response.headers.get('Content-Disposition').split('filename=')[1];
                                return response.blob();
                            })
                            .then(blob => {
                                let url = window.URL.createObjectURL(new Blob([blob]));
                                let a = document.createElement('a');
                                a.href = url;
                                a.download = nombre_xml;
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                                window.URL.revokeObjectURL(url);
                            });

                            // fetch('modelo/descargar_pdf',{
                            //     method: 'POST',
                            //     body: datos_descarga
                            // })
                            // .then(response =>{
                            //     nombre_pdf = response.headers.get('Content-Disposition').split('filename=')[1];
                            //     return response.blob();
                            // })
                            // .then(blob => {
                            //     let url = window.URL.createObjectURL(new Blob([blob]));
                            //     let a = document.createElement('a');
                            //     a.href = url;
                            //     a.download = nombre_pdf;
                            //     document.body.appendChild(a);
                            //     a.click();
                            //     a.remove();
                            //     window.URL.revokeObjectURL(url);
                            // });

                            let ventana = window.open('https://www.marverrefacciones.mx/factura_pdf?folio_comprobante=' + respuesta_json['pedido']['FolioComprobante'], "_blank");
                            // ventana.close();

                        });
                    }else if( respuesta_json['pedido']['Tipocomprobante'] == 2 ){
                        document.querySelector('#descargar').innerHTML = '<i class="fas fa-download me-2"></i>Descargar Recibo';

                        document.querySelector('#descargar').addEventListener('click', () => {
                            window.open('https://www.marverrefacciones.mx/recibo?folio=' + respuesta_json['pedido']['FolioComprobante']);
                        });
                    }else if( respuesta_json['pedido']['Tipocomprobante'] == 3 || respuesta_json['pedido']['Tipocomprobante'] == 5 ){
                        document.querySelector('#descargar').innerHTML = '<i class="fas fa-download me-2"></i>Descargar Preventa';

                        document.querySelector('#descargar').addEventListener('click', () => {
                            window.open('https://www.marverrefacciones.mx/preventa?folio=' + respuesta_json['pedido']['FolioComprobante']);
                        });
                    }

                    document.querySelector('#folio').innerHTML = '<strong>Folio:</strong> ' + folio;
                    document.querySelector('#fecha').innerHTML = '<strong>Fecha:</strong> ' + respuesta_json['pedido']['FechaPedido'];
                    document.querySelector('#hora').innerHTML = '<strong>Hora:</strong> ' + respuesta_json['pedido']['HoraPedido'];
                    document.querySelector('#entrega').innerHTML = '<strong>Entrega:</strong> ' + respuesta_json['pedido']['MEntrega'];
                    document.querySelector('#observacion').innerHTML = '<strong>Observacion:</strong> ' + respuesta_json['Observaciones'];
                    document.querySelector('#total').innerHTML = '<strong>Total:</strong> $' + respuesta_json['pedido']['TotalPedido'].toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                    document.querySelector('#total').style.color = 'green';

                    switch(respuesta_json['pedido']['Status']){
                        case 'C':
                            document.querySelector('#progreso_1').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Capturado</span>';
                        break;
                        case 'Z':
                            document.querySelector('#progreso_1').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Surtiendo</span>';
                        break;
                        case 'S':
                            document.querySelector('#progreso_1').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Surtido</span>';
                        break;
                        case 'F':
                            document.querySelector('#progreso_3').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i>Facturado</span>';
                        break;
                        case 'E':
                            document.querySelector('#progreso_2').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-truck me-2"></i>Enviado</span>';
                        break;
                        case 'R':
                            document.querySelector('#progreso_3').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i>Finalizado</span>';
                        break;
                        case 'CA':
                            document.querySelector('#progreso_1').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Cancelado</span>';
                        break;
                        default:
                            document.querySelector('#progreso_1').innerHTML = '<span class="d-flex align-items-center"><i class="fas fa-box-open me-2"></i>Indefinido</span>';
                        break;
                    }

                    switch(respuesta_json['pedido']['Status']){
                        case 'E':
                            document.querySelector('#progreso_2').style.width = '33.33%';
                        break;
                        case 'F':
                        case 'R':
                            document.querySelector('#progreso_2').style.width = '33.33%';
                            document.querySelector('#progreso_3').style.width = '33.33%';
                        break;
                    }

                    respuesta_json['productos'].forEach( producto =>{

                        let tr = document.createElement('tr');

                        let img = document.createElement('img');
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';

                        img.onload = () => {
                            td_img.innerHTML = '';
                            td_img.appendChild(img);
                            document.getElementById('spinner').classList.remove('show');
                        };

                        let td_img = document.createElement('td');
                        td_img.style.textAlign = 'center';
                        
                        td_img.innerHTML = '<div style="margin: 23px;" class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>';

                        tr.appendChild(td_img);

                        let td = document.createElement('td');
                        td.innerText = producto['CodigoArticulo'];
                        tr.appendChild(td);

                        td = document.createElement('td');
                        td.innerText = producto['CantidadPedida'];
                        td.classList.add('d-none');
                        td.classList.add('d-md-table-cell');
                        tr.appendChild(td);
                            
                        td = document.createElement('td');
                        td.innerText = producto['PrecioPedido'].toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                        td.classList.add('dinero');
                        td.classList.add('d-none');
                        td.classList.add('d-md-table-cell');
                        tr.appendChild(td);

                        td = document.createElement('td');
                        td.innerText = producto['ImportePedida'].toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                        td.classList.add('dinero');
                        td.classList.add('d-none');
                        td.classList.add('d-md-table-cell');
                        tr.appendChild(td);

                        td = document.createElement('td');
                        td.classList.add('dinero');
                        td.innerText = (( producto['ImportePedida'] * ( 1 - ( producto['DescuentoPedida'] * 0.01 ) ) ) * 1.16).toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                        tr.appendChild(td);

                        document.querySelector('tbody').appendChild(tr);

                        let datos_codigo_refaccion = new FormData();
                        datos_codigo_refaccion.append('codigo', producto['CodigoArticulo']);

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

                            let refaccion = null;
                            refacciones.forEach( (posible_refaccion) =>{
                                if(posible_refaccion['clave']){
                                    refaccion = posible_refaccion;
                                }
                            } );

                            if( refaccion ){

                                img.onerror = () => {
                                    img.onerror = () => {
                                        img.src = 'img/logo.png';
                                    };
                                    img.src = refaccion['imagen'];
                                };

                                try{
                                    img.src = 'https://marver.mx/img/fabricantes/' + refaccion['fabricante'] + '/' + refaccion['clave'].replace('/', '-').replace('\\', '-');
                                }catch(exc){
                                    img.src = refaccion['imagen'];
                                }
                                

                            }else{
                                img.src = 'img/logo.png';
                            }

                        });                        

                    });

                    // document.getElementById('spinner').classList.remove('show');
            });
        });

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