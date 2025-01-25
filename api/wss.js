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
    if(!pool){
      pool = await mssql.connect(sqlConfig);
      console.log('Base de datos abierta');
    }
    // Conectar a la base de datos

    const result = await pool.query('SELECT * FROM PedidosNotificacion ORDER BY Folio');

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
            SELECT Folio, Status, 'pedido' AS Tipo, AlSurtiendo, ALSurtir, Alfacturar, Vendedor, Nombre
            FROM PedidosCliente
            LEFT JOIN Vendedores ON Vendedores.Clave = Vendedor
            WHERE
            ( Status = 'C' ) OR
            ( Status = 'Z' AND AlSurtiendo = @clave ) OR
            ( Status = 'S' AND ALSurtir = @clave ) OR
            ( Status = 'F' AND Alfacturar = @clave )
            UNION ALL
            SELECT Folio, Status, 'mostrador' AS Tipo, AlSurtiendo, ALSurtir, Alfacturar, Vendedor, Nombre
            FROM PedidosMostrador
            LEFT JOIN Vendedores ON Vendedores.Clave = Vendedor
            WHERE
            ( Status = 'C' ) OR
            ( Status = 'Z' AND AlSurtiendo = @clave ) OR
            ( Status = 'S' AND ALSurtir = @clave ) OR
            ( Status = 'F' AND Alfacturar = @clave )
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
            `SELECT pcd.CodigoArticulo, pro.Localizacion, pcd.CantidadPedida, pcd.CantidadSurtida, pcd.CantidadFacturada
              FROM PedidoClientesDetalle pcd LEFT JOIN Producto pro ON pro.Codigo = pcd.CodigoArticulo
              WHERE pcd.Folio = @folio ORDER BY pro.Localizacion`
            :
            `SELECT pmd.CodigoArticulo, pro.Localizacion, pmd.CantidadPedida, pmd.CantidadSurtida, pmd.CantidadFacturada
            FROM PedidoMostradorDetalle pmd LEFT JOIN Producto pro ON pro.Codigo = pmd.CodigoArticulo
            WHERE pmd.Folio = @folio ORDER BY pro.Localizacion`);

          if (res.recordset.length > 0) {
            ws.send(JSON.stringify({ "ruta": "pedidoDetalle", "pedido": res.recordset, "tipo": datos.tipo, "folio": datos.folio, "status": datos.status }));
          }
          break;
        }
        case 'restarSurtido': {
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

          if (res.recordset[0].Status != 'Z' && res.recordset[0].Status != 'S') {
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

          if( res.recordset[0].CantidadSurtida - 1 < 0 ){
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
        case 'sumarSurtido': {
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

          if (res.recordset[0].Status != 'Z' && res.recordset[0].Status != 'S') {
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

          if( res.recordset[0].CantidadSurtida + 1 > res.recordset[0].CantidadPedida ){
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

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);
          solicitud.input('clave', mssql.VarChar, credencial.clave.toString());
          solicitud.input('hora', mssql.VarChar, horaActual());

          await solicitud.query(datos.tipo == "pedido" ?
          `UPDATE PedidosCliente SET Status = 'Z', AlSurtiendo = @clave, HoraSurtiendo = @hora WHERE Folio = @folio`
          :
          `UPDATE PedidosMostrador SET Status = 'Z', AlSurtiendo = @clave, HoraSurtiendo = @hora WHERE Folio = @folio`);
          
          break;
        }
        case 'statusCapturado': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);

          await solicitud.query(datos.tipo == "pedido" ?
          `UPDATE PedidosCliente SET Status = 'C' WHERE Folio = @folio`
          :
          `UPDATE PedidosMostrador SET Status = 'C' WHERE Folio = @folio`);
          
          await solicitud.query(datos.tipo == "pedido" ?
          `UPDATE PedidoClientesDetalle SET CantidadSurtida = 0 WHERE Folio = @folio`
          :
          `UPDATE PedidoMostradorDetalle SET CantidadSurtida = 0 WHERE Folio = @folio`);

          break;
        }
        case 'statusFacturado': {
          const credencial = jwt.verify(datos.jwt, secretKey);

          const solicitud = pool.request();

          solicitud.input('folio', mssql.Int, datos.folio);
          solicitud.input('clave', mssql.VarChar, credencial.clave.toString());
          solicitud.input('hora', mssql.VarChar, horaActual());

          await solicitud.query(datos.tipo == "pedido" ?
          `UPDATE PedidosCliente SET Status = 'F', ALSurtir = @clave, FechaSurtido = GETDATE(), HoraSurtido = @hora, Alfacturar = @clave, FechaFacturado = GETDATE(), HoraFacturado = @hora WHERE Folio = @folio`
          :
          `UPDATE PedidosMostrador SET Status = 'F', ALSurtir = @clave, FechaSurtido = GETDATE(), HoraSurtido = @hora, Alfacturar = @clave, FechaFacturado = GETDATE(), HoraFacturado = @hora WHERE Folio = @folio`);
          
          await solicitud.query(datos.tipo == "pedido" ?
          `UPDATE PedidoClientesDetalle SET CantidadFacturada = CantidadSurtida WHERE Folio = @folio`
          :
          `UPDATE PedidoMostradorDetalle SET CantidadFacturada = CantidadSurtida WHERE Folio = @folio`);

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