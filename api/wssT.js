const mssql = require('mssql');

// Configuración de la base de datos SQL Server
const sqlConfig = {
  user: 'MARITE',     // Reemplaza con tu usuario de SQL Server
  password: '2505M$RITE', // Reemplaza con tu contraseña
  server: '127.0.0.1',       // Reemplaza con tu servidor SQL
  database: 'Mochis', // Reemplaza con el nombre de tu base de datos
  pool: {
    max: 1,          // Conexiones máximas simultáneas
    min: 1,           // Conexiones mínimas siempre activas
    idleTimeoutMillis: 30000, // Tiempo máximo de inactividad
    acquireTimeoutMillis: 60000 // Tiempo de espera para obtener conexión
  },
  options: {
    encrypt: false,           // Si usas cifrado
    trustServerCertificate: true // Si necesitas confiar en el certificado del servidor
  }
};

datos = {
  folio: 10008608,
  tipo: "pedido"
};

async function correr() {
  let pool = await mssql.connect(sqlConfig);

  let solicitud = pool.request();
  solicitud.input('folio', mssql.Int, datos.folio);

  let pedido = (await solicitud.query(datos.tipo == "pedido" ?
    `SELECT * FROM PedidosCliente WHERE Folio = @folio`
    :
    `SELECT * FROM PedidosMostrador WHERE Folio = @folio`)).recordset[0];

  solicitud.input('cliente', mssql.Int, pedido.Cliente);
  console.log(pedido.Cliente);

  if (pedido.Status == 'Z') {
    console.log('Si es Z')
    return;
  }

  let descuentoUtilidad = (await solicitud.query(`SELECT DescuentoUniversal, Utilidad FROM Clientes WHERE Clave = @cliente`)).recordset[0];
  let descuentos = (await solicitud.query(`SELECT * FROM DescuentosCliente WHERE Cliente = @cliente`)).recordset;

  let pedidoDetalle = (await solicitud.query(datos.tipo == "pedido" ?
    `SELECT PedidoClientesDetalle.*, Producto.Costo, Producto.Utilidades, Producto.Producto, Producto.Sistema, Producto.Subsistema, Producto.Subsistema FROM PedidoClientesDetalle INNER JOIN Producto ON Producto.Codigo = PedidoClientesDetalle.CodigoArticulo WHERE Folio = @folio`
    :
    `SELECT * FROM PedidoMostradorDetalle WHERE Folio = @folio`)).recordset;

  pedidoDetalle.forEach(producto => {

    //La calculo pero no la voy a usar
    let descuentoInicial = descuentoUtilidad.DescuentoUniversal;

    for (const descuento of descuentos) {
      if (descuento.Sistema == producto.Sistema) {
        descuentoInicial =
          (Math.round(((1 - (
            (1 - ((Math.round((descuentoInicial * 0.01) * 100) / 100)))
            *
            (1 - ((Math.round((descuento.DescuentoSistema * 0.01) * 100) / 100)))
          )) * 100) * 100) / 100)
          ;
        break;
      }
    }

    for (const descuento of descuentos) {
      if (descuento.Subsistema == producto.Subsistema) {
        descuentoInicial =
          (Math.round(((1 - (
            (1 - ((Math.round((descuentoInicial * 0.01) * 100) / 100)))
            *
            (1 - ((Math.round((descuento.DescuentoSubsistema * 0.01) * 100) / 100)))
          )) * 100) * 100) / 100)
          ;
          break;
      }
    }

    for (const descuento of descuentos) {
      if (descuento.Producto == producto.Producto) {
        descuentoInicial =
          (Math.round(((1 - (
            (1 - ((Math.round((descuentoInicial * 0.01) * 100) / 100)))
            *
            (1 - ((Math.round((descuento.DescuentoProducto * 0.01) * 100) / 100)))
          )) * 100) * 100) / 100)
          ;
          break;
      }
    }

    for (const descuento of descuentos) {
      if (descuento.Fabricante == producto.Fabricante) {
        descuentoInicial =
          (Math.round(((1 - (
            (1 - ((Math.round((descuentoInicial * 0.01) * 100) / 100)))
            *
            (1 - ((Math.round((descuento.DescuentoFabricante * 0.01) * 100) / 100)))
          )) * 100) * 100) / 100)
          ;
          break;
      }
    }

    console.log(`Codigo: ${producto.CodigoArticulo} Cosot: ${producto.Costo} Precio: ${Math.round(((((descuentoUtilidad.Utilidad > 0 ? descuentoUtilidad.Utilidad : producto.Utilidades) + 100) / 100) * producto.Costo) * 100) / 100} Descuento: ${descuentoInicial}`);
  });
}

correr();