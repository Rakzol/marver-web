from moviepy.editor import VideoFileClip

for c in range(1,11):
    VideoFileClip('llantas' + str(c) + '.mp4').subclip(5, 10).write_gif('llantas' + str(c) + '.gif', fps = 60, loop=0)