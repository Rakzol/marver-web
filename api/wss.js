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
  cert: fs.readFileSync('api/crt.crt'),
  key: fs.readFileSync('api/key.pem')
};

const server = https.createServer(sslOptions, async (req, res) => {
  // Servir archivos estáticos
  const filePath = req.url === '/' ? 'api/index.html' : `api${req.url}`;

  fs.readFile(filePath, (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('404 Not Found');
      return;
    }
    const ext = filePath.split('.').pop();
    const contentType = {
      html: 'text/html',
      css: 'text/css',
      js: 'application/javascript'
    };

    res.writeHead(200, { 'Content-Type': contentType });
    res.end(data);
  });
});

const wss = new WebSocket.WebSocketServer({ server });

// Store all connected clients in an array
const clients = [];

// Conexión a la base de datos
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
async function fetchDataAndSend() {
  let pool;
  try {
    // Conectar a la base de datos
    pool = await mssql.connect(sqlConfig);

    const result = await pool.query('SELECT * FROM bitacoras_backup');

    if (result.recordset.length > 0) {
      // Enviar los registros a todos los clientes conectados
      const data = JSON.stringify(result.recordset);

      clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
          client.send(data);
        }
      });

      // Eliminar los datos de la tabla después de enviarlos
      await pool.query('DELETE bitacoras_backup'); // Asegúrate de eliminar solo los registros enviados
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
setInterval(fetchDataAndSend, 1000); // 1000 ms = 1 segundo

// When a client connects to the WebSocket server
wss.on('connection', async (ws) => {
  console.log('Cliente conectado');

  // Add the new WebSocket connection to the clients array
  clients.push(ws);

  // When the WebSocket receives a message
  let pool;
  ws.on('message', async (message) => {
    try {

      const datos = JSON.parse(message.toString());

      switch (datos['ruta']) {
        case 'login':

          pool = await mssql.connect(sqlConfig);
          const request = pool.request();
          request.input('clave', mssql.Int, datos['clave']);
          request.input('contraseña', mssql.VarChar(50), datos['contraseña']);

          const result = await request.query('SELECT Clave, Nombre, Perfil FROM Usuarios WHERE Clave = @clave AND Contraseña = @contraseña');

          if (!result.recordset[0]) {
            ws.send(JSON.stringify({ "error": "Credenciales invalidas" }));
            return;
          }

          const payload = {
            clave: result.recordset[0]['Clave'],
            nombre: result.recordset[0]['Nombre'],
            perfil: result.recordset[0]['Perfil']
          };

          // Opciones del token (opcional)
          const options = {
            expiresIn: '30d' // El token expirará en 1 hora
          };

          ws.send(JSON.stringify({ "token": jwt.sign(payload, secretKey, options) }));

          break;
        case 'validar':
          ws.send(JSON.stringify({ "validacion": jwt.verify(datos['token'], secretKey) }));
          break;
        case '🐱':
          ws.send(JSON.stringify({ "gatos": "🐱 🐈 😺 😸 😹 😻 😼 😽 🙀 😿 😾 🐾" }));
          break;
        default:
          ws.send(JSON.stringify({ "error": "Ruta no econtrada" }));
          break;
      }

    } catch (error) {
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

    // Remove the client from the list of connected clients
    const index = clients.indexOf(ws);
    if (index !== -1) {
      clients.splice(index, 1);
    }
  });

  // Send a welcome message to the new client
  //ws.send('Welcome to the chat!');
});

// Start the server on port 8888
server.listen(8888);