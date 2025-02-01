const { execSync } = require("child_process");

// Obtener la IP de la impresora desde Windows y limpiar el sufijo
function obtenerIPImpresora(nombreImpresora) {
    try {
        const output = execSync(`wmic printer where "Name='${nombreImpresora}'" get PortName`, { encoding: "utf-8" });
        const lines = output.split(/\r?\n/).filter(line => line.trim() && line.includes(".")); // Filtrar líneas con IP

        if (lines.length > 0) {
            let ipConSufijo = lines[0].trim();
            let match = ipConSufijo.match(/^(\d+\.\d+\.\d+\.\d+)/); // Extraer solo la IP (formato IPv4)
            return match ? match[1] : null;
        }
        return null;
    } catch (error) {
        console.error("Error obteniendo la IP de la impresora:", error);
        return null;
    }
}

const nombreImpresora = "EPSON TM-T88V Receipt5(EDWIN)";
const PRINTER_IP = obtenerIPImpresora(nombreImpresora); // Fallback a IP por defecto

console.log("IP limpia de la impresora:", PRINTER_IP);
