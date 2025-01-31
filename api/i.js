const net = require("net");

// Configuración de la impresora
const PRINTER_IP = "10.10.10.161";
const PRINTER_PORT = 9100; // Puerto de impresión en la TM-T88VII

// Datos de ejemplo
const productos = [
    { codigo: "4D4-V4-5S", cantidad: 152, localizacion: "01C01-02-0", descripcion: "Bombad de agua desccripcion" },
    { codigo: "D4V4-A-ASD5S", cantidad: 142, localizacion: "01PX5-07", descripcion: "descripcion de producto" },
    { codigo: "S559F-A-ASDS-S", cantidad: 2456, localizacion: "56J01-05-0", descripcion: "descripcion" }
];

// Función para generar el ticket en ESC/POS
function generarTicket(productos, folioPedido, tipoPedido, cliente, fechaPedido, pedido, fechaSurtiendo, surtiendo, fechaSurtido, surtido, fechaImpreso, impreso) {
    let ticket = "";

    // Comandos ESC/POS
    const ESC = "\x1B";
    const GS = "\x1D";
    const LF = "\x0A";

    ticket += ESC + "@"; // Inicializa la impresora
    ticket += ESC + "a" + "\x01"; // Centrar texto

    ticket += `Tipo (${cliente}): `;
    ticket += ESC + "E" + "\x01";
    ticket += tipoPedido + LF;
    ticket += ESC + "E" + "\x00";

    ticket += LF;

    ticket += ESC + "a" + "\x00"; // Alinear a la izquierda

    ticket += `Pedido (${pedido}): `;
    ticket += ESC + "E" + "\x01";
    ticket += fechaPedido + LF;
    ticket += ESC + "E" + "\x00";

    ticket += `Surtiendo (${surtiendo}): `;
    ticket += ESC + "E" + "\x01";
    ticket += fechaSurtiendo + LF;
    ticket += ESC + "E" + "\x00";

    ticket += `Surtido (${surtido}): `;
    ticket += ESC + "E" + "\x01";
    ticket += fechaSurtido + LF;
    ticket += ESC + "E" + "\x00";

    ticket += `Impreso (${impreso}): `;
    ticket += ESC + "E" + "\x01";
    ticket += fechaImpreso + LF;
    ticket += ESC + "E" + "\x00";

    ticket += LF;

    ticket += "CODIGO                 ESTANTE    CANTIDAD" + LF;
    ticket += "-".repeat(42) + LF;

    productos.forEach((producto) => {
        ticket += `${producto.codigo.padEnd(22)} ${producto.localizacion.padEnd(10)} ${producto.cantidad.toString().padStart(8)}` + LF;
        ticket += ESC + "E" + "\x01";
        ticket += `${producto.descripcion}` + LF;
        ticket += ESC + "E" + "\x00";
    });

    // Espacio antes del código de barras
    ticket += LF;

    // Código de barras en formato CODE128 con la estructura que mencionaste
    ticket += ESC + "a" + "\x01"; // Centrar
    ticket += folioPedido + LF; // Centrar
    ticket += GS + "h" + "\x7F"; // Altura del código de barras
    ticket += GS + "w" + "\xFF"; // Ancho del código de barras
    ticket += GS + "k" + String.fromCharCode(79); // Especificar tipo de código de barras (Code 128 auto)
    ticket += String.fromCharCode(folioPedido.toString().length); // Longitud del folio
    ticket += folioPedido; // Número de folio

    // Cortar papel
    ticket += LF + LF + LF + LF + LF;
    ticket += GS + "V" + "\x00";

    return ticket;
}

// Función para enviar a la impresora
function imprimir(ticket) {
    const client = new net.Socket();

    client.connect(PRINTER_PORT, PRINTER_IP, () => {
        console.log("Conectado a la impresora...");
        client.write(ticket, "binary", () => {
            console.log("Ticket enviado.");
            client.destroy(); // Cierra la conexión después de enviar
        });
    });

    client.on("error", (err) => {
        console.error("Error de conexión:", err);
    });

    client.on("close", () => {
        console.log("Conexión cerrada.");
    });
}

// Generar y enviar el ticket
const ticket = generarTicket(productos, 10009369, "Web", 3, "2025-01-25 11:45:25", 1, "2025-01-25 11:47:44", 45, "2025-01-25 11:50:13", 22, "2025-01-25 11:55:01", 12);
imprimir(ticket);
