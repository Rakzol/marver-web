import os
import json
import mido

def find_gh3_songs(base_path):
    """
    Busca la carpeta de canciones de Guitar Hero 3.
    """
    songs_path = os.path.join(base_path, "songs")
    if not os.path.exists(songs_path):
        raise FileNotFoundError("No se encontró la carpeta de canciones en la ruta proporcionada.")

    songs = []
    for root, dirs, files in os.walk(songs_path):
        for file in files:
            if file.endswith(".mid") or file.endswith(".chart"):
                songs.append(os.path.join(root, file))
    return songs

def parse_midi_file(midi_path):
    """
    Analiza un archivo MIDI y devuelve eventos de notas y BPM.
    """
    mid = mido.MidiFile(midi_path)
    bpm = 120.0  # Valor predeterminado de BPM
    notes = []

    for track in mid.tracks:
        time = 0
        for msg in track:
            time += msg.time
            if msg.type == 'set_tempo':
                bpm = mido.tempo2bpm(msg.tempo)
            if msg.type == 'note_on' and msg.velocity > 0:
                notes.append({
                    "_time": time / mid.ticks_per_beat * (bpm / 60),
                    "_lineIndex": msg.note % 4,  # Simplificación para línea
                    "_lineLayer": (msg.note // 4) % 3,  # Simplificación para capa
                    "_type": 0,  # 0 para izquierda, 1 para derecha
                    "_cutDirection": 8  # Dirección predeterminada
                })
    return notes, bpm

def create_beat_saber_map(song_path, output_folder):
    """
    Genera un archivo JSON inicial para una pista de Beat Saber.
    """
    song_name = os.path.basename(song_path).split('.')[0]
    notes, bpm = parse_midi_file(song_path)

    map_data = {
        "_version": "2.0.0",
        "_events": [],
        "_notes": notes,
        "_obstacles": [],
        "_customData": {
            "_songFilename": f"{song_name}.ogg",
            "_bpm": bpm,
        }
    }

    output_path = os.path.join(output_folder, f"{song_name}.dat")
    with open(output_path, "w") as f:
        json.dump(map_data, f, indent=4)

    print(f"Pista de Beat Saber generada: {output_path}")

def main():
    gh3_path = input("Introduce la ruta de instalación de Guitar Hero 3: ")
    output_folder = input("Introduce la carpeta de salida para las pistas de Beat Saber: ")

    if not os.path.exists(output_folder):
        os.makedirs(output_folder)

    try:
        songs = find_gh3_songs(gh3_path)
        print(f"Se encontraron {len(songs)} canciones.")

        for song_path in songs:
            if song_path.endswith(".mid"):
                create_beat_saber_map(song_path, output_folder)

    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()
