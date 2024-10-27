from pytubefix import YouTube

def descargar_video(url):
    try:
        yt = YouTube(url)

        indice = -1
        for stream in yt.streams:
            indice += 1
            print(indice, stream)

        yt.streams[int(input("Indice:"))].download()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    url = "https://www.youtube.com/watch?v=_BXYvOdkBgg"
    descargar_video(url)
