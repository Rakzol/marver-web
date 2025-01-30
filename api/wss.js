const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');
const mssql = require('mssql');
const jwt = require('jsonwebtoken');

const secretKey = 'Q4V5U6b4l68v31W92N46o49K9P6w6HJ4';

// Configuración de la base de datos SQL Server
const sqlConfig = {
  user: 'MARITE',     // Reemplaza con tu usuario de SQL Server
  password: '2505M$RITE', // Reemplaza con tu contraseña
  server: '127.0.0.1',       // Reemplaza con tu servidor SQL
  database: 'Mochis', // Reemplaza con el nombre de tu base de datos
  pool: {
    max: 200,          // Conexiones máximas simultáneas
    min: 50,           // Conexiones mínimas siempre activas
    idleTimeoutMillis: 30000, // Tiempo máximo de inactividad
    acquireTimeoutMillis: 60000 // Tiempo de espera para obtener conexión
  },
  options: {
    encrypt: false,           // Si usas cifrado
    trustServerCertificate: true // Si necesitas confiar en el certificado del servidor
  }
};

const sslOptions = {
  cert: fs.readFileSync('crt.crt'),
  key: fs.readFileSync('key.pem')
};

const server = https.createServer(sslOptions, async (req, res) => {
  // Servir archivos estáticos
  const filePath = req.url === '/' ? 'index.html' : `${req.url.substring(1)}`;

  fs.readFile(filePath, (err, data) => {

    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('404 Not Found');
      return;
    }
    const ext = filePath.split('.').pop();

    // Mapear extensiones a content types
    const contentTypeMap = {
      html: 'text/html',
      css: 'text/css',
      js: 'application/javascript',
      png: 'image/png',
      jpg: 'image/jpeg',
      jpeg: 'image/jpeg',
      gif: 'image/gif',
      svg: 'image/svg+xml',
      ico: 'image/x-icon',
      json: 'application/json'
    };

    const contentType = contentTypeMap[ext] || 'application/octet-stream'; // Por defecto a binario genérico

    res.writeHead(200, { 'Content-Type': contentType });
    res.end(data);
  });
});

const wss = new WebSocket.WebSocketServer({ server });

let pool;

// Store all connected clients in an array
let clients = [];

// Conexión a la base de datos
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
async function fetchDataAndSend() {
  try {
    if (!pool) {
      pool = await mssql.connect(sqlConfig);
      console.log('Base de datos abierta');
    }
    // Conectar a la base de datos

    const result = await pool.query(`
      SELECT PedidosNotificacion.Folio, PedidosNotificacion.Status, 'pedido' AS Tipo, PedidosNotificacion.AlSurtiendo, PedidosNotificacion.ALSurtir, PedidosNotificacion.Vendedor, Vendedores.Nombre AS NombreVendedor, PedidosNotificacion.Cliente, Clientes.Razon_Social AS NombreCliente
      FROM PedidosNotificacion
      LEFT JOIN Vendedores ON Vendedores.Clave = PedidosNotificacion.Vendedor
      LEFT JOIN Clientes ON Clientes.Clave = PedidosNotificacion.Cliente
      ORDER BY PedidosNotificacion.Folio`);

    if (result.recordset.length > 0) {
      // Enviar los registros a todos los clientes conectados
      clients.forEach(client => {
        try {
          if (client.ws.readyState === WebSocket.OPEN) {
            jwt.verify(client.jwt, secretKey);
            client.ws.send(JSON.stringify({ "ruta": "pedidos", "pedidos": result.recordset }));
          }
        } catch (err) {
          console.error(err);
        }
      });

      // Eliminar los datos de la tabla después de enviarlos
      await pool.query('DELETE PedidosNotificacion'); // Asegúrate de eliminar solo los registros enviados
    }
  } catch (error) {
    console.error('Erro con la base de datos: ', error);
  } finally {
    setTimeout(fetchDataAndSend, 500);
    /*if (pool) {
      await pool.close();
    }*/
  }
}

// Ejecutar la consulta cada segundo

/*
CAMBIARLO POR setTimeout en el finally
*/

setTimeout(fetchDataAndSend, 500); // 1000 ms = 1 segundo

function r(n) {
  return Math.round(n * 100) / 100;
}

// When a client connects to the WebSocket server
wss.on('connection', async (ws) => {
  console.log('Cliente conectado');

  // Add the new WebSocket connection to the clients array
  clients.push({ "ws": ws });

  // When the WebSocket receives a message
  ws.on('message', async (message) => {
    try {

      const datos = JSON.parse(message.toString());

      /*
      * CAMBIAR LOS POOL POR UN UNICO POOL
      * ESTAR SEGURO DE CUANDO SI Y CUANO NO USAR async/await
      */

      switch (datos.ruta) {
        case 'login': {

          const request = pool.request();
          request.input('clave', mssql.Int, datos.clave);
          request.input('contraseña', mssql.VarChar, datos.contraseña);

          const result = await request.query('SELECT Clave, Nombre, Extra1 FROM Vendedores WHERE Clave = @clave AND Contraseña = @contraseña');

          if (!result.recordset[0]) {
            ws.send(JSON.stringify({ "error": "Credenciales invalidas" }));
            return;
          }

          const payload = {
            clave: result.recordset[0].Clave,
            nombre: result.recordset[0].Nombre,
            perfil: result.recordset[0].Extra1
          };

          // Opciones del jwt (opcional)
          const options = {
            expiresIn: '30d' // El jwt expirará en 1 hora
          };

          ws.send(JSON.stringify({ "jwt": jwt.sign(payload, secretKey, options) }));

          break;
        }
        case 'jwt': {
          jwt.verify(datos.jwt, secretKey);
          const clienteEncontrado = clients.find(cliente => cliente.ws == ws);
          clienteEncontrado.jwt = datos.jwt;
          break;
        }
        case 'validar': {
          ws.send(JSON.stringify({ "validacion": jwt.verify(datos.jwt, secretKey) }));
          break;
        }
        case 'pedidos': {
          const credenciales = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('clave', mssql.VarChar, credenciales.clave.toString());

          const resultado = await solicitud.query(`
            SELECT PedidosCliente.Folio, PedidosCliente.Status, 'pedido' AS Tipo, PedidosCliente.AlSurtiendo, PedidosCliente.ALSurtir, PedidosCliente.Vendedor, Vendedores.Nombre AS NombreVendedor, PedidosCliente.Cliente, Clientes.Razon_Social AS NombreCliente
            FROM PedidosCliente
            LEFT JOIN Vendedores ON Vendedores.Clave = PedidosCliente.Vendedor
            LEFT JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            WHERE
            ( PedidosCliente.Status = 'C' ) OR
            ( PedidosCliente.Status = 'Z' AND PedidosCliente.AlSurtiendo = @clave ) OR
            ( PedidosCliente.Status = 'S' AND PedidosCliente.ALSurtir = @clave )
            UNION ALL
            SELECT PedidosMostrador.Folio, PedidosMostrador.Status, 'mostrador' AS Tipo, PedidosMostrador.AlSurtiendo, PedidosMostrador.ALSurtir, PedidosMostrador.Vendedor, Vendedores.Nombre AS NombreVendedor, PedidosMostrador.Cliente, Clientes.Razon_Social AS NombreCliente
            FROM PedidosMostrador
            LEFT JOIN Vendedores ON Vendedores.Clave = PedidosMostrador.Vendedor
            LEFT JOIN Clientes ON Clientes.Clave = PedidosMostrador.Cliente
            WHERE
            ( PedidosMostrador.Status = 'C' ) OR
            ( PedidosMostrador.Status = 'Z' AND PedidosMostrador.AlSurtiendo = @clave ) OR
            ( PedidosMostrador.Status = 'S' AND PedidosMostrador.ALSurtir = @clave )
            ORDER BY Folio`);

          if (resultado.recordset.length > 0) {
            ws.send(JSON.stringify({ "ruta": "pedidos", "pedidos": resultado.recordset }));
          }
          break;
        }
        case 'pedidoDetalle': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicit = pool.request();

          solicit.input('folio', mssql.Int, datos.folio);

          const res = await solicit.query(datos.tipo == "pedido" ?
            `SELECT pcd.CodigoArticulo, pro.Localizacion, pro.Descripcion, pcd.CantidadPedida, pcd.CantidadSurtida
              FROM PedidoClientesDetalle pcd LEFT JOIN Producto pro ON pro.Codigo = pcd.CodigoArticulo
              WHERE pcd.Folio = @folio ORDER BY pro.Localizacion`
            :
            `SELECT pmd.CodigoArticulo, pro.Localizacion, pro.Descripcion, pmd.CantidadPedida, pmd.CantidadSurtida
            FROM PedidoMostradorDetalle pmd LEFT JOIN Producto pro ON pro.Codigo = pmd.CodigoArticulo
            WHERE pmd.Folio = @folio ORDER BY pro.Localizacion`);

          if (res.recordset.length > 0) {
            ws.send(JSON.stringify({ "ruta": "pedidoDetalle", "pedido": res.recordset, "tipo": datos.tipo, "folio": datos.folio, "status": datos.status }));
          }
          break;
        }
        case 'restarSurtidoCodigo': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);

          let res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status
            FROM PedidosCliente
            WHERE Folio = @folio`
            :
            `SELECT Status
          FROM PedidosMostrador
          WHERE Folio = @folio`);

          if (res.recordset[0].Status != 'Z') {
            return;
          }

          solicitud.input('codigo', mssql.VarChar, datos.codigo);

          res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT CantidadPedida, CantidadSurtida
              FROM PedidoClientesDetalle
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `SELECT CantidadPedida, CantidadSurtida
            FROM PedidoMostradorDetalle
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          if (!res.recordset[0]) {
            return;
          }

          if (res.recordset[0].CantidadSurtida - 1 < 0) {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidoClientesDetalle SET CantidadSurtida = CantidadSurtida - 1
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `UPDATE PedidoMostradorDetalle SET CantidadSurtida = CantidadSurtida - 1
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          ws.send(JSON.stringify({ "ruta": "actualizarSurtido", "tipo": datos.tipo, "folio": datos.folio, "codigo": datos.codigo, "cantidadSurtida": res.recordset[0].CantidadSurtida - 1, "cantidadPedida": res.recordset[0].CantidadPedida }));
          break;
        }
        case 'sumarSurtidoCodigo': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);

          let res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status
            FROM PedidosCliente
            WHERE Folio = @folio`
            :
            `SELECT Status
          FROM PedidosMostrador
          WHERE Folio = @folio`);

          if (res.recordset[0].Status != 'Z') {
            return;
          }

          solicitud.input('codigo', mssql.VarChar, datos.codigo);

          res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT CantidadPedida, CantidadSurtida
              FROM PedidoClientesDetalle
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `SELECT CantidadPedida, CantidadSurtida
            FROM PedidoMostradorDetalle
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          if (!res.recordset[0]) {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidoClientesDetalle SET CantidadSurtida = CantidadSurtida + 1
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `UPDATE PedidoMostradorDetalle SET CantidadSurtida = CantidadSurtida + 1
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          ws.send(JSON.stringify({ "ruta": "actualizarSurtido", "tipo": datos.tipo, "folio": datos.folio, "codigo": datos.codigo, "cantidadSurtida": res.recordset[0].CantidadSurtida + 1, "cantidadPedida": res.recordset[0].CantidadPedida }));
          break;
        }
        case 'sumarSurtidoBarra': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);

          let res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status
            FROM PedidosCliente
            WHERE Folio = @folio`
            :
            `SELECT Status
          FROM PedidosMostrador
          WHERE Folio = @folio`);

          if (res.recordset[0].Status != 'Z') {
            return;
          }

          solicitud.input('barra', mssql.VarChar, datos.barra);

          res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT pcd.CodigoArticulo
              FROM PedidoClientesDetalle pcd INNER JOIN Producto pro ON pro.Codigo = pcd.CodigoArticulo
              WHERE pcd.Folio = @folio AND pro.Alterno2 = @barra`
            :
            `SELECT pmd.CodigoArticulo
            FROM PedidoMostradorDetalle pmd INNER JOIN Producto pro ON pro.Codigo = pmd.CodigoArticulo
            WHERE pmd.Folio = @folio AND pro.Alterno2 = @barra`);

          if (!res.recordset[0]) {
            console.log("Codigo de barras incorrecto");
            return;
          }

          const codigo = res.recordset[0].CodigoArticulo;

          solicitud.input('codigo', mssql.VarChar, codigo);

          res = await solicitud.query(datos.tipo == "pedido" ?
            `SELECT CantidadPedida, CantidadSurtida
              FROM PedidoClientesDetalle
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `SELECT CantidadPedida, CantidadSurtida
            FROM PedidoMostradorDetalle
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          if (!res.recordset[0]) {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidoClientesDetalle SET CantidadSurtida = CantidadSurtida + 1
              WHERE Folio = @folio AND CodigoArticulo = @codigo`
            :
            `UPDATE PedidoMostradorDetalle SET CantidadSurtida = CantidadSurtida + 1
            WHERE Folio = @folio AND CodigoArticulo = @codigo`);

          ws.send(JSON.stringify({ "ruta": "actualizarSurtido", "tipo": datos.tipo, "folio": datos.folio, "codigo": codigo, "cantidadSurtida": res.recordset[0].CantidadSurtida + 1, "cantidadPedida": res.recordset[0].CantidadPedida }));
          break;
        }
        case 'statusSurtiendo': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          let solicitud = pool.request();
          solicitud.input('folio', mssql.Int, datos.folio);
          solicitud.input('clave', mssql.VarChar, credencial.clave.toString());
          solicitud.input('hora', mssql.VarChar, horaActual());

          const pedido = (await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status FROM PedidosCliente WHERE Folio = @folio`
            :
            `SELECT Status FROM PedidosMostrador WHERE Folio = @folio`)).recordset[0];

          //por si dos le dan al mismo tiempo
          if (pedido.Status == 'Z') {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidosCliente SET Status = 'Z', HoraSurtiendo = @hora, AlSurtiendo = @clave WHERE Folio = @folio`
            :
            `UPDATE PedidosMostrador SET Status = 'Z', HoraSurtiendo = @hora, AlSurtiendo = @clave WHERE Folio = @folio`);

          break;
        }
        case 'statusSurtido': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          let solicitud = pool.request();
          solicitud.input('folio', mssql.Int, datos.folio);

          const pedido = (await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status FROM PedidosCliente WHERE Folio = @folio`
            :
            `SELECT Status FROM PedidosMostrador WHERE Folio = @folio`)).recordset[0];

          //por si dos le dan al mismo tiempo
          if (pedido.Status == 'S') {
            return;
          }

          const pedidoDetalle = (await solicitud.query(datos.tipo == "pedido" ?
            `SELECT * FROM PedidoClientesDetalle WHERE Folio = @folio`
            :
            `SELECT * FROM PedidoMostradorDetalle WHERE Folio = @folio`)).recordset;

          let CodigosSurtido = 0;
          let UnidadesSurtido = 0;
          let DescuentosSurtido = 0;
          let SubtotalSurtido = 0;
          let IvaSurtido = 0;
          let TotalSurtido = 0;
          let Costototal = 0;

          let importeSurtido = 0;
          for (const producto of pedidoDetalle) {
            if (producto.CantidadSurtida > 0) {
              CodigosSurtido++;
              UnidadesSurtido += producto.CantidadSurtida;
            }

            const ImporteSurtida = r(producto.PrecioPedido * producto.CantidadSurtida);
            if (producto.DescuentoPedida > 0) {
              DescuentosSurtido += r(r(producto.DescuentoPedida * 0.01) * ImporteSurtida);
            }
            importeSurtido += ImporteSurtida;

            Costototal += r(producto.CostoPedida * producto.CantidadSurtida);

            solicitud = pool.request();
            solicitud.input('folio', mssql.Int, datos.folio);
            solicitud.input('CodigoArticulo', mssql.VarChar, producto.CodigoArticulo);
            solicitud.input('ImporteSurtida', mssql.Float, ImporteSurtida);
            await solicitud.query(datos.tipo == "pedido" ?
              `UPDATE PedidoClientesDetalle SET
            PrecioSurtida = PrecioPedido,
            ImporteSurtida = @ImporteSurtida,
            DescuentoSurtida = DescuentoPedida,
            CostoSurtida = CostoPedida
            WHERE Folio = @folio AND CodigoArticulo = @CodigoArticulo`
              :
              `UPDATE PedidoMostradorDetalle SET
            PrecioSurtida = PrecioPedido,
            ImporteSurtida = @ImporteSurtida,
            DescuentoSurtida = DescuentoPedida,
            CostoSurtida = CostoPedida
            WHERE Folio = @folio AND CodigoArticulo = @CodigoArticulo`);

          };

          SubtotalSurtido = importeSurtido - DescuentosSurtido;
          IvaSurtido = r(SubtotalSurtido * 0.16);
          TotalSurtido = SubtotalSurtido + IvaSurtido;

          solicitud = pool.request();
          solicitud.input('folio', mssql.Int, datos.folio);
          solicitud.input('HoraSurtido', mssql.VarChar, horaActual());
          solicitud.input('CodigosSurtido', mssql.Int, CodigosSurtido);
          solicitud.input('UnidadesSurtido', mssql.Float, UnidadesSurtido);
          solicitud.input('DescuentosSurtido', mssql.Float, DescuentosSurtido);
          solicitud.input('SubtotalSurtido', mssql.Float, SubtotalSurtido);
          solicitud.input('IvaSurtido', mssql.Float, IvaSurtido);
          solicitud.input('TotalSurtido', mssql.Float, TotalSurtido);
          solicitud.input('Costototal', mssql.Float, Costototal);
          solicitud.input('ALSurtir', mssql.VarChar, credencial.clave.toString());
          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidosCliente SET
            Status = 'S',
            FechaSurtido = GETDATE(),
            HoraSurtido = @HoraSurtido,
            CodigosSurtido = @CodigosSurtido,
            UnidadesSurtido = @UnidadesSurtido,
            DescuentosSurtido = @DescuentosSurtido,
            SubtotalSurtido = @SubtotalSurtido,
            IvaSurtido = @IvaSurtido,
            TotalSurtido = @TotalSurtido,
            Costototal = @Costototal,
            ALSurtir = @ALSurtir
            WHERE Folio = @folio`
            :
            `UPDATE PedidosMostrador SET
            Status = 'S',
            FechaSurtido = GETDATE(),
            HoraSurtido = @HoraSurtido,
            CodigosSurtido = @CodigosSurtido,
            UnidadesSurtido = @UnidadesSurtido,
            DescuentosSurtido = @DescuentosSurtido,
            SubtotalSurtido = @SubtotalSurtido,
            IvaSurtido = @IvaSurtido,
            TotalSurtido = @TotalSurtido,
            Costototal = @Costototal,
            ALSurtir = @ALSurtir
            WHERE Folio = @folio`);

          break;
        }
        case 'statusCapturado': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          let solicitud = pool.request();
          solicitud.input('folio', mssql.Int, datos.folio);

          const pedido = (await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status FROM PedidosCliente WHERE Folio = @folio`
            :
            `SELECT Status FROM PedidosMostrador WHERE Folio = @folio`)).recordset[0];

          //por si dos le dan al mismo tiempo
          if (pedido.Status == 'C') {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidosCliente SET Status = 'C' WHERE Folio = @folio`
            :
            `UPDATE PedidosMostrador SET Status = 'C' WHERE Folio = @folio`);

          break;
        }
        case 'statusCancelado': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          let solicitud = pool.request();
          solicitud.input('folio', mssql.Int, datos.folio);

          const pedido = (await solicitud.query(datos.tipo == "pedido" ?
            `SELECT Status FROM PedidosCliente WHERE Folio = @folio`
            :
            `SELECT Status FROM PedidosMostrador WHERE Folio = @folio`)).recordset[0];

          //por si dos le dan al mismo tiempo
          if (pedido.Status != 'C') {
            return;
          }

          await solicitud.query(datos.tipo == "pedido" ?
            `UPDATE PedidosCliente SET Status = 'CA' WHERE Folio = @folio`
            :
            `UPDATE PedidosMostrador SET Status = 'CA' WHERE Folio = @folio`);

          break;
        }
        case '🐱': {
          ws.send(JSON.stringify({ "gatos": "🐱 🐈 😺 😸 😹 😻 😼 😽 🙀 😿 😾 🐾" }));
          break;
        }
        default: {
          ws.send(JSON.stringify({ "error": "Ruta no econtrada" }));
          break;
        }
      }

    } catch (error) {
      console.error(error);
      ws.send(JSON.stringify({ "error": error.message }));
    } finally {
      /*if (pool) {
        await pool.close();
      }*/
    }

  });

  // When the client disconnects
  ws.on('close', async () => {
    console.log('Client desconectado');

    clients = clients.filter(cliente => cliente.ws != ws);
  });

  // Send a welcome message to the new client
  //ws.send('Welcome to the chat!');
});

function horaActual() {
  const ahora = new Date();

  // Obtener componentes de la hora
  let horas = ahora.getHours();
  const minutos = ahora.getMinutes().toString().padStart(2, '0');
  const segundos = ahora.getSeconds().toString().padStart(2, '0');

  // Determinar AM/PM
  const ampm = horas >= 12 ? 'p. m.' : 'a. m.';

  // Convertir a formato de 12 horas
  horas = horas % 12;
  horas = horas || 12; // Ajustar 0 a 12

  // Formatear la cadena final
  return `${horas}:${minutos}:${segundos} ${ampm}`;
}

// Start the server on port 8888
server.listen(8888, () => {
  console.log('Servidor abierto');
});