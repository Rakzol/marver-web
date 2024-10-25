from moviepy.editor import VideoFileClip  # Asegúrate de usar esta línea para importar correctamente

def recortar_video(ruta_video, inicio_minuto, inicio_segundo, fin_minuto, fin_segundo, ruta_salida):
    # Calcula el tiempo de inicio y fin en segundos
    tiempo_inicio = inicio_minuto * 60 + inicio_segundo
    tiempo_fin = fin_minuto * 60 + fin_segundo

    # Carga el video y recorta el tiempo especificado
    video = VideoFileClip(ruta_video).subclip(tiempo_inicio, tiempo_fin)
    
    # Quita el audio para reducir el tamaño
    video = video.without_audio()

    # Exporta el video con compresión máxima
    video.write_videofile(
        ruta_salida, 
        codec='libx264', 
        preset='ultrafast', 
        ffmpeg_params=["-crf", "35"]  # Aumenta el valor de crf para una compresión máxima
    )

    # Cierra el clip
    video.close()

# Ejemplo de uso
ruta_video = 'C:/Users/rakzol/Downloads/videoplayback.mp4'  # Cambia esto por la ruta de tu video
inicio_minuto = 4   # Minuto de inicio
inicio_segundo = 21 # Segundo de inicio
fin_minuto = 4     # Minuto de fin
fin_segundo = 31    # Segundo de fin
ruta_salida = 'llantas8.mp4'  # Ruta donde se guardará el video recortado

recortar_video(ruta_video, inicio_minuto, inicio_segundo, fin_minuto, fin_segundo, ruta_salida)