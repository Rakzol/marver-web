from moviepy.editor import VideoFileClip

from PIL import Image

#Verifica si 'ANTIALIAS' está en la versión actual de Pillow
if not hasattr(Image, 'ANTIALIAS'):
    Image.ANTIALIAS = Image.LANCZOS

video = VideoFileClip('C:/Users/Rakzol/Downloads/videoplayback.mp4')

clips = [
    [(0,1,29),(0,1,34)],
    [(0,2,58),(0,3,3)],
    [(0,6,2),(0,6,7)],
    [(0,6,58),(0,7,3)],
    [(0,7,32),(0,7,37)],
    [(0,8,45),(0,8,50)],
    [(0,4,11),(0,4,16)],
    [(0,4,21),(0,4,26)],
    [(0,10,59),(0,11,4)],
    [(0,12,1),(0,12,6)],
]

c = 0
for clip in clips:
    c += 1
    subclip = video.subclip(clip[0], clip[1]).resize(height=480).set_fps(30)
    subclip.write_videofile(f'Lowllantas{c}.mp4', codec='libx264', audio=False, bitrate='500k')
    subclip.write_videofile(f'Lowllantas{c}.webm', codec='libvpx', audio=False, bitrate='500k')
    subclip.write_videofile(f'Lowllantas{c}.mov', codec='libx264', audio=False, bitrate='500k')