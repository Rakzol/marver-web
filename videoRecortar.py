from moviepy.editor import VideoFileClip, vfx

# VideoFileClip('video480p.mp4').subclip((0,1,33),(0,1,35)).fx(vfx.speedx, 0.5).write_videofile('llanta480pv.mp4')
# VideoFileClip('video480p.mp4').subclip((0,1,30),(0,1,35)).write_videofile('llanta480p.mp4')
# VideoFileClip('video1440p.mp4').subclip((0,1,30),(0,1,35)).resize(height=480).write_videofile('llanta1440p.mp4')
# VideoFileClip('video1440p2.webm').subclip((0,1,30),(0,1,35)).resize(height=480).write_videofile('llanta1440p2.mp4')

# exit()

video = VideoFileClip('video480pwebm.webm')

clips = [
    [(0,1,30),(0,1,35)],
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

#El bitrate hace la magia para bajarlo peso 500k es ideal para 480p
c = 0
for clip in clips:
    c += 1
    subclip = video.subclip(clip[0], clip[1])
    # subclip.write_videofile(f'Low2llantas{c}.mp4', codec='libx264', preset='ultrafast', fps=30, audio=False, bitrate='500k')
    subclip.write_videofile(f'llantas{c}.webm', bitrate='500k')

    # subclip.write_videofile(f'Lowllantas{c}.webm', codec='libvpx', audio=False, bitrate='500k')
    # subclip.write_videofile(f'Lowllantas{c}.mov', codec='libx264', audio=False, bitrate='500k')
    # video.subclip(clip[0], clip[1]).resize(height=144).write_gif(f'Lowllantas{c}.gif', fps=30)