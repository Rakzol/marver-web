import cv2
import imageio

def convert_video_to_webp(input_video_path, output_webp_path, quality=90):
    # Abrir el video usando OpenCV
    cap = cv2.VideoCapture(input_video_path)
    
    # Obtener propiedades del video
    fps = cap.get(cv2.CAP_PROP_FPS)
    frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    
    frames = []
    
    while True:
        ret, frame = cap.read()
        if not ret:
            break
        # Convertir BGR a RGB
        frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        frames.append(frame)

    cap.release()
    
    # Guardar los frames como un archivo WebP en loop infinito
    imageio.mimwrite(output_webp_path, frames, format='WEBP', fps=fps, quality=quality, loop=0)

if __name__ == "__main__":
    input_video = "llanta480p.mp4"  # Cambia esto por tu archivo de entrada
    output_webp = "webp480p-100.webp"  # Cambia esto por tu archivo de salida
    quality = 100  # Ajusta la calidad del WebP (0-100)

    convert_video_to_webp("llanta480p.mp4", "webp480p-40.webp", 40)

    print(f"Conversión completada: {output_webp}")
