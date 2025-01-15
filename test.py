import qrcode
from PIL import Image

# Define el texto largo que quieres incluir en el QR
texto_largo = """data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAAXNSR0IArs4c6QAAAcJJREFUSEvFljtOxDAQhp2GvQIPIR6ChgpauAIVEnAKoIFTsM3CKQCJiitACxUNiIcQjyssjdE/a5uJY8eTxKudZmOvM5//32M7hZpQFC242nunTQ7V9CU9mF4ocY9+3m27Ua4mgwl6+P2mzmYWK0aZCYjziQcqpbTW/y53hUvB/rqSYt929Emtl4Ar62p9BiQEZxOI5peAkYcUW6t/Hy7V1Poe8YtilCJSdJ3BhqsJNLy/UL2NfZoIJoFAm08gVWxSxUnw0vax+vr84NVem1sK1lAZC0ARrzd9rn58YAAtjD8b2zuDqbBm5+YdBG2rkitla510MjnA2Ovgvt1WMS8wM6azYs4KrrWtaAnQJpMqLsFDRWb3cWobtQXTKXa+vKsOXq4c329L4E0VV8CAIvhEcoMJiqQrm6N9y4OD0Z+CN1Hs7uPVrRPHfLo9pWf/mswFprsY5zR+Q2D0WdX4WECYCyQoTqrYgUM2A/J813e3l6vc0c2VB4xMIfi4wODR6SVVXGdz1IbQAcH6NIdDqW3jmUXWI5OUx8AWaiaQDRz84KtxJwtY76z1Sozrx6Hy+/gA/B+r6CZrPDGwq+pE4YmL6w8nwtsfYMKjMAAAAABJRU5ErkJggg=="""

# Crea el objeto QR con la versión más alta (para más capacidad de almacenamiento)
qr = qrcode.QRCode(
    version=1,  # Establece la versión más alta (para máximo almacenamiento)
    error_correction=qrcode.constants.ERROR_CORRECT_L,  # Mínimo nivel de corrección de errores
    box_size=5,  # El tamaño de cada celda del QR
    border=4,  # Ancho del borde del QR
)

# Añade el texto al QR
qr.add_data(texto_largo)
qr.make(fit=True)

# Crea una imagen a partir del código QR
img = qr.make_image(fill='black', back_color='white')

# Muestra la imagen
img.show()