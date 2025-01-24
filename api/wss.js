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

// Store all connected clients in an array
let clients = [];

// Conexión a la base de datos
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
async function fetchDataAndSend() {
  let pool;
  try {
    // Conectar a la base de datos
    pool = await mssql.connect(sqlConfig);

    const result = await pool.query('SELECT * FROM PedidosNotificacion ORDER BY Folio');

    if (result.recordset.length > 0) {
      // Enviar los registros a todos los clientes conectados
      clients.forEach(client => {
        try {
          if (client.ws.readyState === WebSocket.OPEN) {
            jwt.verify(client.jwt, secretKey);
            client.ws.send(JSON.stringify(result.recordset));
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
    if (pool) {
      await pool.close();
    }
  }
}

// Ejecutar la consulta cada segundo

/*
CAMBIARLO POR setTimeout en el finally
*/

setInterval(fetchDataAndSend, 1000); // 1000 ms = 1 segundo

// When a client connects to the WebSocket server
wss.on('connection', async (ws) => {
  console.log('Cliente conectado');

  // Add the new WebSocket connection to the clients array
  clients.push({ "ws": ws });

  // When the WebSocket receives a message
  let pool;
  ws.on('message', async (message) => {
    try {

      const datos = JSON.parse(message.toString());

      /*
      * CAMBIAR LOS POOL POR UN UNICO POOL
      * ESTAR SEGURO DE CUANDO SI Y CUANO NO USAR async/await
      */

      switch (datos.ruta) {
        case 'login':

          pool = await mssql.connect(sqlConfig);
          const request = pool.request();
          request.input('clave', mssql.Int, datos.clave);
          request.input('contraseña', mssql.VarChar(50), datos.contraseña);

          const result = await request.query('SELECT Clave, Nombre, Perfil FROM Usuarios WHERE Clave = @clave AND Contraseña = @contraseña');

          if (!result.recordset[0]) {
            ws.send(JSON.stringify({ "error": "Credenciales invalidas" }));
            return;
          }

          const payload = {
            clave: result.recordset[0].Clave,
            nombre: result.recordset[0].Nombre,
            perfil: result.recordset[0].Perfil
          };

          // Opciones del jwt (opcional)
          const options = {
            expiresIn: '30d' // El jwt expirará en 1 hora
          };

          ws.send(JSON.stringify({ "jwt": jwt.sign(payload, secretKey, options) }));

          break;
        case 'jwt':
          jwt.verify(datos.jwt, secretKey);
          const clienteEncontrado = clients.find(cliente => cliente.ws == ws);
          clienteEncontrado.jwt = datos.jwt;
          break;
        case 'validar':
          ws.send(JSON.stringify({ "validacion": jwt.verify(datos.jwt, secretKey) }));
          break;
        case 'pedidos':
          const credenciales = jwt.verify(datos.jwt, secretKey);

          pool = await mssql.connect(sqlConfig);
          const solicitud = pool.request();

          solicitud.input('clave', mssql.VarChar(20), credenciales.clave.toString());

          const resultado = await solicitud.query(`
            SELECT Folio, Status, 'pedido' AS Tipo, AlSurtiendo, ALSurtir, Alfacturar FROM PedidosCliente WHERE
            ( Status = 'C' ) OR
            ( Status = 'Z' AND AlSurtiendo = @clave ) OR
            ( Status = 'S' AND ALSurtir = @clave ) OR
            ( Status = 'F' AND Alfacturar = @clave )
            UNION ALL
            SELECT Folio, Status, 'mostrador' AS Tipo, AlSurtiendo, ALSurtir, Alfacturar FROM PedidosMostrador WHERE
            ( Status = 'C' ) OR
            ( Status = 'Z' AND AlSurtiendo = @clave ) OR
            ( Status = 'S' AND ALSurtir = @clave ) OR
            ( Status = 'F' AND Alfacturar = @clave )
            ORDER BY Folio`);

          if (resultado.recordset.length > 0) {
            ws.send(JSON.stringify(resultado.recordset))
          }
          break;
        case '🐱':
          ws.send(JSON.stringify({ "gatos": "🐱 🐈 😺 😸 😹 😻 😼 😽 🙀 😿 😾 🐾" }));
          break;
        default:
          ws.send(JSON.stringify({ "error": "Ruta no econtrada" }));
          break;
      }

    } catch (error) {
      console.error(error);
      ws.send(JSON.stringify({ "error": error.message }));
    } finally {
      if (pool) {
        await pool.close();
      }
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

// Start the server on port 8888
server.listen(8888);